@props(['history'])

<div class="bg-white p-4 rounded-xl border border-slate-200">
    <canvas id="priceChart" class="w-full h-64"></canvas>
</div>

<script src="https://jsdelivr.net"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('priceChart').getContext('2d');
        const historyData = @json($history);

        const datasets = [];
        const colors = {
            'Benzina': '#ef4444',
            'Gasolio': '#3b82f6',
            'GPL': '#10b981',
            'Metano': '#f59e0b'
        };

        let labels = [];

        Object.keys(historyData).forEach(fuelType => {
            const dataPoints = historyData[fuelType];
            
            if (labels.length === 0) {
                labels = dataPoints.map(p => new Date(p.recorded_at).toLocaleDateString('it-IT', {day: 'numeric', month: 'short'}));
            }

            datasets.push({
                label: fuelType,
                data: dataPoints.map(p => p.price),
                borderColor: colors[fuelType] || '#6b7280',
                backgroundColor: 'transparent',
                borderWidth: 3,
                tension: 0.2,
                pointRadius: 4
            });
        });

        new Chart(ctx, {
            type: 'line',
            data: { labels, datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } },
                scales: {
                    y: {
                        ticks: { callback: value => '€ ' + value.toFixed(3) }
                    }
                }
            }
        });
    });
</script>
