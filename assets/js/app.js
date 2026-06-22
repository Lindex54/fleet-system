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
const vehicleRegistrationFilter = document.querySelector('#vehicle-filter-registration');
const vehicleModelFilter = document.querySelector('#vehicle-filter-model');
const vehicleDepartmentFilter = document.querySelector('#vehicle-filter-department');
const vehicleTypeFilter = document.querySelector('#vehicle-filter-type');
const vehicleStatusFilter = document.querySelector('#vehicle-filter-status');
const vehiclePrintGroup = document.querySelector('#vehicle-print-group');
const vehicleFilterApplyButton = document.querySelector('#vehicle-filter-apply');
const vehicleFilterResetButton = document.querySelector('#vehicle-filter-reset');
const vehicleFilterSummary = document.querySelector('[data-vehicle-filter-summary]');
const printVehiclesButton = document.querySelector('[data-print-vehicles]');
const logbookSearch = document.querySelector('#logbook-search');
const logbookRows = document.querySelectorAll('[data-logbook-table] .logbook-row');
const driverSearch = document.querySelector('#driver-search');
const driverRows = document.querySelectorAll('[data-driver-table] .driver-row');
const driverDepartmentFilter = document.querySelector('#driver-filter-department');
const driverStatusFilter = document.querySelector('#driver-filter-status');
const driverAssignedVehicleFilter = document.querySelector('#driver-filter-assigned-vehicle');
const driverFilterApplyButton = document.querySelector('#driver-filter-apply');
const driverFilterResetButton = document.querySelector('#driver-filter-reset');
const driverFilterSummary = document.querySelector('[data-driver-filter-summary]');
const printSelectedDriversButtons = document.querySelectorAll('[data-print-selected-drivers]');
const APP_BASE_PATH = document.body?.dataset.basePath || '/fleet-system';
const PRINT_BANNER_URL = `${window.location.origin}${APP_BASE_PATH}/assets/images/branding/banner.png`;
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
const communicationHistoryViewModal = document.querySelector('#communication-history-view-modal');
const openCommunicationHistoryViewButtons = document.querySelectorAll('[data-open-communication-history-view]');
const closeCommunicationHistoryViewButtons = document.querySelectorAll('[data-close-communication-history-view-modal]');
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

function updateVehicleFilterSummary(count) {
  if (!vehicleFilterSummary) {
    return;
  }

  const filterParts = [];

  if (vehicleRegistrationFilter?.value.trim()) {
    filterParts.push(`reg. no. "${vehicleRegistrationFilter.value.trim()}"`);
  }

  if (vehicleModelFilter?.value) {
    filterParts.push(`model "${vehicleModelFilter.value}"`);
  }

  if (vehicleDepartmentFilter?.value) {
    filterParts.push(`department "${vehicleDepartmentFilter.value}"`);
  }

  if (vehicleTypeFilter?.value) {
    filterParts.push(`type "${vehicleTypeFilter.value}"`);
  }

  if (vehicleStatusFilter?.value) {
    filterParts.push(`status "${vehicleStatusFilter.value}"`);
  }

  if (filterParts.length === 0) {
    vehicleFilterSummary.textContent = `Showing all ${count} vehicle${count === 1 ? '' : 's'}.`;
    return;
  }

  vehicleFilterSummary.textContent = `Showing ${count} vehicle${count === 1 ? '' : 's'} filtered by ${filterParts.join(', ')}.`;
}

function filterVehicleRows() {
  const query = vehicleSearch?.value.trim().toLowerCase() || '';
  const registration = vehicleRegistrationFilter?.value.trim().toLowerCase() || '';
  const model = (vehicleModelFilter?.value || '').trim().toLowerCase();
  const department = (vehicleDepartmentFilter?.value || '').trim().toLowerCase();
  const vehicleType = (vehicleTypeFilter?.value || '').trim().toLowerCase();
  const status = (vehicleStatusFilter?.value || '').trim().toLowerCase();

  let visibleCount = 0;

  vehicleRows.forEach((row) => {
    const haystack = row.dataset.search || row.textContent.toLowerCase();
    const rowRegistration = (row.dataset.registrationNumber || '').toLowerCase();
    const rowModel = (row.dataset.model || '').toLowerCase();
    const rowDepartment = (row.dataset.department || '').toLowerCase();
    const rowVehicleType = ((row.dataset.vehicleType || '') || '').toLowerCase();
    const rowStatus = (row.dataset.statusLabel || '').toLowerCase();
    const matchesSearch = query.length === 0 || haystack.includes(query);
    const matchesRegistration = registration.length === 0 || rowRegistration === registration;
    const matchesModel = model.length === 0 || rowModel === model;
    const matchesDepartment = department.length === 0 || rowDepartment === department;
    const matchesVehicleType = vehicleType.length === 0 || rowVehicleType === vehicleType;
    const matchesStatus = status.length === 0 || rowStatus === status;
    const isVisible = matchesSearch && matchesRegistration && matchesModel && matchesDepartment && matchesVehicleType && matchesStatus;

    row.classList.toggle('hidden', !isVisible);

    if (isVisible) {
      visibleCount += 1;
    }
  });

  updateVehicleFilterSummary(visibleCount);
  vehicleTablePaginator?.refresh(true);
}

vehicleSearch?.addEventListener('input', filterVehicleRows);
vehicleFilterApplyButton?.addEventListener('click', filterVehicleRows);
vehicleFilterResetButton?.addEventListener('click', () => {
  if (vehicleSearch) {
    vehicleSearch.value = '';
  }

  if (vehicleRegistrationFilter) {
    vehicleRegistrationFilter.value = '';
  }

  if (vehicleModelFilter) {
    vehicleModelFilter.value = '';
  }

  if (vehicleDepartmentFilter) {
    vehicleDepartmentFilter.value = '';
  }

  if (vehicleTypeFilter) {
    vehicleTypeFilter.value = '';
  }

  if (vehicleStatusFilter) {
    vehicleStatusFilter.value = '';
  }

  if (vehiclePrintGroup) {
    vehiclePrintGroup.value = 'department';
  }

  filterVehicleRows();
});

logbookSearch?.addEventListener('input', (event) => {
  const query = event.target.value.trim().toLowerCase();

  logbookRows.forEach((row) => {
    const haystack = row.dataset.search || row.textContent.toLowerCase();
    row.classList.toggle('hidden', query.length > 0 && !haystack.includes(query));
  });

  logbookTablePaginator?.refresh(true);
});

function updateDriverFilterSummary(count) {
  if (!driverFilterSummary) {
    return;
  }

  const filterParts = [];

  if (driverDepartmentFilter?.value) {
    filterParts.push(`department "${driverDepartmentFilter.value}"`);
  }

  if (driverStatusFilter?.value) {
    filterParts.push(`status "${driverStatusFilter.value}"`);
  }

  if (driverAssignedVehicleFilter?.value) {
    filterParts.push(`assigned vehicle "${driverAssignedVehicleFilter.value}"`);
  }

  if (filterParts.length === 0) {
    driverFilterSummary.textContent = `Showing all ${count} driver${count === 1 ? '' : 's'}.`;
    return;
  }

  driverFilterSummary.textContent = `Showing ${count} driver${count === 1 ? '' : 's'} filtered by ${filterParts.join(', ')}.`;
}

