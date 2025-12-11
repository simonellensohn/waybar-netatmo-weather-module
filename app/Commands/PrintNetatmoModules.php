<?php

namespace App\Commands;

use App\Actions\RetrieveAccessToken;
use App\Data\AuthCredentials;
use App\Data\NetatmoModule;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use LaravelZero\Framework\Commands\Command;

class PrintNetatmoModules extends Command
{
    protected $signature = 'netatmo:print-modules {refreshToken?}';

    protected $description = 'Prints the current modules data in a JSON format for waybar.';

    public function handle(): void
    {
        $response = Http::asForm()
            ->acceptJson()
            ->withToken($this->getAccessToken())
            ->post('https://api.netatmo.com/api/getstationsdata', [
                'device_id' => config('netatmo.device_id'),
            ]);

        if ($response->failed()) {
            echo $this->handleError($response);

            return;
        }

        echo $this->handleResponse($response);
    }

    private function getAccessToken(): string
    {
        $credentials = AuthCredentials::from(Storage::json('netatmo_credentials.json'));

        if ($forceRefreshToken = $this->argument('refreshToken')) {
            $credentials->refresh_token = $forceRefreshToken;
            $credentials->access_token = null;
        }

        if ($credentials->isEmpty()) {
            $this->fail('Unable to retrieve an access token. Generate a new refresh token and pass it to this command.');
        }

        if ($credentials->needRefresh()) {
            $credentials = new RetrieveAccessToken()->handle($credentials->refresh_token);
        }

        return $credentials->access_token;
    }

    private function handleResponse(Response $response): string
    {
        $station = $response->json('body.devices.0');
        $modules = NetatmoModule::collectResponse([
            ...$station['modules'] ?? [],
            Arr::except($station, 'modules'),
        ]);
        /** @var ?NetatmoModule $focusedModule */
        $focusedModule = $modules->firstWhere('id', config('netatmo.focused_module_id')) ?? $modules->first();

        $tooltip = $modules
            ->map(fn (NetatmoModule $module) => $module->format())
            ->push('', '<span color="#a6adc8">Last update: '.date('H:i').'</span>')
            ->join(PHP_EOL);

        return json_encode(
            value: [
                'text' => ' '.($focusedModule?->temperature ?? '?').'°C'.($modules->some('co2', '>', 1000) ? ' <span color="#ff5555">️☁</span>' : ''),
                'tooltip' => $tooltip,
            ],
            flags: JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }

    private function handleError(Response $response): string
    {
        return json_encode(
            value: [
                'text' => ' ?°C',
                'tooltip' => implode(PHP_EOL, [
                    'Data retrieval failed...',
                    '',
                    json_encode($response->json(), JSON_PRETTY_PRINT),
                ]),
            ],
            flags: JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }
}
