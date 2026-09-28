<?php

namespace App\Http\Controllers;

use App\Models\SetupTeam;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalesmanController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()->where('role', 'sale');
        $search = $request->input('search');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $salesmen = $query->latest()->get();
        $supervisors = User::query()->where('role', 'supervisor')->orderBy('name')->get();
        $teamMembersBySupervisor = SetupTeam::with('member')->get()->groupBy('supervisor_user_id');
        $totalTeams = $supervisors->count();
        $totalMembers = User::query()->where('role', 'sale')->count();
        $unassignedMembers = User::query()->where('role', 'sale')->whereDoesntHave('setupTeamsAsMember')->count();
        // dd($supervisors);
        return view('salesmen.index', compact('salesmen', 'search', 'supervisors', 'teamMembersBySupervisor', 'totalTeams', 'totalMembers', 'unassignedMembers'));
    }

    public function create(): View
    {
        $supervisors = User::query()->where('role', 'supervisor')->orderBy('name')->get();

        return view('salesmen.create', compact('supervisors'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:50',
            'position' => 'nullable|string|max:100',
            'province_id' => 'nullable|integer|exists:provinces,id',
            'district_id' => 'nullable|integer|exists:districts,id',
            'sub_district_id' => 'nullable|integer|exists:sub_districts,id',
            'address' => 'nullable|string',
            'zipcode' => 'nullable|string|max:20',
            'supervisor_id' => 'nullable|exists:users,id',
            'sales_target' => 'nullable|numeric|min:0',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt('secret1234'),
            'role' => 'sale',
            'phone' => $data['phone'] ?? null,
            'position' => $data['position'] ?? null,
            'province_id' => $data['province_id'] ?? null,
            'district_id' => $data['district_id'] ?? null,
            'sub_district_id' => $data['sub_district_id'] ?? null,
            'address' => $data['address'] ?? null,
            'zipcode' => $data['zipcode'] ?? null,
        ]);

        if (!empty($data['supervisor_id'])) {
            SetupTeam::create([
                'supervisor_user_id' => $data['supervisor_id'],
                'member_user_id' => $user->id,
                'sales_target' => $data['sales_target'] ?? 0,
            ]);
        }

        return redirect()->route('salesmen.index')->with('success', 'เพิ่มพนักงานขายเรียบร้อยแล้ว');
    }

    public function teamDetail($salesmanId): View
    {
        $supervisor = User::findOrFail($salesmanId);
        $teamMembers = SetupTeam::where('supervisor_user_id', $supervisor->id)
            ->with('member')
            ->get();

        $availableMembers = User::query()
            ->where('role', 'sale')
            ->where('id', '!=', $supervisor->id)
            ->whereDoesntHave('setupTeamsAsMember')
            ->orderBy('name')
            ->get();

        return view('salesmen.team-detail', compact('supervisor', 'teamMembers', 'availableMembers'));
    }

    public function teamAssign(Request $request, $salesmanId): RedirectResponse
    {
        $data = $request->validate([
            'member_id' => 'required|exists:users,id',
            'sales_target' => 'nullable|numeric|min:0',
        ]);

        $supervisor = User::findOrFail($salesmanId);
        $member = User::findOrFail($data['member_id']);

        SetupTeam::updateOrCreate(
            [
                'supervisor_user_id' => $supervisor->id,
                'member_user_id' => $member->id,
            ],
            [
                'sales_target' => $data['sales_target'] ?? 0,
            ]
        );

        return back()->with('success', 'เพิ่มพนักงานในทีมเรียบร้อยแล้ว');
    }

    public function teamRemove(Request $request, $salesmanId): RedirectResponse
    {
        $supervisor = User::findOrFail($salesmanId);
        $memberId = $request->input('member_id');

        SetupTeam::where('supervisor_user_id', $supervisor->id)
            ->where('member_user_id', $memberId)
            ->delete();

        return back()->with('success', 'ลบพนักงานจากทีมเรียบร้อยแล้ว');
    }

    public function teamUpdateTarget(Request $request, $salesmanId): RedirectResponse
    {
        $supervisor = User::findOrFail($salesmanId);
        $data = $request->validate([
            'member_id' => 'required|exists:users,id',
            'sales_target' => 'nullable|numeric|min:0',
        ]);

        SetupTeam::where('supervisor_user_id', $supervisor->id)
            ->where('member_user_id', $data['member_id'])
            ->update(['sales_target' => $data['sales_target']]);

        return back()->with('success', 'อัปเดตเป้าหมายยอดขายเรียบร้อยแล้ว');
    }
}