function filterDriverRows() {
  const query = driverSearch?.value.trim().toLowerCase() || '';
  const department = (driverDepartmentFilter?.value || '').trim().toLowerCase();
  const status = (driverStatusFilter?.value || '').trim().toLowerCase();
  const assignedVehicle = (driverAssignedVehicleFilter?.value || '').trim().toLowerCase();

  let visibleCount = 0;

  driverRows.forEach((row) => {
    const haystack = row.dataset.search || row.textContent.toLowerCase();
    const rowDepartment = (row.dataset.department || '').toLowerCase();
    const rowStatus = (row.dataset.statusLabel || '').toLowerCase();
    const rowAssignedVehicle = (row.dataset.assignedVehicle || '').toLowerCase();
    const matchesSearch = query.length === 0 || haystack.includes(query);
    const matchesDepartment = department.length === 0 || rowDepartment === department;
    const matchesStatus = status.length === 0 || rowStatus === status;
    const matchesAssignedVehicle = assignedVehicle.length === 0 || rowAssignedVehicle === assignedVehicle;
    const isVisible = matchesSearch && matchesDepartment && matchesStatus && matchesAssignedVehicle;

    row.classList.toggle('hidden', !isVisible);

    if (isVisible) {
      visibleCount += 1;
    }
  });

  updateDriverFilterSummary(visibleCount);
  driverTablePaginator?.refresh(true);
}

driverSearch?.addEventListener('input', filterDriverRows);
driverFilterApplyButton?.addEventListener('click', filterDriverRows);
driverFilterResetButton?.addEventListener('click', () => {
  if (driverSearch) {
    driverSearch.value = '';
  }

  if (driverDepartmentFilter) {
    driverDepartmentFilter.value = '';
  }

  if (driverStatusFilter) {
    driverStatusFilter.value = '';
  }

  if (driverAssignedVehicleFilter) {
    driverAssignedVehicleFilter.value = '';
  }

  filterDriverRows();
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

  maintenanceTablePaginator?.refresh(true);
}

maintenanceSearch?.addEventListener('input', filterMaintenanceRows);
maintenanceStatus?.addEventListener('change', filterMaintenanceRows);

preInspectionSearch?.addEventListener('input', (event) => {
  const query = event.target.value.trim().toLowerCase();

  preInspectionRows.forEach((row) => {
    const haystack = row.dataset.search || row.textContent.toLowerCase();
    row.classList.toggle('hidden', query.length > 0 && !haystack.includes(query));
  });

  preInspectionTablePaginator?.refresh(true);
});

postInspectionSearch?.addEventListener('input', (event) => {
  const query = event.target.value.trim().toLowerCase();

  postInspectionRows.forEach((row) => {
    const haystack = row.dataset.search || row.textContent.toLowerCase();
    row.classList.toggle('hidden', query.length > 0 && !haystack.includes(query));
  });

  postInspectionTablePaginator?.refresh(true);
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

  communicationHistoryTablePaginator?.refresh(true);
});

function setCommunicationHistoryModalOpen(isOpen) {
  if (!communicationHistoryViewModal) {
    return;
  }

  communicationHistoryViewModal.classList.toggle('hidden', !isOpen);
  communicationHistoryViewModal.classList.toggle('flex', isOpen);
  communicationHistoryViewModal.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
  document.body.classList.toggle('overflow-hidden', isOpen);
}

function setCommunicationHistoryModalText(selector, value) {
  const element = communicationHistoryViewModal?.querySelector(selector);
  if (element) {
    element.textContent = value;
  }
}

openCommunicationHistoryViewButtons.forEach((button) => {
  button.addEventListener('click', () => {
    setCommunicationHistoryModalText('[data-communication-history-view-subject]', button.dataset.messageSubject || 'Message details');
    setCommunicationHistoryModalText('[data-communication-history-view-datetime]', button.dataset.messageDatetime || 'Date not recorded');
    setCommunicationHistoryModalText('[data-communication-history-view-sender]', button.dataset.messageSender || 'Unknown sender');
    setCommunicationHistoryModalText('[data-communication-history-view-type]', button.dataset.messageType || 'Unknown type');
    setCommunicationHistoryModalText('[data-communication-history-view-drivers]', button.dataset.messageDrivers || '0');
    setCommunicationHistoryModalText('[data-communication-history-view-officers]', button.dataset.messageOfficers || '0');
    setCommunicationHistoryModalText('[data-communication-history-view-body]', button.dataset.messageBody || 'No message body recorded.');
    setCommunicationHistoryModalOpen(true);
  });
});

closeCommunicationHistoryViewButtons.forEach((button) => {
  button.addEventListener('click', () => {
    setCommunicationHistoryModalOpen(false);
  });
});

communicationHistoryViewModal?.addEventListener('click', (event) => {
  if (event.target === communicationHistoryViewModal) {
    setCommunicationHistoryModalOpen(false);
  }
});

document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape' && communicationHistoryViewModal?.classList.contains('flex')) {
    setCommunicationHistoryModalOpen(false);
  }
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
const printPageButtons = document.querySelectorAll('[data-print-page]');
let activePrintRoot = null;

function ensurePrintFooter(printRoot) {
  if (!printRoot || printRoot.querySelector('[data-print-footer]')) {
    return;
  }

  const footer = document.createElement('div');
  footer.className = 'print-page-footer';
  footer.setAttribute('data-print-footer', '');
  footer.innerHTML = `
    <span class="print-page-footer-text">Busitema University Estates MIS</span>
  `;

  printRoot.appendChild(footer);
}

function ensurePrintHeader(printRoot) {
  if (!printRoot || printRoot.hasAttribute('data-print-root-custom-header') || printRoot.querySelector('[data-print-header]')) {
    return;
  }

  const header = document.createElement('div');
  header.className = 'print-page-header';
  header.setAttribute('data-print-header', '');
  header.innerHTML = `
    <div class="app-print-banner"><img src="${PRINT_BANNER_URL}" alt="Busitema University print banner"></div>
    <div class="print-page-header-brand">Busitema University Estates MIS</div>
    <div class="print-page-header-unit">ESTATES UNIT</div>
  `;

  printRoot.insertBefore(header, printRoot.firstChild);
}

function ensurePrintCleanup(printRoot) {
  if (!printRoot) {
    return;
  }

  printRoot.querySelectorAll('input[type="search"]').forEach((input) => {
    const container = input.closest('.border-b') || input.closest('label') || input.parentElement;
    container?.setAttribute('data-print-hide', '');
  });

  printRoot.querySelectorAll('select').forEach((select) => {
    if ((select.id || '').includes('filter') || (select.id || '').includes('status')) {
      const container = select.closest('.border-b') || select.closest('label') || select.parentElement;
      container?.setAttribute('data-print-hide', '');
    }
  });

  printRoot.querySelectorAll('table').forEach((table) => {
    const rows = Array.from(table.querySelectorAll('tr'));
    const actionColumnIndexes = new Set();

    rows.forEach((row) => {
      Array.from(row.children).forEach((cell, index) => {
        if (cell.querySelector('button, form, [data-view-logbook-entry], [data-edit-logbook-entry], [data-open-logbook-delete], [data-view-pre-inspection-entry], [data-edit-pre-inspection-entry], [data-open-pre-inspection-delete], [data-view-post-inspection-entry], [data-edit-post-inspection-entry], [data-open-post-inspection-delete], [data-view-vehicle-entry], [data-edit-vehicle-entry], [data-delete-vehicle-entry], [data-view-driver-entry], [data-edit-driver-entry], [data-delete-driver-entry], [data-view-maintenance-entry], [data-edit-maintenance-entry], [data-delete-maintenance-entry], [data-open-provider-delete], [data-view-provider-entry], [data-edit-provider-entry]')) {
          actionColumnIndexes.add(index);
        }
      });
    });

    if (actionColumnIndexes.size === 0) {
      return;
    }

    rows.forEach((row) => {
      Array.from(row.children).forEach((cell, index) => {
        if (actionColumnIndexes.has(index)) {
          cell.setAttribute('data-print-hide', '');
        }
      });
    });
  });
}

