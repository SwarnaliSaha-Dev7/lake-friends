<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\FoodCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BeverageCategoryManageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $page_title       = 'Manage Beverage Categories';
        $title            = 'Beverage Categories List';

        $user             = auth()->user();
        $club_id          = $user->club_id;

        $beverageCatList  = FoodCategory::where('club_id', $club_id)
                                      ->where('item_type', 'beverage')
                                      ->latest()
                                      ->get();

        return view('master_manage.beverage_categories.list', compact('beverageCatList','page_title','title'));

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $page_title = 'Beverage Categories Add';
        $title      = 'Beverage Categories Add';

        $user       = auth()->user();
        $club_id    = $user->club_id;

        return view('master_manage.beverage_categories.create', compact('title','page_title'));
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
                                                  ->where('item_type','beverage')
                                                  ->whereNull('deleted_at');
                                }),
                      ],

        ]);

        $store = FoodCategory::create([
            'name'      => ucwords($request->name),
            'club_id'   => $club_id,
            'item_type' => 'beverage'
        ]);

        return redirect()
             ->route('manage-beverage-categories.index')
             ->with('success', 'Beverage Category added successfully!');
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
        $page_title    = 'Edit Beverage Category';
        $title         = 'Edit Beverage Category';

        $user          = auth()->user();
        $club_id       = $user->club_id;

        $beverageCats  = FoodCategory::where('club_id', $club_id)
                                  ->where('item_type','beverage')
                                  ->where('id', $id)
                                  ->firstOrFail();

        return view('master_manage.beverage_categories.edit', compact('beverageCats', 'page_title', 'title'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user          = auth()->user();
        $club_id       = $user->club_id;

        $beverageCats  = FoodCategory::where('club_id', $club_id)
                                  ->where('item_type','beverage')
                                  ->where('id', $id)
                                  ->firstOrFail();

        $data          = $request->validate([
            'name' => ['required','string','max:255',
                           Rule::unique('food_categories')
                               ->ignore($id)
                               ->where(function($query) use($club_id){
                                     return $query->where('club_id', $club_id)
                                                  ->where('item_type','beverage')
                                                  ->whereNull('deleted_at');
                                }),
                      ],
        ]);

        $beverageCats->update([
            'name' => ucwords($request->name),
        ]);

        return redirect()
              ->route('manage-beverage-categories.index')
              ->with('success', 'Beverage Category updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user          = auth()->user();
        $club_id       = $user->club_id;

        $beverageCats  = FoodCategory::where('club_id', $club_id)
                                  ->where('item_type','beverage')
                                  ->where('id', $id)
                                  ->firstOrFail();

        $beverageCats->delete();

        return redirect()
             ->route('manage-beverage-categories.index')
             ->with('success', 'Beverage Category deleted successfully!');
    }
}
