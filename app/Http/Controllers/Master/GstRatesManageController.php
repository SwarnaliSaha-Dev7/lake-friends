<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\GstRates;
use Illuminate\Http\Request;

class GstRatesManageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $page_title = 'Manage GST Rates';
        $title      = 'GST Rates List';

        $user       = auth()->user();
        $club_id    = $user->club_id;

        $gstList    = GstRates::where('club_id', $club_id)
                             ->latest()
                              ->get();

        return view('master_manage.gst_rates.list', compact('gstList','page_title','title'));
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
        $page_title = 'Edit GST Rates';
        $title      = 'Edit GST Rates';

        $user       = auth()->user();
        $club_id    = $user->club_id;

        $gst        = GstRates::where('club_id', $club_id)
                              ->where('id', $id)
                              ->firstOrFail();

        return view('master_manage.gst_rates.edit', compact('gst', 'page_title', 'title'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user    = auth()->user();
        $club_id = $user->club_id;

        $gst     = GstRates::where('club_id', $club_id)
                             ->where('id', $id)
                             ->firstOrFail();

        $data = $request->validate([
            'gst_percentage' => 'required|numeric'
        ]);

        $gst->update(['gst_percentage' => $request->gst_percentage]);

        return redirect()
              ->route('manage-gst-rates.index')
              ->with('success', 'GST Rate updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
