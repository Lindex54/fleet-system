/* global jQuery */

// Initializes shared Fleet System jQuery helpers once the document is ready.
$(document).ready(function () {
  // Handles inline validation and double-submit protection for all non-filter forms.
  initializeFleetFormValidation();

  // Shows selected filenames and preview thumbnails for supported image uploads.
  initializeFleetFileEnhancements();

  // Handles AJAX submission for selected forms while preserving PHP fallback behavior.
  initializeFleetAjaxForms();

  // Updates helper text around dynamic fields such as maintenance completion dates.
  initializeFleetDynamicFields();

  // Shows a clean "no records found" state when existing table filters hide every row.
  initializeFleetTableFeedback();
});

// Shows a reusable success or error message near the active form.
function showFleetMessage(type, message, $context) {
  const $scope = $context && $context.length ? $context : $('body');
  const messageClass = type === 'success' ? 'fleet-inline-feedback-success' : 'fleet-inline-feedback-error';
  let $host = $scope.find('[data-fleet-feedback-host]').first();

  if ($host.length === 0 && $scope.is('form')) {
    $host = $('<div data-fleet-feedback-host></div>');
    $scope.prepend($host);
  }

  if ($host.length === 0) {
    $host = $('<div data-fleet-feedback-host></div>').prependTo('body');
  }

  $host.html(
    $('<div></div>')
      .addClass(`fleet-inline-feedback ${messageClass}`)
      .text(message)
  );
}

// Disables a submit button and swaps in temporary loading text.
function setButtonLoading($button, loadingText) {
  if (!$button || !$button.length) {
    return;
  }

  if (!$button.data('fleet-original-text')) {
    $button.data('fleet-original-text', $.trim($button.text()));
  }

  $button.prop('disabled', true).attr('aria-busy', 'true').text(loadingText || 'Saving...');
}

// Restores a submit button after validation or AJAX completes.
function resetButtonLoading($button) {
  if (!$button || !$button.length) {
    return;
  }

  $button.prop('disabled', false).removeAttr('aria-busy');

  if ($button.data('fleet-original-text')) {
    $button.text($button.data('fleet-original-text'));
  }
}

// Adds or updates one inline validation error under the active field.
function showFleetFieldError($field, message) {
  clearFleetFieldError($field);
  $field.addClass('fleet-invalid-field').attr('aria-invalid', 'true');
  $('<div class="fleet-field-error" data-fleet-field-error></div>').text(message).insertAfter($field);
}

// Removes any existing inline validation error for the active field.
function clearFleetFieldError($field) {
  $field.removeClass('fleet-invalid-field').removeAttr('aria-invalid');
  $field.siblings('[data-fleet-field-error]').first().remove();
}

// Validates one field according to its HTML type, required state, and upload rules.
function validateFleetField($field) {
  if (!$field.length || $field.is(':disabled') || $field.attr('type') === 'hidden') {
    return true;
  }

  const tagName = ($field.prop('tagName') || '').toLowerCase();
  const fieldType = (($field.attr('type') || tagName) + '').toLowerCase();
  const value = $.trim($field.val() || '');
  const isRequired = $field.prop('required');
  const phonePattern = /^\+?[0-9\s\-()]{7,20}$/;

  clearFleetFieldError($field);

  if (isRequired) {
    if (fieldType === 'file' && (!$field[0].files || $field[0].files.length === 0)) {
      showFleetFieldError($field, 'This file is required.');
      return false;
    }

    if (fieldType !== 'file' && value === '') {
      showFleetFieldError($field, 'This field is required.');
      return false;
    }
  }

  if (value !== '') {
    if (fieldType === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
      showFleetFieldError($field, 'Enter a valid email address.');
      return false;
    }

    if (fieldType === 'tel' && !phonePattern.test(value)) {
      showFleetFieldError($field, 'Enter a valid phone number.');
      return false;
    }

    if (fieldType === 'date' && Number.isNaN(new Date(value).getTime())) {
      showFleetFieldError($field, 'Enter a valid date.');
      return false;
    }

    if (fieldType === 'number') {
      const numericValue = Number(value);
      const minValue = $field.attr('min');
      const maxValue = $field.attr('max');

      if (Number.isNaN(numericValue)) {
        showFleetFieldError($field, 'Enter a valid number.');
        return false;
      }

      if (minValue !== undefined && minValue !== '' && numericValue < Number(minValue)) {
        showFleetFieldError($field, `Value must be at least ${minValue}.`);
        return false;
      }

      if (maxValue !== undefined && maxValue !== '' && numericValue > Number(maxValue)) {
        showFleetFieldError($field, `Value must not exceed ${maxValue}.`);
        return false;
      }
    }
  }

  if (fieldType === 'file' && $field[0].files && $field[0].files.length > 0) {
    const file = $field[0].files[0];
    const acceptRules = ($field.attr('accept') || '').split(',').map((rule) => $.trim(rule.toLowerCase())).filter(Boolean);
    const maxBytes = Number($field.data('maxFileSize') || 5242880);
    const extension = `.${(file.name.split('.').pop() || '').toLowerCase()}`;

    if (file.size > maxBytes) {
      showFleetFieldError($field, 'Selected file is too large.');
      return false;
    }

    if (acceptRules.length > 0) {
      const matchesAcceptRule = acceptRules.some((rule) => {
        if (rule.startsWith('.')) {
          return rule === extension;
        }

        if (rule.endsWith('/*')) {
          return file.type.toLowerCase().startsWith(rule.replace('/*', '/'));
        }

        return file.type.toLowerCase() === rule;
      });

      if (!matchesAcceptRule) {
        showFleetFieldError($field, 'Selected file type is not allowed.');
        return false;
      }
    }
  }

  return true;
}

