// Sidebar controls
const sidebar = document.querySelector('#app-sidebar');
const sidebarToggle = document.querySelector('#sidebar-toggle');
const sidebarBackdrop = document.querySelector('#sidebar-backdrop');

// Opens or closes the mobile sidebar and backdrop together.
function setSidebarOpen(isOpen) {
  if (!sidebar || !sidebarBackdrop) {
    return;
  }

  sidebar.classList.toggle('-translate-x-full', !isOpen);
  sidebarBackdrop.classList.toggle('hidden', !isOpen);
}

sidebarToggle?.addEventListener('click', () => setSidebarOpen(true));
sidebarBackdrop?.addEventListener('click', () => setSidebarOpen(false));

// Search and filter controls used across data-heavy pages
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
const estateProjectSearch = document.querySelector('#estate-project-search');
const estateStatusFilter = document.querySelector('#estate-status-filter');
const estateCategoryFilter = document.querySelector('#estate-category-filter');
const estateProjectCards = document.querySelectorAll('[data-estate-project-list] .estate-project-card');
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

// Applies the maintenance search text and status filter at the same time.
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

// Applies the Estates search, status, and category filters entirely in the browser.
function filterEstateProjects() {
  const query = estateProjectSearch?.value.trim().toLowerCase() || '';
  const status = estateStatusFilter?.value || 'all';
  const category = estateCategoryFilter?.value || 'all';

  estateProjectCards.forEach((card) => {
    const haystack = card.dataset.search || card.textContent.toLowerCase();
    const cardStatus = card.dataset.status || '';
    const cardCategory = card.dataset.category || '';
    const matchesSearch = query.length === 0 || haystack.includes(query);
    const matchesStatus = status === 'all' || cardStatus === status;
    const matchesCategory = category === 'all' || cardCategory === category;

    card.classList.toggle('hidden', !matchesSearch || !matchesStatus || !matchesCategory);
  });
}

estateProjectSearch?.addEventListener('input', filterEstateProjects);
estateStatusFilter?.addEventListener('change', filterEstateProjects);
estateCategoryFilter?.addEventListener('change', filterEstateProjects);

// Shared page actions and communications recipient helpers
document.querySelector('[data-print-page]')?.addEventListener('click', () => {
  window.print();
});

// Recalculates selected communication recipients and updates the send UI state.
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

// Renders removable email chips for manually added officer recipients.
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

// Parses comma-separated officer emails and adds them to the recipient chip list.
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

