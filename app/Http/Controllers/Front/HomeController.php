<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Services\UtilityService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        return view('front.home');
    }


     public function getTimeSlots(Request $request)
    {   
        //dd(getTimeSlots($request));
        return app(UtilityService::class)->getTimeSlots($request);
    }
}
