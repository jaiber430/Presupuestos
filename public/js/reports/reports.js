// ============================
// FUNCIONES GLOBALES
// ============================

// Función global para verificar si es viernes
function esViernes() {
    const hoy = new Date();
    const diaSemana = hoy.getDay();
    return diaSemana === 3;
}

// Función global para aplicar readonly según el día
function aplicarReadonlySegunViernes() {
    const observacionText = document.getElementById('observacion-text');
    const btnGuardarObservacion = document.getElementById('btn-guardar-observacion');
    const modalInfoText = document.getElementById('modal-info-text');
    
    if (observacionText && btnGuardarObservacion && modalInfoText) {
        const esRol4 = (window.userRolId === 4);
        
        if (esRol4) {
            if (esViernes()) {
                observacionText.removeAttribute('readonly');
                observacionText.classList.add('editable-hoy');
                btnGuardarObservacion.disabled = false;
                modalInfoText.textContent = 'Las observaciones solo pueden ser editadas los días viernes.';
            } else {
                observacionText.setAttribute('readonly', 'true');
                observacionText.classList.remove('editable-hoy');
                btnGuardarObservacion.disabled = true;
                modalInfoText.textContent = 'Solo puede editar observaciones los días viernes.';
            }
        } else {
            observacionText.setAttribute('readonly', 'true');
            observacionText.classList.remove('editable-hoy');
            btnGuardarObservacion.style.display = 'none';
            modalInfoText.textContent = 'Solo lectura - No tiene permisos para editar observaciones.';
        }
    }
}

// Función global para abrir el modal de observación
function abrirModalObservacion(cdp, observacionExistente = '', rowIndex = -1) {
    const modalElement = document.getElementById('modalObservacion');
    if (!modalElement) {
        console.error('Modal de observación no encontrado');
        return;
    }

    const modalObservacion = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
    const modalCdpNumber = document.getElementById('modal-cdp-number');
    const observacionText = document.getElementById('observacion-text');
    
    if (!modalCdpNumber || !observacionText) {
        console.error('Elementos del modal no encontrados');
        return;
    }

    window.observacionActual = {
        cdp: cdp,
        rowIndex: rowIndex,
        observacion: observacionExistente
    };

    modalCdpNumber.textContent = cdp;
    observacionText.value = observacionExistente;

    aplicarReadonlySegunViernes();
    modalObservacion.show();
}

