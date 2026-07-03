<?php
/* ============================================
   login_profesional.php - LOGIN PROFESIONAL
   ============================================ */

session_start();

if (isset($_SESSION["profesional_logueado"]) && $_SESSION["profesional_logueado"] === true) {
    header("Location: admin_consultas.php");
    exit;
}

$estado = $_GET["estado"] ?? "";
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Mejor Amigo - Panel profesional</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- CSS propio -->
    <link rel="stylesheet" href="css/login_profesional.css">
</head>

<body>

    <main class="pagina-login-profesional">

        <section class="card-login-profesional" aria-labelledby="titulo-login-profesional">

            <header class="encabezado-login">
                <a href="inicio.html" class="boton-volver" aria-label="Volver al inicio">‹</a>

                <div>
                    <p>Mi Mejor Amigo</p>
                    <h1 id="titulo-login-profesional">Panel profesional</h1>
                </div>
            </header>

            <p class="texto-login">
                Ingresá con tu matrícula y clave de acceso para administrar consultas.
            </p>

            <?php if ($estado === "error") : ?>
                <div class="mensaje-login mensaje-error-login" aria-live="polite">
                    <p>Los datos ingresados no coinciden con ningún profesional.</p>
                </div>
            <?php endif; ?>

            <?php if ($estado === "cerrado") : ?>
                <div class="mensaje-login mensaje-ok-login" aria-live="polite">
                    <p>Sesión cerrada correctamente.</p>
                </div>
            <?php endif; ?>

            <?php if ($estado === "sesion") : ?>
                <div class="mensaje-login mensaje-error-login" aria-live="polite">
                    <p>Primero tenés que iniciar sesión para acceder al panel.</p>
                </div>
            <?php endif; ?>

            <form class="form-login" action="php/procesar_login_profesional.php" method="POST">

                <div class="campo-login">
                    <label for="matricula_profesional">Matrícula profesional</label>
                    <input type="number" id="matricula_profesional" name="matricula_profesional"
                        placeholder="Ej: 000000" required>
                </div>

                <div class="campo-login">
                    <label for="clave_acceso">Clave de acceso</label>
                    <input type="password" id="clave_acceso" name="clave_acceso"
                        placeholder="Ej: vet000000" required>
                </div>

                <button type="submit" class="boton-login">
                    Ingresar
                </button>

            </form>

        </section>

    </main>

</body>

</html>