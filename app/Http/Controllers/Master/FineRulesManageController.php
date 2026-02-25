<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\FineRule;
use Illuminate\Http\Request;

class FineRulesManageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $page_title     = 'Manage Fine Rules';
        $title          = 'Fine Rules';

        $user           = auth()->user();
        $club_id        = $user->club_id;

        $fineRulesList  = FineRule::where('club_id', $club_id)
                                  ->latest()
                                  ->get();

        return view('master_manage.fine_rules.list', compact('fineRulesList','page_title','title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
        $page_title = 'Edit Fine Rules';
        $title      = 'Edit Fine Rules';

        $user       = auth()->user();
        $club_id    = $user->club_id;

        $fineRules  = FineRule::where('club_id', $club_id)
                              ->where('id', $id)
                              ->firstOrFail();

        return view('master_manage.fine_rules.edit', compact('fineRules', 'page_title', 'title'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user    = auth()->user();
        $club_id = $user->club_id;

        $fineRules     = FineRule::where('club_id', $club_id)
                                 ->where('id', $id)
                                 ->firstOrFail();

        $data = $request->validate([
            'per_day_fine_amount' => 'required|numeric|min:0|max:9999999999|decimal:0,2'
        ]);

        $fineRules->update(['per_day_fine_amount' => $request->per_day_fine_amount]);

        return redirect()
              ->route('manage-fine-rules.index')
              ->with('success', 'Fine Rules updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
