<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\CardType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CardTypesManageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $page_title = 'Manage Card Types';
        $title      = 'Card Types List';

        $user       = auth()->user();
        $club_id    = $user->club_id;

        $cardTypesList = CardType::where('club_id', $club_id)
                                 ->latest()
                                 ->get();

        return view('master_manage.card_types.list', compact('cardTypesList','title','page_title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $page_title = 'Card Types Add';
        $title      = 'Card Types Add';

        $user       = auth()->user();
        $club_id    = $user->club_id;

        return view('master_manage.card_types.create', compact('title','page_title'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255',
                         Rule::unique('card_types')
                            ->where(function ($query) use ($request) {
                                return $query->where('club_id', $request->user()->club_id)
                                             ->whereNull('deleted_at');
                                        }),
                                    ],
        ]);

        $user = auth()->user();
        $club_id = $user->club_id;

        $store = CardType::create([

            'name'              => $request->name,
            'club_id'           => $club_id,
        ]);

        return redirect()
                ->route('manage-card-types.index')
                ->with('success', 'Card Type added successfully!');
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
        $page_title = 'Edit Card Types';
        $title      = 'Edit Card Types';

        $user = auth()->user();
        $club_id = $user->club_id;

        $cardTypes = CardType::where('club_id', $club_id)
                        ->where('id', $id)
                        ->firstOrFail();

        return view('master_manage.card_types.edit', compact('cardTypes','page_title','title'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user    = auth()->user();
        $club_id = $user->club_id;

        $cardTypes = CardType::where('club_id', $club_id)
                             ->where('id', $id)
                             ->firstOrFail();

        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255',
                        Rule::unique('card_types')
                            ->ignore($id)
                            ->where(function ($query) use ($request) {
                                return $query->where('club_id', $request->user()->club_id)
                                             ->whereNull('deleted_at');
                                }),
                        ],
        ]);

        $cardTypes->update([
            'name'  => $request->name,
        ]);

        return redirect()
                ->route('manage-card-types.index')
                ->with('success', 'Card Type updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user    = auth()->user();
        $club_id = $user->club_id;

        $card_types = CardType::where('club_id', $club_id)
                              ->where('id', $id)
                              ->firstOrFail();

        $card_types->delete();

        return redirect()
                ->route('manage-card-types.index')
                ->with('success', 'Card Type deleted successfully!');

    }
}
