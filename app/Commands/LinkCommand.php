<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\DB;
use LaravelZero\Framework\Commands\Command;

class LinkCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'link';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lié un dossier staging_path à un dossier working_path';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $configFile = getcwd().'/config.json';
        $config = json_decode(file_get_contents($configFile), true);

        $stagingDirs = $this->getDirectories($config['staging_path']);
        $workingDirs = $this->getDirectories($config['working_path']);
        $stagingChoice = $this->choice('Choisissez un dossier dans le staging_path', $stagingDirs);
        $workingChoice = $this->choice('Choisissez un dossier dans le working_path', $workingDirs);

        $this->task("Liaison des dossiers $stagingChoice et $workingChoice", function () use ($stagingDirs, $workingDirs, $stagingChoice, $workingChoice, $config) {
            try {
                $this->createLink($stagingChoice, $workingChoice);
            }catch (\Exception $e) {
                $this->error($e->getMessage());
            }
        });

        $this->call('start', ['--without-config']);
    }

    private function getDirectories(string $path): array
    {
        return array_filter(
            scandir($path),
            fn($dir) => is_dir($path.DIRECTORY_SEPARATOR.$dir) && !in_array($dir, ['.', '..'])
        );
    }

    private function createLink(string $stagingDir, string $workingDir)
    {
        $KEY_EXISTS = DB::table('links')
            ->where('keybase', $stagingDir.'_'.$workingDir)
            ->count();

        if($KEY_EXISTS > 0) {
            $this->error("La liaison existe déjà");
            return false;
        } else {
            DB::table('links')->insert([
                'keybase' => $stagingDir.'_'.$workingDir,
                'staging_path' => $stagingDir,
                'working_path' => $workingDir
            ]);
            $this->info("Liaison créée");
            return true;
        }
    }
}
