<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerAssignment;
use App\Models\User;
use App\Notifications\CustomerAssignedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AssignmentController extends Controller
{
    public function index(): View
    {
        $assignments = CustomerAssignment::query()
            ->with(['customer:id,name', 'salesman:id,name'])
            ->latest('assigned_at')
            ->latest('id')
            ->paginate(15);

        return view('assignments.index', compact('assignments'));
    }

    public function create(): View
    {
        $customers = Customer::query()
            ->orderBy('name')
            ->get(['id', 'name', 'phone', 'flag']);

        $salesmen = User::query()
            ->where('role', 'sale')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('assignments.create', compact('customers', 'salesmen'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_ids' => 'required|array|min:1',
            'customer_ids.*' => 'required|integer|distinct|exists:customers,id',
            'salesman_id' => 'required|integer|exists:users,id',
            'assigned_at' => 'required|date',
            'status' => 'required|in:follow_up,pending,completed',
            'notes' => 'nullable|string|max:1000',
        ]);

        $salesman = User::query()
            ->where('id', $data['salesman_id'])
            ->where('role', 'sale')
            ->first();

        if (!$salesman) {
            return back()
                ->withInput()
                ->withErrors(['salesman_id' => 'ผู้ใช้ที่เลือกไม่ได้เป็นพนักงานขาย']);
        }

        $affectedCount = 0;
        $customerNames = Customer::query()
            ->whereIn('id', $data['customer_ids'])
            ->pluck('name', 'id');

        DB::transaction(function () use ($data, $salesman, $customerNames, &$affectedCount): void {
            foreach ($data['customer_ids'] as $customerId) {
                $existingAssignment = CustomerAssignment::query()
                    ->where('customer_id', $customerId)
                    ->first();
                $isNewAssignment = ! $existingAssignment
                    || (int) $existingAssignment->salesman_id !== (int) $data['salesman_id'];

                $assignment = CustomerAssignment::updateOrCreate(
                    ['customer_id' => $customerId],
                    [
                        'salesman_id' => $data['salesman_id'],
                        'assigned_at' => $data['assigned_at'],
                        'status' => $data['status'],
                        'notes' => $data['notes'] ?? null,
                    ]
                );

                if ($isNewAssignment) {
                    $salesman->notify(new CustomerAssignedNotification(
                        $assignment->id,
                        $customerNames->get($customerId, 'ลูกค้า')
                    ));
                }

                $affectedCount++;
            }
        });

        return redirect()
            ->route('assignments.index')
            ->with('success', "บันทึกการมอบหมายลูกค้า {$affectedCount} รายการเรียบร้อยแล้ว");
    }
}
