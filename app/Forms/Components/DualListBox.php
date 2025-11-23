<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Field;

class DualListBox extends Field
{
    protected string $view = 'forms.components.dual-listbox';

    protected array $options = [];

    public function options(array $options): static
    {
        $this->options = $options;
        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->dehydrateStateUsing(function ($state) {
            // Convertir el JSON string a array
            if (is_string($state)) {
                $decoded = json_decode($state, true);
                return is_array($decoded) ? $decoded : [];
            }
            return is_array($state) ? $state : [];
        });
    }
}
