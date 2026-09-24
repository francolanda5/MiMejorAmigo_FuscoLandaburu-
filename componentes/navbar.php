<?php
/* Componente reutilizable: barra de navegación principal. */
function renderizarNavbar($seccionActiva = "inicio") {
    $enlaceActivo = function ($seccion) use ($seccionActiva) {
        return $seccion === $seccionActiva ? " active" : "";
    };
?>
<nav class="navbar navbar-expand-lg sticky-top navbar-mi-amigo">
    <div class="container-fluid navbar-contenido">
        <a class="navbar-brand marca-navbar" href="inicio.php#inicio">
            <img src="img/iconos/icono-pata.png" alt="Ícono de pata" class="icono-navbar">
            <span class="texto-marca">
                <span class="marca-linea-uno">MI MEJOR</span>
                <span class="marca-linea-dos">AMIGO</span>
            </span>
        </a>

        <button class="navbar-toggler boton-menu" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Abrir menú">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse menu-navbar" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link<?php echo $enlaceActivo("servicios"); ?>" href="inicio.php#servicios">Servicios</a></li>
                <li class="nav-item"><a class="nav-link<?php echo $enlaceActivo("consejos"); ?>" href="inicio.php#consejos">Consejos y bienestar</a></li>
                <li class="nav-item"><a class="nav-link<?php echo $enlaceActivo("contacto"); ?>" href="inicio.php#contacto">Contacto</a></li>
                <li class="nav-item"><a class="nav-link<?php echo $enlaceActivo("sobre-nosotros"); ?>" href="inicio.php#sobre-nosotros">Sobre nosotros</a></li>
                <li class="nav-item nav-item-turno"><a class="nav-link nav-link-turno" href="turnos.php">Sacar turno</a></li>
            </ul>
        </div>
    </div>
</nav>
<?php
}
