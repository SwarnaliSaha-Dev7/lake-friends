<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Card;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CardsManageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $page_title = 'Manage Cards';
        $title      = 'Cards List';

        $user       = auth()->user();
        $club_id    = $user->club_id;

        $cardsList  = Card::where('club_id', $club_id)
                         ->latest()
                         ->get();

        return view('master_manage.cards.list', compact('cardsList','page_title','title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $page_title = 'Cards Add';
        $title      = 'Cards Add';

        $user       = auth()->user();
        $club_id    = $user->club_id;

        return view('master_manage.cards.create', compact('page_title','title'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user    = auth()->user();
        $club_id = $user->club_id;

        $data    = $request->validate([
            'card_no' => ['required','string','max:255',
                           Rule::unique('cards')
                             ->where(function($query) use($club_id){
                                return $query->where('club_id', $club_id)
                                             ->whereNull('deleted_at');
                             }),
                         ],

            'status'  => 'required',

        ]);

        $store = Card::create([
            'card_no' => $request->card_no,
            'status'  => $request->status,
            'club_id' => $club_id,
        ]);

        return redirect()
             ->route('manage-cards.index')
             ->with('success', 'Card added successfully!');
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
        $page_title = 'Edit Cards';
        $title      = 'Edit Cards';

        $user       = auth()->user();
        $club_id    = $user->club_id;

        $cards      = Card::where('club_id', $club_id)
                     ->where('id', $id)
                     ->firstOrFail();

        return view('master_manage.cards.edit', compact('cards', 'page_title', 'title'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user       = auth()->user();
        $club_id    = $user->club_id;

        $cards      = Card::where('club_id', $club_id)
                          ->where('id', $id)
                          ->firstOrFail();

        $data       = $request->validate([
            'card_no' => ['required','string','max:255',
                           Rule::unique('cards')
                               ->ignore($id)
                               ->where(function($query) use($club_id){
                                    return $query->where('club_id', $club_id)
                                                 ->whereNull('deleted_at');
                               }),
                         ],
            'status'  => 'required',
        ]);

        $cards->update([
            'card_no' => $request->card_no,
            'status'  => $request->status,
        ]);

        return redirect()
              ->route('manage-cards.index')
              ->with('success', 'Card updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user    = auth()->user();
        $club_id = $user->club_id;

        $cards = Card::where('club_id', $club_id)
                    ->where('id', $id)
                    ->firstOrFail();

        $cards->delete();

        return redirect()
             ->route('manage-cards.index')
             ->with('success', 'Card deleted successfully!');
    }
}
