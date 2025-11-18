/**
 * NOVE ÓPTICA – Zero-Trust Build V13.2
 * Script de cambio de modo oscuro/claro persistente
 * Compatible con prefers-color-scheme y almacenamiento local
 */

(function() {
  const STORAGE_KEY = 'nove_theme';
  const html = document.documentElement;
  const toggleBtn = document.createElement('button');

  // ───────────────────────────────────────────────
  //  🧩 Crear botón de cambio
  // ───────────────────────────────────────────────
  toggleBtn.id = 'darkModeToggle';
  toggleBtn.textContent = '🌙';
  toggleBtn.title = 'Cambiar modo claro/oscuro';
  toggleBtn.setAttribute('aria-label', 'Cambiar modo claro u oscuro');
  toggleBtn.style.cssText = `
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 9999;
    background: var(--color-card);
    color: var(--color-texto);
    border: 1px solid var(--color-borde);
    border-radius: 50%;
    width: 45px;
    height: 45px;
    font-size: 1.3rem;
    cursor: pointer;
    box-shadow: var(--sombra);
    transition: all 0.3s ease;
  `;
  toggleBtn.addEventListener('mouseenter', ()=> toggleBtn.style.transform = 'scale(1.1)');
  toggleBtn.addEventListener('mouseleave', ()=> toggleBtn.style.transform = 'scale(1)');
  document.body.appendChild(toggleBtn);

  // ───────────────────────────────────────────────
  //  🎨 Detección inicial
  // ───────────────────────────────────────────────
  const userPref = localStorage.getItem(STORAGE_KEY);
  const systemPrefDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
  const darkMode = (userPref === 'dark') || (userPref === null && systemPrefDark);
  aplicarTema(darkMode);

  // ───────────────────────────────────────────────
  //  🔄 Evento de cambio manual
  // ───────────────────────────────────────────────
  toggleBtn.addEventListener('click', () => {
    const actual = html.dataset.theme === 'dark';
    aplicarTema(!actual);
    localStorage.setItem(STORAGE_KEY, !actual ? 'dark' : 'light');
  });

  // ───────────────────────────────────────────────
  //  🧱 Función de aplicación de tema
  // ───────────────────────────────────────────────
  function aplicarTema(oscuro) {
    if (oscuro) {
      html.dataset.theme = 'dark';
      toggleBtn.textContent = '☀️';
      document.documentElement.style.setProperty('color-scheme', 'dark');
    } else {
      html.dataset.theme = 'light';
      toggleBtn.textContent = '🌙';
      document.documentElement.style.setProperty('color-scheme', 'light');
    }
  }

  // Escuchar cambios del sistema en tiempo real
  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
    const newMode = e.matches ? 'dark' : 'light';
    if (!localStorage.getItem(STORAGE_KEY)) aplicarTema(newMode === 'dark');
  });
})();