// Validates a whole form before it is submitted through PHP or AJAX.
function validateFleetForm($form) {
  let isValid = true;

  $form.find('input, select, textarea').each(function validateFieldLoop() {
    const $field = $(this);
    if (!validateFleetField($field)) {
      isValid = false;
    }
  });

  if (!isValid) {
    const $firstInvalid = $form.find('.fleet-invalid-field').first();
    if ($firstInvalid.length) {
      $firstInvalid.trigger('focus');
    }
  }

  return isValid;
}

// Binds global validation cleanup and double-submit protection to active forms.
function initializeFleetFormValidation() {
  $('form').each(function prepareFleetForm() {
    const $form = $(this);
    const method = ($form.attr('method') || 'get').toLowerCase();
    const isFilterForm = method === 'get' || $form.is('[data-vehicle-usage-filter-form]');

    if (isFilterForm) {
      return;
    }

    $form.on('input change', 'input, select, textarea', function handleFieldCorrection() {
      validateFleetField($(this));
    });

    $form.on('submit', function handleFormSubmit(event) {
      const $submitButton = $form.find('button[type="submit"], input[type="submit"]').first();

      if (!validateFleetForm($form)) {
        event.preventDefault();
        resetButtonLoading($submitButton);
        return;
      }

      if ($form.data('fleetSubmitting') === true) {
        event.preventDefault();
        return;
      }

      $form.data('fleetSubmitting', true);
      setButtonLoading($submitButton, $submitButton.data('loadingText') || 'Processing...');
    });
  });
}

// Shows selected upload metadata and image previews for image-compatible file inputs.
function initializeFleetFileEnhancements() {
  $('input[type="file"]').each(function prepareFileInput() {
    const $input = $(this);
    const $meta = $('<div class="fleet-file-meta" data-fleet-file-meta></div>');
    const $preview = $('<div class="fleet-file-preview" data-fleet-file-preview><img alt="Selected preview"></div>');

    $input.after($meta);
    $meta.after($preview);

    $input.on('change', function handleFileSelection() {
      clearFleetFieldError($input);

      const file = this.files && this.files.length ? this.files[0] : null;
      if (!file) {
        $meta.text('');
        $preview.hide().find('img').attr('src', '');
        return;
      }

      $meta.text(`Selected file: ${file.name}`);
      validateFleetField($input);

      // Shows image preview when a user selects an image-capable upload file.
      if (file.type.toLowerCase().startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function renderPreview(event) {
          $preview.show().find('img').attr('src', event.target.result);
        };
        reader.readAsDataURL(file);
      } else {
        $preview.hide().find('img').attr('src', '');
      }
    });
  });
}

