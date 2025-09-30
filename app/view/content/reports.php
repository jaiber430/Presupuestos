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
                            <?php foreach ($semanas as $semana): ?>
                                <tr>
                                    <td class="fw-semibold">Semana <?= $semana['numero_semana'] ?></td>
                                    <td><?= date("d/m/Y", strtotime($semana['fecha_inicio'])) ?></td>
                                    <td><?= date("d/m/Y", strtotime($semana['fecha_fin'])) ?></td>
                                    <td>
                                        <div class="d-flex gap-1 flex-wrap">
                                            <button class="btn btn-outline-secondary btn-sm btn-ver-detalles"
                                                data-week="Semana <?= $semana['numero_semana'] ?>"
                                                data-semana-id="<?= $semana['id'] ?>"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalDetalles"
                                                title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>

                                            <button class="btn btn-primary btn-sm btn-open-modal"
                                                data-week="Semana <?= $semana['numero_semana'] ?>"
                                                data-semana-id="<?= $semana['id'] ?>"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalReporte"
                                                title="Subir reporte">
                                                <i class="fas fa-upload"></i>
                                            </button>

                                            <button class="btn btn-danger btn-sm btn-delete-week"
                                                data-week="Semana <?= $semana['numero_semana'] ?>"
                                                data-semana-id="<?= $semana['id'] ?>"
                                                title="Eliminar semana">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
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
                    <form id="formReporte" class="FormularioAjax" action="<?= APP_URL . "reports" ?>" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="week" id="input-week">
                        <input type="hidden" name="semana_id" id="input-semana-id">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label for="file-cdp" class="form-label">CDP (Excel)</label>
                                <input type="file" class="form-control" id="file-cdp" name="cdp" accept=".xlsx, .xls">
                            </div>
                            <div class="col-md-4">
                                <label for="file-rp" class="form-label">R.P (Excel)</label>
                                <input type="file" class="form-control" id="file-rp" name="rp" accept=".xlsx, .xls">
                            </div>
                            <div class="col-md-4">
                                <label for="file-pagos" class="form-label">Pagos (Excel)</label>
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
                    <h5 class="modal-title" id="modalDetallesLabel">Detalles de <span class="text-muted" id="modal-detalles-week-label"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <!-- Filtros + Mini gráfica - CON NUEVOS FILTROS -->
                    <div class="row mb-3 g-3 align-items-stretch">
                        <div class="col-lg-8 col-md-7 col-12">
                            <div class="reports-header p-3 h-100">
                                <!-- Fila 1: Filtros principales -->
                                <div class="d-flex align-items-center flex-wrap gap-3 mb-2">
                                    <!-- Dependencia -->
                                    <div class="d-flex align-items-center gap-2">
                                        <label for="modal-dependency-input" class="form-label m-0 small fw-semibold">Dependencia:</label>
                                        <input id="modal-dependency-input" list="dependencias-list" class="form-control form-control-sm" placeholder="Todas" autocomplete="off" style="width: 180px;" />
                                        <datalist id="dependencias-list">
                                            <!-- Opciones dinámicas cargadas por JS -->
                                        </datalist>
                                    </div>

                                    <!-- Concepto Interno -->
                                    <div class="d-flex align-items-center gap-2">
                                        <label for="filtro-concepto" class="form-label m-0 small fw-semibold">Concepto:</label>
                                        <select id="filtro-concepto" class="form-select form-select-sm" style="width: 200px;">
                                            <option value="">Todos los conceptos</option>
                                        </select>
                                    </div>

                                    <!-- Botón Buscar -->
                                    <button class="btn btn-success btn-sm" id="btn-modal-buscar">
                                        <i class="fas fa-search me-1"></i>Buscar
                                    </button>
                                </div>

                                <!-- Fila 2: Filtros adicionales -->
                                <div class="d-flex align-items-center flex-wrap gap-3">
                                    <!-- Informe de Pagos -->
                                    <div class="d-flex align-items-center gap-2">
                                        <label for="filtro-pagos" class="form-label m-0 small fw-semibold">Pagos:</label>
                                        <select id="filtro-pagos" class="form-select form-select-sm" style="width: 150px;">
                                            <option value="">Todos</option>
                                            <option value="con_pagos">Con pagos</option>
                                            <option value="sin_pagos">Sin pagos</option>
                                        </select>
                                    </div>

                                    <!-- Informe de Contrato -->
                                    <div class="d-flex align-items-center gap-2">
                                        <label for="filtro-contrato" class="form-label m-0 small fw-semibold">Contrato:</label>
                                        <select id="filtro-contrato" class="form-select form-select-sm" style="width: 150px;">
                                            <option value="">Todos</option>
                                            <option value="con_contrato">Con contrato</option>
                                            <option value="sin_contrato">Sin contrato</option>
                                        </select>
                                    </div>

                                    <!-- Botón Limpiar Filtros -->
                                    <button class="btn btn-outline-secondary btn-sm" id="btn-limpiar-filtros">
                                        <i class="fas fa-times me-1"></i>Limpiar
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
                                    <th>Número CDP</th>
                                    <th>Fecha de Registro</th>
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
                                <!-- Datos cargados dinámicamente -->
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
    }

    .reports-table td {
        vertical-align: middle;
        border-color: #e9ecef;
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
        max-height: calc(100vh - 200px);
        overflow-y: auto;
        border: 1px solid #e9ecef;
        border-radius: 6px;
    }

    /* Estilos para las clases de compromiso del JS */
    .verde {
        background-color: #d4edda !important;
        color: #155724;
    }

    .naranja {
        background-color: #fff3cd !important;
        color: #856404;
    }

    .rojo {
        background-color: #f8d7da !important;
        color: #721c24;
    }

    .cell-textarea textarea {
        width: 100%;
        border: none;
        background: transparent;
        resize: none;
        font-size: inherit;
        font-family: inherit;
        padding: 0;
        margin: 0;
    }

    /* Estilos para los nuevos filtros */
    .reports-header {
        background: #f8f9fa;
        border-radius: 6px;
        border: 1px solid #e9ecef;
    }

    .form-select-sm {
        font-size: 0.875rem;
        padding: 0.25rem 0.5rem;
    }

    .form-label.small {
        font-size: 0.875rem;
        min-width: max-content;
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

        .reports-header .d-flex {
            gap: 0.5rem !important;
        }

        .form-select-sm,
        .form-control-sm {
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

        .reports-header {
            padding: 1rem !important;
        }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?= APP_URL ?>js/reports/reports.js"></script>