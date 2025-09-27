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
      <?php foreach ($users as $user): ?>
        <tr>
          <td><?= $user['id'] ?></td>
          <td><?= htmlspecialchars($user['email']) ?></td>
          <td>
            <?php if (!empty($user['es_verificado'])): ?>
              <span class="badge badge-success">✔ Sí</span>
            <?php else: ?>
              <span class="badge badge-danger">✘ No</span>
            <?php endif; ?>
          </td>
          <td><?= htmlspecialchars($user['nombre_rol'] ?? '') ?></td>
          <td>
            <form method="post" action="usuarios/update">
              <input type="hidden" name="id" value="<?= $user['id'] ?>">
              <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>">
              <label>
                <input type="checkbox" name="verificado" value="1" <?= !empty($user['es_verificado']) ? 'checked' : '' ?>>
                Verificado
              </label>

              <select name="role_id" id="role_id" required>
                <option value="">Seleccione el rol</option>
                <?php foreach ($roles as $role): ?>
                  <option value="<?= $role['id'] ?>" <?= ($role['id'] == ($user['rol_id'] ?? 0)) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($role['nombre']) ?>
                  </option>
                <?php endforeach; ?>
              </select>

              <button type="submit">💾 Guardar</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>

    </tbody>
  </table>
</div>