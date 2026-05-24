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

const vehicleSearch = document.querySelector('#vehicle-search');
const vehicleRows = document.querySelectorAll('[data-vehicle-table] .vehicle-row');

vehicleSearch?.addEventListener('input', (event) => {
  const query = event.target.value.trim().toLowerCase();

  vehicleRows.forEach((row) => {
    const haystack = row.dataset.search || row.textContent.toLowerCase();
    row.classList.toggle('hidden', query.length > 0 && !haystack.includes(query));
  });
});

document.querySelector('[data-print-page]')?.addEventListener('click', () => {
  window.print();
});

const vehicleModal = document.querySelector('#vehicle-modal');
const openVehicleModalButton = document.querySelector('[data-open-vehicle-modal]');
const closeVehicleModalButtons = document.querySelectorAll('[data-close-vehicle-modal]');

function setVehicleModalOpen(isOpen) {
  if (!vehicleModal) {
    return;
  }

  vehicleModal.classList.toggle('hidden', !isOpen);
  vehicleModal.classList.toggle('flex', isOpen);
  vehicleModal.setAttribute('aria-hidden', String(!isOpen));
  document.body.classList.toggle('overflow-hidden', isOpen);

  if (isOpen) {
    vehicleModal.querySelector('input, select, button')?.focus();
  } else {
    openVehicleModalButton?.focus();
  }
}

openVehicleModalButton?.addEventListener('click', () => setVehicleModalOpen(true));

closeVehicleModalButtons.forEach((button) => {
  button.addEventListener('click', () => setVehicleModalOpen(false));
});

vehicleModal?.addEventListener('click', (event) => {
  if (event.target === vehicleModal) {
    setVehicleModalOpen(false);
  }
});

document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape' && vehicleModal && !vehicleModal.classList.contains('hidden')) {
    setVehicleModalOpen(false);
  }
});
