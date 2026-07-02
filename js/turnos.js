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
  const campoCorreo = document.getElementById("correo");
  const campoTelefono = document.getElementById("telefono");

  const mensajeErrorDatos = document.getElementById("mensaje-error-datos");
  const mensajeMascota = document.getElementById("mensaje-mascota");

  const listaMascotas = document.getElementById("lista-mascotas");
  const resumenMascota = document.getElementById("resumen-mascota");

  const opcionesConsulta = document.querySelectorAll('input[name="tipo_consulta"]');
  const campoMotivoConsulta = document.getElementById("motivo_consulta");
  const resumenConsulta = document.getElementById("resumen-consulta");

  const campoProfesional = document.getElementById("matricula_profesional");
  const resumenProfesional = document.getElementById("resumen-profesional");

  const botonesHorario = document.querySelectorAll(".boton-horario");
  const campoHorario = document.getElementById("horario_turno");
  const resumenHorario = document.getElementById("resumen-horario");

  const campoFecha = document.getElementById("fecha_turno");
  const resumenFecha = document.getElementById("resumen-fecha");

  let redireccionInicio = null;

  const duenosSimulados = [
    {
      nombreDueno: "Juan Perez",
      correo: "juan@mail.com",
      telefono: "1112345678",
      mascotas: [
        {
          id: 1,
          nombre: "Luna",
          raza: "Golden Retriever",
          imagen: "img/animales/luna.png"
        },
        {
          id: 2,
          nombre: "Milo",
          raza: "Gato doméstico",
          imagen: ""
        }
      ]
    },
    {
      nombreDueno: "Sofia Martinez",
      correo: "sofia@mail.com",
      telefono: "1198765432",
      mascotas: [
        {
          id: 3,
          nombre: "Nala",
          raza: "Caniche",
          imagen: ""
        }
      ]
    }
  ];

  const mascotasDeEjemplo = [
    {
      id: 1,
      nombre: "Luna",
      raza: "Golden Retriever",
      imagen: "img/animales/luna.png"
    },
    {
      id: 2,
      nombre: "Milo",
      raza: "Gato doméstico",
      imagen: ""
    }
  ];

  const profesionalesPorConsulta = {
    consulta_general: [
      {
        nombre: "Valeria Gomez",
        especialidad: "Clínica general",
        matricula: "378892"
      },
      {
        nombre: "Dra. Ana Pérez",
        especialidad: "Clínica general",
        matricula: "123456"
      }
    ],

    vacunacion: [
      {
        nombre: "Dr. Martín Gómez",
        especialidad: "Medicina preventiva",
        matricula: "567890"
      },
      {
        nombre: "Valeria Gomez",
        especialidad: "Clínica general",
        matricula: "378892"
      }
    ],

    cirugias: [
      {
        nombre: "Julian Vivas",
        especialidad: "Cirugía veterinaria",
        matricula: "465789"
      },
      {
        nombre: "Dra. Laura Díaz",
        especialidad: "Cirugía",
        matricula: "901234"
      }
    ],

    analisis: [
      {
        nombre: "Dr. Pablo Ruiz",
        especialidad: "Laboratorio",
        matricula: "345678"
      },
      {
        nombre: "Dra. Carla Romero",
        especialidad: "Análisis clínicos",
        matricula: "512340"
      }
    ]
  };

  function limpiarRedireccion() {
    if (redireccionInicio !== null) {
      clearTimeout(redireccionInicio);
      redireccionInicio = null;
    }
  }

  function mostrarPasoUno() {
    limpiarRedireccion();

    pasoUno.hidden = false;
    pasoDos.hidden = true;
    pasoExito.hidden = true;

    window.scrollTo(0, 0);
  }

  function mostrarPasoDos() {
    limpiarRedireccion();

    pasoUno.hidden = true;
    pasoDos.hidden = false;
    pasoExito.hidden = true;

    window.scrollTo(0, 0);
  }

  function mostrarPasoExito() {
    pasoUno.hidden = true;
    pasoDos.hidden = true;
    pasoExito.hidden = false;

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

    formDatosPersona.reset();
    formReservaTurno.reset();

    listaMascotas.innerHTML = "";
    resumenMascota.textContent = "Seleccioná una mascota";

    seleccionarPrimerHorario();
    actualizarConsultaSeleccionada();
    actualizarResumenFecha();

    window.scrollTo(0, 0);
  }

  function validarDatosPersona() {
    const nombre = campoNombre.value.trim();
    const correo = campoCorreo.value.trim();
    const telefono = campoTelefono.value.trim();

    if (nombre === "" || correo === "" || telefono === "") {
      mensajeErrorDatos.textContent = "Completá nombre, correo y teléfono para continuar.";
      return false;
    }

    if (!correo.includes("@")) {
      mensajeErrorDatos.textContent = "Ingresá un correo electrónico válido.";
      return false;
    }

    mensajeErrorDatos.textContent = "";
    return true;
  }

  function normalizarTexto(texto) {
    return texto.trim().toLowerCase();
  }

  function buscarDuenoSimulado() {
    const correoIngresado = normalizarTexto(campoCorreo.value);
    const telefonoIngresado = normalizarTexto(campoTelefono.value);

    return duenosSimulados.find(function (dueno) {
      const correoDueno = normalizarTexto(dueno.correo);
      const telefonoDueno = normalizarTexto(dueno.telefono);

      return correoDueno === correoIngresado || telefonoDueno === telefonoIngresado;
    });
  }

  function obtenerMascotasParaTurno() {
    const duenoEncontrado = buscarDuenoSimulado();

    if (duenoEncontrado) {
      return duenoEncontrado.mascotas;
    }

    return mascotasDeEjemplo;
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

  function cargarMascotasDelDueno() {
    const mascotas = obtenerMascotasParaTurno();

    listaMascotas.innerHTML = "";
    mensajeMascota.textContent = "";

    mascotas.forEach(function (mascota, indice) {
      const opcion = document.createElement("label");
      opcion.className = "card-mascota card-mascota-opcion";

      const input = document.createElement("input");
      input.type = "radio";
      input.name = "id_paciente";
      input.value = mascota.id;
      input.dataset.nombre = mascota.nombre;
      input.dataset.raza = mascota.raza;

      if (indice === 0) {
        input.checked = true;
        opcion.classList.add("mascota-activa");
      }

      const avatar = crearAvatarMascota(mascota);

      const contenido = document.createElement("span");
      contenido.className = "contenido-mascota";

      const nombre = document.createElement("span");
      nombre.className = "nombre-mascota";
      nombre.textContent = mascota.nombre + " - " + mascota.raza;

      const raza = document.createElement("span");
      raza.className = "raza-mascota";
      raza.textContent = "Mascota seleccionable";

      contenido.appendChild(nombre);
      contenido.appendChild(raza);

      opcion.appendChild(input);
      opcion.appendChild(avatar);
      opcion.appendChild(contenido);

      listaMascotas.appendChild(opcion);
    });

    activarEventosMascotas();
    actualizarResumenMascota();
  }

  function activarEventosMascotas() {
    const opcionesMascota = document.querySelectorAll('input[name="id_paciente"]');

    opcionesMascota.forEach(function (opcionMascota) {
      opcionMascota.addEventListener("change", function () {
        actualizarResumenMascota();
      });
    });
  }

  function actualizarResumenMascota() {
    const mascotaSeleccionada = document.querySelector('input[name="id_paciente"]:checked');
    const cardsMascota = document.querySelectorAll(".card-mascota-opcion");

    cardsMascota.forEach(function (card) {
      card.classList.remove("mascota-activa");
    });

    if (!mascotaSeleccionada) {
      resumenMascota.textContent = "Seleccioná una mascota";
      return;
    }

    mascotaSeleccionada.closest(".card-mascota-opcion").classList.add("mascota-activa");

    resumenMascota.textContent = mascotaSeleccionada.dataset.nombre + " - " + mascotaSeleccionada.dataset.raza;
  }

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

    cargarProfesionales(claveConsulta);
  }

  function cargarProfesionales(claveConsulta) {
    const profesionales = profesionalesPorConsulta[claveConsulta] || [];

    campoProfesional.innerHTML = "";

    profesionales.forEach(function (profesional) {
      const opcion = document.createElement("option");

      opcion.value = profesional.matricula;
      opcion.textContent = profesional.nombre + " - " + profesional.especialidad;
      opcion.dataset.nombre = profesional.nombre;
      opcion.dataset.especialidad = profesional.especialidad;

      campoProfesional.appendChild(opcion);
    });

    actualizarResumenProfesional();
  }

  function actualizarResumenProfesional() {
    const opcionSeleccionada = campoProfesional.options[campoProfesional.selectedIndex];

    if (!opcionSeleccionada) {
      resumenProfesional.textContent = "Sin profesional asignado";
      return;
    }

    resumenProfesional.textContent = opcionSeleccionada.dataset.nombre;
  }

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

  function seleccionarPrimerHorario() {
    const primerHorario = document.querySelector(".boton-horario");

    botonesHorario.forEach(function (botonHorario) {
      botonHorario.classList.remove("horario-activo");
      botonHorario.setAttribute("aria-pressed", "false");
    });

    if (primerHorario) {
      primerHorario.classList.add("horario-activo");
      primerHorario.setAttribute("aria-pressed", "true");

      campoHorario.value = primerHorario.dataset.horario;
      resumenHorario.textContent = primerHorario.dataset.horario;
    }
  }

  formDatosPersona.addEventListener("submit", function (evento) {
    evento.preventDefault();

    const datosValidos = validarDatosPersona();

    if (datosValidos) {
      cargarMascotasDelDueno();
      mostrarPasoDos();
    }
  });

  botonVolverPasoUno.addEventListener("click", function () {
    mostrarPasoUno();
  });

  opcionesConsulta.forEach(function (opcionConsulta) {
    opcionConsulta.addEventListener("change", function () {
      actualizarConsultaSeleccionada();
    });
  });

  campoProfesional.addEventListener("change", function () {
    actualizarResumenProfesional();
  });

  campoFecha.addEventListener("change", function () {
    actualizarResumenFecha();
  });

  botonesHorario.forEach(function (boton) {
    boton.addEventListener("click", function () {
      const horarioSeleccionado = boton.dataset.horario;

      botonesHorario.forEach(function (botonHorario) {
        botonHorario.classList.remove("horario-activo");
        botonHorario.setAttribute("aria-pressed", "false");
      });

      boton.classList.add("horario-activo");
      boton.setAttribute("aria-pressed", "true");

      campoHorario.value = horarioSeleccionado;
      resumenHorario.textContent = horarioSeleccionado;
    });
  });

  formReservaTurno.addEventListener("submit", function (evento) {
    evento.preventDefault();

    const mascotaSeleccionada = document.querySelector('input[name="id_paciente"]:checked');

    if (!mascotaSeleccionada) {
      mensajeMascota.textContent = "Seleccioná una mascota para continuar.";
      return;
    }

    mensajeMascota.textContent = "";
    mostrarPasoExito();
  });

  window.addEventListener("pageshow", function () {
    resetearFlujoInicial();
  });

  resetearFlujoInicial();
});