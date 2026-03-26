<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Locker;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LockerManageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $page_title = 'Manage Lockers';
        $title      = 'Lockers List';

        $user       = auth()->user();
        $club_id    = $user->club_id;

        $lockersList  = Locker::where('club_id', $club_id)
                              ->latest()
                              ->get();

        return view('master_manage.lockers.list', compact('lockersList','page_title','title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
   {
        $page_title = 'Lockers Add';
        $title      = 'Lockers Add';

        $user       = auth()->user();
        $club_id    = $user->club_id;

        return view('master_manage.lockers.create', compact('page_title','title'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user    = auth()->user();
        $club_id = $user->club_id;

        $data    = $request->validate([
            'locker_number' => ['required','string','max:255',
                           Rule::unique('lockers')
                               ->where(function($query) use($club_id){
                                    return $query->where('club_id', $club_id)
                                                 ->whereNull('deleted_at');
                             }),
                         ],

        ]);


        //dd($club_id);
        $store = Locker::create([
            'locker_number' => $request->locker_number,
            'club_id' => $club_id,
            'is_active' => "1",
        ]);

        return redirect()
             ->route('manage-lockers.index')
             ->with('success', 'Locker added successfully!');
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
        $page_title = 'Edit Lockers';
        $title      = 'Edit Lockers';

        $user       = auth()->user();
        $club_id    = $user->club_id;

        $lockers    = Locker::where('club_id', $club_id)
                            ->where('id', $id)
                            ->firstOrFail();

        return view('master_manage.lockers.edit', compact('lockers', 'page_title', 'title'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user       = auth()->user();
        $club_id    = $user->club_id;

        $lockers    = Locker::where('club_id', $club_id)
                            ->where('id', $id)
                            ->firstOrFail();

        $data       = $request->validate([
            'locker_number' => ['required','string','max:255',
                           Rule::unique('lockers')
                               ->ignore($id)
                               ->where(function($query) use($club_id){
                                    return $query->where('club_id', $club_id)
                                                 ->whereNull('deleted_at');
                               }),
                         ],
        ]);

        $lockers->update([
            'locker_number' => $request->locker_number,
            'is_active'     => $request->is_active,
        ]);

        return redirect()
              ->route('manage-lockers.index')
              ->with('success', 'Locker updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user    = auth()->user();
        $club_id = $user->club_id;

        $lockers = Locker::where('club_id', $club_id)
                         ->where('id', $id)
                         ->firstOrFail();

        if( $lockers->status == 'occupied'){
            return redirect()->route('manage-lockers.index')->with('error', 'This locker is already assigned to a member and cannot be deleted.');
        }

        $lockers->delete();

        return redirect()
             ->route('manage-lockers.index')
             ->with('success', 'Locker deleted successfully!');
    }
}
