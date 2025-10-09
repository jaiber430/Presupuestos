document.addEventListener('DOMContentLoaded', function () {
    // ============================
    // MODAL SUBIR REPORTE
    // ============================
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

    // ============================
    // MODAL VER DETALLES
    // ============================
    const modalDetalles = document.getElementById('modalDetalles');
    const weekLabelDetalles = document.getElementById('modal-detalles-week-label');
    const triggersDetalles = document.querySelectorAll('.btn-ver-detalles');
    const dependenciaInput = document.getElementById('modal-dependency-input');
    const dependenciaDataList = document.getElementById('dependencias-list');
    const btnBuscar = document.getElementById('btn-modal-buscar');
    const tbodyDetalles = document.getElementById('tabla-detalles-body');
    const filtroConceptoSelect = document.getElementById('filtro-concepto');

    // Instancia del modal (Bootstrap 5)
    const modalDetallesInstance = new bootstrap.Modal(modalDetalles);

    // Almacenar dependencias y datos globales
    let dependencias = [];
    let datosGlobales = null;
    let datosFiltradosActuales = [];

    // ============================
    // UTILIDADES
    // ============================
    const limpiarNumero = (valor) => {
        if (!valor) return 0;
        return parseFloat(String(valor).replace(/[^0-9.-]+/g, "")) || 0;
    };

    const formatoMoneda = (valor) => new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0
    }).format(valor);

    // ============================
    // ACTUALIZAR FILTRO DE BUSCAR POR SEGÚN SELECCIÓN
    // ============================
    const actualizarFiltroBuscarPor = async (tipoFiltro) => {
        console.log('Tipo de filtro seleccionado:', tipoFiltro);
        
        // Limpiar el input y datalist
        dependenciaInput.value = '';
        dependenciaInput.placeholder = 'Seleccione un filtro primero...';
        dependenciaInput.disabled = true;
        dependenciaDataList.innerHTML = '';

        switch(tipoFiltro) {
            case '1': // Dependencia
                console.log('Cargando dependencias...');
                dependenciaInput.placeholder = 'Buscar dependencia...';
                dependenciaInput.disabled = false;
                try {
                    const resp = await fetch(`${BASE_URL}reports/dependencias`);
                    const data = await resp.json();
                    console.log('Dependencias cargadas:', data);
                    if (Array.isArray(data)) {
                        data.forEach(dep => {
                            const opt = document.createElement('option');
                            opt.value = dep.codigo;
                            opt.textContent = `${dep.codigo} - ${dep.nombre}`;
                            dependenciaDataList.appendChild(opt);
                        });
                    }
                } catch (error) {
                    console.error('Error cargando dependencias:', error);
                }
                break;
                
            case '2': // Numero CDP
                console.log('Cargando CDPs...');
                dependenciaInput.placeholder = 'Buscar número CDP...';
                dependenciaInput.disabled = false;
                try {
                    const resp = await fetch(`${BASE_URL}reports/cdps`);
                    const data = await resp.json();
                    console.log('CDPs cargados:', data);
                    if (Array.isArray(data)) {
                        data.forEach(cdp => {
                            const opt = document.createElement('option');
                            opt.value = cdp;
                            opt.textContent = cdp;
                            dependenciaDataList.appendChild(opt);
                        });
                    }
                } catch (error) {
                    console.error('Error cargando CDPs:', error);
                }
                break;
                
            case '3': // Concepto
                console.log('Cargando conceptos...');
                dependenciaInput.placeholder = 'Buscar concepto...';
                dependenciaInput.disabled = false;
                try {
                    const resp = await fetch(`${BASE_URL}reports/conceptos`);
                    const data = await resp.json();
                    console.log('Conceptos cargados:', data);
                    if (Array.isArray(data)) {
                        data.forEach(concepto => {
                            const opt = document.createElement('option');
                            opt.value = concepto;
                            opt.textContent = concepto;
                            dependenciaDataList.appendChild(opt);
                        });
                    }
                } catch (error) {
                    console.error('Error cargando conceptos:', error);
                }
                break;
                
            default:
                dependenciaInput.placeholder = 'Seleccione un filtro primero...';
                dependenciaInput.disabled = true;
                break;
        }
    };

    // ============================
    // BUSCAR Y RENDER TABLA
    // ============================
    const buscarYRender = async () => {
        // Obtener valores de todos los filtros
        const conceptoValue = filtroConceptoSelect.value;
        const buscarPorValue = dependenciaInput.value.trim();

        // Construir parámetros de búsqueda
        const params = new URLSearchParams();
        
        // Solo agregar el filtro de "buscar por" si hay un tipo seleccionado y un valor
        if (conceptoValue && buscarPorValue) {
            switch(conceptoValue) {
                case '1': // Dependencia
                    params.set('dependencia', buscarPorValue);
                    break;
                case '2': // Numero CDP
                    params.set('numero_cdp', buscarPorValue);
                    break;
                case '3': // Concepto
                    params.set('concepto_interno', buscarPorValue);
                    break;
            }
        }

        try {
            const resp = await fetch(`${BASE_URL}reports/consulta?${params.toString()}`);
            const data = await resp.json();
            tbodyDetalles.innerHTML = '';
            
            if (!Array.isArray(data) || data.length === 0) {
                tbodyDetalles.innerHTML = `<tr><td colspan="11" class="text-center text-muted py-5">Sin resultados para los filtros aplicados</td></tr>`;
                datosFiltradosActuales = [];
                return [];
            }

            // Guardar datos filtrados
            datosFiltradosActuales = data;

            // Actualizar contador de resultados
            const contador = document.getElementById('contador-resultados');
            const filasMostradas = document.getElementById('filas-mostradas');
            if (contador) contador.textContent = data.length;
            if (filasMostradas) filasMostradas.textContent = data.length;

            // Calcular total presupuesto para el footer
            const totalPresupuesto = data.reduce((sum, row) => sum + limpiarNumero(row.valor_inicial), 0);
            const totalPresupuestoFooter = document.getElementById('total-presupuesto-footer');
            if (totalPresupuestoFooter) totalPresupuestoFooter.textContent = formatoMoneda(totalPresupuesto);

            // Renderizar filas de la tabla
            data.forEach(row => {
                const inicial = limpiarNumero(row.valor_inicial);
                const saldo = limpiarNumero(row.saldo_por_comprometer);
                const comprometido = inicial - saldo;
                const porcentaje = inicial > 0 ? ((comprometido / inicial) * 100).toFixed(2) : 0;
                let clase = 'rojo';
                if (comprometido === inicial && saldo === 0) clase = 'verde';
                else if (comprometido > 0) clase = 'naranja';

                const tr = document.createElement('tr');
                const safe = (txt) => (txt ?? '').toString().replace(/</g, '&lt;').replace(/>/g, '&gt;');

                // Hacer el CDP clickeable
                const cdpCell = `<td class="cdp-clickable" data-row='${JSON.stringify(row).replace(/'/g, "\\'")}'>${safe(row.numero_cdp)}</td>`;

                tr.innerHTML = `
                    ${cdpCell}
                    <td>${safe(row.fecha_registro)}</td>
                    <td class="cell-textarea"><textarea readonly spellcheck="false">${safe(row.concepto_interno)}</textarea></td>
                    <td class="cell-textarea"><textarea readonly spellcheck="false">${safe(row.rubro)}</textarea></td>
                    <td class="cell-textarea"><textarea readonly spellcheck="false">${safe(row.descripcionRubro)}</textarea></td>
                    <td>${safe(row.fuente)}</td>
                    <td>${formatoMoneda(limpiarNumero(row.valor_actual))}</td>
                    <td>${formatoMoneda(limpiarNumero(row.saldo_por_comprometer))}</td>
                    <td>${formatoMoneda(comprometido)}</td>
                    <td class="${clase}">${porcentaje}%</td>
                    <td class="cell-textarea"><textarea readonly spellcheck="false">${safe(row.objeto)}</textarea></td>
                `;
                tbodyDetalles.appendChild(tr);
            });

            // Agregar event listeners a los CDP clickeables
            document.querySelectorAll('.cdp-clickable').forEach(cell => {
                cell.addEventListener('click', function () {
                    const rowData = JSON.parse(this.getAttribute('data-row'));

                    // Remover clase activa de todos los CDP
                    document.querySelectorAll('.cdp-clickable').forEach(cdp => {
                        cdp.classList.remove('cdp-active');
                    });

                    // Agregar clase activa al CDP clickeado
                    this.classList.add('cdp-active');

                    // Mostrar notificación
                    if (window.Swal) {
                        Swal.fire({
                            title: `CDP: ${rowData.numero_cdp || 'N/A'}`,
                            text: `Visualizando datos específicos de este CDP`,
                            icon: 'info',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                });
            });

            return data;
        } catch (err) {
            console.error('Error cargando detalles:', err);
            if (window.Swal) Swal.fire('Error', 'No fue posible cargar los detalles.', 'error');
            return [];
        }
    };

    // ============================
    // LIMPIAR FILTROS
    // ============================
    const btnLimpiarFiltros = document.getElementById('btn-limpiar-filtros');
    if (btnLimpiarFiltros) {
        btnLimpiarFiltros.addEventListener('click', () => {
            // Limpiar todos los campos de filtro
            filtroConceptoSelect.value = '';
            dependenciaInput.value = '';
            dependenciaInput.placeholder = 'Seleccione un filtro primero...';
            dependenciaInput.disabled = true;
            dependenciaDataList.innerHTML = '';

            // Ejecutar búsqueda sin filtros
            btnBuscar.click();
        });
    }

    // ============================
    // EVENTOS MODAL DETALLES
    // ============================
    triggersDetalles.forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();

            const w = btn.getAttribute('data-week');
            weekLabelDetalles.textContent = w;

            tbodyDetalles.innerHTML = '';
            
            // Resetear filtros al abrir el modal
            filtroConceptoSelect.value = '';
            dependenciaInput.value = '';
            dependenciaInput.placeholder = 'Seleccione un filtro primero...';
            dependenciaInput.disabled = true;
            dependenciaDataList.innerHTML = '';

            // Resetear contadores
            const contador = document.getElementById('contador-resultados');
            const filasMostradas = document.getElementById('filas-mostradas');
            if (contador) contador.textContent = '0';
            if (filasMostradas) filasMostradas.textContent = '0';

            modalDetallesInstance.show();

            // Cargar datos iniciales
            setTimeout(() => {
                btnBuscar.click();
            }, 500);
        });
    });

    // Event listener para el select de filtro concepto
    filtroConceptoSelect.addEventListener('change', function() {
        const selectedValue = this.value;
        actualizarFiltroBuscarPor(selectedValue);
    });

    // Event listener para el botón buscar
    btnBuscar.addEventListener('click', async (e) => {
        e.preventDefault();
        const data = await buscarYRender();
        
        // Actualizar etiqueta descriptiva
        const conceptoValue = filtroConceptoSelect.value;
        const buscarPorValue = dependenciaInput.value.trim();
        
        let labelTexto = 'Todos los datos';
        if (conceptoValue && buscarPorValue) {
            let tipoFiltro = '';
            switch(conceptoValue) {
                case '1': tipoFiltro = 'Dependencia'; break;
                case '2': tipoFiltro = 'Número CDP'; break;
                case '3': tipoFiltro = 'Concepto'; break;
            }
            labelTexto = `${tipoFiltro}: ${buscarPorValue}`;
        }

        // Actualizar mini gráfica label
        const miniLabel = document.getElementById('mini-presupuesto-label');
        if (miniLabel) miniLabel.textContent = labelTexto;
    });

    // Event listener para Enter en el input de búsqueda
    dependenciaInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            btnBuscar.click();
        }
    });

    // Cargar dependencias solo una vez
    let depsCargadas = false;
    const cargarDependencias = async () => {
        if (depsCargadas) return;
        try {
            const resp = await fetch(`${BASE_URL}reports/dependencias`);
            const data = await resp.json();
            if (Array.isArray(data)) {
                dependenciaDataList.innerHTML = '';
                dependencias = data;
                data.forEach(dep => {
                    const opt = document.createElement('option');
                    opt.value = dep.codigo;
                    opt.textContent = `${dep.codigo} - ${dep.nombre}`;
                    dependenciaDataList.appendChild(opt);
                });
                depsCargadas = true;
            }
        } catch (e) { console.error('Error al cargar dependencias:', e); }
    };

    // Función para obtener nombre completo de dependencia
    const obtenerNombreDependencia = (codigo) => {
        if (!codigo || !codigo.trim()) return 'Todas las dependencias';
        const clean = codigo.trim();
        const dep = dependencias.find(d => d.codigo.toString().trim() === clean);
        return dep ? `${dep.codigo} - ${dep.nombre}` : clean;
    };

    // ============================
    // AGREGACIÓN DE FILAS
    // ============================
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

    // ============================
    // GRÁFICAS GLOBALES (PANEL PRINCIPAL)
    // ============================
    let chartGastos = null;
    let chartPresupuesto = null;
    let chartDependencias = null;
    const palette = ['#4e79a7', '#f28e2b', '#e15759', '#76b7b2', '#59a14f', '#edc948', '#b07aa1', '#ff9da7', '#9c755f', '#bab0ab'];

    const disposeChart = (chartRef) => {
        if (chartRef && typeof chartRef.destroy === 'function') chartRef.destroy();
        return null;
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
        const entries = Array.from(porDependencia.entries()).sort((a, b) => b[1] - a[1]);
        const top = entries.slice(0, 10);
        if (entries.length > 10) {
            const otros = entries.slice(10).reduce((acc, cur) => acc + cur[1], 0);
            top.push(['Otros', otros]);
        }
        const depLabels = top.map(x => x[0]);
        const depValues = top.map(x => x[1]);
        const canvasDeps = document.getElementById('canvas-dependencias');
        if (canvasDeps) {
            chartDependencias = new Chart(canvasDeps.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: depLabels,
                    datasets: [{
                        label: 'Comprometido',
                        data: depValues,
                        backgroundColor: depLabels.map((_, i) => palette[i % palette.length])
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    plugins: { legend: { display: false }, tooltip: { mode: 'nearest', intersect: true } },
                    scales: { x: { beginAtZero: true } }
                }
            });
        }
    };

    // ============================
    // MINI CHART EN MODAL (AL LADO DE LA TABLA)
    // ============================
    let miniChart = null;
    let cdpIndividualChart = null;

    const miniContainer = () => document.getElementById('mini-presupuesto-container');
    const cdpContainer = () => document.getElementById('cdp-individual-container');
    const miniCanvas = () => document.getElementById('mini-presupuesto-chart');
    const cdpCanvas = () => document.getElementById('cdp-individual-chart');
    const miniLabel = () => document.getElementById('mini-presupuesto-label');
    const cdpLabel = () => document.getElementById('cdp-individual-label');
    const hideMiniBtn = () => document.getElementById('mini-hide-btn');
    const hideCdpBtn = () => document.getElementById('cdp-hide-btn');

    const renderMiniChart = (rows, dependenciaTxt) => {
        // Ocultar gráfica de CDP individual y mostrar la general
        if (cdpContainer()) cdpContainer().style.display = 'none';
        if (miniContainer()) miniContainer().style.display = 'block';

        if (!miniCanvas()) return;
        if (miniChart) { miniChart.destroy(); miniChart = null; }
        if (!rows || rows.length === 0) {
            if (miniContainer()) miniContainer().style.display = 'none';
            return;
        }

        const { totalInicial, totalSaldo, totalComprometido, totalActual } = aggregateRows(rows);

        if (miniLabel()) miniLabel().textContent = dependenciaTxt || '';

        // Gráfica general
        const config = {
            type: 'bar',
            data: {
                labels: ['Inicial', 'Actual', 'Comprometido', 'Saldo'],
                datasets: [{
                    label: 'Valores',
                    data: [totalInicial, totalActual, totalComprometido, totalSaldo],
                    backgroundColor: [
                        '#4e79a7', // Inicial → Azul
                        '#59a14f', // Actual → Verde
                        '#e15759', // Comprometido → Rojo
                        '#b07aa1'  // Saldo → Morado
                    ],
                    datalabels: {
                        anchor: 'end',
                        align: 'top',
                        color: 'black',
                        font: { weight: 'bold', size: 12 },
                        formatter: value => formatoMoneda(value),
                        offset: 4
                    }
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return `${context.label}: ${formatoMoneda(context.raw)}`;
                            }
                        }
                    },
                    datalabels: {
                        display: true,
                        color: 'black',
                        font: { weight: 'bold', size: 12 },
                        formatter: value => formatoMoneda(value),
                        offset: 4
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function (value) {
                                return formatoMoneda(value);
                            }
                        },
                        grid: {
                            drawBorder: false,
                            color: '#eee'
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { weight: 'bold' }
                        }
                    }
                },
                layout: {
                    padding: {
                        top: 10,
                        right: 10,
                        bottom: 10,
                        left: 10
                    }
                }
            },
            plugins: [ChartDataLabels]
        };

        miniChart = new Chart(miniCanvas().getContext('2d'), config);

        if (hideMiniBtn()) {
            hideMiniBtn().onclick = () => {
                if (miniContainer()) miniContainer().style.display = 'none';
            };
        }
    };

    // ============================
    // NUEVA FUNCIONALIDAD: CLICK EN CDP PARA MOSTRAR GRÁFICA INDIVIDUAL EN EL MODAL
    // ============================
    const renderCdpIndividualChart = (rowData) => {
        if (!rowData || !cdpCanvas()) return;

        // Destruir gráfica anterior si existe
        if (cdpIndividualChart) {
            cdpIndividualChart.destroy();
            cdpIndividualChart = null;
        }

        const inicial = limpiarNumero(rowData.valor_inicial);
        const saldo = limpiarNumero(rowData.saldo_por_comprometer);
        const operaciones = limpiarNumero(rowData.valor_operaciones);
        const actual = limpiarNumero(rowData.valor_actual);
        const comprometido = Math.max(inicial - saldo, 0);

        // Ocultar gráfica general y mostrar la individual
        if (miniContainer()) miniContainer().style.display = 'none';
        if (cdpContainer()) cdpContainer().style.display = 'block';

        // Actualizar título
        if (cdpLabel()) {
            cdpLabel().textContent = `CDP: ${rowData.numero_cdp || 'N/A'} - ${rowData.descripcion || 'Sin descripción'}`;
        }

        // Crear gráfica individual de CDP
        cdpIndividualChart = new Chart(cdpCanvas().getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Inicial', 'Operaciones', 'Actual', 'Comprometido', 'Saldo'],
                datasets: [{
                    label: 'Valores',
                    data: [inicial, operaciones, actual, comprometido, saldo],
                    backgroundColor: [
                        '#4e79a7', // Inicial → Azul
                        '#f28e2b', // Operaciones → Naranja
                        '#59a14f', // Actual → Verde
                        '#e15759', // Comprometido → Rojo
                        '#b07aa1'  // Saldo → Morado
                    ],
                    borderColor: [
                        '#2c3e50',
                        '#d35400',
                        '#27ae60',
                        '#c0392b',
                        '#8e44ad'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return `${context.label}: ${formatoMoneda(context.raw)}`;
                            }
                        }
                    },
                    title: {
                        display: true,
                        text: `Detalle CDP: ${rowData.numero_cdp || 'N/A'}`,
                        font: { size: 14, weight: 'bold' }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function (value) {
                                return formatoMoneda(value);
                            }
                        },
                        grid: {
                            drawBorder: false,
                            color: '#eee'
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { weight: 'bold' }
                        }
                    }
                }
            }
        });

        // Botón para ocultar gráfica individual y volver a la general
        if (hideCdpBtn()) {
            hideCdpBtn().onclick = () => {
                if (cdpContainer()) cdpContainer().style.display = 'none';
                if (miniContainer()) miniContainer().style.display = 'block';

                // Remover clase activa de todos los CDP
                document.querySelectorAll('.cdp-clickable').forEach(cdp => {
                    cdp.classList.remove('cdp-active');
                });
            };
        }

        // Mostrar notificación
        if (window.Swal) {
            Swal.fire({
                title: `CDP: ${rowData.numero_cdp || 'N/A'}`,
                text: `Visualizando datos específicos de este CDP en el panel lateral`,
                icon: 'info',
                timer: 2000,
                showConfirmButton: false
            });
        }
    };

    // ============================
    // BUSCAR Y RENDER TABLA CON TODOS LOS FILTROS
    // ============================
    const buscarYRenderConFiltros = async () => {
        // Obtener valores de todos los filtros
        const conceptoValue = document.getElementById('filtro-concepto').value;
        const buscarPorValue = dependenciaInput.value.trim();
        const pagosValue = document.getElementById('filtro-pagos').value;
        const contratoValue = document.getElementById('filtro-contrato').value;
        const valorMin = document.getElementById('filtro-valor-min').value;
        const valorMax = document.getElementById('filtro-valor-max').value;

        // Construir parámetros de búsqueda
        const params = new URLSearchParams();
        
        // Solo agregar el filtro de "buscar por" si hay un tipo seleccionado y un valor
        if (conceptoValue && buscarPorValue) {
            switch(conceptoValue) {
                case '1': // Dependencia
                    params.set('dependencia', buscarPorValue);
                    break;
                case '2': // Numero CDP
                    params.set('numero_cdp', buscarPorValue);
                    break;
                case '3': // Concepto
                    params.set('concepto_interno', buscarPorValue);
                    break;
            }
        }
        
        if (pagosValue) params.set('estado_pagos', pagosValue);
        if (contratoValue) params.set('estado_contrato', contratoValue);
        if (valorMin) params.set('valor_min', valorMin);
        if (valorMax) params.set('valor_max', valorMax);

        try {
            const resp = await fetch(`${BASE_URL}reports/consulta?${params.toString()}`);
            const data = await resp.json();
            tbodyDetalles.innerHTML = '';
            
            if (!Array.isArray(data) || data.length === 0) {
                tbodyDetalles.innerHTML = `<tr><td colspan="11" class="text-center text-muted py-5">Sin resultados para los filtros aplicados</td></tr>`;
                datosFiltradosActuales = [];
                return [];
            }

            // Guardar datos filtrados para actualizar el datalist
            datosFiltradosActuales = data;

            // Actualizar el datalist según el filtro seleccionado
            actualizarFiltroBuscarPor(conceptoValue);

            // Actualizar contador de resultados
            const contador = document.getElementById('contador-resultados');
            const filasMostradas = document.getElementById('filas-mostradas');
            if (contador) contador.textContent = data.length;
            if (filasMostradas) filasMostradas.textContent = data.length;

            // Calcular total presupuesto para el footer
            const totalPresupuesto = data.reduce((sum, row) => sum + limpiarNumero(row.valor_inicial), 0);
            const totalPresupuestoFooter = document.getElementById('total-presupuesto-footer');
            if (totalPresupuestoFooter) totalPresupuestoFooter.textContent = formatoMoneda(totalPresupuesto);

            data.forEach(row => {
                const inicial = limpiarNumero(row.valor_inicial);
                const saldo = limpiarNumero(row.saldo_por_comprometer);
                const comprometido = inicial - saldo;
                const porcentaje = inicial > 0 ? ((comprometido / inicial) * 100).toFixed(2) : 0;
                let clase = 'rojo';
                if (comprometido === inicial && saldo === 0) clase = 'verde';
                else if (comprometido > 0) clase = 'naranja';

                const tr = document.createElement('tr');
                const safe = (txt) => (txt ?? '').toString().replace(/</g, '&lt;').replace(/>/g, '&gt;');

                // Hacer el CDP clickeable
                const cdpCell = `<td class="cdp-clickable" data-row='${JSON.stringify(row).replace(/'/g, "\\'")}'>${safe(row.numero_cdp)}</td>`;

                tr.innerHTML = `
                    ${cdpCell}
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

            // Agregar event listeners a los CDP clickeables
            document.querySelectorAll('.cdp-clickable').forEach(cell => {
                cell.addEventListener('click', function () {
                    const rowData = JSON.parse(this.getAttribute('data-row'));

                    // Remover clase activa de todos los CDP
                    document.querySelectorAll('.cdp-clickable').forEach(cdp => {
                        cdp.classList.remove('cdp-active');
                    });

                    // Agregar clase activa al CDP clickeado
                    this.classList.add('cdp-active');

                    // Renderizar gráfica individual en el modal
                    renderCdpIndividualChart(rowData);
                });
            });

            return data;
        } catch (err) {
            console.error('Error cargando detalles:', err);
            if (window.Swal) Swal.fire('Error', 'No fue posible cargar los detalles.', 'error');
            return [];
        }
    };

    // ============================
    // CARGAR DATOS GLOBALES PARA EL MODAL
    // ============================
    const cargarDatosGlobales = async () => {
        if (datosGlobales) return datosGlobales;
        try {
            const resp = await fetch(`${BASE_URL}reports/consulta`);
            const data = await resp.json();
            if (Array.isArray(data)) {
                datosGlobales = data;
                return data;
            }
        } catch (err) {
            console.error('Error al cargar datos globales para el modal:', err);
        }
        return [];
    };

    // ============================
    // CARGAR CHARTS GLOBALES INICIALES
    // ============================
    let globalLoaded = false;
    const loadGlobalCharts = async () => {
        if (globalLoaded) return;
        globalLoaded = true;
        try {
            const resp = await fetch(`${BASE_URL}reports/consulta`);
            const data = await resp.json();
            if (Array.isArray(data)) {
                updateCharts(data);
                datosGlobales = data;
            }
        } catch (e) { console.error('No se pudo cargar datos globales', e); }
    };
    loadGlobalCharts();

    // ============================
    // SELECTOR DE GRÁFICAS PRINCIPALES
    // ============================
    const chartSelect = document.getElementById('chart-select');
    const chartContainers = document.querySelectorAll('.chart-container');

    chartSelect.addEventListener('change', function () {
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
    (() => {
        const preset = 'presupuesto';
        chartContainers.forEach(c => {
            c.style.display = (c.id === 'chart-' + preset) ? 'block' : 'none';
        });
        chartSelect.value = preset;
    })();

    // ============================
    // SUBIDA DE ARCHIVOS
    // ============================
    const formReporte = document.getElementById('formReporte');
    if (formReporte) {
        formReporte.addEventListener('submit', async function (e) {
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

    // ============================
    // ELIMINAR SEMANA
    // ============================
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
                        datosGlobales = null;
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