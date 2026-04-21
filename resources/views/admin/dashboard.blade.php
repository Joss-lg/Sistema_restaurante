@extends('layouts.admin')

@section('title', 'Dashboard Financiero | Ollintem Pro')
@section('header-title', 'Dashboard Financiero')
@section('header-subtitle', 'Visión analítica de operaciones')

@section('content')
<div class="p-8 lg:p-10 xl:p-12 max-w-[1800px] mx-auto w-full space-y-8 flex-1 flex flex-col">
    
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
        
        <div class="glass-card rounded-[1.5rem] p-7 flex flex-col justify-between h-44 group">
            <div class="flex justify-between items-start">
                <h3 class="text-[10px] font-bold text-[var(--text-muted)] uppercase tracking-[0.2em]">Ingresos Brutos</h3>
                <div class="w-9 h-9 rounded-xl border border-[var(--border-color)] flex items-center justify-center icon-wrapper group-hover:border-emerald-500/40 transition-all duration-300">
                    <i class="fas fa-wallet text-[var(--text-muted)] text-sm group-hover:text-emerald-400 group-hover:scale-110 transition-all duration-300"></i>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-[2.5rem] leading-none font-black text-metallic tracking-tighter">${{ number_format($stats['ventas_dia'] ?? 0, 2) }}</p>
                <p class="text-[11px] font-bold text-emerald-400 mt-3 flex items-center gap-1.5 opacity-90 tracking-wide">
                    <i class="fas fa-arrow-trend-up"></i> +4.2% vs ayer
                </p>
            </div>
        </div>

        <div class="glass-card rounded-[1.5rem] p-7 flex flex-col justify-between h-44 group">
            <div class="flex justify-between items-start">
                <h3 class="text-[10px] font-bold text-[var(--text-muted)] uppercase tracking-[0.2em]">Volumen de Órdenes</h3>
                <div class="w-9 h-9 rounded-xl border border-[var(--border-color)] flex items-center justify-center icon-wrapper group-hover:border-[#3B82F6]/40 transition-all duration-300">
                    <i class="fas fa-receipt text-[var(--text-muted)] text-sm group-hover:text-[#3B82F6] group-hover:scale-110 transition-all duration-300"></i>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-[2.5rem] leading-none font-black text-metallic tracking-tighter">{{ $stats['ordenes_dia'] ?? 0 }}</p>
                <p class="text-[10px] font-semibold text-[var(--text-muted)] mt-3 uppercase tracking-wide">Transacciones</p>
            </div>
        </div>

        <div class="glass-card rounded-[1.5rem] p-7 flex flex-col justify-between h-44 group">
            <div class="flex justify-between items-start">
                <h3 class="text-[10px] font-bold text-[var(--text-muted)] uppercase tracking-[0.2em]">Ticket Promedio</h3>
                <div class="w-9 h-9 rounded-xl border border-[var(--border-color)] flex items-center justify-center icon-wrapper group-hover:border-indigo-400/40 transition-all duration-300">
                    <i class="fas fa-tag text-[var(--text-muted)] text-sm group-hover:text-indigo-400 group-hover:scale-110 transition-all duration-300"></i>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-[2.5rem] leading-none font-black text-metallic tracking-tighter">$0<span class="text-2xl text-[var(--text-muted)] opacity-50 font-bold">.00</span></p>
                <p class="text-[10px] font-semibold text-[var(--text-muted)] mt-3 uppercase tracking-wide">Por Comensal</p>
            </div>
        </div>

        <div class="glass-card rounded-[1.5rem] p-7 flex flex-col justify-between h-44 group">
            <div class="flex justify-between items-start">
                <h3 class="text-[10px] font-bold text-[var(--text-muted)] uppercase tracking-[0.2em]">Afluencia Total</h3>
                <div class="w-9 h-9 rounded-xl border border-[var(--border-color)] flex items-center justify-center icon-wrapper group-hover:border-orange-400/40 transition-all duration-300">
                    <i class="fas fa-user-friends text-[var(--text-muted)] text-sm group-hover:text-orange-400 group-hover:scale-110 transition-all duration-300"></i>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-[2.5rem] leading-none font-black text-metallic tracking-tighter">0</p>
                <p class="text-[10px] font-semibold text-[var(--text-muted)] mt-3 uppercase tracking-wide">Registros</p>
            </div>
        </div>
    </div>

    <div class="glass-card rounded-[2rem] p-8 lg:p-10 w-full flex-1 flex flex-col min-h-[450px]">
        <div class="flex justify-between items-start mb-8">
            <div>
                <h2 class="text-xl font-black tracking-tight text-[var(--text-color)]">Análisis de Flujo</h2>
                <p class="text-xs text-[var(--text-muted)] font-medium mt-1.5">Métricas de rendimiento a lo largo de la jornada</p>
            </div>
            <button class="flex items-center gap-2.5 text-[11px] font-bold text-[var(--text-color)] border border-[var(--border-color)] px-5 py-2.5 rounded-xl hover:bg-black/5 modo-crema:hover:bg-zinc-100 transition-all shadow-lg group">
                <i class="fas fa-cloud-download-alt text-[#3B82F6] group-hover:scale-110 transition-transform"></i> Exportar
            </button>
        </div>
        
        <div class="w-full relative flex-1 min-h-[300px]">
            <canvas id="salesChart"></canvas>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let myChart; 

    function initChart() {
        const esCrema = document.body.classList.contains('modo-crema');
        const textColor = esCrema ? '#52525b' : '#71717A';
        const gridColor = esCrema ? 'rgba(0, 0, 0, 0.05)' : 'rgba(255, 255, 255, 0.02)';
        const tooltipBg = esCrema ? 'rgba(255, 255, 255, 0.95)' : 'rgba(10, 10, 12, 0.95)';
        const tooltipText = esCrema ? '#18181b' : '#FAFAFA';

        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.color = textColor; 

        const ctx = document.getElementById('salesChart').getContext('2d');
        
        const orangeGlow = ctx.createLinearGradient(0, 0, 0, 400);
        orangeGlow.addColorStop(0, 'rgba(249, 115, 22, 0.25)');
        orangeGlow.addColorStop(1, 'rgba(249, 115, 22, 0)');

        if (myChart) {
            myChart.destroy();
        }

        myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00'],
                datasets: [{
                    label: 'Ingresos Brutos',
                    data: [0, 800, 4863, 2100, 900, 0, 0, 0],
                    borderColor: '#F97316', 
                    backgroundColor: orangeGlow,
                    borderWidth: 3,
                    pointBackgroundColor: esCrema ? '#ffffff' : '#0A0A0C', 
                    pointBorderColor: '#F97316',
                    pointBorderWidth: 2.5,
                    pointRadius: 4,
                    pointHoverRadius: 7,
                    fill: true,
                    tension: 0.45 
                }, {
                    label: 'Transacciones',
                    data: [0, 4, 15, 8, 3, 0, 0, 0],
                    borderColor: '#3B82F6', 
                    borderWidth: 2.5,
                    borderDash: [5, 5], 
                    pointBackgroundColor: esCrema ? '#ffffff' : '#0A0A0C',
                    pointBorderColor: '#3B82F6',
                    pointBorderWidth: 2,
                    pointRadius: 0, 
                    pointHoverRadius: 6,
                    tension: 0.45
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top', align: 'end', labels: { usePointStyle: true, boxWidth: 8, font: { size: 12, weight: '700' }, color: textColor, padding: 25 } },
                    tooltip: { backgroundColor: tooltipBg, titleColor: tooltipText, bodyColor: tooltipText, borderColor: gridColor, borderWidth: 1, padding: 16, cornerRadius: 16, titleFont: { size: 13, weight: '600', family: 'Inter' }, bodyFont: { size: 14, weight: 'bold', family: 'Inter' }, displayColors: true, boxPadding: 8, usePointStyle: true }
                },
                scales: {
                    x: { grid: { display: false }, border: { display: false }, ticks: { font: { weight: '600', size: 11 }, padding: 10 } },
                    y: { beginAtZero: true, grid: { color: gridColor, borderDash: [6, 6] }, border: { display: false }, ticks: { font: { weight: '600', size: 11 }, padding: 15 } }
                }
            }
        });
    }

    // Observar cuando se cambie el tema (modo crema/oscuro) para redibujar la gráfica automáticamente
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.attributeName === "class") {
                initChart();
            }
        });
    });
    observer.observe(document.body, { attributes: true });

    document.addEventListener('DOMContentLoaded', () => {
        initChart();
    });
</script>
@endpush