<p align="center">
    <img title="Waybar Netatmo Weather Module" height="193" src="https://raw.githubusercontent.com/simonellensohn/waybar-netatmo-weather-module/main/screenshot.png" alt="Waybar Netatmo Weather Module" />
</p>

Waybar Netatmo Weather Module provides a simple visualization of your personal Netatmo Weather Station modules.

## Features

- Displays temperature, humidity, and CO₂ levels for all modules
- Displays a warning when a module's CO₂ level exceeds 1000 ppm
- Displays a warning when a module's battery level drops below 20%

## Requirements

- PHP 8.4+
- Waybar
- Netatmo Smart Home Weather Station

## Installation

### Netatmo App

1. Head to https://dev.netatmo.com/apps and create a new app.
2. Generate a new token with the scope `read_station`.
3. Save the generated `Refresh Token`.

### Set up module

1. Clone repository
    ```bash
    git clone https://github.com/simonellensohn/waybar-netatmo-weather-module.git
    ```

2. Install dependencies:
    ```bash
    composer install
    ```

3. Set up the environment file and enter your Netatmo app information:
    ```bash
    cp .env.example .env
    ```

4. Execute the script using the generated refresh token to set up and test the credentials:
    ```bash
    php waybar-netatmo-weather-module netatmo:print-modules "[refresh_token]"
    ```

5. Configure your Waybar module:
    ```json5
    // ~/.config/waybar/config.jsonc
    "custom/netatmo": {
        "exec": "php /path/to/waybar-netatmo-weather-module netatmo:print-modules",
        "return-type": "json",
        "interval": 300,
        "format": "{}",
        "tooltip": true
    }
    ```

6. Reload Waybar:
    ```bash
    killall -SIGUSR2 waybar
    ```

## License

Waybar Netatmo Weather Module is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).
