<?php

namespace App\Http\Controllers;

use App\Actions\WorkOrder\ApproveWorkOrderAction;
use App\Actions\WorkOrder\RejectWorkOrderAction;
use App\Actions\WorkOrder\TransitionWorkOrderStatusAction;
use App\Http\Requests\AssignWorkOrderRequest;
use App\Http\Requests\RejectWorkOrderRequest;
use App\Http\Requests\StoreWorkOrderRequest;
use App\Http\Requests\TransitionWorkOrderStatusRequest;
use App\Models\Asset;
use App\Models\User;
use App\Models\WorkOrder;
use App\Support\Notifier;
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
            // Hanya dikirim kalau Supervisor — Teknisi tidak butuh, hemat payload
            'technicians' => $user->hasRole('supervisor')
                ? User::whereHas('role', fn ($q) => $q->where('slug', 'technician'))->get(['id', 'name'])
                : [],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', WorkOrder::class);

        return Inertia::render('WorkOrders/Create', [
            'assets' => Asset::where('status', 'active')->get(['id', 'name', 'category']),
        ]);
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

        Notifier::notify(
            $technician,
            'wo_assigned',
            "Anda ditugaskan work order untuk aset \"{$workOrder->asset->name}\".",
            $workOrder->id,
        );

        return back()->with('success', 'Work order berhasil ditugaskan.');
    }

    public function transition(
        TransitionWorkOrderStatusRequest $request,
        WorkOrder $workOrder,
        TransitionWorkOrderStatusAction $action
    ): RedirectResponse {
        $this->authorize('transition', $workOrder);

        $action->execute(
            $workOrder,
            $request->validated('status'),
            auth()->id(),
            $request->validated('note'),
            $request->validated('parts', []),
        );

        return back()->with('success', 'Status work order berhasil diperbarui.');
    }

    public function approve(WorkOrder $workOrder, ApproveWorkOrderAction $action): RedirectResponse
    {
        $this->authorize('approve', WorkOrder::class);

        $action->execute($workOrder, auth()->id());

        return back()->with('success', 'Work order disetujui dan ditutup.');
    }

    public function reject(
        RejectWorkOrderRequest $request,
        WorkOrder $workOrder,
        RejectWorkOrderAction $action
    ): RedirectResponse {
        $action->execute($workOrder, auth()->id(), $request->validated('rejection_note'));

        return back()->with('success', 'Work order dikembalikan ke teknisi untuk revisi.');
    }
}