// Modal references for all module pages
const vehicleModal = document.querySelector('#vehicle-modal');
const openVehicleModalButtons = document.querySelectorAll('[data-open-vehicle-modal]');
const closeVehicleModalButtons = document.querySelectorAll('[data-close-vehicle-modal]');
const logbookModal = document.querySelector('#logbook-modal');
const openLogbookModalButtons = document.querySelectorAll('[data-open-logbook-modal]');
const closeLogbookModalButtons = document.querySelectorAll('[data-close-logbook-modal]');
const editLogbookEntryButtons = document.querySelectorAll('[data-edit-logbook-entry]');
const logbookActionField = document.querySelector('[data-logbook-action-field]');
const logbookEntryIdField = document.querySelector('[data-logbook-entry-id-field]');
const logbookModalTitle = document.querySelector('[data-logbook-modal-title]');
const logbookSubmitButton = document.querySelector('[data-logbook-submit-button]');
const deleteLogbookForms = document.querySelectorAll('[data-delete-logbook-form]');
const openLogbookDeleteButtons = document.querySelectorAll('[data-open-logbook-delete]');
const logbookDeleteModal = document.querySelector('#logbook-delete-modal');
const cancelLogbookDeleteButton = document.querySelector('[data-cancel-logbook-delete]');
const confirmLogbookDeleteButton = document.querySelector('[data-confirm-logbook-delete]');
const driverModal = document.querySelector('#driver-modal');
const openDriverModalButtons = document.querySelectorAll('[data-open-driver-modal]');
const closeDriverModalButtons = document.querySelectorAll('[data-close-driver-modal]');
const editDriverEntryButtons = document.querySelectorAll('[data-edit-driver-entry]');
const deleteDriverForms = document.querySelectorAll('[data-delete-driver-form]');
const openDriverDeleteButtons = document.querySelectorAll('[data-open-driver-delete]');
const driverActionField = document.querySelector('[data-driver-action-field]');
const driverIdField = document.querySelector('[data-driver-id-field]');
const driverModalTitle = document.querySelector('[data-driver-modal-title]');
const driverSubmitButton = document.querySelector('[data-driver-submit-button]');
const driverForm = document.querySelector('[data-driver-form]');
const driverVehicleSelect = document.querySelector('[data-driver-vehicle-select]');
const driverDeleteModal = document.querySelector('#driver-delete-modal');
const cancelDriverDeleteButton = document.querySelector('[data-cancel-driver-delete]');
const confirmDriverDeleteButton = document.querySelector('[data-confirm-driver-delete]');
const maintenanceModal = document.querySelector('#maintenance-modal');
const openMaintenanceModalButtons = document.querySelectorAll('[data-open-maintenance-modal]');
const closeMaintenanceModalButtons = document.querySelectorAll('[data-close-maintenance-modal]');
const editMaintenanceEntryButtons = document.querySelectorAll('[data-edit-maintenance-entry]');
const deleteMaintenanceForms = document.querySelectorAll('[data-delete-maintenance-form]');
const openMaintenanceDeleteButtons = document.querySelectorAll('[data-open-maintenance-delete]');
const maintenanceActionField = document.querySelector('[data-maintenance-action-field]');
const maintenanceRecordIdField = document.querySelector('[data-maintenance-record-id-field]');
const maintenanceModalTitle = document.querySelector('[data-maintenance-modal-title]');
const maintenanceSubmitButton = document.querySelector('[data-maintenance-submit-button]');
const maintenanceForm = document.querySelector('[data-maintenance-form]');
const maintenanceDeleteModal = document.querySelector('#maintenance-delete-modal');
const cancelMaintenanceDeleteButton = document.querySelector('[data-cancel-maintenance-delete]');
const confirmMaintenanceDeleteButton = document.querySelector('[data-confirm-maintenance-delete]');
const preInspectionModal = document.querySelector('#pre-inspection-modal');
const openPreInspectionModalButtons = document.querySelectorAll('[data-open-pre-inspection-modal]');
const closePreInspectionModalButtons = document.querySelectorAll('[data-close-pre-inspection-modal]');
const postInspectionModal = document.querySelector('#post-inspection-modal');
const openPostInspectionModalButtons = document.querySelectorAll('[data-open-post-inspection-modal]');
const closePostInspectionModalButtons = document.querySelectorAll('[data-close-post-inspection-modal]');
const providerModal = document.querySelector('#provider-modal');
const openProviderModalButtons = document.querySelectorAll('[data-open-provider-modal]');
const closeProviderModalButtons = document.querySelectorAll('[data-close-provider-modal]');
const estateViewModal = document.querySelector('#estate-view-modal');
const openEstateViewModalButtons = document.querySelectorAll('[data-open-estate-view-modal]');
const closeEstateViewModalButtons = document.querySelectorAll('[data-close-estate-view-modal]');
const estateEditModal = document.querySelector('#estate-edit-modal');
const openEstateEditModalButtons = document.querySelectorAll('[data-open-estate-edit-modal]');
const closeEstateEditModalButtons = document.querySelectorAll('[data-close-estate-edit-modal]');
const estateEditProgress = document.querySelector('[data-estate-edit-progress]');
const estateEditProgressLabel = document.querySelector('[data-estate-edit-progress-label]');
const estateNewModal = document.querySelector('#estate-new-modal');
const openEstateNewModalButtons = document.querySelectorAll('[data-open-estate-new-modal]');
const closeEstateNewModalButtons = document.querySelectorAll('[data-close-estate-new-modal]');
const estateNewProgress = document.querySelector('[data-estate-new-progress]');
const estateNewProgressLabel = document.querySelector('[data-estate-new-progress-label]');

// Vehicle page modal behavior
// Opens or closes the vehicle modal and keeps focus/scroll state in sync.
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

if (vehicleModal?.dataset.openOnLoad === 'true') {
  // When the server returns the page with the modal already open, keep body scroll locked.
  document.body.classList.add('overflow-hidden');
}

// Shared flash popup/toast behavior
const flashNotice = document.querySelector('[data-flash-notice]');
const dismissFlashButton = document.querySelector('[data-dismiss-flash]');
const flashProgress = document.querySelector('[data-flash-progress]');
let flashNoticeTimerId;

// Animates the popup toast out and removes it from the DOM after the transition.
function dismissFlashNotice() {
  if (!flashNotice) {
    return;
  }

  flashNotice.classList.remove('opacity-100', 'translate-y-0', 'scale-100');
  flashNotice.classList.add('-translate-y-2', 'opacity-0', 'scale-95');

  window.clearTimeout(flashNoticeTimerId);
  window.setTimeout(() => {
    flashNotice.remove();
  }, 500);
}

