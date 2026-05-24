'use strict';

const API_BASE = '../src/api';

/**
 * Create a DOM element with classes, text, attributes, dataset, and children.
 * @param {string} tag - HTML tag name
 * @param {Object} [options]
 * @param {string[]} [options.classes] - CSS classes to add
 * @param {string} [options.text] - textContent
 * @param {string} [options.title] - title attribute (tooltip)
 * @param {Object<string,string>} [options.dataset] - data-* attributes (camelCase keys)
 * @param {Object<string,string>} [options.attrs] - other attributes (e.g., aria-*)
 * @param {Element[]} [options.children] - children to append in order
 * @returns {HTMLElement}
 */
function el(tag, { classes = [], text, title, dataset = {}, attrs = {}, children = [] } = {}) {
  const node = document.createElement(tag);
  if (classes.length) node.classList.add(...classes);
  if (text !== undefined) node.textContent = text;
  if (title !== undefined) node.title = title;
  Object.assign(node.dataset, dataset);
  for (const [key, value] of Object.entries(attrs)) node.setAttribute(key, value);
  children.forEach(child => node.appendChild(child));
  return node;
}

function setNotesLoading(isLoading) {
  const loading = document.getElementById('notesLoadingState');
  loading.classList.toggle('d-none', !isLoading);
}

function setButtonBusy(btn, isBusy) {
  if (isBusy) {
    btn.disabled = true;

    const icon = btn.querySelector('i');
    if (icon) {
      const spinner = document.createElement('span');
      spinner.classList.add('spinner-border', 'spinner-border-sm', 'js-busy-button-spinner');
      if (btn.textContent.trim()) {
        spinner.classList.add('me-1');
      }
      icon.classList.add('d-none', 'js-hidden-icon');
      icon.before(spinner);
    }
  } else {
    const spinner = btn.querySelector('.js-busy-button-spinner');
    if (spinner) {
      spinner.remove();
      btn.querySelector('.js-hidden-icon').classList.remove('d-none', 'js-hidden-icon');
    }

    btn.disabled = false;
  }
}

// Confirmation modal
function confirmAction({
  title = 'Confirmation',
  message = 'Are you sure?',
  confirmLabel = 'Confirm',
  confirmIcon = 'bi-check-circle',       // 'bi-trash'
  confirmVariant = 'btn-outline-tertiary',    // 'btn-danger'
  cancelLabel = 'Cancel',
  cancelIcon = 'bi-x-circle',        // 'bi-arrow-left'
  cancelVariant = 'btn-outline-tertiary',    // 'btn-danger'
}) {
  const modal = document.getElementById('confirmModal');
  const modalTitle = modal.querySelector('#confirmModalTitle');
  const modalMessage = modal.querySelector('#confirmModalMessage');

  modalTitle.textContent = title;
  modalMessage.textContent = message;

  // Cancel button
  const modalCancelBtn = modal.querySelector('#confirmModalCancelBtn');
  modalCancelBtn.className = `btn ${cancelVariant}`;
  modalCancelBtn.replaceChildren(el('i', { classes: ['bi', cancelIcon, 'me-1'] }), cancelLabel);

  // Confirm button
  const modalConfirmBtn = modal.querySelector('#confirmModalConfirmBtn');
  modalConfirmBtn.className = `btn ${confirmVariant}`;
  modalConfirmBtn.replaceChildren(el('i', { classes: ['bi', confirmIcon, 'me-1'] }), confirmLabel);

  return new Promise(resolve => {
    const controller = new AbortController();

    let confirmed = false;
    modalConfirmBtn.addEventListener('click', () => {
      confirmed = true;
    }, { signal: controller.signal });

    modal.addEventListener('hidden.bs.modal', () => {
      controller.abort();
      resolve(confirmed);
    }, { signal: controller.signal });

    bootstrap.Modal.getOrCreateInstance(modal).show();
  });
}

