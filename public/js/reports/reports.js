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
                    opt.textContent = `${dep.nombre}`;
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
    let chartGastos = null;
    let chartPresupuesto = null;
    let chartDependencias = null;

    const renderPie = (canvasId, labels, data, colors) => {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return null;
        const ctx = canvas.getContext('2d');
        return new Chart(ctx, {
            type: 'pie',
            data: { labels, datasets: [{ data, backgroundColor: colors }] },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });
    };

    const palette = ['#4e79a7','#f28e2b','#e15759','#76b7b2','#59a14f','#edc948','#b07aa1','#ff9da7','#9c755f','#bab0ab'];

    const updateCharts = (rows) => {
        let totalInicial = 0, totalSaldo = 0, totalComprometido = 0;
        const porDependencia = new Map();
        rows.forEach(r => {
            const inicial = limpiarNumero(r.valor_inicial);
            const saldo = limpiarNumero(r.saldo_por_comprometer);
            const comprometido = Math.max(inicial - saldo, 0);
            totalInicial += inicial;
            totalSaldo += saldo;
            totalComprometido += comprometido;
            const depName = r.dependencia_descripcion || r.dependencia || 'Sin dependencia';
            porDependencia.set(depName, (porDependencia.get(depName) || 0) + comprometido);
        });

        const totalPresEl = document.getElementById('total-presupuesto');
        if (totalPresEl) totalPresEl.textContent = formatoMoneda(totalInicial);

        if (chartGastos) chartGastos.destroy();
        chartGastos = renderPie('canvas-gastos', ['Comprometido', 'Saldo por Comprometer'], [totalComprometido, totalSaldo], ['#ef5350', '#ffcc80']);

        if (chartPresupuesto) chartPresupuesto.destroy();
        chartPresupuesto = renderPie('canvas-presupuesto', ['Comprometido', 'Saldo por Comprometer'], [totalComprometido, totalSaldo], ['#4e79a7', '#f28e2b']);

        const entries = Array.from(porDependencia.entries()).sort((a,b)=>b[1]-a[1]);
        const top = entries.slice(0,6);
        if (entries.length > 6) {
            const otros = entries.slice(6).reduce((acc,cur)=>acc+cur[1],0);
            top.push(['Otros', otros]);
        }
        const depLabels = top.map(x=>x[0]);
        const depValues = top.map(x=>x[1]);
        const depColors = depLabels.map((_,i)=> palette[i % palette.length]);
        if (chartDependencias) chartDependencias.destroy();
        chartDependencias = renderPie('canvas-dependencias', depLabels, depValues, depColors);
    };

    const buscarYRender = async (depValue, codigoValue) => {
        const params = new URLSearchParams();
        if (depValue) params.set('dependencia', depValue);
        if (codigoValue) params.set('codigo_cdp', codigoValue);
        try {
            const resp = await fetch(`${BASE_URL}reports/consulta?${params.toString()}`);
            const data = await resp.json();
            tbodyDetalles.innerHTML = '';
            if (!Array.isArray(data) || data.length === 0) {
                tbodyDetalles.innerHTML = `<tr><td colspan="16" class="text-center text-muted">Sin resultados</td></tr>`;
                updateCharts([]);
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
                tr.innerHTML = `
                    <td>${index + 1}</td>
                    <td>${row.numero_cdp ?? ''}</td>
                    <td>${row.fecha_registro ?? ''}</td>
                    <td>${row.dependencia ?? ''}</td>
                    <td>${row.dependencia_descripcion ?? ''}</td>
                    <td>${row.concepto_interno ?? ''}</td>
                    <td>${row.rubro ?? ''}</td>
                    <td>${row.descripcion ?? ''}</td>
                    <td>${row.fuente ?? ''}</td>
                    <td>${formatoMoneda(limpiarNumero(row.valor_inicial))}</td>
                    <td>${formatoMoneda(limpiarNumero(row.valor_operaciones))}</td>
                    <td>${formatoMoneda(limpiarNumero(row.valor_actual))}</td>
                    <td>${formatoMoneda(limpiarNumero(row.saldo_por_comprometer))}</td>
                    <td>${formatoMoneda(comprometido)}</td>
                    <td class="${clase}">${porcentaje}%</td>
                    <td>${row.objeto ?? ''}</td>
                `;
                tbodyDetalles.appendChild(tr);
            });
            updateCharts(data);
            return data;
        } catch (err) {
            console.error('Error cargando detalles:', err);
            if (window.Swal) Swal.fire('Error', 'No fue posible cargar los detalles.', 'error');
            return [];
        }
    };

    triggersDetalles.forEach(btn => {
        btn.addEventListener('click', async () => {
            const w = btn.getAttribute('data-week');
            weekLabelDetalles.textContent = w;
            await cargarDependencias();
            tbodyDetalles.innerHTML = '';
            // cargar datos por defecto (sin filtros) para mostrar algo al abrir
            const depValue = (dependenciaSelect.value === 'all') ? '' : dependenciaSelect.value;
            const codigoValue = cdpInput.value.trim();
            await buscarYRender(depValue, codigoValue);
        });
    });

    btnBuscar.addEventListener('click', async (e) => {
        e.preventDefault();
        const depValue = (dependenciaSelect.value === 'all') ? '' : dependenciaSelect.value;
        const codigoValue = cdpInput.value.trim();
        await buscarYRender(depValue, codigoValue);
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

    // Botones Eliminar Semana
    const deleteButtons = document.querySelectorAll('.btn-delete-week');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            const week = btn.getAttribute('data-week');

            const proceed = await (async () => {
                if (window.Swal && typeof window.Swal.fire === 'function') {
                    const res = await window.Swal.fire({
                        title: '¿Eliminar?',
                        text: `¿Seguro que deseas eliminar los datos de ${week}? Esta acción no se puede deshacer.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar'
                    });
                    return res.isConfirmed;
                }
                return window.confirm(`¿Eliminar los datos de ${week}?`);
            })();

            if (!proceed) return;

            // TODO: Reemplazar por petición real al backend
            // Ejemplo con fetch a una ruta de eliminación
            // const resp = await fetch(`${APP_URL}reports/delete`, { method: 'POST', body: new URLSearchParams({ week }) });
            // const json = await resp.json();

            // Mientras tanto, remover la fila de la tabla como feedback
            const row = btn.closest('tr');
            if (row) {
                row.remove();
            }

            if (window.Swal && typeof window.Swal.fire === 'function') {
                window.Swal.fire('Eliminado', `Se eliminaron los datos de ${week}.`, 'success');
            } else {
                alert(`Se eliminaron los datos de ${week}.`);
            }
        });
    });
});