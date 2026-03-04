<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\MinimumSpendRule;
use Illuminate\Http\Request;

class MinimumSpendRuleManageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $page_title         = 'Manage Minimum Spend Rules';
        $title              = 'Minimum Spend Rules';

        $user               = auth()->user();
        $club_id            = $user->club_id;

        $minSpendRuleList   = MinimumSpendRule::where('club_id', $club_id)
                                             ->latest()
                                             ->get();

        return view('master_manage.minimum_spend_rules.list', compact('minSpendRuleList','page_title','title'));
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
        $page_title     = 'Edit Minimum Spend Rules';
        $title          = 'Edit Minimum Spend Rules';

        $user           = auth()->user();
        $club_id        = $user->club_id;

        $minSpendRules  = MinimumSpendRule::where('club_id', $club_id)
                                          ->where('id', $id)
                                          ->firstOrFail();

        return view('master_manage.minimum_spend_rules.edit', compact('minSpendRules', 'page_title', 'title'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user           = auth()->user();
        $club_id        = $user->club_id;

        $minSpendRules  = MinimumSpendRule::where('club_id', $club_id)
                                          ->where('id', $id)
                                          ->firstOrFail();

        $data = $request->validate([
            'minimum_amount' => 'required|numeric|min:0|max:99999999|decimal:0,2'
        ]);

        $minSpendRules->update(['minimum_amount' => $request->minimum_amount]);

        return redirect()
              ->route('manage-minimum-spend-rules.index')
              ->with('success', 'Minimum Spend Amount updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
