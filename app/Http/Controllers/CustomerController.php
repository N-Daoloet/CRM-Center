<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $query = Customer::query();
        $search = $request->input('search');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $customers = $query->latest()->get();

        return view('customers.index', compact('customers', 'search'));
    }

    public function create(): View
    {
        return view('customers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'english_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'phone' => 'nullable|string|max:50',
            'flag' => 'nullable|integer',
            'channel' => 'nullable|integer',
            'province_id' => 'nullable|integer',
            'district_id' => 'nullable|integer',
            'sub_district_id' => 'nullable|integer',
            'address' => 'nullable|string',
            'zipcode' => 'nullable|string|max:20',
            'status' => 'nullable|in:active,inactive',
        ]);

        $data['status'] = $data['status'] ?? 'active';

        Customer::create($data);

        return redirect()->route('customers.index')->with('success', 'เพิ่มลูกค้าเรียบร้อยแล้ว');
    }

    public function edit(Customer $customer): View
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'english_name' => 'nullable|string|max:255',
            'email' => ['required', 'email', 'unique:customers,email,' . $customer->id],
            'phone' => 'nullable|string|max:50',
            'flag' => 'nullable|integer',
            'channel' => 'nullable|integer',
            'province_id' => 'nullable|integer',
            'district_id' => 'nullable|integer',
            'sub_district_id' => 'nullable|integer',
            'address' => 'nullable|string',
            'zipcode' => 'nullable|string|max:20',
            'status' => 'nullable|in:active,inactive',
        ]);

        $data['status'] = $data['status'] ?? $customer->status ?? 'active';

        $customer->update($data);

        return redirect()->route('customers.index')->with('success', 'อัปเดตข้อมูลลูกค้าเรียบร้อยแล้ว');
    }
}
