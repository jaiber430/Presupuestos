// Script completo para reports - Solo quitamos la validación de archivos
document.addEventListener('DOMContentLoaded', function(){
    // Modal Subir Reporte
    const modal = document.getElementById('modalReporte');
    const weekLabel = document.getElementById('modal-week-label');
    const inputWeek = document.getElementById('input-week');
    const inputSemanaId = document.getElementById('input-semana-id');
    const triggers = document.querySelectorAll('.btn-open-modal');

    triggers.forEach(btn => {
        btn.addEventListener('click', () => {
            const w = btn.getAttribute('data-week');
            const semanaId = btn.getAttribute('data-semana-id');
            weekLabel.textContent = '- ' + w;
            inputWeek.value = w;
            if (inputSemanaId) {
                inputSemanaId.value = semanaId;
            }
        });
    });

    // Modal Ver Detalles
    const modalDetalles = document.getElementById('modalDetalles');
    const weekLabelDetalles = document.getElementById('modal-detalles-week-label');
    const triggersDetalles = document.querySelectorAll('.btn-ver-detalles');
    const dependenciaInput = document.getElementById('modal-dependency-input');
    const dependenciaDataList = document.getElementById('dependencias-list');
    const btnBuscar = document.getElementById('btn-modal-buscar');
    const tbodyDetalles = document.getElementById('tabla-detalles-body');

    // Cargar dependencias al abrir el modal
    let depsCargadas = false;
    const cargarDependencias = async () => {
        if (depsCargadas) return;
        try {
            const resp = await fetch(`${BASE_URL}reports/dependencias`);
            const data = await resp.json();
            if (Array.isArray(data)) {
                if (dependenciaDataList) dependenciaDataList.innerHTML = '';
                data.forEach(dep => {
                    const opt = document.createElement('option');
                    opt.value = dep.codigo;
                    opt.label = `${dep.codigo} - ${dep.nombre}`;
                    opt.textContent = `${dep.codigo} - ${dep.nombre}`;
                    dependenciaDataList.appendChild(opt);
                });
                depsCargadas = true;
            }
        } catch (e) { console.error(e); }
    };

    // Utilidades de formato
    const limpiarNumero = (valor) => {
        if (!valor) return 0;
        return parseFloat(String(valor).replace(/[^0-9.-]+/g, "")) || 0;
    };

    const formatoMoneda = (valor) => new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 }).format(valor);

    // Gestión de gráficas
    let chartGastos = null;
    let chartPresupuesto = null;
    let chartDependencias = null;

    const disposeChart = (chartRef) => {
        if (chartRef && typeof chartRef.destroy === 'function') {
            chartRef.destroy();
        }
        return null;
    };

    const palette = ['#4e79a7','#f28e2b','#e15759','#76b7b2','#59a14f','#edc948','#b07aa1','#ff9da7','#9c755f','#bab0ab'];

    // Agregación de filas
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

        // Chart 1: Distribución
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

        // Chart 2: Estado Presupuesto
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

        // Chart 3: Dependencias
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
                const safe = (txt) => (txt ?? '').toString().replace(/</g,'&lt;').replace(/>/g,'&gt;');
                tr.innerHTML = `
                    <td>${safe(row.numero_cdp)}</td>
                    <td>${safe(row.fecha_registro)}</td>
                    <td class="cell-textarea"><textarea readonly spellcheck="false">${safe(row.concepto_interno)}</textarea></td>
                    <td class="cell-textarea"><textarea readonly spellcheck="false">${safe(row.rubro)}</textarea></td>
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

    // Cargar datos globales
    let globalLoaded = false;
    const loadGlobalCharts = async () => {
        if (globalLoaded) return;
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
            const raw = dependenciaInput.value.trim();
            const depValue = raw === '' ? '' : raw;
            const data = await buscarYRender(depValue);
            if (depValue) renderMiniChart(data, depValue); else renderMiniChart([], '');
        });
    });

    btnBuscar.addEventListener('click', async (e) => {
        e.preventDefault();
        const raw = dependenciaInput.value.trim();
        const depValue = raw === '' ? '' : raw;
        const data = await buscarYRender(depValue);
        if (depValue) renderMiniChart(data, depValue); else renderMiniChart([], '');
    });

    // Selector de Gráficas
    const chartSelect = document.getElementById('chart-select');
    const chartContainers = document.querySelectorAll('.chart-container');

    chartSelect.addEventListener('change', function() {
        const selectedChart = this.value;
        chartContainers.forEach(container => {
            container.style.display = 'none';
        });
        const selectedContainer = document.getElementById('chart-' + selectedChart);
        if (selectedContainer) {
            selectedContainer.style.display = 'block';
        }
    });

    // Forzar estado inicial
    (()=>{
        const preset = 'presupuesto';
        chartContainers.forEach(c=>{ c.style.display = (c.id === 'chart-' + preset) ? 'block' : 'none'; });
        chartSelect.value = preset;
    })();

    // SUBIDA DE ARCHIVOS SIMPLIFICADA - Sin validación de mierda
    const formReporte = document.getElementById('formReporte');
    if (formReporte) {
        formReporte.addEventListener('submit', async function(e) {
            e.preventDefault();

            const weekValue = inputWeek.value;
            const semanaIdValue = inputSemanaId ? inputSemanaId.value : '';
            
            if (!weekValue || !semanaIdValue) {
                Swal.fire('Error', 'Datos incompletos.', 'error');
                return;
            }

            const fileCdp = document.getElementById('file-cdp').files[0];
            const fileRp = document.getElementById('file-rp').files[0];
            const filePagos = document.getElementById('file-pagos').files[0];
            
            if (!fileCdp && !fileRp && !filePagos) {
                Swal.fire('Error', 'Debe seleccionar al menos un archivo.', 'warning');
                return;
            }

            // Mostrar carga directamente
            let loadingAlert = Swal.fire({
                title: 'Subiendo archivos...',
                html: `
                    <div class="text-center">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Subiendo...</span>
                        </div>
                        <p class="mb-1">Procesando archivos Excel</p>
                    </div>
                `,
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => { Swal.showLoading(); }
            });

            try {
                const formData = new FormData();
                formData.append('week', weekValue);
                formData.append('semana_id', semanaIdValue);
                if (fileCdp) formData.append('cdp', fileCdp);
                if (fileRp) formData.append('rp', fileRp);
                if (filePagos) formData.append('pagos', filePagos);

                const response = await fetch(formReporte.action, {
                    method: 'POST',
                    body: formData
                });

                Swal.close();
                const result = await response.json();

                Swal.fire({
                    title: result.titulo,
                    text: result.texto,
                    icon: result.icono,
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    if (result.icono === 'success') {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('modalReporte'));
                        if (modal) modal.hide();
                        window.location.reload();
                    }
                });

            } catch (error) {
                console.error('Error:', error);
                Swal.close();
                Swal.fire('Error', 'Ocurrió un error al subir los archivos.', 'error');
            }
        });
    }

    // Botones Eliminar Semana
    const deleteButtons = document.querySelectorAll('.btn-delete-week');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const week = btn.getAttribute('data-week') || '';
            const semanaId = btn.getAttribute('data-semana-id') || '';
            
            if (!week || !semanaId) {
                Swal.fire('Error', 'Faltan datos.', 'error');
                return;
            }
            
            Swal.fire({
                title: '¿Eliminar datos?',
                text: `Se eliminarán los datos de ${week}.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(async (res) => {
                if (!res.isConfirmed) return;
                try {
                    const deleteAlert = Swal.fire({
                        title: 'Eliminando...',
                        html: `
                            <div class="text-center">
                                <div class="spinner-border text-danger mb-3" role="status">
                                    <span class="visually-hidden">Eliminando...</span>
                                </div>
                                <p>Eliminando datos</p>
                            </div>
                        `,
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    const formData = new FormData();
                    formData.append('week', week);
                    formData.append('semana_id', semanaId);
                    
                    const resp = await fetch(`${BASE_URL}reports/delete`, { 
                        method: 'POST', 
                        body: formData 
                    });
                    
                    const data = await resp.json();
                    
                    Swal.close();
                    
                    Swal.fire(data.titulo || 'Resultado', data.texto || '', data.icono || 'info');
                    
                    if (data.icono === 'success') {
                        if (tbodyDetalles) tbodyDetalles.innerHTML = '';
                        chartGastos = disposeChart(chartGastos);
                        chartPresupuesto = disposeChart(chartPresupuesto);
                        chartDependencias = disposeChart(chartDependencias);
                        
                        if (document.getElementById('total-presupuesto')) {
                            document.getElementById('total-presupuesto').textContent = formatoMoneda(0);
                        }
                        
                        globalLoaded = false;
                        await loadGlobalCharts();
                        
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    }
                } catch (e) {
                    console.error('Error al eliminar:', e);
                    Swal.fire('Error', 'No se pudo eliminar.', 'error');
                }
            });
        });
    });
});