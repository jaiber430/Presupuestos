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
                        <tbody>
                            <?php
                            $contador = 1;
                            foreach ($semanas as $semana):
                            ?>
                                <tr>
                                    <td class="fw-semibold">Semana <?= $contador ?></td>
                                    <td><?= date("d/m/Y", strtotime($semana['inicio'])) ?></td>
                                    <td><?= date("d/m/Y", strtotime($semana['fin'])) ?></td>
                                    <td>
                                        <div class="d-flex gap-1 flex-wrap">
                                            <button class="btn btn-outline-secondary btn-sm btn-ver-detalles"
                                                data-week="Semana <?= $contador ?>"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalDetalles">
                                                <i class="fas fa-eye me-1"></i>Ver Detalles
                                            </button>

                                            <button class="btn btn-primary btn-sm btn-open-modal"
                                                data-week="Semana <?= $contador ?>"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalReporte">
                                                <i class="fas fa-upload me-1"></i>Subir Reporte
                                            </button>

                                            <button class="btn btn-danger btn-sm btn-delete-week"
                                                data-week="Semana <?= $contador ?>">
                                                <i class="fas fa-trash-alt me-1"></i>Eliminar
                                            </button>
                                        </div>
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
                    <h5 class="modal-title" id="modalReporteLabel">
                        <i class="fas fa-upload me-2"></i>
                        Subir Reporte <span class="text-muted" id="modal-week-label"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form id="formReporte" class="FormularioAjax" action="<?= APP_URL . "reports" ?>" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="week" id="input-week">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label for="file-cdp" class="form-label">
                                    <i class="fas fa-file-excel me-1"></i>CDP (Excel XLSX)
                                </label>
                                <input type="file" class="form-control" id="file-cdp" name="cdp" accept=".xlsx, .xls">
                            </div>
                            <div class="col-md-4">
                                <label for="file-rp" class="form-label">
                                    <i class="fas fa-file-excel me-1"></i>R.P (Excel XLSX)
                                </label>
                                <input type="file" class="form-control" id="file-rp" name="rp" accept=".xlsx, .xls">
                            </div>
                            <div class="col-md-4">
                                <label for="file-pagos" class="form-label">
                                    <i class="fas fa-file-excel me-1"></i>Pagos (Excel XLSX)
                                </label>
                                <input type="file" class="form-control" id="file-pagos" name="pagos" accept=".xlsx, .xls">
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

    <!-- Modal Ver Detalles - FULLSCREEN -->
    <div class="modal fade modal-reports" id="modalDetalles" tabindex="-1" aria-labelledby="modalDetallesLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content reports-modal reports-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetallesLabel">
                        <i class="fas fa-search me-2"></i>
                        Detalles de <span class="text-muted" id="modal-detalles-week-label"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <!-- Filtros Mejorados -->
                    <div class="row mb-3 g-2 align-items-center">
                        <div class="col-auto">
                            <label for="modal-dependency-input" class="form-label small fw-semibold mb-1">Dependencia:</label>
                            <input id="modal-dependency-input" list="dependencias-list" class="form-control form-control-sm" placeholder="Todas" autocomplete="off" style="width: 200px;" />
                        </div>
                        <div class="col-auto">
                            <label for="modal-rubro-input" class="form-label small fw-semibold mb-1">Rubro:</label>
                            <input id="modal-rubro-input" list="rubros-list" class="form-control form-control-sm" placeholder="Todos" autocomplete="off" style="width: 180px;" />
                        </div>
                        <div class="col-auto">
                            <label for="modal-fuente-input" class="form-label small fw-semibold mb-1">Fuente:</label>
                            <select id="modal-fuente-input" class="form-select form-select-sm" style="width: 150px;">
                                <option value="">Todas</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <label class="form-label small fw-semibold mb-1">&nbsp;</label>
                            <div class="d-flex gap-1">
                                <button class="btn btn-success btn-sm" id="btn-modal-buscar">
                                    <i class="fas fa-search me-1"></i>Filtrar
                                </button>
                                <button class="btn btn-outline-secondary btn-sm" id="btn-modal-reset">
                                    <i class="fas fa-redo me-1"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-auto ms-auto">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-light text-dark">
                                    <i class="fas fa-list me-1"></i>
                                    <span id="total-registros">0</span> registros
                                </span>
                                <span class="badge bg-primary">
                                    <i class="fas fa-dollar-sign me-1"></i>
                                    S/ <span id="total-monto">0</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <datalist id="dependencias-list">
                        <!-- Opciones dinámicas cargadas por JS -->
                    </datalist>
                    <datalist id="rubros-list">
                        <!-- Opciones dinámicas cargadas por JS -->
                    </datalist>

                    <!-- Tabla de detalles -->
                    <div class="table-scroll-wrapper">
                        <table class="table table-striped table-hover reports-table">
                            <thead class="table-dark">
                                <tr>
                                    <th>Número CDP</th>
                                    <th>Fecha de Registro</th>
                                    <th>Concepto Interno</th>
                                    <th>Rubro</th>
                                    <th>Descripción</th>
                                    <th>Fuente</th>
                                    <th class="text-end">Valor Actual</th>
                                    <th class="text-end">Saldo por Comprometer</th>
                                    <th class="text-end">Valor Comprometido</th>
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

<style>
    .reports-page {
        background-color: #f5f7fa;
    }

    .rp-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        border: 1px solid #dee2e6;
        transition: all 0.3s ease;
    }

    .rp-card:hover {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    .subheader {
        color: #2c3e50;
        font-weight: 600;
    }

    .table-responsive {
        border-radius: 6px;
    }

    .reports-table th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        color: #495057;
        font-size: 0.875rem;
        padding: 8px 6px;
    }

    .reports-table td {
        vertical-align: middle;
        border-color: #e9ecef;
        font-size: 0.875rem;
        padding: 6px;
    }

    .reports-table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .btn {
        border-radius: 4px;
        font-weight: 500;
    }

    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    .modal-content {
        border: none;
        border-radius: 8px;
    }

    .modal-fullscreen .modal-content {
        border-radius: 0;
    }

    .table-scroll-wrapper {
        max-height: calc(100vh - 180px);
        overflow-y: auto;
        border: 1px solid #e9ecef;
        border-radius: 6px;
    }

    /* Filtros mejorados */
    .form-label.small {
        font-size: 0.8rem;
        margin-bottom: 0.25rem;
    }

    .form-control-sm,
    .form-select-sm {
        font-size: 0.875rem;
    }

    .badge {
        font-size: 0.75rem;
        font-weight: 500;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .reports-layout .order-2 {
            order: 2 !important;
        }

        .reports-layout .order-1 {
            order: 1 !important;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        .reports-table {
            font-size: 0.75rem;
        }

        .d-flex.gap-1.flex-wrap {
            gap: 0.25rem !important;
        }

        /* Filtros responsive */
        .row.g-2 .col-auto {
            margin-bottom: 0.5rem;
        }

        .form-control-sm,
        .form-select-sm {
            width: 100% !important;
        }
    }

    @media (max-width: 576px) {
        .rp-card {
            padding: 1rem !important;
        }

        .table-responsive {
            font-size: 0.7rem;
        }

        .modal-fullscreen {
            padding: 0;
        }

        .reports-table th,
        .reports-table td {
            padding: 4px;
            font-size: 0.7rem;
        }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?= APP_URL ?>js/reports/reports.js"></script>