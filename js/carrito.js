/**
 * NOVE ÓPTICA – Zero Trust Build V13.2
 * Manejo del carrito (AJAX seguro)
 */
document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".btn-add").forEach(btn => {
    btn.addEventListener("click", async () => {
      const csrf = document.querySelector("input[name='csrf_token']")?.value;
      const datos = new FormData();
      datos.append("csrf_token", csrf);
      datos.append("codigo", btn.dataset.codigo);
      datos.append("nombre", btn.dataset.nombre);
      datos.append("precio", btn.dataset.precio);
      datos.append("imagen", btn.dataset.imagen);
      datos.append("cantidad", 1);

      try {
        const resp = await fetch("/nove_optica/carrito/agregar.php", { method: "POST", body: datos });
        const data = await resp.json();
        if (data.ok) {
          const c = document.getElementById("contador-carrito");
          if (c) c.textContent = data.total_items;
          alert("Producto añadido al carrito ✅");
        } else {
          alert("Error: " + (data.error || "No se pudo añadir el producto."));
        }
      } catch {
        alert("Error de conexión con el servidor.");
      }
    });
  });
});