if (flashNotice) {
  // Render the flash message as a popup toast so the user clearly notices the result.
  flashNotice.classList.remove('hidden');
  flashNotice.classList.add('opacity-0', '-translate-y-3', 'scale-95');

  window.setTimeout(() => {
    flashNotice.classList.remove('opacity-0', '-translate-y-3', 'scale-95');
    flashNotice.classList.add('opacity-100', 'translate-y-0', 'scale-100');
  }, 20);

  if (flashProgress) {
    flashProgress.style.transition = 'transform 5s linear';
    window.setTimeout(() => {
      flashProgress.style.transform = 'scaleX(0)';
    }, 50);
  }

  flashNoticeTimerId = window.setTimeout(dismissFlashNotice, 5000);
}

dismissFlashButton?.addEventListener('click', dismissFlashNotice);

// Vehicle page keyboard handling
document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape' && vehicleModal && !vehicleModal.classList.contains('hidden')) {
    setVehicleModalOpen(false);
  }
});

// Logbook modal, edit, and delete behavior
// Opens or closes the logbook modal and keeps focus/scroll state in sync.
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

let pendingLogbookDeleteForm = null;

// Opens or closes the custom logbook delete confirmation modal.
function setLogbookDeleteModalOpen(isOpen, form = null) {
  if (!logbookDeleteModal) {
    return;
  }

  pendingLogbookDeleteForm = isOpen ? form : null;
  logbookDeleteModal.classList.toggle('is-open', isOpen);
  logbookDeleteModal.setAttribute('aria-hidden', String(!isOpen));
  document.body.classList.toggle('overflow-hidden', isOpen);

  if (isOpen) {
    confirmLogbookDeleteButton?.focus();
  }
}

// Switches the shared logbook modal between create mode and edit mode.
function setLogbookFormMode(mode) {
  if (logbookActionField) {
    logbookActionField.value = mode === 'update' ? 'update' : 'create';
  }

  if (logbookModalTitle) {
    logbookModalTitle.textContent = mode === 'update' ? 'Edit Vehicle Log Entry' : 'New Vehicle Log Entry';
  }

  if (logbookSubmitButton) {
    logbookSubmitButton.textContent = mode === 'update' ? 'Save Changes' : 'Create Log Entry';
  }
}

// Writes a value into a single field inside the logbook modal if it exists.
function setLogbookFieldValue(selector, value) {
  const field = logbookModal?.querySelector(selector);

  if (field) {
    field.value = value;
  }
}

// Converts a table date like dd/mm/yyyy into the yyyy-mm-dd format required by date inputs.
function convertDisplayDateToInputValue(value) {
  if (!value || !value.includes('/')) {
    return value || '';
  }

  const [day, month, year] = value.split('/');
  return `${year}-${month}-${day}`;
}

// Removes currency and placeholder formatting before values are put back into numeric inputs.
function convertDisplayNumber(value) {
  if (!value || value === '-') {
    return '';
  }

  return String(value).replace(/[^\d.]/g, '');
}

// Reads the clicked logbook row and pre-fills the shared modal for editing.
function populateLogbookEditForm(button) {
  const row = button.closest('.logbook-row');
  if (!row) {
    return;
  }

  setLogbookFormMode('update');

  if (logbookEntryIdField) {
    logbookEntryIdField.value = row.dataset.entryId || '';
  }

  setLogbookFieldValue('input[name="date"]', convertDisplayDateToInputValue(row.dataset.date || ''));
  setLogbookFieldValue('select[name="vehicle"]', row.dataset.vehicleId || '');
  setLogbookFieldValue('select[name="driver"]', row.dataset.driverId || 'unassigned');
  setLogbookFieldValue('input[name="departure_location"]', row.dataset.from || '');
  setLogbookFieldValue('input[name="destination"]', row.dataset.to || '');
  setLogbookFieldValue('textarea[name="purpose"]', row.dataset.purpose || '');
  setLogbookFieldValue('input[name="odometer_start"]', convertDisplayNumber(row.dataset.odoStart || ''));
  setLogbookFieldValue('input[name="odometer_end"]', convertDisplayNumber(row.dataset.odoEnd || ''));
  setLogbookFieldValue('input[name="fuel_litres"]', convertDisplayNumber(row.dataset.fuel || ''));
  setLogbookFieldValue('input[name="fuel_cost"]', convertDisplayNumber(row.dataset.cost || ''));
  setLogbookFieldValue('input[name="remarks"]', row.dataset.remarks === '-' ? '' : (row.dataset.remarks || ''));
}

