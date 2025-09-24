    // Script para manejar la apertura del modal y actualizar el título dinámicamente
document.addEventListener('DOMContentLoaded', function(){
    // Modal Subir Reporte
    const modal = document.getElementById('modalReporte');
    const weekLabel = document.getElementById('modal-week-label');
    const inputWeek = document.getElementById('input-week');
    const triggers = document.querySelectorAll('.btn-open-modal');

    triggers.forEach(btn => {
        btn.addEventListener('click', () => {
            const w = btn.getAttribute('data-week');
            weekLabel.textContent = '- ' + w;
            inputWeek.value = w;
        });
    });

    // Modal Ver Detalles
    const modalDetalles = document.getElementById('modalDetalles');
    const weekLabelDetalles = document.getElementById('modal-detalles-week-label');
    const triggersDetalles = document.querySelectorAll('.btn-ver-detalles');
    const dependenciaSelect = document.getElementById('modal-dependency-select');
    const cdpInput = document.getElementById('modal-cdp-input');
    const btnBuscar = document.getElementById('btn-modal-buscar');
    const tbodyDetalles = document.getElementById('tabla-detalles-body');

    // Cargar dependencias al abrir el modal (una sola vez)
    let depsCargadas = false;
    const cargarDependencias = async () => {
        if (depsCargadas) return;
        try {
            const resp = await fetch(`${BASE_URL}reports/dependencias`);
            const data = await resp.json();
            if (Array.isArray(data)) {
                data.forEach(dep => {
                    const opt = document.createElement('option');
                    opt.value = dep.codigo;
                    // Mostrar código y nombre
                    opt.textContent = `${dep.codigo} - ${dep.nombre}`;
                    dependenciaSelect.appendChild(opt);
                });
                depsCargadas = true;
            }
        } catch (e) { console.error(e); }
    };

    // Utilidades de formato usadas por buscarYRender
    const limpiarNumero = (valor) => {
        if (!valor) return 0;
        return parseFloat(String(valor).replace(/[^0-9.-]+/g, "")) || 0;
    };

    const formatoMoneda = (valor) => new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 }).format(valor);

    // Gestión de gráficas
    let chartGastos = null;        // Barra apilada (Comprometido vs Saldo)
    let chartPresupuesto = null;   // Barras múltiples
    let chartDependencias = null;  // Barra horizontal dependencias

    const disposeChart = (chartRef) => {
        if (chartRef && typeof chartRef.destroy === 'function') {
            chartRef.destroy();
        }
        return null;
    };

    const palette = ['#4e79a7','#f28e2b','#e15759','#76b7b2','#59a14f','#edc948','#b07aa1','#ff9da7','#9c755f','#bab0ab'];

    // Agregación de filas en un solo lugar para reutilizar en charts y mini chart
    const aggregateRows = (rows) => {
        const acc = {
            totalInicial: 0,
            totalSaldo: 0,
            totalComprometido: 0,
            totalOperaciones: 0,
            totalActual: 0,
            porDependencia: new Map()
        };
        rows.forEach(r => {
            const inicial = limpiarNumero(r.valor_inicial);
            const saldo = limpiarNumero(r.saldo_por_comprometer);
            const operaciones = limpiarNumero(r.valor_operaciones);
            const actual = limpiarNumero(r.valor_actual);
            const comprometido = Math.max(inicial - saldo, 0);
            acc.totalInicial += inicial;
            acc.totalSaldo += saldo;
            acc.totalComprometido += comprometido;
            acc.totalOperaciones += operaciones;
            acc.totalActual += actual;
            const depName = r.dependencia_descripcion || r.dependencia || 'Sin dependencia';
            acc.porDependencia.set(depName, (acc.porDependencia.get(depName) || 0) + comprometido);
        });
        return acc;
    };

    const updateCharts = (rows) => {
        const { totalInicial, totalSaldo, totalComprometido, totalOperaciones, totalActual, porDependencia } = aggregateRows(rows);

        const totalPresEl = document.getElementById('total-presupuesto');
        if (totalPresEl) totalPresEl.textContent = formatoMoneda(totalInicial);

        // Chart 1: Distribución (barra apilada)
        chartGastos = disposeChart(chartGastos);
        const canvasGastos = document.getElementById('canvas-gastos');
        if (canvasGastos) {
            chartGastos = new Chart(canvasGastos.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: ['Total Global'],
                    datasets: [
                        { label: 'Comprometido', data: [totalComprometido], backgroundColor: '#ef5350' },
                        { label: 'Saldo por Comprometer', data: [totalSaldo], backgroundColor: '#ffcc80' }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'bottom' }, tooltip: { mode: 'index', intersect: false } },
                    interaction: { mode: 'index', intersect: false },
                    scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } }
                }
            });
        }

        // Chart 2: Estado Presupuesto (multi barras)
        chartPresupuesto = disposeChart(chartPresupuesto);
        const canvasPres = document.getElementById('canvas-presupuesto');
        if (canvasPres) {
            chartPresupuesto = new Chart(canvasPres.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: ['Presupuesto'],
                    datasets: [
                        { label: 'Inicial', data: [totalInicial], backgroundColor: '#4e79a7' },
                        { label: 'Operaciones', data: [totalOperaciones], backgroundColor: '#f28e2b' },
                        { label: 'Actual', data: [totalActual], backgroundColor: '#59a14f' },
                        { label: 'Comprometido', data: [totalComprometido], backgroundColor: '#e15759' },
                        { label: 'Saldo', data: [totalSaldo], backgroundColor: '#b07aa1' }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'bottom' }, tooltip: { mode: 'index', intersect: false } },
                    interaction: { mode: 'index', intersect: false },
                    scales: { y: { beginAtZero: true } }
                }
            });
        }

        // Chart 3: Dependencias (horizontal)
        chartDependencias = disposeChart(chartDependencias);
        const entries = Array.from(porDependencia.entries()).sort((a,b)=>b[1]-a[1]);
        const top = entries.slice(0,10);
        if (entries.length > 10) {
            const otros = entries.slice(10).reduce((acc,cur)=>acc+cur[1],0);
            top.push(['Otros', otros]);
        }
        const depLabels = top.map(x=>x[0]);
        const depValues = top.map(x=>x[1]);
        const canvasDeps = document.getElementById('canvas-dependencias');
        if (canvasDeps) {
            chartDependencias = new Chart(canvasDeps.getContext('2d'), {
                type: 'bar',
                data: { labels: depLabels, datasets: [{ label: 'Comprometido', data: depValues, backgroundColor: depLabels.map((_,i)=> palette[i % palette.length]) }] },
                options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false }, tooltip: { mode: 'nearest', intersect: true } }, scales: { x: { beginAtZero: true } } }
            });
        }
    };

    // Mini gráfica dependencia específica
    let miniChart = null;
    const miniContainer = () => document.getElementById('mini-presupuesto-container');
    const miniCanvas = () => document.getElementById('mini-presupuesto-chart');
    const miniLabel = () => document.getElementById('mini-presupuesto-label');
    const hideMiniBtn = () => document.getElementById('mini-hide-btn');

    const renderMiniChart = (rows, dependenciaTxt) => {
        if (!miniCanvas()) return;
        if (miniChart) { miniChart.destroy(); miniChart = null; }
        if (!rows || rows.length === 0) { if (miniContainer()) miniContainer().style.display = 'none'; return; }
        const { totalInicial, totalSaldo, totalComprometido, totalActual } = aggregateRows(rows);
        if (miniContainer()) miniContainer().style.display = 'block';
        if (miniLabel()) miniLabel().textContent = dependenciaTxt || '';
        miniChart = new Chart(miniCanvas().getContext('2d'), {
            type: 'bar',
            data: { labels: ['Inicial','Actual','Comprometido','Saldo'], datasets: [{ label: 'Valores', data: [totalInicial, totalActual, totalComprometido, totalSaldo], backgroundColor: ['#4e79a7','#59a14f','#e15759','#b07aa1'] }] },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
        if (hideMiniBtn()) hideMiniBtn().onclick = () => { if (miniContainer()) miniContainer().style.display = 'none'; };
    };

    const buscarYRender = async (depValue) => {
        const params = new URLSearchParams();
        if (depValue) params.set('dependencia', depValue);
        try {
            const resp = await fetch(`${BASE_URL}reports/consulta?${params.toString()}`);
            const data = await resp.json();
            tbodyDetalles.innerHTML = '';
            if (!Array.isArray(data) || data.length === 0) {
                tbodyDetalles.innerHTML = `<tr><td colspan="14" class="text-center text-muted">Sin resultados</td></tr>`;
                return [];
            }
            data.forEach((row, index) => {
                const inicial = limpiarNumero(row.valor_inicial);
                const saldo = limpiarNumero(row.saldo_por_comprometer);
                const comprometido = inicial - saldo;
                const porcentaje = inicial > 0 ? ((comprometido / inicial) * 100).toFixed(2) : 0;
                let clase = 'rojo';
                if (comprometido === inicial && saldo === 0) clase = 'verde'; else if (comprometido > 0) clase = 'naranja';
                const tr = document.createElement('tr');
                // Sanitizar texto para evitar inyección simple (reemplazar < y >)
                const safe = (txt) => (txt ?? '').toString().replace(/</g,'&lt;').replace(/>/g,'&gt;');
                tr.innerHTML = `
                    <td>${index + 1}</td>
                    <td>${safe(row.numero_cdp)}</td>
                    <td>${safe(row.fecha_registro)}</td>
                    <td>${safe(row.dependencia)}</td>
                    <td>${safe(row.dependencia_descripcion)}</td>
                    <td>${safe(row.concepto_interno)}</td>
                    <td>${safe(row.rubro)}</td>
                    <td class="cell-textarea"><textarea readonly spellcheck="false">${safe(row.descripcion)}</textarea></td>
                    <td>${safe(row.fuente)}</td>
                    <td>${formatoMoneda(limpiarNumero(row.valor_actual))}</td>
                    <td>${formatoMoneda(limpiarNumero(row.saldo_por_comprometer))}</td>
                    <td>${formatoMoneda(comprometido)}</td>
                    <td class="${clase}">${porcentaje}%</td>
                    <td class="cell-textarea"><textarea readonly spellcheck="false">${safe(row.objeto)}</textarea></td>
                `;
                tbodyDetalles.appendChild(tr);
            });
            return data;
        } catch (err) {
            console.error('Error cargando detalles:', err);
            if (window.Swal) Swal.fire('Error', 'No fue posible cargar los detalles.', 'error');
            return [];
        }
    };

    // Cargar siempre datos globales para panel principal al abrir la página (Todas)
    // Se hace una consulta inicial sin parámetros solo si aún no se ha hecho.
    let globalLoaded = false;
    const loadGlobalCharts = async () => {
        if (globalLoaded) return; // evitar recarga innecesaria
        globalLoaded = true;
        try {
            const resp = await fetch(`${BASE_URL}reports/consulta`);
            const data = await resp.json();
            if (Array.isArray(data)) updateCharts(data);
        } catch (e) { console.error('No se pudo cargar datos globales', e); }
    };
    loadGlobalCharts();

    triggersDetalles.forEach(btn => {
        btn.addEventListener('click', async () => {
            const w = btn.getAttribute('data-week');
            weekLabelDetalles.textContent = w;
            await cargarDependencias();
            tbodyDetalles.innerHTML = '';
            const depValue = (dependenciaSelect.value === 'all') ? '' : dependenciaSelect.value;
            const data = await buscarYRender(depValue);
            if (depValue) renderMiniChart(data, depValue); else renderMiniChart([], '');
        });
    });

    btnBuscar.addEventListener('click', async (e) => {
        e.preventDefault();
        const depValue = (dependenciaSelect.value === 'all') ? '' : dependenciaSelect.value;
        const data = await buscarYRender(depValue);
        if (depValue) renderMiniChart(data, depValue); else renderMiniChart([], '');
    });

    // Selector de Gráficas
    const chartSelect = document.getElementById('chart-select');
    const chartContainers = document.querySelectorAll('.chart-container');

    chartSelect.addEventListener('change', function() {
        const selectedChart = this.value;
        
        // Ocultar todas las gráficas
        chartContainers.forEach(container => {
            container.style.display = 'none';
        });
        
        // Mostrar la gráfica seleccionada
        const selectedContainer = document.getElementById('chart-' + selectedChart);
        if (selectedContainer) {
            selectedContainer.style.display = 'block';
        }
        
        console.log('Gráfica seleccionada: ' + selectedChart);
    });

    // Forzar estado inicial (presupuesto visible)
    (()=>{
        const preset = 'presupuesto';
        chartContainers.forEach(c=>{ c.style.display = (c.id === 'chart-' + preset) ? 'block' : 'none'; });
        chartSelect.value = preset;
    })();
});