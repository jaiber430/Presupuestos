
<body>


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
        <td>1</td>
        <td>juan@example.com</td>
        <td><span class="badge badge-success">✔ Sí</span></td>
        <td>Administrador</td>
        <td>
          <form>
            <input type="email" value="juan@example.com">
            <label><input type="checkbox" checked> Verificado</label>
            <select>
              <option selected>Administrador</option>
              <option>Usuario</option>
              <option>Invitado</option>
            </select>
            <button type="submit">💾 Guardar</button>
          </form>
        </td>
      </tr>

      <tr>
        <td>2</td>
        <td>maria@example.com</td>
        <td><span class="badge badge-error">✘ No</span></td>
        <td>Usuario</td>
        <td>
          <form>
            <input type="email" value="maria@example.com">
            <label><input type="checkbox"> Verificado</label>
            <select>
              <option>Administrador</option>
              <option selected>Usuario</option>
              <option>Invitado</option>
            </select>
            <button type="submit">💾 Guardar</button>
          </form>
        </td>
      </tr>

      <tr>
        <td>3</td>
        <td>carlos@example.com</td>
        <td><span class="badge badge-success">✔ Sí</span></td>
        <td>Invitado</td>
        <td>
          <form>
            <input type="email" value="carlos@example.com">
            <label><input type="checkbox" checked> Verificado</label>
            <select>
              <option>Administrador</option>
              <option>Usuario</option>
              <option selected>Invitado</option>
            </select>
            <button type="submit">💾 Guardar</button>
          </form>
        </td>
      </tr>

    </tbody>
  </table>
</div>

</body>

