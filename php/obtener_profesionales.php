<?php
/* ============================================
   obtener_profesionales.php - OBTENER PROFESIONALES
   ============================================ */

header("Content-Type: application/json; charset=utf-8");

require_once "conexion.php";

$tipo_consulta = $_GET["tipo"] ?? "consulta_general";

$especialidades = [];

if ($tipo_consulta === "consulta_general") {
    $especialidades = ["Clínica general"];
}

if ($tipo_consulta === "vacunacion") {
    $especialidades = ["Medicina preventiva", "Vacunación"];
}

if ($tipo_consulta === "cirugias") {
    $especialidades = ["Cirugía veterinaria"];
}

if ($tipo_consulta === "analisis") {
    $especialidades = ["Laboratorio", "Análisis clínicos"];
}

if (empty($especialidades)) {
    echo json_encode([
        "exito" => false,
        "mensaje" => "Tipo de consulta no válido."
    ]);
    exit;
}

try {
    $marcadores = implode(",", array_fill(0, count($especialidades), "?"));

    $consulta = $conexion->prepare("
        SELECT 
            `matricula_profesional`,
            `nombre`,
            `especialidad`
        FROM `veterinario`
        WHERE `especialidad` IN ($marcadores)
        ORDER BY `nombre` ASC
    ");

    $consulta->execute($especialidades);

    $profesionales = $consulta->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "exito" => true,
        "profesionales" => $profesionales
    ]);

} catch (PDOException $error) {
    echo json_encode([
        "exito" => false,
        "mensaje" => "No se pudieron obtener los profesionales.",
        "detalle" => $error->getMessage()
    ]);
}
?>