openLogbookModalButtons.forEach((button) => {
  button.addEventListener('click', () => {
    // Fresh opens reset the shared modal into create mode.
    setLogbookFormMode('create');
    if (logbookEntryIdField) {
      logbookEntryIdField.value = '';
    }
    setLogbookModalOpen(true);
  });
});

editLogbookEntryButtons.forEach((button) => {
  button.addEventListener('click', () => {
    populateLogbookEditForm(button);
    setLogbookModalOpen(true);
  });
});

openLogbookDeleteButtons.forEach((button) => {
  button.addEventListener('click', (event) => {
    event.preventDefault();
    const form = button.closest('form');
    if (form) {
      setLogbookDeleteModalOpen(true, form);
    }
  });
});

closeLogbookModalButtons.forEach((button) => {
  button.addEventListener('click', () => setLogbookModalOpen(false));
});

logbookModal?.addEventListener('click', (event) => {
  if (event.target === logbookModal) {
    setLogbookModalOpen(false);
  }
});

if (logbookModal?.dataset.openOnLoad === 'true') {
  // When the server returns the page with the logbook modal already open, preserve scroll lock.
  document.body.classList.add('overflow-hidden');
}

confirmLogbookDeleteButton?.addEventListener('click', () => {
  if (!pendingLogbookDeleteForm) {
    setLogbookDeleteModalOpen(false);
    return;
  }

  const form = pendingLogbookDeleteForm;
  setLogbookDeleteModalOpen(false);
  form.submit();
});

cancelLogbookDeleteButton?.addEventListener('click', () => {
  setLogbookDeleteModalOpen(false);
});

logbookDeleteModal?.addEventListener('click', (event) => {
  if (event.target === logbookDeleteModal) {
    setLogbookDeleteModalOpen(false);
  }
});

document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape' && logbookModal && !logbookModal.classList.contains('hidden')) {
    setLogbookModalOpen(false);
  }

  if (event.key === 'Escape' && logbookDeleteModal && logbookDeleteModal.classList.contains('is-open')) {
    setLogbookDeleteModalOpen(false);
  }
});

// Driver modal, edit, assignment, and delete behavior
// Opens or closes the driver modal and keeps focus/scroll state in sync.
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

// Switches the shared driver modal between create mode and edit mode.
function setDriverFormMode(mode) {
  if (driverActionField) {
    driverActionField.value = mode === 'update' ? 'update' : 'create';
  }

  if (driverModalTitle) {
    driverModalTitle.textContent = mode === 'update' ? 'Edit Driver' : 'Add New Driver';
  }

  if (driverSubmitButton) {
    driverSubmitButton.textContent = mode === 'update' ? 'Save Changes' : 'Add Driver';
  }
}

// Writes a value into a single field inside the driver modal if it exists.
function setDriverFieldValue(selector, value) {
  const field = driverModal?.querySelector(selector);

  if (field) {
    field.value = value;
  }
}

// Disables vehicles already assigned to a different driver while leaving the current driver's vehicle selectable.
function setDriverVehicleAvailability(currentDriverId = '') {
  if (!driverVehicleSelect) {
    return;
  }

  Array.from(driverVehicleSelect.options).forEach((option) => {
    const assignedDriverId = option.dataset.assignedDriverId || '';

    if (option.value === 'unassigned') {
      option.disabled = false;
      return;
    }

    option.disabled = assignedDriverId !== '' && assignedDriverId !== String(currentDriverId);
  });
}

// Resets the shared driver modal back to a clean create state.
function resetDriverFormForCreate() {
  driverForm?.reset();

  if (driverIdField) {
    driverIdField.value = '';
  }

  setDriverFormMode('create');
  setDriverFieldValue('select[name="assigned_vehicle"]', 'unassigned');
  setDriverFieldValue('select[name="status"]', 'active');
  setDriverVehicleAvailability('');
}

// Reads the clicked driver row and pre-fills the shared modal for editing.
function populateDriverEditForm(button) {
  const row = button.closest('.driver-row');
  if (!row) {
    return;
  }

  setDriverFormMode('update');

  if (driverIdField) {
    driverIdField.value = row.dataset.driverId || '';
  }

  setDriverFieldValue('input[name="full_name"]', row.dataset.fullName || '');
  setDriverFieldValue('input[name="employee_id"]', row.dataset.employeeId || '');
  setDriverFieldValue('input[name="phone"]', row.dataset.phone || '');
  setDriverFieldValue('input[name="email"]', row.dataset.email || '');
  setDriverFieldValue('input[name="license_number"]', row.dataset.licenseNumber || '');
  setDriverFieldValue('input[name="license_classes"]', row.dataset.licenseClasses || '');
  setDriverFieldValue('input[name="license_expiry"]', row.dataset.licenseExpiry || '');
  setDriverFieldValue('input[name="department"]', row.dataset.department || '');
  setDriverFieldValue('select[name="assigned_vehicle"]', row.dataset.assignedVehicleId || 'unassigned');
  setDriverFieldValue('select[name="status"]', row.dataset.status || 'active');
  setDriverVehicleAvailability(row.dataset.driverId || '');
}

