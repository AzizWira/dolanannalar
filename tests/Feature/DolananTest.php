<?php

namespace Tests\Feature;

use App\Jobs\SendReportEmail;
use App\Models\Attachment;
use App\Models\Report;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Tests\TestCase;

class DolananTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role = 'parent', string $email = 'parent@example.test'): User
    {
        $u = User::factory()->create(['email' => $email]);
        $u->forceFill(['role' => $role, 'active' => true, 'email_verified_at' => now()])->save();

        return $u;
    }

    private function student(User $parent, ?User $tutor = null): Student
    {
        $s = Student::create(['name' => 'Anak Uji', 'grade' => 'SD kelas 3', 'school' => 'Sekolah Uji']);
        $s->guardians()->create(['email' => $parent->email]);
        if ($tutor) {
            $s->assignments()->create(['user_id' => $tutor->id, 'type' => 'primary']);
        }

return $s;
    }

    private function report(Student $s, User $t, string $status = 'published'): Report
    {
        return $s->reports()->create(['user_id' => $t->id, 'lesson_date' => today(), 'subject' => 'Matematika', 'material' => 'Penjumlahan', 'understanding' => 'Baik', 'concentration' => 'Cukup', 'problem_solving' => 'Perlu Latihan', 'notes' => 'Latihan tiga soal', 'status' => $status]);
    }

    private function payload(Student $s, string $action = 'publish'): array
    {
        return ['student_id' => $s->id, 'lesson_date' => today()->toDateString(), 'subject' => 'Matematika', 'material' => 'Penjumlahan', 'understanding' => 'Baik', 'concentration' => 'Cukup', 'problem_solving' => 'Perlu Latihan', 'notes' => 'Latihan tiga soal', 'token' => (string) Str::uuid(), 'action' => $action];
    }

    public function test_public_pages_render_and_prices_are_correct(): void
    {
        foreach (['/', '/layanan', '/tentor', '/harga', '/perkembangan-anak', '/masuk', '/daftar', '/lupa-password', '/sitemap.xml', '/robots.txt'] as $path) {
            $this->get($path)->assertOk();
        }
        $this->get('/harga')->assertSee('Rp25.000')->assertSee('Rp50.000')->assertSee('Tepat 5 km')->assertSee('Rp15.000');
    }

    public function test_parent_only_sees_linked_children_and_published_reports(): void
    {
        $p = $this->user();
        $other = $this->user('parent', 'other@example.test');
        $t = $this->user('tutor', 'tutor@example.test');
        $s = $this->student($p, $t);
        $unrelated = $this->student($other, $t);
        $visible = $this->report($s, $t);
        $draft = $this->report($s, $t, 'draft');
        $hidden = $this->report($unrelated, $t);
        $this->actingAs($p)->get('/dashboard')->assertOk()->assertSee('Jejak perkembangan');
        $this->get('/laporan/'.$visible->id)->assertOk();
        $this->get('/laporan/'.$draft->id)->assertNotFound();
        $this->get('/laporan/'.$hidden->id)->assertNotFound();
        $this->get('/laporan/buat')->assertForbidden();
        $this->get('/kelola/murid')->assertForbidden();
        $this->get('/dashboard?child='.$unrelated->id)->assertOk()->assertDontSee('other@example.test');
    }

    public function test_unverified_and_disabled_accounts_cannot_access_reports(): void
    {
        $p = $this->user();
        $p->forceFill(['email_verified_at' => null])->save();
        $this->actingAs($p)->get('/dashboard')->assertRedirect('/verifikasi-email');
        $p->forceFill(['active' => false, 'email_verified_at' => now()])->save();
        $this->actingAs($p)->get('/dashboard')->assertRedirect('/masuk');
    }

    public function test_draft_is_private_and_publication_queues_once(): void
    {
        Queue::fake();
        $p = $this->user();
        $t = $this->user('tutor', 'tutor@example.test');
        $s = $this->student($p, $t);
        $data = $this->payload($s, 'draft');
        $this->actingAs($t)->post('/laporan', $data)->assertRedirect();
        Queue::assertNothingPushed();
        $report = Report::firstOrFail();
        $data['action'] = 'publish';
        $data['token'] = (string) Str::uuid();
        $this->put('/laporan/'.$report->id, $data)->assertRedirect('/laporan/'.$report->id);
        Queue::assertPushed(SendReportEmail::class, 1);
        $this->put('/laporan/'.$report->id, $data)->assertRedirect();
        Queue::assertPushed(SendReportEmail::class, 1);
        $data['token'] = (string) Str::uuid();
        $data['notes'] = 'Catatan diperbarui';
        $this->put('/laporan/'.$report->id, $data)->assertRedirect();
        Queue::assertPushed(SendReportEmail::class, 2);
        $this->assertDatabaseHas('reports', ['id' => $report->id, 'revision' => 2, 'status' => 'published']);
    }

    public function test_tutor_cannot_write_for_unassigned_student_or_edit_another_authors_report(): void
    {
        $p = $this->user();
        $t = $this->user('tutor', 'tutor@example.test');
        $replacement = $this->user('tutor', 'replacement@example.test');
        $s = $this->student($p, $t);
        $report = $this->report($s, $t);
        $this->actingAs($replacement)->post('/laporan', $this->payload($s))->assertNotFound();
        $s->assignments()->create(['user_id' => $replacement->id, 'type' => 'replacement']);
        $this->get('/laporan/'.$report->id)->assertOk();
        $this->get('/laporan/'.$report->id.'/edit')->assertForbidden();
        $s->assignments()->where('user_id', $replacement->id)->update(['ended_at' => now()]);
        $this->get('/laporan/'.$report->id)->assertNotFound();
    }

    public function test_pdf_download_is_private_and_relationship_revocation_takes_effect(): void
    {
        Storage::fake('local');
        $p = $this->user();
        $other = $this->user('parent', 'other@example.test');
        $t = $this->user('tutor', 'tutor@example.test');
        $s = $this->student($p, $t);
        $report = $this->report($s, $t);
        Storage::disk('local')->put('reports/test.pdf', '%PDF-1.4 test');
        $file = $report->attachments()->create(['path' => 'reports/test.pdf', 'name' => 'latihan.pdf', 'size' => 13]);
        $this->actingAs($p)->get('/lampiran/'.$file->id)->assertOk()->assertDownload('latihan.pdf');
        $this->actingAs($other)->get('/lampiran/'.$file->id)->assertNotFound();
        $s->guardians()->delete();
        $this->actingAs($p)->get('/lampiran/'.$file->id)->assertNotFound();
    }

    public function test_upload_rejects_non_pdf_and_oversize_files(): void
    {
        Storage::fake('local');
        $p = $this->user();
        $t = $this->user('tutor', 'tutor@example.test');
        $s = $this->student($p, $t);
        $data = $this->payload($s);
        $data['files'] = [UploadedFile::fake()->image('photo.jpg')];
        $this->actingAs($t)->post('/laporan', $data)->assertSessionHasErrors('files.0');
        $data['files'] = [UploadedFile::fake()->create('large.pdf', 6000, 'application/pdf')];
        $this->post('/laporan', $data)->assertSessionHasErrors('files.0');
        $this->assertDatabaseCount('reports', 0);
    }

    public function test_content_is_persistent_and_only_admin_can_update_it(): void
    {
        $p = $this->user();
        $admin = $this->user('admin', 'admin@example.test');
        $data = config('dolanan.site');
        $data['headline'] = 'Judul dari pengelola';
        $data['prices'][0]['price'] = 27000;
        $this->actingAs($p)->put('/kelola/konten', $data)->assertForbidden();
        $this->actingAs($admin)->put('/kelola/konten', $data)->assertRedirect();
        $this->get('/')->assertSee('Judul dari');
        $this->get('/harga')->assertSee('Rp27.000');
        $this->assertDatabaseHas('settings', ['key' => 'site']);
    }

    public function test_admin_screens_render_and_cannot_disable_last_admin(): void
    {
        $a = $this->user('admin', 'admin@example.test');
        $this->actingAs($a);
        foreach (['/kelola/murid', '/kelola/tentor', '/kelola/konten', '/laporan/buat', '/dashboard'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->put('/kelola/tentor/'.$a->id, ['name' => $a->name, 'email' => $a->email])->assertStatus(422);
    }

    public function test_registration_cannot_escalate_role_or_bypass_verification(): void
    {
        Mail::fake();
        $this->post('/daftar', ['name' => 'Orang Tua', 'email' => 'NEW@example.test', 'password' => 'SecurePass2026', 'password_confirmation' => 'SecurePass2026', 'role' => 'admin', 'email_verified_at' => now()])->assertRedirect('/verifikasi-email');
        $this->assertDatabaseHas('users', ['email' => 'new@example.test', 'role' => 'parent', 'email_verified_at' => null]);
    }

    public function test_notification_does_not_send_after_guardian_access_revoked(): void
    {
        Mail::fake();
        $p = $this->user();
        $t = $this->user('tutor', 'tutor@example.test');
        $s = $this->student($p, $t);
        $report = $this->report($s, $t);
        $pub = DB::table('publications')->insertGetId(['report_id' => $report->id, 'token' => (string) Str::uuid(), 'revision' => 1]);
        $id = DB::table('deliveries')->insertGetId(['publication_id' => $pub, 'user_id' => $p->id]);
        $s->guardians()->delete();
        (new SendReportEmail($id))->handle();
        Mail::assertNothingSent();
    }

    public function test_notification_is_sent_once_with_link_without_report_contents(): void
    {
        Mail::fake();
        $p = $this->user();
        $t = $this->user('tutor', 'tutor@example.test');
        $s = $this->student($p, $t);
        $report = $this->report($s, $t);
        $pub = DB::table('publications')->insertGetId(['report_id' => $report->id, 'token' => (string) Str::uuid(), 'revision' => 1]);
        $id = DB::table('deliveries')->insertGetId(['publication_id' => $pub, 'user_id' => $p->id]);
        $job = new SendReportEmail($id);
        $job->handle();
        $job->handle();
        Mail::assertSentCount(1);
        Mail::assertSent(Mailable::class, function ($mail) use ($p, $report) {
            $html = $mail->render();

            return $mail->hasTo($p->email) && str_contains($html, '/laporan/'.$report->id) && ! str_contains($html, $report->notes);
        });
        $this->assertNotNull(DB::table('deliveries')->find($id)->sent_at);
    }

    public function test_google_requires_verified_provider_identity(): void
    {
        $google = (new \Laravel\Socialite\Two\User)->setRaw(['email_verified' => false])->map(['id' => 'google-123', 'email' => 'parent@example.test', 'name' => 'Orang Tua']);
        $driver = \Mockery::mock();
        $driver->shouldReceive('user')->once()->andReturn($google);
        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($driver);
        $this->get('/auth/google/callback')->assertForbidden();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_google_can_link_verified_parent_but_not_unverified_existing_account(): void
    {
        $p = $this->user();
        $p->forceFill(['email_verified_at' => null])->save();
        $google = (new \Laravel\Socialite\Two\User)->setRaw(['email_verified' => true])->map(['id' => 'google-123', 'email' => $p->email, 'name' => 'Orang Tua']);
        $driver = \Mockery::mock();
        $driver->shouldReceive('user')->twice()->andReturn($google);
        Socialite::shouldReceive('driver')->with('google')->twice()->andReturn($driver);
        $this->get('/auth/google/callback')->assertForbidden();
        $p->forceFill(['email_verified_at' => now()])->save();
        $this->get('/auth/google/callback')->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($p);
        $this->assertDatabaseHas('users', ['id' => $p->id, 'google_id' => 'google-123', 'role' => 'parent']);
    }

    public function test_inactive_student_keeps_history_editable_but_cannot_receive_new_reports(): void
    {
        Queue::fake();
        $parent = $this->user();
        $tutor = $this->user('tutor', 'tutor@example.test');
        $student = $this->student($parent, $tutor);
        $report = $this->report($student, $tutor);
        $student->update(['active' => false]);
        $payload = $this->payload($student);
        $payload['notes'] = 'Koreksi catatan pertemuan sebelumnya.';
        $this->actingAs($tutor)->get('/laporan/'.$report->id.'/edit')->assertOk();
        $this->put('/laporan/'.$report->id, $payload)->assertRedirect('/laporan/'.$report->id);
        $this->assertDatabaseHas('reports', ['id' => $report->id, 'notes' => $payload['notes']]);
        $this->post('/laporan', $this->payload($student))->assertNotFound();
        $this->actingAs($parent)->get('/laporan/'.$report->id)->assertOk();
    }

    public function test_valid_pdf_is_saved_and_available_only_through_authorized_download(): void
    {
        Queue::fake();
        Storage::fake('local');
        $parent = $this->user();
        $tutor = $this->user('tutor', 'tutor@example.test');
        $student = $this->student($parent, $tutor);
        $payload = $this->payload($student);
        $payload['files'] = [UploadedFile::fake()->createWithContent('latihan.pdf', "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF")];
        $this->actingAs($tutor)->post('/laporan', $payload)->assertRedirect();
        $attachment = Attachment::firstOrFail();
        Storage::disk('local')->assertExists($attachment->path);
        $this->actingAs($parent)->get('/lampiran/'.$attachment->id)->assertOk()->assertDownload('latihan.pdf');
        $this->get('/storage/'.$attachment->path)->assertNotFound();
    }
}
