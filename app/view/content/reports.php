<div class="container-fluid mt-4 reports-page">

    <!-- Header de la página -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold text-primary mb-1">
                        <i class="fas fa-chart-line me-2"></i>Reportes Presupuestales
                    </h2>
                    <p class="text-muted mb-0">Sistema de gestión y análisis de presupuesto semanal</p>
                </div>
                <div class="text-end">
                    <span class="badge bg-info fs-6">
                        <i class="fas fa-calendar me-1"></i>
                        <?= date('d/m/Y') ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido en dos columnas: izquierda (tabla) | derecha (gráfico) -->
    <div class="row g-4 reports-layout">
        <!-- Columna Tabla -->
        <div class="col-12 col-xl-6 order-2 order-lg-1">
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
                                            Semana <?= $semana['numero_semana'] ?>
                                        </td>
                                        <td>
                                            <span class="text-muted"><?= date("d/m/Y", strtotime($semana['fecha_inicio'])) ?></span>
                                        </td>
                                        <td>
                                            <span class="text-muted"><?= date("d/m/Y", strtotime($semana['fecha_fin'])) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <?php if (!empty($semana['archivo_cdp']) || !empty($semana['archivo_rp']) || !empty($semana['archivo_pagos'])): ?>
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
                                                    data-week="Semana <?= $semana['numero_semana'] ?>"
                                                    data-semana-id="<?= $semana['id'] ?>"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalDetalles"
                                                    title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </button>

                                                <?php if (empty($semana['archivo_cdp']) && empty($semana['archivo_rp']) && empty($semana['archivo_pagos'])): ?>
                                                    <button class="btn btn-primary btn-sm btn-open-modal"
                                                        data-week="Semana <?= $semana['numero_semana'] ?>"
                                                        data-semana-id="<?= $semana['id'] ?>"
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
                <div class="card-footer bg-light py-3">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Total <?= count($semanas) ?> semanas registradas
                    </small>
                </div>
            </div>
        </div>

        <!-- Columna Gráficas -->
        <div class="col-12 col-xl-6 order-1 order-lg-2">
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
                </div>
                <div class="card-body">

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
                </div>
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
                                                        <datalist id="dependencias-list"></datalist>
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

                                <!-- Columna Mini Gráfica -->
                                <div class="col-lg-4 col-md-5">
                                    <div id="mini-presupuesto-container" class="card border-0 bg-gradient-info text-white shadow" style="display:none;">
                                        <div class="card-header bg-transparent border-bottom-0 py-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0 fw-semibold">
                                                    <i class="fas fa-chart-pie me-2"></i>Resumen
                                                </h6>
                                                <button type="button" class="btn btn-sm btn-light text-dark p-1" id="mini-hide-btn" title="Ocultar">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                            <small class="opacity-75" id="mini-presupuesto-label"></small>
                                        </div>
                                        <div class="card-body p-3">
                                            <canvas id="mini-presupuesto-chart" height="160"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Panel de Resultados -->
                    <div class="card shadow-sm border-0">
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

                        <!-- Tabla Mejorada -->
                        <div class="table-responsive" style="max-height: 60vh;">
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

                        <!-- Footer de la tabla -->
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
            </div>
        </div>
    </div>
</div>

