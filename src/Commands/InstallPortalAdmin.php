<?php

namespace NinjaPortal\Admin\Commands;

use Exception;
use Filament\PanelProvider;
use Filament\Support\Commands\Concerns\CanGeneratePanels;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use function Laravel\Prompts\select;

class InstallPortalAdmin extends Command
{
    use CanGeneratePanels;

    protected $signature = 'portal:install:admin';
    protected $description = 'Install Portal Admin with Filament';

    public function handle(): void
    {
        $this->info('Starting the Portal Admin installation process.');

        $adminType = select(
            'Which type of Admin would you like to install?',
            [
                '0' => 'Filament (Web Interface)',
                '1' => 'API (Backend Interface)',
                '2' => 'Both (Filament + API)',
            ],
            'Filament (Web Interface)'
        );


        if ($adminType !== 'Filament (Web Interface)') {
            $this->error('Only Filament Admin is supported at the moment.');
            return;
        }

        // Verify Filament Admin dependency
        if (!class_exists(PanelProvider::class)) {
            $this->error('Filament Admin is not installed. Please install [filament/filament] package first.');
            return;
        }

        $this->installFilamentAdmin();
    }

    private function installFilamentAdmin(): void
    {
        $this->info('Installing <bg=bright-yellow> Filament </> Admin');

        $this->createStubs();

        $this->generatePanel(id: "admin");

        $this->publishMigrationFiles();
        $this->runMigrations();

        $this->info('Admin Panel generated successfully.');
        $this->info('You may now create a user by running <bg=black> php artisan make:filament-user </>.');
    }

    private function publishMigrationFiles(): void
    {
        try {
            Artisan::call('vendor:publish', [
                '--tag' => 'ninjaadmin-migrations',
                '--no-interaction' => true
            ]);
            $this->info('Migration files published successfully.');
        } catch (Exception $e) {
            $this->error('Failed to publish migration files: ' . $e->getMessage());
        }
    }

    private function runMigrations(): void
    {
        try {
            Artisan::call('migrate');
            $this->info('Migrations ran successfully.');
        } catch (Exception $e) {
            $this->error('Migration failed: ' . $e->getMessage());
        }
    }

    protected function getDefaultStubPath(): string
    {
        return $this->packagePath('stubs');
    }

    protected function packagePath($path = ''): string
    {
        return dirname(__DIR__, 3) . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    private function createStubs(): void
    {
        try {
            File::copyDirectory(
                base_path('vendor/filament/support/stubs'),
                $this->packagePath('stubs')
            );

            $stubs = glob($this->packagePath('stubs') . '/*.stub');
            foreach ($stubs as $stub) {
                $content = file_get_contents($stub);
                $content = preg_replace(
                    '/(.*)(->id\(\'\{\{ id \}\}\'\))/',
                    "$1$2\n$1->plugin(\\NinjaPortal\\Admin\\NinjaAdminPlugin::make())",
                    $content
                );
                file_put_contents($stub, $content);
            }

            $this->info('Stub files created and modified successfully.');
        } catch (Exception $e) {
            $this->error('Failed to create stub files: ' . $e->getMessage());
        }
    }
}
