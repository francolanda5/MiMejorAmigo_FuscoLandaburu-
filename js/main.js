/* ============================================
   main.js - INTERACCIONES GENERALES
   ============================================ */

document.addEventListener("DOMContentLoaded", function () {
  const menuNavbar = document.getElementById("navbarNav");
  const linksMenu = document.querySelectorAll(".menu-navbar .nav-link");

  linksMenu.forEach(function (link) {
    link.addEventListener("click", function () {
      if (menuNavbar.classList.contains("show")) {
        const menuBootstrap = bootstrap.Collapse.getOrCreateInstance(menuNavbar);
        menuBootstrap.hide();
      }
    });
  });
});