<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\Member;
use App\Models\MemberCardMapping;
use App\Models\MembershipPurchaseHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $cardMapping = MemberCardMapping::where('card_id', $cards->id)->first();
        $membershipExpiry = null;
        $isMembershipExpired = false;
        if ($cardMapping) {
            $member = Member::find($cardMapping->member_id);
            if ($member) {
                $latestActive = MembershipPurchaseHistory::where('member_id', $member->id)
                    ->where('status', 'active')
                    ->latest('expiry_date')
                    ->first();

                $membershipExpiry = $latestActive?->expiry_date;
                if ($membershipExpiry) {
                    $isMembershipExpired = Carbon::parse($membershipExpiry)->isPast();
                }
            }
        }

        return view('master_manage.cards.edit', compact(
            'cards',
            'page_title',
            'title',
            'cardMapping',
            'membershipExpiry',
            'isMembershipExpired'
        ));
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

    public function delinkCard(Request $request)
    {
        try {
            $request->validate([
                'card_id' => ['required', 'integer'],
            ]);

            $clubId = club_id();

            $card = Card::where('club_id', $clubId)->find($request->card_id);
            if (!$card) {
                return response()->json([
                    'statusCode' => 404,
                    'message' => 'Card not found'
                ]);
            }

            $cardMapping = MemberCardMapping::where('card_id', $card->id)->first();
            if (!$cardMapping) {
                return response()->json([
                    'statusCode' => 404,
                    'message' => 'Card mapping not found'
                ]);
            }

            $member = Member::where('club_id', $clubId)->find($cardMapping->member_id);
            if (!$member) {
                return response()->json([
                    'statusCode' => 404,
                    'message' => 'Member not found'
                ]);
            }

            DB::beginTransaction();

            $cardMapping->delete();
            $card->update(['is_assigned' => 0]);

            DB::commit();

            return response()->json([
                'statusCode' => 200,
                'message' => 'Card delinked successfully'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'statusCode' => 500,
                'message' => $th->getMessage()
            ]);
        }
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

        if( $cards->is_assigned == 1){
            return redirect()->route('manage-cards.index')->with('error', 'This card is already assigned to a member and cannot be deleted.');
        }

        $cards->delete();

        return redirect()
             ->route('manage-cards.index')
             ->with('success', 'Card deleted successfully!');
    }
}
