<?php

namespace App\Http\Controllers;

use App\Models\MembershipDurationType;
use Illuminate\Http\Request;

class ClubMemberController extends Controller
{
    public function list()
    {
        $title = 'Club Member list';
        $page_title = 'Manage Club Member';

        $membershipDurationTypeList = MembershipDurationType::all();
        return view('club_member.list', compact('title','page_title','membershipDurationTypeList'));
    }
}
