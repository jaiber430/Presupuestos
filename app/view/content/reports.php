<!-- PRIMER SUBHEADER ocupa toda la pantalla de izquierda a derecha DE EXTREMO A EXTREMO -->
<div class="container-fluid px-0">
    <div class="row">
        <div class="col-12">
            <div class="reports-header p-2">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 w-100">
                    <h3 class="reports-title m-0 ms-3">Reportes</h3>
                    <div class="reports-controls d-flex align-items-center gap-2">
                        <label for="year-fiscal" class="form-label m-0">Subdirector:</label>
                            <input type="text" id="subdirector" class="form-control form-control-sm" readonly/>
                        <label for="year-fiscal" class="form-label m-0">Año Fiscal:</label>
                            <input type="number" id="year-fiscal" class="form-control form-control-sm" readonly/>
                        <label for="date-start" class="form-label m-0">Inicio:</label>
                            <input type="date" id="date-start" class="form-control form-control-sm" readonly/>
                        <label for="date-end" class="form-label m-0">Fin:</label>
                            <input type="date" id="date-end" class="form-control form-control-sm" readonly/>
                        <label for="status-filter" class="form-label m-0">Estado:</label>
                            <input type="text" id="status-filter" class="form-control form-control-sm" readonly/>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- SEGUNDO subheader de dependencias tambien ocupa toda la pantalla de izquierda a derecha DE EXTREMO A EXTREMO -->
    <div class="row">
        <div class="col-12">
            <div class="reports-header p-2">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 w-100">
                    <div class="reports-controls d-flex align-items-center gap-2">
                        <label for="dependency-select" class="form-label m-0 ms-3">Dependencia:</label>
                        <select id="dependency-select" class="form-select form-select-sm w-auto">
                            <option value="all" selected>Todas</option>
                            <option value="dep1">Dependencia 1</option>
                            <option value="dep2">Dependencia 2</option>
                        </select>
                        <button class="btn btn-success btn-sm">
                            <i class="fas fa-search me-1"></i>Buscar
                        </button>
                    </div>
                    <div></div>
                </div>
            </div>
        </div>
    </div>
</div>

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
                            <button class="btn btn-outline-secondary btn-sm">Ver Detalles</button>
                            <button class="btn btn-primary btn-sm btn-open-modal" data-week="Semana 1" data-bs-toggle="modal" data-bs-target="#modalReporte">Subir Reporte</button>
                        </td>
                    </tr>
                    <tr>
                        <td>Semana 2</td>
                        <td>08/01/2025</td>
                        <td>14/01/2025</td>
                        <td>
                            <button class="btn btn-outline-secondary btn-sm">Ver Detalles</button>
                            <button class="btn btn-primary btn-sm btn-open-modal" data-week="Semana 2" data-bs-toggle="modal" data-bs-target="#modalReporte">Subir Reporte</button>
                        </td>
                    </tr>
                </tbody>
                </table>
            </div>
        </div>
        <div class="col-lg-5 mt-4 mt-lg-0">
            <div class="rp-card p-3 h-100">
                <!-- Primera sección: Distribución de Gastos -->
                <div class="card-section">
                    <h3 class="subheader">Distribución de Gastos</h3>
                    <div class="pie-chart-placeholder mt-3">Gráfico de pastel</div>
                    <div class="legend mt-3">
                        <div><span class="legend-color" style="background-color: #4e79a7;"></span> Categoría 1</div>
                        <div><span class="legend-color" style="background-color: #f28e2b;"></span> Categoría 2</div>
                        <div><span class="legend-color" style="background-color: #e15759;"></span> Categoría 3</div>
                        <div><span class="legend-color" style="background-color: #76b7b2;"></span> Categoría 4</div>
                        <div><span class="legend-color" style="background-color: #59a14f;"></span> Categoría 5</div>
                    </div>
                </div>
                
                <!-- Línea separadora punteada -->
                <div class="dotted-separator"></div>
                
                <!-- Segunda sección: Presupuesto General -->
                <div class="card-section">
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
                                <label for="file-cdp" class="form-label">CDP (Excel)</label>
                                <input type="file" class="form-control" id="file-cdp" name="cdp" accept=".xlsx">
                            </div>
                            <div class="col-md-4">
                                <label for="file-rp" class="form-label">R.P (Excel)</label>
                                <input type="file" class="form-control" id="file-rp" name="rp" accept=".xlsx">
                            </div>
                            <div class="col-md-4">
                                <label for="file-pagos" class="form-label">Pagos (Excel)</label>
                                <input type="file" class="form-control" id="file-pagos" name="pagos" accept=".xlsx">
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
</div>
<script>
    // Script para manejar la apertura del modal y actualizar el título dinámicamente
document.addEventListener('DOMContentLoaded', function(){
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
});
</script>
