<?php
/* ============================================
   obtener_horarios.php - OBTENER HORARIOS DISPONIBLES
   ============================================ */

header("Content-Type: application/json; charset=utf-8");

require_once "conexion.php";

$matricula_profesional = $_GET["matricula"] ?? "";
$fecha = $_GET["fecha"] ?? "";

if (empty($matricula_profesional)) {
    echo json_encode([
        "exito" => false,
        "mensaje" => "Falta la matrícula del profesional."
    ]);
    exit;
}

if (empty($fecha)) {
    echo json_encode([
        "exito" => false,
        "mensaje" => "Falta la fecha del turno."
    ]);
    exit;
}

$timestamp_fecha = strtotime($fecha);

if ($timestamp_fecha === false) {
    echo json_encode([
        "exito" => false,
        "mensaje" => "La fecha seleccionada no es válida."
    ]);
    exit;
}

/* 
   date('N') devuelve:
   1 lunes, 2 martes, 3 miércoles, 4 jueves,
   5 viernes, 6 sábado, 7 domingo.
*/
$dia_semana = date("N", $timestamp_fecha);

try {
    /* ============================================
       1. BUSCAR HORARIO DEL PROFESIONAL SEGÚN DÍA
       ============================================ */

    $consulta_horario = $conexion->prepare("
        SELECT 
            `horario_inicio`,
            `horario_final`
        FROM `horario de atención`
        WHERE `matricula_profesional` = :matricula_profesional
          AND `dia_semana` = :dia_semana
        LIMIT 1
    ");

    $consulta_horario->bindParam(":matricula_profesional", $matricula_profesional, PDO::PARAM_INT);
    $consulta_horario->bindParam(":dia_semana", $dia_semana, PDO::PARAM_INT);
    $consulta_horario->execute();

    $horario = $consulta_horario->fetch(PDO::FETCH_ASSOC);

    if (!$horario) {
        echo json_encode([
            "exito" => true,
            "horarios" => [],
            "mensaje" => "Sin atención ese día."
        ]);
        exit;
    }

    /* ============================================
       2. GENERAR HORARIOS AUTOMÁTICAMENTE
       ============================================ */

    $hora_inicio = substr($horario["horario_inicio"], 0, 5);
    $hora_final = substr($horario["horario_final"], 0, 5);

    $horarios_generados = [];

    $hora_actual = new DateTime($hora_inicio);
    $hora_limite = new DateTime($hora_final);

    /*
       Intervalo simple y defendible:
       cada 90 minutos.
       Ejemplo sábado:
       09:00, 10:30, 12:00, 13:30
    */
    $intervalo_minutos = 90;

    while ($hora_actual < $hora_limite) {
        $horarios_generados[] = $hora_actual->format("H:i");
        $hora_actual->modify("+" . $intervalo_minutos . " minutes");
    }

    /* ============================================
       3. BUSCAR HORARIOS YA OCUPADOS
       ============================================ */

    $consulta_ocupados = $conexion->prepare("
        SELECT 
            TIME_FORMAT(`horario`, '%H:%i') AS horario_ocupado
        FROM `consulta`
        WHERE `matricula_profesional` = :matricula_profesional
          AND `fecha` = :fecha
    ");

    $consulta_ocupados->bindParam(":matricula_profesional", $matricula_profesional, PDO::PARAM_INT);
    $consulta_ocupados->bindParam(":fecha", $fecha);
    $consulta_ocupados->execute();

    $resultados_ocupados = $consulta_ocupados->fetchAll(PDO::FETCH_ASSOC);

    $horarios_ocupados = [];

    foreach ($resultados_ocupados as $fila) {
        $horarios_ocupados[] = $fila["horario_ocupado"];
    }

    /* ============================================
       4. DEVOLVER SOLO HORARIOS LIBRES
       ============================================ */

    $horarios_disponibles = [];

    foreach ($horarios_generados as $hora) {
        if (!in_array($hora, $horarios_ocupados)) {
            $horarios_disponibles[] = $hora;
        }
    }

    if (empty($horarios_disponibles)) {
        echo json_encode([
            "exito" => true,
            "horarios" => [],
            "mensaje" => "Sin horarios disponibles."
        ]);
        exit;
    }

    echo json_encode([
        "exito" => true,
        "horarios" => $horarios_disponibles,
        "dia_semana" => $dia_semana,
        "horario_inicio" => $hora_inicio,
        "horario_final" => $hora_final
    ]);

} catch (PDOException $error) {
    echo json_encode([
        "exito" => false,
        "mensaje" => "No se pudieron obtener los horarios.",
        "detalle" => $error->getMessage()
    ]);
}
?>