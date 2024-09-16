<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

class ConnectyCubeService
{
    protected $client;
    protected $baseUrl = 'https://api.connectycube.com';
    protected $sessionToken;

    public function __construct()
    {
        $this->client = new Client();
        $this->sessionToken = $this->getSessionToken();
    }

    /**
     * Authenticate and retrieve session token.
     */
    public function authenticate()
    {
        // Check if session token exists in cache
        if (Cache::has('connectycube_token')) {
            return Cache::get('connectycube_token');
        }

        $url = $this->baseUrl . '/session';
        $data = [
            'application_id' => env('CONNECTYCUBE_APP_ID'),
            'auth_key'       => env('CONNECTYCUBE_AUTH_KEY'),
            'auth_secret'    => env('CONNECTYCUBE_AUTH_SECRET'),
            'nonce'          => rand(),
            'timestamp'      => time(),
            'user'           => [
                'login'    => env('CONNECTYCUBE_LOGIN'),
                'password' => env('CONNECTYCUBE_PASSWORD')
            ]
        ];

        $response = $this->client->post($url, [
            'json' => $data
        ]);

        $result = json_decode($response->getBody()->getContents());

        // Store token in cache with expiration
        Cache::put('connectycube_token', $result->session->token, now()->addMinutes(30));

        return $result->session->token;
    }

    /**
     * Get the current session token.
     */
    public function getSessionToken()
    {
        return $this->sessionToken ?: $this->authenticate();
    }

    /**
     * Create a new user in ConnectyCube.
     */
    public function createUser($login, $password, $email)
    {
        $url = $this->baseUrl . '/users';

        $data = [
            'user' => [
                'login'    => $login,
                'password' => $password,
                'email'    => $email
            ]
        ];

        $response = $this->client->post($url, [
            'json' => $data,
            'headers' => [
                'CB-Token' => $this->getSessionToken()
            ]
        ]);

        return json_decode($response->getBody()->getContents());
    }

    /**
     * Send a chat message.
     */
    public function sendMessage($recipientId, $message)
    {
        $url = $this->baseUrl . '/chat/Message';

        $data = [
            'message' => [
                'recipient_id' => $recipientId,
                'message'      => $message,
                'send_to_chat' => 1,
                'markable'     => 1
            ]
        ];

        $response = $this->client->post($url, [
            'json' => $data,
            'headers' => [
                'CB-Token' => $this->getSessionToken()
            ]
        ]);

        return json_decode($response->getBody()->getContents());
    }

    /**
     * Initiate a video call.
     */
    public function initiateVideoCall($opponentIds)
    {
        $url = $this->baseUrl . '/chat/Call';

        $data = [
            'call' => [
                'opponents_ids' => implode(',', $opponentIds),
                'type'          => 1, // 1 for video, 2 for audio
                'session_id'    => uniqid()
            ]
        ];

        $response = $this->client->post($url, [
            'json' => $data,
            'headers' => [
                'CB-Token' => $this->getSessionToken()
            ]
        ]);

        return json_decode($response->getBody()->getContents());
    }

    /**
     * Get chat history.
     */
    public function getChatHistory($dialogId)
    {
        $url = $this->baseUrl . '/chat/Message?chat_dialog_id=' . $dialogId;

        $response = $this->client->get($url, [
            'headers' => [
                'CB-Token' => $this->getSessionToken()
            ]
        ]);

        return json_decode($response->getBody()->getContents());
    }
}
