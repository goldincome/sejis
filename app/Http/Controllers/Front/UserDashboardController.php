<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;

class UserDashboardController extends Controller
{
    public function index()
    {
        
        return view('front.dashboard');
    }
}