function showToast({
  message,
  variant = 'info', // success, danger, info by default
  delay = 5000
}) {
  const toastContainer = document.getElementById('toastContainer');

  // Defaulting to info
  let textColor = 'text-light';
  let borderColor = 'border-light';
  if (variant === 'success') {
    textColor = 'text-success';
    borderColor = 'border-success';
  } else if (variant === 'danger') {
    textColor = 'text-danger';
    borderColor = 'border-danger';
  }

  const toastBody = el('div', { classes: ['toast-body'], text: message });
  const toastButton = el('button', {
    classes: ['btn-close', 'me-2', 'm-auto'],
    dataset: {
      bsDismiss: 'toast',
    },
  });

  const toast = el('div', {
    classes: ['toast', 'bg-dark', textColor, borderColor],
    attrs: {
      'role': 'alert',
      'aria-live': 'assertive',
      'aria-atomic': 'true',
    },
    children: [el('div', {
      classes: ['d-flex'],
      children: [toastBody, toastButton]
    })],
  });

  toastContainer.append(toast);

  toast.addEventListener('hidden.bs.toast', () => toast.remove(), { once: true });

  new bootstrap.Toast(toast, { delay }).show();
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', init);
} else {
  init();
}

async function init() {
  // Cache DOM properties
  const noteModal = document.getElementById('noteModal');
  const noteModalTitle = document.getElementById('noteModalTitle');
  const noteForm = document.getElementById('noteForm');
  const noteId = document.getElementById('noteId');
  const title = document.getElementById('title');
  const titleCharCounter = document.getElementById('titleCounter');
  const content = document.getElementById('content');
  const contentCharCounter = document.getElementById('contentCounter');
  const color = document.getElementById('color');
  const isPinned = document.getElementById('isPinned');
  const noteSubmitBtn = document.getElementById('noteSubmitBtn');
  const notesGrid = document.getElementById('notesGrid');

  // Wire the modal show.bs.modal listener
  noteModal.addEventListener('show.bs.modal', (event) => {
    const trigger = event.relatedTarget;
    const mode = trigger.dataset.mode;

    if (mode === "create") {
      noteModalTitle.textContent = 'New note';
      noteId.value = '';
      title.value = '';
      content.value = '';
      color.value = '#212529';
      isPinned.checked = false;
      noteSubmitBtn.replaceChildren(el('i', { classes: ['bi', 'bi-plus-circle', 'me-1'] }), 'Create');
    } else if (mode === "edit") {
      const dataset = trigger.dataset;

      noteModalTitle.textContent = 'Edit note';
      noteId.value = dataset.noteId;
      title.value = dataset.title;
      content.value = dataset.content;
      color.value = dataset.color;
      isPinned.checked = dataset.pinned === "true";
      noteSubmitBtn.replaceChildren(el('i', { classes: ['bi', 'bi-check-circle', 'me-1'] }), 'Save');
    }

    titleCharCounter.textContent = title.value.length + " / 255";
    contentCharCounter.textContent = content.value.length + " / 5000";
  });

  noteModal.addEventListener('shown.bs.modal', () => {
    title.focus();
  });

  // Initial render
  reloadNotes();

  // Create or Edit a note
  noteForm.addEventListener('submit', async (event) => {
    event.preventDefault();

    const payload = {
      title: title.value.trim(),
      content: content.value.trim() || null,
      color: color.value,
      is_pinned: isPinned.checked,
    };

    setButtonBusy(noteSubmitBtn, true);
    const successMessage = noteId.value === '' ? 'Note created.' : 'Note saved.';
    const errorMessage = noteId.value === '' ? 'Cannot create note.' : 'Cannot save note.';
    try {
      if (noteId.value === '') {
        // Create note
        await createNote(payload);
      } else {
        // Edit note
        await updateNote(noteId.value, payload);
      }
      showToast({ message: successMessage, variant: 'success' });

      bootstrap.Modal.getOrCreateInstance(noteModal).hide();
      await reloadNotes();
    } catch (err) {
      showToast({ message: errorMessage, variant: 'danger' });
      console.error(err);
    } finally {
      setButtonBusy(noteSubmitBtn, false);
    }
  });

  // Create or Edit note Title live char counter
  title.addEventListener('input', () => {
    titleCharCounter.textContent = title.value.length + " / 255";
  });

  // Create or Edit note Content live char counter
  content.addEventListener('input', () => {
    contentCharCounter.textContent = content.value.length + " / 5000";
  });

  // Delete a note
  notesGrid.addEventListener('click', async (event) => {
    const deleteBtn = event.target.closest('.js-delete-btn');
    if (!deleteBtn) return;

    if (!await confirmAction({
      title: 'Delete this note?',
      message: 'This cannot be undone.',
      confirmLabel: 'Delete',
      confirmIcon: 'bi-trash',
    })) return;

    setButtonBusy(deleteBtn, true);
    try {
      await deleteNote(deleteBtn.dataset.noteId);
      showToast({ message: 'Note deleted.', variant: 'success' });

      await reloadNotes();
    } catch (err) {
      showToast({ message: 'Cannot delete note.', variant: 'danger' });
      console.error(err);
    } finally {
      setButtonBusy(deleteBtn, false);
    }
  });

  // Pin/unpin a note
  notesGrid.addEventListener('click', async (event) => {
    const pinBtn = event.target.closest('.js-pin-btn');
    if (!pinBtn) return;

    const pinIcon = pinBtn.querySelector('.pin-default');
    const currentlyPinned = pinIcon.classList.contains('bi-pin-angle-fill');

    const pinHoverIcon = pinBtn.querySelector('.pin-hover');
    pinHoverIcon.classList.add('d-none');

    const payload = {
      is_pinned: !currentlyPinned,
    };

    const successMessage = currentlyPinned ? 'Note unpinned.' : 'Note pinned.';
    const errorMessage = currentlyPinned ? 'Cannot unpin note.' : 'Cannot pin note.';

    setButtonBusy(pinBtn, true);
    try {
      await setPinned(pinBtn.dataset.noteId, payload);
      showToast({ message: successMessage, variant: 'success' });

      await reloadNotes();
    } catch (err) {
      showToast({ message: errorMessage, variant: 'danger' });
      console.error(err);
    } finally {
      setButtonBusy(pinBtn, false);
      pinHoverIcon.classList.remove('d-none');
    }
  });

}

