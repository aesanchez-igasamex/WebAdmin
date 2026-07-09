<!-- CSS -->
<link rel="shortcut icon" href="<?php echo NIVEL ?>../img/favicon.png">
<link rel="stylesheet" href="<?= NIVEL ?>../vendor/assetsB/css/bootstrap.min.css">
<link rel="stylesheet" href="<?= NIVEL ?>../vendor/assetsB/fonts/bootstrap-icons.min.css">
<link rel="stylesheet" href="<?= NIVEL ?>../vendor/assetsB/css/main.min.css">
<link rel="stylesheet" href="<?= NIVEL ?>../vendor/assetsB/vendor/overlay-scroll/OverlayScrollbars.min.css">
<link rel="stylesheet" href="<?= NIVEL ?>../vendor/assetsB/vendor/datatables/dataTables.bs5.css">
<link rel="stylesheet" href="<?= NIVEL ?>../vendor/assetsB/vendor/datatables/dataTables.bs5-custom.css">
<link rel="stylesheet" href="<?= NIVEL ?>../vendor/assetsB/vendor/jquery-loadingModal/css/jquery.loadingModal.css">
<link rel="stylesheet" href="<?= NIVEL ?>../vendor/assetsB/vendor/sweetalert/dist/sweetalert2.min.css">
<link rel="stylesheet" href="<?= NIVEL ?>../vendor/assetsB/css/menu.css">
<link rel="stylesheet" href="<?= NIVEL ?>../vendor/select2/dist/css/select2.min.css">
<link rel="stylesheet" href="<?= NIVEL ?>../vendor/apalfrey/select2-bootstrap-5-theme/dist/select2-bootstrap-5-theme.css">

<!-- JAVASCRIPT -->
<script src="<?= NIVEL ?>../vendor/assetsB/js/jquery.min.js"></script>
<script src="<?= NIVEL ?>../vendor/assetsB/js/bootstrap.bundle.min.js"></script>
<script src="<?= NIVEL ?>../vendor/assetsB/js/modernizr.js"></script>
<script src="<?= NIVEL ?>../vendor/assetsB/js/moment.js"></script>
<script src="<?= NIVEL ?>../vendor/assetsB/vendor/overlay-scroll/jquery.overlayScrollbars.min.js"></script>
<script src="<?= NIVEL ?>../vendor/assetsB/vendor/overlay-scroll/custom-scrollbar.js"></script>
<script src="<?= NIVEL ?>../vendor/assetsB/vendor/datatables/dataTables.min.js"></script>
<script src="<?= NIVEL ?>../vendor/assetsB/vendor/datatables/dataTables.bootstrap.min.js"></script>
<script src="<?= NIVEL ?>../vendor/assetsB/vendor/jquery-loadingModal/js/jquery.loadingModal.js"></script>
<script src="<?= NIVEL ?>../vendor/assetsB/vendor/sweetalert/dist/sweetalert2.all.min.js"></script>
<script src="<?= NIVEL ?>../vendor/assetsB/vendor/sweetalert/dist/sweetalert2.min.js"></script>
<script src="<?= NIVEL ?>../vendor/select2/dist/js/select2.min.js"></script>
<script src="<?= NIVEL ?>../vendor/select2/dist/js/es.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        try {
			if (localStorage.getItem("obligacionesSidebarDesktop") === "collapsed") {
				document.documentElement.classList.add("sidebar-pref-collapsed");
			}
		} catch (error) {
			console.warn("No fue posible leer la preferencia del 1 lateral.", error);
		}

		$(".form-select-chosen").select2({
			language: "es",
			theme: "bootstrap-5",
			placeholder: "Seleccione una opción",
			containerCssClass: "select2--small",
			dropdownCssClass: "select2--small"
		});

		// Enfocar automáticamente la barra de búsqueda al hacer clic
		$('.form-select-chosen').on('select2:open', function() {
            setTimeout(function() {
                document.querySelector('.select2-search__field')?.focus();
            }, 100);
        });
	});

	function mensaje(val) {

		ocultarModalesPadre();

		const opDefault = {
            icon: 'info',                               // Icono por defecto ('success', 'error', 'warning', 'info' o 'question')
			allowEnterKey: false,                       // Habilitar la tecla Enter
			allowEscapeKey: false,                      // Deshabilitar la tecla Escape
			allowOutsideClick: false,                   // Deshabilitar clic fuera del modal
			timerProgressBar: true,                     // Mostrar barra de progreso si hay temporizador
            showConfirmButton: false,                   // Ocultar el botón de confirmación por defecto
			focusConfirm: false,                        // Enfocar el botón de confirmación
			timer: (val.timer > 0) ? val.timer : 1500,  // Cerrar automáticamente después de 2 segundos (ajustable según necesidad)
            title: (val.titulo) ? val.titulo : "",
			text: (val.mensaje) ? val.mensaje : ""
		};


        if (val.error == 0) opDefault.icon = "success";
        if (val.error == 1) opDefault.icon = "error";
        if (val.aceptar == 1) {
			opDefault.confirmButtonText = "Aceptar";
			opDefault.showConfirmButton = true;
			opDefault.timer = false;
		}

        Swal.fire( opDefault ).then( () => restaurarModalesPadre() );
	}

	function ocultarModalesPadre() {
		if (window.parent && window.parent.$) {
			window.parent.$('.modal.show').each(function() {
				this.dataset.reactivar = "true";
				window.parent.$(this).removeClass('show').hide();
			});
			window.parent.$('body').removeClass('modal-open').css('padding-right', '');
		}
	}

	function restaurarModalesPadre() {
		if (window.parent && window.parent.$) {
			window.parent.$('.modal').each(function() {
				if (this.dataset.reactivar === "true") {
					window.parent.$(this).addClass('show').show();
					delete this.dataset.reactivar;
				}
			});
			if (window.parent.$('.modal.show').length > 0) {
				window.parent.$('body').addClass('modal-open');
			}
		}
	}

	function perfil(id) {
		$("#modalPerfil iframe").attr("src", "<?= NIVEL ?>usuarios/editarPerfil.php?id=" + id);
		$("#modalPerfil").modal("show");
	}

	function salir() {
		$("#modalSalir").modal("show");
	}

	function cerrarSesion() {
		$.post("<?= NIVEL ?>com/cerrarSesion.php", {}, function(ruta) {
			location.href = ruta;
		});
	}

</script>