let pendingDriverDeleteForm = null;

// Opens or closes the custom driver delete confirmation modal.
function setDriverDeleteModalOpen(isOpen, form = null) {
  if (!driverDeleteModal) {
    return;
  }

  pendingDriverDeleteForm = isOpen ? form : null;
  driverDeleteModal.classList.toggle('is-open', isOpen);
  driverDeleteModal.setAttribute('aria-hidden', String(!isOpen));
  document.body.classList.toggle('overflow-hidden', isOpen);

  if (isOpen) {
    confirmDriverDeleteButton?.focus();
  }
}

openDriverModalButtons.forEach((button) => {
  button.addEventListener('click', () => {
    resetDriverFormForCreate();
    setDriverModalOpen(true);
  });
});

editDriverEntryButtons.forEach((button) => {
  button.addEventListener('click', () => {
    populateDriverEditForm(button);
    setDriverModalOpen(true);
  });
});

openDriverDeleteButtons.forEach((button) => {
  button.addEventListener('click', (event) => {
    event.preventDefault();
    const form = button.closest('form');

    if (form) {
      setDriverDeleteModalOpen(true, form);
    }
  });
});

closeDriverModalButtons.forEach((button) => {
  button.addEventListener('click', () => setDriverModalOpen(false));
});

driverModal?.addEventListener('click', (event) => {
  if (event.target === driverModal) {
    setDriverModalOpen(false);
  }
});

if (driverModal?.dataset.openOnLoad === 'true') {
  // Preserve scroll locking when the server reopens the modal after a failed submission.
  document.body.classList.add('overflow-hidden');
  setDriverVehicleAvailability(driverIdField?.value || '');
}

confirmDriverDeleteButton?.addEventListener('click', () => {
  if (!pendingDriverDeleteForm) {
    setDriverDeleteModalOpen(false);
    return;
  }

  const form = pendingDriverDeleteForm;
  setDriverDeleteModalOpen(false);
  form.submit();
});

cancelDriverDeleteButton?.addEventListener('click', () => {
  setDriverDeleteModalOpen(false);
});

driverDeleteModal?.addEventListener('click', (event) => {
  if (event.target === driverDeleteModal) {
    setDriverDeleteModalOpen(false);
  }
});

document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape' && driverModal && !driverModal.classList.contains('hidden')) {
    setDriverModalOpen(false);
  }

  if (event.key === 'Escape' && driverDeleteModal && driverDeleteModal.classList.contains('is-open')) {
    setDriverDeleteModalOpen(false);
  }
});

// Maintenance modal behavior
// Opens or closes the maintenance modal and keeps focus/scroll state in sync.
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

// Switches the shared maintenance modal between create mode and edit mode.
function setMaintenanceFormMode(mode) {
  if (maintenanceActionField) {
    maintenanceActionField.value = mode === 'update' ? 'update' : 'create';
  }

  if (maintenanceModalTitle) {
    maintenanceModalTitle.textContent = mode === 'update' ? 'Edit Maintenance Record' : 'New Maintenance Record';
  }

  if (maintenanceSubmitButton) {
    maintenanceSubmitButton.textContent = mode === 'update' ? 'Save Changes' : 'Create Record';
  }
}

// Writes a value into a single field inside the maintenance modal if it exists.
function setMaintenanceFieldValue(selector, value) {
  const field = maintenanceModal?.querySelector(selector);

  if (field) {
    field.value = value;
  }
}

// Resets the shared maintenance modal back to a clean create state.
function resetMaintenanceFormForCreate() {
  maintenanceForm?.reset();

  if (maintenanceRecordIdField) {
    maintenanceRecordIdField.value = '';
  }

  setMaintenanceFormMode('create');
  setMaintenanceFieldValue('select[name="status"]', 'reported');
  setMaintenanceFieldValue('select[name="maintenance_type"]', 'repair');
  setMaintenanceFieldValue('input[name="date_reported"]', new Date().toISOString().slice(0, 10));
}

