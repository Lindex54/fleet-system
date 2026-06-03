// Module modal references for pre-inspection, post-inspection, providers, logbook, drivers, and maintenance pages
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

const postInspectionModal = document.querySelector('#post-inspection-modal');
const openPostInspectionModalButtons = document.querySelectorAll('[data-open-post-inspection-modal]');
const closePostInspectionModalButtons = document.querySelectorAll('[data-close-post-inspection-modal]');
const editPostInspectionEntryButtons = document.querySelectorAll('[data-edit-post-inspection-entry]');
const openPostInspectionDeleteButtons = document.querySelectorAll('[data-open-post-inspection-delete]');
const postInspectionActionField = document.querySelector('[data-post-inspection-action-field]');
const postInspectionReportIdField = document.querySelector('[data-post-inspection-report-id-field]');
const postInspectionModalTitle = document.querySelector('[data-post-inspection-modal-title]');
const postInspectionSubmitButton = document.querySelector('[data-post-inspection-submit-button]');
const postInspectionForm = document.querySelector('[data-post-inspection-form]');
const postInspectionDeleteModal = document.querySelector('#post-inspection-delete-modal');
const cancelPostInspectionDeleteButton = document.querySelector('[data-cancel-post-inspection-delete]');
const confirmPostInspectionDeleteButton = document.querySelector('[data-confirm-post-inspection-delete]');

const providerModal = document.querySelector('#provider-modal');
const openProviderModalButtons = document.querySelectorAll('[data-open-provider-modal]');
const closeProviderModalButtons = document.querySelectorAll('[data-close-provider-modal]');
const editProviderEntryButtons = document.querySelectorAll('[data-edit-provider-entry]');
const openProviderDeleteButtons = document.querySelectorAll('[data-open-provider-delete]');
const providerActionField = document.querySelector('[data-provider-action-field]');
const providerIdField = document.querySelector('[data-provider-id-field]');
const providerModalTitle = document.querySelector('[data-provider-modal-title]');
const providerSubmitButton = document.querySelector('[data-provider-submit-button]');
const providerForm = document.querySelector('[data-provider-form]');
const providerDeleteModal = document.querySelector('#provider-delete-modal');
const cancelProviderDeleteButton = document.querySelector('[data-cancel-provider-delete]');
const confirmProviderDeleteButton = document.querySelector('[data-confirm-provider-delete]');

const logbookModal = document.querySelector('#logbook-modal');
const openLogbookModalButtons = document.querySelectorAll('[data-open-logbook-modal]');
const closeLogbookModalButtons = document.querySelectorAll('[data-close-logbook-modal]');
const editLogbookEntryButtons = document.querySelectorAll('[data-edit-logbook-entry]');
const logbookActionField = document.querySelector('[data-logbook-action-field]');
const logbookEntryIdField = document.querySelector('[data-logbook-entry-id-field]');
const logbookModalTitle = document.querySelector('[data-logbook-modal-title]');
const logbookSubmitButton = document.querySelector('[data-logbook-submit-button]');
const logbookVehicleSelect = document.querySelector('[data-logbook-vehicle-select]');
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
const driverPhotoPathField = document.querySelector('[data-driver-photo-path-field]');
const nationalIdPhotoPathField = document.querySelector('[data-national-id-photo-path-field]');
const drivingLicenseScanPathField = document.querySelector('[data-driving-license-scan-path-field]');
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

function setDeletePreview(modal, nameSelector, detailSelector, form, fallbackName, fallbackDetail) {
  const nameField = modal?.querySelector(nameSelector);
  const detailField = modal?.querySelector(detailSelector);
  const name = form?.dataset.deleteName || fallbackName;
  const detail = form?.dataset.deleteDetail || fallbackDetail;

  if (nameField) {
    nameField.textContent = name;
  }

  if (detailField) {
    detailField.textContent = detail;
  }
}

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

  if (isOpen) {
    setDeletePreview(
      preInspectionDeleteModal,
      '[data-pre-inspection-delete-name]',
      '[data-pre-inspection-delete-detail]',
      form,
      'This report',
      'Vehicle, invoice, date, and inspector will appear here.'
    );
  }

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