function initializePrintFooters() {
  document.querySelectorAll('[data-print-root]').forEach((printRoot) => {
    ensurePrintHeader(printRoot);
    ensurePrintCleanup(printRoot);
    ensurePrintFooter(printRoot);
  });
}

function clearPrintTarget() {
  if (activePrintRoot) {
    activePrintRoot.classList.remove('print-target-active');
    activePrintRoot = null;
  }

  document.body.classList.remove('print-target-active');
}

function resolvePrintRoot(button) {
  const selector = button.dataset.printTarget;
  if (selector) {
    return document.querySelector(selector);
  }

  return button.closest('main')?.querySelector('[data-print-root]') || null;
}

initializePrintFooters();

printPageButtons.forEach((button) => {
  button.addEventListener('click', () => {
    const printRoot = resolvePrintRoot(button);

    clearPrintTarget();

    if (printRoot) {
      ensurePrintFooter(printRoot);
      activePrintRoot = printRoot;
      activePrintRoot.classList.add('print-target-active');
      document.body.classList.add('print-target-active');
    }

    window.print();
  });
});

window.addEventListener('afterprint', clearPrintTarget);

function createTablePaginator(table, rowSelector, pageSize = 10) {
  if (!table) {
    return null;
  }

  const rows = Array.from(table.querySelectorAll(rowSelector));

  const wrapper = table.closest('.overflow-x-auto') || table.parentElement;
  if (!wrapper) {
    return {
      refresh() {},
    };
  }

  const controls = document.createElement('div');
  controls.className = 'pagination-controls print:hidden flex flex-col gap-3 border-t border-fleet-line-soft px-4 py-4 sm:flex-row sm:items-center sm:justify-between';

  const summary = document.createElement('p');
  summary.className = 'text-sm text-fleet-muted';

  const actions = document.createElement('div');
  actions.className = 'flex items-center gap-3';

  const previousButton = document.createElement('button');
  previousButton.type = 'button';
  previousButton.className = 'inline-flex h-10 items-center rounded-lg border border-fleet-line bg-fleet-surface px-4 text-sm font-semibold text-fleet-ink shadow-sm hover:bg-fleet-surface-muted disabled:cursor-not-allowed disabled:opacity-50';
  previousButton.textContent = 'Previous';

  const pageLabel = document.createElement('span');
  pageLabel.className = 'text-sm font-semibold text-fleet-ink';

  const nextButton = document.createElement('button');
  nextButton.type = 'button';
  nextButton.className = 'inline-flex h-10 items-center rounded-lg border border-fleet-line bg-fleet-surface px-4 text-sm font-semibold text-fleet-ink shadow-sm hover:bg-fleet-surface-muted disabled:cursor-not-allowed disabled:opacity-50';
  nextButton.textContent = 'Next';

  actions.append(previousButton, pageLabel, nextButton);
  controls.append(summary, actions);
  wrapper.insertAdjacentElement('afterend', controls);

  let currentPage = 1;

  function render(resetPage = false) {
    const visibleRows = rows.filter((row) => !row.classList.contains('hidden'));
    const totalRows = visibleRows.length;
    const totalPages = Math.max(1, Math.ceil(totalRows / pageSize));

    if (resetPage) {
      currentPage = 1;
    } else if (currentPage > totalPages) {
      currentPage = totalPages;
    }

    const startIndex = (currentPage - 1) * pageSize;
    const endIndex = startIndex + pageSize;

    rows.forEach((row) => {
      if (row.classList.contains('hidden')) {
        row.style.display = '';
        return;
      }

      const visibleIndex = visibleRows.indexOf(row);
      row.style.display = visibleIndex >= startIndex && visibleIndex < endIndex ? '' : 'none';
    });

    if (totalRows === 0) {
      controls.classList.add('hidden');
      return;
    }

    controls.classList.remove('hidden');
    summary.textContent = `Showing ${startIndex + 1}-${Math.min(endIndex, totalRows)} of ${totalRows} entries`;
    pageLabel.textContent = `Page ${currentPage} of ${totalPages}`;
    previousButton.disabled = currentPage === 1;
    nextButton.disabled = currentPage === totalPages;
  }

  previousButton.addEventListener('click', () => {
    if (currentPage === 1) {
      return;
    }

    currentPage -= 1;
    render();
  });

  nextButton.addEventListener('click', () => {
    const visibleRows = rows.filter((row) => !row.classList.contains('hidden'));
    const totalPages = Math.max(1, Math.ceil(visibleRows.length / pageSize));

    if (currentPage === totalPages) {
      return;
    }

    currentPage += 1;
    render();
  });

  window.addEventListener('beforeprint', () => {
    rows.forEach((row) => {
      if (!row.classList.contains('hidden')) {
        row.style.display = '';
      }
    });
  });

  window.addEventListener('afterprint', () => {
    render();
  });

  render();

  return {
    refresh(resetPage = false) {
      render(resetPage);
    },
  };
}

const vehicleTablePaginator = createTablePaginator(document.querySelector('[data-vehicle-table]'), '.vehicle-row');
const logbookTablePaginator = createTablePaginator(document.querySelector('[data-logbook-table]'), '.logbook-row');
const driverTablePaginator = createTablePaginator(document.querySelector('[data-driver-table]'), '.driver-row');
const maintenanceTablePaginator = createTablePaginator(document.querySelector('[data-maintenance-table]'), '.maintenance-row');
const preInspectionTablePaginator = createTablePaginator(document.querySelector('[data-pre-inspection-table]'), '.pre-inspection-row');
const postInspectionTablePaginator = createTablePaginator(document.querySelector('[data-post-inspection-table]'), '.post-inspection-row');
const communicationHistoryTablePaginator = createTablePaginator(document.querySelector('[data-communication-history-table]'), '.communication-history-row');
const dashboardLogTablePaginator = createTablePaginator(document.querySelector('[data-dashboard-log-table]'), '.dashboard-log-row');
const driverHistoryTablePaginator = createTablePaginator(document.querySelector('[data-driver-history-table]'), '.driver-history-row');
const vehicleUsageTablePaginators = Array.from(document.querySelectorAll('[data-vehicle-usage-driver-table]'))
  .map((table) => createTablePaginator(table, '.vehicle-usage-log-row'))
  .filter(Boolean);

if (vehicleRows.length > 0) {
  filterVehicleRows();
}

if (driverRows.length > 0) {
  filterDriverRows();
}

function getVisibleDriverRows() {
  return Array.from(driverRows).filter((row) => !row.classList.contains('hidden'));
}

function buildPrintBannerMarkup() {
  return `<div class="app-print-banner"><img src="${PRINT_BANNER_URL}" alt="Busitema University print banner"></div><div class="print-page-header-brand">Busitema University Estates MIS</div><div class="print-page-header-unit">ESTATES UNIT</div>`;
}

