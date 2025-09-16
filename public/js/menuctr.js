// Evitar que los enlaces con href="#" recarguen la página
$(document).on('click','a[href="#"]',function(e){ e.preventDefault(); });

const $sidebar = $('#sidebarMenu');
const $backdrop = $('#sidebarBackdrop');

// Funciones para abrir y cerrar el sidebar

function openSidebar(){
  $sidebar.addClass('show');
  $backdrop.addClass('show');
  $('body').addClass('sidebar-open');
}

// Cerrar sidebar

function closeSidebar(){
  $sidebar.removeClass('show');
  $backdrop.removeClass('show');
  $('body').removeClass('sidebar-open');
}

$('#toggleMenu').on('click', openSidebar);
$('#closeMenu, #sidebarBackdrop').on('click', closeSidebar);

// Cerrar menú al hacer clic fuera del sidebar
$(document).on('click', function(e) {
  if ($sidebar.hasClass('show') && 
      !$sidebar.is(e.target) && 
      $sidebar.has(e.target).length === 0 && 
      !$('#toggleMenu').is(e.target) &&
      $('#toggleMenu').has(e.target).length === 0) {
    closeSidebar();
  }
});

// Cerrar si se cambia a ancho >= md