// Switches the shared post-inspection modal between create mode and edit mode.
function setPostInspectionFormMode(mode) {
  if (postInspectionActionField) {
    postInspectionActionField.value = mode === 'update' ? 'update' : 'create';
  }

  if (postInspectionModalTitle) {
    postInspectionModalTitle.textContent = mode === 'update' ? 'Edit Post-Inspection Report' : 'New Post-Inspection Report';
  }

  if (postInspectionSubmitButton) {
    postInspectionSubmitButton.textContent = mode === 'update' ? 'Save Changes' : 'Save Report';
  }
}

// Writes a value into a single field inside the post-inspection modal if it exists.
function setPostInspectionFieldValue(selector, value) {
  const field = postInspectionModal?.querySelector(selector);

  if (field) {
    field.value = value;
  }
}

// Applies saved system condition and remarks back into the post-inspection checklist.
function applyPostInspectionSystemChecks(systemChecks = []) {
  if (!postInspectionModal) {
    return;
  }

  const checksByName = new Map();

  systemChecks.forEach((check) => {
    if (check?.system_name) {
      checksByName.set(check.system_name, check);
    }
  });

  const hiddenSystemFields = postInspectionModal.querySelectorAll('input[name="system_name[]"]');
  hiddenSystemFields.forEach((field, index) => {
    const check = checksByName.get(field.value) || {};
    const statusField = postInspectionModal.querySelector(`input[name="system_status[${index}]"][value="${check.condition_status || ''}"]`);
    const remarkField = postInspectionModal.querySelectorAll('input[name="system_remarks[]"]')[index];

    postInspectionModal.querySelectorAll(`input[name="system_status[${index}]"]`).forEach((radio) => {
      radio.checked = false;
    });

    if (statusField) {
      statusField.checked = true;
    }

    if (remarkField) {
      remarkField.value = check.remarks || '';
    }
  });
}

// Resets the shared post-inspection modal back to a clean create state.
function resetPostInspectionFormForCreate() {
  postInspectionForm?.reset();

  if (postInspectionReportIdField) {
    postInspectionReportIdField.value = '';
  }

  setPostInspectionFormMode('create');
  setPostInspectionFieldValue('input[name="inspection_date"]', new Date().toISOString().slice(0, 10));
  setPostInspectionFieldValue('input[name="amount_spent"]', '0');
  setPostInspectionFieldValue('textarea[name="recommendation"]', 'This is to request you authorise payment to the above service provider...');
  applyPostInspectionSystemChecks([]);
}

// Reads the clicked post-inspection row and pre-fills the shared modal for editing.
function populatePostInspectionEditForm(button) {
  const row = button.closest('.post-inspection-row');
  if (!row) {
    return;
  }

  setPostInspectionFormMode('update');

  if (postInspectionReportIdField) {
    postInspectionReportIdField.value = row.dataset.reportId || '';
  }

  setPostInspectionFieldValue('input[name="invoice_number"]', row.dataset.invoiceNumber || '');
  setPostInspectionFieldValue('input[name="post_invoice"]', row.dataset.postInvoiceNumber || '');
  setPostInspectionFieldValue('input[name="inspection_date"]', row.dataset.inspectionDate || '');
  setPostInspectionFieldValue('input[name="inspector_name"]', row.dataset.inspectorName || '');
  setPostInspectionFieldValue('input[name="inspector_title"]', row.dataset.inspectorTitle || '');
  setPostInspectionFieldValue('select[name="vehicle"]', row.dataset.vehicleId || '');
  setPostInspectionFieldValue('input[name="mileage"]', row.dataset.mileage || '');
  setPostInspectionFieldValue('select[name="overall_status"]', row.dataset.overallStatus || '');
  setPostInspectionFieldValue('textarea[name="works_done"]', row.dataset.worksDone || '');
  setPostInspectionFieldValue('input[name="amount_spent"]', row.dataset.repairCost || '0');
  setPostInspectionFieldValue('select[name="service_provider"]', row.dataset.serviceProviderId || '');
  setPostInspectionFieldValue('textarea[name="recommendation"]', row.dataset.recommendation || '');

  let systemChecks = [];

  try {
    systemChecks = JSON.parse(row.dataset.systemChecks || '[]');
  } catch (error) {
    systemChecks = [];
  }

  applyPostInspectionSystemChecks(Array.isArray(systemChecks) ? systemChecks : []);
}

