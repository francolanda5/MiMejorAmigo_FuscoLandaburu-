<?php
/* ============================================
   actualizar_consulta.php - ACTUALIZAR CONSULTA
   ============================================ */

session_start();

if (!isset($_SESSION["profesional_logueado"]) || $_SESSION["profesional_logueado"] !== true) {
    header("Location: ../login_profesional.php?estado=sesion");
    exit;
}

require_once "conexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../admin_consultas.php?estado=error");
    exit;
}

/* ============================================
   1. RECIBIR DATOS DEL FORMULARIO
   ============================================ */

$id_consulta = $_POST["id_consulta"] ?? "";
$diagnostico = $_POST["diagnostico"] ?? "";
$tratamiento = $_POST["tratamiento"] ?? "";
$pago = $_POST["pago"] ?? "0";
$estado_consulta = $_POST["estado_consulta"] ?? "Pendiente";

/* ============================================
   2. VALIDAR DATOS BÁSICOS
   ============================================ */

if (empty($id_consulta)) {
    header("Location: ../admin_consultas.php?estado=error");
    exit;
}

if ($pago !== "0" && $pago !== "1") {
    $pago = "0";
}

$estados_permitidos = ["Pendiente", "Atendida", "Cancelada"];

if (!in_array($estado_consulta, $estados_permitidos)) {
    $estado_consulta = "Pendiente";
}

/* ============================================
   3. CONVERTIR CAMPOS VACÍOS A NULL
   ============================================ */

$diagnostico = trim($diagnostico);
$tratamiento = trim($tratamiento);

$diagnostico_sql = $diagnostico === "" ? null : $diagnostico;
$tratamiento_sql = $tratamiento === "" ? null : $tratamiento;

try {
    /* ============================================
       4. ACTUALIZAR CONSULTA
       ============================================ */

    $consulta = $conexion->prepare("
        UPDATE `consulta`
        SET
            `diagnostico` = :diagnostico,
            `tratamiento` = :tratamiento,
            `pago` = :pago,
            `estado` = :estado_consulta
        WHERE `id_consulta` = :id_consulta
    ");

    if ($diagnostico_sql === null) {
        $consulta->bindValue(":diagnostico", null, PDO::PARAM_NULL);
    } else {
        $consulta->bindValue(":diagnostico", $diagnostico_sql);
    }

    if ($tratamiento_sql === null) {
        $consulta->bindValue(":tratamiento", null, PDO::PARAM_NULL);
    } else {
        $consulta->bindValue(":tratamiento", $tratamiento_sql);
    }

    $consulta->bindValue(":pago", $pago, PDO::PARAM_INT);
    $consulta->bindValue(":estado_consulta", $estado_consulta);
    $consulta->bindValue(":id_consulta", $id_consulta, PDO::PARAM_INT);

    $consulta->execute();

    header("Location: ../admin_consultas.php?estado=ok");
    exit;

} catch (PDOException $error) {
    header("Location: ../admin_consultas.php?estado=error");
    exit;
}
?>