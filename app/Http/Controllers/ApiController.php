<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function authStatus()
{
    return response()->json(['loggedIn' => Auth::check()]);
}
}
