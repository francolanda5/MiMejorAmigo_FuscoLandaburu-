<?php
/* ============================================
   admin_consultas.php - PANEL SIMPLE DE CONSULTAS
   ============================================ */

session_start();

if (!isset($_SESSION["profesional_logueado"]) || $_SESSION["profesional_logueado"] !== true) {
    header("Location: login_profesional.php?estado=sesion");
    exit;
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

require_once "php/conexion.php";

$mensaje_estado = $_GET["estado"] ?? "";

$nombre_profesional_sesion = $_SESSION["nombre_profesional"] ?? "Profesional";

$filtro_profesional = $_GET["profesional"] ?? "";
$filtro_tipo = $_GET["tipo"] ?? "";
$filtro_pago = $_GET["pago"] ?? "";
$filtro_fecha = $_GET["fecha"] ?? "";

$consultas = [];
$profesionales = [];

$tipos_consulta = [
    "consulta_general" => "Consulta general",
    "vacunacion" => "Vacunación",
    "cirugias" => "Cirugías",
    "analisis" => "Análisis"
];

try {
    /* ============================================
       OBTENER PROFESIONALES PARA EL FILTRO
       ============================================ */

    $consulta_profesionales = $conexion->prepare("
        SELECT
            `matricula_profesional`,
            `nombre`,
            `especialidad`
        FROM `veterinario`
        ORDER BY `nombre` ASC
    ");

    $consulta_profesionales->execute();
    $profesionales = $consulta_profesionales->fetchAll(PDO::FETCH_ASSOC);

    /* ============================================
       ARMAR FILTROS DE CONSULTAS
       ============================================ */

    $condiciones = [];
    $parametros = [];

    if (!empty($filtro_profesional)) {
        $condiciones[] = "`consulta`.`matricula_profesional` = :profesional";
        $parametros[":profesional"] = $filtro_profesional;
    }

    if (!empty($filtro_tipo) && isset($tipos_consulta[$filtro_tipo])) {
        $condiciones[] = "`consulta`.`motivo_consulta` LIKE :tipo";
        $parametros[":tipo"] = $tipos_consulta[$filtro_tipo] . "%";
    }

    if ($filtro_pago === "0" || $filtro_pago === "1") {
        $condiciones[] = "`consulta`.`pago` = :pago";
        $parametros[":pago"] = $filtro_pago;
    }

    if (!empty($filtro_fecha)) {
        $condiciones[] = "`consulta`.`fecha` = :fecha";
        $parametros[":fecha"] = $filtro_fecha;
    }

    $sql_where = "";

    if (!empty($condiciones)) {
        $sql_where = "WHERE " . implode(" AND ", $condiciones);
    }

    /* ============================================
       OBTENER CONSULTAS
       ============================================ */

    $consulta = $conexion->prepare("
        SELECT
            `consulta`.`id_consulta`,
            `consulta`.`fecha`,
            TIME_FORMAT(`consulta`.`horario`, '%H:%i') AS horario,
            `consulta`.`motivo_consulta`,
            `consulta`.`diagnostico`,
            `consulta`.`tratamiento`,
            `consulta`.`pago`,

            `mascota`.`nombre` AS nombre_mascota,
            `mascota`.`especie`,
            `mascota`.`raza`,

            `dueño`.`nombre` AS nombre_dueno,

            `veterinario`.`nombre` AS nombre_profesional,
            `veterinario`.`especialidad`

        FROM `consulta`

        LEFT JOIN `mascota`
            ON `consulta`.`id_paciente` = `mascota`.`id_paciente`

        LEFT JOIN `dueño`
            ON `mascota`.`dni_dueño` = `dueño`.`dni_dueño`

        LEFT JOIN `veterinario`
            ON `consulta`.`matricula_profesional` = `veterinario`.`matricula_profesional`

        $sql_where

        ORDER BY `consulta`.`fecha` DESC, `consulta`.`horario` DESC
    ");

    foreach ($parametros as $clave => $valor) {
        if ($clave === ":profesional" || $clave === ":pago") {
            $consulta->bindValue($clave, $valor, PDO::PARAM_INT);
        } else {
            $consulta->bindValue($clave, $valor);
        }
    }

    $consulta->execute();
    $consultas = $consulta->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $error) {
    $consultas = [];
    $profesionales = [];
    $mensaje_estado = "error";
}

function limpiarTexto($texto) {
    return htmlspecialchars($texto ?? "", ENT_QUOTES, "UTF-8");
}

function formatearFechaAdmin($fecha) {
    if (empty($fecha)) {
        return "Sin fecha";
    }

    $fecha_creada = date_create($fecha);

    if (!$fecha_creada) {
        return $fecha;
    }

    return date_format($fecha_creada, "d/m/Y");
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Mejor Amigo - Consultas</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- CSS propio -->
    <link rel="stylesheet" href="css/admin.css">
</head>

<body>

    <main class="pagina-admin">

        <!-- ENCABEZADO -->
        <header class="encabezado-admin">
            <a href="inicio.html" class="boton-volver" aria-label="Volver al inicio">‹</a>

            <div class="titulos-admin">
                <p>Panel administrador</p>
                <h1>Consultas</h1>
            </div>
        </header>

        <!-- SESIÓN -->
        <section class="sesion-admin">
            <p>
                Sesión iniciada como
                <strong><?php echo limpiarTexto($nombre_profesional_sesion); ?></strong>
            </p>

            <a href="php/cerrar_sesion.php" class="boton-cerrar-sesion">
                Cerrar sesión
            </a>
        </section>

        <!-- MENSAJES -->
        <?php if ($mensaje_estado === "ok") : ?>
            <section class="mensaje-admin mensaje-ok" aria-live="polite">
                <p>La consulta se actualizó correctamente.</p>
            </section>
        <?php endif; ?>

        <?php if ($mensaje_estado === "error") : ?>
            <section class="mensaje-admin mensaje-error-admin" aria-live="polite">
                <p>No se pudo actualizar la consulta. Revisá los datos e intentá nuevamente.</p>
            </section>
        <?php endif; ?>

        <!-- INTRO -->
        <section class="intro-admin">
            <h2>Turnos registrados</h2>
            <p>
                Desde acá podés revisar las consultas cargadas y actualizar diagnóstico, tratamiento y estado de pago.
            </p>
        </section>

        <!-- FILTROS -->
        <section class="card-filtros" aria-labelledby="titulo-filtros">
            <header class="cabecera-filtros">
                <div>
                    <h2 id="titulo-filtros">Filtrar consultas</h2>
                    <p>
                        Mostrando <?php echo count($consultas); ?> resultado(s).
                    </p>
                </div>
            </header>

            <form class="form-filtros" method="GET" action="admin_consultas.php">

                <div class="campo-filtro">
                    <label for="profesional">Profesional</label>

                    <select id="profesional" name="profesional">
                        <option value="">Todos los profesionales</option>

                        <?php foreach ($profesionales as $profesional) : ?>
                            <?php
                            $matricula = (string) $profesional["matricula_profesional"];
                            ?>

                            <option value="<?php echo limpiarTexto($matricula); ?>"
                                <?php echo $filtro_profesional === $matricula ? "selected" : ""; ?>>
                                <?php echo limpiarTexto($profesional["nombre"]); ?>
                                -
                                <?php echo limpiarTexto($profesional["especialidad"]); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="campo-filtro">
                    <label for="tipo">Tipo de consulta</label>

                    <select id="tipo" name="tipo">
                        <option value="">Todas las consultas</option>

                        <option value="consulta_general" <?php echo $filtro_tipo === "consulta_general" ? "selected" : ""; ?>>
                            Consulta general
                        </option>

                        <option value="vacunacion" <?php echo $filtro_tipo === "vacunacion" ? "selected" : ""; ?>>
                            Vacunación
                        </option>

                        <option value="cirugias" <?php echo $filtro_tipo === "cirugias" ? "selected" : ""; ?>>
                            Cirugías
                        </option>

                        <option value="analisis" <?php echo $filtro_tipo === "analisis" ? "selected" : ""; ?>>
                            Análisis
                        </option>
                    </select>
                </div>

                <div class="campo-filtro">
                    <label for="pago">Estado de pago</label>

                    <select id="pago" name="pago">
                        <option value="">Todos</option>
                        <option value="0" <?php echo $filtro_pago === "0" ? "selected" : ""; ?>>
                            Pendiente
                        </option>
                        <option value="1" <?php echo $filtro_pago === "1" ? "selected" : ""; ?>>
                            Pago
                        </option>
                    </select>
                </div>

                <div class="campo-filtro">
                    <label for="fecha">Fecha</label>
                    <input type="date" id="fecha" name="fecha" value="<?php echo limpiarTexto($filtro_fecha); ?>">
                </div>

                <div class="acciones-filtros">
                    <button type="submit" class="boton-filtrar">
                        Filtrar
                    </button>

                    <a href="admin_consultas.php" class="boton-limpiar">
                        Limpiar
                    </a>
                </div>

            </form>
        </section>

        <!-- LISTADO DE CONSULTAS -->
        <section class="listado-consultas" aria-label="Listado de consultas">

            <?php if (empty($consultas)) : ?>

                <article class="card-consulta card-vacia">
                    <h2>No hay consultas para mostrar</h2>
                    <p>Probá limpiar los filtros o revisar si hay turnos cargados.</p>
                </article>

            <?php endif; ?>

            <?php foreach ($consultas as $consulta_item) : ?>

                <?php
                $id_consulta = $consulta_item["id_consulta"];
                $pago = (int) $consulta_item["pago"];
                ?>

                <article class="card-consulta">

                    <header class="cabecera-card-consulta">
                        <div>
                            <span class="etiqueta-id">Consulta #<?php echo limpiarTexto($id_consulta); ?></span>
                            <h2>
                                <?php echo limpiarTexto(formatearFechaAdmin($consulta_item["fecha"])); ?>
                                ·
                                <?php echo limpiarTexto($consulta_item["horario"]); ?>
                            </h2>
                        </div>

                        <?php if ($pago === 1) : ?>
                            <span class="badge-pago pago-realizado">Pago</span>
                        <?php else : ?>
                            <span class="badge-pago pago-pendiente">Pendiente</span>
                        <?php endif; ?>
                    </header>

                    <section class="datos-consulta" aria-label="Datos de la consulta">

                        <div class="fila-dato">
                            <span>Mascota</span>
                            <strong>
                                <?php echo limpiarTexto($consulta_item["nombre_mascota"]); ?>
                            </strong>
                        </div>

                        <div class="fila-dato">
                            <span>Dueño</span>
                            <strong>
                                <?php echo limpiarTexto($consulta_item["nombre_dueno"]); ?>
                            </strong>
                        </div>

                        <div class="fila-dato">
                            <span>Profesional</span>
                            <strong>
                                <?php echo limpiarTexto($consulta_item["nombre_profesional"]); ?>
                            </strong>
                        </div>

                        <div class="motivo-consulta">
                            <span>Motivo de consulta</span>
                            <p>
                                <?php echo limpiarTexto($consulta_item["motivo_consulta"]); ?>
                            </p>
                        </div>

                    </section>

                    <div class="acciones-card-consulta">
                        <a href="detalle_consulta.php?id_consulta=<?php echo limpiarTexto($id_consulta); ?>"
                            class="boton-detalle">
                            Ver detalle
                        </a>

                        <button type="button" class="boton-editar" data-id="<?php echo limpiarTexto($id_consulta); ?>">
                            Editar
                        </button>
                    </div>

                    <!-- FORMULARIO DE EDICIÓN -->
                    <form class="form-edicion" id="form-edicion-<?php echo limpiarTexto($id_consulta); ?>"
                        action="php/actualizar_consulta.php" method="POST" hidden>

                        <input type="hidden" name="id_consulta" value="<?php echo limpiarTexto($id_consulta); ?>">

                        <div class="campo-edicion">
                            <label for="diagnostico-<?php echo limpiarTexto($id_consulta); ?>">Diagnóstico</label>
                            <textarea id="diagnostico-<?php echo limpiarTexto($id_consulta); ?>" name="diagnostico"
                                placeholder="Escribí el diagnóstico..."><?php echo limpiarTexto($consulta_item["diagnostico"]); ?></textarea>
                        </div>

                        <div class="campo-edicion">
                            <label for="tratamiento-<?php echo limpiarTexto($id_consulta); ?>">Tratamiento</label>
                            <textarea id="tratamiento-<?php echo limpiarTexto($id_consulta); ?>" name="tratamiento"
                                placeholder="Escribí el tratamiento indicado..."><?php echo limpiarTexto($consulta_item["tratamiento"]); ?></textarea>
                        </div>

                        <div class="campo-edicion">
                            <label for="pago-<?php echo limpiarTexto($id_consulta); ?>">Estado de pago</label>

                            <select id="pago-<?php echo limpiarTexto($id_consulta); ?>" name="pago">
                                <option value="0" <?php echo $pago === 0 ? "selected" : ""; ?>>
                                    Pendiente
                                </option>
                                <option value="1" <?php echo $pago === 1 ? "selected" : ""; ?>>
                                    Pago
                                </option>
                            </select>
                        </div>

                        <div class="acciones-edicion">
                            <button type="submit" class="boton-guardar">
                                Guardar cambios
                            </button>

                            <button type="button" class="boton-cancelar" data-id="<?php echo limpiarTexto($id_consulta); ?>">
                                Cancelar
                            </button>
                        </div>

                    </form>

                </article>

            <?php endforeach; ?>

        </section>

    </main>

    <script>
        /* ============================================
           ADMIN - MOSTRAR / OCULTAR EDICIÓN
           ============================================ */

        document.addEventListener("DOMContentLoaded", function () {
            const botonesEditar = document.querySelectorAll(".boton-editar");
            const botonesCancelar = document.querySelectorAll(".boton-cancelar");

            botonesEditar.forEach(function (boton) {
                boton.addEventListener("click", function () {
                    const idConsulta = boton.dataset.id;
                    const formulario = document.getElementById("form-edicion-" + idConsulta);

                    if (formulario) {
                        formulario.hidden = !formulario.hidden;
                    }
                });
            });

            botonesCancelar.forEach(function (boton) {
                boton.addEventListener("click", function () {
                    const idConsulta = boton.dataset.id;
                    const formulario = document.getElementById("form-edicion-" + idConsulta);

                    if (formulario) {
                        formulario.hidden = true;
                    }
                });
            });
        });
    </script>

</body>

</html>