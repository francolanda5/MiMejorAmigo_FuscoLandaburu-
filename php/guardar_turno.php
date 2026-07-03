<?php
/* ============================================
   guardar_turno.php - GUARDAR TURNO
   ============================================ */

header("Content-Type: application/json; charset=utf-8");

require_once "conexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "exito" => false,
        "mensaje" => "Método no permitido."
    ]);
    exit;
}

/* ============================================
   1. RECIBIR DATOS DEL FORMULARIO
   ============================================ */

$id_paciente = $_POST["id_paciente"] ?? "";
$fecha = $_POST["fecha_turno"] ?? "";
$horario = $_POST["horario_turno"] ?? "";
$motivo_consulta = $_POST["motivo_consulta"] ?? "";
$observaciones = $_POST["observaciones"] ?? "";
$matricula_profesional = $_POST["matricula_profesional"] ?? "";

/* ============================================
   2. VALIDAR DATOS OBLIGATORIOS
   ============================================ */

if (
    empty($id_paciente) ||
    empty($fecha) ||
    empty($horario) ||
    empty($motivo_consulta) ||
    empty($matricula_profesional)
) {
    echo json_encode([
        "exito" => false,
        "mensaje" => "Faltan datos obligatorios para guardar el turno."
    ]);
    exit;
}

/* ============================================
   3. PREPARAR DATOS PARA GUARDAR
   ============================================ */

if (!empty($observaciones)) {
    $motivo_consulta = $motivo_consulta . ". Observaciones: " . $observaciones;
}

/* 
   En la web el horario llega como 09:00.
   En SQL se guarda como TIME: 09:00:00.
*/
if (strlen($horario) === 5) {
    $horario = $horario . ":00";
}

try {

    /* ============================================
       4. VALIDAR QUE EL HORARIO NO ESTÉ OCUPADO
       ============================================ */

    $consulta_ocupado = $conexion->prepare("
        SELECT COUNT(*) AS cantidad
        FROM `consulta`
        WHERE `matricula_profesional` = :matricula_profesional
          AND `fecha` = :fecha
          AND `horario` = :horario
    ");

    $consulta_ocupado->bindParam(":matricula_profesional", $matricula_profesional, PDO::PARAM_INT);
    $consulta_ocupado->bindParam(":fecha", $fecha);
    $consulta_ocupado->bindParam(":horario", $horario);

    $consulta_ocupado->execute();

    $resultado_ocupado = $consulta_ocupado->fetch(PDO::FETCH_ASSOC);

    if ($resultado_ocupado["cantidad"] > 0) {
        echo json_encode([
            "exito" => false,
            "mensaje" => "Ese horario ya no está disponible. Elegí otro turno."
        ]);
        exit;
    }

    /* ============================================
       5. GUARDAR TURNO EN LA TABLA CONSULTA
       ============================================ */

    $consulta = $conexion->prepare("
        INSERT INTO `consulta`
        (
            `id_paciente`,
            `fecha`,
            `horario`,
            `motivo_consulta`,
            `diagnostico`,
            `tratamiento`,
            `pago`,
            `matricula_profesional`
        )
        VALUES
        (
            :id_paciente,
            :fecha,
            :horario,
            :motivo_consulta,
            NULL,
            NULL,
            0,
            :matricula_profesional
        )
    ");

    $consulta->bindParam(":id_paciente", $id_paciente, PDO::PARAM_INT);
    $consulta->bindParam(":fecha", $fecha);
    $consulta->bindParam(":horario", $horario);
    $consulta->bindParam(":motivo_consulta", $motivo_consulta);
    $consulta->bindParam(":matricula_profesional", $matricula_profesional, PDO::PARAM_INT);

    $consulta->execute();

    echo json_encode([
        "exito" => true,
        "mensaje" => "Turno guardado correctamente."
    ]);

} catch (PDOException $error) {
    echo json_encode([
        "exito" => false,
        "mensaje" => "No se pudo guardar el turno.",
        "detalle" => $error->getMessage()
    ]);
}
?>