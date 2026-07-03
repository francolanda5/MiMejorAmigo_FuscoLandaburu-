<?php
/* ============================================
   obtener_mascotas.php - OBTENER MASCOTAS DEL DUEÑO
   ============================================ */

header("Content-Type: application/json; charset=utf-8");

require_once "conexion.php";

$correo = $_GET["correo"] ?? "";
$telefono = $_GET["telefono"] ?? "";

if (empty($correo) && empty($telefono)) {
    echo json_encode([
        "exito" => false,
        "mensaje" => "Faltan datos para buscar las mascotas."
    ]);
    exit;
}

try {
    $consulta = $conexion->prepare("
        SELECT 
            `mascota`.`id_paciente`,
            `mascota`.`nombre`,
            `mascota`.`especie`,
            `mascota`.`raza`
        FROM `dueño`
        INNER JOIN `mascota`
            ON `dueño`.`dni_dueño` = `mascota`.`dni_dueño`
        WHERE `dueño`.`mail` = :correo
           OR `dueño`.`teléfono` = :telefono
        ORDER BY `mascota`.`nombre` ASC
    ");

    $consulta->bindParam(":correo", $correo);
    $consulta->bindParam(":telefono", $telefono);

    $consulta->execute();

    $mascotas = $consulta->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "exito" => true,
        "mascotas" => $mascotas
    ]);

} catch (PDOException $error) {
    echo json_encode([
        "exito" => false,
        "mensaje" => "No se pudieron obtener las mascotas.",
        "detalle" => $error->getMessage()
    ]);
}
?>