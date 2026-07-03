<?php
/* ============================================
   actualizar_consulta.php - ACTUALIZAR CONSULTA
   ============================================ */

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
            `pago` = :pago
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
    $consulta->bindValue(":id_consulta", $id_consulta, PDO::PARAM_INT);

    $consulta->execute();

    header("Location: ../admin_consultas.php?estado=ok");
    exit;

} catch (PDOException $error) {
    header("Location: ../admin_consultas.php?estado=error");
    exit;
}
?>