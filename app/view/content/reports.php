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
                    <canvas id="canvas-gastos" class="mt-3" height="260"></canvas>
                </div>

                <!-- Gráfica 2: Estado del Presupuesto -->
                <div id="chart-presupuesto" class="chart-container" style="display: none;">
                    <div class="budget-header mb-3">
                        <h3 class="subheader mb-2">Presupuesto General</h3>
                        <div class="total-budget">
                            <span class="budget-label">Total Presupuesto Asignado:</span>
                            <span class="budget-amount" id="total-presupuesto">S/ 0</span>
                        </div>
                    </div>
                    <div class="budget-chart-container">
                        <canvas id="canvas-presupuesto" class="budget-chart"></canvas>
                    </div>
                </div>

                <!-- Gráfica 3: Gastos por Dependencia -->
                <div id="chart-dependencias" class="chart-container" style="display: none;">
                    <h3 class="subheader">Gastos por Dependencia</h3>
                    <canvas id="canvas-dependencias" class="mt-3" height="260"></canvas>
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
                                        <button class="btn btn-success btn-sm" id="btn-modal-buscar">
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
                            <tbody id="tabla-detalles-body">
                            
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Fin Modal Ver Detalles -->
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?= APP_URL ?>js/reports/reports.js"></script>