<style>
    /* COLORES SENA - VERDE HIJUEPUTA */
    .reports-page {
        background: linear-gradient(135deg, #f0f9f0 0%, #e6f3e6 100%);
        min-height: 100vh;
    }

    .card {
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: transform 0.2s ease-in-out;
        background: white;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 168, 89, 0.15);
    }

    .bg-gradient-primary {
        background: linear-gradient(135deg, #006837 0%, #00a859 100%) !important;
    }

    .bg-gradient-info {
        background: linear-gradient(135deg, #00c06b 0%, #00a859 100%) !important;
    }

    .bg-primary {
        background-color: #00a859 !important;
    }

    .text-primary {
        color: #00a859 !important;
    }

    .btn-primary {
        background-color: #00a859;
        border-color: #00a859;
        font-weight: 600;
    }

    .btn-primary:hover {
        background-color: #006837;
        border-color: #006837;
        transform: translateY(-1px);
    }

    .btn-success {
        background-color: #00a859;
        border-color: #00a859;
    }

    .btn-success:hover {
        background-color: #006837;
        border-color: #006837;
    }

    .btn-outline-primary {
        color: #00a859;
        border-color: #00a859;
    }

    .btn-outline-primary:hover {
        background-color: #00a859;
        border-color: #00a859;
        color: white;
    }

    /* Headers de cards con verde sena */
    .card-header.bg-white {
        background-color: #e8f5e8 !important;
        border-bottom: 2px solid #00a859;
    }

    .card-header.bg-light {
        background-color: #e8f5e8 !important;
        border-bottom: 2px solid #00a859;
    }

    /* Badges verde sena */
    .badge.bg-primary {
        background-color: #00a859 !important;
    }

    .badge.bg-success {
        background-color: #00a859 !important;
    }

    .badge.bg-info {
        background-color: #00c06b !important;
    }

    .badge.bg-warning {
        background-color: #ffc107 !important;
        color: #000;
    }

    /* Tablas con acentos verde */
    .table-dark {
        background: linear-gradient(135deg, #006837 0%, #00a859 100%) !important;
    }

    .table-light {
        background-color: #e8f5e8 !important;
    }

    /* Hover effects verde */
    .card:hover {
        border-left: 4px solid #00a859;
    }

    /* Iconos en verde */
    .text-primary i,
    .text-muted i.text-primary {
        color: #00a859 !important;
    }

    /* Estados de compromiso */
    .verde {
        background: linear-gradient(135deg, #d4edda 0%, #00a859 100%) !important;
        color: #004d29 !important;
        font-weight: 600;
    }

    .naranja {
        background: linear-gradient(135deg, #fff3cd 0%, #ffc107 100%) !important;
        color: #856404 !important;
        font-weight: 600;
    }

    .rojo {
        background: linear-gradient(135deg, #f8d7da 0%, #dc3545 100%) !important;
        color: #721c24 !important;
        font-weight: 600;
    }

    .table th {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
    }

    .table td {
        font-size: 0.875rem;
        vertical-align: middle;
    }

    .sticky-top {
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .font-monospace {
        font-family: 'SF Mono', Monaco, 'Cascadia Code', 'Roboto Mono', Consolas, 'Courier New', monospace;
    }

    .btn {
        border-radius: 6px;
        font-weight: 500;
        transition: all 0.2s ease-in-out;
    }

    .btn:hover {
        transform: translateY(-1px);
    }

    .btn-sm {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
    }

    .badge {
        font-size: 0.75rem;
        padding: 0.35rem 0.65rem;
    }

    .modal-content {
        border: none;
        border-radius: 12px;
        box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175);
    }

    .modal-header {
        border-radius: 12px 12px 0 0;
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
        line-height: 1.4;
    }

    .chart-container {
        padding: 1rem;
    }

    .main-chart {
        border-radius: 8px;
        background: #fff;
        padding: 1rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    /* Alertas con verde sena */
    .alert-info {
        background-color: #e8f5e8;
        border-color: #00a859;
        color: #006837;
    }

    .alert-info .text-info {
        color: #00a859 !important;
    }

    /* Footer de cards */
    .card-footer.bg-light {
        background-color: #f0f9f0 !important;
        border-top: 1px solid #00a859;
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

        .table {
            font-size: 0.75rem;
        }

        .card-body {
            padding: 1rem !important;
        }

        .modal-body {
            padding: 1rem !important;
        }
    }

    @media (max-width: 576px) {
        .container-fluid {
            padding: 1rem;
        }

        .card {
            margin-bottom: 1rem;
        }

        .table-responsive {
            font-size: 0.7rem;
        }

        .btn-group-vertical .btn {
            margin-bottom: 0.25rem;
        }
    }

    /* Animaciones */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .card,
    .modal-content {
        animation: fadeIn 0.3s ease-in-out;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?= APP_URL ?>js/reports/reports.js"></script>