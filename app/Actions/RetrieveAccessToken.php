<?php

namespace App\Actions;

use App\Data\AuthCredentials;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class RetrieveAccessToken
{
    public function handle(string $refreshToken): AuthCredentials
    {
        $response = Http::asForm()
            ->throw()
            ->acceptJson()
            ->post('https://api.netatmo.com/oauth2/token', [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
                'client_id' => config('netatmo.client_id'),
                'client_secret' => config('netatmo.client_secret'),
            ]);

        $credentials = [
            'access_token' => $response->json('access_token'),
            'refresh_token' => $response->json('refresh_token'),
            'expires_at' => now()->addSeconds($response->json('expires_at'))->toIso8601String(),
        ];

        Storage::put('netatmo_credentials.json', json_encode($credentials));

        return AuthCredentials::from($credentials);
    }
}
