<?php
/* ============================================
   conexion.php - CONEXIÓN A BASE DE DATOS
   ============================================ */

$host = "localhost";
$base_datos = "mimejoramigo";
$usuario = "root";
$contrasena = "";

try {
    $conexion = new PDO(
        "mysql:host=$host;dbname=$base_datos;charset=utf8mb4",
        $usuario,
        $contrasena
    );

    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $error) {
    die("Error de conexión: " . $error->getMessage());
}
?>