<?php

namespace App\Data;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class NetatmoModule
{
    public function __construct(
        public string $id,
        public string $name,
        public ?int $battery_percentage,
        public bool $reachable,
        public ?int $last_message,
        public float $temperature,
        public int $humidity,
        public ?int $co2,
        public float $min_temp,
        public float $max_temp,
        public string $temp_trend
    ) {}

    public static function collectResponse(array $modules): Collection
    {
        return Collection::make(array_map(static function (array $module) {
            return new self(
                id: $module['_id'],
                name: $module['module_name'],
                battery_percentage: $module['battery_percent'] ?? null,
                reachable: $module['reachable'] ?? true,
                last_message: $module['last_message'] ?? null,
                temperature: Arr::get($module['dashboard_data'], 'Temperature'),
                humidity: Arr::get($module['dashboard_data'], 'Humidity'),
                co2: Arr::get($module['dashboard_data'], 'CO2'),
                min_temp: Arr::get($module['dashboard_data'], 'min_temp'),
                max_temp: Arr::get($module['dashboard_data'], 'max_temp'),
                temp_trend: Arr::get($module['dashboard_data'], 'temp_trend'),
            );
        }, $modules));
    }

    public function format(): string
    {
        return implode('', array_filter([
            str($this->name)->padRight(16),
            str("🌡 {$this->temperature}°C")->padRight(10),
            str("💧 {$this->humidity}%")->padRight(8),
            filled($this->co2) ? ($this->co2 > 1000 ? "<span color=\"#ff5555\">☁ {$this->co2}</span>" : "☁ {$this->co2}") : null,
            filled($this->battery_percentage) && $this->battery_percentage < 20 ? '<span color="#ff5555">⚠️</span>' : null,
        ]));
    }
}
