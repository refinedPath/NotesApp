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

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', init);
} else {
  init();
}

async function init() {
  // Cache DOM properties
  const noteModal = document.getElementById('noteModal');
  const noteModalLabel = document.getElementById('noteModalLabel');
  const noteForm = document.getElementById('noteForm');
  const noteId = document.getElementById('noteId');
  const title = document.getElementById('title');
  const content = document.getElementById('content');
  const color = document.getElementById('color');
  const isPinned = document.getElementById('isPinned');
  const noteSubmitBtn = document.getElementById('noteSubmitBtn');

  // Wire the modal show.bs.modal listener
  noteModal.addEventListener('show.bs.modal', (event) => {
    const trigger = event.relatedTarget;
    const mode = trigger.dataset.mode;

    if (mode === "create") {
      noteModalLabel.textContent = 'New note';
      noteId.value = '';
      title.value = '';
      content.value = '';
      color.value = '#212529';
      isPinned.checked = false;
      noteSubmitBtn.textContent = 'Create';
    } else if (mode === "edit") {
      const dataset = trigger.dataset;

      noteModalLabel.textContent = 'Edit note';
      noteId.value = dataset.noteId;
      title.value = dataset.title;
      content.value = dataset.content;
      color.value = dataset.color;
      isPinned.checked = dataset.pinned === "true";
      noteSubmitBtn.textContent = 'Save';
    }
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
      is_pinned: isPinned.checked ? 1 : 0,
    };

    noteSubmitBtn.disabled = true;
    try {
      if (noteId.value === '') {
        // Create note
        await createNote(payload);
      } else {
        // Edit note
        await updateNote(noteId.value, payload);
      }

      bootstrap.Modal.getOrCreateInstance(noteModal).hide();
      await reloadNotes();
    } catch (err) {
      console.error(err);
    } finally {
      noteSubmitBtn.disabled = false;
    }
  });

  // Delete note
  const notesGrid = document.getElementById('notesGrid');
  notesGrid.addEventListener('click', async (event) => {
    const deleteBtn = event.target.closest('.js-delete-btn');
    if (!deleteBtn) return;

    if (!confirm('Delete this note? This cannot be undone.')) return;

    try {
      await deleteNote(deleteBtn.dataset.noteId);
      await reloadNotes();
    } catch (err) {
      console.error(err);
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

  // if (hasNotes) {
    const nodes = notes.map(createNoteCardElement);
    notesGrid.replaceChildren(...nodes);
  // }
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
    classes: ['note-pin', 'border-0', 'p-0', 'bg-transparent'],
    title: pinned ? 'Unpin' : 'Pin',
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
  if (!response.ok) throw new Error(`HTTP ${response.status}`);

  const data = await response.json();
  if (data.error) throw new Error(data.error);

  return data.success;
}

// Reload the note list from the server and re-render
async function reloadNotes() {
  try {
    const notes = await fetchNotes();
    renderNotes(notes);
  } catch (err) {
    console.error(err);
  }
}
