<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purok;
use App\Models\Street;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurokController extends Controller
{
    /**
     * Display a listing of puroks.
     */
    public function index(): View
    {
        // Auto-sync: fix any users with old purok string but missing purok_id
        $purokMap = Purok::pluck('id', 'name');
        User::whereNotNull('purok')
            ->whereNull('purok_id')
            ->cursor()
            ->each(function ($user) use ($purokMap) {
                if (isset($purokMap[$user->purok])) {
                    $user->update(['purok_id' => $purokMap[$user->purok]]);
                }
            });

        $puroks = Purok::withCount(['residents', 'streets'])->orderBy('name')->paginate(10);

        return view('admin.puroks.index', compact('puroks'));
    }

    /**
     * Show the form for creating a new purok.
     */
    public function create(): View
    {
        $streets = Street::active()->orderBy('name')->get();

        return view('admin.puroks.create', compact('streets'));
    }

    /**
     * Store a newly created purok in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:puroks,name'],
            'description' => ['nullable', 'string', 'max:500'],
            'street_ids' => ['nullable', 'array'],
            'street_ids.*' => ['integer', 'exists:streets,id'],
            'new_street_name' => ['nullable', 'string', 'max:255'],
        ], [
            'name.required' => 'Purok name is required.',
            'name.unique' => 'This purok name already exists.',
        ]);

        $validated['is_active'] = true;

        $purok = Purok::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ]);

        $streetIds = $validated['street_ids'] ?? [];
        if (! empty($validated['new_street_name'])) {
            $street = Street::firstOrCreate(
                ['name' => trim($validated['new_street_name'])],
                ['is_active' => true]
            );
            $streetIds[] = $street->id;
        }

        if (! empty($streetIds)) {
            $purok->streets()->syncWithoutDetaching(array_unique($streetIds));
        }

        return redirect()->route('admin.puroks.index')
            ->with('success', 'Purok created successfully.');
    }

    /**
     * Show the form for editing the specified purok.
     */
    public function edit(Purok $purok): View
    {
        $purok->load('streets');
        $streets = Street::active()->orderBy('name')->get();

        return view('admin.puroks.edit', compact('purok', 'streets'));
    }

    /**
     * Update the specified purok in storage.
     */
    public function update(Request $request, Purok $purok): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:puroks,name,' . $purok->id],
            'description' => ['nullable', 'string', 'max:500'],
            'street_ids' => ['nullable', 'array'],
            'street_ids.*' => ['integer', 'exists:streets,id'],
            'new_street_name' => ['nullable', 'string', 'max:255'],
        ], [
            'name.required' => 'Purok name is required.',
            'name.unique' => 'This purok name already exists.',
        ]);

        $purok->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        $streetIds = $validated['street_ids'] ?? [];
        if (! empty($validated['new_street_name'])) {
            $street = Street::firstOrCreate(
                ['name' => trim($validated['new_street_name'])],
                ['is_active' => true]
            );
            $streetIds[] = $street->id;
        }

        if (! empty($streetIds)) {
            $purok->streets()->syncWithoutDetaching(array_unique($streetIds));
        }

        return redirect()->route('admin.puroks.index')
            ->with('success', 'Purok updated successfully.');
    }

    /**
     * Toggle the active status of the specified purok.
     */
    public function toggleStatus(Purok $purok): RedirectResponse
    {
        $purok->update([
            'is_active' => ! $purok->is_active,
        ]);

        $status = $purok->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Purok {$purok->name} has been {$status}.");
    }
}
