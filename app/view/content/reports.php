<div class="container-fluid  reports-page">
    <!-- Contenido en dos columnas: izquierda (tabla) | derecha (gráfico) -->
    <div class="row g-4 reports-layout">
        <!-- Columna Tabla -->
        <div class="col-12 col-xl order-2 order-lg-1">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="card-title mb-0 fw-bold text-primary">
                        <i class="fas fa-folder-open me-2"></i>Archivos Semanales
                    </h5>
                    <p class="text-muted small mb-0 mt-1">Gestión de reportes de presupuesto por semana</p>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">
                                        <i class="fas fa-calendar-week me-1 text-muted"></i>Semana
                                    </th>
                                    <th>
                                        <i class="fas fa-play me-1 text-muted"></i>Desde
                                    </th>
                                    <th>
                                        <i class="fas fa-stop me-1 text-muted"></i>Hasta
                                    </th>
                                    <th class="text-center">
                                        <i class="fas fa-info-circle me-1 text-muted"></i>Estado
                                    </th>
                                    <th class="text-center pe-4">
                                        <i class="fas fa-cogs me-1 text-muted"></i>Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($semanas as $semana): ?>
                                    <tr class="border-bottom">
                                        <td class="ps-4 fw-bold text-dark">
                                            <i class="fas fa-calendar-alt me-2 text-primary"></i>
                                            Semana <?= $semana['numeroSemana'] ?>
                                        </td>
                                        <td>
                                            <span class="text-muted"><?= date("d/m/Y", strtotime($semana['fechaInicio'])) ?></span>
                                        </td>
                                        <td>
                                            <span class="text-muted"><?= date("d/m/Y", strtotime($semana['fechaFin'])) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <?php if (!empty($semana['archivoCdp']) || !empty($semana['archivoRp']) || !empty($semana['archivoPagos'])): ?>
                                                <span class="badge bg-success rounded-pill">
                                                    <i class="fas fa-check me-1"></i>Cargado
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-warning rounded-pill">
                                                    <i class="fas fa-clock me-1"></i>Pendiente
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center pe-4">
                                            <div class="d-flex gap-2 justify-content-center">
                                                <button class="btn btn-outline-primary btn-sm btn-ver-detalles"
                                                    data-week="Semana <?= $semana['numeroSemana'] ?>"
                                                    data-semana-id="<?= $semana['idSemana'] ?>"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalDetalles"
                                                    title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <?php if (empty($semana['archivoCdp']) && empty($semana['archivoRp']) && empty($semana['archivoPagos'])): ?>
                                                    <button class="btn btn-primary btn-sm btn-open-modal"
                                                        data-week="Semana <?= $semana['numeroSemana'] ?>"
                                                        data-semana-id="<?= $semana['idSemana'] ?>"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalReporte"
                                                        title="Subir reporte">
                                                        <i class="fas fa-upload"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-success btn-sm" disabled
                                                        title="Archivos cargados">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <button class="btn btn-outline-danger btn-sm btn-delete-week"
                                                    data-week="Semana <?= $semana['numeroSemana'] ?>"
                                                    data-semana-id="<?= $semana['idSemana'] ?>"
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
                <div class="card-footer bg-light py-3">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Total <?= count($semanas) ?> semanas registradas
                    </small>
                </div>
            </div>
        </div>
        <!-- Columna Gráficas -->
        <!-- <div class="col-12 col-xl-6 order-1 order-lg-2">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                        <div>
                            <h5 class="card-title mb-0 fw-bold text-primary">
                                <i class="fas fa-chart-bar me-2"></i>Panel Analítico
                            </h5>
                            <p class="text-muted small mb-0 mt-1">Visualización de estado presupuestal y compromisos</p>
                        </div>
                        <div class="chart-selector">
                            <label for="chart-select" class="form-label fw-semibold small mb-1 me-2">Vista:</label>
                            <select id="chart-select" class="form-select form-select-sm w-auto">
                                <option value="presupuesto" selected>
                                    <i class="fas fa-chart-pie me-1"></i>Estado Presupuesto
                                </option>
                                <option value="gastos">
                                    <i class="fas fa-chart-bar me-1"></i>Distribución
                                </option>
                                <option value="dependencias">
                                    <i class="fas fa-building me-1"></i>Por Dependencia
                                </option>
                            </select>
                        </div>
                    </div>
                </div> -->
        <!-- <div class="card-body">
                    <div id="chart-presupuesto" class="chart-container">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0 fw-semibold text-dark">
                                <i class="fas fa-chart-pie me-2 text-primary"></i>Estado del Presupuesto
                            </h6>
                            <div class="total-budget">
                                <span class="badge bg-primary fs-6">
                                    <i class="fas fa-money-bill-wave me-1"></i>
                                    Total: <span id="total-presupuesto">$0</span>
                                </span>
                            </div>
                        </div>
                        <p class="text-muted small mb-3">Comparación de valores Inicial, Operaciones, Actual, Comprometido y Saldo</p>
                        <canvas id="canvas-presupuesto" class="budget-chart main-chart" height="300"></canvas>
                    </div>
                    <div id="chart-gastos" class="chart-container" style="display:none;">
                        <h6 class="fw-semibold text-dark mb-3">
                            <i class="fas fa-chart-bar me-2 text-primary"></i>Distribución de Gastos
                        </h6>
                        <p class="text-muted small mb-3">Relación entre valores comprometidos y saldo por comprometer</p>
                        <canvas id="canvas-gastos" class="main-chart" height="300"></canvas>
                    </div>
                    <div id="chart-dependencias" class="chart-container" style="display:none;">
                        <h6 class="fw-semibold text-dark mb-3">
                            <i class="fas fa-building me-2 text-primary"></i>Comprometido por Dependencia
                        </h6>
                        <p class="text-muted small mb-3">Top dependencias según valor comprometido</p>
                        <canvas id="canvas-dependencias" class="main-chart" height="300"></canvas>
                    </div>
                </div> -->
    </div>