// Fetch all notes from the API
async function fetchNotes() {
  const response = await fetch(`${API_BASE}/notes/read.php`);

  if (!response.ok) throw new Error(`HTTP ${response.status}`);

  const data = await response.json();

  if (data.error) throw new Error(data.error);

  if (!Array.isArray(data.success)) {
    throw new Error('Unexpected API response shape: expected an array of notes.');
  }

  return data.success;
}

// Render an array of notes into the grid
function renderNotes(notes) {
  const notesGrid = document.getElementById('notesGrid');
  const notesEmptyState = document.getElementById('notesEmptyState');

  const hasNotes = notes.length !== 0;

  notesEmptyState.classList.toggle('d-none', hasNotes);

  const nodes = notes.map(createNoteCardElement);
  notesGrid.replaceChildren(...nodes);
}

function createNoteCardElement(note) {
  const pinned = Boolean(note.is_pinned);
  const createdAt = new Date(note.created_at.replace(' ', 'T'))
    .toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });

  const pinIcon = el('i', {
    classes: ['bi', pinned ? 'bi-pin-angle-fill' : 'bi-pin', 'pin-default']
  });
  const pinHoverIcon = el('i', { classes: ['bi', 'bi-pin-angle', 'pin-hover'] });

  const pinBtn = el('button', {
    classes: ['note-pin', 'border-0', 'p-0', 'bg-transparent', 'js-pin-btn'],
    title: pinned ? 'Unpin' : 'Pin',
    dataset: { noteId: note.id },
    children: [pinIcon, pinHoverIcon],
  });

  const editBtn = el('button', {
    classes: ['btn', 'btn-outline-tertiary'],
    title: 'Edit',
    dataset: {
      bsToggle: 'modal',
      bsTarget: '#noteModal',
      mode: 'edit',
      noteId: note.id,
      title: note.title,
      content: note.content ?? '',
      color: note.color,
      pinned: pinned ? 'true' : 'false',
    },
    children: [el('i', { classes: ['bi', 'bi-pencil'] })],
  });

  const deleteBtn = el('button', {
    classes: ['btn', 'btn-outline-tertiary', 'js-delete-btn'],
    title: 'Delete',
    dataset: { noteId: note.id },
    children: [el('i', { classes: ['bi', 'bi-trash'] })],
  });

  const footer = el('div', {
    classes: ['d-flex', 'justify-content-between', 'align-items-center', 'mt-2'],
    children: [
      el('small', { classes: ['text-body-secondary'], text: createdAt }),
      el('div', { classes: ['btn-group', 'btn-group-sm'], children: [editBtn, deleteBtn] }),
    ],
  });

  const body = el('div', {
    classes: ['card-body', 'd-flex', 'flex-column'],
    children: [
      el('h5', { classes: ['card-title', 'note-title-preview'], text: note.title, title: note.title }),
      el('p', { classes: ['card-text', 'note-preview', 'flex-grow-1'], text: note.content ?? '' }),
      footer,
    ],
  });

  const inner = el('div', {
    classes: ['card', 'note-card', 'h-100', 'position-relative'],
    children: [pinBtn, body],
  });
  inner.style.setProperty('--note-color', note.color);

  return el('div', {
    classes: ['col-12', 'col-sm-6', 'col-lg-4', 'mb-3'],
    children: [inner],
  });
}

