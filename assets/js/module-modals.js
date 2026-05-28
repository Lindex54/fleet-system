// Module modal references for pre-inspection, logbook, drivers, and maintenance pages
const preInspectionModal = document.querySelector('#pre-inspection-modal');
const openPreInspectionModalButtons = document.querySelectorAll('[data-open-pre-inspection-modal]');
const closePreInspectionModalButtons = document.querySelectorAll('[data-close-pre-inspection-modal]');
const editPreInspectionEntryButtons = document.querySelectorAll('[data-edit-pre-inspection-entry]');
const openPreInspectionDeleteButtons = document.querySelectorAll('[data-open-pre-inspection-delete]');
const preInspectionActionField = document.querySelector('[data-pre-inspection-action-field]');
const preInspectionReportIdField = document.querySelector('[data-pre-inspection-report-id-field]');
const preInspectionModalTitle = document.querySelector('[data-pre-inspection-modal-title]');
const preInspectionSubmitButton = document.querySelector('[data-pre-inspection-submit-button]');
const preInspectionForm = document.querySelector('[data-pre-inspection-form]');
const preInspectionItemsContainer = document.querySelector('[data-pre-inspection-items]');
const preInspectionAddItemButton = document.querySelector('[data-add-pre-inspection-item]');
const preInspectionItemTemplate = document.querySelector('#pre-inspection-item-template');
const preInspectionDeleteModal = document.querySelector('#pre-inspection-delete-modal');
const cancelPreInspectionDeleteButton = document.querySelector('[data-cancel-pre-inspection-delete]');
const confirmPreInspectionDeleteButton = document.querySelector('[data-confirm-pre-inspection-delete]');

const logbookModal = document.querySelector('#logbook-modal');
const openLogbookModalButtons = document.querySelectorAll('[data-open-logbook-modal]');
const closeLogbookModalButtons = document.querySelectorAll('[data-close-logbook-modal]');
const editLogbookEntryButtons = document.querySelectorAll('[data-edit-logbook-entry]');
const logbookActionField = document.querySelector('[data-logbook-action-field]');
const logbookEntryIdField = document.querySelector('[data-logbook-entry-id-field]');
const logbookModalTitle = document.querySelector('[data-logbook-modal-title]');
const logbookSubmitButton = document.querySelector('[data-logbook-submit-button]');
const openLogbookDeleteButtons = document.querySelectorAll('[data-open-logbook-delete]');
const logbookDeleteModal = document.querySelector('#logbook-delete-modal');
const cancelLogbookDeleteButton = document.querySelector('[data-cancel-logbook-delete]');
const confirmLogbookDeleteButton = document.querySelector('[data-confirm-logbook-delete]');

const driverModal = document.querySelector('#driver-modal');
const openDriverModalButtons = document.querySelectorAll('[data-open-driver-modal]');
const closeDriverModalButtons = document.querySelectorAll('[data-close-driver-modal]');
const editDriverEntryButtons = document.querySelectorAll('[data-edit-driver-entry]');
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
const openMaintenanceDeleteButtons = document.querySelectorAll('[data-open-maintenance-delete]');
const maintenanceActionField = document.querySelector('[data-maintenance-action-field]');
const maintenanceRecordIdField = document.querySelector('[data-maintenance-record-id-field]');
const maintenanceModalTitle = document.querySelector('[data-maintenance-modal-title]');
const maintenanceSubmitButton = document.querySelector('[data-maintenance-submit-button]');
const maintenanceForm = document.querySelector('[data-maintenance-form]');
const maintenanceDeleteModal = document.querySelector('#maintenance-delete-modal');
const cancelMaintenanceDeleteButton = document.querySelector('[data-cancel-maintenance-delete]');
const confirmMaintenanceDeleteButton = document.querySelector('[data-confirm-maintenance-delete]');

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

// Switches the shared pre-inspection modal between create mode and edit mode.
function setPreInspectionFormMode(mode) {
  if (preInspectionActionField) {
    preInspectionActionField.value = mode === 'update' ? 'update' : 'create';
  }

  if (preInspectionModalTitle) {
    preInspectionModalTitle.textContent = mode === 'update' ? 'Edit Pre-Inspection Report' : 'New Pre-Inspection Report';
  }

  if (preInspectionSubmitButton) {
    preInspectionSubmitButton.textContent = mode === 'update' ? 'Save Changes' : 'Save Report';
  }
}

