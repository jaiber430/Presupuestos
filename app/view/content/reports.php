<div class="container mt-4 reports-page">
	<div class="row">
		<div class="col-12">
            <div class="reports-header p-3">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h2 class="subheader m-0">Reportes</h2>
                    <div class="d-flex align-items-center gap-2">
                        <label for="year-fiscal" class="form-label m-0">Año Fiscal:</label>
                        <select id="year-fiscal" class="form-select form-select-sm w-auto">
                            <option value="2025" selected>2025</option>
                        </select>
                    </div>
                </div>
            </div>
		</div>
	</div>

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
                <h3 class="subheader">Distribución de Gastos</h3>
                <!-- aqui va el grafico de pastel -->
                <div class="pie-chart-placeholder mt-3">Gráfico de pastel</div>
                <!-- aqui termina el grafico de pastel -->
                <div class="legend mt-3">
                    <div><span class="legend-color" style="background-color: #4e79a7;"></span> 1</div>
                    <div><span class="legend-color" style="background-color: #f28e2b;"></span> 2</div>
                    <div><span class="legend-color" style="background-color: #e15759;"></span> 3</div>
                    <div><span class="legend-color" style="background-color: #76b7b2;"></span> 4</div>
                    <div><span class="legend-color" style="background-color: #59a14f;"></span> 5</div>
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