function escapeHtml(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

function getVisibleVehicleRows() {
  return Array.from(vehicleRows).filter((row) => !row.classList.contains('hidden'));
}

function getVehicleRowData(row) {
  return {
    registration: row.dataset.registrationNumber || '-',
    make: row.dataset.make || '-',
    model: row.dataset.model || '-',
    year: row.dataset.year || '-',
    vehicleType: row.dataset.vehicleType || '-',
    fuelType: row.dataset.fuelType || '-',
    department: row.dataset.department || '-',
    currentMileage: row.dataset.currentMileage ? `${row.dataset.currentMileage} km` : '-',
    insuranceExpiry: row.dataset.insuranceExpiry || 'Not set',
    status: row.dataset.statusLabel || '-',
    repairsDone: row.dataset.repairsDone || 'No repairs recorded.',
  };
}

function buildVehicleDetailSheetMarkup(vehicle) {
  return `
    <div class="vehicle-print-sheet">
      <div class="mb-6 border-b border-slate-200 pb-5">
        <p style="font-size:12px;font-weight:800;letter-spacing:0.18em;text-transform:uppercase;color:#2563eb;">Vehicle Details</p>
        <h1 style="margin:8px 0 0;font-size:30px;font-weight:800;color:#0f172a;">${escapeHtml(vehicle.registration)}</h1>
        <p style="margin:8px 0 0;font-size:14px;color:#64748b;">${escapeHtml(`${vehicle.make} ${vehicle.model}`.trim())}</p>
      </div>
      <div style="display:grid;gap:20px;grid-template-columns:repeat(2,minmax(0,1fr));">
        <section style="border:1px solid #cbd5e1;border-radius:18px;padding:20px;">
          <p style="margin:0;font-size:12px;font-weight:800;letter-spacing:0.18em;text-transform:uppercase;color:#64748b;">Identity</p>
          <dl style="margin:16px 0 0;display:grid;gap:12px;font-size:14px;">
            <div style="display:flex;justify-content:space-between;gap:16px;"><dt style="color:#64748b;">Registration</dt><dd style="margin:0;font-weight:700;color:#0f172a;">${escapeHtml(vehicle.registration)}</dd></div>
            <div style="display:flex;justify-content:space-between;gap:16px;"><dt style="color:#64748b;">Make</dt><dd style="margin:0;font-weight:700;color:#0f172a;">${escapeHtml(vehicle.make)}</dd></div>
            <div style="display:flex;justify-content:space-between;gap:16px;"><dt style="color:#64748b;">Model</dt><dd style="margin:0;font-weight:700;color:#0f172a;">${escapeHtml(vehicle.model)}</dd></div>
            <div style="display:flex;justify-content:space-between;gap:16px;"><dt style="color:#64748b;">Year</dt><dd style="margin:0;font-weight:700;color:#0f172a;">${escapeHtml(vehicle.year)}</dd></div>
          </dl>
        </section>
        <section style="border:1px solid #cbd5e1;border-radius:18px;padding:20px;">
          <p style="margin:0;font-size:12px;font-weight:800;letter-spacing:0.18em;text-transform:uppercase;color:#64748b;">Operational Details</p>
          <dl style="margin:16px 0 0;display:grid;gap:12px;font-size:14px;">
            <div style="display:flex;justify-content:space-between;gap:16px;"><dt style="color:#64748b;">Type</dt><dd style="margin:0;font-weight:700;color:#0f172a;">${escapeHtml(vehicle.vehicleType)}</dd></div>
            <div style="display:flex;justify-content:space-between;gap:16px;"><dt style="color:#64748b;">Fuel</dt><dd style="margin:0;font-weight:700;color:#0f172a;">${escapeHtml(vehicle.fuelType)}</dd></div>
            <div style="display:flex;justify-content:space-between;gap:16px;"><dt style="color:#64748b;">Department</dt><dd style="margin:0;font-weight:700;color:#0f172a;">${escapeHtml(vehicle.department)}</dd></div>
            <div style="display:flex;justify-content:space-between;gap:16px;"><dt style="color:#64748b;">Status</dt><dd style="margin:0;font-weight:700;color:#0f172a;">${escapeHtml(vehicle.status)}</dd></div>
          </dl>
        </section>
        <section style="border:1px solid #cbd5e1;border-radius:18px;padding:20px;">
          <p style="margin:0;font-size:12px;font-weight:800;letter-spacing:0.18em;text-transform:uppercase;color:#64748b;">Compliance</p>
          <dl style="margin:16px 0 0;display:grid;gap:12px;font-size:14px;">
            <div style="display:flex;justify-content:space-between;gap:16px;"><dt style="color:#64748b;">Current Mileage</dt><dd style="margin:0;font-weight:700;color:#0f172a;">${escapeHtml(vehicle.currentMileage)}</dd></div>
            <div style="display:flex;justify-content:space-between;gap:16px;"><dt style="color:#64748b;">Insurance Expiry</dt><dd style="margin:0;font-weight:700;color:#0f172a;">${escapeHtml(vehicle.insuranceExpiry)}</dd></div>
          </dl>
        </section>
        <section style="border:1px solid #cbd5e1;border-radius:18px;padding:20px;">
          <p style="margin:0;font-size:12px;font-weight:800;letter-spacing:0.18em;text-transform:uppercase;color:#64748b;">Repairs Done</p>
          <p style="margin:16px 0 0;font-size:14px;line-height:1.7;color:#0f172a;">${escapeHtml(vehicle.repairsDone)}</p>
        </section>
      </div>
    </div>
  `;
}

function printVehicleSheetMarkup(title, markup) {
  const printWindow = window.open('', '_blank', 'width=1200,height=800');
  if (!printWindow) {
    return;
  }

  const styles = Array.from(document.querySelectorAll('style, link[rel="stylesheet"]'))
    .map((node) => node.outerHTML)
    .join('');

  printWindow.document.open();
  printWindow.document.write(`
    <!DOCTYPE html>
    <html lang="en">
      <head>
        <meta charset="utf-8">
        <title>${escapeHtml(title)}</title>
        ${styles}
        <style>
          body { background:#fff; color:#0f172a; padding:24px; font-family:Arial,sans-serif; }
          .app-print-banner { margin-bottom:24px; }
          .app-print-banner img { display:block; width:100%; max-width:1200px; margin:0 auto; }
          .vehicle-bulk-print-header { margin-bottom:24px; }
          .vehicle-bulk-print-header h1 { margin:0 0 8px; font-size:30px; font-weight:800; }
          .vehicle-bulk-print-header p { margin:0; color:#475569; font-size:14px; }
          .vehicle-bulk-print-section { margin-top:28px; break-inside:avoid; }
          .vehicle-bulk-print-section h2 { margin:0 0 14px; font-size:20px; font-weight:800; }
          .vehicle-bulk-print-table { width:100%; border-collapse:collapse; }
          .vehicle-bulk-print-table th, .vehicle-bulk-print-table td { border:1px solid #cbd5e1; padding:10px 12px; text-align:left; vertical-align:top; font-size:13px; }
          .vehicle-bulk-print-table th { background:#e2e8f0; color:#334155; font-weight:700; }
          .vehicle-bulk-print-table td { color:#0f172a; }
          @media print {
            body { padding:0; }
          }
        </style>
      </head>
      <body>
        ${buildPrintBannerMarkup()}
        ${markup}
      </body>
    </html>
  `);
  printWindow.document.close();
  printWindow.focus();
  printWindow.print();
  printWindow.close();
}

function printVehiclesByCategory() {
  const rowsToPrint = getVisibleVehicleRows();

  if (rowsToPrint.length === 0) {
    return;
  }

  if (rowsToPrint.length === 1) {
    const vehicle = getVehicleRowData(rowsToPrint[0]);

    printVehicleSheetMarkup('Vehicle Details', buildVehicleDetailSheetMarkup(vehicle));
    return;
  }

  const groupField = vehiclePrintGroup?.value || 'department';
  const groupLabels = {
    department: 'Department',
    model: 'Model',
    vehicleType: 'Vehicle Type',
    statusLabel: 'Status',
  };
  const groupedVehicles = new Map();

  rowsToPrint.forEach((row) => {
    const vehicle = getVehicleRowData(row);
    const rawGroupValue = row.dataset[groupField] || '';
    const groupValue = rawGroupValue.trim() || 'Uncategorized';

    if (!groupedVehicles.has(groupValue)) {
      groupedVehicles.set(groupValue, []);
    }

    groupedVehicles.get(groupValue).push(vehicle);
  });

  const sectionsMarkup = Array.from(groupedVehicles.entries())
    .sort(([groupA], [groupB]) => groupA.localeCompare(groupB))
    .map(([groupName, vehicles]) => `
      <section class="vehicle-bulk-print-section">
        <h2>${escapeHtml(groupName)} <span style="font-size:13px;font-weight:600;color:#64748b;">(${vehicles.length} vehicle${vehicles.length === 1 ? '' : 's'})</span></h2>
        <table class="vehicle-bulk-print-table">
          <thead>
            <tr>
              <th>Reg. No.</th>
              <th>Make / Model</th>
              <th>Year</th>
              <th>Type</th>
              <th>Department</th>
              <th>Mileage</th>
              <th>Insurance Expiry</th>
              <th>Status</th>
              <th>Repairs Done</th>
            </tr>
          </thead>
          <tbody>
            ${vehicles.map((vehicle) => `
              <tr>
                <td>${escapeHtml(vehicle.registration)}</td>
                <td>${escapeHtml(`${vehicle.make} ${vehicle.model}`.trim())}</td>
                <td>${escapeHtml(vehicle.year)}</td>
                <td>${escapeHtml(vehicle.vehicleType)}</td>
                <td>${escapeHtml(vehicle.department)}</td>
                <td>${escapeHtml(vehicle.currentMileage)}</td>
                <td>${escapeHtml(vehicle.insuranceExpiry)}</td>
                <td>${escapeHtml(vehicle.status)}</td>
                <td>${escapeHtml(vehicle.repairsDone)}</td>
              </tr>
            `).join('')}
          </tbody>
        </table>
      </section>
    `).join('');

  const markup = `
    <header class="vehicle-bulk-print-header">
      <h1>Fleet Vehicles Report</h1>
      <p>${rowsToPrint.length} vehicle${rowsToPrint.length === 1 ? '' : 's'} grouped by ${escapeHtml(groupLabels[groupField] || 'Category')}.</p>
    </header>
    ${sectionsMarkup}
  `;

  printVehicleSheetMarkup('Fleet Vehicles Report', markup);
}

function buildDriverPrintContact(row) {
  const parts = [row.dataset.phone || '', row.dataset.email || '']
    .map((value) => value.trim())
    .filter((value) => value !== '' && value !== '-');

  return parts.length > 0 ? parts.join(' / ') : 'No contact on file';
}

function printDriverSelections() {
  const rowsToPrint = getVisibleDriverRows();

  if (rowsToPrint.length === 0) {
    return;
  }

  const printWindow = window.open('', '_blank', 'width=1100,height=800');
  if (!printWindow) {
    return;
  }

  const styles = Array.from(document.querySelectorAll('style, link[rel="stylesheet"]'))
    .map((node) => node.outerHTML)
    .join('');

  const cardsMarkup = rowsToPrint.map((row) => {
    const name = row.dataset.fullName || 'Driver';
    const department = row.dataset.department || 'No department recorded';
    const vehicle = row.dataset.assignedVehicle || 'Unassigned';
    const contact = buildDriverPrintContact(row);
    const permitExpiry = row.dataset.licenseExpiry || '-';
    const licenseNumber = row.dataset.licenseNumber || '-';
    const status = row.dataset.statusLabel || '-';
    const employeeId = row.dataset.employeeId || 'Not assigned';

    return `
      <article class="driver-print-card">
        <div class="driver-print-card__header">
          <h2>${name}</h2>
          <span>${status}</span>
        </div>
        <div class="driver-print-grid">
          <div><strong>Name</strong><p>${name}</p></div>
          <div><strong>Department</strong><p>${department}</p></div>
          <div><strong>Vehicle</strong><p>${vehicle}</p></div>
          <div><strong>Contact</strong><p>${contact}</p></div>
          <div><strong>Permit Expiry Date</strong><p>${permitExpiry}</p></div>
          <div><strong>License Number</strong><p>${licenseNumber}</p></div>
          <div><strong>Employee ID</strong><p>${employeeId}</p></div>
        </div>
      </article>
    `;
  }).join('');

  printWindow.document.open();
  printWindow.document.write(`
    <!DOCTYPE html>
    <html lang="en">
      <head>
        <meta charset="utf-8">
        <title>Driver Details</title>
        ${styles}
        <style>
          body { background: #ffffff; color: #0f172a; padding: 24px; font-family: Arial, sans-serif; }
          .app-print-banner { margin-bottom: 24px; }
          .app-print-banner img { display: block; width: 100%; max-width: 1200px; margin: 0 auto; }
          .driver-print-header { margin-bottom: 24px; }
          .driver-print-header h1 { margin: 0 0 8px; font-size: 28px; }
          .driver-print-header p { margin: 0; color: #475569; }
          .driver-print-list { display: grid; gap: 16px; }
          .driver-print-card { border: 1px solid #cbd5e1; border-radius: 16px; padding: 20px; break-inside: avoid; }
          .driver-print-card__header { display: flex; justify-content: space-between; gap: 12px; align-items: baseline; margin-bottom: 16px; }
          .driver-print-card__header h2 { margin: 0; font-size: 20px; }
          .driver-print-card__header span { font-size: 14px; color: #475569; }
          .driver-print-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
          .driver-print-grid strong { display: block; margin-bottom: 4px; font-size: 12px; letter-spacing: 0.08em; text-transform: uppercase; color: #64748b; }
          .driver-print-grid p { margin: 0; font-size: 15px; font-weight: 600; }
          @media print {
            body { padding: 0; }
          }
        </style>
      </head>
      <body>
        ${buildPrintBannerMarkup()}
        <header class="driver-print-header">
          <h1>Driver Details</h1>
          <p>Showing ${rowsToPrint.length} driver${rowsToPrint.length === 1 ? '' : 's'}.</p>
        </header>
        <section class="driver-print-list">
          ${cardsMarkup}
        </section>
      </body>
    </html>
  `);
  printWindow.document.close();
  printWindow.focus();
  printWindow.print();
  printWindow.close();
}

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

printSelectedDriversButtons.forEach((button) => {
  button.addEventListener('click', printDriverSelections);
});

// Modal references for all module pages
const vehicleModal = document.querySelector('#vehicle-modal');
const vehicleViewModal = document.querySelector('#vehicle-view-modal');
const vehicleDetailSheet = document.querySelector('[data-vehicle-detail-sheet]');
const vehicleForm = vehicleModal?.querySelector('form');
const vehicleActionField = vehicleModal?.querySelector('[data-vehicle-action-field]');
const vehicleIdField = vehicleModal?.querySelector('[data-vehicle-id-field]');
const vehicleImagePathField = vehicleModal?.querySelector('[data-vehicle-image-path-field]');
const vehicleModalTitle = vehicleModal?.querySelector('[data-vehicle-modal-title]');
const vehicleSubmitButton = vehicleModal?.querySelector('[data-vehicle-submit-button]');
const vehicleRepairsField = vehicleModal?.querySelector('[data-vehicle-repairs-field]');
const openVehicleModalButtons = document.querySelectorAll('[data-open-vehicle-modal]');
const openVehicleViewButtons = document.querySelectorAll('[data-open-vehicle-view]');
const openVehicleEditButtons = document.querySelectorAll('[data-open-vehicle-edit]');
const closeVehicleModalButtons = document.querySelectorAll('[data-close-vehicle-modal]');
const closeVehicleViewModalButtons = document.querySelectorAll('[data-close-vehicle-view-modal]');
const openVehicleDeleteButtons = document.querySelectorAll('[data-open-vehicle-delete]');
const printVehicleViewButton = document.querySelector('[data-print-vehicle-view]');
const vehicleDeleteModal = document.querySelector('#vehicle-delete-modal');
const cancelVehicleDeleteButton = document.querySelector('[data-cancel-vehicle-delete]');
const confirmVehicleDeleteButton = document.querySelector('[data-confirm-vehicle-delete]');
const estateViewModal = document.querySelector('#estate-view-modal');
const openEstateViewModalButtons = document.querySelectorAll('[data-open-estate-view-modal]');
const closeEstateViewModalButtons = document.querySelectorAll('[data-close-estate-view-modal]');
const estateEditModal = document.querySelector('#estate-edit-modal');
const openEstateEditModalButtons = document.querySelectorAll('[data-open-estate-edit-modal]');
const closeEstateEditModalButtons = document.querySelectorAll('[data-close-estate-edit-modal]');
const openEstateDeleteButtons = document.querySelectorAll('[data-open-estate-delete]');
const estateDeleteModal = document.querySelector('#estate-delete-modal');
const cancelEstateDeleteButton = document.querySelector('[data-cancel-estate-delete]');
const confirmEstateDeleteButton = document.querySelector('[data-confirm-estate-delete]');
const estateEditProgress = document.querySelector('[data-estate-edit-progress]');
const estateEditProgressLabel = document.querySelector('[data-estate-edit-progress-label]');
const estateNewModal = document.querySelector('#estate-new-modal');
const openEstateNewModalButtons = document.querySelectorAll('[data-open-estate-new-modal]');
const closeEstateNewModalButtons = document.querySelectorAll('[data-close-estate-new-modal]');
const estateNewProgress = document.querySelector('[data-estate-new-progress]');
const estateNewProgressLabel = document.querySelector('[data-estate-new-progress-label]');
let activeVehicleViewRow = null;

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

// Vehicle page modal behavior
function setVehicleFormMode(mode) {
  const isUpdateMode = mode === 'update';

  if (vehicleActionField) {
    vehicleActionField.value = isUpdateMode ? 'update' : 'create';
  }

  if (vehicleModalTitle) {
    vehicleModalTitle.textContent = isUpdateMode ? 'Edit Vehicle' : 'Add New Vehicle';
  }

  if (vehicleSubmitButton) {
    vehicleSubmitButton.textContent = isUpdateMode ? 'Save Changes' : 'Add Vehicle';
  }

  if (vehicleRepairsField) {
    vehicleRepairsField.classList.toggle('hidden', !isUpdateMode);
    vehicleRepairsField.classList.toggle('block', isUpdateMode);
  }
}

function setVehicleFieldValue(selector, value) {
  const field = vehicleModal?.querySelector(selector);

  if (field) {
    field.value = value;
  }
}

function setVehicleViewText(selector, value) {
  const field = vehicleViewModal?.querySelector(selector);

  if (field) {
    field.textContent = value;
  }
}

function setVehicleImagePreview(storedPath = '', fileUrl = '', fileName = '', isImage = false) {
  const preview = vehicleModal?.querySelector('[data-vehicle-image-preview]');
  const image = vehicleModal?.querySelector('[data-vehicle-image-preview-tag]');
  const link = vehicleModal?.querySelector('[data-vehicle-image-link]');

  if (!preview || !image || !link) {
    return;
  }

  const hasFile = storedPath.trim() !== '';

  preview.classList.toggle('hidden', !hasFile);
  preview.classList.toggle('block', hasFile);
  image.src = hasFile ? fileUrl : '';
  image.classList.toggle('hidden', !(hasFile && isImage));
  image.classList.toggle('block', hasFile && isImage);
  link.href = hasFile ? fileUrl : '#';
  link.textContent = hasFile ? (fileName || storedPath.split('/').pop() || 'Vehicle image') : '';
}

function setVehicleViewImage(imageUrl = '', isImage = false) {
  const imageWrap = vehicleViewModal?.querySelector('[data-vehicle-view-image-wrap]');
  const image = vehicleViewModal?.querySelector('[data-vehicle-view-image]');

  if (!imageWrap || !image) {
    return;
  }

  const showImage = imageUrl.trim() !== '' && isImage;
  image.src = showImage ? imageUrl : '';
  imageWrap.classList.toggle('hidden', !showImage);
  imageWrap.classList.toggle('block', showImage);
}

function setVehicleViewModalOpen(isOpen) {
  if (!vehicleViewModal) {
    return;
  }

  vehicleViewModal.classList.toggle('hidden', !isOpen);
  vehicleViewModal.classList.toggle('flex', isOpen);
  vehicleViewModal.setAttribute('aria-hidden', String(!isOpen));
  document.body.classList.toggle('overflow-hidden', isOpen);

  if (isOpen) {
    vehicleViewModal.querySelector('button')?.focus();
  } else {
    activeVehicleViewRow = null;
    openVehicleViewButtons[0]?.focus();
  }
}

function populateVehicleViewModal(button) {
  const row = button.closest('.vehicle-row');
  if (!row) {
    return;
  }

  activeVehicleViewRow = row;

  const registration = row.dataset.registrationNumber || '-';
  const make = row.dataset.make || '-';
  const model = row.dataset.model || '-';
  const year = row.dataset.year || '-';
  const type = row.dataset.vehicleType || '-';
  const fuel = row.dataset.fuelType || '-';
  const department = row.dataset.department || '-';
  const mileage = row.dataset.currentMileage ? `${row.dataset.currentMileage} km` : '-';
  const insurance = row.dataset.insuranceExpiry || 'Not set';
  const status = row.dataset.statusLabel || '-';
  const repairs = row.dataset.repairsDone || 'No repairs recorded.';
  const imageUrl = row.dataset.vehicleImageUrl || '';
  const imageIsImage = row.dataset.vehicleImageIsImage === 'true';

  setVehicleViewText('[data-vehicle-view-name]', registration);
  setVehicleViewText('[data-vehicle-view-subtitle]', `${make} ${model}`.trim());
  setVehicleViewText('[data-vehicle-view-registration]', registration);
  setVehicleViewText('[data-vehicle-view-make]', make);
  setVehicleViewText('[data-vehicle-view-model]', model);
  setVehicleViewText('[data-vehicle-view-year]', year);
  setVehicleViewText('[data-vehicle-view-type]', type);
  setVehicleViewText('[data-vehicle-view-fuel]', fuel);
  setVehicleViewText('[data-vehicle-view-department]', department);
  setVehicleViewText('[data-vehicle-view-status]', status);
  setVehicleViewText('[data-vehicle-view-mileage]', mileage);
  setVehicleViewText('[data-vehicle-view-insurance]', insurance);
  setVehicleViewText('[data-vehicle-view-repairs]', repairs);
  setVehicleViewImage(imageUrl, imageIsImage);
}

function printVehicleDetailSheet() {
  const visibleRows = getVisibleVehicleRows();
  const sourceRow = activeVehicleViewRow || (visibleRows.length === 1 ? visibleRows[0] : null);

  if (!sourceRow) {
    return;
  }

  const vehicle = getVehicleRowData(sourceRow);
  printVehicleSheetMarkup('Vehicle Details', buildVehicleDetailSheetMarkup(vehicle));
}

function resetVehicleFormForCreate() {
  vehicleForm?.reset();

  if (vehicleIdField) {
    vehicleIdField.value = '';
  }

  if (vehicleImagePathField) {
    vehicleImagePathField.value = '';
  }

  setVehicleFormMode('create');
  setVehicleFieldValue('select[name="vehicle_type"]', 'sedan');
  setVehicleFieldValue('select[name="fuel_type"]', 'diesel');
  setVehicleFieldValue('input[name="current_mileage"]', '0');
  setVehicleFieldValue('select[name="status"]', 'active');
  setVehicleFieldValue('textarea[name="repairs_done"]', '');
  setVehicleImagePreview('', '', '', false);
}

function populateVehicleEditForm(button) {
  const row = button.closest('.vehicle-row');
  if (!row) {
    return;
  }

  setVehicleFormMode('update');

  if (vehicleIdField) {
    vehicleIdField.value = row.dataset.vehicleId || '';
  }

  if (vehicleImagePathField) {
    vehicleImagePathField.value = row.dataset.vehicleImage || '';
  }

  setVehicleFieldValue('input[name="registration_number"]', row.dataset.registrationNumber || '');
  setVehicleFieldValue('input[name="make"]', row.dataset.make || '');
  setVehicleFieldValue('input[name="model"]', row.dataset.model || '');
  setVehicleFieldValue('input[name="year"]', row.dataset.year || '');
  setVehicleFieldValue('select[name="vehicle_type"]', row.dataset.vehicleType || 'sedan');
  setVehicleFieldValue('select[name="fuel_type"]', row.dataset.fuelType || 'diesel');
  setVehicleFieldValue('input[name="department"]', row.dataset.department || '');
  setVehicleFieldValue('input[name="current_mileage"]', row.dataset.currentMileage || '0');
  setVehicleFieldValue('input[name="insurance_expiry"]', row.dataset.insuranceExpiry || '');
  setVehicleFieldValue('select[name="status"]', row.dataset.status || 'active');
  setVehicleFieldValue('textarea[name="repairs_done"]', row.dataset.repairsDone || '');
  setVehicleImagePreview(
    row.dataset.vehicleImage || '',
    row.dataset.vehicleImageUrl || '',
    row.dataset.vehicleImageName || 'Vehicle image',
    row.dataset.vehicleImageIsImage === 'true'
  );
}

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

let pendingVehicleDeleteForm = null;

// Opens or closes the custom vehicle delete confirmation modal.
function setVehicleDeleteModalOpen(isOpen, form = null) {
  if (!vehicleDeleteModal) {
    return;
  }

  pendingVehicleDeleteForm = isOpen ? form : null;

  if (isOpen) {
    setDeletePreview(
      vehicleDeleteModal,
      '[data-vehicle-delete-name]',
      '[data-vehicle-delete-detail]',
      form,
      'This vehicle',
      'Registration and basic details will appear here.'
    );
  }

  vehicleDeleteModal.classList.toggle('is-open', isOpen);
  vehicleDeleteModal.setAttribute('aria-hidden', String(!isOpen));
  document.body.classList.toggle('overflow-hidden', isOpen);

  if (isOpen) {
    confirmVehicleDeleteButton?.focus();
  }
}

openVehicleModalButtons.forEach((button) => {
  button.addEventListener('click', () => {
    resetVehicleFormForCreate();
    setVehicleModalOpen(true);
  });
});

openVehicleEditButtons.forEach((button) => {
  button.addEventListener('click', () => {
    populateVehicleEditForm(button);
    setVehicleModalOpen(true);
  });
});

openVehicleViewButtons.forEach((button) => {
  button.addEventListener('click', () => {
    populateVehicleViewModal(button);
    setVehicleViewModalOpen(true);
  });
});

openVehicleDeleteButtons.forEach((button) => {
  button.addEventListener('click', (event) => {
    event.preventDefault();
    const form = button.closest('form');

    if (form) {
      setVehicleDeleteModalOpen(true, form);
    }
  });
});

closeVehicleModalButtons.forEach((button) => {
  button.addEventListener('click', () => setVehicleModalOpen(false));
});

closeVehicleViewModalButtons.forEach((button) => {
  button.addEventListener('click', () => setVehicleViewModalOpen(false));
});

vehicleModal?.addEventListener('click', (event) => {
  if (event.target === vehicleModal) {
    setVehicleModalOpen(false);
  }
});

vehicleViewModal?.addEventListener('click', (event) => {
  if (event.target === vehicleViewModal) {
    setVehicleViewModalOpen(false);
  }
});

vehicleDeleteModal?.addEventListener('click', (event) => {
  if (event.target === vehicleDeleteModal) {
    setVehicleDeleteModalOpen(false);
  }
});

confirmVehicleDeleteButton?.addEventListener('click', () => {
  if (!pendingVehicleDeleteForm) {
    setVehicleDeleteModalOpen(false);
    return;
  }

  const form = pendingVehicleDeleteForm;
  setVehicleDeleteModalOpen(false);
  form.submit();
});

cancelVehicleDeleteButton?.addEventListener('click', () => {
  setVehicleDeleteModalOpen(false);
});

if (vehicleModal?.dataset.openOnLoad === 'true') {
  // When the server returns the page with the modal already open, keep body scroll locked.
  document.body.classList.add('overflow-hidden');
}

printVehicleViewButton?.addEventListener('click', printVehicleDetailSheet);
printVehiclesButton?.addEventListener('click', printVehiclesByCategory);

document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape' && vehicleViewModal && !vehicleViewModal.classList.contains('hidden')) {
    setVehicleViewModalOpen(false);
  }

  if (event.key === 'Escape' && vehicleDeleteModal && vehicleDeleteModal.classList.contains('is-open')) {
    setVehicleDeleteModalOpen(false);
  }
});

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
  setEstateInput('[data-estate-edit-start]', projectCard.dataset.startRaw || projectCard.dataset.start);
  setEstateInput('[data-estate-edit-deadline]', projectCard.dataset.deadlineRaw || projectCard.dataset.deadline);
  setEstateInput('[data-estate-edit-budget]', projectCard.dataset.budgetRaw || stripCurrency(projectCard.dataset.budget));
  setEstateInput('[data-estate-edit-spent]', projectCard.dataset.spentRaw || stripCurrency(projectCard.dataset.spent));
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