// Writes a value into a single field inside the pre-inspection modal if it exists.
function setPreInspectionFieldValue(selector, value) {
  const field = preInspectionModal?.querySelector(selector);

  if (field) {
    field.value = value;
  }
}

// Re-numbers the repeated inspection item cards so the labels stay clear after edits.
function updatePreInspectionItemLabels() {
  const itemRows = preInspectionItemsContainer?.querySelectorAll('[data-pre-inspection-item-row]') || [];

  itemRows.forEach((row, index) => {
    const label = row.querySelector('[data-pre-inspection-item-label]') || row.querySelector('p');

    if (label) {
      label.textContent = `Item ${index + 1}`;
    }
  });
}

// Creates one inspection item card and optionally fills it with existing row values.
function appendPreInspectionItemRow(item = {}) {
  if (!preInspectionItemsContainer || !preInspectionItemTemplate) {
    return;
  }

  const fragment = preInspectionItemTemplate.content.cloneNode(true);
  const row = fragment.querySelector('[data-pre-inspection-item-row]');

  if (!row) {
    return;
  }

  const pointField = row.querySelector('input[name="inspection_point[]"]');
  const findingsField = row.querySelector('textarea[name="inspection_findings[]"]');
  const actionField = row.querySelector('textarea[name="inspection_action[]"]');

  if (pointField) {
    pointField.value = item.inspection_point || '';
  }

  if (findingsField) {
    findingsField.value = item.inspection_findings || '';
  }

  if (actionField) {
    actionField.value = item.inspection_action || '';
  }

  preInspectionItemsContainer.append(row);
  updatePreInspectionItemLabels();
}

// Clears and rebuilds the inspection item list from either defaults or saved row data.
function renderPreInspectionItems(items = []) {
  if (!preInspectionItemsContainer) {
    return;
  }

  preInspectionItemsContainer.innerHTML = '';
  const rows = items.length > 0 ? items : [{ inspection_point: '', inspection_findings: '', inspection_action: '' }];
  rows.forEach((item) => appendPreInspectionItemRow(item));
}

// Resets the shared pre-inspection modal back to a clean create state.
function resetPreInspectionFormForCreate() {
  preInspectionForm?.reset();

  if (preInspectionReportIdField) {
    preInspectionReportIdField.value = '';
  }

  setPreInspectionFormMode('create');
  setPreInspectionFieldValue('input[name="inspection_date"]', new Date().toISOString().slice(0, 10));
  setPreInspectionFieldValue('input[name="memo_to"]', 'University Secretary');
  setPreInspectionFieldValue('input[name="memo_thru_one"]', 'University Bursar');
  setPreInspectionFieldValue('input[name="memo_thru_two"]', 'Programme Controller');
  setPreInspectionFieldValue('textarea[name="closing_note"]', 'The purpose of this report is to therefore request you authorize repair and maintenance works on this vehicle for full restoration.');
  setPreInspectionFieldValue('input[name="cc"]', 'Senior Estates Officer');
  renderPreInspectionItems();
}

// Reads the clicked pre-inspection row and pre-fills the shared modal for editing.
function populatePreInspectionEditForm(button) {
  const row = button.closest('.pre-inspection-row');
  if (!row) {
    return;
  }

  setPreInspectionFormMode('update');

  if (preInspectionReportIdField) {
    preInspectionReportIdField.value = row.dataset.reportId || '';
  }

  setPreInspectionFieldValue('input[name="invoice_number"]', row.dataset.invoiceNumber || '');
  setPreInspectionFieldValue('input[name="inspection_date"]', row.dataset.inspectionDate || '');
  setPreInspectionFieldValue('input[name="inspector_name"]', row.dataset.inspectorName || '');
  setPreInspectionFieldValue('input[name="inspector_title"]', row.dataset.inspectorTitle || '');
  setPreInspectionFieldValue('select[name="vehicle"]', row.dataset.vehicleId || '');
  setPreInspectionFieldValue('input[name="mileage"]', row.dataset.mileage || '');
  setPreInspectionFieldValue('select[name="overall_status"]', row.dataset.overallStatus || '');
  setPreInspectionFieldValue('input[name="defects"]', row.dataset.defects || '');
  setPreInspectionFieldValue('input[name="memo_to"]', row.dataset.memoTo || '');
  setPreInspectionFieldValue('input[name="memo_thru_one"]', row.dataset.memoThruOne || '');
  setPreInspectionFieldValue('input[name="memo_thru_two"]', row.dataset.memoThruTwo || '');
  setPreInspectionFieldValue('input[name="memo_from"]', row.dataset.memoFrom || '');
  setPreInspectionFieldValue('input[name="vehicle_description"]', row.dataset.vehicleDescription || '');
  setPreInspectionFieldValue('textarea[name="closing_note"]', row.dataset.closingNote || '');
  setPreInspectionFieldValue('input[name="cc"]', row.dataset.cc || '');

  let items = [];

  try {
    items = JSON.parse(row.dataset.items || '[]');
  } catch (error) {
    items = [];
  }

  renderPreInspectionItems(Array.isArray(items) ? items : []);
}

