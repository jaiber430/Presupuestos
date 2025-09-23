  

<div class="container mt-4 reports-page">
    <!-- Contenido en dos columnas: izquierda (tabla) | derecha (gráfico) -->
    <div class="row mt-4">
        <div class="col-lg-7">
            <div class="rp-card p-3 h-100">
                <h3 class="subheader">Gastos por Semana</h3>
                <table class="table reports-table mt-3">
                <thead>
                    <tr>
                        <th>Semana</th>
                        <th>Desde</th>
                        <th>Hasta</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Semana 1</td>
                        <td>01/01/2025</td>
                        <td>07/01/2025</td>
                        <td>
                            <button class="btn btn-outline-secondary btn-sm btn-ver-detalles" data-week="Semana 1" data-bs-toggle="modal" data-bs-target="#modalDetalles">Ver Detalles</button>
                            <button class="btn btn-primary btn-sm btn-open-modal" data-week="Semana 1" data-bs-toggle="modal" data-bs-target="#modalReporte">Subir Reporte</button>
                            <button class="btn btn-danger btn-sm btn-delete-week" data-week="Semana 1"><i class="fas fa-trash-alt me-1"></i>Eliminar</button>
                        </td>
                    </tr>
                    <tr>
                        <td>Semana 2</td>
                        <td>08/01/2025</td>
                        <td>14/01/2025</td>
                        <td>
                            <button class="btn btn-outline-secondary btn-sm btn-ver-detalles" data-week="Semana 2" data-bs-toggle="modal" data-bs-target="#modalDetalles">Ver Detalles</button>
                            <button class="btn btn-primary btn-sm btn-open-modal" data-week="Semana 2" data-bs-toggle="modal" data-bs-target="#modalReporte">Subir Reporte</button>
                            <button class="btn btn-danger btn-sm btn-delete-week" data-week="Semana 2"><i class="fas fa-trash-alt me-1"></i>Eliminar</button>
                        </td>
                    </tr>
                </tbody>
                </table>
            </div>
        </div>
        <div class="col-lg-5 mt-4 mt-lg-0">
            <div class="rp-card p-3 h-100">
                <!-- Selector de Gráficas -->
                <div class="chart-selector mb-3">
                    <label for="chart-select" class="form-label fw-bold">Ver Gráfica:</label>
                    <select id="chart-select" class="form-select form-select-sm w-auto">
                        <option value="gastos" selected>Distribución de Gastos</option>
                        <option value="presupuesto">Estado del Presupuesto</option>
                        <option value="dependencias">Gastos por Dependencia</option>
                    </select>
                </div>

                <!-- Gráfica 1: Distribución de Gastos -->
                <div id="chart-gastos" class="chart-container">
                    <h3 class="subheader">Distribución de Gastos</h3>
                    <div class="pie-chart-placeholder mt-3">Gráfico de pastel - Distribución de Gastos</div>
                    <div class="legend mt-3">
                        <div><span class="legend-color" style="background-color: #4e79a7;"></span> Material de Oficina</div>
                        <div><span class="legend-color" style="background-color: #f28e2b;"></span> Equipos Tecnológicos</div>
                        <div><span class="legend-color" style="background-color: #e15759;"></span> Servicios Públicos</div>
                        <div><span class="legend-color" style="background-color: #76b7b2;"></span> Mantenimiento</div>
                        <div><span class="legend-color" style="background-color: #59a14f;"></span> Capacitación</div>
                    </div>
                </div>

                <!-- Gráfica 2: Estado del Presupuesto -->
                <div id="chart-presupuesto" class="chart-container" style="display: none;">
                    <div class="budget-header mb-3">
                        <h3 class="subheader mb-2">Presupuesto General</h3>
                        <div class="total-budget">
                            <span class="budget-label">Total Presupuesto Asignado:</span>
                            <span class="budget-amount">S/ 100,000</span>
                        </div>
                    </div>
                    
                    <div class="budget-chart-container">
                        <div class="pie-chart-placeholder budget-chart">Estado del Presupuesto</div>
                        <div class="legend mt-3 budget-legend">
                            <div><span class="legend-color" style="background-color: #4e79a7;"></span> Presupuesto Comprometido</div>
                            <div><span class="legend-color" style="background-color: #f28e2b;"></span> Por Comprometer</div>
                            <div><span class="legend-color" style="background-color: #e15759;"></span> Apropiación Disponible</div>
                            <div><span class="legend-color" style="background-color: #76b7b2;"></span> Presupuesto Pagado</div>
                        </div>
                    </div>
                </div>

                <!-- Gráfica 3: Gastos por Dependencia -->
                <div id="chart-dependencias" class="chart-container" style="display: none;">
                    <h3 class="subheader">Gastos por Dependencia</h3>
                    <div class="pie-chart-placeholder mt-3">Gráfico de pastel - Gastos por Dependencia</div>
                    <div class="legend mt-3">
                        <div><span class="legend-color" style="background-color: #2ca02c;"></span> Dirección Administrativa (45%)</div>
                        <div><span class="legend-color" style="background-color: #1f77b4;"></span> Dirección Técnica (30%)</div>
                        <div><span class="legend-color" style="background-color: #ff7f0e;"></span> Dirección Académica (15%)</div>
                        <div><span class="legend-color" style="background-color: #d62728;"></span> Dirección de Calidad (6%)</div>
                        <div><span class="legend-color" style="background-color: #9467bd;"></span> Rectoría (4%)</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Subir Reporte -->
    <div class="modal fade modal-reports" id="modalReporte" tabindex="-1" aria-labelledby="modalReporteLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content reports-modal reports-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalReporteLabel">Subir Reporte <span class="text-muted" id="modal-week-label"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form id="formReporte" class="FormularioAjax" action="<?= APP_URL . "reports"?>" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="week" id="input-week">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label for="file-cdp" class="form-label">CDP (Excel CSV)</label>
                                <input type="file" class="form-control" id="file-cdp" name="cdp" accept=".csv">
                            </div>
                            <div class="col-md-4">
                                <label for="file-rp" class="form-label">R.P (Excel CSV)</label>
                                <input type="file" class="form-control" id="file-rp" name="rp" accept=".csv">
                            </div>
                            <div class="col-md-4">
                                <label for="file-pagos" class="form-label">Pagos (Excel CSV)</label>
                                <input type="file" class="form-control" id="file-pagos" name="pagos" accept=".csv">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
                
            </div>
        </div>
    </div>
    <!-- Fin Modal Subir Reporte -->

    <!-- Modal Ver Detalles -->
    <div class="modal fade modal-reports" id="modalDetalles" tabindex="-1" aria-labelledby="modalDetallesLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content reports-modal reports-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetallesLabel">Detalles de <span class="text-muted" id="modal-detalles-week-label"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <!-- Filtros movidos desde el segundo subheader -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="reports-header p-2">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 w-100">
                                    <div class="reports-controls d-flex align-items-center gap-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <label for="modal-dependency-select" class="form-label m-0">Dependencia:</label>
                                            <select id="modal-dependency-select" class="form-select form-select-sm w-auto">
                                                <option value="all" selected>Todas</option>
                                            </select>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <label for="modal-cdp-select" class="form-label m-0">CDP:</label>
                                            <input type="text" id="modal-cdp-input" class="form-control form-control-sm" placeholder="Número de CDP">
                                        </div>
                                        <button class="btn btn-success btn-sm">
                                            <i class="fas fa-search me-1"></i>Buscar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tabla de detalles -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover reports-table">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Número CDP</th>
                                    <th>Fecha de Registro</th>
                                    <th>Dependencia</th>
                                    <th>Dependencia Descripción</th>
                                    <th>Concepto Interno</th>
                                    <th>Rubro</th>
                                    <th>Descripción</th>
                                    <th>Fuente</th>
                                    <th>Valor Inicial</th>
                                    <th>Valor Operaciones</th>
                                    <th>Valor Actual</th>
                                    <th>Saldo por Comprometer</th>
                                    <th>Valor Comprometido</th>
                                    <th>Compromiso</th>
                                    <th>Objeto</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Fin Modal Ver Detalles -->
</div>
<script>
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

    triggersDetalles.forEach(btn => {
        btn.addEventListener('click', () => {
            const w = btn.getAttribute('data-week');
            weekLabelDetalles.textContent = w;
            
            // Aquí puedes agregar lógica adicional para cargar datos específicos de la semana
            // Por ejemplo, hacer una petición AJAX para obtener los datos de la tabla
            console.log('Cargando detalles para: ' + w);
        });
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
});
</script>
