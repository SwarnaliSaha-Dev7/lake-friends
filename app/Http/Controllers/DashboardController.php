<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        $page_title = 'Dashboard';
        $title = 'Dashboard';
        return view('dashboard', compact('title','page_title'));
    }
}
