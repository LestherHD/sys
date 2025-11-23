<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GenerarResource extends Command
{
    protected $signature = 'sysbase:make {name}';
    protected $description = 'Genera modelo, migration, seeder, api controller y filament resource usando stubs personalizados';

    public function handle()
    {
        $name = Str::studly($this->argument('name'));
        $table = Str::snake(Str::plural($name));

        $this->info("=== Generando módulo $name ===");

        /** Preguntas */
        $crearModel = $this->confirm("¿Deseas crear el Modelo $name?");
        $crearMigration = $this->confirm("¿Deseas crear la Migración de $table?");
        $crearSeeder = $this->confirm("¿Deseas crear el Seeder $name"."Seeder?");
        $crearController = $this->confirm("¿Deseas crear el API Controller $name"."Controller?");
        $crearFilament = $this->confirm("¿Deseas crear el Filament Resource $name"."Resource?");

        /** Modelo */
        if ($crearModel) {
            $this->crearArchivoDesdeStub(
                'model.stub',
                app_path("Models/{$name}.php"),
                ['{{ model }}' => $name]
            );
            $this->info("Modelo creado: app/Models/{$name}.php");
        }

        /** Migration */
        if ($crearMigration) {
            $fileName = date('Y_m_d_His')."_create_{$table}_table.php";

            $this->crearArchivoDesdeStub(
                'migration.stub',
                database_path("migrations/{$fileName}"),
                [
                    '{{ table }}' => $table,
                    '{{ model }}' => $name,
                ]
            );

            $this->info("Migración creada: database/migrations/{$fileName}");
        }

        /** Seeder */
        if ($crearSeeder) {
            $this->crearArchivoDesdeStub(
                'seeder.stub',
                database_path("seeders/{$name}Seeder.php"),
                [
                    '{{ model }}' => $name,
                    '{{ table }}' => $table,
                ]
            );

            $this->info("Seeder creado: database/seeders/{$name}Seeder.php");
        }

        /** Controller */
        if ($crearController) {
            $this->crearArchivoDesdeStub(
                'controller.stub',
                app_path("Http/Controllers/Api/{$name}Controller.php"),
                [
                    '{{ model }}' => $name,
                    '{{ modelCamel }}' => Str::camel($name),
                ]
            );

            $this->info("Controlador API creado: app/Http/Controllers/Api/{$name}Controller.php");
        }

        /** Filament Resource */
        if ($crearFilament) {
            $this->crearArchivoDesdeStub(
                'resource.stub',
                app_path("Filament/Resources/{$name}Resource.php"),
                [
                    '{{ model }}' => $name,
                    '{{ modelPlural }}' => Str::pluralStudly($name),
                    '{{ modelCamel }}' => Str::camel($name),
                ]
            );

            $this->info("Filament Resource creado: app/Filament/Resources/{$name}Resource.php");
        }

        $this->info("=== Módulo $name generado correctamente ===");

        return Command::SUCCESS;
    }

    private function crearArchivoDesdeStub($stubName, $outputPath, $replacements)
    {
        $stubPath = base_path("stubs/{$stubName}");

        if (!File::exists($stubPath)) {
            $this->error("El stub {$stubName} no existe.");
            return;
        }

        $content = File::get($stubPath);

        foreach ($replacements as $key => $value) {
            $content = str_replace($key, $value, $content);
        }

        File::ensureDirectoryExists(dirname($outputPath));

        File::put($outputPath, $content);
    }
}