// Sends safe AJAX requests for selected forms while preserving standard PHP fallback.
function initializeFleetAjaxForms() {
  $('form[data-fleet-ajax="true"]').each(function bindAjaxForm() {
    const $form = $(this);

    $form.on('submit', function handleAjaxSubmit(event) {
      if (!validateFleetForm($form)) {
        event.preventDefault();
        return;
      }

      event.preventDefault();

      const $submitButton = $form.find('button[type="submit"], input[type="submit"]').first();
      const useFormData = ($form.attr('enctype') || '').toLowerCase() === 'multipart/form-data' || $form.find('input[type="file"]').length > 0;
      const ajaxOptions = {
        url: $form.attr('action') || window.location.href,
        type: ($form.attr('method') || 'POST').toUpperCase(),
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
      };

      setButtonLoading($submitButton, $submitButton.data('loadingText') || 'Saving...');

      if (useFormData) {
        ajaxOptions.data = new FormData($form[0]);
        ajaxOptions.processData = false;
        ajaxOptions.contentType = false;
      } else {
        ajaxOptions.data = $form.serialize();
      }

      $.ajax(ajaxOptions)
        .done(function handleAjaxSuccess(response) {
          if (!response || response.success !== true) {
            showFleetMessage('error', (response && response.message) || 'Something went wrong. Please try again.', $form);
            return;
          }

          showFleetMessage('success', response.message || 'Saved successfully.', $form);

          if ($form.data('fleetResetOnSuccess') === true || response.reset_form === true) {
            $form[0].reset();
            $form.find('[data-fleet-file-meta]').text('');
            $form.find('[data-fleet-file-preview]').hide().find('img').attr('src', '');
            $form.find('.fleet-field-error').remove();
            $form.find('.fleet-invalid-field').removeClass('fleet-invalid-field').removeAttr('aria-invalid');
          }

          $form.data('fleetSubmitting', false);

          if (response.reload === true) {
            window.setTimeout(function reloadPageAfterAjax() {
              window.location.reload();
            }, 900);
            return;
          }

          if (response.redirect) {
            window.setTimeout(function redirectAfterAjax() {
              window.location.assign(response.redirect);
            }, 900);
          }
        })
        .fail(function handleAjaxFailure(xhr) {
          const response = xhr.responseJSON || {};
          showFleetMessage('error', response.message || 'Request failed. Please try again.', $form);
          $form.data('fleetSubmitting', false);
        })
        .always(function alwaysResetButton() {
          resetButtonLoading($submitButton);
        });
    });
  });
}

// Adds lightweight dynamic field behavior where field choices affect related inputs.
function initializeFleetDynamicFields() {
  // Shows date completed only when maintenance status is completed.
  $(document).on('change', '[data-maintenance-form] select[name="status"]', function handleMaintenanceStatus() {
    const $status = $(this);
    const $dateCompleted = $status.closest('form').find('input[name="date_completed"]').closest('label');
    const isCompleted = $status.val() === 'completed';

    $dateCompleted.toggleClass('opacity-60', !isCompleted);
  }).trigger('change');

  // Auto-fills the starting odometer from the selected trip vehicle when available.
  $(document).on('change', 'form select[name="vehicle_id"]', function handleTripVehicleChange() {
    const $select = $(this);
    const currentMileage = $select.find(':selected').data('currentMileage');
    const $odometerStart = $select.closest('form').find('input[name="odometer_start"]');

    if ($odometerStart.length && (String($odometerStart.val() || '').trim() === '') && currentMileage !== undefined) {
      $odometerStart.val(currentMileage);
    }
  }).trigger('change');

  // Highlights remark boxes when a post-inspection system is marked faulty.
  $(document).on('change', 'input[type="radio"][name^="system_status["]', function handleSystemStatusChange() {
    const $radio = $(this);
    const $row = $radio.closest('.grid.items-center');
    const $remarks = $row.find('input[name="system_remarks[]"]');
    const isFaulty = $radio.val() === 'faulty' && $radio.is(':checked');

    $remarks.toggleClass('fleet-invalid-field', isFaulty && $.trim($remarks.val() || '') === '');
  });
}

// Shows a reusable "no records found" row when table filters hide all visible rows.
function initializeFleetTableFeedback() {
  const tableBindings = [
    { input: '#vehicle-search', table: '[data-vehicle-table]', row: '.vehicle-row', columns: 10 },
    { input: '#driver-search', table: '[data-driver-table]', row: '.driver-row', columns: 6 },
    { input: '#maintenance-search', table: '[data-maintenance-table]', row: '.maintenance-row', columns: 8 },
    { input: '#logbook-search', table: '[data-logbook-table]', row: '.logbook-row', columns: 8 },
    { input: '#pre-inspection-search', table: '[data-pre-inspection-table]', row: '.pre-inspection-row', columns: 8 },
    { input: '#post-inspection-search', table: '[data-post-inspection-table]', row: '.post-inspection-row', columns: 8 },
  ];

  $.each(tableBindings, function bindTableFeedback(_, binding) {
    const $input = $(binding.input);
    const $table = $(binding.table);

    if ($input.length === 0 || $table.length === 0) {
      return;
    }

    const $tbody = $table.find('tbody').first();
    const $messageRow = $(
      `<tr class="fleet-no-records" data-fleet-no-records><td colspan="${binding.columns}" class="px-5 py-6 text-center text-sm text-fleet-muted">No records found.</td></tr>`
    );

    $tbody.append($messageRow);

    const refreshNoRecordsState = function refreshNoRecordsState() {
      window.setTimeout(function checkRows() {
        const visibleRows = $table.find(binding.row).filter(function onlyVisibleRows() {
          return !$(this).hasClass('hidden') && $(this).css('display') !== 'none';
        }).length;

        $messageRow.toggle(visibleRows === 0);
      }, 40);
    };

    $input.on('input change keyup', refreshNoRecordsState);
    refreshNoRecordsState();
  });
}
