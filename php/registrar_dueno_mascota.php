<?php
/* ============================================
   registrar_dueno_mascota.php - REGISTRAR DUEÑO Y MASCOTA
   ============================================ */

header("Content-Type: application/json; charset=utf-8");

require_once "conexion.php";

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

$nombre_dueno = trim($_POST["nombre_dueno"] ?? "");
$dni_dueno = trim($_POST["dni_dueno"] ?? "");
$correo = trim($_POST["correo"] ?? "");
$telefono = trim($_POST["telefono"] ?? "");

$nombre_mascota = trim($_POST["nombre_mascota"] ?? "");
$especie = trim($_POST["especie"] ?? "");
$raza = trim($_POST["raza"] ?? "");
$edad = trim($_POST["edad"] ?? "");

/* ============================================
   2. VALIDAR DATOS OBLIGATORIOS
   ============================================ */

if (
    empty($nombre_dueno) ||
    empty($dni_dueno) ||
    empty($correo) ||
    empty($telefono) ||
    empty($nombre_mascota) ||
    empty($especie) ||
    empty($raza) ||
    $edad === ""
) {
    echo json_encode([
        "exito" => false,
        "mensaje" => "Faltan datos obligatorios para registrar la mascota."
    ]);
    exit;
}

if (!is_numeric($dni_dueno)) {
    echo json_encode([
        "exito" => false,
        "mensaje" => "El DNI debe contener solo números."
    ]);
    exit;
}

if (!is_numeric($edad) || (int)$edad < 0) {
    echo json_encode([
        "exito" => false,
        "mensaje" => "La edad de la mascota no es válida."
    ]);
    exit;
}

/* ============================================
   3. REGISTRAR DUEÑO SI NO EXISTE Y CREAR MASCOTA
   ============================================ */

try {
    $conexion->beginTransaction();

    /* ============================================
       3.1 BUSCAR SI EL DUEÑO YA EXISTE
       ============================================ */

    $buscar_dueno = $conexion->prepare("
        SELECT 
            `dni_dueño`,
            `nombre`,
            `mail`,
            `teléfono`
        FROM `dueño`
        WHERE `dni_dueño` = :dni_dueno
           OR `mail` = :correo
           OR `teléfono` = :telefono
        LIMIT 1
    ");

    $buscar_dueno->bindParam(":dni_dueno", $dni_dueno);
    $buscar_dueno->bindParam(":correo", $correo);
    $buscar_dueno->bindParam(":telefono", $telefono);

    $buscar_dueno->execute();

    $dueno_existente = $buscar_dueno->fetch(PDO::FETCH_ASSOC);

    if ($dueno_existente) {
        $dni_para_mascota = $dueno_existente["dni_dueño"];
        $dueno_fue_creado = false;
    } else {
        /* ============================================
           3.2 CREAR DUEÑO NUEVO
           ============================================ */

        $calle = "";
        $altura = 0;
        $barrio = "";

        $insertar_dueno = $conexion->prepare("
            INSERT INTO `dueño`
            (
                `dni_dueño`,
                `nombre`,
                `mail`,
                `teléfono`,
                `calle`,
                `altura`,
                `barrio`
            )
            VALUES
            (
                :dni_dueno,
                :nombre,
                :correo,
                :telefono,
                :calle,
                :altura,
                :barrio
            )
        ");

        $insertar_dueno->bindParam(":dni_dueno", $dni_dueno);
        $insertar_dueno->bindParam(":nombre", $nombre_dueno);
        $insertar_dueno->bindParam(":correo", $correo);
        $insertar_dueno->bindParam(":telefono", $telefono);
        $insertar_dueno->bindParam(":calle", $calle);
        $insertar_dueno->bindParam(":altura", $altura);
        $insertar_dueno->bindParam(":barrio", $barrio);

        $insertar_dueno->execute();

        $dni_para_mascota = $dni_dueno;
        $dueno_fue_creado = true;
    }

    /* ============================================
       3.3 CREAR MASCOTA NUEVA
       ============================================ */

    $insertar_mascota = $conexion->prepare("
        INSERT INTO `mascota`
        (
            `dni_dueño`,
            `nombre`,
            `especie`,
            `raza`,
            `edad`
        )
        VALUES
        (
            :dni_dueno,
            :nombre,
            :especie,
            :raza,
            :edad
        )
    ");

    $insertar_mascota->bindParam(":dni_dueno", $dni_para_mascota);
    $insertar_mascota->bindParam(":nombre", $nombre_mascota);
    $insertar_mascota->bindParam(":especie", $especie);
    $insertar_mascota->bindParam(":raza", $raza);
    $insertar_mascota->bindParam(":edad", $edad, PDO::PARAM_INT);

    $insertar_mascota->execute();

    $id_paciente = $conexion->lastInsertId();

    if (empty($id_paciente) || $id_paciente === "0") {
        $buscar_mascota = $conexion->prepare("
            SELECT `id_paciente`
            FROM `mascota`
            WHERE `dni_dueño` = :dni_dueno
              AND `nombre` = :nombre
            ORDER BY `id_paciente` DESC
            LIMIT 1
        ");

        $buscar_mascota->bindParam(":dni_dueno", $dni_para_mascota);
        $buscar_mascota->bindParam(":nombre", $nombre_mascota);
        $buscar_mascota->execute();

        $mascota_encontrada = $buscar_mascota->fetch(PDO::FETCH_ASSOC);
        $id_paciente = $mascota_encontrada["id_paciente"] ?? "";
    }

    if (empty($id_paciente)) {
        $conexion->rollBack();

        echo json_encode([
            "exito" => false,
            "mensaje" => "La mascota se creó, pero no se pudo recuperar su identificador."
        ]);
        exit;
    }

    $conexion->commit();

    echo json_encode([
        "exito" => true,
        "mensaje" => "Mascota registrada correctamente.",
        "dueno_creado" => $dueno_fue_creado,
        "dni_dueno" => $dni_para_mascota,
        "mascota" => [
            "id_paciente" => $id_paciente,
            "nombre" => $nombre_mascota,
            "especie" => $especie,
            "raza" => $raza,
            "edad" => $edad
        ]
    ]);

} catch (PDOException $error) {
    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }

    echo json_encode([
        "exito" => false,
        "mensaje" => "No se pudo registrar la mascota.",
        "detalle" => $error->getMessage()
    ]);
}
?>