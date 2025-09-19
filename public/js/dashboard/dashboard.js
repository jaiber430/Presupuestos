// Escapar texto para evitar XSS
function escapeHtml(text) {
  return $('<div>').text(text).html();
}

$(function() {	
  $.post(BASE_URL + 'dashboard/listar', function(answer) {
    if (answer.state == 1) {
      let userRolePermissions = answer.data.userRolePermissions;
      let allRolePermissions  = answer.data.allRolePermissions;

      // Mejor fuera del loop
      let userRolePermissionsNames = userRolePermissions.map(p => p.nombre_permiso);

      userRolePermissions.forEach((permiso) => {
        let clase = "";
        if(permiso.nombre_permiso === "Gestionar roles"){
            clase= "manage_role";
        } else if (permiso.nombre_permiso === "Gestionar usuarios") {
            clase= "manage_user";
        }            
        console.log(userRolePermissions);
        $('.permissions').append(
          `<a class="${clase} accordion-button single-link" href="${BASE_URL}${permiso.url}">${escapeHtml(permiso.nombre_permiso)}</a>`
        );
      });

      if (allRolePermissions.length > 0) {
        let rolesMap = {};
        allRolePermissions.forEach(rol => {
          if(!rolesMap[rol.rol_id]){
            rolesMap[rol.rol_id] = {
              rol_id: rol.rol_id,
              nombre_rol: rol.nombre_rol,
              icon: rol.icon,
              permisosArray: rol.permiso_nombre.split(',').map(p => p.trim()),
              permisosEstado: rol.permiso_activo ? rol.permiso_activo.split(',').map(s => parseInt(s)) : [] 
            };
          } else {
            rol.permiso_nombre.split(',').map(p => p.trim()).forEach(p=>{
              if(!rolesMap[rol.rol_id].permisosArray.includes(p)){
                rolesMap[rol.rol_id].permisosArray.push(p);
                rolesMap[rol.rol_id].permisosEstado.push(rol.estado ? parseInt(rol.estado) : 0);
              }
            });
          }
        });

        let roles = Object.values(rolesMap);

        roles.forEach((rol) => {
          let permisosHtml = '<ul class="list-unstyled mb-0">';
          rol.permisosArray.forEach((permiso, index) => {
            let permisoId = rol.nombre_rol.toLowerCase().replace(/\s+/g, '-') + '-perm-' + index;
            let checked = userRolePermissionsNames.includes(permiso) ? 'checked' : '';

            permisosHtml += `
              <li class="d-flex align-items-center justify-content-between mb-2">
                <span>${escapeHtml(permiso)}</span>
                <div class="form-check form-switch mb-0">
                  <input class="form-check-input" type="checkbox" id="${permisoId}" ${checked}>
                </div>
              </li>`;
          });
          permisosHtml += '</ul>';

          let target = rol.nombre_rol.toLowerCase().replace(/\s+/g, '-');

          $('.accordion').append(`
            <div class="accordion-item">
              <h2 class="accordion-header" id="heading-${target}">
                <button class="accordion-button collapsed" type="button"
                        data-bs-toggle="collapse" data-bs-target="#collapse-${target}"
                        aria-expanded="false" aria-controls="collapse-${target}"
                        data-rol="${escapeHtml(rol.nombre_rol)}">
                  <i class="${rol.icon} me-2"></i>${escapeHtml(rol.nombre_rol)}
                </button>
              </h2>
              <div id="collapse-${target}" class="accordion-collapse collapse"
                   aria-labelledby="heading-${target}" data-bs-parent="#accordionRoles">
                <div class="accordion-body">
                  ${permisosHtml}
                </div>
              </div>
            </div>`);
        });

      } else {
        console.warn('No hay roles disponibles');
      }

    } else {
      console.error(answer.message);
    }
  }, 'json');

  $(document).on("click", ".manage_role", function(e){
    e.preventDefault();
    $("#modalManageRoles").modal("show");
  });


  $(document).on("change", ".form-check-input", function() {
    let permisoNombre = $(this).closest('li').find('span').text();
    let rolNombre = $(this).closest('.accordion-item').find('.accordion-button').data('rol');
    let estado = $(this).is(':checked') ? 1 : 0;

    $.post('/actualizar-permiso', {
      rol: rolNombre,
      permiso: permisoNombre,
      estado: estado
    }, function(response) {
      if(response.success){
        console.log('Permiso actualizado correctamente');
      } else {
        console.error('Error al actualizar permiso');
      }
    }, 'json');
    
  });

});
