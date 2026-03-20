<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\FineRule;
use App\Models\MembershipPlanType;
use App\Models\MembershipType;
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
                                  ->with('membershipPlanType')
                                  ->latest()
                                  ->get();

        $membershipType = MembershipType::where('club_id', $club_id)->where('name', 'Club Membership')->first();
        $planTypes      = $membershipType
            ? MembershipPlanType::where('membership_type_id', $membershipType->id)->where('is_active', 1)->get()
            : collect();

        // Plans that already have a fine rule
        $takenPlanIds = $fineRulesList->pluck('membership_plan_type_id')->filter()->toArray();

        return view('master_manage.fine_rules.list', compact('fineRulesList', 'planTypes', 'takenPlanIds', 'page_title', 'title'));
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
        $user    = auth()->user();
        $club_id = $user->club_id;

        $request->validate([
            'membership_plan_type_id' => 'nullable|exists:membership_plan_types,id',
            'grace_days'              => 'nullable|integer|min:0',
            'max_fine_cap'            => 'nullable|numeric|min:0',
        ]);

        // Prevent duplicate rule for same plan
        $exists = FineRule::where('club_id', $club_id)
            ->where('rule_type', 'membership_expiry')
            ->where('membership_plan_type_id', $request->membership_plan_type_id ?: null)
            ->exists();

        if ($exists) {
            return redirect()->route('manage-fine-rules.index')
                ->with('error', 'A fine rule for this plan already exists.');
        }

        FineRule::create([
            'club_id'                 => $club_id,
            'membership_plan_type_id' => $request->membership_plan_type_id ?: null,
            'rule_type'               => 'membership_expiry',
            'per_day_fine_amount'     => 0,
            'grace_days'              => $request->grace_days ?? 0,
            'max_fine_cap'            => $request->max_fine_cap ?: null,
        ]);

        return redirect()->route('manage-fine-rules.index')
            ->with('success', 'Fine rule added successfully.');
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

        $membershipType = MembershipType::where('club_id', $club_id)->where('name', 'Club Membership')->first();
        $planTypes      = $membershipType
            ? MembershipPlanType::where('membership_type_id', $membershipType->id)->where('is_active', 1)->get()
            : collect();

        return view('master_manage.fine_rules.edit', compact('fineRules', 'planTypes', 'page_title', 'title'));
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

        $request->validate([
            'grace_days'  => 'nullable|integer|min:0',
            'max_fine_cap'=> 'nullable|numeric|min:0',
        ]);

        $fineRules->update([
            'grace_days'  => $request->grace_days ?? 0,
            'max_fine_cap'=> $request->max_fine_cap ?: null,
        ]);

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
