/* ╔════════════════════════════════════════════════════════════╗
   ║ NOVE ÓPTICA v13.2 Zero-Trust Build | integrity.js          ║
   ║ Control avanzado de fingerprint y sesión local segura       ║
   ╚════════════════════════════════════════════════════════════╝ */

"use strict";

(() => {

  // =============================================================
  // 🧠 FINGERPRINT LOCAL
  // =============================================================
  function generarFingerprint() {
    try {
      const canvas = document.createElement("canvas");
      const ctx = canvas.getContext("2d");
      ctx.textBaseline = "top";
      ctx.font = "14px Arial";
      ctx.fillStyle = "#f60";
      ctx.fillRect(125, 1, 62, 20);
      ctx.fillStyle = "#069";
      ctx.fillText(navigator.userAgent, 2, 15);
      const hash = btoa(canvas.toDataURL());
      return btoa(
        navigator.userAgent +
        "|" + navigator.language +
        "|" + Intl.DateTimeFormat().resolvedOptions().timeZone +
        "|" + hash.slice(0, 25)
      );
    } catch {
      return btoa(navigator.userAgent + "|" + navigator.language);
    }
  }

  const fingerprint = generarFingerprint();
  const previo = sessionStorage.getItem("fingerprint");

  if (!previo) {
    sessionStorage.setItem("fingerprint", fingerprint);
  } else if (previo !== fingerprint) {
    console.warn("⚠️ Huella del navegador modificada. Reiniciando sesión...");
    sessionStorage.clear();
    window.location.href = "/nove_optica/auth/logout.php?alert=fingerprint_changed";
  }

  // =============================================================
  // ⏱️ CONTROL DE INACTIVIDAD
  // =============================================================
  let ultimoEvento = Date.now();
  function reiniciarInactividad() { ultimoEvento = Date.now(); }
  ["mousemove","keydown","scroll","click"].forEach(e =>
    document.addEventListener(e, reiniciarInactividad)
  );

  setInterval(() => {
    if (Date.now() - ultimoEvento > 900000) { // 15 min
      window.location.href = "/nove_optica/auth/logout.php?alert=timeout";
    }
  }, 60000);

  // =============================================================
  // 🔍 VALIDACIÓN BACKEND
  // =============================================================
  async function validarIntegridad() {
    try {
      const resp = await fetch("/nove_optica/includes/security.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
          "X-Fingerprint": fingerprint
        },
        body: "check_integrity=1"
      });
      if (!resp.ok) throw new Error("Error red");
      const data = await resp.text();
      if (data.trim() !== "OK") {
        sessionStorage.clear();
        window.location.href = "/nove_optica/auth/logout.php?alert=session_invalid";
      }
    } catch {
      console.error("Error al validar integridad del cliente.");
    }
  }

  validarIntegridad();
  setInterval(validarIntegridad, 180000); // cada 3 min

  // =============================================================
  // 🧩 MONITOREO DOM (anti-tamper)
  // =============================================================
  const obs = new MutationObserver(muts => {
    for (const m of muts) {
      if (m.type === "childList" && m.addedNodes.length > 15) {
        console.warn("🚨 Posible manipulación detectada en el DOM.");
        sessionStorage.clear();
        window.location.href = "/nove_optica/auth/logout.php?alert=dom_tamper";
      }
    }
  });
  obs.observe(document.body, { childList: true, subtree: true });

  console.info("✅ integrity.js activo — Zero-Trust habilitado");
})();
