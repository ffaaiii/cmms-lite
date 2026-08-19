<?php

namespace App\Http\Controllers;

use App\Actions\Maintenance\GenerateWorkOrderFromChecklistAction;
use App\Http\Requests\StoreInspectionChecklistRequest;
use App\Models\Asset;
use App\Models\InspectionChecklist;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class InspectionChecklistController extends Controller
{
    // Halaman review Supervisor — hanya tampilkan yang masih pending_review
    public function index(): Response
    {
        $this->authorize('viewAny', InspectionChecklist::class);

        return Inertia::render('Checklists/Index', [
            'checklists' => InspectionChecklist::with(['asset', 'inspector'])
                ->where('status', 'pending_review')
                ->latest('created_at')
                ->paginate(15),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', InspectionChecklist::class);

        return Inertia::render('Checklists/Create', [
            'assets' => Asset::where('status', 'active')->get(['id', 'name', 'category', 'location']),
        ]);
    }

    public function store(StoreInspectionChecklistRequest $request): RedirectResponse
    {
        $status = $request->validated('condition_found') === 'good' ? 'confirmed' : 'pending_review';

        InspectionChecklist::create([
            ...$request->validated(),
            'inspected_by' => auth()->id(),
            'status' => $status,
        ]);

        return redirect()->route('technician.tasks.index')
            ->with('success', 'Checklist berhasil disimpan.');
    }

    public function confirm(InspectionChecklist $checklist, GenerateWorkOrderFromChecklistAction $action): RedirectResponse
    {
        $this->authorize('review', InspectionChecklist::class);

        $action->confirm($checklist, auth()->id());

        return back()->with('success', 'Usulan dikonfirmasi, work order corrective dibuat.');
    }

    public function dismiss(InspectionChecklist $checklist, GenerateWorkOrderFromChecklistAction $action): RedirectResponse
    {
        $this->authorize('review', InspectionChecklist::class);

        $action->dismiss($checklist, auth()->id());

        return back()->with('success', 'Usulan ditolak.');
    }
}
