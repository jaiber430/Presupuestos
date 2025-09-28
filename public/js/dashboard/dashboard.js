// Escapar texto para evitar XSS
function escapeHtml(text) {
  return $('<div>').text(text).html();
}

$(document).ready(function() {	
    console.log("hayAnioFiscal:", hayAnioFiscal);
    // Abrir modal solo si NO hay año fiscal activo
    if(!hayAnioFiscal){
        
        // Usar setTimeout para asegurar que el DOM esté listo
        setTimeout(function() {           
            // Método 1: Usar Bootstrap nativo
            if (typeof bootstrap !== 'undefined') {
                var modalElement = document.getElementById('modalAnioFiscal');
                if (modalElement) {
                    var modal = new bootstrap.Modal(modalElement, {
                        backdrop: 'static',
                        keyboard: false
                    });
                    modal.show();
                }
            } 
            // Método 2: Usar jQuery como fallback
            else if ($.fn.modal) {
                $('#modalAnioFiscal').modal({
                    backdrop: 'static',
                    keyboard: false,
                    show: true
                });

            }
            
            // Verificar si se abrió
            setTimeout(function() {
                if ($('#modalAnioFiscal').is(':visible')) {

                } else {
                    console.log('Modal no visible, intentando forzar...');
                    $('#modalAnioFiscal').addClass('show');
                    $('#modalAnioFiscal').css('display', 'block');
                }
            }, 500);
        }, 100);
        
        // Bloquear cierre
        $(document).on('hide.bs.modal', '#modalAnioFiscal', function (e) {
            e.preventDefault();
            return false;
        });
    }

    $('#valor_presupuesto').on('input', function() {
        let value = $(this).val();
        console.log(value);
        // Quitar todo lo que no sea número
        value = value.replace(/\D/g, '');

        if(value === '') {
            $(this).val('');
            $('#monto_hidden').val('');
            return;
        }

        // Formatear con separador de miles
        let formatted = '$' + parseInt(value, 10).toLocaleString('es-CO');

        // Mostrar en el input
        $(this).val(formatted);

        // Guardar valor limpio en el hidden
        $('#monto_hidden').val(value);
    });


  $.post(BASE_URL + 'dashboard/listar', function(answer) {
    if (answer.state == 1) {
      let userRolePermissions = answer.data.userRolePermissions;
      let allRolePermissions  = answer.data.allRolePermissions;
      $('.permissions').empty(); 
      $('.accordion').empty();

      let userRolePermissionsNames = userRolePermissions.map(p => p.nombre_permiso);

      userRolePermissions.forEach((permiso) => {
        let clase = "";
        if(permiso.nombre_permiso === "Gestionar Permisos"){
            clase= "manage_role";
        } else if (permiso.nombre_permiso === "Gestionar usuarios") {
            clase= "manage_user";
        }            
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

  $(document).off("change", ".form-check-input");
  $(document).on("change", ".form-check-input", function() {
    let permisoNombre = $(this).closest('li').find('span').text();
    let rolNombre = $(this).closest('.accordion-item').find('.accordion-button').data('rol');
    let estado = $(this).is(':checked') ? 1 : 0;

    $.post(BASE_URL + 'dashboard/actualizar-permiso', {
      rol: rolNombre,
      permiso: permisoNombre,
      estado: estado
    }, function(response) {
    if (response.state === 1) {
          showToast(response.message, 'success'); 
      } else {
          showToast(response.message, 'error');
      }
  }, 'json');
      
  });

  
  

  function showToast(message, type = 'info') {
      const bg = {
          success: 'bg-success text-white',
          error: 'bg-danger text-white',
          warning: 'bg-warning text-dark',
          info: 'bg-info text-white'
      }[type] || 'bg-info text-white';

      const container = document.querySelector('#toast-container');

      const toastEl = document.createElement('div');
      toastEl.className = `toast align-items-center ${bg} border-0`;
      toastEl.role = 'alert';
      toastEl.ariaLive = 'assertive';
      toastEl.ariaAtomic = 'true';
      toastEl.innerHTML = `
          <div class="d-flex">
              <div class="toast-body">${message}</div>
              <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
          </div>
      `;

      container.appendChild(toastEl);

      const bsToast = new bootstrap.Toast(toastEl, { delay: 3000 });
      bsToast.show();

      // Quitar del DOM cuando desaparezca
      toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
  }





});