// Reads the clicked maintenance row and pre-fills the shared modal for editing.
function populateMaintenanceEditForm(button) {
  const row = button.closest('.maintenance-row');
  if (!row) {
    return;
  }

  setMaintenanceFormMode('update');

  if (maintenanceRecordIdField) {
    maintenanceRecordIdField.value = row.dataset.recordId || '';
  }

  setMaintenanceFieldValue('select[name="vehicle"]', row.dataset.vehicleId || '');
  setMaintenanceFieldValue('select[name="maintenance_type"]', row.dataset.type || 'repair');
  setMaintenanceFieldValue('input[name="date_reported"]', row.dataset.dateReported || '');
  setMaintenanceFieldValue('input[name="date_completed"]', row.dataset.dateCompleted || '');
  setMaintenanceFieldValue('textarea[name="description"]', row.dataset.description || '');
  setMaintenanceFieldValue('select[name="service_provider"]', row.dataset.serviceProviderId || '');
  setMaintenanceFieldValue('input[name="parts_replaced"]', row.dataset.partsReplaced || '');
  setMaintenanceFieldValue('input[name="total_cost"]', row.dataset.totalCost || '');
  setMaintenanceFieldValue('input[name="mileage_at_service"]', row.dataset.mileageAtService || '');
  setMaintenanceFieldValue('input[name="invoice_number"]', row.dataset.invoiceNumber || '');
  setMaintenanceFieldValue('select[name="status"]', row.dataset.statusValue || 'reported');
  setMaintenanceFieldValue('input[name="remarks"]', row.dataset.remarks || '');
}

let pendingMaintenanceDeleteForm = null;

// Opens or closes the custom maintenance delete confirmation modal.
function setMaintenanceDeleteModalOpen(isOpen, form = null) {
  if (!maintenanceDeleteModal) {
    return;
  }

  pendingMaintenanceDeleteForm = isOpen ? form : null;
  maintenanceDeleteModal.classList.toggle('is-open', isOpen);
  maintenanceDeleteModal.setAttribute('aria-hidden', String(!isOpen));
  document.body.classList.toggle('overflow-hidden', isOpen);

  if (isOpen) {
    confirmMaintenanceDeleteButton?.focus();
  }
}

openMaintenanceModalButtons.forEach((button) => {
  button.addEventListener('click', () => {
    resetMaintenanceFormForCreate();
    setMaintenanceModalOpen(true);
  });
});

editMaintenanceEntryButtons.forEach((button) => {
  button.addEventListener('click', () => {
    populateMaintenanceEditForm(button);
    setMaintenanceModalOpen(true);
  });
});

openMaintenanceDeleteButtons.forEach((button) => {
  button.addEventListener('click', (event) => {
    event.preventDefault();
    const form = button.closest('form');

    if (form) {
      setMaintenanceDeleteModalOpen(true, form);
    }
  });
});

closeMaintenanceModalButtons.forEach((button) => {
  button.addEventListener('click', () => setMaintenanceModalOpen(false));
});

maintenanceModal?.addEventListener('click', (event) => {
  if (event.target === maintenanceModal) {
    setMaintenanceModalOpen(false);
  }
});

if (maintenanceModal?.dataset.openOnLoad === 'true') {
  document.body.classList.add('overflow-hidden');
}

confirmMaintenanceDeleteButton?.addEventListener('click', () => {
  if (!pendingMaintenanceDeleteForm) {
    setMaintenanceDeleteModalOpen(false);
    return;
  }

  const form = pendingMaintenanceDeleteForm;
  setMaintenanceDeleteModalOpen(false);
  form.submit();
});

cancelMaintenanceDeleteButton?.addEventListener('click', () => {
  setMaintenanceDeleteModalOpen(false);
});

maintenanceDeleteModal?.addEventListener('click', (event) => {
  if (event.target === maintenanceDeleteModal) {
    setMaintenanceDeleteModalOpen(false);
  }
});

document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape' && maintenanceModal && !maintenanceModal.classList.contains('hidden')) {
    setMaintenanceModalOpen(false);
  }

  if (event.key === 'Escape' && maintenanceDeleteModal && maintenanceDeleteModal.classList.contains('is-open')) {
    setMaintenanceDeleteModalOpen(false);
  }
});

// Pre-inspection modal behavior
// Opens or closes the pre-inspection modal and keeps focus/scroll state in sync.
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

// Post-inspection modal behavior
// Opens or closes the post-inspection modal and keeps focus/scroll state in sync.
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

// Provider modal behavior
// Opens or closes the provider modal and keeps focus/scroll state in sync.
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

