<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\FoodCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FoodCategoryManageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $page_title   = 'Manage Food Categories';
        $title        = 'Food Categories List';

        $user         = auth()->user();
        $club_id      = $user->club_id;

        $foodCatList  = FoodCategory::where('club_id', $club_id)
                                    ->where('item_type', 'food')
                                    ->latest()
                                    ->get();

        return view('master_manage.food_categories.list', compact('foodCatList','page_title','title'));

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $page_title = 'Food Categories Add';
        $title      = 'Food Categories Add';

        $user       = auth()->user();
        $club_id    = $user->club_id;

        return view('master_manage.food_categories.create', compact('title','page_title'));
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
                                                  ->where('item_type','food')
                                                  ->whereNull('deleted_at');
                                }),
                      ],

        ]);

        $store = FoodCategory::create([
            'name'      => $request->name,
            'club_id'   => $club_id,
            'item_type' => 'food'
        ]);

        return redirect()
             ->route('manage-food-categories.index')
             ->with('status', 'Food Category added successfully!');
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
        $page_title = 'Edit Food Category';
        $title      = 'Edit Food Category';

        $user       = auth()->user();
        $club_id    = $user->club_id;

        $foodCats   = FoodCategory::where('club_id', $club_id)
                                  ->where('item_type','food')
                                  ->where('id', $id)
                                  ->firstOrFail();

        return view('master_manage.food_categories.edit', compact('foodCats', 'page_title', 'title'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user       = auth()->user();
        $club_id    = $user->club_id;

        $foodCats   = FoodCategory::where('club_id', $club_id)
                                  ->where('item_type','food')
                                  ->where('id', $id)
                                  ->firstOrFail();
        
        $data       = $request->validate([
            'name' => ['required','string','max:255',
                           Rule::unique('food_categories')
                               ->ignore($id)
                               ->where(function($query) use($club_id){
                                     return $query->where('club_id', $club_id)
                                                  ->where('item_type','food')
                                                  ->whereNull('deleted_at');
                                }),
                      ],
        ]);

        $foodCats->update([
            'name' => $request->name,
        ]);

        return redirect()
              ->route('manage-food-categories.index')
              ->with('success', 'Food Category updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user    = auth()->user();
        $club_id = $user->club_id;

        $foodCats = FoodCategory::where('club_id', $club_id)
                                ->where('item_type','food')
                                ->where('id', $id)
                                ->firstOrFail();

        $foodCats->delete();

        return redirect()
             ->route('manage-food-categories.index')
             ->with('success', 'Food Category deleted successfully!');
    }
}