let pendingPreInspectionDeleteForm = null;

// Opens or closes the custom pre-inspection delete confirmation modal.
function setPreInspectionDeleteModalOpen(isOpen, form = null) {
  if (!preInspectionDeleteModal) {
    return;
  }

  pendingPreInspectionDeleteForm = isOpen ? form : null;
  preInspectionDeleteModal.classList.toggle('is-open', isOpen);
  preInspectionDeleteModal.setAttribute('aria-hidden', String(!isOpen));
  document.body.classList.toggle('overflow-hidden', isOpen);

  if (isOpen) {
    confirmPreInspectionDeleteButton?.focus();
  }
}

openPreInspectionModalButtons.forEach((button) => {
  button.addEventListener('click', () => {
    resetPreInspectionFormForCreate();
    setPreInspectionModalOpen(true);
  });
});

editPreInspectionEntryButtons.forEach((button) => {
  button.addEventListener('click', () => {
    populatePreInspectionEditForm(button);
    setPreInspectionModalOpen(true);
  });
});

openPreInspectionDeleteButtons.forEach((button) => {
  button.addEventListener('click', (event) => {
    event.preventDefault();
    const form = button.closest('form');

    if (form) {
      setPreInspectionDeleteModalOpen(true, form);
    }
  });
});

closePreInspectionModalButtons.forEach((button) => {
  button.addEventListener('click', () => setPreInspectionModalOpen(false));
});

preInspectionAddItemButton?.addEventListener('click', () => {
  appendPreInspectionItemRow();
});

preInspectionItemsContainer?.addEventListener('click', (event) => {
  const removeButton = event.target.closest('[data-remove-pre-inspection-item]');

  if (!removeButton) {
    return;
  }

  const itemRows = preInspectionItemsContainer.querySelectorAll('[data-pre-inspection-item-row]');
  const row = removeButton.closest('[data-pre-inspection-item-row]');

  if (itemRows.length <= 1 || !row) {
    return;
  }

  row.remove();
  updatePreInspectionItemLabels();
});

preInspectionModal?.addEventListener('click', (event) => {
  if (event.target === preInspectionModal) {
    setPreInspectionModalOpen(false);
  }
});

if (preInspectionModal?.dataset.openOnLoad === 'true') {
  document.body.classList.add('overflow-hidden');
  updatePreInspectionItemLabels();
}

confirmPreInspectionDeleteButton?.addEventListener('click', () => {
  if (!pendingPreInspectionDeleteForm) {
    setPreInspectionDeleteModalOpen(false);
    return;
  }

  const form = pendingPreInspectionDeleteForm;
  setPreInspectionDeleteModalOpen(false);
  form.submit();
});

cancelPreInspectionDeleteButton?.addEventListener('click', () => {
  setPreInspectionDeleteModalOpen(false);
});

preInspectionDeleteModal?.addEventListener('click', (event) => {
  if (event.target === preInspectionDeleteModal) {
    setPreInspectionDeleteModalOpen(false);
  }
});

document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape' && preInspectionModal && !preInspectionModal.classList.contains('hidden')) {
    setPreInspectionModalOpen(false);
  }

  if (event.key === 'Escape' && preInspectionDeleteModal && preInspectionDeleteModal.classList.contains('is-open')) {
    setPreInspectionDeleteModalOpen(false);
  }
});

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
