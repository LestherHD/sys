<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AutoScaffoldCommand extends Command
{
    protected $signature = 'scaffolding:auto {model} {table}';
    protected $description = 'Generador Sysbase con SEGURIDAD MÁXIMA contra sobrescritura.';

    public function handle()
    {
        $model = Str::studly($this->argument('model'));
        $table = $this->argument('table');
        $modelPlural = Str::plural($model);
        $modelVariable = Str::camel($model);
        $modelLabel = Str::headline($model); // Ej: "Categoria Menu"

        $this->info("🛡️  GESTOR SYSBASE BLINDADO: $model");
        $this->line("-----------------------------------------------------");

        if (!Schema::hasTable($table)) {
            $this->error("❌ ERROR: La tabla '$table' no existe. Ejecuta migraciones primero.");
            return;
        }

        // =========================================================
        // PASO 1: MODELO
        // =========================================================
        $this->line(""); // Espacio visual
        if ($this->confirm("1. ¿Gestionar MODELO '$model'?", true)) {
            $relationshipsCode = $this->detectRelationships($table);
            $columns = Schema::getColumnListing($table);

            // Detectar si tiene soft deletes
            $hasSoftDeletes = in_array('deleted_at', $columns);
            $softDeletesImport = $hasSoftDeletes ? "use Illuminate\Database\Eloquent\SoftDeletes;\n" : "";
            $softDeletesTrait = $hasSoftDeletes ? ", SoftDeletes" : "";

            // Detectar si tiene campo password
            $hasPassword = in_array('password', $columns);
            $passwordImports = $hasPassword ? "use Illuminate\Database\Eloquent\Casts\Attribute;\n" : "";
            $passwordMutator = $hasPassword ? $this->generatePasswordMutator() : "";

            // Generar fillable automáticamente
            $fillableFields = $this->generateFillableFields($table);

            // Generar casts automáticamente
            $casts = $this->generateCasts($table);

            $this->createFileSafely('model.stub', app_path("Models/{$model}.php"), [
                '{{ model }}' => $model,
                '{{ table }}' => $table,
                '{{ relationships }}' => $relationshipsCode,
                '{{ softDeletesImport }}' => $softDeletesImport,
                '{{ softDeletesTrait }}' => $softDeletesTrait,
                '{{ passwordImports }}' => $passwordImports,
                '{{ passwordMutator }}' => $passwordMutator,
                '{{ fillableFields }}' => $fillableFields,
                '{{ casts }}' => $casts,
            ]);
        }

        // =========================================================
        // PASO 2: API CONTROLLER
        // =========================================================
        $this->line("");
        if ($this->confirm("2. ¿Gestionar API CONTROLLER?", true)) {
            $this->createFileSafely('api_controller.stub', app_path("Http/Controllers/Api/{$model}Controller.php"), [
                '{{ model }}' => $model,
                '{{ modelVariable }}' => $modelVariable
            ]);
        }

        // =========================================================
        // PASO 3: SEEDER (Aquí fue tu problema anterior)
        // =========================================================
        $this->line("");
        if ($this->confirm("3. ¿Gestionar SEEDER?", true)) {
            // Nota: Usamos database_path puro para evitar confusiones de ruta
            $this->createFileSafely('seeder.stub', database_path("seeders/{$model}Seeder.php"), [
                '{{ model }}' => $model
            ]);
        }

        // =========================================================
        // PASO 4: FILAMENT RESOURCE
        // =========================================================
        $this->line("");
        if ($this->confirm("4. ¿Gestionar FILAMENT RESOURCE?", true)) {

            $resourceDir = app_path("Filament/Resources/{$model}Resource");
            $pagesDir = "$resourceDir/Pages";
            File::ensureDirectoryExists($pagesDir);

            $columnsCode = $this->generateTableColumns($table);
            $formFields = $this->generateFormFields($table);

            // Resource Main
            $this->createFileSafely('resource.stub', "$resourceDir.php", [
                '{{ model }}' => $model,
                '{{ modelPlural }}' => $modelPlural,
                '{{ tableColumns }}' => $columnsCode,
                '{{ formFields }}' => $formFields,
            ]);

            // Pages
            $this->createFileSafely('page_list.stub', "$pagesDir/List{$modelPlural}.php", ['{{ model }}' => $model, '{{ modelPlural }}' => $modelPlural, '{{ modelLabel }}' => $modelLabel]);
            $this->createFileSafely('page_create.stub', "$pagesDir/Create{$model}.php", ['{{ model }}' => $model]);
            $this->createFileSafely('page_view.stub', "$pagesDir/View{$model}.php", ['{{ model }}' => $model]);
            $this->createFileSafely('page_edit.stub', "$pagesDir/Edit{$model}.php", ['{{ model }}' => $model]);
        }

        $this->line("");
        $this->info("✅ ¡Proceso finalizado con seguridad!");
    }

    /**
     * 🔒 FUNCIÓN DE CREACIÓN SEGURA
     * Verifica estrictamente si el archivo existe y pide confirmación explícita.
     */
    protected function createFileSafely($stubName, $destination, $replacements)
    {
        $fileName = basename($destination);

        // Verificación doble: File::exists de Laravel Y file_exists de PHP nativo
        if (File::exists($destination) || file_exists($destination)) {
            $this->warn("   ⚠️  ALERTA: El archivo YA EXISTE: $fileName");

            // EL DEFAULT ES FALSE (NO). Si das enter, NO sobrescribe.
            if (!$this->confirm("      ¿Deseas BORRARLO y crear uno nuevo? (Se perderán tus cambios)", false)) {
                $this->info("      ⏩ Omitido (Tu archivo está a salvo).");
                return;
            }
        }

        $stubPath = resource_path("stubs/custom/{$stubName}");
        if (!File::exists($stubPath)) {
            $this->error("      ❌ Error crítico: Falta el stub $stubName");
            return;
        }

        $content = File::get($stubPath);
        foreach ($replacements as $key => $value) {
            $content = str_replace($key, $value, $content);
        }

        File::ensureDirectoryExists(dirname($destination));

        // Guardar sin BOM (UTF-8 puro) para evitar errores de namespace
        file_put_contents($destination, $content);

        $this->info("      ✔ Archivo generado/actualizado: $fileName");
    }

    // --- FUNCIONES DE AYUDA (INTELIGENCIA) ---

    private function detectRelationships($table)
    {
        $columns = Schema::getColumnListing($table);
        $code = "";
        foreach ($columns as $column) {
            if (Str::endsWith($column, '_id')) {
                $relationName = Str::beforeLast($column, '_id');
                $methodName = Str::camel($relationName);
                $relatedModel = Str::studly($relationName);
                $code .= "\n    public function {$methodName}(): \Illuminate\Database\Eloquent\Relations\BelongsTo\n    {\n        return \$this->belongsTo({$relatedModel}::class, '{$column}');\n    }\n";
            }
        }
        return $code;
    }

    private function generateTableColumns($table)
    {
        $columns = Schema::getColumnListing($table);
        $code = "";

        foreach ($columns as $column) {
            if (in_array($column, ['password', 'remember_token', 'email_verified_at', 'deleted_at'])) continue;

            $type = Schema::getColumnType($table, $column);

            if ($column === 'id') {
                $code .= "                Tables\Columns\TextColumn::make('id')->sortable()->toggleable(isToggledHiddenByDefault: true),\n";
            }
            elseif ($type === 'boolean' || in_array($column, ['activo', 'visible', 'is_active'])) {
                $code .= "                Tables\Columns\IconColumn::make('$column')->boolean(),\n";
            }
            elseif (str_contains($column, 'image') || str_contains($column, 'avatar')) {
                $code .= "                Tables\Columns\ImageColumn::make('$column'),\n";
            }
            elseif (in_array($column, ['created_at', 'updated_at'])) {
                $toggle = "->toggleable(isToggledHiddenByDefault: true)";
                $code .= "                Tables\Columns\TextColumn::make('$column')->dateTime()->sortable()$toggle,\n";
            }
            else {
                $searchable = in_array($column, ['name', 'nombre', 'email', 'slug']) ? "->searchable()->sortable()->weight('bold')" : "";
                $code .= "                Tables\Columns\TextColumn::make('$column'){$searchable},\n";
            }
        }
        return $code;
    }

    private function generatePasswordMutator()
    {
        return "    /**
     * Hashea automáticamente la contraseña
     */
    protected function password(): Attribute
    {
        return Attribute::make(
            set: fn (\$value) => !empty(\$value) && !str_starts_with(\$value, '\$2y\$')
                ? bcrypt(\$value)
                : \$value
        );
    }

";
    }

    private function generateFillableFields($table)
    {
        $columns = Schema::getColumnListing($table);
        $fillable = [];

        // Excluir campos automáticos
        $excluded = ['id', 'created_at', 'updated_at', 'deleted_at', 'remember_token', 'email_verified_at'];

        foreach ($columns as $column) {
            if (!in_array($column, $excluded)) {
                $fillable[] = "'$column'";
            }
        }

        if (empty($fillable)) {
            return "// Sin campos fillable detectados";
        }

        return "\n        " . implode(",\n        ", $fillable) . ",\n    ";
    }

    private function generateCasts($table)
    {
        $columns = Schema::getColumnListing($table);
        $casts = [];

        foreach ($columns as $column) {
            $type = Schema::getColumnType($table, $column);

            if ($type === 'boolean' || in_array($column, ['activo', 'visible', 'is_active', 'visible_publico'])) {
                $casts[] = "'$column' => 'boolean'";
            }
            elseif ($type === 'date') {
                $casts[] = "'$column' => 'date'";
            }
            elseif ($type === 'datetime' && !in_array($column, ['created_at', 'updated_at', 'deleted_at'])) {
                $casts[] = "'$column' => 'datetime'";
            }
            elseif ($type === 'json') {
                $casts[] = "'$column' => 'array'";
            }
        }

        if (empty($casts)) {
            return "";
        }

        return "protected \$casts = [\n        " . implode(",\n        ", $casts) . ",\n    ];\n";
    }

    private function generateFormFields($table)
    {
        $columns = Schema::getColumnListing($table);
        $code = "";

        // Excluir campos automáticos y especiales
        $excluded = ['id', 'created_at', 'updated_at', 'deleted_at', 'remember_token', 'email_verified_at'];

        foreach ($columns as $column) {
            if (in_array($column, $excluded)) continue;

            $type = Schema::getColumnType($table, $column);
            $label = Str::title(str_replace('_', ' ', $column));

            // Password
            if ($column === 'password') {
                $code .= "                Forms\Components\TextInput::make('password')\n";
                $code .= "                    ->password()\n";
                $code .= "                    ->required(fn (\$record) => \$record === null)\n";
                $code .= "                    ->dehydrateStateUsing(fn (\$state) => filled(\$state) ? \$state : null)\n";
                $code .= "                    ->dehydrated(fn (\$state) => filled(\$state))\n";
                $code .= "                    ->label('$label'),\n";
            }
            // Email
            elseif ($column === 'email') {
                $code .= "                Forms\Components\TextInput::make('email')\n";
                $code .= "                    ->email()\n";
                $code .= "                    ->required()\n";
                $code .= "                    ->label('$label'),\n";
            }
            // Boolean / Toggle
            elseif ($type === 'boolean' || in_array($column, ['activo', 'visible', 'is_active', 'visible_publico'])) {
                $code .= "                Forms\Components\Toggle::make('$column')\n";
                $code .= "                    ->label('$label')\n";
                $code .= "                    ->default(true),\n";
            }
            // Textarea para campos text
            elseif ($type === 'text') {
                $code .= "                Forms\Components\Textarea::make('$column')\n";
                $code .= "                    ->label('$label')\n";
                $code .= "                    ->rows(3),\n";
            }
            // Date
            elseif ($type === 'date') {
                $code .= "                Forms\Components\DatePicker::make('$column')\n";
                $code .= "                    ->label('$label'),\n";
            }
            // DateTime
            elseif ($type === 'datetime') {
                $code .= "                Forms\Components\DateTimePicker::make('$column')\n";
                $code .= "                    ->label('$label'),\n";
            }
            // Numeric
            elseif (in_array($type, ['integer', 'bigint', 'decimal', 'float', 'double'])) {
                $code .= "                Forms\Components\TextInput::make('$column')\n";
                $code .= "                    ->numeric()\n";
                $code .= "                    ->label('$label'),\n";
            }
            // Foreign keys - Select
            elseif (Str::endsWith($column, '_id')) {
                $relationName = Str::beforeLast($column, '_id');
                $relatedModel = Str::studly($relationName);
                $code .= "                Forms\Components\Select::make('$column')\n";
                $code .= "                    ->relationship('$relationName', 'name')\n";
                $code .= "                    ->searchable()\n";
                $code .= "                    ->preload()\n";
                $code .= "                    ->label('$label'),\n";
            }
            // Default TextInput
            else {
                $maxLength = "";
                try {
                    $columnInfo = Schema::getConnection()->getDoctrineColumn($table, $column);
                    if ($columnInfo->getLength()) {
                        $maxLength = "\n                    ->maxLength({$columnInfo->getLength()})";
                    }
                } catch (\Exception $e) {
                    // Si falla, continuar sin maxLength
                }

                $required = in_array($column, ['name', 'nombre', 'title', 'email']) ? "\n                    ->required()" : "";
                $code .= "                Forms\Components\TextInput::make('$column')\n";
                $code .= "                    ->label('$label')$required$maxLength,\n";
            }
        }

        return $code;
    }
}
