<?php
/* ============================================
   obtener_mascotas.php - OBTENER MASCOTAS DEL DUEÑO
   ============================================ */

header("Content-Type: application/json; charset=utf-8");

require_once "conexion.php";
require_once "validaciones.php";

$correo = strtolower(textoRecibido($_GET["correo"] ?? ""));
$telefono = textoRecibido($_GET["telefono"] ?? "");
$dni = textoRecibido($_GET["dni"] ?? "");

if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || !esTelefonoValido($telefono) || !esDniValido($dni)) {
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
        WHERE `dueño`.`dni_dueño` = :dni
          AND `dueño`.`mail` = :correo
          AND `dueño`.`teléfono` = :telefono
        ORDER BY `mascota`.`nombre` ASC
    ");

    $consulta->bindParam(":dni", $dni);
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
