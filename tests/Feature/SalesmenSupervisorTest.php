<?php

namespace Tests\Feature;

use App\Models\SetupTeam;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class SalesmenSupervisorTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    public function test_salesmen_store_creates_sale_user_and_setup_team_assignment(): void
    {
        $supervisor = User::create([
            'name' => 'Team Supervisor',
            'email' => 'team-supervisor@example.com',
            'password' => bcrypt('secret'),
            'role' => 'supervisor',
        ]);

        $response = $this->post(route('salesmen.store'), [
            'name' => 'Member One',
            'email' => 'member-one@example.com',
            'phone' => '0811111111',
            'position' => 'Account Executive',
            'supervisor_id' => $supervisor->id,
            'sales_target' => 150000,
        ]);

        $response->assertRedirect(route('salesmen.index'));

        $member = User::where('email', 'member-one@example.com')->firstOrFail();
        $this->assertSame('sale', $member->role);
        $this->assertDatabaseHas('setup_teams', [
            'supervisor_user_id' => $supervisor->id,
            'member_user_id' => $member->id,
            'sales_target' => '150000.00',
        ]);
    }

    public function test_salesmen_index_loads_supervisors_and_sale_users(): void
    {
        $supervisor = User::create([
            'name' => 'Team Supervisor',
            'email' => 'team-supervisor@example.com',
            'password' => bcrypt('secret'),
            'role' => 'supervisor',
        ]);

        $member = User::create([
            'name' => 'Member One',
            'email' => 'member-one@example.com',
            'password' => bcrypt('secret'),
            'role' => 'sale',
        ]);

        SetupTeam::create([
            'supervisor_user_id' => $supervisor->id,
            'member_user_id' => $member->id,
            'sales_target' => 120000,
        ]);

        $response = $this->get(route('salesmen.index'));

        $response->assertOk();
        $response->assertSee('ทีมผู้ดูแล');
        $response->assertSee('พนักงานขาย');
        $response->assertSee('Team Supervisor');
        $response->assertSee('Member One');
    }
}
