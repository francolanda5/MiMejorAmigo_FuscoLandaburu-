<?php
/* ============================================
   turnos.php - SACAR TURNO
   ============================================ */

$fecha_actual = date("Y-m-d");
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Mejor Amigo - Sacar turno</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- CSS propio -->
    <link rel="stylesheet" href="css/turnos.css">
</head>

<body>

    <main class="pagina-turnos">

        <!-- ENCABEZADO -->
        <header class="encabezado-turnos">
            <a href="inicio.html" class="boton-volver" aria-label="Volver al inicio">‹</a>

            <div class="titulos-turnos">
                <p>Mi Mejor Amigo</p>
                <h1>Sacar turno</h1>
            </div>
        </header>

        <!-- PASO 1 -->
        <section id="paso-uno" class="seccion-paso">

            <article class="card-turno card-presentacion">
                <span class="etiqueta-paso">Paso 1</span>

                <h2>Datos del dueño</h2>

                <p>
                    Completá tus datos para buscar tus mascotas registradas o cargar una nueva.
                </p>
            </article>

            <form id="form-datos-persona" class="form-turno">

                <div class="campo-form">
                    <label for="nombre_apellido">Nombre y apellido</label>
                    <input type="text" id="nombre_apellido" name="nombre_apellido"
                        placeholder="Tu nombre completo" autocomplete="name" required>
                </div>

                <div class="campo-form">
                    <label for="dni_dueno">DNI</label>
                    <input type="number" id="dni_dueno" name="dni_dueno"
                        placeholder="Tu DNI" inputmode="numeric" required>
                </div>

                <div class="campo-form">
                    <label for="correo">Correo electrónico</label>
                    <input type="email" id="correo" name="correo"
                        placeholder="tu@email.com" autocomplete="email" required>
                </div>

                <div class="campo-form">
                    <label for="telefono">Teléfono</label>
                    <input type="tel" id="telefono" name="telefono"
                        placeholder="Tu teléfono" autocomplete="tel" required>
                </div>

                <p id="mensaje-error-datos" class="mensaje-error" aria-live="polite"></p>

                <button type="submit" class="boton-principal">
                    Continuar
                </button>

            </form>

        </section>

        <!-- PASO 2 -->
        <section id="paso-dos" class="seccion-paso" hidden>

            <article class="card-turno card-presentacion">
                <span class="etiqueta-paso">Paso 2</span>

                <h2>Reservá la consulta</h2>

                <p>
                    Elegí la mascota, el tipo de atención, profesional, fecha y horario.
                </p>
            </article>

            <form id="form-reserva-turno" class="form-turno">

                <!-- MASCOTA -->
                <section class="bloque-formulario">
                    <h2>Mascota</h2>

                    <fieldset id="selector-mascota" class="selector-mascota selector-sin-opciones">
                        <legend class="texto-oculto">Seleccioná una mascota</legend>

                        <button type="button" id="boton-mascota"
                            class="card-mascota boton-mascota-selector"
                            aria-expanded="false" disabled>

                            <span id="avatar-mascota-seleccionada" class="avatar-mascota-contenedor"></span>

                            <span class="contenido-mascota">
                                <span id="nombre-mascota-seleccionada" class="nombre-mascota">
                                    Seleccioná una mascota
                                </span>

                                <span id="raza-mascota-seleccionada" class="raza-mascota">
                                    Disponible luego de completar tus datos
                                </span>
                            </span>

                            <span id="flecha-mascota" class="flecha-card" aria-hidden="true" hidden>⌄</span>
                        </button>

                        <div id="lista-mascotas" class="lista-mascotas" hidden></div>

                        <input type="hidden" id="id_paciente" name="id_paciente" value="">
                    </fieldset>

                    <p id="mensaje-mascota" class="mensaje-info" aria-live="polite"></p>

                    <button type="button" id="boton-nueva-mascota" class="boton-secundario boton-nueva-mascota" hidden>
                        Agregar nueva mascota
                    </button>

                    <!-- NUEVA MASCOTA -->
                    <section id="panel-nueva-mascota" class="panel-nueva-mascota" hidden>
                        <h3 id="titulo-nueva-mascota">Nueva mascota</h3>

                        <p>
                            Cargá los datos de la mascota para continuar con la reserva del turno.
                        </p>

                        <div class="campo-form">
                            <label for="nombre_mascota_nueva">Nombre de la mascota</label>
                            <input type="text" id="nombre_mascota_nueva" name="nombre_mascota_nueva"
                                placeholder="Ej: Luna" disabled>
                        </div>

                        <div class="campo-form">
                            <label for="especie_mascota_nueva">Especie</label>
                            <select id="especie_mascota_nueva" name="especie_mascota_nueva" disabled>
                                <option value="">Seleccioná una especie</option>
                                <option value="Perro">Perro</option>
                                <option value="Gato">Gato</option>
                                <option value="Loro">Loro</option>
                                <option value="Conejo">Conejo</option>
                                <option value="Hámster">Hámster</option>
                                <option value="Tortuga">Tortuga</option>
                                <option value="Pez">Pez</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>

                        <div class="campo-form">
                            <label for="raza_mascota_nueva">Raza</label>
                            <input type="text" id="raza_mascota_nueva" name="raza_mascota_nueva"
                                placeholder="Ej: Mestizo" disabled>
                        </div>

                        <div class="campo-form">
                            <label for="edad_mascota_nueva">Edad</label>
                            <input type="number" id="edad_mascota_nueva" name="edad_mascota_nueva"
                                placeholder="Ej: 3" min="0" disabled>
                        </div>

                        <p id="mensaje-nueva-mascota" class="mensaje-error" aria-live="polite"></p>

                        <div class="acciones-nueva-mascota">
                            <button type="button" id="guardar-nueva-mascota" class="boton-principal">
                                Guardar mascota
                            </button>

                            <button type="button" id="cancelar-nueva-mascota" class="boton-secundario">
                                Cancelar
                            </button>
                        </div>
                    </section>
                </section>

                <!-- TIPO DE CONSULTA -->
                <section class="bloque-formulario">
                    <h2>Tipo de consulta</h2>

                    <fieldset class="opciones-consulta">
                        <legend class="texto-oculto">Elegí el tipo de consulta</legend>

                        <label class="card-opcion-consulta">
                            <input type="radio" name="tipo_consulta" value="consulta_general"
                                data-motivo="Consulta general" checked>
                            <span>Consulta general</span>
                        </label>

                        <label class="card-opcion-consulta">
                            <input type="radio" name="tipo_consulta" value="vacunacion"
                                data-motivo="Vacunación">
                            <span>Vacunación</span>
                        </label>

                        <label class="card-opcion-consulta">
                            <input type="radio" name="tipo_consulta" value="cirugias"
                                data-motivo="Cirugías">
                            <span>Cirugías</span>
                        </label>

                        <label class="card-opcion-consulta">
                            <input type="radio" name="tipo_consulta" value="analisis"
                                data-motivo="Análisis">
                            <span>Análisis</span>
                        </label>
                    </fieldset>

                    <input type="hidden" id="motivo_consulta" name="motivo_consulta" value="Consulta general">
                </section>

                <!-- PROFESIONAL -->
                <section class="bloque-formulario">
                    <h2>Profesional</h2>

                    <div class="campo-form">
                        <label for="matricula_profesional">Profesional disponible</label>

                        <select id="matricula_profesional" name="matricula_profesional" required>
                            <option value="">Cargando profesionales...</option>
                        </select>
                    </div>
                </section>

                <!-- FECHA -->
                <section class="bloque-formulario">
                    <h2>Fecha</h2>

                    <div class="campo-form">
                        <label for="fecha_turno">Elegí una fecha</label>
                        <input type="date" id="fecha_turno" name="fecha_turno"
                            value="<?php echo $fecha_actual; ?>" required>
                    </div>
                </section>

                <!-- HORARIOS -->
                <section class="bloque-formulario">
                    <h2>Horario</h2>

                    <fieldset class="card-horarios">
                        <legend class="texto-oculto">Elegí un horario</legend>
                    </fieldset>

                    <input type="hidden" id="horario_turno" name="horario_turno" value="">
                </section>

                <!-- OBSERVACIONES -->
                <section class="bloque-formulario">
                    <h2>Observaciones</h2>

                    <div class="campo-form">
                        <label for="observaciones">Comentarios opcionales</label>
                        <textarea id="observaciones" name="observaciones"
                            placeholder="Contanos si hay algo importante para tener en cuenta"></textarea>
                    </div>
                </section>

                <!-- RESUMEN -->
                <section class="card-resumen">
                    <h2>Resumen del turno</h2>

                    <div class="fila-resumen">
                        <span>Mascota</span>
                        <strong id="resumen-mascota">Seleccioná una mascota</strong>
                    </div>

                    <div class="fila-resumen">
                        <span>Consulta</span>
                        <strong id="resumen-consulta">Consulta general</strong>
                    </div>

                    <div class="fila-resumen">
                        <span>Profesional</span>
                        <strong id="resumen-profesional">Cargando...</strong>
                    </div>

                    <div class="fila-resumen">
                        <span>Fecha</span>
                        <strong id="resumen-fecha">Seleccioná una fecha</strong>
                    </div>

                    <div class="fila-resumen">
                        <span>Horario</span>
                        <strong id="resumen-horario">Sin horario</strong>
                    </div>
                </section>

                <p id="mensaje-error-turno" class="mensaje-error" aria-live="polite"></p>

                <button type="submit" class="boton-principal boton-confirmar">
                    Confirmar turno
                </button>

                <button type="button" id="volver-paso-uno" class="boton-secundario">
                    Volver
                </button>

            </form>

        </section>

        <!-- ÉXITO -->
        <section id="paso-exito" class="seccion-paso" hidden>

            <article class="card-turno card-exito">
                <h2>Turno confirmado</h2>

                <p>
                    La reserva se guardó correctamente. Te vamos a redirigir al inicio en unos segundos.
                </p>

                <p class="texto-mail-exito">
                    <span id="texto-mail-confirmacion">
                        Te enviamos la información del turno al correo indicado.
                    </span>
                    <strong id="mail-confirmacion" class="mail-confirmacion" hidden></strong>
                </p>

                <a href="inicio.html" class="boton-principal link-inicio">
                    Volver al inicio
                </a>
            </article>

        </section>

    </main>

    <script src="js/turnos.js"></script>
</body>

</html>