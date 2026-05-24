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
const logbookSearch = document.querySelector('#logbook-search');
const logbookRows = document.querySelectorAll('[data-logbook-table] .logbook-row');
const driverSearch = document.querySelector('#driver-search');
const driverRows = document.querySelectorAll('[data-driver-table] .driver-row');
const maintenanceSearch = document.querySelector('#maintenance-search');
const maintenanceStatus = document.querySelector('#maintenance-status');
const maintenanceRows = document.querySelectorAll('[data-maintenance-table] .maintenance-row');

vehicleSearch?.addEventListener('input', (event) => {
  const query = event.target.value.trim().toLowerCase();

  vehicleRows.forEach((row) => {
    const haystack = row.dataset.search || row.textContent.toLowerCase();
    row.classList.toggle('hidden', query.length > 0 && !haystack.includes(query));
  });
});

logbookSearch?.addEventListener('input', (event) => {
  const query = event.target.value.trim().toLowerCase();

  logbookRows.forEach((row) => {
    const haystack = row.dataset.search || row.textContent.toLowerCase();
    row.classList.toggle('hidden', query.length > 0 && !haystack.includes(query));
  });
});

driverSearch?.addEventListener('input', (event) => {
  const query = event.target.value.trim().toLowerCase();

  driverRows.forEach((row) => {
    const haystack = row.dataset.search || row.textContent.toLowerCase();
    row.classList.toggle('hidden', query.length > 0 && !haystack.includes(query));
  });
});

function filterMaintenanceRows() {
  const query = maintenanceSearch?.value.trim().toLowerCase() || '';
  const status = maintenanceStatus?.value || 'all';

  maintenanceRows.forEach((row) => {
    const haystack = row.dataset.search || row.textContent.toLowerCase();
    const rowStatus = row.dataset.status || '';
    const matchesSearch = query.length === 0 || haystack.includes(query);
    const matchesStatus = status === 'all' || rowStatus === status;

    row.classList.toggle('hidden', !matchesSearch || !matchesStatus);
  });
}

maintenanceSearch?.addEventListener('input', filterMaintenanceRows);
maintenanceStatus?.addEventListener('change', filterMaintenanceRows);

document.querySelector('[data-print-page]')?.addEventListener('click', () => {
  window.print();
});

const vehicleModal = document.querySelector('#vehicle-modal');
const openVehicleModalButtons = document.querySelectorAll('[data-open-vehicle-modal]');
const closeVehicleModalButtons = document.querySelectorAll('[data-close-vehicle-modal]');
const logbookModal = document.querySelector('#logbook-modal');
const openLogbookModalButtons = document.querySelectorAll('[data-open-logbook-modal]');
const closeLogbookModalButtons = document.querySelectorAll('[data-close-logbook-modal]');

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
    openVehicleModalButtons[0]?.focus();
  }
}

openVehicleModalButtons.forEach((button) => {
  button.addEventListener('click', () => setVehicleModalOpen(true));
});

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

function setLogbookModalOpen(isOpen) {
  if (!logbookModal) {
    return;
  }

  logbookModal.classList.toggle('hidden', !isOpen);
  logbookModal.classList.toggle('flex', isOpen);
  logbookModal.setAttribute('aria-hidden', String(!isOpen));
  document.body.classList.toggle('overflow-hidden', isOpen);

  if (isOpen) {
    logbookModal.querySelector('input, select, textarea, button')?.focus();
  } else {
    openLogbookModalButtons[0]?.focus();
  }
}

openLogbookModalButtons.forEach((button) => {
  button.addEventListener('click', () => setLogbookModalOpen(true));
});

closeLogbookModalButtons.forEach((button) => {
  button.addEventListener('click', () => setLogbookModalOpen(false));
});

logbookModal?.addEventListener('click', (event) => {
  if (event.target === logbookModal) {
    setLogbookModalOpen(false);
  }
});

document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape' && logbookModal && !logbookModal.classList.contains('hidden')) {
    setLogbookModalOpen(false);
  }
});
