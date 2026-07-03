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

try {
    /* ============================================
       1. BUSCAR RANGO HORARIO DEL PROFESIONAL
       ============================================ */

    $consulta_horario = $conexion->prepare("
        SELECT 
            `horario_inicio`,
            `horario_final`
        FROM `horario de atención`
        WHERE `matricula_profesional` = :matricula_profesional
        LIMIT 1
    ");

    $consulta_horario->bindParam(":matricula_profesional", $matricula_profesional, PDO::PARAM_INT);
    $consulta_horario->execute();

    $horario = $consulta_horario->fetch(PDO::FETCH_ASSOC);

    if (!$horario) {
        echo json_encode([
            "exito" => false,
            "mensaje" => "El profesional no tiene horarios cargados."
        ]);
        exit;
    }

    /* ============================================
       2. HORARIOS BASE DEL SISTEMA
       ============================================ */

    $horarios_base = ["09:00", "10:30", "12:00", "15:30", "17:00"];

    $inicio = substr($horario["horario_inicio"], 0, 5);
    $final = substr($horario["horario_final"], 0, 5);

    $horarios_del_profesional = [];

    foreach ($horarios_base as $hora) {
        if ($hora >= $inicio && $hora <= $final) {
            $horarios_del_profesional[] = $hora;
        }
    }

    /* ============================================
       3. BUSCAR HORARIOS YA OCUPADOS
       ============================================ */

    $horarios_ocupados = [];

    if (!empty($fecha)) {
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

        foreach ($resultados_ocupados as $fila) {
            $horarios_ocupados[] = $fila["horario_ocupado"];
        }
    }

    /* ============================================
       4. DEVOLVER SOLO HORARIOS DISPONIBLES
       ============================================ */

    $horarios_disponibles = [];

    foreach ($horarios_del_profesional as $hora) {
        if (!in_array($hora, $horarios_ocupados)) {
            $horarios_disponibles[] = $hora;
        }
    }

    echo json_encode([
        "exito" => true,
        "horarios" => $horarios_disponibles
    ]);

} catch (PDOException $error) {
    echo json_encode([
        "exito" => false,
        "mensaje" => "No se pudieron obtener los horarios.",
        "detalle" => $error->getMessage()
    ]);
}
?>