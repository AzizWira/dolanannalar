<?php

namespace App\Jobs;

use App\Models\Report;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendReportEmail implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public int $deliveryId)
    {
        $this->afterCommit();
    }

    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function middleware(): array
    {
        return [(new WithoutOverlapping('report-delivery-'.$this->deliveryId))->releaseAfter(30)->expireAfter(120)];
    }

    public function handle(): void
    {
        $d = DB::table('deliveries')->find($this->deliveryId);
        if (! $d || $d->sent_at) {
            return;
        }
        $p = DB::table('publications')->find($d->publication_id);
        $report = Report::find($p?->report_id);
        $u = User::find($d->user_id);
        if (! $report || ! $u || ! $u->active || ! $u->hasVerifiedEmail() || $u->role !== 'parent' || ! $report->canRead($u)) {
            return;
        }
        Mail::to($u->email)->send((new Mailable)->subject('Pembaruan belajar anak — Dolanan Nalar')->view('emails.report', ['url' => url('/laporan/'.$report->id)])->text('emails.report-text'));
        DB::table('deliveries')->where('id', $d->id)->update(['sent_at' => now(), 'updated_at' => now()]);
    }
}
