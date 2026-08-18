<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignWorkOrderRequest;
use App\Http\Requests\StoreWorkOrderRequest;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class WorkOrderController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', WorkOrder::class);

        $user = auth()->user();

        $query = WorkOrder::with(['asset', 'technician', 'creator'])->latest();

        if ($user->hasRole('technician')) {
            $query->where('assigned_to', $user->id);
        }

        return Inertia::render('WorkOrders/Index', [
            'workOrders' => $query->paginate(15),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', WorkOrder::class);

        return Inertia::render('WorkOrders/Create');
    }

    public function store(StoreWorkOrderRequest $request): RedirectResponse
    {
        WorkOrder::create([
            ...$request->validated(),
            'created_by' => auth()->id(),
            'status' => 'draft',
        ]);

        return redirect()->route('supervisor.work-orders.index')
            ->with('success', 'Work order berhasil dibuat.');
    }

    public function show(WorkOrder $workOrder): Response
    {
        $this->authorize('view', $workOrder);

        return Inertia::render('WorkOrders/Show', [
            'workOrder' => $workOrder->load(['asset', 'technician', 'creator', 'parts', 'logs.user']),
        ]);
    }

    public function assign(AssignWorkOrderRequest $request, WorkOrder $workOrder): RedirectResponse
    {
        $this->authorize('assign', WorkOrder::class);

        $technician = User::findOrFail($request->validated('assigned_to'));

        if (! $technician->hasRole('technician')) {
            return back()->withErrors(['assigned_to' => 'User yang dipilih bukan Teknisi.']);
        }

        $previousStatus = $workOrder->status;
        $isReassignToDifferentTechnician = $workOrder->assigned_to !== $technician->id;

        $updateData = [
            'assigned_to' => $technician->id,
            'status' => 'assigned',
        ];

        // Reset hitungan reject & flag eskalasi HANYA kalau Teknisi berbeda
        // dari sebelumnya — sesuai ADR-004, eskalasi ditangani lewat reassign
        // ke Teknisi lain. Kalau Supervisor assign ulang ke Teknisi yang sama
        // (mis. Teknisi izin lalu masuk lagi), riwayat reject tidak relevan
        // untuk direset karena orangnya tidak berganti.
        if ($isReassignToDifferentTechnician) {
            $updateData['rejection_count'] = 0;
            $updateData['is_escalated'] = false;
        }

        $workOrder->update($updateData);

        $workOrder->logs()->create([
            'user_id' => auth()->id(),
            'from_status' => $previousStatus,
            'to_status' => 'assigned',
            'note' => $isReassignToDifferentTechnician && $previousStatus !== 'draft'
                ? "Dialihkan ke {$technician->name}"
                : "Ditugaskan ke {$technician->name}",
        ]);

        return back()->with('success', 'Work order berhasil ditugaskan.');
    }
}