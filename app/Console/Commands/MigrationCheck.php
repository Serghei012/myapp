<?php
namespace AdoreMe\MsTest\Console\Commands;

use Illuminate\Database\Console\Migrations\BaseCommand;
use Illuminate\Database\Migrations\Migrator;

/**
 * Created by PhpStorm.
 * User: gabrielcroitoru
 * Date: 12/03/2018
 * Time: 10:08
 */
class MigrationCheck extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:diff 
                {--path= : The path of migrations files to be executed.}
                {--step : Force the migrations to be run so they can be rolled back individually.}';

    /**
     * @var Migrator
     */
    protected $migrator;

    /**
     * Create a new migration command instance.
     *
     * @param  \Illuminate\Database\Migrations\Migrator $migrator
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->migrator = app('migrator');
    }

    public function fire()
    {
        $paths                 = $this->getMigrationPaths();
        $migrationFiles        = $this->migrator->getMigrationFiles($paths);
        $repository            = $this->migrator->getRepository();
        $ran                   = $repository->getRan();
        $missingFromFilesystem = [];
        foreach ($ran as $key) {
            if (array_key_exists($key, $migrationFiles)) {
                unset($migrationFiles[$key]);
            } else {
                $missingFromFilesystem[] = $key;
            }
        }
        $notes  = [];
        $status = 0;
        if (count($missingFromFilesystem) > 0) {
            $status  = 1;
            $notes[] = 'Migrations missing from filesystem:';
            $notes   = array_merge($notes, $missingFromFilesystem);
        }
        if (count($migrationFiles) > 0) {
            $status  = 1;
            $notes[] = 'Migrations missing from db:';
            $notes   = array_merge($notes, array_keys($migrationFiles));
        }

        if (count($notes) > 0) {
            $this->error(implode(PHP_EOL, $notes));
        }
        exit($status);
    }

}
