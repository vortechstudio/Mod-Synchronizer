<?php

namespace App\Commands\Config;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

use function Laravel\Prompts\select;

class ConfigCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'config:edit';

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
        $configFile = getcwd().'/config.json';
        $config = json_decode(file_get_contents($configFile), true);

        $menuSelect = select(
            label: 'Que souhaitez-vous modifier ?',
            options: [
                'staging_path' => 'Chemin du staging_area',
                'working_path' => 'Chemin de votre dossier de travail',
            ]
        );

        $newValue = match ($menuSelect) {
            'staging_path' => $this->askForStagingPath(),
            'working_path' => $this->askForWorkingPath(),
        };

        $config[$menuSelect] = $newValue;
        $this->saveConfig($configFile, $config);
    }

    private function askForStagingPath()
    {
        return $this->ask("Veuillez entrer le chemin du staging_area pour vos mods");
    }
    /**
     * Demande à l'utilisateur d'entrer le chemin vers son dossier de travail
     *
     * @return string Le chemin du dossier de travail saisi par l'utilisateur
     */
    private function askForWorkingPath()
    {
        return $this->ask("Veuillez entrer le chemin de votre dossier de travail.");
    }

    private function saveConfig($filePath, $config)
    {
        file_put_contents($filePath, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->info("Configuration sauvegardée dans $filePath.");
    }
}
