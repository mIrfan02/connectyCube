<?php

namespace App\Http\Controllers;

use App\Services\ConnectyCubeService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $connectyCube;

    public function __construct(ConnectyCubeService $connectyCube)
    {
        $this->connectyCube = $connectyCube;
    }

    public function createConnectyCubeUser()
    {
        $login = 'john_doe';
        $password = 'password123';
        $email = 'john_doe@example.com';

        $response = $this->connectyCube->createUser($login, $password, $email);

        return response()->json($response);
    }

    public function sendChatMessage(Request $request)
    {
        $recipientId = $request->input('recipient_id');
        $message = $request->input('message');

        $response = $this->connectyCube->sendMessage($recipientId, $message);

        return response()->json($response);
    }

    public function initiateVideoCall(Request $request)
    {
        $opponentIds = $request->input('opponent_ids'); // array of IDs

        $response = $this->connectyCube->initiateVideoCall($opponentIds);

        return response()->json($response);
    }

    public function getChatHistory($dialogId)
    {
        $response = $this->connectyCube->getChatHistory($dialogId);

        return response()->json($response);
    }
}
