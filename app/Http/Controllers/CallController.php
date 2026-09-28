<?php

namespace App\Http\Controllers;

use App\Models\Call;
use App\Models\Campaign;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CallController extends Controller
{
    public function index(Request $request): View
    {
        $query = Call::with(['customer', 'salesman', 'campaign']);
        $search = $request->input('search');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('customer', function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%");
                })->orWhereHas('salesman', function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%");
                })->orWhereHas('campaign', function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%");
                })->orWhere('result', 'like', "%{$search}%");
            });
        }

        $calls = $query->latest()->get();

        return view('calls.index', compact('calls', 'search'));
    }

    public function create(): View
    {
        $customers = Customer::all();
        $salesmen = User::query()->where('role', 'sale')->get();
        $campaigns = Campaign::all();

        return view('calls.create', compact('customers', 'salesmen', 'campaigns'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'salesman_id' => 'required|exists:users,id',
            'campaign_id' => 'nullable|exists:campaigns,id',
            'call_time' => 'required|date',
            'result' => 'nullable|string|max:50',
            'next_step' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        Call::create($data);

        return redirect()->route('customers.index')->with('success', 'บันทึกการโทรเรียบร้อยแล้ว');
    }
}