let pendingEstateDeleteForm = null;

// Opens or closes the custom estate delete confirmation modal.
function setEstateDeleteModalOpen(isOpen, form = null) {
  if (!estateDeleteModal) {
    return;
  }

  pendingEstateDeleteForm = isOpen ? form : null;

  if (isOpen) {
    setDeletePreview(
      estateDeleteModal,
      '[data-estate-delete-name]',
      '[data-estate-delete-detail]',
      form,
      'This project',
      'Project code and location will appear here.'
    );
  }

  estateDeleteModal.classList.toggle('is-open', isOpen);
  estateDeleteModal.setAttribute('aria-hidden', String(!isOpen));
  document.body.classList.toggle('overflow-hidden', isOpen);

  if (isOpen) {
    confirmEstateDeleteButton?.focus();
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

openEstateDeleteButtons.forEach((button) => {
  button.addEventListener('click', (event) => {
    event.preventDefault();
    const form = button.closest('form');

    if (form) {
      setEstateDeleteModalOpen(true, form);
    }
  });
});

estateEditProgress?.addEventListener('input', (event) => {
  if (estateEditProgressLabel) {
    estateEditProgressLabel.textContent = event.target.value;
  }
});

if (estateEditModal?.dataset.openOnLoad === 'true') {
  document.body.classList.add('overflow-hidden');
}

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

if (estateNewModal?.dataset.openOnLoad === 'true') {
  document.body.classList.add('overflow-hidden');
}

confirmEstateDeleteButton?.addEventListener('click', () => {
  if (!pendingEstateDeleteForm) {
    setEstateDeleteModalOpen(false);
    return;
  }

  const form = pendingEstateDeleteForm;
  setEstateDeleteModalOpen(false);
  form.submit();
});

cancelEstateDeleteButton?.addEventListener('click', () => {
  setEstateDeleteModalOpen(false);
});

estateDeleteModal?.addEventListener('click', (event) => {
  if (event.target === estateDeleteModal) {
    setEstateDeleteModalOpen(false);
  }
});

document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape' && estateNewModal && !estateNewModal.classList.contains('hidden')) {
    setEstateNewModalOpen(false);
  }

  if (event.key === 'Escape' && estateDeleteModal && estateDeleteModal.classList.contains('is-open')) {
    setEstateDeleteModalOpen(false);
  }
});