let pendingPostInspectionDeleteForm = null;

// Opens or closes the custom post-inspection delete confirmation modal.
function setPostInspectionDeleteModalOpen(isOpen, form = null) {
  if (!postInspectionDeleteModal) {
    return;
  }

  pendingPostInspectionDeleteForm = isOpen ? form : null;

  if (isOpen) {
    setDeletePreview(
      postInspectionDeleteModal,
      '[data-post-inspection-delete-name]',
      '[data-post-inspection-delete-detail]',
      form,
      'This report',
      'Vehicle, invoice, date, and repair cost will appear here.'
    );
  }

  postInspectionDeleteModal.classList.toggle('is-open', isOpen);
  postInspectionDeleteModal.setAttribute('aria-hidden', String(!isOpen));
  document.body.classList.toggle('overflow-hidden', isOpen);

  if (isOpen) {
    confirmPostInspectionDeleteButton?.focus();
  }
}

openPostInspectionModalButtons.forEach((button) => {
  button.addEventListener('click', () => {
    resetPostInspectionFormForCreate();
    setPostInspectionModalOpen(true);
  });
});

editPostInspectionEntryButtons.forEach((button) => {
  button.addEventListener('click', () => {
    populatePostInspectionEditForm(button);
    setPostInspectionModalOpen(true);
  });
});

openPostInspectionDeleteButtons.forEach((button) => {
  button.addEventListener('click', (event) => {
    event.preventDefault();
    const form = button.closest('form');

    if (form) {
      setPostInspectionDeleteModalOpen(true, form);
    }
  });
});

closePostInspectionModalButtons.forEach((button) => {
  button.addEventListener('click', () => setPostInspectionModalOpen(false));
});

postInspectionModal?.addEventListener('click', (event) => {
  if (event.target === postInspectionModal) {
    setPostInspectionModalOpen(false);
  }
});

if (postInspectionModal?.dataset.openOnLoad === 'true') {
  document.body.classList.add('overflow-hidden');
}

confirmPostInspectionDeleteButton?.addEventListener('click', () => {
  if (!pendingPostInspectionDeleteForm) {
    setPostInspectionDeleteModalOpen(false);
    return;
  }

  const form = pendingPostInspectionDeleteForm;
  setPostInspectionDeleteModalOpen(false);
  form.submit();
});

cancelPostInspectionDeleteButton?.addEventListener('click', () => {
  setPostInspectionDeleteModalOpen(false);
});

postInspectionDeleteModal?.addEventListener('click', (event) => {
  if (event.target === postInspectionDeleteModal) {
    setPostInspectionDeleteModalOpen(false);
  }
});

document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape' && postInspectionModal && !postInspectionModal.classList.contains('hidden')) {
    setPostInspectionModalOpen(false);
  }

  if (event.key === 'Escape' && postInspectionDeleteModal && postInspectionDeleteModal.classList.contains('is-open')) {
    setPostInspectionDeleteModalOpen(false);
  }
});

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
    providerModal.querySelector('input, select, button')?.focus();
  } else {
    openProviderModalButtons[0]?.focus();
  }
}

