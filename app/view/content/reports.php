<div class="container-fluid mt-4 reports-page">
    <!-- Contenido en dos columnas: izquierda (tabla) | derecha (gráfico) -->
    <div class="row mt-4 g-4 reports-layout">
        <!-- Columna Tabla -->
        <div class="col-12 col-xl-6 order-2 order-lg-1">
            <div class="rp-card p-3 h-100 rp-card-table">
                <h3 class="subheader mb-1">Gastos por Semana</h3>
                <p class="text-muted small mb-3">Listado de semanas cargadas. Desde aquí puedes ver detalles o subir nuevos reportes semanales.</p>
                <div class="table-responsive">
                <table class="table reports-table mb-0">
                    <thead>
                        <tr>
                            <th>Semana</th>
                            <th>Desde</th>
                            <th>Hasta</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <?php
                                    $contador = 1;
                        foreach ($semanas as $semana): 
                        ?>
                            <tr>
                                <td>Semana <?= $contador ?></td>
                                <td><?= date("d/m/Y", strtotime($semana['inicio'])) ?></td>
                                <td><?= date("d/m/Y", strtotime($semana['fin'])) ?></td>
                                <td>
                                    <button class="btn btn-outline-secondary btn-sm btn-ver-detalles" 
                                            data-week="Semana <?= $contador ?>" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalDetalles">Ver Detalles</button>
                                    
                                    <button class="btn btn-primary btn-sm btn-open-modal" 
                                            data-week="Semana <?= $contador ?>" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalReporte">Subir Reporte</button>
                                    
                                    <button class="btn btn-danger btn-sm btn-delete-week" 
                                            data-week="Semana <?= $contador ?>">
                                            <i class="fas fa-trash-alt me-1"></i>Eliminar
                                    </button>
                                </td>
                            </tr>
                        <?php 
                            $contador++;
                        endforeach; 
                        ?>
                                                
                    </tbody>
                </table>
                </div>
            </div>
        </div>
        <!-- Columna Gráficas -->
        <div class="col-12 col-xl-6 order-1 order-lg-2">
            <div class="rp-card p-3 h-100 charts-wrapper">
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-2 gap-2">
                    <div>
                        <h3 class="subheader mb-0">Panel Analítico</h3>
                        <small class="text-muted">Visualización detallada de estado presupuestal y compromisos.</small>
                    </div>
                    <div class="chart-selector">
                        <label for="chart-select" class="form-label fw-bold small mb-1">Ver:</label>
                        <select id="chart-select" class="form-select form-select-sm w-auto">
                            <option value="presupuesto" selected>Estado del Presupuesto</option>
                            <option value="gastos">Distribución (Comprometido vs Saldo)</option>
                            <option value="dependencias">Comprometido por Dependencia</option>
                        </select>
                    </div>
                </div>

                <div id="chart-presupuesto" class="chart-container">
                    <h5 class="chart-title mb-1">Estado del Presupuesto</h5>
                    <p class="chart-desc small text-muted mb-2">Comparación de valores Inicial, Operaciones (ajustes), Actual, Comprometido y Saldo.</p>
                    <div class="total-budget mb-2">
                        <span class="budget-label">Total Presupuesto Asignado:</span>
                        <span class="budget-amount" id="total-presupuesto">S/ 0</span>
                    </div>
                    <canvas id="canvas-presupuesto" class="budget-chart main-chart" height="320"></canvas>
                </div>

                <div id="chart-gastos" class="chart-container" style="display:none;">
                    <h5 class="chart-title">Distribución de Gastos</h5>
                    <p class="chart-desc small text-muted mb-1">Relación entre valores comprometidos y saldo por comprometer del conjunto consultado.</p>
                    <canvas id="canvas-gastos" class="mt-2 main-chart" height="320"></canvas>
                </div>


                <div id="chart-dependencias" class="chart-container" style="display:none;">
                    <h5 class="chart-title">Comprometido por Dependencia</h5>
                    <p class="chart-desc small text-muted mb-1">Top dependencias según valor comprometido (agrupando el resto en "Otros").</p>
                    <canvas id="canvas-dependencias" class="mt-2 main-chart" height="320"></canvas>
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
                    <!-- Filtros + Mini gráfica -->
                    <div class="row mb-3 g-3 align-items-stretch">
                        <div class="col-lg-8 col-md-7 col-12">
                            <div class="reports-header p-2 h-100">
                                <div class="d-flex align-items-center flex-wrap gap-3 w-100">
                                    <div class="d-flex align-items-center gap-2">
                                        <label for="modal-dependency-input" class="form-label m-0">Dependencia:</label>
                                        <input id="modal-dependency-input" list="dependencias-list" class="form-control form-control-sm w-auto" placeholder="Todas (dejar vacío)" autocomplete="off" />
                                        <datalist id="dependencias-list">
                                            <!-- Opciones dinámicas cargadas por JS -->
                                        </datalist>
                                    </div>
                                    <button class="btn btn-success btn-sm" id="btn-modal-buscar">
                                        <i class="fas fa-search me-1"></i>Buscar
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-5 col-12 d-flex">
                            <div id="mini-presupuesto-container" class="mini-presupuesto-box w-100" style="display:none;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <small class="text-muted fw-semibold">Estado Dependencia</small>
                                    <button type="button" class="btn btn-link p-0 small text-decoration-none" id="mini-hide-btn" title="Ocultar" style="line-height:1;">&times;</button>
                                </div>
                                <canvas id="mini-presupuesto-chart" height="190"></canvas>
                                <small class="text-muted d-block mt-1" id="mini-presupuesto-label"></small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tabla de detalles -->
                    <div class="table-scroll-wrapper">
                        <table class="table table-striped table-hover reports-table">
                            <thead class="table-dark">
                                <tr>
                                    <!-- <th>#</th> -->
                                    <th>Número CDP</th>
                                    <th>Fecha de Registro</th>
                                    <!-- <th>Dependencia</th>
                                    <th>Dependencia Descripción</th> -->
                                    <th>Concepto Interno</th>
                                    <th>Rubro</th>
                                    <th>Descripción</th>
                                    <th>Fuente</th>
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