</div>
</div>

<!-- Modal Subir Reporte -->
<div class="modal fade modal-reports" id="modalReporte" tabindex="-1" aria-labelledby="modalReporteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-upload me-2"></i>
                    Subir Reporte <span class="text-warning" id="modal-week-label"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formReporte" class="FormularioAjax" action="<?= APP_URL . "reports" ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="week" id="input-week">
                    <input type="hidden" name="semana_id" id="input-semana-id">
                    <input type="text" name="centro_id" id="input-centro-id">
                    <div class="alert alert-info border-0">
                        <div class="d-flex">
                            <i class="fas fa-info-circle fa-2x text-info me-3"></i>
                            <div>
                                <h6 class="alert-heading mb-1">Formato requerido</h6>
                                <p class="mb-0 small">Cada archivo Excel debe tener exactamente 23 columnas según el formato establecido.</p>
                            </div>
                        </div>
                    </div>
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="card border h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-file-excel fa-3x text-success mb-3"></i>
                                    <h6 class="card-title fw-semibold">CDP</h6>
                                    <p class="text-muted small mb-3">Certificado de Disponibilidad Presupuestal</p>
                                    <input type="file" class="form-control" id="file-cdp" name="cdp" accept=".xlsx, .xls">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-file-invoice-dollar fa-3x text-warning mb-3"></i>
                                    <h6 class="card-title fw-semibold">R.P</h6>
                                    <p class="text-muted small mb-3">Registro Presupuestal</p>
                                    <input type="file" class="form-control" id="file-rp" name="rp" accept=".xlsx, .xls">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card border h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-receipt fa-3x text-info mb-3"></i>
                                    <h6 class="card-title fw-semibold">Pagos</h6>
                                    <p class="text-muted small mb-3">Registro de pagos ejecutados</p>
                                    <input type="file" class="form-control" id="file-pagos" name="pagos" accept=".xlsx, .xls">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-4">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Guardar Archivos
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ver Detalles - FULLSCREEN -->
<div class="modal fade modal-reports" id="modalDetalles" tabindex="-1" aria-labelledby="modalDetallesLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content border-0">
            <!-- Header mejorado -->
            <div class="modal-header bg-gradient-primary text-white">
                <div class="d-flex align-items-center w-100">
                    <div class="flex-grow-1">
                        <h5 class="modal-title mb-0 fw-bold">
                            <i class="fas fa-chart-bar me-2"></i>
                            Detalles de <span class="text-warning" id="modal-detalles-week-label"></span>
                        </h5>
                        <small class="opacity-75">Análisis detallado del presupuesto y compromisos</small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
            </div>
            <div class="modal-body p-4">
                <!-- Panel de Filtros Mejorado -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-light py-3">
                        <h6 class="mb-0 fw-semibold text-primary">
                            <i class="fas fa-filter me-2"></i>Filtros de Búsqueda
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 align-items-end">
                            <!-- Columna Filtros -->
                            <div class="col-lg-8 col-md-7">
                                <div class="row g-3">
                                    <!-- Fila 1: Filtros Principales -->
                                    <div class="col-12">
                                        <div class="row g-3 align-items-end">
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold text-dark mb-2">
                                                    <i class="fas fa-building me-1 text-muted"></i>Dependencia
                                                </label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-light border-end-0">
                                                        <i class="fas fa-search text-muted"></i>
                                                    </span>
                                                    <input id="modal-dependency-input" list="dependencias-list"
                                                        class="form-control border-start-0"
                                                        placeholder="Buscar dependencia..."
                                                        autocomplete="off">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold text-dark mb-2">
                                                    <i class="fas fa-tags me-1 text-muted"></i>Concepto
                                                </label>
                                                <select id="filtro-concepto" class="form-select form-select-sm">
                                                    <option value="">Todos los conceptos</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold text-dark mb-2">
                                                    <i class="fas fa-cog me-1 text-muted"></i>Acciones
                                                </label>
                                                <div class="d-flex gap-2">
                                                    <button class="btn btn-success btn-sm flex-fill" id="btn-modal-buscar">
                                                        <i class="fas fa-search me-1"></i>Buscar
                                                    </button>
                                                    <button class="btn btn-outline-secondary btn-sm" id="btn-limpiar-filtros" title="Limpiar filtros">
                                                        <i class="fas fa-eraser"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Fila 2: Filtros Adicionales -->
                                    <div class="col-12">
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold text-dark mb-2">
                                                    <i class="fas fa-money-bill-wave me-1 text-muted"></i>Estado Pagos
                                                </label>
                                                <select id="filtro-pagos" class="form-select form-select-sm">
                                                    <option value="">Todos</option>
                                                    <option value="con_pagos" class="text-success">✓ Con pagos</option>
                                                    <option value="sin_pagos" class="text-danger">✗ Sin pagos</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold text-dark mb-2">
                                                    <i class="fas fa-file-contract me-1 text-muted"></i>Contrato
                                                </label>
                                                <select id="filtro-contrato" class="form-select form-select-sm">
                                                    <option value="">Todos</option>
                                                    <option value="con_contrato" class="text-success">✓ Con contrato</option>
                                                    <option value="sin_contrato" class="text-danger">✗ Sin contrato</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold text-dark mb-2">
                                                    <i class="fas fa-chart-line me-1 text-muted"></i>Rango de Valores
                                                </label>
                                                <div class="input-group input-group-sm">
                                                    <input type="number" class="form-control" placeholder="Mínimo" id="filtro-valor-min">
                                                    <span class="input-group-text bg-light">-</span>
                                                    <input type="number" class="form-control" placeholder="Máximo" id="filtro-valor-max">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Columna Mini Gráfica (vacía aquí, se mueve abajo) -->
                            <div class="col-lg-4 col-md-5"></div>
                        </div>
                    </div>
                </div>

                <!-- NUEVO LAYOUT: Tabla + Gráfica en columnas -->
                <div class="row g-3">
                    <!-- Columna Izquierda: Tabla con scroll -->
                    <div class="col-12 col-lg-7">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-semibold text-primary">
                                    <i class="fas fa-table me-2"></i>Resultados de la Búsqueda
                                    <span class="badge bg-primary ms-2" id="contador-resultados">0</span>
                                </h6>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-primary btn-sm" id="btn-exportar">
                                        <i class="fas fa-download me-1"></i>Exportar
                                    </button>
                                    <button class="btn btn-outline-secondary btn-sm" id="btn-refrescar">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                            </div>
                            <!-- Contenedor con scroll vertical limitado -->
                            <div class="table-responsive" style="max-height: 60vh; overflow-y: auto;">
                                <table class="table table-hover table-striped align-middle mb-0">
                                    <thead class="table-dark sticky-top">
                                        <tr>
                                            <th width="120" class="text-center">
                                                <i class="fas fa-hashtag me-1"></i>CDP
                                            </th>
                                            <th width="110" class="text-center">
                                                <i class="fas fa-calendar me-1"></i>Fecha
                                            </th>
                                            <th width="200">
                                                <i class="fas fa-tag me-1"></i>Concepto
                                            </th>
                                            <th width="150">
                                                <i class="fas fa-folder me-1"></i>Rubro
                                            </th>
                                            <th width="200">
                                                <i class="fas fa-align-left me-1"></i>Descripción
                                            </th>
                                            <th width="100" class="text-center">
                                                <i class="fas fa-fountain me-1"></i>Fuente
                                            </th>
                                            <th width="140" class="text-end">
                                                <i class="fas fa-money-bill me-1"></i>Valor Actual
                                            </th>
                                            <th width="140" class="text-end">
                                                <i class="fas fa-wallet me-1"></i>Saldo
                                            </th>
                                            <th width="140" class="text-end">
                                                <i class="fas fa-hand-holding-usd me-1"></i>Comprometido
                                            </th>
                                            <th width="120" class="text-center">
                                                <i class="fas fa-percentage me-1"></i>% Compromiso
                                            </th>
                                            <th width="250">
                                                <i class="fas fa-bullseye me-1"></i>Objeto
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="tabla-detalles-body" class="font-monospace">
                                        <tr>
                                            <td colspan="11" class="text-center text-muted py-5">
                                                <i class="fas fa-search fa-2x mb-3 d-block"></i>
                                                Utilice los filtros para realizar una búsqueda
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer bg-light py-3">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Mostrando <span id="filas-mostradas">0</span> registros
                                        </small>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <small class="text-muted">
                                            Total presupuesto: <span class="fw-bold text-success" id="total-presupuesto-footer">$0</span>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Columna Derecha: Mini Gráfica (al lado de la tabla) -->
                    <div class="col-12 col-lg-5">
                        <!-- Contenedor para gráfica de CDP individual -->
                        <div id="cdp-individual-container" class="card border shadow-sm" style="display:none;">
                            <div class="card-header bg-white border-bottom py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 fw-semibold text-primary">
                                        <i class="fas fa-chart-pie me-2"></i>Detalle CDP
                                    </h6>
                                    <button type="button" class="btn btn-sm btn-outline-secondary p-1" id="cdp-hide-btn" title="Ocultar">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <small class="text-muted" id="cdp-individual-label"></small>
                            </div>
                            <div class="card-body p-3">
                                <canvas id="cdp-individual-chart" height="280"></canvas>
                            </div>
                        </div>

                        <!-- Contenedor original para gráfica general -->
                        <div id="mini-presupuesto-container" class="card border shadow-sm">
                            <div class="card-header bg-white border-bottom py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 fw-semibold text-primary">
                                        <i class="fas fa-chart-pie me-2"></i>Resumen
                                    </h6>
                                    <button type="button" class="btn btn-sm btn-outline-secondary p-1" id="mini-hide-btn" title="Ocultar">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <small class="text-muted" id="mini-presupuesto-label"></small>
                            </div>
                            <div class="card-body p-3">
                                <canvas id="mini-presupuesto-chart" height="280"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- DATALISTS FUERA DEL MODAL -->
