/**
 * NOVE ÓPTICA – Zero-Trust Build V13.2
 * Dashboard dinámico con verificación CSRF y UX mejorada
 * Ultra-Stable AppServ Edition
 */

"use strict";

document.addEventListener("DOMContentLoaded", () => {

  // =============================================================
  // 🎨 CONFIGURACIÓN GLOBAL CHART.JS
  // =============================================================
  Chart.defaults.font.family = "Segoe UI, Arial, sans-serif";
  Chart.defaults.color = "#e9e9e9";
  const colores = ['#5b9bd5', '#7aa8d8', '#4f93ce', '#99c3ee', '#3a78b4'];

  const csrf = document.querySelector("input[name='csrf_token']")?.value;
  if (!csrf) console.warn("⚠️ CSRF token ausente en dashboard.js");

  // =============================================================
  // 🔄 FUNCIÓN PRINCIPAL: CARGA DE DATOS
  // =============================================================
  async function cargarDatos(inicio, fin) {
    if (!inicio || !fin) return alert("Selecciona ambas fechas.");

    mostrarSpinnerGlobal();

    try {
      const resp = await fetch("/nove_optica/admin/stats_data.php", {
        method: "POST",
        headers: { "X-CSRF-Token": csrf },
        body: new URLSearchParams({ inicio, fin })
      });

      if (!resp.ok) throw new Error("Error HTTP");
      const data = await resp.json();
      if (data.error) throw new Error(data.error);

      actualizarGraficos(data);
    } catch (err) {
      console.error("❌ Error al obtener datos del servidor:", err);
      alert("No se pudieron cargar las estadísticas.");
    } finally {
      ocultarSpinnerGlobal();
    }
  }

  // =============================================================
  // 📈 CREACIÓN Y ACTUALIZACIÓN DE GRÁFICOS
  // =============================================================
  let grafUsuarios, grafVentas, grafTop;

  function actualizarGraficos(data) {
    if (grafUsuarios) grafUsuarios.destroy();
    if (grafVentas) grafVentas.destroy();
    if (grafTop) grafTop.destroy();

    grafUsuarios = new Chart(document.getElementById("grafUsuarios"), {
      type: "line",
      data: {
        labels: data.labelsUsuarios,
        datasets: [{
          label: "Usuarios nuevos",
          data: data.dataUsuarios,
          borderColor: "#6da8d6",
          fill: false,
          tension: 0.3
        }]
      },
      options: { plugins: { legend: { display: false } }, responsive: true }
    });

    grafVentas = new Chart(document.getElementById("grafVentas"), {
      type: "bar",
      data: {
        labels: data.labelsVentas,
        datasets: [{
          label: "Ventas (€)",
          data: data.dataVentas,
          backgroundColor: "#7aa8d8",
          borderRadius: 8
        }]
      },
      options: { plugins: { legend: { display: false } }, responsive: true }
    });

    grafTop = new Chart(document.getElementById("grafTop"), {
      type: "doughnut",
      data: {
        labels: data.labelsTop,
        datasets: [{
          data: data.dataTop,
          backgroundColor: colores
        }]
      },
      options: {
        plugins: {
          legend: { position: "bottom", labels: { color: "#ccc", boxWidth: 14 } }
        },
        responsive: true
      }
    });
  }

  // =============================================================
  // 📅 EVENTO DE FILTRO DE FECHAS
  // =============================================================
  const btnActualizar = document.querySelector(".boton-admin, .boton-primario");
  const fechaInicio = document.getElementById("fecha_inicio");
  const fechaFin = document.getElementById("fecha_fin");

  if (btnActualizar && fechaInicio && fechaFin) {
    btnActualizar.addEventListener("click", () =>
      cargarDatos(fechaInicio.value, fechaFin.value)
    );
  }

  // =============================================================
  // 🚀 AUTO-CARGA INICIAL (últimos 7 días)
  // =============================================================
  const hoy = new Date();
  const hace7 = new Date(hoy);
  hace7.setDate(hoy.getDate() - 7);
  const formato = d => d.toISOString().split("T")[0];

  if (fechaInicio && fechaFin) {
    fechaInicio.value = formato(hace7);
    fechaFin.value = formato(hoy);
    cargarDatos(formato(hace7), formato(hoy));
  }

  console.info("✅ dashboard.js cargado — estadísticas activas y seguras");
});
