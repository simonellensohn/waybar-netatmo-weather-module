<?php

namespace App\Data;

use Illuminate\Support\Carbon;

class AuthCredentials
{
    public function __construct(
        public ?string $access_token,
        public ?string $refresh_token,
        public ?Carbon $expires_at,
    ) {}

    public static function from(?array $data = []): self
    {
        return new self(
            access_token: $data['access_token'] ?? null,
            refresh_token: $data['refresh_token'] ?? null,
            expires_at: filled($data['expires_at'] ?? null) ? Carbon::parse($data['expires_at']) : null,
        );
    }

    public function isEmpty(): bool
    {
        return blank($this->access_token) && blank($this->refresh_token);
    }

    public function needRefresh(): bool
    {
        if (blank($this->access_token) && filled($this->refresh_token)) {
            return true;
        }

        if ($this->expires_at && now()->gte($this->expires_at)) {
            return true;
        }

        return false;
    }
}
