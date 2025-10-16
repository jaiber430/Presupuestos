$(document).ready(function(){					

	$(document).on("click", "#btnBorrarRol",function () { 
		var idRol = $(this).data('id_rol');
		$("#idRolDell").val(idRol);
		var nombreRol = $(this).data('nombre_rol');
		$("#nombreRolDel").val(nombreRol);
	});
	
	$(document).on("click", "#idCheck",function () { 
		let idSBM = $(this).val();
		if($(this).is(':checked')){ SubM(idSBM); }
		else { SubM(idSBM); }
	});
	
	function SubM(idSBM){
		$.post("../../include/ctrlindex.php", {
			action:'arregloGenerarPermiso',
			idRolPermiso:$("#idRolPermiso").val(),
			idMenuPermiso:$("#idMenuPermiso").val(),
			idSBM:idSBM
		}, function(data){
			//$("#menuControl").html(data.tabs);
		}, 'json');		
	}

		
	$(document).on("click", "#editarRol",function () {
		$("#divRespRegistRol").empty();
		var id_rol = $(this).data('id_rol');
		$("#id_rolUpdate").val(id_rol);	
		var nombre_rol = $(this).data('nombre_rol');
		$("#nombre_rolUpdt").val(nombre_rol);	
	});
	
	function CierraPopup() { $('#editarRolM').trigger('click');	}
	function CierraPopupCerrar() { $('#btnCerrarGuardar').trigger('click');	}
	$('#dato_txt').on('focus', function() { $("#listaEncontrada").empty();});
	$(document).on("click", "#btn_actualizar",function () {
		if($("#nombre_rolUpdt").val()==""){
			alert("DEBE EXISTIR UN NOMBRE.");
			location.reload();
		}else{
			$.post("../controlador/rol_ctrl.php", {
			action:'ActualizarRol',
			id_rol:$("#id_rolUpdate").val(),
			nombre_rolUpdt:$("#nombre_rolUpdt").val()
			}, function(data){
				if(data.rsultd==1){
					$("#id_rolUpdate").empty();
					$("#nombre_rolUpdt").empty();
					$('#divRespRol').empty();
					CierraPopup();
				}else{
					$('#divRespRol').empty();
					alert(data.msjUpdt);
				}				
			}, 'json');
		}
	});
	$(document).on("click", "#btn_Buscar",function () { 
		if($("#dato_txt").val()==""){
			alertify.error("DEBE INGRESAR TEXTO A BUSCAR.");
			$("#dato_txt").focus();
		}else{
			$.post("../controlador/rol_ctrl.php", {
				action:'buscarListaRol',
				dato_txt:$("#dato_txt").val()
			}, function(data){
				if(data.rstd==1){
					$("#example").html(data.datosRol);		
				}else{
					$("#example").html(data.datosRol); 
				} 
			}, 'json');
		}
	});
	$(document).on("focus", "#dato_txt",function () { $("#listaEncontrada").empty(); });			
	$(document).on("click", "#btn_guardar",function () { 
		if($("#nombreRolNew").val()==""){
			$("#divRespRegistRol").html("DEBE INGRESAR UN NOMBRE.");	
		}else{	
			$.post("../controlador/rol_ctrl.php", {
				action:'guardarRol',																		
				nombreRolNew:$("#nombreRolNew").val()				
				}, function(data){	
					if(data.restl==1){
						$("#nombreRolNew").val("");
						$("#divRespRegistRol").empty();
						$("#divRespRegistRol").html(data.msj);
						$('#btnCerrarGuardar').trigger('click');
					}else{
						$("#divRespRegistRol").empty();
						$("#divRespRegistRol").html(data.msj);	
					}						
				}, 'json');
		}		
	});

	//$(document).on("click", "#btnEditRol",function () {$("#divRespRegistRol").empty();});
	$(document).on("click", "#btnEliminar",function () {
		$.post("../controlador/rol_ctrl.php", {
		action:'eliminarRol',																		
		idRolDell:$("#idRolDell").val()				
		}, function(data){
			if(data.rsultd==1){				
				$("#idRolDell").empty();
				$("#nombreDelRol").empty();	
				$("#divRespDel").empty();	
				$("#divRespDel").html(data.msjDell);	
			}else{
				$("#divRespDel").empty();	
				$("#divRespDel").html(data.msjDell);	
			}
		}, 'json');
	});	
	//1°  llamado desde el rol, dentro de la tabla
	$(document).on("click", "#btnPermisos",function () {
		var id_rol = $(this).data('id_rol');
		$("#idRolPermiso").val(id_rol);		
		var nombre_rol = $(this).data('nombre_rol');
		$("#nombreRolPermiso").val(nombre_rol);	
		cargarRoles(id_rol);
	});	
	function cargarRoles(id){
		$.post("../../include/ctrlindex.php", {
			action:'arregloRoles',
			idRolPermiso:$("#idRolPermiso").val() // id rol seleccionado. ADMIN, INSTRUCTOR, APRENDIZ.
		}, function(data){
			$("#menuControl").html(data.tabs);
		}, 'json');		
	}
	
	//2° llamado al segundo modal, para que presente las opciones del menu, para ese rol seleccioando.
	$(document).on("click", "#btnSubMenu",function () {
		var idMenu = $(this).data('idmenu');
		$("#idMenuPermiso").val(idMenu);		
		var nombreMenu = $(this).data('nombre_menu');
		$("#nombreMenuPermiso").val(nombreMenu);	
		cargarSubMenu();
	});		
	function cargarSubMenu(){
		$.post("../../include/ctrlindex.php", {
			action:'arregloSbMn',
			idMenuPermiso:$("#idMenuPermiso").val(),
			idRol:$("#idRolPermiso").val()
		}, function(data){
			$("#menuCtrlSubMenu").html(data.tabsSm);
		}, 'json');	
	}
		
});