<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use App\Services\ProjectMailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProjectSmtpTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'super-admin', 'guard_name' => 'web']);

        $this->admin = User::factory()->create(['is_active' => true]);
        $this->admin->assignRole('super-admin');

        $this->project = Project::create([
            'name' => ['zh-TW' => '測試專案'],
            'domain' => 'test.com',
            'yearly_budget_twd' => 1000,
            'contact_email' => 'contact@test.com',
        ]);
    }

    public function test_can_save_smtp_settings(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->putJson("/api/projects/{$this->project->id}", [
            'smtp_host' => 'smtp.gmail.com',
            'smtp_port' => 587,
            'smtp_encryption' => 'tls',
            'smtp_username' => 'user@gmail.com',
            'smtp_password' => 'test-app-password',
            'smtp_from_address' => 'noreply@test.com',
            'smtp_from_name' => 'Test Company',
        ]);

        $response->assertOk();

        $this->project->refresh();
        $this->assertEquals('smtp.gmail.com', $this->project->smtp_host);
        $this->assertEquals(587, $this->project->smtp_port);
        $this->assertEquals('user@gmail.com', $this->project->smtp_username);
        $this->assertEquals('test-app-password', $this->project->smtp_password);
        $this->assertEquals('noreply@test.com', $this->project->smtp_from_address);
    }

    public function test_empty_password_does_not_overwrite_existing(): void
    {
        Sanctum::actingAs($this->admin);

        $this->project->update([
            'smtp_host' => 'smtp.gmail.com',
            'smtp_username' => 'user@gmail.com',
            'smtp_password' => 'original-password',
        ]);

        $response = $this->putJson("/api/projects/{$this->project->id}", [
            'smtp_host' => 'smtp.gmail.com',
            'smtp_username' => 'new-user@gmail.com',
        ]);

        $response->assertOk();

        $this->project->refresh();
        $this->assertEquals('new-user@gmail.com', $this->project->smtp_username);
        $this->assertEquals('original-password', $this->project->smtp_password);
    }

    public function test_api_response_does_not_contain_smtp_password(): void
    {
        Sanctum::actingAs($this->admin);

        $this->project->update([
            'smtp_host' => 'smtp.gmail.com',
            'smtp_username' => 'user@gmail.com',
            'smtp_password' => 'secret-password',
        ]);

        $response = $this->getJson('/api/projects/current');

        $response->assertOk();
        $response->assertJsonMissing(['smtp_password' => 'secret-password']);
        $response->assertJsonFragment(['has_smtp_password' => true]);
    }

    public function test_api_response_has_smtp_password_false_when_not_set(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/projects/current');

        $response->assertOk();
        $response->assertJsonFragment(['has_smtp_password' => false]);
    }

    public function test_project_mail_service_fallback(): void
    {
        $service = new ProjectMailService();

        // Project without SMTP config should return default mailer
        $mailer = $service->getMailer($this->project);
        $this->assertNotNull($mailer);
        $this->assertFalse($service->hasSmtpConfig($this->project));
    }

    public function test_project_mail_service_with_config(): void
    {
        $this->project->update([
            'smtp_host' => 'smtp.gmail.com',
            'smtp_username' => 'user@gmail.com',
            'smtp_password' => 'test-password',
        ]);

        $service = new ProjectMailService();
        $this->assertTrue($service->hasSmtpConfig($this->project));
    }
}
