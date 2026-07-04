<?php
/* ============================================
   cancelar_consulta.php - CANCELAR TURNO
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
   1. RECIBIR ID DE CONSULTA
   ============================================ */

$id_consulta = $_POST["id_consulta"] ?? "";

if (empty($id_consulta) || !is_numeric($id_consulta)) {
    header("Location: ../admin_consultas.php?estado=error");
    exit;
}

try {
    /* ============================================
       2. ACTUALIZAR ESTADO A CANCELADA
       ============================================ */

    $consulta = $conexion->prepare("
        UPDATE `consulta`
        SET `estado` = 'Cancelada'
        WHERE `id_consulta` = :id_consulta
    ");

    $consulta->bindValue(":id_consulta", $id_consulta, PDO::PARAM_INT);
    $consulta->execute();

    header("Location: ../admin_consultas.php?estado=cancelada");
    exit;

} catch (PDOException $error) {
    header("Location: ../admin_consultas.php?estado=error");
    exit;
}
?>