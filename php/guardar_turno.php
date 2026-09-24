<?php
/* ============================================
   guardar_turno.php - GUARDAR TURNO
   ============================================ */

header("Content-Type: application/json; charset=utf-8");

require_once "conexion.php";
require_once "validaciones.php";

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

$id_paciente = textoRecibido($_POST["id_paciente"] ?? "");
$fecha = textoRecibido($_POST["fecha_turno"] ?? "");
$horario = textoRecibido($_POST["horario_turno"] ?? "");
$motivo_consulta = textoRecibido($_POST["motivo_consulta"] ?? "");
$observaciones = textoRecibido($_POST["observaciones"] ?? "");
$matricula_profesional = textoRecibido($_POST["matricula_profesional"] ?? "");

/* ============================================
   2. VALIDAR DATOS OBLIGATORIOS
   ============================================ */

if (
    !esIdPositivo($id_paciente) ||
    !esFechaValida($fecha) ||
    !esHorarioValido($horario) ||
    $motivo_consulta === "" ||
    !esIdPositivo($matricula_profesional)
) {
    echo json_encode([
        "exito" => false,
        "mensaje" => "Faltan datos obligatorios para guardar el turno."
    ]);
    exit;
}

$motivos_permitidos = ["Consulta general", "Vacunación", "Cirugías", "Análisis"];

if (!in_array($motivo_consulta, $motivos_permitidos, true) || $fecha < date("Y-m-d")) {
    echo json_encode([
        "exito" => false,
        "mensaje" => "La fecha o el tipo de consulta no son válidos."
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
