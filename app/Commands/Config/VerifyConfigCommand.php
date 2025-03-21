<?php

namespace App\Commands\Config;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\File;
use LaravelZero\Framework\Commands\Command;

class VerifyConfigCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'config:verify';

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
        $this->task('Vérification des paramètres applicatif', function() {
            $configFile = getcwd().'/config.json';

            if(!File::exists($configFile)) {
                $this->error('Le fichier de configuration n\'existe pas');
                $config = $this->createConfig();
                $this->saveConfig($configFile, $config);
            } else {
                $config = json_decode(file_get_contents($configFile), true);
                if(!isset($config['staging_path']) || empty($config['staging_path'])) {
                    $this->warn("Vous n'avez pas définie le chemin vers votre `staging_area`");
                    $config['staging_path'] = $this->askForStagingPath();
                    $this->saveConfig($configFile, $config);
                } else if(!isset($config['working_path']) || empty($config['working_path'])) {
                    $this->warn("Vous n'avez pas définie le chemin vers Blender");
                    $config["working_path"] = $this->askForWorkingPath();
                    $this->saveConfig($configFile, $config);
                } else {
                    return true;
                }
            }
            return true;
        });
    }

    /**
     * Creates a new configuration array with staging and working paths
     *
     * @return array Array containing staging_path and working_path
     */
    private function createConfig() {
        return [
            'staging_path' => $this->askForStagingPath(),
            'working_path' => $this->askForWorkingPath(),
        ];
    }

    /**
     * Demande à l'utilisateur d'entrer le chemin vers le staging area pour les mods
     *
     * @return string Le chemin du staging area saisi par l'utilisateur
     */
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
