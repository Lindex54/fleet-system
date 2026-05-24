const sidebar = document.querySelector('#app-sidebar');
const sidebarToggle = document.querySelector('#sidebar-toggle');
const sidebarBackdrop = document.querySelector('#sidebar-backdrop');

function setSidebarOpen(isOpen) {
  if (!sidebar || !sidebarBackdrop) {
    return;
  }

  sidebar.classList.toggle('-translate-x-full', !isOpen);
  sidebarBackdrop.classList.toggle('hidden', !isOpen);
}

sidebarToggle?.addEventListener('click', () => setSidebarOpen(true));
sidebarBackdrop?.addEventListener('click', () => setSidebarOpen(false));