document.addEventListener('DOMContentLoaded', function () {
    // ============================
    // CONFIGURACIÓN INICIAL
    // ============================
    const addLoadingStyles = () => {
        const styles = `
            .swal2-popup { border-radius: 15px; padding: 2rem; }
            .upload-progress-container { background: #f8f9fa; border-radius: 10px; padding: 20px; margin: 15px 0; }
            .progress { border-radius: 10px; overflow: hidden; }
            .progress-bar { transition: width 0.6s ease; }
            .upload-status { font-weight: 600; color: #2c3e50; margin-bottom: 5px; }
            .upload-details { font-size: 0.9em; color: #7f8c8d; }
            .cdp-clickable { cursor: pointer; transition: all 0.3s ease; position: relative; }
            .cdp-clickable:hover { background-color: #e3f2fd !important; transform: translateY(-1px); box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
            .cdp-clickable::after { content: '🔍'; position: absolute; right: 5px; top: 50%; transform: translateY(-50%); opacity: 0; transition: opacity 0.3s ease; }
            .cdp-clickable:hover::after { opacity: 1; }
            .cdp-active { background-color: #bbdefb !important; border: 2px solid #2196f3; }
            .chart-center-container { text-align: center; padding: 20px; background: #f8f9fa; border-radius: 10px; margin-top: 20px; }
            .chart-hidden-indicator { color: #6c757d; font-style: italic; }
            .editable-hoy { background-color: #e8f5e8 !important; border: 2px solid #28a745 !important; }
            .observacion-link { cursor: pointer; color: #0d6efd; text-decoration: underline; transition: color 0.3s ease; background: none; border: none; padding: 0; font: inherit; }
            .observacion-link:hover { color: #0a58ca; }
            .observacion-existe { color: #198754; font-weight: 500; }
            .observacion-vacia { color: #6c757d; font-style: italic; }
            .observacion-solo-lectura { color: #6c757d; cursor: default; }
            .observacion-solo-lectura:hover { color: #6c757d; text-decoration: none; }
        `;
        const styleSheet = document.createElement('style');
        styleSheet.textContent = styles;
        document.head.appendChild(styleSheet);
    };

    addLoadingStyles();

    // ============================
    // VARIABLES GLOBALES
    // ============================
    window.observacionActual = { cdp: '', rowIndex: -1, observacion: '' };
    let datosGlobales = null;
    let datosFiltradosActuales = [];
    let modalObservacion = null;
    let miniChart = null;
    let cdpIndividualChart = null;

    // ============================
    // INICIALIZACIÓN DE MODALES
    // ============================
    const inicializarModales = () => {
        const modalElement = document.getElementById('modalObservacion');
        if (modalElement) {
            modalObservacion = new bootstrap.Modal(modalElement);
        }

        const btnGuardarObservacion = document.getElementById('btn-guardar-observacion');
        if (btnGuardarObservacion && window.userRolId === 4) {
            btnGuardarObservacion.addEventListener('click', function() {
                const observacionText = document.getElementById('observacion-text');
                if (!observacionText) return;
                
                const nuevaObservacion = observacionText.value.trim();
                
                if (!nuevaObservacion) {
                    Swal.fire('Error', 'Debe ingresar una observación', 'warning');
                    return;
                }

                guardarObservacionEnServidor(window.observacionActual.cdp, nuevaObservacion, window.observacionActual.rowIndex);
            });
        }
    };

    setTimeout(inicializarModales, 100);

    // ============================
    // ELEMENTOS DEL DOM
    // ============================
    const modalDetalles = document.getElementById('modalDetalles');
    const weekLabelDetalles = document.getElementById('modal-detalles-week-label');
    const triggersDetalles = document.querySelectorAll('.btn-ver-detalles');
    const dependenciaInput = document.getElementById('modal-dependency-input');
    const dependenciaDataList = document.getElementById('dependencias-list');
    const btnBuscar = document.getElementById('btn-modal-buscar');
    const tbodyDetalles = document.getElementById('tabla-detalles-body');
    const filtroConceptoSelect = document.getElementById('filtro-concepto');
    const btnLimpiarFiltros = document.getElementById('btn-limpiar-filtros');

    const modalDetallesInstance = modalDetalles ? new bootstrap.Modal(modalDetalles) : null;

    // ============================
    // FUNCIONES INTERNAS
    // ============================
    function guardarObservacionEnServidor(cdp, observacion, rowIndex) {
        if (window.userRolId !== 4) {
            Swal.fire('Error', 'No tiene permisos para guardar observaciones', 'error');
            return;
        }

        Swal.fire({
            title: 'Guardando...',
            text: 'Guardando observación',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        setTimeout(() => {
            actualizarObservacionEnTabla(cdp, observacion, rowIndex);
            Swal.fire({
                title: '¡Éxito!',
                text: 'Observación guardada correctamente',
                icon: 'success',
                confirmButtonText: 'Aceptar'
            });

            if (modalObservacion) modalObservacion.hide();
        }, 1000);
    }

    function actualizarObservacionEnTabla(cdp, observacion, rowIndex) {
        const rows = document.querySelectorAll('#tabla-detalles-body tr');
        if (rows[rowIndex]) {
            const observacionCell = rows[rowIndex].querySelector('.observacion-cell');
            if (observacionCell) {
                const link = observacionCell.querySelector('.observacion-link');
                if (link) {
                    const textoEnlace = observacion ? 'Ver Observación' : (window.userRolId === 4 ? 'Agregar Observación' : 'Sin Observación');
                    link.textContent = textoEnlace;
                    
                    if (window.userRolId === 4) {
                        link.className = `observacion-link ${observacion ? 'observacion-existe' : 'observacion-vacia'}`;
                    } else {
                        link.className = `observacion-link observacion-solo-lectura`;
                    }
                    
                    link.setAttribute('title', observacion || 'Sin observación');
                    link.setAttribute('onclick', `abrirModalObservacion('${cdp}', '${observacion.replace(/'/g, "\\'")}', ${rowIndex})`);
                }
            }
        }
    }

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
    // FILTROS Y BÚSQUEDA
    // ============================
    const actualizarFiltroBuscarPor = async (tipoFiltro) => {
        if (dependenciaInput) {
            dependenciaInput.value = '';
            dependenciaInput.placeholder = 'Seleccione un filtro primero...';
            dependenciaInput.disabled = true;
        }
        if (dependenciaDataList) dependenciaDataList.innerHTML = '';

        switch (tipoFiltro) {
            case '1': // Dependencia
                if (dependenciaInput) {
                    dependenciaInput.placeholder = 'Buscar dependencia...';
                    dependenciaInput.disabled = false;
                }
                try {
                    const resp = await fetch(`${BASE_URL}reports/dependencias`);
                    const data = await resp.json();
                    if (Array.isArray(data) && dependenciaDataList) {
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
                if (dependenciaInput) {
                    dependenciaInput.placeholder = 'Buscar número CDP...';
                    dependenciaInput.disabled = false;
                }
                try {
                    const resp = await fetch(`${BASE_URL}reports/cdps`);
                    const data = await resp.json();
                    if (Array.isArray(data) && dependenciaDataList) {
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
                if (dependenciaInput) {
                    dependenciaInput.placeholder = 'Buscar concepto...';
                    dependenciaInput.disabled = false;
                }
                try {
                    const resp = await fetch(`${BASE_URL}reports/conceptos`);
                    const data = await resp.json();
                    if (Array.isArray(data) && dependenciaDataList) {
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
                if (dependenciaInput) {
                    dependenciaInput.placeholder = 'Seleccione un filtro primero...';
                    dependenciaInput.disabled = true;
                }
                break;
        }
    };

    const buscarYRender = async () => {
        const conceptoValue = filtroConceptoSelect ? filtroConceptoSelect.value : '';
        const buscarPorValue = dependenciaInput ? dependenciaInput.value.trim() : '';

        const params = new URLSearchParams();
        if (conceptoValue && buscarPorValue) {
            switch (conceptoValue) {
                case '1': params.set('dependencia', buscarPorValue); break;
                case '2': params.set('numero_cdp', buscarPorValue); break;
                case '3': params.set('concepto_interno', buscarPorValue); break;
            }
        }

        try {
            const resp = await fetch(`${BASE_URL}reports/consulta?${params.toString()}`);
            const data = await resp.json();
            if (tbodyDetalles) tbodyDetalles.innerHTML = '';

            if (!Array.isArray(data) || data.length === 0) {
                if (tbodyDetalles) {
                    tbodyDetalles.innerHTML = `<tr><td colspan="17" class="text-center text-muted py-5">Sin resultados para los filtros aplicados</td></tr>`;
                }
                datosFiltradosActuales = [];
                
                const miniContainer = document.getElementById('mini-presupuesto-container');
                const cdpContainer = document.getElementById('cdp-individual-container');
                if (miniContainer) miniContainer.style.display = 'none';
                if (cdpContainer) cdpContainer.style.display = 'none';
                return [];
            }

            datosFiltradosActuales = data;

            const contador = document.getElementById('contador-resultados');
            const filasMostradas = document.getElementById('filas-mostradas');
            if (contador) contador.textContent = data.length;
            if (filasMostradas) filasMostradas.textContent = data.length;

            const totalPresupuesto = data.reduce((sum, row) => sum + limpiarNumero(row.valor_inicial), 0);
            const totalPresupuestoFooter = document.getElementById('total-presupuesto-footer');
            if (totalPresupuestoFooter) totalPresupuestoFooter.textContent = formatoMoneda(totalPresupuesto);

            data.forEach((row, index) => {
                const inicial = limpiarNumero(row.valor_inicial || 0);
                const saldo = limpiarNumero(row.saldo_por_comprometer || 0);
                const comprometido = limpiarNumero(row.valor_comprometer || 0);
                const operaciones = limpiarNumero(row.valor_operaciones || 0);
                const pagado = limpiarNumero(row.valor_pagado || 0);
                const porcentaje = limpiarNumero(row.porcentaje_compromiso || 0);
                
                let clase = 'rojo';
                if (comprometido === inicial && saldo === 0) clase = 'verde';
                else if (comprometido > 0) clase = 'naranja';

                const tr = document.createElement('tr');
                
                const safe = (txt) => {
                    if (txt === null || txt === undefined) return '';
                    return String(txt).replace(/</g, '&lt;').replace(/>/g, '&gt;');
                };

                const numeroCdp = safe(row.numero_cdp || 'N/A');
                const cdpCell = `<td class="cdp-clickable" data-row='${JSON.stringify(row).replace(/'/g, "\\'")}'>${numeroCdp}</td>`;

                const observacionExistente = row.observacion || '';
                const tieneObservacion = observacionExistente.trim() !== '';
                let textoEnlace, claseObservacion;

                if (window.userRolId === 4) {
                    textoEnlace = tieneObservacion ? 'Ver Observación' : 'Agregar Observación';
                    claseObservacion = tieneObservacion ? 'observacion-existe' : 'observacion-vacia';
                } else {
                    textoEnlace = tieneObservacion ? 'Ver Observación' : 'Sin Observación';
                    claseObservacion = 'observacion-solo-lectura';
                }

                let rowHTML = `
                    ${cdpCell}
                    <td>${safe(row.fecha_registro || '')}</td>
                    <td class="text-center">${safe(row.dependencia || '')}</td>
                    <td class="cell-textarea"><textarea readonly spellcheck="false">${safe(row.dependencia_descripcion || '')}</textarea></td>
                    <td class="cell-textarea"><textarea readonly spellcheck="false">${safe(row.concepto_interno || '')}</textarea></td>
                    <td class="cell-textarea"><textarea readonly spellcheck="false">${safe(row.rubro || '')}</textarea></td>
                    <td class="cell-textarea"><textarea readonly spellcheck="false">${safe(row.descripcion || '')}</textarea></td>
                    <td class="text-center">${safe(row.fuente || '')}</td>
                    <td class="text-end">${formatoMoneda(inicial)}</td>
                    <td class="text-end">${formatoMoneda(operaciones)}</td>
                    <td class="text-end">${formatoMoneda(limpiarNumero(row.valor_actual || 0))}</td>
                    <td class="text-end">${formatoMoneda(saldo)}</td>
                    <td class="text-end">${formatoMoneda(comprometido)}</td>
                    <td class="text-end">${formatoMoneda(pagado)}</td>
                    <td class="text-center ${clase}">${porcentaje}%</td>
                    <td class="cell-textarea"><textarea readonly spellcheck="false">${safe(row.objeto || '')}</textarea></td>
                    <td class="text-center observacion-cell">
                        <button type="button" class="observacion-link ${claseObservacion}" 
                           onclick="abrirModalObservacion('${numeroCdp}', '${observacionExistente.replace(/'/g, "\\'")}', ${index})"
                           title="${observacionExistente || 'Sin observación'}">
                            ${textoEnlace}
                        </button>
                    </td>
                `;

                tr.innerHTML = rowHTML;
                if (tbodyDetalles) tbodyDetalles.appendChild(tr);
            });

            document.querySelectorAll('.cdp-clickable').forEach(cell => {
                cell.addEventListener('click', function () {
                    const rowData = JSON.parse(this.getAttribute('data-row'));

                    document.querySelectorAll('.cdp-clickable').forEach(cdp => {
                        cdp.classList.remove('cdp-active');
                    });

                    this.classList.add('cdp-active');
                    renderCdpIndividualChart(rowData);

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

            renderMiniChart(data, '');
            return data;
        } catch (err) {
            console.error('Error cargando detalles:', err);
            if (window.Swal) Swal.fire('Error', 'No fue posible cargar los detalles.', 'error');
            return [];
        }
    };

    // ============================
    // EVENT LISTENERS
    // ============================
    if (filtroConceptoSelect) {
        filtroConceptoSelect.addEventListener('change', function () {
            actualizarFiltroBuscarPor(this.value);
        });
    }

    if (btnBuscar) {
        btnBuscar.addEventListener('click', async (e) => {
            e.preventDefault();
            const data = await buscarYRender();

            const conceptoValue = filtroConceptoSelect ? filtroConceptoSelect.value : '';
            const buscarPorValue = dependenciaInput ? dependenciaInput.value.trim() : '';

            let labelTexto = 'Todos los datos';
            if (conceptoValue && buscarPorValue) {
                let tipoFiltro = '';
                switch (conceptoValue) {
                    case '1': tipoFiltro = 'Dependencia'; break;
                    case '2': tipoFiltro = 'Número CDP'; break;
                    case '3': tipoFiltro = 'Concepto'; break;
                }
                labelTexto = `${tipoFiltro}: ${buscarPorValue}`;
            }

            const miniLabel = document.getElementById('mini-presupuesto-label');
            if (miniLabel) miniLabel.textContent = labelTexto;
        });
    }

    if (btnLimpiarFiltros) {
        btnLimpiarFiltros.addEventListener('click', () => {
            if (filtroConceptoSelect) filtroConceptoSelect.value = '';
            if (dependenciaInput) {
                dependenciaInput.value = '';
                dependenciaInput.placeholder = 'Seleccione un filtro primero...';
                dependenciaInput.disabled = true;
            }
            if (dependenciaDataList) dependenciaDataList.innerHTML = '';

            if (btnBuscar) btnBuscar.click();
        });
    }

    if (dependenciaInput) {
        dependenciaInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter' && btnBuscar) {
                btnBuscar.click();
            }
        });
    }

    triggersDetalles.forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();

            const w = btn.getAttribute('data-week');
            if (weekLabelDetalles) weekLabelDetalles.textContent = w;

            if (tbodyDetalles) tbodyDetalles.innerHTML = '';

            if (filtroConceptoSelect) filtroConceptoSelect.value = '';
            if (dependenciaInput) {
                dependenciaInput.value = '';
                dependenciaInput.placeholder = 'Seleccione un filtro primero...';
                dependenciaInput.disabled = true;
            }
            if (dependenciaDataList) dependenciaDataList.innerHTML = '';

            const contador = document.getElementById('contador-resultados');
            const filasMostradas = document.getElementById('filas-mostradas');
            if (contador) contador.textContent = '0';
            if (filasMostradas) filasMostradas.textContent = '0';

            const miniContainer = document.getElementById('mini-presupuesto-container');
            const cdpContainer = document.getElementById('cdp-individual-container');
            if (miniContainer) miniContainer.style.display = 'none';
            if (cdpContainer) cdpContainer.style.display = 'none';

            if (modalDetallesInstance) modalDetallesInstance.show();

            setTimeout(() => {
                if (btnBuscar) btnBuscar.click();
            }, 500);
        });
    });

    // ============================
    // GRÁFICAS
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

    const renderMiniChart = (rows, dependenciaTxt) => {
        const miniContainer = document.getElementById('mini-presupuesto-container');
        const cdpContainer = document.getElementById('cdp-individual-container');
        const miniCanvas = document.getElementById('mini-presupuesto-chart');
        const miniLabel = document.getElementById('mini-presupuesto-label');
        const hideMiniBtn = document.getElementById('mini-hide-btn');

        if (cdpContainer) cdpContainer.style.display = 'none';
        if (miniContainer) miniContainer.style.display = 'block';

        if (!miniCanvas) return;
        if (miniChart) { 
            miniChart.destroy(); 
            miniChart = null; 
        }
        if (!rows || rows.length === 0) {
            if (miniContainer) miniContainer.style.display = 'none';
            return;
        }

        const { totalInicial, totalSaldo, totalComprometido, totalActual } = aggregateRows(rows);

        if (miniLabel) miniLabel.textContent = dependenciaTxt || 'Resumen General';

        const config = {
            type: 'bar',
            data: {
                labels: ['Inicial', 'Actual', 'Comprometido', 'Saldo'],
                datasets: [{
                    label: 'Valores',
                    data: [totalInicial, totalActual, totalComprometido, totalSaldo],
                    backgroundColor: ['#4e79a7', '#59a14f', '#e15759', '#b07aa1']
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
        };

        miniChart = new Chart(miniCanvas.getContext('2d'), config);

        if (hideMiniBtn) {
            hideMiniBtn.onclick = () => {
                if (miniContainer) miniContainer.style.display = 'none';
            };
        }
    };

    const renderCdpIndividualChart = (rowData) => {
        const miniContainer = document.getElementById('mini-presupuesto-container');
        const cdpContainer = document.getElementById('cdp-individual-container');
        const cdpCanvas = document.getElementById('cdp-individual-chart');
        const cdpLabel = document.getElementById('cdp-individual-label');
        const hideCdpBtn = document.getElementById('cdp-hide-btn');

        if (!rowData || !cdpCanvas) return;

        if (cdpIndividualChart) {
            cdpIndividualChart.destroy();
            cdpIndividualChart = null;
        }

        const inicial = limpiarNumero(rowData.valor_inicial);
        const saldo = limpiarNumero(rowData.saldo_por_comprometer);
        const operaciones = limpiarNumero(rowData.valor_operaciones);
        const actual = limpiarNumero(rowData.valor_actual);
        const comprometido = Math.max(inicial - saldo, 0);

        if (miniContainer) miniContainer.style.display = 'none';
        if (cdpContainer) cdpContainer.style.display = 'block';

        if (cdpLabel) {
            cdpLabel.textContent = `CDP: ${rowData.numero_cdp || 'N/A'} - ${rowData.descripcion || 'Sin descripción'}`;
        }

        cdpIndividualChart = new Chart(cdpCanvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Inicial', 'Operaciones', 'Actual', 'Comprometido', 'Saldo'],
                datasets: [{
                    label: 'Valores',
                    data: [inicial, operaciones, actual, comprometido, saldo],
                    backgroundColor: ['#4e79a7', '#f28e2b', '#59a14f', '#e15759', '#b07aa1']
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

        if (hideCdpBtn) {
            hideCdpBtn.onclick = () => {
                if (cdpContainer) cdpContainer.style.display = 'none';
                if (miniContainer) miniContainer.style.display = 'block';

                document.querySelectorAll('.cdp-clickable').forEach(cdp => {
                    cdp.classList.remove('cdp-active');
                });
            };
        }
    };

    // ============================
    // INICIALIZACIÓN FINAL
    // ============================
    document.addEventListener('shown.bs.modal', function (e) {
        if (e.target.id === 'modalDetalles') {
            setTimeout(aplicarReadonlySegunViernes, 100);
        }
    });
});