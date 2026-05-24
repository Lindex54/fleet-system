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
const preInspectionSearch = document.querySelector('#pre-inspection-search');
const preInspectionRows = document.querySelectorAll('[data-pre-inspection-table] .pre-inspection-row');
const postInspectionSearch = document.querySelector('#post-inspection-search');
const postInspectionRows = document.querySelectorAll('[data-post-inspection-table] .post-inspection-row');
const providerSearch = document.querySelector('#provider-search');
const providerCards = document.querySelectorAll('[data-provider-list] .provider-card');
const communicationHistorySearch = document.querySelector('#communication-history-search');
const communicationHistoryRows = document.querySelectorAll('[data-communication-history-table] .communication-history-row');
const driverRecipientCheckboxes = document.querySelectorAll('[data-driver-recipient]');
const selectAllDrivers = document.querySelector('[data-select-all-drivers]');
const driverRecipientCount = document.querySelector('[data-driver-recipient-count]');
const officerRecipientCount = document.querySelector('[data-officer-recipient-count]');
const officerEmailInput = document.querySelector('#officer-email-input');
const addOfficerRecipientButton = document.querySelector('[data-add-officer-recipient]');
const officerRecipientList = document.querySelector('[data-officer-recipient-list]');
const communicationRecipientLabel = document.querySelector('[data-communication-recipient-label]');
const communicationSendButton = document.querySelector('[data-communication-send]');
const officerRecipients = new Set();

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

preInspectionSearch?.addEventListener('input', (event) => {
  const query = event.target.value.trim().toLowerCase();

  preInspectionRows.forEach((row) => {
    const haystack = row.dataset.search || row.textContent.toLowerCase();
    row.classList.toggle('hidden', query.length > 0 && !haystack.includes(query));
  });
});

postInspectionSearch?.addEventListener('input', (event) => {
  const query = event.target.value.trim().toLowerCase();

  postInspectionRows.forEach((row) => {
    const haystack = row.dataset.search || row.textContent.toLowerCase();
    row.classList.toggle('hidden', query.length > 0 && !haystack.includes(query));
  });
});

providerSearch?.addEventListener('input', (event) => {
  const query = event.target.value.trim().toLowerCase();

  providerCards.forEach((card) => {
    const haystack = card.dataset.search || card.textContent.toLowerCase();
    card.classList.toggle('hidden', query.length > 0 && !haystack.includes(query));
  });
});

communicationHistorySearch?.addEventListener('input', (event) => {
  const query = event.target.value.trim().toLowerCase();

  communicationHistoryRows.forEach((row) => {
    const haystack = row.dataset.search || row.textContent.toLowerCase();
    row.classList.toggle('hidden', query.length > 0 && !haystack.includes(query));
  });
});

document.querySelector('[data-print-page]')?.addEventListener('click', () => {
  window.print();
});

function updateCommunicationRecipientState() {
  const selectedDrivers = Array.from(driverRecipientCheckboxes).filter((checkbox) => checkbox.checked).length;
  const selectedOfficers = officerRecipients.size;
  const totalRecipients = selectedDrivers + selectedOfficers;

  if (driverRecipientCount) {
    driverRecipientCount.textContent = String(selectedDrivers);
  }

  if (officerRecipientCount) {
    officerRecipientCount.textContent = String(selectedOfficers);
  }

  if (communicationRecipientLabel) {
    communicationRecipientLabel.textContent = totalRecipients === 0
      ? 'No recipients selected'
      : `${totalRecipients} recipient${totalRecipients === 1 ? '' : 's'} selected`;
  }

  if (communicationSendButton) {
    communicationSendButton.disabled = totalRecipients === 0;
    communicationSendButton.classList.toggle('bg-slate-400', totalRecipients === 0);
    communicationSendButton.classList.toggle('hover:bg-fleet-sidebar', totalRecipients === 0);
    communicationSendButton.classList.toggle('bg-fleet-sidebar', totalRecipients > 0);
    communicationSendButton.classList.toggle('hover:bg-fleet-sidebar-active', totalRecipients > 0);
  }
}

