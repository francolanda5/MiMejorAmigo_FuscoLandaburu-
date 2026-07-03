<?php
/* ============================================
   procesar_login_profesional.php - VALIDAR LOGIN
   ============================================ */

session_start();

require_once "conexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../login_profesional.php?estado=error");
    exit;
}

$matricula_profesional = $_POST["matricula_profesional"] ?? "";
$clave_acceso = $_POST["clave_acceso"] ?? "";

$matricula_profesional = trim($matricula_profesional);
$clave_acceso = trim($clave_acceso);

if (empty($matricula_profesional) || empty($clave_acceso)) {
    header("Location: ../login_profesional.php?estado=error");
    exit;
}

try {
    $consulta = $conexion->prepare("
        SELECT
            `matricula_profesional`,
            `nombre`,
            `especialidad`
        FROM `veterinario`
        WHERE `matricula_profesional` = :matricula_profesional
          AND `clave_acceso` = :clave_acceso
        LIMIT 1
    ");

    $consulta->bindParam(":matricula_profesional", $matricula_profesional, PDO::PARAM_INT);
    $consulta->bindParam(":clave_acceso", $clave_acceso);

    $consulta->execute();

    $profesional = $consulta->fetch(PDO::FETCH_ASSOC);

    if (!$profesional) {
        header("Location: ../login_profesional.php?estado=error");
        exit;
    }

    session_regenerate_id(true);

    $_SESSION["profesional_logueado"] = true;
    $_SESSION["matricula_profesional"] = $profesional["matricula_profesional"];
    $_SESSION["nombre_profesional"] = $profesional["nombre"];
    $_SESSION["especialidad_profesional"] = $profesional["especialidad"];

    header("Location: ../admin_consultas.php");
    exit;

} catch (PDOException $error) {
    header("Location: ../login_profesional.php?estado=error");
    exit;
}
?>