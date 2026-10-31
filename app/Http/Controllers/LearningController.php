<?php

namespace App\Http\Controllers;

use App\Jobs\SendReportEmail;
use App\Models\Attachment;
use App\Models\Report;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class LearningController extends Controller
{
    public function dashboard(Request $r)
    {
        $students = Student::visibleTo($r->user())->with('assignments.tutor')->orderBy('name')->get();
        $selected = $students->firstWhere('id', (int) $r->input('child')) ?? $students->first();
        $query = Report::whereIn('student_id', $students->pluck('id'))->with('student', 'tutor');
        if ($r->user()->role === 'parent') {
            $query->where('status', 'published')->where('student_id', $selected?->id ?? 0);
        }
        $r->validate(['month' => 'nullable|date_format:Y-m', 'q' => 'nullable|string|max:100']);
        if ($r->filled('month')) {
            $query->whereBetween('lesson_date', [$r->month.'-01', date('Y-m-t', strtotime($r->month.'-01'))]);
        }
        if ($r->filled('q')) {
            $query->whereHas('student', fn ($q) => $q->where('name', 'like', '%'.$r->q.'%'));
        }
        $reports = $query->orderByDesc('lesson_date')->orderByDesc('id')->paginate(12)->withQueryString();
        $chart = $selected?->reports()->where('status', 'published')->orderByDesc('lesson_date')->orderByDesc('id')->limit(12)->get()->reverse()->values() ?? collect();

        return view('dashboard.index', compact('students', 'selected', 'reports', 'chart'));
    }

    public function form(Request $r, ?Report $report = null)
    {
        abort_if($r->user()->role === 'parent', 403);
        if ($report) {
            abort_unless($report->canEdit($r->user()), 403);
        }
        $students = Student::visibleTo($r->user())->where(function (Builder $query) use ($report): void {
            $query->where('active', true);
            if ($report) {
                $query->orWhere('id', $report->student_id);
            }
        })->orderBy('name')->get();

        return view('dashboard.report-form', ['report' => $report ?? new Report, 'students' => $students]);
    }

    public function save(Request $r, ?Report $report = null)
    {
        $u = $r->user();
        abort_if($u->role === 'parent', 403);
        if ($report) {
            abort_unless($report->canEdit($u), 403);
        }
        $publishing = $r->input('action') === 'publish';
        if ($report?->status === 'published') {
            abort_unless($publishing, 422, 'Laporan terbit harus disimpan sebagai pembaruan.');
        }
        $required = $publishing ? 'required' : 'nullable';
        $data = $r->validate([
            'student_id' => 'required|integer', 'lesson_date' => 'required|date|before_or_equal:today',
            'subject' => "$required|string|max:150", 'material' => "$required|string|max:5000", 'mastered' => 'nullable|string|max:5000', 'practice' => 'nullable|string|max:5000',
            'understanding' => [$required, Rule::in(['Baik', 'Cukup', 'Perlu Latihan'])],
            'concentration' => [$required, Rule::in(['Baik', 'Cukup', 'Perlu Latihan'])],
            'problem_solving' => [$required, Rule::in(['Baik', 'Cukup', 'Perlu Latihan'])],
            'notes' => "$required|string|max:10000", 'token' => 'required|uuid', 'files' => 'nullable|array|max:3', 'files.*' => 'file|mimes:pdf|mimetypes:application/pdf|max:5120', 'remove_files' => 'nullable|array', 'remove_files.*' => 'integer',
        ]);
        $student = Student::visibleTo($u)->when(! $report, fn (Builder $query): Builder => $query->where('active', true))->findOrFail($data['student_id']);
        if ($report) {
            abort_unless($report->student_id === $student->id, 422);
        }
        $existing = DB::table('publications')->where('token', $data['token'])->first();
        if ($existing) {
            $previous = Report::findOrFail($existing->report_id);
            abort_unless($previous->canEdit($u), 403);

            return redirect('/laporan/'.$previous->id)->with('success', 'Laporan sudah diterbitkan.');
        }
        $removals = $report ? $report->attachments()->whereIn('id', $r->input('remove_files', []))->get() : collect();
        abort_if(($report?->attachments()->count() ?? 0) - $removals->count() + count($r->file('files', [])) > 3, 422, 'Maksimal 3 PDF dalam satu laporan.');
        $stored = [];
        try {
            $saved = DB::transaction(function () use ($r, $report, $student, $u, $data, $publishing, $removals, &$stored) {
                $model = $report ? Report::whereKey($report->id)->lockForUpdate()->firstOrFail() : new Report;
                $model->fill(collect($data)->except(['token', 'files', 'remove_files'])->all());
                if (! $model->exists) {
                    $model->user_id = $u->id;
                }
                if ($publishing) {
                    $model->status = 'published';
                    $model->published_at ??= now();
                    $model->revision++;
                    $model->grade_snapshot ??= $student->grade;
                    $model->school_snapshot ??= $student->school;
                }
                $model->save();
                foreach ($removals as $f) {
                    $f->delete();
                }
                foreach ($r->file('files', []) as $file) {
                    $path = $file->store('reports', 'local');
                    $stored[] = $path;
                    $model->attachments()->create(['path' => $path, 'name' => basename($file->getClientOriginalName()), 'size' => $file->getSize()]);
                }
                if ($publishing) {
                    $id = DB::table('publications')->insertGetId(['report_id' => $model->id, 'token' => $data['token'], 'revision' => $model->revision, 'created_at' => now(), 'updated_at' => now()]);
                    $parents = User::where('role', 'parent')->where('active', true)->whereNotNull('email_verified_at')->whereIn('email', $student->guardians()->pluck('email'))->get();
                    foreach ($parents as $parent) {
                        $delivery = DB::table('deliveries')->insertGetId(['publication_id' => $id, 'user_id' => $parent->id, 'created_at' => now(), 'updated_at' => now()]);
                        SendReportEmail::dispatch($delivery);
                    }
                }

return $model;
            });
        } catch (\Throwable $e) {
            foreach ($stored as $path) {
                Storage::disk('local')->delete($path);
            }throw $e;
        }
        foreach ($removals as $f) {
            Storage::disk('local')->delete($f->path);
        }

        return redirect('/laporan/'.$saved->id)->with('success', $publishing ? 'Laporan diterbitkan. Pemberitahuan masuk antrean email.' : 'Draf berhasil disimpan.');
    }

    public function show(Request $r, Report $report)
    {
        abort_unless($report->canRead($r->user()), 404);
        $report->load('student', 'tutor', 'attachments');

        return view('dashboard.report', compact('report'));
    }

    public function download(Request $r, Attachment $attachment)
    {
        abort_unless($attachment->report->canRead($r->user()),404);

        return Storage::disk('local')->download($attachment->path,$attachment->name,['Content-Type' => 'application/pdf', 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store']);
    }
}