// Landing page vehicle showcase
window.nextVehicle = window.nextVehicle || function nextVehicleFallback() {};
window.previousVehicle = window.previousVehicle || function previousVehicleFallback() {};

const fleetShowcase = document.querySelector('[data-fleet-showcase]');

if (fleetShowcase) {
  const fleetVehicles = [
    {
      registration: 'UBQ 123C',
      model: 'Toyota Prado',
      type: 'Administrative SUV',
      status: 'Available',
      capacity: '7 Seater',
      usage: 'Executive field coordination',
      description: 'Reliable field-ready transport for official university movement, inspections, and administrative duty.',
      image: 'assets/images/fleet-showcase/prado-garage.png',
      alt: 'Toyota Prado university fleet vehicle',
    },
    {
      registration: 'UG 347M',
      model: 'Toyota Hiace',
      type: 'Staff Transport Van',
      status: 'Reserved',
      capacity: '14 Seater',
      usage: 'Campus and inter-campus staff movement',
      description: 'A dependable transport van used for scheduled staff transfers, committee travel, and coordinated academic trips.',
      image: 'assets/images/fleet-showcase/hiace-garage.png',
      alt: 'Toyota Hiace staff transport van',
    },
    {
      registration: 'UAK 881P',
      model: 'Mitsubishi Canter',
      type: 'Works Utility Truck',
      status: 'In Service',
      capacity: 'Heavy Utility',
      usage: 'Estates maintenance logistics',
      description: 'Supports estates and works activity through material movement, maintenance deployment, and site operations support.',
      image: 'assets/images/fleet-showcase/canter-garage.png',
      alt: 'Mitsubishi Canter works truck',
    },
    {
      registration: 'UBE 554K',
      model: 'Toyota Corolla',
      type: 'Departmental Sedan',
      status: 'Available',
      capacity: '5 Seater',
      usage: 'Routine departmental travel',
      description: 'Efficient official transport for departmental errands, documentation runs, and light operational assignments.',
      image: 'assets/images/fleet-showcase/corolla-garage.png',
      alt: 'Toyota Corolla departmental vehicle',
    },
  ];

  const previousVehicleButton = fleetShowcase.querySelector('[data-vehicle-prev]');
  const nextVehicleButton = fleetShowcase.querySelector('[data-vehicle-next]');
  const showcaseImage = fleetShowcase.querySelector('[data-vehicle-image]');
  const showcaseModel = fleetShowcase.querySelector('[data-vehicle-model]');
  const showcaseStatus = fleetShowcase.querySelector('[data-vehicle-status]');
  const showcaseRegistration = fleetShowcase.querySelector('[data-vehicle-registration]');
  const showcaseType = fleetShowcase.querySelector('[data-vehicle-type]');
  const showcaseCapacity = fleetShowcase.querySelector('[data-vehicle-capacity]');
  const showcaseUsage = fleetShowcase.querySelector('[data-vehicle-usage]');
  const showcaseSubtitle = fleetShowcase.querySelector('[data-vehicle-subtitle]');
  const showcaseCaption = fleetShowcase.querySelector('[data-vehicle-caption]');
  const showcaseDescription = fleetShowcase.querySelector('[data-vehicle-description]');
  const showcaseDots = fleetShowcase.querySelector('[data-vehicle-dots]');

  let currentVehicleIndex = 0;
  let vehicleAutoRotateId = null;

  function getStatusClass(status) {
    if (status === 'Available') {
      return 'is-available';
    }

    if (status === 'Reserved') {
      return 'is-reserved';
    }

    return 'is-service';
  }

  function renderDots() {
    if (!showcaseDots) {
      return;
    }

    showcaseDots.innerHTML = '';

    fleetVehicles.forEach((vehicle, index) => {
      const dot = document.createElement('button');
      dot.type = 'button';
      dot.className = `landing-showcase-dot${index === currentVehicleIndex ? ' is-active' : ''}`;
      dot.setAttribute('aria-label', `Show ${vehicle.model}`);
      dot.onclick = () => {
        setFleetVehicle(index);
        restartVehicleAutoRotate();
      };
      showcaseDots.append(dot);
    });
  }

  function renderVehicle(index) {
    const vehicle = fleetVehicles[index];

    if (!vehicle) {
      return;
    }

    showcaseModel.textContent = vehicle.model;
    showcaseStatus.textContent = vehicle.status;
    showcaseStatus.className = `landing-status-pill ${getStatusClass(vehicle.status)}`;
    showcaseRegistration.textContent = vehicle.registration;
    showcaseType.textContent = vehicle.type;
    showcaseCapacity.textContent = vehicle.capacity;
    showcaseUsage.textContent = vehicle.usage;
    showcaseSubtitle.textContent = vehicle.model;
    if (showcaseCaption) {
      showcaseCaption.textContent = `${vehicle.type} • ${vehicle.registration}`;
    }
    showcaseDescription.textContent = vehicle.description;

    if (!showcaseImage) {
      renderDots();
      return;
    }

    showcaseImage.classList.remove('is-visible');
    showcaseImage.src = vehicle.image;
    showcaseImage.alt = vehicle.alt;
    window.requestAnimationFrame(() => {
      showcaseImage.classList.add('is-visible');
    });

    renderDots();
  }

  function setFleetVehicle(index) {
    currentVehicleIndex = (index + fleetVehicles.length) % fleetVehicles.length;
    fleetShowcase.dataset.activeVehicleIndex = String(currentVehicleIndex);
    renderVehicle(currentVehicleIndex);
  }

  function restartVehicleAutoRotate() {
    window.clearInterval(vehicleAutoRotateId);
    vehicleAutoRotateId = window.setInterval(() => {
      setFleetVehicle(currentVehicleIndex + 1);
    }, 5000);
  }

  window.nextVehicle = function nextVehicle() {
    setFleetVehicle(currentVehicleIndex + 1);
    restartVehicleAutoRotate();
  };

  window.previousVehicle = function previousVehicle() {
    setFleetVehicle(currentVehicleIndex - 1);
    restartVehicleAutoRotate();
  };

  previousVehicleButton.onclick = window.previousVehicle;
  nextVehicleButton.onclick = window.nextVehicle;

  fleetVehicles.forEach((vehicle) => {
    const image = new Image();
    image.src = vehicle.image;
  });

  window.__fleetShowcaseState = {
    fleetVehicles,
    getCurrentVehicleIndex: () => currentVehicleIndex,
    setFleetVehicle,
  };

  setFleetVehicle(currentVehicleIndex);
  restartVehicleAutoRotate();
}
