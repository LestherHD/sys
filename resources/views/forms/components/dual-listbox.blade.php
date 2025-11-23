@php
    $options = $getOptions();
    $selected = collect($getState() ?? []);
    $id = $getId();
@endphp

<div x-data="{ }" class="space-y-2">
    <div class="grid grid-cols-[1fr_auto_1fr] gap-4 items-start">
        {{-- LISTA IZQUIERDA --}}
        <div class="space-y-2">
            <label class="text-sm font-medium text-gray-950 dark:text-white">
                Opciones disponibles
            </label>
            <input
                type="text"
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                placeholder="Buscar..."
                onkeyup="dualListFilter(this, 'left-{{ $id }}')">

            <select
                multiple
                id="left-{{ $id }}"
                class="w-full h-56 rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-950 dark:text-white p-2 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                style="scrollbar-width: thin;">
                @foreach ($options as $key => $value)
                    @if (! $selected->contains($key))
                        <option value="{{ $key }}" class="p-1 hover:bg-gray-100 dark:hover:bg-gray-600">
                            {{ $value }}
                        </option>
                    @endif
                @endforeach
            </select>
        </div>

        {{-- BOTONES --}}
        <div class="flex flex-col justify-center gap-2 pt-8">
            <button
                type="button"
                onclick="dualListMove('left-{{ $id }}', 'right-{{ $id }}', '{{ $id }}')"
                class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg shadow transition-colors duration-200 font-semibold">
                →
            </button>

            <button
                type="button"
                onclick="dualListMove('right-{{ $id }}', 'left-{{ $id }}', '{{ $id }}')"
                class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg shadow transition-colors duration-200 font-semibold">
                ←
            </button>
        </div>

        {{-- LISTA DERECHA --}}
        <div class="space-y-2">
            <label class="text-sm font-medium text-gray-950 dark:text-white">
                Seleccionados
            </label>

            <input
                type="text"
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                placeholder="Buscar..."
                onkeyup="dualListFilter(this, 'right-{{ $id }}')">

            <select
                multiple
                id="right-{{ $id }}"
                class="w-full h-56 rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-950 dark:text-white p-2 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                style="scrollbar-width: thin;">
                @foreach ($options as $key => $value)
                    @if ($selected->contains($key))
                        <option value="{{ $key }}" class="p-1 hover:bg-gray-100 dark:hover:bg-gray-600">
                            {{ $value }}
                        </option>
                    @endif
                @endforeach
            </select>

            {{-- Hidden input sync --}}
            <input type="hidden" id="hidden-{{ $id }}" {{ $applyStateBindingModifiers('wire:model') }}="{{ $getStatePath() }}">
        </div>
    </div>
</div>

<script>
    function dualListMove(fromId, toId, componentId) {
        const from = document.getElementById(fromId);
        const to = document.getElementById(toId);

        [...from.selectedOptions].forEach(option => {
            to.appendChild(option.cloneNode(true));
            option.remove();
        });

        dualListSyncHiddenInput(componentId);
    }

    function dualListFilter(input, selectId) {
        const filter = input.value.toLowerCase();
        const options = document.getElementById(selectId).options;

        for (let o of options) {
            o.style.display = o.text.toLowerCase().includes(filter) ? '' : 'none';
        }
    }

    function dualListSyncHiddenInput(componentId) {
        const rightSelect = document.getElementById('right-' + componentId);
        const hiddenInput = document.getElementById('hidden-' + componentId);

        if (rightSelect && hiddenInput) {
            const selected = [...rightSelect.options].map(opt => opt.value);
            hiddenInput.value = JSON.stringify(selected);
            hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
        }
    }
</script>
