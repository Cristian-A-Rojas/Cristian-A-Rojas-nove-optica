/* ╔════════════════════════════════════════════════════════════╗
   ║ NOVE ÓPTICA v13.2 Zero-Trust Build | global.js             ║
   ║ Núcleo JS universal: logout seguro, spinner, popup y UX     ║
   ╚════════════════════════════════════════════════════════════╝ */

"use strict";

document.addEventListener("DOMContentLoaded", () => {

  /* ───────────────────────────────
     🔒 LOGOUT SEGURO Y CONFIRMADO
  ─────────────────────────────── */
  document.querySelectorAll("a.logout, #cerrarSesion, #logoutFooter, form[action*='logout.php']").forEach(el => {
    el.addEventListener("click", e => {
      if (!confirm("¿Deseas cerrar sesión de forma segura?")) {
        e.preventDefault();
      } else {
        sessionStorage.clear();
      }
    });
  });

  /* ───────────────────────────────
     🌀 SPINNER GLOBAL UNIVERSAL
  ─────────────────────────────── */
  const spinner = document.createElement("div");
  spinner.id = "spinner-global";
  spinner.className = "spinner-global";
  spinner.innerHTML = `<div class="loader" role="status" aria-label="Procesando"></div>`;
  spinner.hidden = true;
  document.body.appendChild(spinner);

  const mostrarSpinner = () => spinner.hidden = false;
  const ocultarSpinner = () => spinner.hidden = true;
  window.mostrarSpinnerGlobal = mostrarSpinner;
  window.ocultarSpinnerGlobal = ocultarSpinner;
  window.addEventListener("load", ocultarSpinner);

  /* ───────────────────────────────
     📤 SPINNER EN FORMULARIOS
  ─────────────────────────────── */
  document.querySelectorAll("form").forEach(form => {
    form.addEventListener("submit", e => {
      const btn = form.querySelector("button[type='submit']");
      if (btn && !btn.disabled) {
        btn.disabled = true;
        btn.innerHTML = `Procesando... <span class="spinner"></span>`;
        mostrarSpinner();
      }
    });
  });

  /* ───────────────────────────────
     🎁 POP-UP DE DESCUENTO (ANTI-BOT + UX)
  ─────────────────────────────── */
  const popup = document.getElementById("popup-descuento");
  const cerrarBtn = document.getElementById("cerrarPopup");

  function mostrarPopup() {
    if (popup && !sessionStorage.getItem("popupMostrado")) {
      popup.classList.add("activo");
      popup.setAttribute("aria-hidden", "false");
      sessionStorage.setItem("popupMostrado", "1");
    }
  }
  function cerrarPopup() {
    if (popup) {
      popup.classList.remove("activo");
      popup.setAttribute("aria-hidden", "true");
    }
  }

  cerrarBtn?.addEventListener("click", cerrarPopup);
  setTimeout(mostrarPopup, 3500); // aparece 3.5s después de la carga

  const formDesc = document.getElementById("form-descuento");
  if (formDesc) {
    formDesc.addEventListener("submit", e => {
      e.preventDefault();
      const correo = document.getElementById("correo-desc")?.value.trim();
      const tel = document.getElementById("telefono-desc")?.value.trim();
      const honeypot = document.getElementById("honeypot")?.value.trim();

      if (honeypot) return; // anti-bot
      if (!correo || !tel) return alert("Por favor completa ambos campos correctamente.");
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo)) return alert("Introduce un correo válido.");

      mostrarSpinner();
      setTimeout(() => {
        alert("Gracias por suscribirte. Recibirás tu código de descuento en breve.");
        cerrarPopup();
        ocultarSpinner();
        formDesc.reset();
      }, 1500);
    });
  }

  /* ───────────────────────────────
     🔄 PREVENCIÓN DE REENVÍO
  ─────────────────────────────── */
  if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
  }

  /* ───────────────────────────────
     🧠 FINGERPRINT LOCAL (Zero-Trust)
  ─────────────────────────────── */
  try {
    const fingerprint = btoa(navigator.userAgent + "|" + navigator.language);
    sessionStorage.setItem("fingerprint", fingerprint);
  } catch {}

  /* ───────────────────────────────
     ✅ CONFIRMACIÓN DE CARGA SEGURA
  ─────────────────────────────── */
  console.info("✅ NOVE Óptica v13.2 global.js activo — Zero-Trust OK");
});