<datalist id="dependencias-list"></datalist>
<datalist id="rubros-list"></datalist>
</div>

<style>
    /* COLORES SENA - DEBE IR DESPUÉS DE BOOTSTRAP */
    .reports-page {
        background: linear-gradient(135deg, #f0f9f0 0%, #e6f3e6 100%) !important;
        min-height: 100vh;
    }

    .bg-gradient-primary {
        background: linear-gradient(135deg, #006837 0%, #00a859 100%) !important;
    }

    .bg-primary {
        background-color: #00a859 !important;
    }

    .text-primary {
        color: #00a859 !important;
    }

    .btn-primary {
        background-color: #00a859 !important;
        border-color: #00a859 !important;
    }

    .btn-primary:hover {
        background-color: #006837 !important;
        border-color: #006837 !important;
    }

    .btn-success {
        background-color: #00a859 !important;
        border-color: #00a859 !important;
    }

    .btn-outline-primary {
        color: #00a859 !important;
        border-color: #00a859 !important;
    }

    .btn-outline-primary:hover {
        background-color: #00a859 !important;
        border-color: #00a859 !important;
        color: white !important;
    }

    .badge.bg-primary {
        background-color: #00a859 !important;
    }

    .badge.bg-success {
        background-color: #00a859 !important;
    }

    .table-dark {
        background: linear-gradient(135deg, #006837 0%, #00a859 100%) !important;
    }

    .card-header.bg-white {
        background-color: #e8f5e8 !important;
        border-bottom: 2px solid #00a859 !important;
    }

    .card-header.bg-light {
        background-color: #e8f5e8 !important;
        border-bottom: 2px solid #00a859 !important;
    }

    .card-footer.bg-light {
        background-color: #f0f9f0 !important;
        border-top: 1px solid #00a859 !important;
    }

    .modal-header.bg-primary {
        background: linear-gradient(135deg, #006837 0%, #00a859 100%) !important;
    }

    .text-primary i,
    .fas.text-primary {
        color: #00a859 !important;
    }

    .verde {
        background: linear-gradient(135deg, #d4edda 0%, #00a859 100%) !important;
        color: #004d29 !important;
    }

    .naranja {
        background: linear-gradient(135deg, #fff3cd 0%, #ffc107 100%) !important;
        color: #856404 !important;
    }

    .rojo {
        background: linear-gradient(135deg, #f8d7da 0%, #dc3545 100%) !important;
        color: #721c24 !important;
    }

    .alert-info {
        background-color: #e8f5e8 !important;
        border-color: #00a859 !important;
        color: #006837 !important;
    }

    .alert-info .text-info {
        color: #00a859 !important;
    }

    .card:hover {
        border-left: 4px solid #00a859 !important;
    }

    /* Mini gráfica: fondo blanco */
    #mini-presupuesto-container,
    #cdp-individual-container {
        background: white !important;
        border: 1px solid #dee2e6 !important;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05) !important;
        border-radius: 8px !important;
        padding: 1rem !important;
    }

    #mini-presupuesto-container .card-header,
    #mini-presupuesto-container .card-body,
    #cdp-individual-container .card-header,
    #cdp-individual-container .card-body {
        background: white !important;
        border: none !important;
        padding: 0 !important;
    }

    #mini-presupuesto-container .card-header,
    #cdp-individual-container .card-header {
        margin-bottom: 1rem;
    }

    #mini-presupuesto-container canvas,
    #cdp-individual-container canvas {
        max-height: 240px !important;
        width: 100% !important;
    }

    #mini-hide-btn,
    #cdp-hide-btn {
        font-size: 0.75rem !important;
        padding: 0.25rem 0.5rem !important;
    }

    /* Scroll en tabla del modal */
    .table-responsive::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: #00a859;
        border-radius: 4px;
    }

    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #006837;
    }

    /* Estilos para CDP clickeable */
    .cdp-clickable {
        cursor: pointer;
        color: #00a859;
        font-weight: bold;
        text-decoration: underline;
        transition: all 0.3s ease;
        padding: 4px 8px;
        border-radius: 4px;
    }

    .cdp-clickable:hover {
        color: #006837;
        background-color: #f0f9f0;
        transform: scale(1.05);
    }

    .cdp-active {
        background-color: #00a859 !important;
        color: white !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    @media (max-width: 991.98px) {

        .modal-body .col-lg-7,
        .modal-body .col-lg-5 {
            width: 100% !important;
        }

        #mini-presupuesto-container,
        #cdp-individual-container {
            margin-top: 1rem;
        }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>
<script src="<?= APP_URL ?>js/reports/reports.js"></script>