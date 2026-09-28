<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $supervisorTeams = User::query()
            ->where('role', 'supervisor')
            ->withCount('setupTeamsAsSupervisor')
            ->orderBy('name')
            ->get();

        return view('settings.index', compact('supervisorTeams'));
    }

    public function updateTeamTarget(Request $request, User $supervisor): RedirectResponse
    {
        if ($supervisor->role !== 'supervisor') {
            abort(404);
        }

        $data = $request->validate([
            'team_sales_target' => 'required|numeric|min:0',
        ]);

        $supervisor->update([
            'team_sales_target' => $data['team_sales_target'],
        ]);

        return back()->with('success', 'อัปเดต Target ทีมเรียบร้อยแล้ว');
    }
}
