/* ============================================
   detalle_consulta.js - CARDS DESPLEGABLES
   ============================================ */

document.addEventListener("DOMContentLoaded", function () {
  const botonesDesplegar = document.querySelectorAll(".boton-desplegar-detalle");

  botonesDesplegar.forEach(function (boton) {
    boton.addEventListener("click", function () {
      const idContenido = boton.getAttribute("aria-controls");
      const contenido = document.getElementById(idContenido);
      const card = boton.closest(".card-desplegable");
      const textoBoton = boton.querySelector(".texto-desplegar");

      if (!contenido || !card || !textoBoton) {
        return;
      }

      const estaAbierta = boton.getAttribute("aria-expanded") === "true";

      if (estaAbierta) {
        contenido.hidden = true;
        boton.setAttribute("aria-expanded", "false");
        textoBoton.textContent = "Ver más";
        card.classList.remove("card-desplegable-abierta");
      } else {
        contenido.hidden = false;
        boton.setAttribute("aria-expanded", "true");
        textoBoton.textContent = "Ver menos";
        card.classList.add("card-desplegable-abierta");
      }
    });
  });
});