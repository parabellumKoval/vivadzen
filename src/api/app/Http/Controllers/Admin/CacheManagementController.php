<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller;

class CacheManagementController extends Controller
{
    public function index()
    {
        return view(backpack_view('cache_management'));
    }
}
