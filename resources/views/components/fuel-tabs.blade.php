{{--
    Componente: fuel-tabs
    Tabs per selezionare il tipo di carburante.
    Props:
        $active   → string ('Benzina', 'Gasolio', 'GPL', 'Metano')
        $mode     → string ('self' | 'served')
        $province → string
        $municipality → string
--}}
@props(['active', 'mode', 'province', 'municipality' => ''])

@php
    $fuels = [
        'Benzina' => ['emoji' => '🟢', 'label' => 'Benzina'],
        'Gasolio' => ['emoji' => '🔵', 'label' => 'Gasolio'],
        'GPL'     => ['emoji' => '🟣', 'GPL'],
        'Metano'  => ['emoji' => '🟠', 'label' => 'Metano'],
    ];
@endphp

<div class="flex gap-2 overflow-x-auto pb-1 -mx-1 px-1 scrollbar-hide">
    @foreach($fuels as $type => $info)
        @php
            $url = route('stations.index', [
                'fuel' => $type, 'mode' => $mode,
                'province' => $province, 'municipality' => $municipality
            ]);
            $isActive = $active === $type;
        @endphp
        <a href="{{ $url }}"
           class="flex-shrink-0 flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-medium
                  transition-colors duration-150
                  {{ $isActive
                      ? 'bg-brand-700 text-white shadow-sm'
                      : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
            <span>{{ $info['emoji'] }}</span>
            <span>{{ $info['label'] ?? $type }}</span>
        </a>
    @endforeach
</div>
