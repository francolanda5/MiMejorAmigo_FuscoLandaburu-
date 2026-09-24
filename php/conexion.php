<?php
/* ============================================
   conexion.php - CONEXIÓN A BASE DE DATOS
   Local XAMPP + Hosting InfinityFree
   ============================================ */

if ($_SERVER["HTTP_HOST"] === "localhost" || $_SERVER["HTTP_HOST"] === "127.0.0.1") {

    /* ============================================
       DATOS PARA XAMPP LOCAL
       ============================================ */

    $host = "localhost";
    $base_datos = "mi-mejor-amigo";
    $usuario = "root";
    $contrasena = "";

} else {

    /* ============================================
       DATOS PARA INFINITYFREE
       ============================================ */

    $host = "sql301.infinityfree.com";
    $base_datos = "if0_42366136_mimejoramigo";
    $usuario = "if0_42366136";
    $contrasena = "VetWeb2026";
}

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
