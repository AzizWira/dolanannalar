<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AccountPresentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_emails_use_branded_html_and_preserve_secure_links(): void
    {
        $user = User::factory()->create(['name' => '<Ayah & Bunda>']);
        $verification = (new VerifyEmail)->toMail($user);
        $this->assertTrue(URL::hasValidSignature(Request::create($verification->viewData['url'])));
        $this->assertStringContainsString('/verifikasi-email/'.$user->id.'/', $verification->viewData['url']);
        $reset = (new ResetPassword('sample-reset-token'))->toMail($user);
        $this->assertStringContainsString('/reset-password/sample-reset-token', $reset->viewData['url']);
        $this->assertStringContainsString('email='.urlencode($user->email), $reset->viewData['url']);
        foreach ([$verification, $reset] as $message) {
            $html = view($message->view['html'], $message->viewData)->render();
            $plain = view($message->view['text'], $message->viewData)->render();
            $this->assertStringContainsString('background-color:#174d3c', $html);
            $this->assertStringContainsString('&lt;Ayah &amp; Bunda&gt;', $html);
            $this->assertStringNotContainsString('<Ayah & Bunda>', $html);
            $this->assertStringContainsString(e($message->viewData['url']), $html);
            $this->assertStringContainsString($message->viewData['action'], $html);
            $this->assertStringContainsString($message->viewData['url'], $plain);
        }
    }

    public function test_logout_ends_session_and_blocks_dashboard_access(): void
    {
        $user = User::factory()->create(['role' => 'parent', 'active' => true]);
        $this->actingAs($user)->get('/dashboard')->assertOk()
            ->assertSee('data-logout-form', false)->assertSee('Yakin ingin keluar?');
        $this->post('/keluar')->assertRedirect('/');
        $this->assertGuest();
        $this->get('/dashboard')->assertRedirect('/masuk');
    }
}
