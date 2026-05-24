@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6">
    
    <!-- Filtri Superiori -->
    <form method="GET" action="{{ route('stations.index') }}" class="bg-white p-4 rounded-xl border border-slate-200 mb-6 shadow-sm space-y-4">
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
            <div>
                <label for="fuel_type_select" class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">Carburante</label>
                <select id="fuel_type_select" name="fuel_type" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2.5 text-sm font-medium focus:ring-2 focus:ring-blue-500">
                    @foreach(['Benzina', 'Gasolio', 'GPL', 'Metano'] as $type)
                        <option value="{{ $type }}" {{ $fuelType == $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="municipality_select" class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">Comune</label>
                <select id="municipality_select" name="municipality" onchange="this.form.submit()" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2.5 text-sm font-medium focus:ring-2 focus:ring-blue-500">
                    <option value="">Tutti i comuni</option>
                    @foreach($comuni as $com)
                        <option value="{{ $com }}" {{ (string)$municipality === (string)$com ? 'selected' : '' }}>
                            {{ ucfirst(strtolower($com)) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-span-2 md:col-span-1">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">Erogazione</span>
                <div class="grid grid-cols-2 gap-1 bg-slate-100 p-1 rounded-lg">
                    <button type="submit" name="is_self" value="1" class="py-1.5 text-xs font-semibold rounded-md transition-all {{ $isSelf ? 'bg-white shadow text-slate-900' : 'text-slate-500' }}">Self</button>
                    <button type="submit" name="is_self" value="0" class="py-1.5 text-xs font-semibold rounded-md transition-all {{ !$isSelf ? 'bg-white shadow text-slate-900' : 'text-slate-500' }}">Servito</button>
                </div>
            </div>
        </div>
    </form>

    <!-- Titolo Sezione Lista -->
    <div class="mb-4">
        <h2 class="text-lg font-bold text-slate-800">Distributori Disponibili ({{ $stations->count() }})</h2>
    </div>

    <!-- Lista dei Distributori -->
    <div class="space-y-3">
        @forelse($stations as $index => $station)
            <x-station-card :station="$station" :index="$index" :is-cheapest="$loop->first" />
        @empty
            <div class="text-center py-12 bg-white rounded-xl border border-slate-200 text-slate-400">Nessun distributore trovato con i filtri selezionati.</div>
        @endforelse
    </div>

</div>
@endsection
