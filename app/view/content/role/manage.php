<?php
echo "<pre>";
print_r($users)
?>
<div class="table-container">
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Email</th>
        <th>Verificado</th>
        <th>Rol</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><?= $user['id'] ?></td>
        <td><?= htmlspecialchars($user['email']) ?></td>
        <td>
          <?php if ($user['es_verificado']): ?>
            <span class="badge badge-success">✔ Sí</span>
          <?php else: ?>
            <span class="badge badge-danger">✘ No</span>
          <?php endif; ?>
        </td>
        <td><?= htmlspecialchars($user['nombre_rol']) ?></td>
        <td>
          <form method="post" action="usuarios/update.php">
            <input type="hidden" name="id" value="<?= $user['id'] ?>">

            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>">

            <label>
              <input type="checkbox" name="verificado" value="1" <?= $user['verificado'] ? 'checked' : '' ?>>
              Verificado
            </label>

            <select name="role_id" id="role_id" required>
              <option value="">Seleccione el rol</option>
              <?php foreach ($roles as $role): ?>
                <option value="<?= $role['id'] ?>"
                  <?= $role['id'] == $user['rol_id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($role['nombre']) ?>
                </option>
              <?php endforeach; ?>
            </select>

            <button type="submit">💾 Guardar</button>
          </form>
        </td>
      </tr>
    </tbody>
  </table>
</div>