// POST a new note to the API
async function createNote(payload) {
  const url = `${API_BASE}/notes/create.php`;
  const response = await fetch(url, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(payload),
  });
  if (!response.ok) throw new Error(`HTTP ${response.status}`);

  const data = await response.json();
  if (data.error) throw new Error(data.error);

  return data.success;
}

// PUT updated note to the API
async function updateNote(noteId, payload) {
  const url = `${API_BASE}/notes/update.php?id=${encodeURIComponent(noteId)}`;
  const response = await fetch(url, {
    method: 'PUT',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(payload),
  });
  if (!response.ok) throw new Error(`HTTP ${response.status}`);

  const data = await response.json();
  if (data.error) throw new Error(data.error);

  return data.success;
}

// DELETE a note via the API
async function deleteNote(id) {
  const url = `${API_BASE}/notes/delete.php?id=${encodeURIComponent(id)}`;
  const response = await fetch(url, { method: 'DELETE' });
  if (response.status === 204) return;

  if (!response.ok) {
    const data = await response.json();
    throw new Error(data.error ?? `HTTP ${response.status}`);
  }
}

// Set pinned/unpinned state for a note via the API
async function setPinned(id, payload) {
  const url = `${API_BASE}/notes/pin.php?id=${encodeURIComponent(id)}`;
  const response = await fetch(url, {
    method: 'PUT',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(payload),
  });
  if (!response.ok) throw new Error(`HTTP ${response.status}`);

  const data = await response.json();
  if (data.error) throw new Error(data.error);

  return data.success;
}

// Reload the note list from the server and re-render
async function reloadNotes() {
  setNotesLoading(true);
  try {
    const notes = await fetchNotes();
    renderNotes(notes);
  } catch (err) {
    showToast({ message: 'Cannot load notes.', variant: 'danger' });
    console.error(err);
  } finally {
    setNotesLoading(false);
  }
}
