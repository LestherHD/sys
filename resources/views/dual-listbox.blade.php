@php
    $options = $getOptions();
    $selected = collect($getState() ?? []);
@endphp

<div class="flex gap-4 w-full">
    {{-- LISTA IZQUIERDA --}}
    <div class="w-1/2">
        <label class="font-medium text-sm">Opciones</label>
        <input type="text" class="w-full mt-1 mb-2 rounded"
               placeholder="Buscar..."
               onkeyup="dualListFilter(this, 'left-{{ $id }}')">

        <select multiple id="left-{{ $id }}" class="w-full h-56 rounded border p-1">
            @foreach ($options as $key => $value)
                @if (! $selected->contains($key))
                    <option value="{{ $key }}">{{ $value }}</option>
                @endif
            @endforeach
        </select>
    </div>

    {{-- BOTONES --}}
    <div class="flex flex-col justify-center gap-2">
        <button type="button"
                onclick="dualListMove('left-{{ $id }}', 'right-{{ $id }}')"
                class="px-3 py-1 bg-gray-700 text-white rounded">></button>

        <button type="button"
                onclick="dualListMove('right-{{ $id }}', 'left-{{ $id }}')"
                class="px-3 py-1 bg-gray-700 text-white rounded"><</button>
    </div>

    {{-- LISTA DERECHA --}}
    <div class="w-1/2">
        <label class="font-medium text-sm">Seleccionados</label>

        <input type="text" class="w-full mt-1 mb-2 rounded"
               placeholder="Buscar..."
               onkeyup="dualListFilter(this, 'right-{{ $id }}')">

        <select multiple id="right-{{ $id }}" class="w-full h-56 rounded border p-1">
            @foreach ($options as $key => $value)
                @if ($selected->contains($key))
                    <option value="{{ $key }}">{{ $value }}</option>
                @endif
            @endforeach
        </select>

        {{-- Hidden input sync --}}
        <input type="hidden" {{ $applyStateBindingModifiers('wire:model') }}="{{ $getStatePath() }}">
    </div>
</div>

<script>
    function dualListMove(fromId, toId) {
        const from = document.getElementById(fromId);
        const to = document.getElementById(toId);

        [...from.selectedOptions].forEach(option => {
            to.appendChild(option.cloneNode(true));
            option.remove();
        });

        dualListSyncHiddenInput();
    }

    function dualListFilter(input, selectId) {
        const filter = input.value.toLowerCase();
        const options = document.getElementById(selectId).options;

        for (let o of options) {
            o.style.display = o.text.toLowerCase().includes(filter) ? '' : 'none';
        }
    }

    function dualListSyncHiddenInput() {
        document.querySelectorAll("input[type=hidden]").forEach(hidden => {
            const selected = [...document.getElementById("right-" + hidden.id)?.options]
                .map(opt => opt.value);
            hidden.value = JSON.stringify(selected);
            hidden.dispatchEvent(new Event('input'));
        });
    }
</script>
