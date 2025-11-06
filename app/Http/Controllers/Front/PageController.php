<?php 
namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PageController extends Controller
{

    public function aboutUs()
    {
        return view('front.pages.aboutus');
    }

    public function contactUs()
    {
        return view('front.pages.contactus');
    }

    public function processContactUs(Request $request)
    {
       // dd($request->all());
        return to_route('contact-us')->with('success', 'Message Sent Successful');
    }

    public function privacyPolicy()
    {
        return view('front.pages.privacy-policy');
    }

    public function termsOfService()
    {
        return view('front.pages.terms-of-service');
    }

    public function bookingPolicy()
    {
        return view('front.pages.booking-policy');
    }

}