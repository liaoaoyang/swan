<?php
/**
 * Created by PhpStorm.
 * User: ng
 * Date: 2018/6/23
 * Time: 下午1:37
 */

namespace App\Console\Commands;

use DB;
use App\Admin\Auth\Database\LaravelAdminTableSeeder;
use Encore\Admin\Auth\Database\Administrator;

class LaravelAdminInstallCommand extends \Encore\Admin\Console\InstallCommand
{
    protected $description = 'Install the admin package (Create Admin DB if not exists)';

    protected function createSWANAdminDatabase()
    {
        $connection = config('admin.database.connection') ?: config('database.default');
        $database = env('DB_DATABASE');

        if ($connection == 'mysql' && $database) {
            try {
                $host = env('DB_HOST');
                $port = env('DB_PORT');
                $username = env('DB_USERNAME');
                $password = env('DB_PASSWORD');
                $pdo = new \PDO("mysql:host={$host};port={$port};charset=UTF8", $username, $password);
                $pdo->exec(sprintf(
                    'CREATE DATABASE IF NOT EXISTS %s ;', $database
                ));

                $this->info(sprintf('Successfully created %s database', $database));

            } catch (\PDOException $exception) {
                $this->error(sprintf('Failed to create %s database, %s', $database, $exception->getMessage()));
                return false;
            }
        }

        return true;
    }



    public function initDatabase()
    {
        if (!$this->createSWANAdminDatabase()) {
            exit();
        }

        $this->call('migrate');

        //$migrateFileName = dirname(app_path()) . '/database/migrations/2016_01_04_173148_create_admin_tables.php';

        if (Administrator::count() == 0) {
            $this->call('db:seed', ['--class' => LaravelAdminTableSeeder::class]);
        }

        $this->call('admin:import', ['extension' => 'log-viewer']);
    }
}