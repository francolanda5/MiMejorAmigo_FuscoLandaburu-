/* ============================================
   turnos.js - FLUJO DE RESERVA DE TURNO
   ============================================ */

document.addEventListener("DOMContentLoaded", function () {
  const pasoUno = document.getElementById("paso-uno");
  const pasoDos = document.getElementById("paso-dos");
  const pasoExito = document.getElementById("paso-exito");

  const formDatosPersona = document.getElementById("form-datos-persona");
  const formReservaTurno = document.getElementById("form-reserva-turno");

  const botonVolverPasoUno = document.getElementById("volver-paso-uno");

  const campoNombre = document.getElementById("nombre_apellido");
  const campoDni = document.getElementById("dni_dueno");
  const campoCorreo = document.getElementById("correo");
  const campoTelefono = document.getElementById("telefono");

  const textoMailConfirmacion = document.getElementById("texto-mail-confirmacion");
  const mailConfirmacion = document.getElementById("mail-confirmacion");

  const mensajeErrorDatos = document.getElementById("mensaje-error-datos");
  const mensajeMascota = document.getElementById("mensaje-mascota");
  const mensajeErrorTurno = document.getElementById("mensaje-error-turno");

  const selectorMascota = document.getElementById("selector-mascota");
  const botonMascota = document.getElementById("boton-mascota");
  const flechaMascota = document.getElementById("flecha-mascota");
  const listaMascotas = document.getElementById("lista-mascotas");
  const campoIdPaciente = document.getElementById("id_paciente");

  const avatarMascotaSeleccionada = document.getElementById("avatar-mascota-seleccionada");
  const nombreMascotaSeleccionada = document.getElementById("nombre-mascota-seleccionada");
  const razaMascotaSeleccionada = document.getElementById("raza-mascota-seleccionada");
  const resumenMascota = document.getElementById("resumen-mascota");

  const botonNuevaMascota = document.getElementById("boton-nueva-mascota");
  const panelNuevaMascota = document.getElementById("panel-nueva-mascota");
  const tituloNuevaMascota = document.getElementById("titulo-nueva-mascota");
  const campoNombreMascotaNueva = document.getElementById("nombre_mascota_nueva");
  const campoEspecieMascotaNueva = document.getElementById("especie_mascota_nueva");
  const campoRazaMascotaNueva = document.getElementById("raza_mascota_nueva");
  const campoEdadMascotaNueva = document.getElementById("edad_mascota_nueva");
  const mensajeNuevaMascota = document.getElementById("mensaje-nueva-mascota");
  const botonGuardarNuevaMascota = document.getElementById("guardar-nueva-mascota");
  const botonCancelarNuevaMascota = document.getElementById("cancelar-nueva-mascota");

  const opcionesConsulta = document.querySelectorAll('input[name="tipo_consulta"]');
  const campoMotivoConsulta = document.getElementById("motivo_consulta");
  const resumenConsulta = document.getElementById("resumen-consulta");

  const campoProfesional = document.getElementById("matricula_profesional");
  const resumenProfesional = document.getElementById("resumen-profesional");

  const contenedorHorarios = document.querySelector(".card-horarios");
  const campoHorario = document.getElementById("horario_turno");
  const resumenHorario = document.getElementById("resumen-horario");

  const campoFecha = document.getElementById("fecha_turno");
  const resumenFecha = document.getElementById("resumen-fecha");

  let redireccionInicio = null;
  let mascotasDisponibles = [];
  let mascotaSeleccionadaActual = null;
  let solicitudProfesionalesActual = 0;
  let solicitudHorariosActual = 0;

  /* ============================================
     FUNCIONES DE PANTALLAS
     ============================================ */

  function limpiarRedireccion() {
    if (redireccionInicio !== null) {
      clearTimeout(redireccionInicio);
      redireccionInicio = null;
    }
  }

  function actualizarMailConfirmacion() {
    const correo = campoCorreo.value.trim();

    if (!textoMailConfirmacion || !mailConfirmacion) {
      return;
    }

    if (correo === "") {
      textoMailConfirmacion.textContent = "Te enviamos la información del turno al correo indicado.";
      mailConfirmacion.textContent = "";
      mailConfirmacion.hidden = true;
      return;
    }

    textoMailConfirmacion.textContent = "Te enviamos la información del turno a:";
    mailConfirmacion.textContent = correo;
    mailConfirmacion.hidden = false;
  }

  function mostrarPasoUno() {
    limpiarRedireccion();

    pasoUno.hidden = false;
    pasoDos.hidden = true;
    pasoExito.hidden = true;

    cerrarDesplegableMascotas();
    ocultarFormularioNuevaMascota();

    window.scrollTo(0, 0);
  }

  function mostrarPasoDos() {
    limpiarRedireccion();

    pasoUno.hidden = true;
    pasoDos.hidden = false;
    pasoExito.hidden = true;

    cerrarDesplegableMascotas();

    window.scrollTo(0, 0);
  }

  function mostrarPasoExito() {
    pasoUno.hidden = true;
    pasoDos.hidden = true;
    pasoExito.hidden = false;

    actualizarMailConfirmacion();

    cerrarDesplegableMascotas();
    ocultarFormularioNuevaMascota();

    window.scrollTo(0, 0);

    redireccionInicio = setTimeout(function () {
      window.location.href = "inicio.html";
    }, 4500);
  }

  function resetearFlujoInicial() {
    limpiarRedireccion();

    pasoUno.hidden = false;
    pasoDos.hidden = true;
    pasoExito.hidden = true;

    mensajeErrorDatos.textContent = "";
    mensajeMascota.textContent = "";

    if (mensajeErrorTurno) {
      mensajeErrorTurno.textContent = "";
    }

    formDatosPersona.reset();
    formReservaTurno.reset();

    limpiarMascotas();
    ocultarFormularioNuevaMascota();
    mostrarEstadoHorario("Sin horario");
    actualizarMailConfirmacion();

    actualizarConsultaSeleccionada();
    actualizarResumenFecha();

    window.scrollTo(0, 0);
  }

  /* ============================================
     VALIDACIÓN PASO 1
     ============================================ */

  function validarDatosPersona() {
    const nombre = campoNombre.value.trim();
    const dni = campoDni.value.trim();
    const correo = campoCorreo.value.trim();
    const telefono = campoTelefono.value.trim();

    if (nombre === "" || dni === "" || correo === "" || telefono === "") {
      mensajeErrorDatos.textContent = "Completá nombre, DNI, correo y teléfono para continuar.";
      return false;
    }

    if (dni.length < 6) {
      mensajeErrorDatos.textContent = "Ingresá un DNI válido.";
      return false;
    }

    if (!correo.includes("@")) {
      mensajeErrorDatos.textContent = "Ingresá un correo electrónico válido.";
      return false;
    }

    mensajeErrorDatos.textContent = "";
    return true;
  }

  /* ============================================
     MASCOTAS DESDE SQL
     ============================================ */

  function limpiarMascotas() {
    mascotasDisponibles = [];
    mascotaSeleccionadaActual = null;

    listaMascotas.innerHTML = "";
    listaMascotas.hidden = true;
    campoIdPaciente.value = "";

    botonMascota.disabled = true;
    botonMascota.classList.remove("mascota-activa");
    botonMascota.setAttribute("aria-expanded", "false");
    selectorMascota.classList.add("selector-sin-opciones");

    if (flechaMascota) {
      flechaMascota.hidden = true;
    }

    avatarMascotaSeleccionada.innerHTML = "";
    nombreMascotaSeleccionada.textContent = "Seleccioná una mascota";
    razaMascotaSeleccionada.textContent = "Disponible luego de completar tus datos";
    resumenMascota.textContent = "Seleccioná una mascota";

    botonNuevaMascota.hidden = true;
    botonNuevaMascota.textContent = "Agregar nueva mascota";
  }

  function obtenerMascotasDesdeBase() {
    const correo = encodeURIComponent(campoCorreo.value.trim());
    const telefono = encodeURIComponent(campoTelefono.value.trim());
    const dni = encodeURIComponent(campoDni.value.trim());

    return fetch(
      "php/obtener_mascotas.php?correo=" +
      correo +
      "&telefono=" +
      telefono +
      "&dni=" +
      dni
    )
      .then(function (respuesta) {
        return respuesta.json();
      });
  }

  function cargarMascotasDelDueno() {
    limpiarMascotas();
    ocultarFormularioNuevaMascota();

    return obtenerMascotasDesdeBase()
      .then(function (datos) {
        if (!datos.exito || !Array.isArray(datos.mascotas)) {
          mensajeErrorDatos.textContent = "No se pudieron cargar las mascotas.";
          return false;
        }

        if (datos.mascotas.length === 0) {
          mensajeMascota.textContent =
            "No encontramos mascotas asociadas a esos datos. Podés registrar una nueva mascota para continuar con la reserva.";

          botonNuevaMascota.hidden = false;
          botonNuevaMascota.textContent = "Registrar nueva mascota";

          return true;
        }

        mascotasDisponibles = datos.mascotas.map(function (mascota) {
          return {
            id: mascota.id_paciente,
            nombre: mascota.nombre,
            especie: mascota.especie,
            raza: mascota.raza,
            edad: mascota.edad,
            imagen: ""
          };
        });

        crearOpcionesMascotas();

        mensajeMascota.textContent = "Seleccioná una mascota o agregá una nueva.";
        botonNuevaMascota.hidden = false;
        botonNuevaMascota.textContent = "Agregar nueva mascota";

        return true;
      })
      .catch(function (error) {
        mensajeErrorDatos.textContent = "No se pudieron cargar las mascotas. Revisá la conexión.";
        console.log(error);
        return false;
      });
  }

  function crearAvatarMascota(mascota) {
    if (mascota.imagen && mascota.imagen !== "") {
      const imagen = document.createElement("img");

      imagen.src = mascota.imagen;
      imagen.alt = mascota.nombre + " - " + mascota.raza;
      imagen.className = "avatar-mascota";

      return imagen;
    }

    const avatarVacio = document.createElement("span");

    avatarVacio.className = "avatar-mascota avatar-mascota-vacio";
    avatarVacio.setAttribute("aria-hidden", "true");

    return avatarVacio;
  }

  function crearOpcionesMascotas() {
    listaMascotas.innerHTML = "";

    mascotasDisponibles.forEach(function (mascota) {
      const opcion = document.createElement("button");

      opcion.type = "button";
      opcion.className = "card-mascota card-mascota-opcion";
      opcion.dataset.idMascota = mascota.id;

      const avatar = crearAvatarMascota(mascota);

      const contenido = document.createElement("span");
      contenido.className = "contenido-mascota";

      const nombre = document.createElement("span");
      nombre.className = "nombre-mascota";
      nombre.textContent = mascota.nombre + " - " + mascota.raza;

      const raza = document.createElement("span");
      raza.className = "raza-mascota";

      if (mascota.especie) {
        raza.textContent = mascota.especie + " · Tocar para seleccionar";
      } else {
        raza.textContent = "Tocar para seleccionar";
      }

      contenido.appendChild(nombre);
      contenido.appendChild(raza);

      opcion.appendChild(avatar);
      opcion.appendChild(contenido);

      opcion.addEventListener("click", function () {
        seleccionarMascota(mascota.id);
        cerrarDesplegableMascotas();
      });

      listaMascotas.appendChild(opcion);
    });

    if (mascotasDisponibles.length > 0) {
      seleccionarMascota(mascotasDisponibles[0].id);

      const puedeDesplegarMascotas = mascotasDisponibles.length > 1;

      botonMascota.disabled = !puedeDesplegarMascotas;

      if (flechaMascota) {
        flechaMascota.hidden = !puedeDesplegarMascotas;
      }

      if (puedeDesplegarMascotas) {
        selectorMascota.classList.remove("selector-sin-opciones");
      } else {
        selectorMascota.classList.add("selector-sin-opciones");
      }
    }
  }

  function seleccionarMascota(idMascota) {
    const mascota = mascotasDisponibles.find(function (item) {
      return String(item.id) === String(idMascota);
    });

    if (!mascota) {
      return;
    }

    mascotaSeleccionadaActual = mascota;
    campoIdPaciente.value = mascota.id;

    avatarMascotaSeleccionada.innerHTML = "";
    avatarMascotaSeleccionada.appendChild(crearAvatarMascota(mascota));

    nombreMascotaSeleccionada.textContent = mascota.nombre + " - " + mascota.raza;
    razaMascotaSeleccionada.textContent = "Mascota seleccionada";

    resumenMascota.textContent = mascota.nombre + " - " + mascota.raza;

    actualizarEstadoOpcionesMascota();
  }

  function actualizarEstadoOpcionesMascota() {
    const opcionesMascota = document.querySelectorAll(".card-mascota-opcion");

    opcionesMascota.forEach(function (opcion) {
      if (String(opcion.dataset.idMascota) === String(campoIdPaciente.value)) {
        opcion.classList.add("mascota-activa");
      } else {
        opcion.classList.remove("mascota-activa");
      }
    });

    if (campoIdPaciente.value !== "") {
      botonMascota.classList.add("mascota-activa");
    } else {
      botonMascota.classList.remove("mascota-activa");
    }
  }

  function abrirCerrarMascotas() {
    if (botonMascota.disabled) {
      return;
    }

    const estaAbierto = !listaMascotas.hidden;

    listaMascotas.hidden = estaAbierto;
    botonMascota.setAttribute("aria-expanded", String(!estaAbierto));
  }

  function cerrarDesplegableMascotas() {
    listaMascotas.hidden = true;
    botonMascota.setAttribute("aria-expanded", "false");
  }

  /* ============================================
     NUEVA MASCOTA
     ============================================ */

  function activarCamposNuevaMascota(activar) {
    campoNombreMascotaNueva.disabled = !activar;
    campoEspecieMascotaNueva.disabled = !activar;
    campoRazaMascotaNueva.disabled = !activar;
    campoEdadMascotaNueva.disabled = !activar;
  }

  function mostrarFormularioNuevaMascota() {
    panelNuevaMascota.hidden = false;
    activarCamposNuevaMascota(true);

    if (mascotasDisponibles.length === 0) {
      tituloNuevaMascota.textContent = "Registrar nueva mascota";
    } else {
      tituloNuevaMascota.textContent = "Agregar nueva mascota";
    }

    mensajeNuevaMascota.textContent = "";

    setTimeout(function () {
      panelNuevaMascota.scrollIntoView({
        behavior: "smooth",
        block: "start"
      });
    }, 100);
  }

  function ocultarFormularioNuevaMascota() {
    panelNuevaMascota.hidden = true;
    activarCamposNuevaMascota(false);

    campoNombreMascotaNueva.value = "";
    campoEspecieMascotaNueva.value = "";
    campoRazaMascotaNueva.value = "";
    campoEdadMascotaNueva.value = "";

    mensajeNuevaMascota.textContent = "";
  }

  function validarFormularioNuevaMascota() {
    const nombreMascota = campoNombreMascotaNueva.value.trim();
    const especieMascota = campoEspecieMascotaNueva.value.trim();
    const razaMascota = campoRazaMascotaNueva.value.trim();
    const edadMascota = campoEdadMascotaNueva.value.trim();

    if (nombreMascota === "" || especieMascota === "" || razaMascota === "" || edadMascota === "") {
      mensajeNuevaMascota.textContent = "Completá nombre, especie, raza y edad de la mascota.";
      return false;
    }

    if (Number(edadMascota) < 0) {
      mensajeNuevaMascota.textContent = "Ingresá una edad válida.";
      return false;
    }

    mensajeNuevaMascota.textContent = "";
    return true;
  }

  function registrarDuenoMascotaEnBase() {
    const datos = new FormData();

    datos.append("nombre_dueno", campoNombre.value.trim());
    datos.append("dni_dueno", campoDni.value.trim());
    datos.append("correo", campoCorreo.value.trim());
    datos.append("telefono", campoTelefono.value.trim());

    datos.append("nombre_mascota", campoNombreMascotaNueva.value.trim());
    datos.append("especie", campoEspecieMascotaNueva.value.trim());
    datos.append("raza", campoRazaMascotaNueva.value.trim());
    datos.append("edad", campoEdadMascotaNueva.value.trim());

    return fetch("php/registrar_dueno_mascota.php", {
      method: "POST",
      body: datos
    })
      .then(function (respuesta) {
        return respuesta.json();
      });
  }

  function agregarMascotaNuevaAlFlujo(mascotaNueva) {
    const mascota = {
      id: mascotaNueva.id_paciente,
      nombre: mascotaNueva.nombre,
      especie: mascotaNueva.especie,
      raza: mascotaNueva.raza,
      edad: mascotaNueva.edad,
      imagen: ""
    };

    const yaExisteEnListado = mascotasDisponibles.some(function (item) {
      return String(item.id) === String(mascota.id);
    });

    if (!yaExisteEnListado) {
      mascotasDisponibles.push(mascota);
    }

    crearOpcionesMascotas();
    seleccionarMascota(mascota.id);

    mensajeMascota.textContent = "Mascota registrada correctamente. Ya podés continuar con la reserva.";

    botonNuevaMascota.hidden = false;
    botonNuevaMascota.textContent = "Agregar nueva mascota";

    ocultarFormularioNuevaMascota();
  }

  function guardarNuevaMascota() {
    if (!validarDatosPersona()) {
      mensajeNuevaMascota.textContent = "Revisá los datos del dueño.";
      return;
    }

    if (!validarFormularioNuevaMascota()) {
      return;
    }

    mensajeNuevaMascota.textContent = "Guardando mascota...";
    botonGuardarNuevaMascota.disabled = true;

    registrarDuenoMascotaEnBase()
      .then(function (respuesta) {
        botonGuardarNuevaMascota.disabled = false;

        if (!respuesta.exito) {
          mensajeNuevaMascota.textContent = respuesta.mensaje || "No se pudo registrar la mascota.";
          return;
        }

        agregarMascotaNuevaAlFlujo(respuesta.mascota);
      })
      .catch(function (error) {
        botonGuardarNuevaMascota.disabled = false;
        mensajeNuevaMascota.textContent = "Ocurrió un error al conectar con el servidor.";
        console.log(error);
      });
  }

  /* ============================================
     CONSULTA Y PROFESIONAL DESDE SQL
     ============================================ */

  function obtenerConsultaSeleccionada() {
    return document.querySelector('input[name="tipo_consulta"]:checked');
  }

  function actualizarConsultaSeleccionada() {
    const consultaSeleccionada = obtenerConsultaSeleccionada();

    if (!consultaSeleccionada) {
      return;
    }

    const claveConsulta = consultaSeleccionada.value;
    const textoConsulta = consultaSeleccionada.dataset.motivo;

    campoMotivoConsulta.value = textoConsulta;
    resumenConsulta.textContent = textoConsulta;

    cargarProfesionalesDesdeBase(claveConsulta);
  }

  function cargarProfesionalesDesdeBase(claveConsulta) {
    solicitudProfesionalesActual++;
    const solicitudActual = solicitudProfesionalesActual;

    campoProfesional.innerHTML = "";

    const opcionCargando = document.createElement("option");
    opcionCargando.value = "";
    opcionCargando.textContent = "Cargando profesionales...";
    campoProfesional.appendChild(opcionCargando);

    resumenProfesional.textContent = "Cargando...";
    mostrarEstadoHorario("Cargando...");

    fetch("php/obtener_profesionales.php?tipo=" + encodeURIComponent(claveConsulta))
      .then(function (respuesta) {
        return respuesta.json();
      })
      .then(function (datos) {
        if (solicitudActual !== solicitudProfesionalesActual) {
          return;
        }

        campoProfesional.innerHTML = "";

        if (!datos.exito || !Array.isArray(datos.profesionales) || datos.profesionales.length === 0) {
          const opcionVacia = document.createElement("option");

          opcionVacia.value = "";
          opcionVacia.textContent = "Sin profesionales disponibles";

          campoProfesional.appendChild(opcionVacia);
          resumenProfesional.textContent = "Sin profesional asignado";
          mostrarEstadoHorario("Sin horario");

          return;
        }

        datos.profesionales.forEach(function (profesional) {
          const opcion = document.createElement("option");

          opcion.value = profesional.matricula_profesional;
          opcion.textContent = profesional.nombre + " - " + profesional.especialidad;
          opcion.dataset.nombre = profesional.nombre;
          opcion.dataset.especialidad = profesional.especialidad;

          campoProfesional.appendChild(opcion);
        });

        actualizarResumenProfesional();
        cargarHorariosDesdeBase(campoProfesional.value);
      })
      .catch(function (error) {
        if (solicitudActual !== solicitudProfesionalesActual) {
          return;
        }

        campoProfesional.innerHTML = "";

        const opcionError = document.createElement("option");

        opcionError.value = "";
        opcionError.textContent = "Error al cargar profesionales";

        campoProfesional.appendChild(opcionError);
        resumenProfesional.textContent = "Sin profesional asignado";
        mostrarEstadoHorario("Sin horario");

        console.log(error);
      });
  }

  function actualizarResumenProfesional() {
    const opcionSeleccionada = campoProfesional.options[campoProfesional.selectedIndex];

    if (!opcionSeleccionada || opcionSeleccionada.value === "") {
      resumenProfesional.textContent = "Sin profesional asignado";
      return;
    }

    resumenProfesional.textContent = opcionSeleccionada.dataset.nombre;
  }

  /* ============================================
     HORARIOS DESDE SQL
     ============================================ */

  function limpiarBotonesHorario() {
    const botonesHorario = contenedorHorarios.querySelectorAll(".boton-horario");

    botonesHorario.forEach(function (boton) {
      boton.remove();
    });
  }

  function mostrarEstadoHorario(texto) {
    limpiarBotonesHorario();

    campoHorario.value = "";
    resumenHorario.textContent = texto;

    const botonEstado = document.createElement("button");

    botonEstado.type = "button";
    botonEstado.className = "boton-horario";
    botonEstado.disabled = true;
    botonEstado.textContent = texto;

    contenedorHorarios.appendChild(botonEstado);
  }

  function cargarHorariosDesdeBase(matriculaProfesional) {
    solicitudHorariosActual++;
    const solicitudActual = solicitudHorariosActual;

    if (!matriculaProfesional || matriculaProfesional === "") {
      mostrarEstadoHorario("Sin horario");
      return;
    }

    mostrarEstadoHorario("Cargando...");

    const fechaTurno = campoFecha.value;

    fetch(
      "php/obtener_horarios.php?matricula=" +
      encodeURIComponent(matriculaProfesional) +
      "&fecha=" +
      encodeURIComponent(fechaTurno)
    )
      .then(function (respuesta) {
        return respuesta.json();
      })
      .then(function (datos) {
        if (solicitudActual !== solicitudHorariosActual) {
          return;
        }

        limpiarBotonesHorario();

        if (!datos.exito) {
          mostrarEstadoHorario(datos.mensaje || "No se pudieron cargar los horarios.");
          return;
        }

        if (!Array.isArray(datos.horarios) || datos.horarios.length === 0) {
          mostrarEstadoHorario(datos.mensaje || "Sin horarios disponibles.");
          return;
        }

        datos.horarios.forEach(function (horario) {
          const botonHorario = document.createElement("button");

          botonHorario.type = "button";
          botonHorario.className = "boton-horario";
          botonHorario.dataset.horario = horario;
          botonHorario.setAttribute("aria-pressed", "false");
          botonHorario.textContent = horario;

          botonHorario.addEventListener("click", function () {
            seleccionarHorario(botonHorario);
          });

          contenedorHorarios.appendChild(botonHorario);
        });

        seleccionarPrimerHorario();
      })
      .catch(function (error) {
        if (solicitudActual !== solicitudHorariosActual) {
          return;
        }

        mostrarEstadoHorario("Error");
        console.log(error);
      });
  }

  function seleccionarHorario(botonSeleccionado) {
    const botonesHorario = contenedorHorarios.querySelectorAll(".boton-horario");

    botonesHorario.forEach(function (botonHorario) {
      botonHorario.classList.remove("horario-activo");
      botonHorario.setAttribute("aria-pressed", "false");
    });

    botonSeleccionado.classList.add("horario-activo");
    botonSeleccionado.setAttribute("aria-pressed", "true");

    campoHorario.value = botonSeleccionado.dataset.horario;
    resumenHorario.textContent = botonSeleccionado.dataset.horario;
  }

  function seleccionarPrimerHorario() {
    const primerHorario = contenedorHorarios.querySelector(".boton-horario:not(:disabled)");

    if (primerHorario) {
      seleccionarHorario(primerHorario);
    } else {
      campoHorario.value = "";
      resumenHorario.textContent = "Sin horario";
    }
  }

  /* ============================================
     FECHA
     ============================================ */

  function formatearFecha(fechaValor) {
    if (!fechaValor) {
      return "Seleccioná una fecha";
    }

    const partesFecha = fechaValor.split("-");
    const fecha = new Date(partesFecha[0], partesFecha[1] - 1, partesFecha[2]);

    const fechaFormateada = new Intl.DateTimeFormat("es-AR", {
      weekday: "long",
      day: "numeric",
      month: "long",
      year: "numeric"
    }).format(fecha);

    return fechaFormateada.charAt(0).toUpperCase() + fechaFormateada.slice(1);
  }

  function actualizarResumenFecha() {
    resumenFecha.textContent = formatearFecha(campoFecha.value);
  }

  /* ============================================
     GUARDAR TURNO EN PHP
     ============================================ */

  function guardarTurnoEnBase() {
    const datosTurno = new FormData(formReservaTurno);

    return fetch("php/guardar_turno.php", {
      method: "POST",
      body: datosTurno
    })
      .then(function (respuesta) {
        return respuesta.json();
      });
  }

  /* ============================================
     EVENTOS
     ============================================ */

  formDatosPersona.addEventListener("submit", function (evento) {
    evento.preventDefault();

    const datosValidos = validarDatosPersona();

    if (datosValidos) {
      mensajeErrorDatos.textContent = "Buscando mascotas...";

      cargarMascotasDelDueno().then(function (puedeContinuar) {
        if (puedeContinuar) {
          mensajeErrorDatos.textContent = "";
          mostrarPasoDos();
        }
      });
    }
  });

  botonVolverPasoUno.addEventListener("click", function () {
    mostrarPasoUno();
  });

  botonMascota.addEventListener("click", function () {
    abrirCerrarMascotas();
  });

  botonNuevaMascota.addEventListener("click", function () {
    mostrarFormularioNuevaMascota();
  });

  botonGuardarNuevaMascota.addEventListener("click", function () {
    guardarNuevaMascota();
  });

  botonCancelarNuevaMascota.addEventListener("click", function () {
    ocultarFormularioNuevaMascota();
  });

  opcionesConsulta.forEach(function (opcionConsulta) {
    opcionConsulta.addEventListener("change", function () {
      actualizarConsultaSeleccionada();
    });
  });

  campoProfesional.addEventListener("change", function () {
    actualizarResumenProfesional();
    cargarHorariosDesdeBase(campoProfesional.value);
  });

  campoFecha.addEventListener("change", function () {
    actualizarResumenFecha();
    cargarHorariosDesdeBase(campoProfesional.value);
  });

  formReservaTurno.addEventListener("submit", function (evento) {
    evento.preventDefault();

    if (!mascotaSeleccionadaActual || campoIdPaciente.value === "") {
      mensajeMascota.textContent = "Seleccioná una mascota o registrá una nueva para continuar.";
      return;
    }

    if (campoProfesional.value === "") {
      if (mensajeErrorTurno) {
        mensajeErrorTurno.textContent = "Seleccioná un profesional para continuar.";
      }
      return;
    }

    if (campoHorario.value === "") {
      if (mensajeErrorTurno) {
        mensajeErrorTurno.textContent = "Seleccioná un horario disponible para continuar.";
      }
      return;
    }

    mensajeMascota.textContent = "";

    if (mensajeErrorTurno) {
      mensajeErrorTurno.textContent = "Guardando turno...";
    }

    guardarTurnoEnBase()
      .then(function (respuesta) {
        if (respuesta.exito) {
          if (mensajeErrorTurno) {
            mensajeErrorTurno.textContent = "";
          }

          mostrarPasoExito();
        } else {
          if (mensajeErrorTurno) {
            mensajeErrorTurno.textContent = respuesta.mensaje || "No se pudo guardar el turno.";
          }

          if (respuesta.detalle) {
            console.log(respuesta.detalle);
          }
        }
      })
      .catch(function (error) {
        if (mensajeErrorTurno) {
          mensajeErrorTurno.textContent = "Ocurrió un error al conectar con el servidor.";
        }

        console.log(error);
      });
  });

  document.addEventListener("click", function (evento) {
    const clickDentroSelector = selectorMascota.contains(evento.target);

    if (!clickDentroSelector) {
      cerrarDesplegableMascotas();
    }
  });

  window.addEventListener("pageshow", function () {
    resetearFlujoInicial();
  });

  resetearFlujoInicial();
});