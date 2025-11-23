<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AutoScaffoldCommand extends Command
{
    protected $signature = 'sysbase:make {model}';
    protected $description = 'Genera Modelo, Migration, Seeder, ApiController y Filament Resource usando stubs personalizados';

    private string $stubPath = 'stubs/sysbase/';

    public function handle()
    {
        $name  = Str::studly($this->argument('model'));
        $table = Str::snake(Str::plural($name));

        $this->info("🚀 Generando módulo SYSBASE: {$name}");
        $this->line(str_repeat('─', 60));

        // Preguntas
        $makeModel      = $this->confirm("¿Crear Modelo {$name}?");
        $makeMigration  = $this->confirm("¿Crear Migration para {$table}?");
        $makeSeeder     = $this->confirm("¿Crear Seeder {$name}Seeder?");
        $makeController = $this->confirm("¿Crear ApiController {$name}Controller?");
        $makeFilament   = $this->confirm("¿Crear Filament Resource {$name}Resource?");

        // 1️⃣ Modelo
        if ($makeModel) {
            $this->createFromStub(
                'model.stub',
                app_path("Models/{$name}.php"),
                ['{{ model }}' => $name]
            );
            $this->info("📌 Modelo creado");
        }

        // 2️⃣ Migration
        if ($makeMigration) {
            $fileName = date('Y_m_d_His')."_create_{$table}_table.php";

            $this->createFromStub(
                'migration.stub',
                database_path("migrations/{$fileName}"),
                ['{{ table }}' => $table]
            );
            $this->info("📌 Migration creada");
        }

        // 3️⃣ Seeder
        if ($makeSeeder) {
            $this->createFromStub(
                'seeder.stub',
                database_path("seeders/{$name}Seeder.php"),
                ['{{ model }}' => $name, '{{ table }}' => $table]
            );
            $this->info("📌 Seeder creado");
        }

        // 4️⃣ ApiController
        if ($makeController) {
            $this->createFromStub(
                'controller.stub',
                app_path("Http/Controllers/Api/{$name}Controller.php"),
                [
                    '{{ model }}'      => $name,
                    '{{ modelCamel }}' => Str::camel($name)
                ]
            );
            $this->info("📌 ApiController generado");
        }

        // 5️⃣ Filament Resource
        if ($makeFilament) {
            $this->generateFilamentResource($name);
        }

        $this->line(str_repeat('─', 60));
        $this->info("🎉 Módulo {$name} generado correctamente");
        return Command::SUCCESS;
    }

    private function createFromStub($stubName, $outputPath, $replacements)
    {
        $stubPath = base_path($this->stubPath.$stubName);

        if (!File::exists($stubPath)) {
            $this->error("❌ No existe el stub: {$stubPath}");
            return;
        }

        $content = File::get($stubPath);

        foreach ($replacements as $key => $value) {
            $content = str_replace($key, $value, $content);
        }

        File::ensureDirectoryExists(dirname($outputPath));
        File::put($outputPath, $content);
    }

    private function generateFilamentResource(string $name)
    {
        $resourceDir = app_path("Filament/Resources/{$name}Resource");
        $pagesDir    = "{$resourceDir}/Pages";

        File::ensureDirectoryExists($pagesDir);

        // Resource principal
        $this->createFromStub(
            'resource-main.stub',
            "{$resourceDir}/{$name}Resource.php",
            [
                '{{ model }}'       => $name,
                '{{ modelPlural }}' => Str::pluralStudly($name)
            ]
        );

        // List
        $this->createFromStub(
            'resource-list.stub',
            "{$pagesDir}/List".Str::pluralStudly($name).".php",
            [
                '{{ model }}'       => $name,
                '{{ modelPlural }}' => Str::pluralStudly($name)
            ]
        );

        // Create
        $this->createFromStub(
            'resource-create.stub',
            "{$pagesDir}/Create{$name}.php",
            ['{{ model }}' => $name]
        );

        // Edit
        $this->createFromStub(
            'resource-edit.stub',
            "{$pagesDir}/Edit{$name}.php",
            ['{{ model }}' => $name]
        );

        $this->info("📌 Filament Resource creado correctamente");
    }
}
