<!-- Obtener el mes y la semana -->
<?php
$meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
$mes_actual = $meses[date('n') - 1];
$ultimo_dia = date('t');
?>
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
                                <?php

                                if ($semanaActiva): ?>
                                    <tr class="border-bottom table-active bg-light">
                                        <td class="ps-4 fw-bold text-dark">
                                            <i class="fas fa-calendar-alt me-2 text-primary"></i>
                                            Semana <?= $semanaActiva['numeroSemana'] ?>
                                            <span class="badge bg-success ms-2">ACTUAL</span>
                                        </td>
                                        <td>
                                            <span class="text-muted"><?= date("d/m/Y", strtotime($semanaActiva['fechaInicio'])) ?></span>
                                        </td>
                                        <td>
                                            <span class="text-muted"><?= date("d/m/Y", strtotime($semanaActiva['fechaFin'])) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <?php if (!empty($semanaActiva['archivoCdp']) || !empty($semanaActiva['archivoRp']) || !empty($semanaActiva['archivoPagos'])): ?>
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
                                                    data-week="Semana <?= $semanaActiva['numeroSemana'] ?>"
                                                    data-semana-id="<?= $semanaActiva['idSemana'] ?>"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalDetalles"
                                                    title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <?php if (empty($semanaActiva['archivoCdp']) && empty($semanaActiva['archivoRp']) && empty($semanaActiva['archivoPagos'])): ?>
                                                    <button class="btn btn-primary btn-sm btn-open-modal"
                                                        data-week="Semana <?= $semanaActiva['numeroSemana'] ?>"
                                                        data-semana-id="<?= $semanaActiva['idSemana'] ?>"
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
                                                    data-week="Semana <?= $semanaActiva['numeroSemana'] ?>"
                                                    data-semana-id="<?= $semanaActiva['idSemana'] ?>"
                                                    title="Eliminar semana">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            No hay semana activa disponible
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-light py-3">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Semana activa actual - <?= date('d/m/Y') ?>
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
                            Detalles de Presupuesto - <span class="text-warning" id="modal-detalles-week-label">Semana <?= $semanaActiva['numeroSemana'] ?? '' ?></span>
                        </h5>
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
                            <?php if ($semanaActiva && $semanaActiva['semanaActiva']): ?>
                                <span class="badge bg-success ms-2">
                                    <i class="fas fa-bolt me-1"></i>ACTUAL
                                </span>
                            <?php endif; ?>
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
                                                    <i class="fas fa-tags me-1 text-muted"></i>Filtrar por
                                                </label>
                                                <select id="filtro-concepto" class="form-select form-select-sm">
                                                    <option value="">Todos los conceptos</option>
                                                    <option value="1">Dependencia</option>
                                                    <option value="2">Numero CDP</option>
                                                    <option value="3">Concepto</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold text-dark mb-2">
                                                    <i class="fas fa-building me-1 text-muted"></i>Buscar por
                                                </label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-light border-end-0">
                                                        <i class="fas fa-search text-muted"></i>
                                                    </span>
                                                    <input id="modal-dependency-input" list="dependencias-list"
                                                        class="form-control border-start-0"
                                                        placeholder="Seleccione un filtro primero..."
                                                        autocomplete="off" disabled>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
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
                                </div>
                            </div>
                            <!-- Columna Información de Semana -->

                            <div class="col-lg-4 col-md-5">
                                <div class="text-end">
                                    <small class="text-muted">
                                        <i class="fas fa-database me-1"></i>
                                        <?= count($informe) ?> registros encontrados
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-semibold text-primary">
                            <i class="fas fa-table me-2"></i>Detalles Presupuestales Completos
                            <span class="badge bg-primary ms-2"><?= count($informe) ?> registros</span>
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

                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="table-dark sticky-top">
                                <tr>
                                    <th width="120" class="text-center">Número CDP</th>
                                    <th width="110" class="text-center">Fecha de Registro</th>
                                    <th width="100" class="text-center">Dependencia</th>
                                    <th width="100" class="text-center">Dependencia Descripción</th>
                                    <th width="200">Concepto Interno</th>
                                    <th width="120">Rubro</th>
                                    <th width="180">Rubro Descripción</th>
                                    <th width="90" class="text-center">Fuente</th>
                                    <th width="120" class="text-end">Valor Inicial</th>
                                    <th width="120" class="text-end">Valor Operaciones</th>
                                    <th width="120" class="text-end">Valor Actual</th>
                                    <th width="120" class="text-end">Saldo por Comprometer</th>
                                    <th width="120" class="text-end">Valor Comprometido</th>
                                    <th width="100" class="text-center">% Compromiso</th>
                                    <th width="120" class="text-end">Valor Pagado</th>
                                    <th width="100" class="text-center">% Pagado</th>

                                    <?php if (isset($_SESSION['user_rol_id']) && $_SESSION['user_rol_id'] == 4): ?>
                                        <th width="120" class="text-center">Obs. <?= $mes_actual . ' 1-7' ?></th>
                                        <th width="120" class="text-center">Obs. <?= $mes_actual . ' 8-15' ?></th>
                                        <th width="120" class="text-center">Obs. <?= $mes_actual . ' 16-23' ?></th>
                                        <th width="120" class="text-center">Obs. <?= $mes_actual . ' 24-' . $ultimo_dia ?></th>
                                        <th width="80" class="text-center">Guardar</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody class="font-monospace">
                                <?php
                                $totalPresupuesto = 0;
                                $totalComprometido = 0;
                                $totalSaldo = 0;
                                $totalValorInicial = 0;
                                $totalValorOperaciones = 0;
                                $totalValorPagado = 0;

                                // Función para extraer solo el texto antes de los dos puntos (Concepto)
                                function extraerConcepto($texto)
                                {
                                    if (empty($texto)) return '';
                                    $partes = explode(':', $texto, 2);
                                    return trim($partes[0]);
                                }

                                // Función para extraer solo el texto hasta el SEGUNDO guión (Servicio)
                                function extraerServicio($texto)
                                {
                                    if (empty($texto)) return '';
                                    $partes = explode('-', $texto);
                                    // Si hay al menos 3 partes (2 guiones), unimos las primeras dos
                                    if (count($partes) >= 3) {
                                        return trim($partes[0]) . ' - ' . trim($partes[1]);
                                    } elseif (count($partes) == 2) {
                                        // Si solo hay un guión, devolvemos la primera parte
                                        return trim($partes[0]);
                                    } else {
                                        // Si no hay guiones, devolvemos el texto completo
                                        return trim($texto);
                                    }
                                }

                                if (empty($informe)): ?>
                                    <tr>
                                        <?php
                                        $colspan = 16;
                                        if (isset($_SESSION['user_rol_id']) && $_SESSION['user_rol_id'] == 4) {
                                            $colspan = 21;
                                        }
                                        ?>
                                        <td colspan="<?= $colspan ?>" class="text-center text-muted py-5">
                                            <i class="fas fa-search fa-2x mb-3 d-block"></i>
                                            No se encontraron registros para esta semana
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($informe as $item):
                                        // Usando todos los campos de tu tabla excepto el ID
                                        $valorInicial = floatval($item['valorInicial'] ?? 0);
                                        $valorOperaciones = floatval($item['valorOperaciones'] ?? 0);
                                        $valorActual = floatval($item['valorActual'] ?? 0);
                                        $saldoComprometer = floatval($item['saldoPorComprometer'] ?? 0);
                                        $valorComprometido = floatval($item['valorComprometido'] ?? 0);
                                        $porcentajeCompromiso = floatval($item['porcentajeCompromiso'] ?? 0);
                                        $valorPagado = floatval($item['valorPagado'] ?? 0);
                                        $porcentajePagado = floatval($item['porcentajePagado'] ?? 0);

                                        // Extraer solo el concepto (texto antes de los dos puntos)
                                        $concepto = extraerConcepto($item['descripcionCompleta'] ?? '');

                                        // Extraer solo el servicio (texto hasta el SEGUNDO guión) para Rubro Descripción
                                        $servicio = extraerServicio($item['descripcion'] ?? '');

                                        // Totales para el footer
                                        $totalValorInicial += $valorInicial;
                                        $totalValorOperaciones += $valorOperaciones;
                                        $totalPresupuesto += $valorActual;
                                        $totalSaldo += $saldoComprometer;
                                        $totalComprometido += $valorComprometido;
                                        $totalValorPagado += $valorPagado;

                                        $claseFila = '';
                                        $claseBadge = 'bg-secondary';

                                        if ($porcentajeCompromiso == 0) {
                                            $claseFila = 'table-danger'; // ROJO para 0%
                                            $claseBadge = 'bg-danger';
                                        } elseif ($porcentajeCompromiso == 100) {
                                            $claseFila = 'table-success'; // VERDE para 100%
                                            $claseBadge = 'bg-success';
                                        } elseif ($porcentajeCompromiso > 80) {
                                            $claseFila = 'table-warning';
                                            $claseBadge = 'bg-warning text-dark';
                                        } elseif ($porcentajeCompromiso > 50) {
                                            $claseFila = 'table-info';
                                            $claseBadge = 'bg-info text-dark';
                                        } elseif ($porcentajeCompromiso > 0) {
                                            $claseBadge = 'bg-primary';
                                        }
                                    ?>
                                        <tr class="<?= $claseFila ?>">
                                            <!-- Columna 1: Número CDP -->
                                            <td class="text-center fw-bold"><?= htmlspecialchars($item['cdp'] ?? '') ?></td>

                                            <!-- Columna 2: Fecha de Registro -->
                                            <td class="text-center"><?= !empty($item['fechaRegistro']) ? date('d/m/Y', strtotime($item['fechaRegistro'])) : '' ?></td>

                                            <!-- Columna 3: Dependencia (ID) -->
                                            <td class="text-center"><?= htmlspecialchars($item['idDependenciaFk'] ?? '') ?></td>

                                            <!-- Columna 4: Dependencia Descripción-->
                                            <td class="text-center"><?= htmlspecialchars($item['dependenciaDescripcion'] ?? '') ?></td>

                                            <!-- Columna 5: Concepto Interno (extraído del objeto) -->
                                            <td class="small" title="<?= htmlspecialchars($item['descripcionCompleta'] ?? '') ?>">
                                                <?= htmlspecialchars($concepto) ?>
                                            </td>

                                            <!-- Columna 6: Rubro -->
                                            <td><?= htmlspecialchars($item['rubro'] ?? '') ?></td>

                                            <!-- Columna 7: Rubro Descripción (ahora con el servicio extraído) -->
                                            <td class="small" title="<?= htmlspecialchars($item['descripcion'] ?? '') ?>">
                                                <?= htmlspecialchars($servicio) ?>
                                            </td>

                                            <!-- Columna 8: Fuente -->
                                            <td class="text-center"><?= htmlspecialchars($item['fuente'] ?? '') ?></td>

                                            <!-- Columna 9: Valor Inicial -->
                                            <td class="text-end">$<?= number_format($valorInicial, 0, ',', '.') ?></td>

                                            <!-- Columna 10: Valor Operaciones -->
                                            <td class="text-end">$<?= number_format($valorOperaciones, 0, ',', '.') ?></td>

                                            <!-- Columna 11: Valor Actual -->
                                            <td class="text-end fw-bold">$<?= number_format($valorActual, 0, ',', '.') ?></td>

                                            <!-- Columna 12: Saldo por Comprometer -->
                                            <td class="text-end">$<?= number_format($saldoComprometer, 0, ',', '.') ?></td>

                                            <!-- Columna 13: Valor Comprometido -->
                                            <td class="text-end">$<?= number_format($valorComprometido, 0, ',', '.') ?></td>

                                            <!-- Columna 14: % Compromiso -->
                                            <td class="text-center">
                                                <span class="badge <?= $claseBadge ?>">
                                                    <?= number_format($porcentajeCompromiso, 1) ?>%
                                                </span>
                                            </td>

                                            <!-- Columna 15: Valor Pagado -->
                                            <td class="text-end">$<?= number_format($valorPagado, 0, ',', '.') ?></td>

                                            <!-- Columna 16: % Pagado -->
                                            <td class="text-center">
                                                <span class="badge bg-secondary">
                                                    <?= number_format($porcentajePagado, 1) ?>%
                                                </span>
                                            </td>

                                            <?php if (isset($_SESSION['user_rol_id']) && $_SESSION['user_rol_id'] == 4): ?>
                                                <td class="text-center">
                                                    <input type="text" class="form-control form-control-sm observacion"
                                                        data-cdp="<?= htmlspecialchars($item['cdp'] ?? '') ?>"
                                                        data-periodo="1"
                                                        value="<?= htmlspecialchars($item['observacion1'] ?? '') ?>">
                                                </td>
                                                <td class="text-center">
                                                    <input type="text" class="form-control form-control-sm observacion"
                                                        data-cdp="<?= htmlspecialchars($item['cdp'] ?? '') ?>"
                                                        data-periodo="2"
                                                        value="<?= htmlspecialchars($item['observacion2'] ?? '') ?>">
                                                </td>
                                                <td class="text-center">
                                                    <input type="text" class="form-control form-control-sm observacion"
                                                        data-cdp="<?= htmlspecialchars($item['cdp'] ?? '') ?>"
                                                        data-periodo="3"
                                                        value="<?= htmlspecialchars($item['observacion3'] ?? '') ?>">
                                                </td>
                                                <td class="text-center">
                                                    <input type="text" class="form-control form-control-sm observacion"
                                                        data-cdp="<?= htmlspecialchars($item['cdp'] ?? '') ?>"
                                                        data-periodo="4"
                                                        value="<?= htmlspecialchars($item['observacion4'] ?? '') ?>">
                                                </td>
                                                <td class="text-center">
                                                    <button class="btn btn-sm btn-success btn-guardar-observaciones"
                                                        data-cdp="<?= htmlspecialchars($item['cdp'] ?? '') ?>">
                                                        <i class="fas fa-save"></i>
                                                    </button>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="card-footer bg-light py-3">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Mostrando <span class="fw-bold"><?= count($informe) ?></span> registros
                                </small>
                            </div>
                            <div class="col-md-8 text-end">
                                <div class="d-flex justify-content-end gap-3 flex-wrap">
                                    <small class="text-muted">
                                        Valor Inicial: <span class="fw-bold text-info">$<?= number_format($totalValorInicial, 0, ',', '.') ?></span>
                                    </small>
                                    <small class="text-muted">
                                        Valor Actual: <span class="fw-bold text-primary">$<?= number_format($totalPresupuesto, 0, ',', '.') ?></span>
                                    </small>
                                    <small class="text-muted">
                                        Comprometido: <span class="fw-bold text-warning">$<?= number_format($totalComprometido, 0, ',', '.') ?></span>
                                    </small>
                                    <small class="text-muted">
                                        Saldo: <span class="fw-bold text-success">$<?= number_format($totalSaldo, 0, ',', '.') ?></span>
                                    </small>
                                    <small class="text-muted">
                                        Pagado: <span class="fw-bold text-secondary">$<?= number_format($totalValorPagado, 0, ',', '.') ?></span>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- GRÁFICAS -->
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-white border-bottom py-3">
                                <h6 class="mb-0 fw-semibold text-primary">
                                    <i class="fas fa-chart-pie me-2"></i>Resumen General
                                </h6>
                            </div>
                            <div class="card-body">
                                <canvas id="graficaGeneral" height="250"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-white border-bottom py-3">
                                <h6 class="mb-0 fw-semibold text-primary">
                                    <i class="fas fa-chart-bar me-2"></i>Distribución por Estado
                                </h6>
                            </div>
                            <div class="card-body">
                                <canvas id="graficaEstado" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="modal-footer bg-light">
                <div class="me-auto">
                    <small class="text-muted">
                        <i class="fas fa-calendar-check me-1 text-success"></i>
                        <?php if ($semanaActiva && $semanaActiva['semanaActiva']): ?>
                            Semana activa automática - Actualizado: <?= date('d/m/Y H:i') ?>
                        <?php else: ?>
                            Semana histórica - Sistema de activación automática activo
                        <?php endif; ?>
                    </small>
                </div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cerrar
                </button>
                <button type="button" class="btn btn-primary" id="btn-imprimir-informe">
                    <i class="fas fa-print me-1"></i>Imprimir
                </button>
            </div>
        </div>
    </div>
</div>


<!-- DATALISTS FUERA DEL MODAL -->
<datalist id="dependencias-list"></datalist>
<datalist id="rubros-list"></datalist>

<!-- Script para pasar el rol del usuario a JavaScript -->
<script>
    window.userRolId = <?php echo $_SESSION['user_rol_id'] ?? 0; ?>;
    window.userId = <?php echo $_SESSION[APP_SESSION_NAME]['idUsuarioSession'] ?? 0; ?>;
</script>


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

    /* Tabla con scroll vertical - MOSTRAR TODAS LAS FILAS */
    .table-responsive {
        max-height: 60vh;
        overflow-y: auto;
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

    /* Contenedor de gráfica centrada */
    .chart-center-container {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-top: 1.5rem;
        min-height: 300px;
    }

    /* Estilo para indicador de gráfica oculta */
    .chart-hidden-indicator {
        text-align: center;
        padding: 2rem;
        color: #6c757d;
        font-style: italic;
        display: block;
    }

    @media (max-width: 991.98px) {

        #mini-presupuesto-container,
        #cdp-individual-container {
            margin-top: 1rem;
        }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>
<script src="<?= APP_URL ?>js/reports/reports.js"></script>