<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <meta name="description" content="Sistema de gestión de presupuestos">

    <!-- Favicon -->
    <link rel="icon" href="<?= APP_URL ?>assets/img/sena.png" type="image/png">

    <!-- Fuentes e iconos -->
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    </noscript>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Estilos propios -->
    <link rel="stylesheet" type="text/css" href="<?= APP_URL ?>css/menustyles.css">

    <!-- Estilos específicos para modales -->
    <link rel="stylesheet" type="text/css" href="<?= APP_URL ?>css/roles/roles.css">
    <link rel="stylesheet" type="text/css" href="<?= APP_URL ?>css/permissions/permissions.css">

    <!-- Estilos adicionales dinámicos -->
    <?php foreach ($styles as $css): ?>
        <link rel="stylesheet" href="<?= APP_URL . '/' . $css ?>">
    <?php endforeach; ?>

    <!-- Solo estos pequeños ajustes para consistencia -->
    <style>
        .page-wrapper {
            background-color: #f8f9fa;
            min-height: calc(100vh - 200px);
        }

        .card {
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .btn {
            border-radius: 6px;
        }
    </style>
</head>

<body>
    <!-- Cabecera principal - SOLO ELIMINÉ LA REDUNDANCIA Y ORGANICÉ VERTICALMENTE -->
    <header class="header d-flex align-items-center justify-content-between px-4 py-3" role="banner">
        <div class="user-section d-flex align-items-center">
            <button class="btn btn-primary btn-menu-toggle me-3" id="toggleMenu" aria-label="Abrir menú" aria-expanded="false" aria-controls="sidebarMenu">
                <i class="fas fa-bars" aria-hidden="true"></i>
            </button>

            <!-- SOLUCIÓN: Eliminé el icono duplicado y organicé verticalmente -->
            <div class="user-info d-flex align-items-center">
                <div class="user-photo me-3">
                    <img src="<?= APP_URL ?>assets/img/default.png" alt="Foto de usuario" class="rounded-circle" width="45" height="45">
                </div>
                <div class="d-flex flex-column">
                    <span class="fw-semibold">
                        <?=
                        $_SESSION[APP_SESSION_NAME]['name'] . " " .
                            $_SESSION[APP_SESSION_NAME]['lastName']
                        ?>
                    </span>
                    <span class="badge bg-secondary mt-1"><?= $userRol ?></span>
                </div>
            </div>
        </div>

        <!-- Sección derecha: logo SENA y título -->
        <div class="sena-section d-flex align-items-center">
            <div class="logo-sena me-3">
                <img src="<?= APP_URL ?>assets/img/logoSena.png" alt="Logo SENA" height="45">
            </div>
            <div class="titulo">
                <h1 class="h4 mb-0">Presupuestos</h1>
            </div>
        </div>
    </header>

    <!-- TODO LO DEMÁS EXACTAMENTE IGUAL -->
    <!-- Subheader informativo -->
    <section class="subheader-container bg-light p-3 border-bottom" aria-labelledby="subheader-title">
        <h2 id="subheader-title" class="visually-hidden">Información del año fiscal</h2>
        <div class="row g-3">
            <div class="col-md">
                <label class="form-label fw-bold">Subdirector:</label>
                <p class="form-control-plaintext mb-0">
                    <?= $subdirector ? htmlspecialchars($subdirector['nombres'] . ' ' . $subdirector['apellidos']) : 'No hay subdirector asignado' ?>
                </p>
            </div>
            <div class="col-md">
                <label class="form-label fw-bold">Año Fiscal:</label>
                <input type="number" class="form-control form-control-sm"
                    value="<?= $anioFiscalActivo['anio_fiscal'] ?? '' ?>"
                    readonly aria-label="Año fiscal actual">
            </div>
            <div class="col-md">
                <label class="form-label fw-bold">Valor:</label>
                <input type="text" class="form-control form-control-sm"
                    value="<?= isset($anioFiscalActivo['valor_anio_fiscal']) ? '$' . number_format($anioFiscalActivo['valor_anio_fiscal'], 0, ',', '.') : '' ?>"
                    readonly aria-label="Valor del año fiscal">
            </div>
            <div class="col-md">
                <label class="form-label fw-bold">Fecha inicio</label>
                <input type="date" class="form-control form-control-sm"
                    value="<?= $anioFiscalActivo['fecha_inicio'] ?? '' ?>"
                    readonly aria-label="Fecha de inicio del año fiscal">
            </div>
            <div class="col-md">
                <label class="form-label fw-bold">Fecha Fin</label>
                <input type="date" class="form-control form-control-sm"
                    value="<?= $anioFiscalActivo['fecha_cierre'] ?? '' ?>"
                    readonly aria-label="Fecha de cierre del año fiscal">
            </div>
            <div class="col-md">
                <label class="form-label fw-bold">Estado:</label>
                <input type="text" class="form-control form-control-sm"
                    value="<?= (!empty($anioFiscalActivo) && $anioFiscalActivo['estado'] == 1) ? 'Activo' : 'Inactivo' ?>"
                    readonly aria-label="Estado del año fiscal">
            </div>
        </div>
    </section>

    <!-- Menú lateral - EXACTAMENTE IGUAL -->
    <nav class="sidebar" id="sidebarMenu" role="navigation" aria-label="Menú principal">
        <div class="accordion1 accordion-flush" id="menuAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingInicio">
                    <a class="accordion-button single-link" href="<?= APP_URL ?>dashboard">
                        <i class="fas fa-home me-2" aria-hidden="true"></i>Inicio
                    </a>
                </h2>
            </div>

            <!-- Menú dinámico desde base de datos -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingPresupuesto">
                    <div class='permissions'></div>
                </h2>
            </div>

            <!-- Enlace de salida -->
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingSalir">
                    <form action="<?= APP_URL ?>logout" method="POST" style="display:inline;">
                        <button type="submit" class="accordion-button single-link btn-link w-100 text-start">
                            <i class="fas fa-sign-out-alt me-2" aria-hidden="true"></i>Salir
                        </button>
                    </form>
                </h2>
            </div>
        </div>
    </nav>

    <!-- Fondo para cerrar menú en móviles -->
    <div class="backdrop-sidebar" id="sidebarBackdrop" aria-hidden="true"></div>

    <!-- Contenido principal -->
    <main class="page-wrapper" id="main-content" role="main">
        <?php require $view; ?>
    </main>

    <!-- Modal Crear Año Fiscal -->
    <div class="modal fade modal-yearfiscal" data-keyboard="false" id="modalAnioFiscal"
        data-backdrop="static" tabindex="-1" aria-labelledby="modalAnioFiscalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content yearfiscal-content">
                <div class="modal-header yearfiscal-header">
                    <h2 class="modal-title h5" id="modalAnioFiscalLabel">Crear Año Fiscal</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body yearfiscal-body">
                    <!-- Contenedor de alertas dinámicas -->
                    <div class="yf-alerts" id="yf-alerts" role="alert" aria-live="polite" aria-atomic="true"></div>

                    <form action="<?= APP_URL ?>crear_anio_fiscal" class="form-Fiscal yearfiscal-form FormularioAjax" method="post">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <?php if ($subdirector): ?>
                                        <label for="subdirector" class="form-label">Subdirector</label>
                                        <input type="text"
                                            id="subdirector"
                                            name="subdirector"
                                            class="form-control"
                                            value="<?= htmlspecialchars($subdirector['nombres'] . ' ' . $subdirector['apellidos']) ?>"
                                            readonly>
                                        <input type="hidden"
                                            name="subdirector_id"
                                            value="<?= $subdirector['id'] ?>">
                                    <?php else: ?>
                                        <div class="alert alert-warning" role="alert">
                                            No hay un subdirector asignado actualmente.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="valor_presupuesto" class="form-label">Valor Presupuesto</label>
                                    <input type="text" step="0.01" name="valor_presupuesto"
                                        class="form-control inputFiscal" id="valor_presupuesto"
                                        placeholder="$0" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="year-fiscal" class="form-label">Año Fiscal</label>
                                    <input type="number"
                                        id="year-fiscal"
                                        class="form-control form-control-sm"
                                        value="<?= date('Y') ?>"
                                        name="year_fiscal"
                                        min="2000"
                                        max="2030"
                                        required />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="estado" class="form-label">Estado</label>
                                    <select class="form-select inputFiscal" id="estado" name="estado" required>
                                        <option value="" selected disabled>Seleccione una opción</option>
                                        <option value="activo">Activo</option>
                                        <option value="inactivo">Inactivo</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                                    <input type="date" class="form-control inputFiscal"
                                        name="fecha_inicio" id="fecha_inicio" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fecha_cierre" class="form-label">Fecha Cierre</label>
                                    <input type="date" class="form-control inputFiscal"
                                        name="fecha_cierre" id="fecha_cierre" required>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer yearfiscal-footer">
                            <button type="button" name="cancel" class="btn btn-secondary yf-btn-cancel"
                                id="cancel-button" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" name="create" class="btn btn-primary yf-btn-create"
                                id="create-button">Crear</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para gestión de roles -->
    <div class="modal fade modal-roles" id="rolesModal" tabindex="-1"
        aria-labelledby="rolesModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content roles-content">
                <div class="modal-header roles-header">
                    <h2 class="modal-title h5" id="rolesModalLabel">Roles disponibles</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body roles-body">
                    <ul class="list-group roles-list" role="list"></ul>
                </div>
                <div class="modal-footer roles-footer">
                    <button type="button" class="btn btn-secondary roles-btn-cancel"
                        data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary roles-btn-save">Guardar cambios</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para gestión de permisos -->
    <div class="modal fade modal-permissions" id="modalManageRoles" tabindex="-1"
        aria-labelledby="modalManageRolesLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content permissions-content shadow-lg border-0 rounded-4">
                <div class="modal-header permissions-header bg-primary text-white rounded-top-4">
                    <h2 class="modal-title fw-bold h5" id="modalManageRolesLabel">
                        <i class="fas fa-user-shield me-2" aria-hidden="true"></i> Gestionar Roles
                    </h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body permissions-body bg-light">
                    <div class="accordion permissions-accordion" id="accordionRoles"></div>
                    <div class="modal-footer permissions-footer">
                        <button type="button" class="btn btn-outline-secondary permissions-btn-cancel"
                            data-bs-dismiss="modal">
                            <i class="fas fa-times me-1" aria-hidden="true"></i> Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenedor global para toasts -->
    <div id="toast-container" class="toast-container position-fixed bottom-0 end-0 p-3"
        style="z-index: 1050;" aria-live="polite" aria-atomic="true"></div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>

    <!-- Scripts propios -->
    <script src="<?= APP_URL ?>js/menuctr.js"></script>

    <!-- Scripts dinámicos -->
    <?php foreach ($scripts as $script): ?>
        <script src="<?= APP_URL . '/' . $script ?>"></script>
    <?php endforeach; ?>

    <?php if (!empty($pageScripts)): ?>
        <?= $pageScripts ?>
    <?php endif; ?>

    <!-- Scripts específicos del dashboard -->
    <script>
        const BASE_URL = "<?= APP_URL ?>";
        const hayAnioFiscal = <?= $hayAnioFiscal ? 'true' : 'false' ?>;
    </script>

    <script src="<?= rtrim(APP_URL, '/') ?>/js/dashboard/dashboard.js"></script>
    <script src="<?= rtrim(APP_URL, '/') ?>/js/sweetalert2.all.min.js"></script>
    <script src="<?= rtrim(APP_URL, '/') ?>/js/alerts.js"></script>
</body>

</html>