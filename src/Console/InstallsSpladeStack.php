<?php

namespace Laravel\Jetstream\Console;

use Illuminate\Filesystem\Filesystem;
use ProtoneMedia\Splade\Commands\InstallsSpladeExceptionHandler;
use ProtoneMedia\Splade\Commands\InstallsSpladeRouteMiddleware;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

trait InstallsSpladeStack
{
    use InstallsSpladeExceptionHandler;
    use InstallsSpladeRouteMiddleware;

    /**
     * Install the Splade Breeze stack.
     *
     * @return bool
     */
    protected function installSpladeStack()
    {
        // Check Laravel version...
        if (version_compare(app()->version(), '10.0', '<')) {
            $this->error('While you can still use Splade with Laravel 9, new projects should use Laravel 10.');

            return Command::FAILURE;
        }

        $this->replaceInFile('// Features::termsAndPrivacyPolicy(),', 'Features::termsAndPrivacyPolicy(),', config_path('jetstream.php'));
        $this->replaceInFile('// Features::profilePhotos(),', 'Features::profilePhotos(),', config_path('jetstream.php'));
        $this->replaceInFile("'stack' => 'inertia'", "'stack' => 'splade'", config_path('jetstream.php'));
        $this->replaceInFile("'middleware' => ['web']", "'middleware' => ['splade', 'web']", config_path('jetstream.php'));
        $this->replaceInFile("'middleware' => ['web']", "'middleware' => ['splade', 'web']", config_path('fortify.php'));

        $this->installExceptionHandler();
        $this->installRouteMiddleware();

        // NPM Packages...
        $this->updateNodePackages(function ($packages) {
            return [
                '@protonemedia/laravel-splade' => '^1.4.8',
                '@tailwindcss/forms' => '^0.5.3',
                '@tailwindcss/typography' => '^0.5.2',
                '@vitejs/plugin-vue' => '^4.0.0',
                'autoprefixer' => '^10.4.12',
                'laravel-vite-plugin' => '^0.7.5',
                'postcss' => '^8.4.18',
                'tailwindcss' => '^3.3.0',
                'vite' => '^4.0.0',
                'vue' => '^3.2.41',
            ] + $packages;
        });

        // Sanctum...
        (new Process([$this->phpBinary(), 'artisan', 'vendor:publish', '--provider=Laravel\Sanctum\SanctumServiceProvider', '--force'], base_path()))
            ->setTimeout(null)
            ->run(function ($type, $output) {
                $this->output->write($output);
            });

        // Add SSR build step...
        $this->updateNodeScript();

        // Directories...
        (new Filesystem)->ensureDirectoryExists(app_path('Actions/Fortify'));
        (new Filesystem)->ensureDirectoryExists(app_path('Actions/Jetstream'));
        (new Filesystem)->ensureDirectoryExists(resource_path('css'));
        (new Filesystem)->ensureDirectoryExists(resource_path('js'));
        (new Filesystem)->ensureDirectoryExists(resource_path('views'));
        (new Filesystem)->ensureDirectoryExists(resource_path('markdown'));

        (new Filesystem)->deleteDirectory(resource_path('sass'));

        // Terms Of Service / Privacy Policy...
        copy(__DIR__.'/../../stubs/resources/markdown/terms.md', resource_path('markdown/terms.md'));
        copy(__DIR__.'/../../stubs/resources/markdown/policy.md', resource_path('markdown/policy.md'));

        // Service Providers...
        copy(__DIR__.'/../../stubs/app/Providers/JetstreamServiceProvider.php', app_path('Providers/JetstreamServiceProvider.php'));

        $this->installServiceProviderAfter('FortifyServiceProvider', 'JetstreamServiceProvider');

        // Models...
        copy(__DIR__.'/../../stubs/app/Models/User.php', app_path('Models/User.php'));

        // Factories...
        copy(__DIR__.'/../../database/factories/UserFactory.php', base_path('database/factories/UserFactory.php'));

        // Actions...
        copy(__DIR__.'/../../stubs/app/Actions/Fortify/CreateNewUser.php', app_path('Actions/Fortify/CreateNewUser.php'));
        copy(__DIR__.'/../../stubs/app/Actions/Fortify/UpdateUserProfileInformation.php', app_path('Actions/Fortify/UpdateUserProfileInformation.php'));
        copy(__DIR__.'/../../stubs/app/Actions/Jetstream/DeleteUser.php', app_path('Actions/Jetstream/DeleteUser.php'));

        $spladeJetstreamStubsDir = __DIR__.'/../../stubs/splade/';
        $spladeBaseStubsDir = base_path('vendor/protonemedia/laravel-splade/stubs/');

        // Views...
        (new Filesystem)->ensureDirectoryExists(resource_path('views'));
        copy($spladeBaseStubsDir.'resources/views/root.blade.php', resource_path('views/root.blade.php'));
        (new Filesystem)->copyDirectory($spladeJetstreamStubsDir.'resources/views', resource_path('views'));

        // Remove Dark Classes until Splade supports it...
        $this->removeDarkClasses((new Finder)
            ->in(resource_path('views'))
            ->name('*.blade.php')
        );

        // Routes...
        $this->replaceInFile('auth:api', 'auth:sanctum', base_path('routes/api.php'));

        copy($spladeJetstreamStubsDir.'routes/web.php', base_path('routes/web.php'));

        // Teams...
        if ($this->option('teams')) {
            $this->ensureApplicationIsTeamCompatible();
        }

        // Tests...
        $this->installSpladeTests();

        // Tailwind / Vite...
        copy($spladeBaseStubsDir.'tailwind.config.js', base_path('tailwind.config.js'));
        copy($spladeBaseStubsDir.'postcss.config.js', base_path('postcss.config.js'));
        copy($spladeBaseStubsDir.'vite.config.js', base_path('vite.config.js'));
        copy($spladeBaseStubsDir.'resources/css/app.css', resource_path('css/app.css'));
        copy($spladeBaseStubsDir.'resources/js/app.js', resource_path('js/app.js'));
        copy($spladeBaseStubsDir.'resources/js/ssr.js', resource_path('js/ssr.js'));

        if (file_exists(base_path('pnpm-lock.yaml'))) {
            $this->runCommands(['pnpm install', 'pnpm run build']);
        } elseif (file_exists(base_path('yarn.lock'))) {
            $this->runCommands(['yarn install', 'yarn run build']);
        } else {
            $this->runCommands(['npm install', 'npm run build']);
        }

        $this->line('');
        $this->components->info('Splade scaffolding installed successfully.');

        return true;
    }