// Switches the shared provider modal between create mode and edit mode.
function setProviderFormMode(mode) {
  if (providerActionField) {
    providerActionField.value = mode === 'update' ? 'update' : 'create';
  }

  if (providerModalTitle) {
    providerModalTitle.textContent = mode === 'update' ? 'Edit Service Provider' : 'Add Service Provider';
  }

  if (providerSubmitButton) {
    providerSubmitButton.textContent = mode === 'update' ? 'Save Changes' : 'Add Provider';
  }
}

// Writes a value into a single field inside the provider modal if it exists.
function setProviderFieldValue(selector, value) {
  const field = providerModal?.querySelector(selector);

  if (field) {
    field.value = value;
  }
}

// Resets the shared provider modal back to a clean create state.
function resetProviderFormForCreate() {
  providerForm?.reset();

  if (providerIdField) {
    providerIdField.value = '';
  }

  setProviderFormMode('create');
  setProviderFieldValue('select[name="status"]', 'active');
}

// Reads the clicked provider card and pre-fills the shared modal for editing.
function populateProviderEditForm(button) {
  const card = button.closest('.provider-card');
  if (!card) {
    return;
  }

  setProviderFormMode('update');

  if (providerIdField) {
    providerIdField.value = card.dataset.providerId || '';
  }

  setProviderFieldValue('input[name="name"]', card.dataset.name || '');
  setProviderFieldValue('input[name="contact_person"]', card.dataset.contactPerson || '');
  setProviderFieldValue('input[name="phone"]', card.dataset.phone || '');
  setProviderFieldValue('input[name="email"]', card.dataset.email || '');
  setProviderFieldValue('input[name="town"]', card.dataset.town || '');
  setProviderFieldValue('input[name="specialty"]', card.dataset.specialty || '');
  setProviderFieldValue('select[name="status"]', card.dataset.status || 'active');
}

let pendingProviderDeleteForm = null;

// Opens or closes the custom provider delete confirmation modal.
function setProviderDeleteModalOpen(isOpen, form = null) {
  if (!providerDeleteModal) {
    return;
  }

  pendingProviderDeleteForm = isOpen ? form : null;

  if (isOpen) {
    const card = form?.closest('.provider-card');
    const providerName = card?.dataset.name || form?.dataset.deleteName || 'Selected provider';
    const providerSummary = [
      card?.dataset.specialty || '',
      card?.dataset.town || '',
    ].filter((value) => value && value !== '-').join(' - ');

    setDeletePreview(
      providerDeleteModal,
      '[data-provider-delete-name]',
      '[data-provider-delete-detail]',
      {
        dataset: {
          deleteName: providerName,
          deleteDetail: providerSummary || form?.dataset.deleteDetail || 'Specialty and town details will appear here.',
        },
      },
      'Selected provider',
      'Specialty and town details will appear here.'
    );
  }

  providerDeleteModal.classList.toggle('is-open', isOpen);
  providerDeleteModal.setAttribute('aria-hidden', String(!isOpen));
  document.body.classList.toggle('overflow-hidden', isOpen);

  if (isOpen) {
    confirmProviderDeleteButton?.focus();
  }
}

openProviderModalButtons.forEach((button) => {
  button.addEventListener('click', () => {
    resetProviderFormForCreate();
    setProviderModalOpen(true);
  });
});

editProviderEntryButtons.forEach((button) => {
  button.addEventListener('click', () => {
    populateProviderEditForm(button);
    setProviderModalOpen(true);
  });
});

openProviderDeleteButtons.forEach((button) => {
  button.addEventListener('click', (event) => {
    event.preventDefault();
    const form = button.closest('form');

    if (form) {
      setProviderDeleteModalOpen(true, form);
    }
  });
});

closeProviderModalButtons.forEach((button) => {
  button.addEventListener('click', () => setProviderModalOpen(false));
});

providerModal?.addEventListener('click', (event) => {
  if (event.target === providerModal) {
    setProviderModalOpen(false);
  }
});

if (providerModal?.dataset.openOnLoad === 'true') {
  document.body.classList.add('overflow-hidden');
}

