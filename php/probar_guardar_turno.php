<?php
/* ============================================
   probar_guardar_turno.php - PRUEBA DE GUARDADO
   ============================================ */
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Probar guardado de turno</title>
</head>
<body>

    <h1>Probar guardar turno</h1>

    <form action="guardar_turno.php" method="POST">

        <input type="hidden" name="id_paciente" value="1">
        <input type="hidden" name="fecha_turno" value="2026-05-20">
        <input type="hidden" name="horario_turno" value="09:00">
        <input type="hidden" name="motivo_consulta" value="Consulta general">
        <input type="hidden" name="observaciones" value="Prueba desde PHP">
        <input type="hidden" name="matricula_profesional" value="378892">

        <button type="submit">Guardar turno de prueba</button>

    </form>

</body>
</html>