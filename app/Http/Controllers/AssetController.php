<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAssetRequest;
use App\Http\Requests\UpdateAssetConditionRequest;
use App\Http\Requests\UpdateAssetRequest;
use App\Models\Asset;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AssetController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Asset::class);

        return Inertia::render('Assets/Index', [
            'assets' => Asset::withTrashed()->latest()->paginate(15),
        ]);
    }

    // Alias untuk route Supervisor — sama datanya, view Vue beda nanti
    public function supervisorIndex(): Response
    {
        return $this->index();
    }

    // Alias untuk route Teknisi — read-only
    public function technicianIndex(): Response
    {
        return $this->index();
    }

    public function create(): Response
    {
        $this->authorize('create', Asset::class);

        return Inertia::render('Assets/Create');
    }

    public function store(StoreAssetRequest $request): RedirectResponse
    {
        Asset::create($request->validated());

        return redirect()->route('admin.assets.index')->with('success', 'Aset berhasil ditambahkan.');
    }

    public function edit(Asset $asset): Response
    {
        $this->authorize('update', $asset);

        return Inertia::render('Assets/Edit', ['asset' => $asset]);
    }

    public function update(UpdateAssetRequest $request, Asset $asset): RedirectResponse
    {
        $asset->update($request->validated());

        return redirect()->route('admin.assets.index')->with('success', 'Aset berhasil diperbarui.');
    }

    public function updateCondition(UpdateAssetConditionRequest $request, Asset $asset): RedirectResponse
    {
        $asset->update($request->validated());

        return redirect()->route('supervisor.assets.index')->with('success', 'Kondisi aset berhasil diperbarui.');
    }

    public function destroy(Asset $asset): RedirectResponse
    {
        $this->authorize('delete', $asset);

        $asset->delete(); // soft delete karena trait SoftDeletes

        return redirect()->route('admin.assets.index')->with('success', 'Aset berhasil dinonaktifkan.');
    }
}