// Estates page modal helpers and modal behavior
const estateStatusClassMap = {
  // Mirrors the PHP status badge map so dynamically populated modal badges match the cards.
  'In Progress': 'border-fleet-warning bg-fleet-warning-soft text-fleet-warning-strong',
  Approved: 'border-blue-300 bg-blue-100 text-fleet-primary',
  'On Hold': 'border-orange-300 bg-orange-100 text-orange-700',
  Completed: 'border-green-300 bg-fleet-success-soft text-fleet-success',
  Planned: 'border-slate-300 bg-slate-100 text-slate-700',
};

const estatePriorityClassMap = {
  // Mirrors the PHP priority badge map used by the visible project cards.
  High: 'border-orange-300 bg-orange-50 text-orange-700',
  Medium: 'border-yellow-300 bg-yellow-50 text-yellow-700',
  Low: 'border-slate-300 bg-slate-50 text-slate-700',
};

// Writes text into a read-only estate modal field when the target exists.
function setEstateText(selector, value) {
  const element = estateViewModal?.querySelector(selector);

  if (element) {
    element.textContent = value || '';
  }
}

// Rebuilds an estate status/priority badge so its colors match the selected project state.
function setEstateBadge(selector, value, classes) {
  const element = estateViewModal?.querySelector(selector);

  if (!element) {
    return;
  }

  element.className = `rounded-lg border px-3 py-1 text-sm font-semibold ${classes}`;
  element.textContent = value || '';
}

// Reads the selected estate card and fills the read-only view modal.
function populateEstateViewModal(projectCard) {
  if (!projectCard) {
    return;
  }

  const progress = projectCard.dataset.progress || '0';
  const status = projectCard.dataset.status || '';
  const priority = projectCard.dataset.priority || '';

  setEstateText('[data-estate-view-name]', projectCard.dataset.name);
  setEstateText('[data-estate-view-code]', projectCard.dataset.code);
  setEstateText('[data-estate-view-category]', projectCard.dataset.category);
  setEstateText('[data-estate-view-location]', projectCard.dataset.location);
  setEstateText('[data-estate-view-contractor]', projectCard.dataset.contractor);
  setEstateText('[data-estate-view-start]', projectCard.dataset.start);
  setEstateText('[data-estate-view-deadline]', projectCard.dataset.deadline);
  setEstateText('[data-estate-view-funding]', projectCard.dataset.funding);
  setEstateText('[data-estate-view-progress]', progress);
  setEstateText('[data-estate-view-budget]', projectCard.dataset.budget);
  setEstateText('[data-estate-view-spent]', projectCard.dataset.spent);
  setEstateText('[data-estate-view-remaining]', projectCard.dataset.remaining);
  setEstateText('[data-estate-view-description]', projectCard.dataset.description);
  setEstateBadge('[data-estate-view-status]', status, estateStatusClassMap[status] || 'border-slate-300 bg-slate-100 text-slate-700');
  setEstateBadge('[data-estate-view-priority]', priority, estatePriorityClassMap[priority] || 'border-slate-300 bg-slate-50 text-slate-700');

  const progressBar = estateViewModal?.querySelector('[data-estate-view-progress-bar]');
  if (progressBar) {
    progressBar.style.width = `${progress}%`;
  }
}

// Opens or closes the estate view modal and optionally loads card details into it.
function setEstateViewModalOpen(isOpen, projectCard = null) {
  if (!estateViewModal) {
    return;
  }

  if (isOpen) {
    populateEstateViewModal(projectCard);
  }

  estateViewModal.classList.toggle('hidden', !isOpen);
  estateViewModal.classList.toggle('flex', isOpen);
  estateViewModal.setAttribute('aria-hidden', String(!isOpen));
  document.body.classList.toggle('overflow-hidden', isOpen);

  if (isOpen) {
    estateViewModal.querySelector('button')?.focus();
  } else {
    openEstateViewModalButtons[0]?.focus();
  }
}

openEstateViewModalButtons.forEach((button) => {
  button.addEventListener('click', () => {
    setEstateViewModalOpen(true, button.closest('.estate-project-card'));
  });
});

closeEstateViewModalButtons.forEach((button) => {
  button.addEventListener('click', () => setEstateViewModalOpen(false));
});

estateViewModal?.addEventListener('click', (event) => {
  if (event.target === estateViewModal) {
    setEstateViewModalOpen(false);
  }
});

document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape' && estateViewModal && !estateViewModal.classList.contains('hidden')) {
    setEstateViewModalOpen(false);
  }
});

// Writes a value into a single field inside the estate edit modal if it exists.
function setEstateInput(selector, value) {
  const element = estateEditModal?.querySelector(selector);

  if (element) {
    element.value = value || '';
  }
}