    /**
     * Adds the SSR build step to the 'build' command.
     *
     * @return void
     */
    protected function updateNodeScript()
    {
        if (! file_exists(base_path('package.json'))) {
            return;
        }

        $packageFile = file_get_contents(base_path('package.json'));

        file_put_contents(
            base_path('package.json'),
            str_replace('"vite build"', '"vite build && vite build --ssr"', $packageFile)
        );
    }

    /**
     * Install Splade's Dusk tests.
     *
     * @return void
     */
    protected function installSpladeTests()
    {
        $this->installDusk();
        (new Filesystem)->ensureDirectoryExists(base_path('tests/Browser/Jetstream'));
        (new Filesystem)->delete(base_path('tests/Browser/ExampleTest.php'));
        (new Filesystem)->copyDirectory(__DIR__.'/../../stubs/tests/splade/dusk', base_path('tests/Browser/Jetstream'));
        (new Filesystem)->copy(__DIR__.'/../../stubs/tests/splade/.env.dusk', base_path('.env.dusk'));
        (new Filesystem)->put(base_path('database/database.sqlite'), '');
    }

    /**
     * Install Laravel Dusk.
     *
     * @return void
     */
    protected function installDusk()
    {
        $this->requireComposerPackages(['laravel/dusk', 'protonemedia/laravel-dusk-fakes'], true);

        (new Process([$this->phpBinary(), 'artisan', 'dusk:install'], base_path()))
            ->setTimeout(null)
            ->run(function ($type, $output) {
                $this->output->write($output);
            });
    }
}
