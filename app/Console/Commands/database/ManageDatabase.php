<?php

namespace App\Console\Commands\database;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ManageDatabase extends Command
{
    protected $signature = 'database:manage {name}
     {--delete : Drop the database instead of creating it}
     {--create : Create the database instead of dropping it}';

    protected $description = 'Create a new MySQL database';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $dbName = $this->argument('name');

        if ($this->option('delete')) {
            $query = "DROP DATABASE IF EXISTS `$dbName`";
            DB::statement($query);

            $this->info("Database '$dbName' has been deleted.");

            return self::SUCCESS;
        } elseif ($this->option('create')) {
            $query = "CREATE DATABASE IF NOT EXISTS `$dbName` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
            DB::statement($query);

            $this->info("Database '$dbName' created successfully.");

            return self::SUCCESS;
        }

        $query = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbName'";
        $result = DB::select($query);

        if (count($result) > 0) {
            $this->info("Database '$dbName' already exists.");
        } else {
            $this->error("Database '$dbName' does not exist.");
        }

        return self::SUCCESS;
    }
}