// Removes currency formatting so displayed amounts can be reused in numeric inputs.
function stripCurrency(value) {
  return (value || '').replace(/[^\d]/g, '');
}

// Reads the selected estate card and pre-fills the edit modal.
function populateEstateEditModal(projectCard) {
  if (!projectCard) {
    return;
  }

  const progress = projectCard.dataset.progress || '0';

  setEstateInput('[data-estate-edit-name]', projectCard.dataset.name);
  setEstateInput('[data-estate-edit-code]', projectCard.dataset.code);
  setEstateInput('[data-estate-edit-category]', projectCard.dataset.category);
  setEstateInput('[data-estate-edit-location]', projectCard.dataset.location);
  setEstateInput('[data-estate-edit-status]', projectCard.dataset.status);
  setEstateInput('[data-estate-edit-priority]', projectCard.dataset.priority);
  setEstateInput('[data-estate-edit-start]', projectCard.dataset.start);
  setEstateInput('[data-estate-edit-deadline]', projectCard.dataset.deadline);
  setEstateInput('[data-estate-edit-budget]', stripCurrency(projectCard.dataset.budget));
  setEstateInput('[data-estate-edit-spent]', stripCurrency(projectCard.dataset.spent));
  setEstateInput('[data-estate-edit-progress]', progress);
  setEstateInput('[data-estate-edit-contractor]', projectCard.dataset.contractor);
  setEstateInput('[data-estate-edit-contractor-contact]', projectCard.dataset.contractorContact);
  setEstateInput('[data-estate-edit-manager]', projectCard.dataset.contractor);
  setEstateInput('[data-estate-edit-funding]', projectCard.dataset.funding);
  setEstateInput('[data-estate-edit-description]', projectCard.dataset.description);

  if (estateEditProgressLabel) {
    estateEditProgressLabel.textContent = progress;
  }
}

// Opens or closes the estate edit modal and optionally loads card details into it.
function setEstateEditModalOpen(isOpen, projectCard = null) {
  if (!estateEditModal) {
    return;
  }

  if (isOpen) {
    populateEstateEditModal(projectCard);
  }

  estateEditModal.classList.toggle('hidden', !isOpen);
  estateEditModal.classList.toggle('flex', isOpen);
  estateEditModal.setAttribute('aria-hidden', String(!isOpen));
  document.body.classList.toggle('overflow-hidden', isOpen);

  if (isOpen) {
    estateEditModal.querySelector('input, select, textarea, button')?.focus();
  } else {
    openEstateEditModalButtons[0]?.focus();
  }
}

openEstateEditModalButtons.forEach((button) => {
  button.addEventListener('click', () => {
    setEstateEditModalOpen(true, button.closest('.estate-project-card'));
  });
});

closeEstateEditModalButtons.forEach((button) => {
  button.addEventListener('click', () => setEstateEditModalOpen(false));
});

estateEditModal?.addEventListener('click', (event) => {
  if (event.target === estateEditModal) {
    setEstateEditModalOpen(false);
  }
});

estateEditProgress?.addEventListener('input', (event) => {
  if (estateEditProgressLabel) {
    estateEditProgressLabel.textContent = event.target.value;
  }
});

document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape' && estateEditModal && !estateEditModal.classList.contains('hidden')) {
    setEstateEditModalOpen(false);
  }
});

// Opens or closes the estate new-project modal.
function setEstateNewModalOpen(isOpen) {
  if (!estateNewModal) {
    return;
  }

  estateNewModal.classList.toggle('hidden', !isOpen);
  estateNewModal.classList.toggle('flex', isOpen);
  estateNewModal.setAttribute('aria-hidden', String(!isOpen));
  document.body.classList.toggle('overflow-hidden', isOpen);

  if (isOpen) {
    estateNewModal.querySelector('input, select, textarea, button')?.focus();
  } else {
    openEstateNewModalButtons[0]?.focus();
  }
}

openEstateNewModalButtons.forEach((button) => {
  button.addEventListener('click', () => setEstateNewModalOpen(true));
});

closeEstateNewModalButtons.forEach((button) => {
  button.addEventListener('click', () => setEstateNewModalOpen(false));
});

estateNewModal?.addEventListener('click', (event) => {
  if (event.target === estateNewModal) {
    setEstateNewModalOpen(false);
  }
});

estateNewProgress?.addEventListener('input', (event) => {
  if (estateNewProgressLabel) {
    estateNewProgressLabel.textContent = event.target.value;
  }
});

document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape' && estateNewModal && !estateNewModal.classList.contains('hidden')) {
    setEstateNewModalOpen(false);
  }
});
