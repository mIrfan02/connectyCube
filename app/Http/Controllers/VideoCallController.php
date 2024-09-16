<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class VideoCallController extends Controller
{
    public function index()
    {
        // Ensure the user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login'); // Redirect to login if not authenticated
        }

        // Get all users except the currently authenticated user
        $users = User::where('id', '!=', auth()->id())->get();

        return view('videocall', compact('users'));
    }
}
