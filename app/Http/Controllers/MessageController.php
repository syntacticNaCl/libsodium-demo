<?php

namespace App\Http\Controllers;

use App\Crypto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function index()
    {
        return view('message');
    }

    public function encrypt(Request $request)
    {
        $validatedData = $request->validate([
            'message' => 'required',
        ]);
        $secretKey = base64_decode(env('SECRET_KEY'));

        $encrypted = Crypto::encrypt($request->message, $secretKey);

        $user = Auth::user();
        $user->message = $encrypted;
        $user->save();

        return view('message', ['messageEncrypted' => base64_encode($encrypted)]);
    }

    public function decrypt(Request $request)
    {
        $secretKey = base64_decode(env('SECRET_KEY'));

        $user = Auth::user();

        $decrypted = Crypto::decrypt($user->message, $secretKey);

        return view('message', ['message' => $decrypted]);
    }
}