confirmProviderDeleteButton?.addEventListener('click', () => {
  if (!pendingProviderDeleteForm) {
    setProviderDeleteModalOpen(false);
    return;
  }

  const form = pendingProviderDeleteForm;
  setProviderDeleteModalOpen(false);
  form.submit();
});

cancelProviderDeleteButton?.addEventListener('click', () => {
  setProviderDeleteModalOpen(false);
});

providerDeleteModal?.addEventListener('click', (event) => {
  if (event.target === providerDeleteModal) {
    setProviderDeleteModalOpen(false);
  }
});

document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape' && providerModal && !providerModal.classList.contains('hidden')) {
    setProviderModalOpen(false);
  }

  if (event.key === 'Escape' && providerDeleteModal && providerDeleteModal.classList.contains('is-open')) {
    setProviderDeleteModalOpen(false);
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

  if (isOpen) {
    setDeletePreview(
      logbookDeleteModal,
      '[data-logbook-delete-name]',
      '[data-logbook-delete-detail]',
      form,
      'This log entry',
      'Vehicle, trip date, driver, and route will appear here.'
    );
  }

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

// Uses the vehicle's stored mileage as the next trip's starting reading during create flow.
function syncLogbookOdometerStartFromVehicle(force = false) {
  if (!logbookModal || !logbookVehicleSelect || logbookActionField?.value === 'update') {
    return;
  }

  const startField = logbookModal.querySelector('input[name="odometer_start"]');
  const selectedOption = logbookVehicleSelect.selectedOptions[0];

  if (!startField || !selectedOption) {
    return;
  }

  if (!force && startField.value.trim() !== '') {
    return;
  }

  startField.value = selectedOption.dataset.currentMileage || '';
}

// Clears the shared logbook modal and restores create defaults before a new trip is entered.
function resetLogbookFormForCreate() {
  const logbookForm = logbookModal?.querySelector('form');

  logbookForm?.reset();

  if (logbookEntryIdField) {
    logbookEntryIdField.value = '';
  }

  setLogbookFormMode('create');
  setLogbookFieldValue('input[name="date"]', new Date().toISOString().slice(0, 10));
  setLogbookFieldValue('select[name="driver"]', 'unassigned');
  syncLogbookOdometerStartFromVehicle(true);
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
    resetLogbookFormForCreate();
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

logbookVehicleSelect?.addEventListener('change', () => {
  syncLogbookOdometerStartFromVehicle();
});

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

// Shows or hides the current uploaded file block for one driver upload field.
function setDriverUploadPreview(fieldName, storedPath = '', fileUrl = '', fileName = '', isImage = false) {
  const preview = driverModal?.querySelector(`[data-driver-file-preview="${fieldName}"]`);
  const link = driverModal?.querySelector(`[data-driver-file-link="${fieldName}"]`);
  const image = driverModal?.querySelector(`[data-driver-file-image="${fieldName}"]`);

  if (!preview || !link || !image) {
    return;
  }

  const hasFile = storedPath.trim() !== '';

  preview.classList.toggle('hidden', !hasFile);
  preview.classList.toggle('block', hasFile);
  link.textContent = hasFile ? fileName || storedPath.split('/').pop() || 'Current file' : '';
  link.href = hasFile ? fileUrl : '#';
  image.src = hasFile ? fileUrl : '';
  image.classList.toggle('hidden', !(hasFile && isImage));
  image.classList.toggle('block', hasFile && isImage);
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
  setDriverFieldValue('select[name="gender"]', '');
  setDriverFieldValue('input[name="national_id_number"]', '');
  setDriverFieldValue('input[name="license_issue_date"]', '');
  setDriverFieldValue('input[name="license_issuing_authority"]', '');
  if (driverPhotoPathField) {
    driverPhotoPathField.value = '';
  }
  if (nationalIdPhotoPathField) {
    nationalIdPhotoPathField.value = '';
  }
  if (drivingLicenseScanPathField) {
    drivingLicenseScanPathField.value = '';
  }
  setDriverUploadPreview('driver_photo');
  setDriverUploadPreview('national_id_photo');
  setDriverUploadPreview('driving_license_scan');
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
  setDriverFieldValue('select[name="gender"]', row.dataset.gender || '');
  setDriverFieldValue('input[name="national_id_number"]', row.dataset.nationalIdNumber || '');
  setDriverFieldValue('input[name="license_number"]', row.dataset.licenseNumber || '');
  setDriverFieldValue('input[name="license_classes"]', row.dataset.licenseClasses || '');
  setDriverFieldValue('input[name="license_issue_date"]', row.dataset.licenseIssueDate || '');
  setDriverFieldValue('input[name="license_issuing_authority"]', row.dataset.licenseIssuingAuthority || '');
  setDriverFieldValue('input[name="license_expiry"]', row.dataset.licenseExpiry || '');
  setDriverFieldValue('input[name="department"]', row.dataset.department || '');
  setDriverFieldValue('select[name="assigned_vehicle"]', row.dataset.assignedVehicleId || 'unassigned');
  setDriverFieldValue('select[name="status"]', row.dataset.status || 'active');
  if (driverPhotoPathField) {
    driverPhotoPathField.value = row.dataset.driverPhoto || '';
  }
  if (nationalIdPhotoPathField) {
    nationalIdPhotoPathField.value = row.dataset.nationalIdPhoto || '';
  }
  if (drivingLicenseScanPathField) {
    drivingLicenseScanPathField.value = row.dataset.drivingLicenseScan || '';
  }
  setDriverUploadPreview(
    'driver_photo',
    row.dataset.driverPhoto || '',
    row.dataset.driverPhotoUrl || '',
    row.dataset.driverPhotoName || '',
    row.dataset.driverPhotoIsImage === 'true'
  );
  setDriverUploadPreview(
    'national_id_photo',
    row.dataset.nationalIdPhoto || '',
    row.dataset.nationalIdPhotoUrl || '',
    row.dataset.nationalIdPhotoName || '',
    row.dataset.nationalIdPhotoIsImage === 'true'
  );
  setDriverUploadPreview(
    'driving_license_scan',
    row.dataset.drivingLicenseScan || '',
    row.dataset.drivingLicenseScanUrl || '',
    row.dataset.drivingLicenseScanName || '',
    row.dataset.drivingLicenseScanIsImage === 'true'
  );
  setDriverVehicleAvailability(row.dataset.driverId || '');
}

let pendingDriverDeleteForm = null;

// Opens or closes the custom driver delete confirmation modal.
function setDriverDeleteModalOpen(isOpen, form = null) {
  if (!driverDeleteModal) {
    return;
  }

  pendingDriverDeleteForm = isOpen ? form : null;

  if (isOpen) {
    setDeletePreview(
      driverDeleteModal,
      '[data-driver-delete-name]',
      '[data-driver-delete-detail]',
      form,
      'This driver',
      'License and assignment details will appear here.'
    );
  }

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

  if (isOpen) {
    const row = form?.closest('.maintenance-row');
    const vehicleName = row?.dataset.vehicle || form?.dataset.deleteName || 'Selected vehicle';
    const maintenanceSummary = [
      row?.dataset.date || '',
      row?.dataset.type || '',
      row?.dataset.provider || '',
      row?.dataset.cost || '',
    ].filter((value) => value && value !== '-').join(' - ');

    setDeletePreview(
      maintenanceDeleteModal,
      '[data-maintenance-delete-name]',
      '[data-maintenance-delete-detail]',
      {
        dataset: {
          deleteName: vehicleName,
          deleteDetail: maintenanceSummary || form?.dataset.deleteDetail || 'Maintenance date, type, provider, and cost will appear here.',
        },
      },
      'Selected vehicle',
      'Maintenance date, type, provider, and cost will appear here.'
    );
  }

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
