<?php
/*
 * Funciones de validación compartidas por los formularios PHP.
 * La validación del navegador ayuda al usuario, pero esta validación
 * vuelve a controlar los datos antes de consultar o modificar la BD.
 */

function textoRecibido($valor) {
    return trim((string)($valor ?? ""));
}

function esDniValido($dni) {
    return preg_match('/^[0-9]{7,8}$/', $dni) === 1;
}

function esTelefonoValido($telefono) {
    return preg_match('/^[0-9 +()-]{8,20}$/', $telefono) === 1;
}

function esFechaValida($fecha) {
    $fechaObjeto = DateTime::createFromFormat('!Y-m-d', $fecha);

    return $fechaObjeto !== false
        && $fechaObjeto->format('Y-m-d') === $fecha;
}

function esHorarioValido($horario) {
    return preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9](?::[0-5][0-9])?$/', $horario) === 1;
}

function esIdPositivo($id) {
    return filter_var($id, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]) !== false;
}
?>
