<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\FoodCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LiquorCategoryManageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $page_title     = 'Manage Liquor Categories';
        $title          = 'Liquor Categories List';

        $user           = auth()->user();
        $club_id        = $user->club_id;

        $liquorCatList  = FoodCategory::where('club_id', $club_id)
                                      ->where('item_type', 'liquor')
                                      ->latest()
                                      ->get();

        return view('master_manage.liquor_categories.list', compact('liquorCatList','page_title','title'));

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $page_title = 'Liquor Categories Add';
        $title      = 'Liquor Categories Add';

        $user       = auth()->user();
        $club_id    = $user->club_id;

        return view('master_manage.liquor_categories.create', compact('title','page_title'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user      = auth()->user();
        $club_id   = $user->club_id;

        $data      = $request->validate([
            'name' => ['required','string','max:255',
                           Rule::unique('food_categories')
                               ->where(function($query) use($club_id){
                                     return $query->where('club_id', $club_id)
                                                  ->where('item_type','liquor')
                                                  ->whereNull('deleted_at');
                                }),
                      ],

        ]);

        $store = FoodCategory::create([
            'name'      => $request->name,
            'club_id'   => $club_id,
            'item_type' => 'liquor'
        ]);

        return redirect()
             ->route('manage-liquor-categories.index')
             ->with('success', 'Liquor Category added successfully!');
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
        $page_title = 'Edit Liquor Category';
        $title      = 'Edit Liquor Category';

        $user       = auth()->user();
        $club_id    = $user->club_id;

        $liquorCats = FoodCategory::where('club_id', $club_id)
                                  ->where('item_type','liquor')
                                  ->where('id', $id)
                                  ->firstOrFail();

        return view('master_manage.liquor_categories.edit', compact('liquorCats', 'page_title', 'title'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user       = auth()->user();
        $club_id    = $user->club_id;

        $liquorCats = FoodCategory::where('club_id', $club_id)
                                  ->where('item_type','liquor')
                                  ->where('id', $id)
                                  ->firstOrFail();

        $data       = $request->validate([
            'name' => ['required','string','max:255',
                           Rule::unique('food_categories')
                               ->ignore($id)
                               ->where(function($query) use($club_id){
                                     return $query->where('club_id', $club_id)
                                                  ->where('item_type','liquor')
                                                  ->whereNull('deleted_at');
                                }),
                      ],
        ]);

        $liquorCats->update([
            'name' => $request->name,
        ]);

        return redirect()
              ->route('manage-liquor-categories.index')
              ->with('success', 'Liquor Category updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user    = auth()->user();
        $club_id = $user->club_id;

        $liquorCats = FoodCategory::where('club_id', $club_id)
                                  ->where('item_type','liquor')
                                  ->where('id', $id)
                                  ->firstOrFail();

        $liquorCats->delete();

        return redirect()
             ->route('manage-liquor-categories.index')
             ->with('success', 'Liquor Category deleted successfully!');
    }
}
