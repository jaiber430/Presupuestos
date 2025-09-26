<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión usuarios</title>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    table {
      border-collapse: collapse;
      margin-top: 15px;
    }
    th, td {
      border: 1px solid #ccc;
      padding: 6px 10px;
    }
    th {
      background: #f0f0f0;
    }
    button {
      padding: 4px 8px;
      cursor: pointer;
    }

    #modal {
      display: none;
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.6);
      justify-content: center;
      align-items: center;
    }
    #modalContent {
      background: #fff;
      padding: 20px;
      border-radius: 8px;
      width: 300px;
    }
    #modalContent h3 {
      margin-top: 0;
    }
    #modalContent input, #modalContent select {
      width: 100%;
      padding: 6px;
      margin-bottom: 10px;
    }

    /* Estilo checkbox azulito */
    .chk-container {
      display: flex;
      align-items: center;
      gap: 6px;
    }
    input[type="checkbox"] {
      accent-color: blue;
      width: 18px;
      height: 18px;
    }
  </style>
</head>
<body>
  <h1>Gestión usuarios</h1>
  <button id="cargar">Mostrar usuarios</button>
  <div id="lista"></div>

  <!-- Modal -->
  <div id="modal">
    <div id="modalContent">
      <h3>Editar usuario</h3>
      <form id="formEditar">
        <input type="hidden" id="m_id" name="id">

        <label>Email:</label>
        <input type="email" id="m_email" name="email">

        <label>¿Verificado?</label>
        <div class="chk-container">
          <input type="checkbox" id="m_verificado">
          <span>Verificado</span>
        </div>
        <!-- Este hidden se manda realmente al servidor -->
        <input type="hidden" id="m_verificado_val" name="es_verificado">

        <label>Rol:</label>
        <select id="m_rol" name="rol_id">
          <!-- Opciones cargadas con AJAX -->
        </select>

        <button type="submit">Guardar</button>
        <button type="button" id="cerrarModal">Cerrar</button>
      </form>
    </div>
  </div>

  <script>
    $(document).ready(function(){
      // Cargar usuarios
      $("#cargar").click(function(){
        $.ajax({
          url: "ctrladmin.php",
          type: "GET",
          success: function(data){
            $("#lista").html(data);
          }
        });
      });

      // Abrir modal
      $(document).on("click", ".verUsuario", function(){
        let id     = $(this).data("id");
        let email  = $(this).data("email");
        let verif  = $(this).data("verificado");
        let rol    = $(this).data("rol");

        $("#m_id").val(id);
        $("#m_email").val(email);

        // Checkbox verificado
        if(verif == 1){
          $("#m_verificado").prop("checked", true);
        } else {
          $("#m_verificado").prop("checked", false);
        }

        // Cargar roles desde BD y marcar el actual
        $.ajax({
          url: "cargarroles.php",
          type: "GET",
          success: function(options){
            $("#m_rol").html(options);
            $("#m_rol").val(rol);
          }
        });

        $("#modal").css("display","flex");
      });

      // Guardar cambios
      $("#formEditar").submit(function(e){
        e.preventDefault();

        // Pasar valor del checkbox al hidden
        $("#m_verificado_val").val($("#m_verificado").is(":checked") ? 1 : 0);

        $.ajax({
          url: "actualizar_usuarios.php",
          type: "POST",
          data: $(this).serialize(),
          success: function(res){
            alert(res);
            $("#modal").hide();
            $("#cargar").click();
          },
          error: function(){
            alert("Error al actualizar usuario");
          }
        });
      });

      // Cerrar modal
      $("#cerrarModal").click(function(){
        $("#modal").hide();
      });
    });
  </script>
</body>
</html>
