<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@dianmustika.test')->firstOrFail();
    }

    public function test_user_index_renders(): void
    {
        $response = $this->actingAs($this->admin)->get(route('users.index'));

        $response->assertOk();
        $response->assertSee('Manajemen Pengguna (Users)');
        $response->assertSee($this->admin->email);
    }

    public function test_user_create_page_renders(): void
    {
        $response = $this->actingAs($this->admin)->get(route('users.create'));

        $response->assertOk();
        $response->assertSee('Tambah Pengguna Baru');
        $response->assertSee('Hak Akses / Peran');
    }

    public function test_user_can_be_created_with_roles(): void
    {
        $role = Role::firstOrFail();

        $response = $this->actingAs($this->admin)->post(route('users.store'), [
            'name' => 'Staff Kasir Baru',
            'email' => 'kasirbaru@dianmustika.test',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'status' => 'ACTIVE',
            'roles' => [$role->id],
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'kasirbaru@dianmustika.test',
            'name' => 'Staff Kasir Baru',
            'status' => 'ACTIVE',
        ]);

        $newUser = User::where('email', 'kasirbaru@dianmustika.test')->firstOrFail();
        $this->assertTrue($newUser->roles->contains('id', $role->id));
    }

    public function test_user_can_be_updated(): void
    {
        $user = User::factory()->create([
            'name' => 'User Lama',
            'email' => 'lama@dianmustika.test',
            'status' => 'ACTIVE',
        ]);

        $response = $this->actingAs($this->admin)->put(route('users.update', $user), [
            'name' => 'User Diedit',
            'email' => 'diedit@dianmustika.test',
            'status' => 'INACTIVE',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'User Diedit',
            'email' => 'diedit@dianmustika.test',
            'status' => 'INACTIVE',
        ]);
    }

    public function test_user_status_can_be_toggled(): void
    {
        $user = User::factory()->create([
            'status' => 'ACTIVE',
        ]);

        $response = $this->actingAs($this->admin)->patch(route('users.toggle-status', $user));

        $response->assertRedirect();
        $this->assertEquals('INACTIVE', $user->fresh()->status);

        $this->actingAs($this->admin)->patch(route('users.toggle-status', $user));
        $this->assertEquals('ACTIVE', $user->fresh()->status);
    }
}

