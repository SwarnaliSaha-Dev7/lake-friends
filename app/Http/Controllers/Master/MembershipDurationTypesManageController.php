<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\MembershipDurationType;
use Illuminate\Http\Request;

class MembershipDurationTypesManageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();

        $club_id = $user->club_id;

        $membership_duration_types = MembershipDurationType::where('club_id', $club_id)
                                        ->latest()
                                        ->get();

        return view('master_manage.membership_duration_types.list', compact('membership_duration_types'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();

        $club_id = $user->club_id;

        return view('master_manage.membership_duration_types.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'              => 'required | string | max:255',
            'duration_months'   => 'required_without:is_lifetime|nullable|integer|min:1|max:12',
            'is_lifetime'       => 'nullable | boolean'
        ]);

        $user = auth()->user();

        $club_id = $user->club_id;

        $store = MembershipDurationType::create([

            'name'              => $request->name,
            'duration_months'   => $request->has('is_lifetime') ? null : $request->duration_months,
            'is_lifetime'       => $request->has('is_lifetime') ? 1 : 0,
            'club_id'           => $club_id,
        ]);

        return redirect()->route('manage-membership-duration-types.index')->with('success', 'Membership Duration Type created successfully!');
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
        $user = auth()->user();

        $club_id = $user->club_id;

        $membership_duration_types = MembershipDurationType::where('club_id', $club_id)
                        ->where('id', $id)
                        ->firstOrFail();

        return view('master_manage.membership_duration_types.edit', compact('membership_duration_types'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name'              => 'required | string | max:255',
            'duration_months'   => 'required_without:is_lifetime|nullable|integer|min:1|max:12',
            'is_lifetime'       => 'nullable | boolean'
        ]);

        $user = auth()->user();

        $club_id = $user->club_id;

        $membership_duration_types = MembershipDurationType::where('club_id', $club_id)
                                            ->where('id', $id)
                                            ->firstOrFail();

        $membership_duration_types->update([
            'name'              => $request->name,
            'duration_months'   => $request->has('is_lifetime') ? null : $request->duration_months,
            'is_lifetime'       => $request->has('is_lifetime') ? 1 : 0,
        ]);

        return redirect()->route('manage-membership-duration-types.index')->with('success', 'Membership Duration Type updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = auth()->user();

        $club_id = $user->club_id;

        $membership_duration_types = MembershipDurationType::where('club_id', $club_id)
                                            ->where('id', $id)
                                            ->firstOrFail();

        $membership_duration_types->delete();

        return redirect()->route('manage-membership-duration-types.index')->with('success', 'Membership Duration Type deleted successfully!');

    }
}
