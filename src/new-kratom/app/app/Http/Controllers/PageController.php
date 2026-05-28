<?php

namespace App\Http\Controllers;

class PageController extends Controller
{
    public function delivery()     { return view('pages.static.delivery'); }
    public function lab()          { return view('pages.static.lab'); }
    public function licence()      { return view('pages.static.licence'); }
    public function stores()       { return view('pages.static.stores'); }
    public function returns()      { return view('pages.static.returns'); }
    public function contact()      { return view('pages.static.contact'); }
    public function support()      { return view('pages.static.support'); }
    public function about()        { return view('pages.static.about'); }
    public function terms()        { return view('pages.static.terms'); }
    public function privacy()      { return view('pages.static.privacy'); }
    public function cookies()      { return view('pages.static.cookies'); }
    public function subscription() { return view('pages.static.subscription'); }
    public function guide()        { return view('pages.static.guide'); }
}
