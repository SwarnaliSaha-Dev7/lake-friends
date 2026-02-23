<?php

namespace App\Http\Controllers;

use App\Models\GstRate;
use App\Models\MembershipDurationType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClubMemberController extends Controller
{
    public function list()
    {
        $title = 'Club Member list';
        $page_title = 'Manage Club Member';

        $membershipDurationTypeList = MembershipDurationType::all();

        $gstPercentage = GstRate::where('club_id', Auth::user()->club_id)
                                        ->value('gst_percentage') ?? 0;

        return view('club_member.list', compact(
                                                'title',
                                                'page_title',
                                                'membershipDurationTypeList',
                                                'gstPercentage'));
    }

    public function store()
    {
        return 748237;
    }
}
