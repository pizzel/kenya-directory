<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:subscribers,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'This email is already subscribed or invalid.'], 422);
        }

        Subscriber::create(['email' => $request->email]);

        return response()->json(['message' => 'Welcome to the club! Check your inbox soon.']);
    }
}