function renderOfficerRecipients() {
  if (!officerRecipientList) {
    return;
  }

  officerRecipientList.innerHTML = '';
  officerRecipientList.classList.toggle('hidden', officerRecipients.size === 0);
  officerRecipientList.classList.toggle('flex', officerRecipients.size > 0);

  officerRecipients.forEach((email) => {
    const chip = document.createElement('span');
    chip.className = 'inline-flex items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-fleet-primary';

    const label = document.createElement('span');
    label.textContent = email;

    const removeButton = document.createElement('button');
    removeButton.type = 'button';
    removeButton.className = 'text-base leading-none text-fleet-muted hover:text-fleet-danger';
    removeButton.setAttribute('aria-label', `Remove ${email}`);
    removeButton.textContent = 'x';
    removeButton.addEventListener('click', () => {
      officerRecipients.delete(email);
      renderOfficerRecipients();
      updateCommunicationRecipientState();
    });

    chip.append(label, removeButton);
    officerRecipientList.append(chip);
  });
}

function addOfficerRecipients() {
  if (!officerEmailInput) {
    return;
  }

  const emails = officerEmailInput.value
    .split(',')
    .map((email) => email.trim().toLowerCase())
    .filter((email) => email.length > 0);

  emails.forEach((email) => officerRecipients.add(email));
  officerEmailInput.value = '';
  renderOfficerRecipients();
  updateCommunicationRecipientState();
}

driverRecipientCheckboxes.forEach((checkbox) => {
  checkbox.addEventListener('change', updateCommunicationRecipientState);
});

selectAllDrivers?.addEventListener('change', (event) => {
  driverRecipientCheckboxes.forEach((checkbox) => {
    checkbox.checked = event.target.checked;
  });
  updateCommunicationRecipientState();
});

addOfficerRecipientButton?.addEventListener('click', addOfficerRecipients);
officerEmailInput?.addEventListener('keydown', (event) => {
  if (event.key === 'Enter') {
    event.preventDefault();
    addOfficerRecipients();
  }
});

updateCommunicationRecipientState();

const vehicleModal = document.querySelector('#vehicle-modal');
const openVehicleModalButtons = document.querySelectorAll('[data-open-vehicle-modal]');
const closeVehicleModalButtons = document.querySelectorAll('[data-close-vehicle-modal]');
const logbookModal = document.querySelector('#logbook-modal');
const openLogbookModalButtons = document.querySelectorAll('[data-open-logbook-modal]');
const closeLogbookModalButtons = document.querySelectorAll('[data-close-logbook-modal]');
const driverModal = document.querySelector('#driver-modal');
const openDriverModalButtons = document.querySelectorAll('[data-open-driver-modal]');
const closeDriverModalButtons = document.querySelectorAll('[data-close-driver-modal]');
const maintenanceModal = document.querySelector('#maintenance-modal');
const openMaintenanceModalButtons = document.querySelectorAll('[data-open-maintenance-modal]');
const closeMaintenanceModalButtons = document.querySelectorAll('[data-close-maintenance-modal]');
const preInspectionModal = document.querySelector('#pre-inspection-modal');
const openPreInspectionModalButtons = document.querySelectorAll('[data-open-pre-inspection-modal]');
const closePreInspectionModalButtons = document.querySelectorAll('[data-close-pre-inspection-modal]');
const postInspectionModal = document.querySelector('#post-inspection-modal');
const openPostInspectionModalButtons = document.querySelectorAll('[data-open-post-inspection-modal]');
const closePostInspectionModalButtons = document.querySelectorAll('[data-close-post-inspection-modal]');
const providerModal = document.querySelector('#provider-modal');
const openProviderModalButtons = document.querySelectorAll('[data-open-provider-modal]');
const closeProviderModalButtons = document.querySelectorAll('[data-close-provider-modal]');

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

function setDriverModalOpen(isOpen) {
  if (!driverModal) {
    return;
  }

  driverModal.classList.toggle('hidden', !isOpen);
  driverModal.classList.toggle('flex', isOpen);
  driverModal.setAttribute('aria-hidden', String(!isOpen));
  document.body.classList.toggle('overflow-hidden', isOpen);

  if (isOpen) {
    driverModal.querySelector('input, select, button')?.focus();
  } else {
    openDriverModalButtons[0]?.focus();
  }
}

openDriverModalButtons.forEach((button) => {
  button.addEventListener('click', () => setDriverModalOpen(true));
});

closeDriverModalButtons.forEach((button) => {
  button.addEventListener('click', () => setDriverModalOpen(false));
});

driverModal?.addEventListener('click', (event) => {
  if (event.target === driverModal) {
    setDriverModalOpen(false);
  }
});

document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape' && driverModal && !driverModal.classList.contains('hidden')) {
    setDriverModalOpen(false);
  }
});

