<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SysbaseMake extends Command
{
    protected $signature = 'sysbase:make {model}';
    protected $description = 'Genera Modelo, Migration, Factory, Seeder, ApiController y Filament Resource con sobreescritura opcional';

    public function handle()
    {
        $model = Str::studly($this->argument('model'));
        $table = Str::snake(Str::plural($model));

        $this->line("======================================");
        $this->info(" Generando módulo SYSBASE → {$model}");
        $this->line("======================================");

        /*
        |--------------------------------------------------------------------------
        | 1. Modelo + Migration + Factory
        |--------------------------------------------------------------------------
        */
        $modelPath = app_path("Models/{$model}.php");
        if ($this->confirmOverwrite($modelPath, "Modelo {$model}")) {
            Artisan::call("make:model", [
                "name" => $model,
                "-m" => true,
                "-f" => true,
                "-s" => false,
            ]);
            $this->info("Modelo + Migration + Factory generados.");
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Seeder
        |--------------------------------------------------------------------------
        */
        $seederPath = database_path("seeders/{$model}Seeder.php");
        if ($this->confirmOverwrite($seederPath, "Seeder {$model}Seeder")) {
            Artisan::call("make:seeder", [
                "name" => "{$model}Seeder",
            ]);
            $this->info("Seeder generado.");
        }

        /*
        |--------------------------------------------------------------------------
        | 3. API CONTROLLER
        |--------------------------------------------------------------------------
        */
        $controllerPath = app_path("Http/Controllers/Api/{$model}Controller.php");
        if ($this->confirmOverwrite($controllerPath, "ApiController {$model}Controller")) {
            Artisan::call("make:controller", [
                "name" => "Api/{$model}Controller",
                "--api" => true,
            ]);
            $this->info("ApiController generado.");
        }

        /*
        |--------------------------------------------------------------------------
        | 4. Filament Resource
        |--------------------------------------------------------------------------
        */
        $filamentFolder = app_path("Filament/Resources/{$model}Resource");
        if ($this->confirmOverwrite($filamentFolder, "Filament Resource {$model}Resource")) {

            // Si existe carpeta completa → se borra
            if (File::exists($filamentFolder)) {
                File::deleteDirectory($filamentFolder);
            }

            Artisan::call("make:filament-resource", [
                "name" => $model,
                "--generate" => true,
                "--panel" => "admin",
            ]);

            $this->info("Filament Resource generado.");
        }

        $this->line("======================================");
        $this->info(" Módulo {$model} generado correctamente 👍");
        $this->line("======================================");

        return Command::SUCCESS;
    }

    /**
     * Pregunta si se debe sobrescribir un archivo existente.
     */
    private function confirmOverwrite(string $path, string $label): bool
    {
        if (!File::exists($path)) {
            return $this->confirm("¿Crear {$label}?", true);
        }

        $this->warn("⚠ {$label} ya existe en: {$path}");

        return $this->confirm("¿Desea sobrescribirlo?", false);
    }
}
