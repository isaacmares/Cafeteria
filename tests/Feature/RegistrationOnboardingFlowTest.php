<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RegistrationOnboardingFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_user_can_register_and_is_redirected_to_onboarding(): void
    {
        $response = $this->post('/register', [
            'name' => 'Juan Pérez',
            'email' => 'juan@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('onboarding.company'));
        $this->assertAuthenticated();

        $user = User::where('email', 'juan@example.com')->first();

        $this->assertNotNull($user);
        $this->assertNull($user->tenant_id);
        $this->assertTrue($user->is_active);
    }

    public function test_authenticated_user_without_tenant_is_redirected_to_onboarding(): void
    {
        $user = User::factory()->create(['tenant_id' => null]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('onboarding.company'));
    }

    public function test_user_can_complete_company_onboarding(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['tenant_id' => null]);

        $response = $this->actingAs($user)->post('/onboarding/company', [
            'name' => 'Mi Cafetería',
            'phone' => '5551234567',
            'address' => 'Calle Principal 123',
            'rfc' => 'XAXX010101000',
            'logo' => UploadedFile::fake()->image('logo.png'),
        ]);

        $response->assertRedirect(route('dashboard'));

        $user->refresh();

        $this->assertNotNull($user->tenant_id);
        $this->assertTrue($user->hasRole('admin'));

        $tenant = Tenant::find($user->tenant_id);
        $this->assertSame('Mi Cafetería', $tenant->name);
        $this->assertSame('active', $tenant->status);
        $this->assertNotNull($tenant->logo);

        $branch = Branch::where('tenant_id', $tenant->id)->first();
        $this->assertSame('Sucursal Principal', $branch->name);
        $this->assertSame('MAIN', $branch->code);
        $this->assertTrue($branch->is_active);
    }

    public function test_login_redirects_to_onboarding_when_tenant_is_missing(): void
    {
        User::factory()->create([
            'email' => 'login@example.com',
            'password' => bcrypt('password123'),
            'tenant_id' => null,
        ]);

        $response = $this->post('/login', [
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('onboarding.company'));
    }

    public function test_login_redirects_to_dashboard_when_tenant_exists(): void
    {
        $tenant = Tenant::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'Empresa Test',
            'status' => 'active',
        ]);

        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'tenant_id' => $tenant->id,
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
    }
}