function setMaintenanceModalOpen(isOpen) {
  if (!maintenanceModal) {
    return;
  }

  maintenanceModal.classList.toggle('hidden', !isOpen);
  maintenanceModal.classList.toggle('flex', isOpen);
  maintenanceModal.setAttribute('aria-hidden', String(!isOpen));
  document.body.classList.toggle('overflow-hidden', isOpen);

  if (isOpen) {
    maintenanceModal.querySelector('input, select, textarea, button')?.focus();
  } else {
    openMaintenanceModalButtons[0]?.focus();
  }
}

openMaintenanceModalButtons.forEach((button) => {
  button.addEventListener('click', () => setMaintenanceModalOpen(true));
});

closeMaintenanceModalButtons.forEach((button) => {
  button.addEventListener('click', () => setMaintenanceModalOpen(false));
});

maintenanceModal?.addEventListener('click', (event) => {
  if (event.target === maintenanceModal) {
    setMaintenanceModalOpen(false);
  }
});

document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape' && maintenanceModal && !maintenanceModal.classList.contains('hidden')) {
    setMaintenanceModalOpen(false);
  }
});

function setPreInspectionModalOpen(isOpen) {
  if (!preInspectionModal) {
    return;
  }

  preInspectionModal.classList.toggle('hidden', !isOpen);
  preInspectionModal.classList.toggle('flex', isOpen);
  preInspectionModal.setAttribute('aria-hidden', String(!isOpen));
  document.body.classList.toggle('overflow-hidden', isOpen);

  if (isOpen) {
    preInspectionModal.querySelector('input, select, textarea, button')?.focus();
  } else {
    openPreInspectionModalButtons[0]?.focus();
  }
}

openPreInspectionModalButtons.forEach((button) => {
  button.addEventListener('click', () => setPreInspectionModalOpen(true));
});

closePreInspectionModalButtons.forEach((button) => {
  button.addEventListener('click', () => setPreInspectionModalOpen(false));
});

preInspectionModal?.addEventListener('click', (event) => {
  if (event.target === preInspectionModal) {
    setPreInspectionModalOpen(false);
  }
});

document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape' && preInspectionModal && !preInspectionModal.classList.contains('hidden')) {
    setPreInspectionModalOpen(false);
  }
});

function setPostInspectionModalOpen(isOpen) {
  if (!postInspectionModal) {
    return;
  }

  postInspectionModal.classList.toggle('hidden', !isOpen);
  postInspectionModal.classList.toggle('flex', isOpen);
  postInspectionModal.setAttribute('aria-hidden', String(!isOpen));
  document.body.classList.toggle('overflow-hidden', isOpen);

  if (isOpen) {
    postInspectionModal.querySelector('input, select, textarea, button')?.focus();
  } else {
    openPostInspectionModalButtons[0]?.focus();
  }
}

openPostInspectionModalButtons.forEach((button) => {
  button.addEventListener('click', () => setPostInspectionModalOpen(true));
});

closePostInspectionModalButtons.forEach((button) => {
  button.addEventListener('click', () => setPostInspectionModalOpen(false));
});

postInspectionModal?.addEventListener('click', (event) => {
  if (event.target === postInspectionModal) {
    setPostInspectionModalOpen(false);
  }
});

document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape' && postInspectionModal && !postInspectionModal.classList.contains('hidden')) {
    setPostInspectionModalOpen(false);
  }
});

function setProviderModalOpen(isOpen) {
  if (!providerModal) {
    return;
  }

  providerModal.classList.toggle('hidden', !isOpen);
  providerModal.classList.toggle('flex', isOpen);
  providerModal.setAttribute('aria-hidden', String(!isOpen));
  document.body.classList.toggle('overflow-hidden', isOpen);

  if (isOpen) {
    providerModal.querySelector('input, select, textarea, button')?.focus();
  } else {
    openProviderModalButtons[0]?.focus();
  }
}

openProviderModalButtons.forEach((button) => {
  button.addEventListener('click', () => setProviderModalOpen(true));
});

closeProviderModalButtons.forEach((button) => {
  button.addEventListener('click', () => setProviderModalOpen(false));
});

providerModal?.addEventListener('click', (event) => {
  if (event.target === providerModal) {
    setProviderModalOpen(false);
  }
});

document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape' && providerModal && !providerModal.classList.contains('hidden')) {
    setProviderModalOpen(false);
  }
});
