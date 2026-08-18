<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\MiscCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MiscCategoryManageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $page_title    = 'Manage Misc Categories';
        $title         = 'Misc Categories List';

        $user          = auth()->user();
        $club_id       = $user->club_id;

        $miscCatList   = MiscCategory::where('club_id', $club_id)
                                      ->latest()
                                      ->get();

        return view('master_manage.misc_categories.list', compact('miscCatList', 'page_title', 'title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $page_title = 'Misc Categories Add';
        $title      = 'Misc Categories Add';

        return view('master_manage.misc_categories.create', compact('title', 'page_title'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user      = auth()->user();
        $club_id   = $user->club_id;

        $data      = $request->validate([
            'name' => ['required', 'string', 'max:255',
                           Rule::unique('misc_categories')
                               ->where(function ($query) use ($club_id) {
                                     return $query->where('club_id', $club_id)
                                                  ->whereNull('deleted_at');
                                }),
                      ],

        ]);

        MiscCategory::create([
            'name'    => ucwords($request->name),
            'club_id' => $club_id,
        ]);

        return redirect()
             ->route('manage-misc-categories.index')
             ->with('success', 'Misc Category added successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $page_title  = 'Edit Misc Category';
        $title       = 'Edit Misc Category';

        $user        = auth()->user();
        $club_id     = $user->club_id;

        $miscCats    = MiscCategory::where('club_id', $club_id)
                                  ->where('id', $id)
                                  ->firstOrFail();

        return view('master_manage.misc_categories.edit', compact('miscCats', 'page_title', 'title'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user        = auth()->user();
        $club_id     = $user->club_id;

        $miscCats    = MiscCategory::where('club_id', $club_id)
                                  ->where('id', $id)
                                  ->firstOrFail();

        $data        = $request->validate([
            'name' => ['required', 'string', 'max:255',
                           Rule::unique('misc_categories')
                               ->ignore($id)
                               ->where(function ($query) use ($club_id) {
                                     return $query->where('club_id', $club_id)
                                                  ->whereNull('deleted_at');
                                }),
                      ],
        ]);

        $miscCats->update([
            'name' => ucwords($request->name),
        ]);

        return redirect()
              ->route('manage-misc-categories.index')
              ->with('success', 'Misc Category updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user        = auth()->user();
        $club_id     = $user->club_id;

        $miscCats    = MiscCategory::where('club_id', $club_id)
                                  ->where('id', $id)
                                  ->firstOrFail();

        $miscCats->delete();

        return redirect()
             ->route('manage-misc-categories.index')
             ->with('success', 'Misc Category deleted successfully!');
    }
}
