<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    private function authorizeAdmin(Request $r): void
    {
        abort_unless($r->user()->role === 'admin', 403);
    }

    public function students(Request $r)
    {
        $this->authorizeAdmin($r);

        return view('admin.students', ['students' => Student::with('guardians', 'assignments.tutor')->orderBy('name')->get(), 'tutors' => User::where('role', 'tutor')->where('active', true)->get(), 'edit' => $r->filled('edit') ? Student::with('guardians', 'assignments')->findOrFail($r->edit) : new Student]);
    }

    public function saveStudent(Request $r, ?Student $student = null)
    {
        $this->authorizeAdmin($r);
        $r->merge(['email' => strtolower(trim((string) $r->email))]);
        $d = $r->validate(['name' => 'required|string|max:100', 'grade' => 'required|string|max:80', 'school' => 'nullable|string|max:150', 'email' => 'required|email|max:190', 'primary' => 'required|integer', 'replacement' => 'nullable|integer|different:primary', 'active' => 'nullable|boolean']);
        $ids = array_filter([$d['primary'], $d['replacement'] ?? null]);
        abort_unless(User::where('role', 'tutor')->where('active', true)->whereIn('id', $ids)->count() === count($ids), 422, 'Pilih tentor aktif.');
        DB::transaction(function () use ($r, $student, $d, $ids) {
            $s = $student ?? new Student;
            $s->fill(collect($d)->only(['name', 'grade', 'school'])->all());
            $s->active = $r->boolean('active');
            $s->save();
            $s->guardians()->delete();
            $s->guardians()->create(['email' => $d['email']]);
            $s->assignments()->whereNull('ended_at')->update(['ended_at' => now()]);
            foreach ($ids as $i => $id) {
                $s->assignments()->create(['user_id' => $id, 'type' => $i === 0 ? 'primary' : 'replacement']);
            }
        });

        return redirect('/kelola/murid')->with('success', 'Data murid dan penugasan berhasil disimpan.');
    }

    public function tutors(Request $r)
    {
        $this->authorizeAdmin($r);

        return view('admin.tutors', ['tutors' => User::whereIn('role', ['admin', 'tutor'])->orderBy('role')->get(), 'edit' => $r->filled('edit') ? User::whereIn('role', ['admin', 'tutor'])->findOrFail($r->edit) : new User]);
    }

    public function saveTutor(Request $r, ?User $tutor = null)
    {
        $this->authorizeAdmin($r);
        $r->merge(['email' => strtolower(trim((string) $r->email))]);
        $d = $r->validate(['name' => 'required|string|max:100', 'email' => ['required', 'email', 'max:190', Rule::unique('users')->ignore($tutor?->id)], 'bio' => 'nullable|string|max:1500', 'subjects' => 'nullable|string|max:200', 'photo' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048', 'active' => 'nullable|boolean', 'public_profile' => 'nullable|boolean']);
        if ($tutor) {
            abort_unless(in_array($tutor->role, ['admin', 'tutor']), 404);
        }
        if ($tutor?->role === 'admin' && ! $r->boolean('active')) {
            abort_if(User::where('role', 'admin')->where('active', true)->where('id', '!=', $tutor->id)->count() === 0, 422, 'Super admin aktif terakhir tidak dapat dinonaktifkan.');
        }
        $new = ! $tutor;
        $tutor ??= new User;
        $tutor->forceFill(collect($d)->except('photo')->all());
        $tutor->active = $r->boolean('active');
        $tutor->public_profile = $r->boolean('public_profile');
        if ($r->hasFile('photo')) {
            $tutor->photo = $r->file('photo')->store('tutors', 'public');
        }
        if ($new) {
            $tutor->role = 'tutor';
            $tutor->password = Str::random(48);
        }$tutor->save();
        if ($new) {
            Password::sendResetLink(['email' => $tutor->email]);
        }

        return redirect('/kelola/tentor')->with('success', $new ? 'Tentor ditambahkan. Tautan membuat password dikirim melalui email.' : 'Profil dan akses berhasil diperbarui.');
    }

    public function content(Request $r)
    {
        $this->authorizeAdmin($r);

        return view('admin.content');
    }

    public function saveContent(Request $r)
    {
        $this->authorizeAdmin($r);
        $d = $r->validate(['name' => 'required|string|max:80', 'slogan' => 'required|string|max:150', 'headline' => 'required|string|max:150', 'description' => 'required|string|max:1500', 'address' => 'required|string|max:200', 'email' => 'nullable|email|max:190', 'instagram' => 'nullable|url:https|max:250', 'whatsapp' => 'required|regex:/^[0-9]{10,16}$/', 'transport' => 'required|integer|min:0', 'distance' => 'required|numeric|min:0', 'extra_20' => 'required|integer|min:0', 'extra_60' => 'required|integer|min:0', 'prices' => 'required|array|min:1|max:20', 'prices.*.label' => 'required|string|max:80', 'prices.*.price' => 'required|integer|min:0', 'services' => 'required|array|min:1|max:20', 'services.*.title' => 'required|string|max:100', 'services.*.level' => 'required|string|max:80', 'services.*.text' => 'required|string|max:1000', 'services.*.image' => 'required|in:jelajah-cerita,bermain-angka,tumbuh-bersama,belajar-di-rumah,ruang-orang-tua']);
        $d['prices'] = array_values($d['prices']);
        $d['services'] = array_values($d['services']);
        Setting::updateOrCreate(['key' => 'site'], ['value' => $d]);

        return back()->with('success','Informasi website berhasil diperbarui.');
    }
}
