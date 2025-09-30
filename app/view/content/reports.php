<tbody>
    <?php
    // $contador = 1; // Ya no necesitas este contador
    foreach ($semanas as $semana):
    ?>
        <tr>
            <td class="fw-semibold">Semana <?= $semana['numero_semana'] ?></td> <!-- Usar numero_semana de la BD -->
            <td><?= date("d/m/Y", strtotime($semana['fecha_inicio'])) ?></td> <!-- Cambiar 'inicio' por 'fecha_inicio' -->
            <td><?= date("d/m/Y", strtotime($semana['fecha_fin'])) ?></td> <!-- Cambiar 'fin' por 'fecha_fin' -->
            <td>
                <div class="d-flex gap-1 flex-wrap">
                    <button class="btn btn-outline-secondary btn-sm btn-ver-detalles"
                        data-week="Semana <?= $semana['numero_semana'] ?>" <!-- Usar numero_semana -->
                        data-semana-id="<?= $semana['id'] ?>" <!-- Agregar data-semana-id para identificar en BD -->
                        data-bs-toggle="modal"
                        data-bs-target="#modalDetalles">
                        <i class="fas fa-eye me-1"></i>Ver Detalles
                    </button>

                    <button class="btn btn-primary btn-sm btn-open-modal"
                        data-week="Semana <?= $semana['numero_semana'] ?>" <!-- Usar numero_semana -->
                        data-semana-id="<?= $semana['id'] ?>" <!-- Agregar data-semana-id -->
                        data-bs-toggle="modal"
                        data-bs-target="#modalReporte">
                        <i class="fas fa-upload me-1"></i>Subir Reporte
                    </button>

                    <button class="btn btn-danger btn-sm btn-delete-week"
                        data-week="Semana <?= $semana['numero_semana'] ?>" <!-- Usar numero_semana -->
                        data-semana-id="<?= $semana['id'] ?>"> <!-- Agregar data-semana-id -->
                        <i class="fas fa-trash-alt me-1"></i>Eliminar
                    </button>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
</tbody>