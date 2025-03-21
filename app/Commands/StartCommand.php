<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

use function Laravel\Prompts\select;

class StartCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'start {--without-config : Start the application without config}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if(!$this->option('without-config')) {
            $this->call('updater');
            $this->call('config:verify');
        }

        $menuSelect = select(
            label: "Mod Synchronizer ".config('app.version'),
            options: [
                "link" => "Lié deux dossiers",
                "sync" => "Synchroniser un dossier de travail",
                "config" => "Configuration",
                "help" => "Aide",
                "exit" => "Quitter"
            ]
        );

        match($menuSelect) {
            "link" => $this->call('link'),
            "sync" => $this->call('sync'),
            "config" => $this->call('config:edit'),
            "help" => $this->call('help'),
            "exit" => $this->call('exit'),
        };
    }
}
