









document.addEventListener("DOMContentLoaded", () => {

  const popup = document.getElementById("popup-descuento");
  const cerrar = document.getElementById("cerrarPopup");
  const form = document.getElementById("form-descuento");

  // // mostrar el popup después de 3 segundos (solo usuarios no logueados)
  if (popup && !sessionStorage.getItem("popup_visto")) {
    setTimeout(() => {
      popup.classList.add("activo");
      popup.setAttribute("aria-hidden", "false");
      sessionStorage.setItem("popup_visto", "1");
    }, 3000);
  }

  // // cerrar el popup
  if (cerrar) {
    cerrar.addEventListener("click", () => {
      popup.classList.remove("activo");
      popup.setAttribute("aria-hidden", "true");
    });
  }

  // // cierre si se hace clic fuera
  if (popup) {
    popup.addEventListener("click", (e) => {
      if (e.target === popup) {
        popup.classList.remove("activo");
        popup.setAttribute("aria-hidden", "true");
      }
    });
  }

  // // validación básica del formulario
  if (form) {
    form.addEventListener("submit", (e) => {

      const correo = document.getElementById("correo-desc");
      const telefono = document.getElementById("telefono-desc");
      const honeypot = document.getElementById("honeypot");

      // anti-bot
      if (honeypot.value !== "") {
        e.preventDefault();
        return;
      }

      // validar email
      if (!correo.value.includes("@") || correo.value.length < 6) {
        e.preventDefault();
        correo.style.border = "2px solid red";
        return;
      }

      // validar teléfono
      const regexTel = /^[0-9+\s-]+$/;
      if (!regexTel.test(telefono.value)) {
        e.preventDefault();
        telefono.style.border = "2px solid red";
        return;
      }

      // pequeño spinner global (si existe)
      const spinner = document.getElementById("spinner-global");
      if (spinner) spinner.hidden = false;
    });
  }
});
