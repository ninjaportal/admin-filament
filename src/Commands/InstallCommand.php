<?php

namespace NinjaPortal\Admin\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'portal:install:admin-filament';

    protected $description = 'Install the Ninja Portal Admin Filament package';

    public function handle(): void
    {
        $this->call('filament:install',['--panels' => true,'--no-interaction' => true]);

        // publish the stub
        $stubPath = __DIR__.'/../../stubs/PanelProvider.stub';
        $stub = file_get_contents($stubPath);

        // publish to
        $path = app_path('Providers/Filament/AdminPanelProvider.php');
        file_put_contents($path, $stub);

        $this->call('vendor:publish',['--tag' => 'ninjaadmin-migrations','--no-interaction' => true]);
        $this->call("migrate");


    }
}
