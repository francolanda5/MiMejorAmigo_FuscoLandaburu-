<?php
/* ============================================
   detalle_consulta.php - DETALLE DE CONSULTA
   ============================================ */

session_start();

if (!isset($_SESSION["profesional_logueado"]) || $_SESSION["profesional_logueado"] !== true) {
    header("Location: login_profesional.php?estado=sesion");
    exit;
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

require_once "php/conexion.php";

$id_consulta = $_GET["id_consulta"] ?? "";
$volver_panel = $_GET["volver"] ?? "admin_consultas.php";

if (
    empty($volver_panel) ||
    strpos($volver_panel, "admin_consultas.php") !== 0 ||
    strpos($volver_panel, "://") !== false ||
    strpos($volver_panel, "//") === 0
) {
    $volver_panel = "admin_consultas.php";
}

$consulta_detalle = null;
$historial_consultas = [];
$antecedentes = [];
$prescripciones = [];
$mensaje_error = "";

if (empty($id_consulta) || !is_numeric($id_consulta)) {
    $mensaje_error = "La consulta solicitada no es válida.";
} else {
    try {
        /* ============================================
           1. OBTENER DATOS PRINCIPALES DE LA CONSULTA
           ============================================ */

        $consulta = $conexion->prepare("
            SELECT
                `consulta`.`id_consulta`,
                `consulta`.`id_paciente`,
                `consulta`.`fecha`,
                TIME_FORMAT(`consulta`.`horario`, '%H:%i') AS horario,
                `consulta`.`horario` AS horario_original,
                `consulta`.`motivo_consulta`,
                `consulta`.`diagnostico`,
                `consulta`.`tratamiento`,
                `consulta`.`pago`,
                `consulta`.`estado`,
                `consulta`.`matricula_profesional`,

                `mascota`.`nombre` AS nombre_mascota,
                `mascota`.`especie`,
                `mascota`.`raza`,
                `mascota`.`edad`,
                `mascota`.`dni_dueño`,

                `dueño`.`nombre` AS nombre_dueno,
                `dueño`.`mail`,
                `dueño`.`teléfono`,
                `dueño`.`calle`,
                `dueño`.`altura`,
                `dueño`.`barrio`,

                `veterinario`.`nombre` AS nombre_profesional,
                `veterinario`.`especialidad`

            FROM `consulta`

            LEFT JOIN `mascota`
                ON `consulta`.`id_paciente` = `mascota`.`id_paciente`

            LEFT JOIN `dueño`
                ON `mascota`.`dni_dueño` = `dueño`.`dni_dueño`

            LEFT JOIN `veterinario`
                ON `consulta`.`matricula_profesional` = `veterinario`.`matricula_profesional`

            WHERE `consulta`.`id_consulta` = :id_consulta

            LIMIT 1
        ");

        $consulta->bindValue(":id_consulta", $id_consulta, PDO::PARAM_INT);
        $consulta->execute();

        $consulta_detalle = $consulta->fetch(PDO::FETCH_ASSOC);

        if (!$consulta_detalle) {
            $mensaje_error = "No se encontró la consulta solicitada.";
        } else {
            /* ============================================
               2. OBTENER HISTORIAL DEL PACIENTE
               ============================================ */

            try {
                $consulta_historial = $conexion->prepare("
                    SELECT
                        `consulta`.`id_consulta`,
                        `consulta`.`fecha`,
                        TIME_FORMAT(`consulta`.`horario`, '%H:%i') AS horario,
                        `consulta`.`motivo_consulta`,
                        `consulta`.`diagnostico`,
                        `consulta`.`tratamiento`,
                        `consulta`.`pago`,
                        `consulta`.`estado`,

                        `veterinario`.`nombre` AS nombre_profesional,
                        `veterinario`.`especialidad`

                    FROM `consulta`

                    LEFT JOIN `veterinario`
                        ON `consulta`.`matricula_profesional` = `veterinario`.`matricula_profesional`

                    WHERE `consulta`.`id_paciente` = :id_paciente
                      AND `consulta`.`id_consulta` <> :id_consulta
                      AND (
                            `consulta`.`fecha` < :fecha_actual
                            OR (
                                `consulta`.`fecha` = :fecha_actual
                                AND `consulta`.`horario` < :horario_actual
                            )
                      )

                    ORDER BY `consulta`.`fecha` DESC, `consulta`.`horario` DESC
                ");

                $consulta_historial->bindValue(":id_paciente", $consulta_detalle["id_paciente"], PDO::PARAM_INT);
                $consulta_historial->bindValue(":id_consulta", $consulta_detalle["id_consulta"], PDO::PARAM_INT);
                $consulta_historial->bindValue(":fecha_actual", $consulta_detalle["fecha"]);
                $consulta_historial->bindValue(":horario_actual", $consulta_detalle["horario_original"]);

                $consulta_historial->execute();

                $historial_consultas = $consulta_historial->fetchAll(PDO::FETCH_ASSOC);

            } catch (PDOException $error_historial) {
                $historial_consultas = [];
            }

            /* ============================================
               3. OBTENER ANTECEDENTES MÉDICOS
               ============================================ */

            try {
                $consulta_antecedentes = $conexion->prepare("
                    SELECT *
                    FROM `antecedente médico`
                    WHERE `id_paciente` = :id_paciente
                    ORDER BY 1 DESC
                ");

                $consulta_antecedentes->bindValue(":id_paciente", $consulta_detalle["id_paciente"], PDO::PARAM_INT);
                $consulta_antecedentes->execute();

                $antecedentes = $consulta_antecedentes->fetchAll(PDO::FETCH_ASSOC);

            } catch (PDOException $error_antecedentes) {
                $antecedentes = [];
            }

            /* ============================================
               4. OBTENER PRESCRIPCIONES
               ============================================ */

            try {
                $consulta_prescripciones = $conexion->prepare("
                    SELECT *
                    FROM `prescripcion`
                    WHERE `id_consulta` = :id_consulta
                    ORDER BY 1 DESC
                ");

                $consulta_prescripciones->bindValue(":id_consulta", $id_consulta, PDO::PARAM_INT);
                $consulta_prescripciones->execute();

                $prescripciones_base = $consulta_prescripciones->fetchAll(PDO::FETCH_ASSOC);

                foreach ($prescripciones_base as $prescripcion) {
                    $medicamento = null;

                    if (!empty($prescripcion["id_medicamento"])) {
                        try {
                            $consulta_medicamento = $conexion->prepare("
                                SELECT *
                                FROM `medicamentos`
                                WHERE `id_medicamento` = :id_medicamento
                                LIMIT 1
                            ");

                            $consulta_medicamento->bindValue(":id_medicamento", $prescripcion["id_medicamento"]);
                            $consulta_medicamento->execute();

                            $medicamento = $consulta_medicamento->fetch(PDO::FETCH_ASSOC);

                        } catch (PDOException $error_medicamento) {
                            $medicamento = null;
                        }
                    }

                    $prescripciones[] = [
                        "prescripcion" => $prescripcion,
                        "medicamento" => $medicamento
                    ];
                }

            } catch (PDOException $error_prescripcion) {
                $prescripciones = [];
            }
        }

    } catch (PDOException $error) {
        $mensaje_error = "No se pudo cargar el detalle de la consulta.";
    }
}

function limpiarTexto($texto) {
    return htmlspecialchars($texto ?? "", ENT_QUOTES, "UTF-8");
}

function mostrarTexto($texto, $texto_vacio = "Sin datos cargados") {
    $texto = trim((string)($texto ?? ""));

    if ($texto === "") {
        return $texto_vacio;
    }

    return $texto;
}

function formatearFechaDetalle($fecha) {
    if (empty($fecha)) {
        return "Sin fecha";
    }

    $fecha_creada = date_create($fecha);

    if (!$fecha_creada) {
        return $fecha;
    }

    return date_format($fecha_creada, "d/m/Y");
}

function etiquetaCampo($campo) {
    $campo = str_replace("_", " ", $campo);
    $campo = str_replace("id ", "ID ", $campo);

    return ucfirst($campo);
}

function obtenerPrimerValor($datos, $claves) {
    if (!is_array($datos)) {
        return "";
    }

    foreach ($claves as $clave) {
        if (isset($datos[$clave]) && trim((string)$datos[$clave]) !== "") {
            return $datos[$clave];
        }
    }

    return "";
}

function obtenerEstadoConsulta($estado) {
    $estado = trim((string)($estado ?? ""));

    if ($estado === "") {
        return "Pendiente";
    }

    return $estado;
}

function renderizarCamposDinamicos($datos, $campos_omitidos = []) {
    foreach ($datos as $campo => $valor) {
        if (in_array($campo, $campos_omitidos)) {
            continue;
        }

        echo '<div class="fila-detalle">';
        echo '<span>' . limpiarTexto(etiquetaCampo($campo)) . '</span>';
        echo '<strong>' . nl2br(limpiarTexto(mostrarTexto($valor))) . '</strong>';
        echo '</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Mejor Amigo - Detalle de consulta</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- CSS propio -->
    <link rel="stylesheet" href="css/detalle_consulta.css">
</head>

<body>

    <main class="pagina-detalle">

        <!-- ENCABEZADO -->
        <header class="encabezado-detalle">
            <a href="<?php echo limpiarTexto($volver_panel); ?>" class="boton-volver" aria-label="Volver al panel">‹</a>

            <div class="titulos-detalle">
                <p>Panel profesional</p>
                <h1>Detalle de consulta</h1>
            </div>
        </header>

        <?php if (!empty($mensaje_error)) : ?>

            <!-- ERROR -->
            <section class="card-detalle card-error">
                <h2>No se pudo mostrar el detalle</h2>
                <p><?php echo limpiarTexto($mensaje_error); ?></p>

                <a href="<?php echo limpiarTexto($volver_panel); ?>" class="boton-principal">
                    Volver al panel
                </a>
            </section>

        <?php else : ?>

            <?php
            $pago = (int)$consulta_detalle["pago"];
            $estado_consulta = obtenerEstadoConsulta($consulta_detalle["estado"]);
            ?>

            <!-- RESUMEN SUPERIOR -->
            <section class="card-resumen-detalle">
                <span class="etiqueta-id">Consulta #<?php echo limpiarTexto($consulta_detalle["id_consulta"]); ?></span>

                <h2>
                    <?php echo limpiarTexto(formatearFechaDetalle($consulta_detalle["fecha"])); ?>
                    ·
                    <?php echo limpiarTexto($consulta_detalle["horario"]); ?>
                </h2>

                <?php if ($pago === 1) : ?>
                    <span class="badge-pago pago-realizado">Pago</span>
                <?php else : ?>
                    <span class="badge-pago pago-pendiente">Pendiente de pago</span>
                <?php endif; ?>
            </section>

            <!-- DATOS DE LA CONSULTA -->
            <section class="card-detalle card-desplegable">
                <header class="cabecera-card-desplegable">
                    <h2>Datos de la consulta</h2>

                    <button type="button" class="boton-desplegar-detalle"
                        aria-expanded="false" aria-controls="contenido-datos-consulta">
                        <span class="texto-desplegar">Ver más</span>
                        <span class="flecha-desplegar" aria-hidden="true">⌄</span>
                    </button>
                </header>

                <div id="contenido-datos-consulta" class="contenido-card-detalle" hidden>
                    <div class="fila-detalle">
                        <span>ID de consulta</span>
                        <strong><?php echo limpiarTexto($consulta_detalle["id_consulta"]); ?></strong>
                    </div>

                    <div class="fila-detalle">
                        <span>Fecha</span>
                        <strong><?php echo limpiarTexto(formatearFechaDetalle($consulta_detalle["fecha"])); ?></strong>
                    </div>

                    <div class="fila-detalle">
                        <span>Horario</span>
                        <strong><?php echo limpiarTexto($consulta_detalle["horario"]); ?></strong>
                    </div>

                    <div class="fila-detalle">
                        <span>Estado</span>
                        <strong><?php echo limpiarTexto($estado_consulta); ?></strong>
                    </div>

                    <div class="bloque-texto-detalle">
                        <span>Motivo de consulta</span>
                        <p><?php echo nl2br(limpiarTexto(mostrarTexto($consulta_detalle["motivo_consulta"]))); ?></p>
                    </div>

                    <div class="bloque-texto-detalle">
                        <span>Diagnóstico</span>
                        <p><?php echo nl2br(limpiarTexto(mostrarTexto($consulta_detalle["diagnostico"]))); ?></p>
                    </div>

                    <div class="bloque-texto-detalle">
                        <span>Tratamiento</span>
                        <p><?php echo nl2br(limpiarTexto(mostrarTexto($consulta_detalle["tratamiento"]))); ?></p>
                    </div>

                    <div class="fila-detalle">
                        <span>Estado de pago</span>
                        <strong><?php echo $pago === 1 ? "Pago" : "Pendiente"; ?></strong>
                    </div>

                    <div class="fila-detalle">
                        <span>Profesional</span>
                        <strong><?php echo limpiarTexto(mostrarTexto($consulta_detalle["nombre_profesional"])); ?></strong>
                    </div>

                    <div class="fila-detalle">
                        <span>Especialidad</span>
                        <strong><?php echo limpiarTexto(mostrarTexto($consulta_detalle["especialidad"])); ?></strong>
                    </div>
                </div>
            </section>

            <!-- DATOS DE LA MASCOTA -->
            <section class="card-detalle card-desplegable">
                <header class="cabecera-card-desplegable">
                    <h2>Datos de la mascota</h2>

                    <button type="button" class="boton-desplegar-detalle"
                        aria-expanded="false" aria-controls="contenido-datos-mascota">
                        <span class="texto-desplegar">Ver más</span>
                        <span class="flecha-desplegar" aria-hidden="true">⌄</span>
                    </button>
                </header>

                <div id="contenido-datos-mascota" class="contenido-card-detalle" hidden>
                    <div class="fila-detalle">
                        <span>ID paciente</span>
                        <strong><?php echo limpiarTexto(mostrarTexto($consulta_detalle["id_paciente"])); ?></strong>
                    </div>

                    <div class="fila-detalle">
                        <span>Nombre</span>
                        <strong><?php echo limpiarTexto(mostrarTexto($consulta_detalle["nombre_mascota"])); ?></strong>
                    </div>

                    <div class="fila-detalle">
                        <span>Especie</span>
                        <strong><?php echo limpiarTexto(mostrarTexto($consulta_detalle["especie"])); ?></strong>
                    </div>

                    <div class="fila-detalle">
                        <span>Raza</span>
                        <strong><?php echo limpiarTexto(mostrarTexto($consulta_detalle["raza"])); ?></strong>
                    </div>

                    <div class="fila-detalle">
                        <span>Edad</span>
                        <strong><?php echo limpiarTexto(mostrarTexto($consulta_detalle["edad"])); ?></strong>
                    </div>
                </div>
            </section>

            <!-- DATOS DEL DUEÑO -->
            <section class="card-detalle card-desplegable">
                <header class="cabecera-card-desplegable">
                    <h2>Datos del dueño</h2>

                    <button type="button" class="boton-desplegar-detalle"
                        aria-expanded="false" aria-controls="contenido-datos-dueno">
                        <span class="texto-desplegar">Ver más</span>
                        <span class="flecha-desplegar" aria-hidden="true">⌄</span>
                    </button>
                </header>

                <div id="contenido-datos-dueno" class="contenido-card-detalle" hidden>
                    <div class="fila-detalle">
                        <span>DNI</span>
                        <strong><?php echo limpiarTexto(mostrarTexto($consulta_detalle["dni_dueño"])); ?></strong>
                    </div>

                    <div class="fila-detalle">
                        <span>Nombre</span>
                        <strong><?php echo limpiarTexto(mostrarTexto($consulta_detalle["nombre_dueno"])); ?></strong>
                    </div>

                    <div class="fila-detalle">
                        <span>Mail</span>
                        <strong><?php echo limpiarTexto(mostrarTexto($consulta_detalle["mail"])); ?></strong>
                    </div>

                    <div class="fila-detalle">
                        <span>Teléfono</span>
                        <strong><?php echo limpiarTexto(mostrarTexto($consulta_detalle["teléfono"])); ?></strong>
                    </div>

                    <div class="fila-detalle">
                        <span>Calle</span>
                        <strong><?php echo limpiarTexto(mostrarTexto($consulta_detalle["calle"])); ?></strong>
                    </div>

                    <div class="fila-detalle">
                        <span>Altura</span>
                        <strong><?php echo limpiarTexto(mostrarTexto($consulta_detalle["altura"])); ?></strong>
                    </div>

                    <div class="fila-detalle">
                        <span>Barrio</span>
                        <strong><?php echo limpiarTexto(mostrarTexto($consulta_detalle["barrio"])); ?></strong>
                    </div>
                </div>
            </section>

            <!-- HISTORIAL DEL PACIENTE -->
            <section class="card-detalle card-desplegable">
                <header class="cabecera-card-desplegable">
                    <h2>Historial del paciente</h2>

                    <button type="button" class="boton-desplegar-detalle"
                        aria-expanded="false" aria-controls="contenido-historial-paciente">
                        <span class="texto-desplegar">Ver más</span>
                        <span class="flecha-desplegar" aria-hidden="true">⌄</span>
                    </button>
                </header>

                <div id="contenido-historial-paciente" class="contenido-card-detalle" hidden>
                    <?php if (empty($historial_consultas)) : ?>

                        <p class="texto-vacio">No hay consultas anteriores registradas para esta mascota.</p>

                    <?php else : ?>

                        <?php foreach ($historial_consultas as $historial_item) : ?>

                            <?php
                            $pago_historial = (int)$historial_item["pago"];
                            $estado_historial = obtenerEstadoConsulta($historial_item["estado"]);
                            ?>

                            <article class="subcard-detalle">
                                <h3>
                                    Consulta #<?php echo limpiarTexto($historial_item["id_consulta"]); ?>
                                    ·
                                    <?php echo limpiarTexto(formatearFechaDetalle($historial_item["fecha"])); ?>
                                    ·
                                    <?php echo limpiarTexto($historial_item["horario"]); ?>
                                </h3>

                                <div class="fila-detalle">
                                    <span>Estado</span>
                                    <strong><?php echo limpiarTexto($estado_historial); ?></strong>
                                </div>

                                <div class="fila-detalle">
                                    <span>Profesional</span>
                                    <strong><?php echo limpiarTexto(mostrarTexto($historial_item["nombre_profesional"])); ?></strong>
                                </div>

                                <div class="fila-detalle">
                                    <span>Especialidad</span>
                                    <strong><?php echo limpiarTexto(mostrarTexto($historial_item["especialidad"])); ?></strong>
                                </div>

                                <div class="fila-detalle">
                                    <span>Estado de pago</span>
                                    <strong><?php echo $pago_historial === 1 ? "Pago" : "Pendiente"; ?></strong>
                                </div>

                                <div class="bloque-texto-detalle">
                                    <span>Motivo de consulta</span>
                                    <p><?php echo nl2br(limpiarTexto(mostrarTexto($historial_item["motivo_consulta"]))); ?></p>
                                </div>

                                <div class="bloque-texto-detalle">
                                    <span>Diagnóstico</span>
                                    <p><?php echo nl2br(limpiarTexto(mostrarTexto($historial_item["diagnostico"]))); ?></p>
                                </div>

                                <div class="bloque-texto-detalle">
                                    <span>Tratamiento</span>
                                    <p><?php echo nl2br(limpiarTexto(mostrarTexto($historial_item["tratamiento"]))); ?></p>
                                </div>
                            </article>

                        <?php endforeach; ?>

                    <?php endif; ?>
                </div>
            </section>

            <!-- ANTECEDENTES -->
            <section class="card-detalle card-desplegable">
                <header class="cabecera-card-desplegable">
                    <h2>Antecedentes médicos</h2>

                    <button type="button" class="boton-desplegar-detalle"
                        aria-expanded="false" aria-controls="contenido-antecedentes">
                        <span class="texto-desplegar">Ver más</span>
                        <span class="flecha-desplegar" aria-hidden="true">⌄</span>
                    </button>
                </header>

                <div id="contenido-antecedentes" class="contenido-card-detalle" hidden>
                    <?php if (empty($antecedentes)) : ?>

                        <p class="texto-vacio">No hay antecedentes médicos cargados.</p>

                    <?php else : ?>

                        <?php foreach ($antecedentes as $indice => $antecedente) : ?>

                            <article class="subcard-detalle">
                                <h3>Antecedente <?php echo $indice + 1; ?></h3>

                                <?php renderizarCamposDinamicos($antecedente); ?>
                            </article>

                        <?php endforeach; ?>

                    <?php endif; ?>
                </div>
            </section>

            <!-- PRESCRIPCIÓN -->
            <section class="card-detalle card-desplegable">
                <header class="cabecera-card-desplegable">
                    <h2>Prescripción y medicamentos</h2>

                    <button type="button" class="boton-desplegar-detalle"
                        aria-expanded="false" aria-controls="contenido-prescripcion">
                        <span class="texto-desplegar">Ver más</span>
                        <span class="flecha-desplegar" aria-hidden="true">⌄</span>
                    </button>
                </header>

                <div id="contenido-prescripcion" class="contenido-card-detalle" hidden>
                    <?php if (empty($prescripciones)) : ?>

                        <p class="texto-vacio">No hay prescripción cargada para esta consulta.</p>

                    <?php else : ?>

                        <?php foreach ($prescripciones as $indice => $item_prescripcion) : ?>

                            <?php
                            $prescripcion = $item_prescripcion["prescripcion"];
                            $medicamento = $item_prescripcion["medicamento"];

                            $nombre_medicamento = obtenerPrimerValor($medicamento, [
                                "nombre",
                                "medicamento",
                                "nombre_medicamento",
                                "descripcion",
                                "descripción"
                            ]);

                            $tipo_medicamento = obtenerPrimerValor($medicamento, [
                                "tipo",
                                "tipo_medicamento",
                                "clasificacion",
                                "clasificación"
                            ]);

                            $duracion = $prescripcion["duracion"] ?? ($prescripcion["duración"] ?? "");
                            ?>

                            <article class="subcard-detalle">
                                <h3>Prescripción <?php echo $indice + 1; ?></h3>

                                <div class="fila-detalle">
                                    <span>Medicamento</span>
                                    <strong><?php echo limpiarTexto(mostrarTexto($nombre_medicamento)); ?></strong>
                                </div>

                                <div class="fila-detalle">
                                    <span>Tipo</span>
                                    <strong><?php echo limpiarTexto(mostrarTexto($tipo_medicamento)); ?></strong>
                                </div>

                                <div class="fila-detalle">
                                    <span>Dosis</span>
                                    <strong><?php echo limpiarTexto(mostrarTexto($prescripcion["dosis"] ?? "")); ?></strong>
                                </div>

                                <div class="fila-detalle">
                                    <span>Frecuencia</span>
                                    <strong><?php echo limpiarTexto(mostrarTexto($prescripcion["frecuencia"] ?? "")); ?></strong>
                                </div>

                                <div class="fila-detalle">
                                    <span>Duración</span>
                                    <strong><?php echo limpiarTexto(mostrarTexto($duracion)); ?></strong>
                                </div>

                                <div class="bloque-texto-detalle">
                                    <span>Reacción adversa</span>
                                    <p><?php echo nl2br(limpiarTexto(mostrarTexto($prescripcion["reacción_adversa"] ?? ""))); ?></p>
                                </div>

                                <div class="bloque-texto-detalle">
                                    <span>Caso de resistencia</span>
                                    <p><?php echo nl2br(limpiarTexto(mostrarTexto($prescripcion["caso_resistencia"] ?? ""))); ?></p>
                                </div>

                                <?php if (!empty($medicamento)) : ?>
                                    <div class="bloque-dinamico">
                                        <h4>Datos completos del medicamento</h4>
                                        <?php renderizarCamposDinamicos($medicamento); ?>
                                    </div>
                                <?php endif; ?>
                            </article>

                        <?php endforeach; ?>

                    <?php endif; ?>
                </div>
            </section>

            <a href="<?php echo limpiarTexto($volver_panel); ?>" class="boton-principal boton-final">
                Volver al panel
            </a>

        <?php endif; ?>

    </main>

    <script src="js/detalle_consulta.js"></script>
</body>

</html>