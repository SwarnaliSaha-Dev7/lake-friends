<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\MembershipPlanType as MembershipDurationType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MembershipDurationTypesManageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $page_title = 'Manage Membership Duration Types';
        $title      = 'Membership Duration Types List';

        $user       = auth()->user();
        $club_id    = $user->club_id;

        $membership_duration_types = MembershipDurationType::where('club_id', $club_id)
                                                           ->latest()
                                                           ->get();

        return view('master_manage.membership_duration_types.list', compact('membership_duration_types','title','page_title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $page_title = 'Membership Duration Types Add';
        $title      = 'Membership Duration Types Add';

        $user       = auth()->user();
        $club_id    = $user->club_id;

        return view('master_manage.membership_duration_types.create', compact('title','page_title'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                         => ['required', 'string', 'max:255',
                                              Rule::unique('membership_plan_types')
                                                  ->where(function ($query) use ($request) {
                                                      return $query->where('club_id', $request->user()->club_id)
                                                                  ->whereNull('deleted_at');
                                                  }),
                                              ],
            'duration_months'              => 'required_without:is_lifetime|nullable|integer|min:1|max:12',
            'is_lifetime'                  => 'nullable|boolean',
            'is_minimum_spend_applicable'  => 'nullable|boolean',
        ]);

        $user = auth()->user();
        $club_id = $user->club_id;

        $store = MembershipDurationType::create([
            'name'                        => $request->name,
            'duration_months'             => $request->has('is_lifetime') ? null : $request->duration_months,
            'is_lifetime'                 => $request->has('is_lifetime') ? 1 : 0,
            'is_minimum_spend_applicable' => $request->has('is_minimum_spend_applicable') ? 1 : 0,
            'club_id'                     => $club_id,
        ]);

        return redirect()
                ->route('manage-membership-duration-types.index')
                ->with('success', 'Membership Duration Type added successfully!');
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
        $page_title = 'Edit Membership Duration Types';
        $title      = 'Edit Membership Duration Types';

        $user = auth()->user();
        $club_id = $user->club_id;

        $membership_duration_types = MembershipDurationType::where('club_id', $club_id)
                        ->where('id', $id)
                        ->firstOrFail();

        return view('master_manage.membership_duration_types.edit', compact('membership_duration_types','page_title','title'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user    = auth()->user();
        $club_id = $user->club_id;

        $membership_duration_types = MembershipDurationType::where('club_id', $club_id)
                                                            ->where('id', $id)
                                                            ->firstOrFail();

        $data = $request->validate([
            'name'                        => ['required', 'string', 'max:255',
                                             Rule::unique('membership_plan_types')
                                             ->ignore($id)
                                             ->where(function ($query) use ($request) {
                                                 return $query->where('club_id', $request->user()->club_id)
                                                             ->whereNull('deleted_at');
                                                 }),
                                             ],
            'duration_months'             => 'required_without:is_lifetime|nullable|integer|min:1|max:12',
            'is_lifetime'                 => 'nullable|boolean',
            'is_minimum_spend_applicable' => 'nullable|boolean',
        ]);

        $membership_duration_types->update([
            'name'                        => $request->name,
            'duration_months'             => $request->has('is_lifetime') ? null : $request->duration_months,
            'is_lifetime'                 => $request->has('is_lifetime') ? 1 : 0,
            'is_minimum_spend_applicable' => $request->has('is_minimum_spend_applicable') ? 1 : 0,
        ]);

        return redirect()
                ->route('manage-membership-duration-types.index')
                ->with('success', 'Membership Duration Type updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user    = auth()->user();
        $club_id = $user->club_id;

        $membership_duration_types = MembershipDurationType::where('club_id', $club_id)
                                                            ->where('id', $id)
                                                            ->firstOrFail();

        $membership_duration_types->delete();

        return redirect()
                ->route('manage-membership-duration-types.index')
                ->with('success', 'Membership Duration Type deleted successfully!');

    }
}
