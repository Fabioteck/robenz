@props(['station', 'index', 'isCheapest' => false])

@php
    // Se è un array, lo convertiamo in un oggetto generico fluido per Blade
    if (is_array($station)) {
        $station = (object) $station;
    }

    // Gestione sicura dei metodi o fallback dalle proprietà dell'oggetto
    $freshness = method_exists($station, 'freshnessStatus') ? $station->freshnessStatus() : 'green';
    $freshnessColor = match($freshness) {
        'green'  => 'bg-emerald-400',
        'yellow' => 'bg-amber-400',
        'red'    => 'bg-red-400',
        default  => 'bg-slate-300',
    };
    $freshnessTitle = match($freshness) {
        'green'  => 'Aggiornato nelle ultime 24h',
        'yellow' => 'Aggiornato ieri',
        'red'    => 'Non aggiornato da 2+ giorni',
        default  => 'Data sconosciuta',
    };

    $managerLabel = method_exists($station, 'managerLabel') ? $station->managerLabel() : ($station->name ?? $station->manager ?? 'Distributore');
    $displayMunicipality = $station->municipality_name ?? ($station->municipality ?? '');
@endphp

{{-- MODIFICATO: Passiamo esplicitamente l'ID ($station->id) al posto dell'intero oggetto --}}
<a href="{{ route('stations.show', $station->id) }}"
   class="fade-up block bg-white rounded-2xl shadow-sm border border-slate-100
          hover:shadow-md hover:border-brand-200 transition-all duration-200
          {{ $isCheapest ? 'ring-2 ring-emerald-400' : '' }}">

    <div class="flex items-center gap-4 p-4">

        {{-- Rank / Posizione --}}
        <div class="flex-shrink-0 w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold
                    {{ $isCheapest ? 'bg-emerald-500 text-white' : 'bg-slate-100 text-slate-500' }}">
            {{ $index + 1 }}
        </div>

        {{-- Info principale --}}
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-0.5">
                {{-- Semaforo freschezza --}}
                <span class="inline-block w-2.5 h-2.5 rounded-full flex-shrink-0 {{ $freshnessColor }}"
                      title="{{ $freshnessTitle }}"></span>

                <span class="font-semibold text-slate-800 truncate text-sm leading-tight">
                    {{ $managerLabel }}
                </span>

                @if($isCheapest)
                    <span class="flex-shrink-0 text-xs font-bold bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">
                        🏆 Più economico
                    </span>
                @endif
            </div>

            <p class="text-xs text-slate-400 truncate">
                {{ $station->address }}, {{ ucfirst(strtolower($displayMunicipality)) }}
            </p>
        </div>

        {{-- Prezzo --}}
        <div class="flex-shrink-0 text-right">
            <div class="text-xl font-bold {{ $isCheapest ? 'text-emerald-600' : 'text-slate-800' }}">
                {{ number_format($station->price, 3, ',', '.') }}
            </div>
            <div class="text-xs text-slate-400">€/L</div>
        </div>

        {{-- Freccia --}}
        <div class="flex-shrink-0 text-slate-300 ml-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </div>
    </div>
</a>
