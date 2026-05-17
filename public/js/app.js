const noteModal = document.getElementById('noteModal');
const noteModalLabel = document.getElementById('noteModalLabel');
const noteId = document.getElementById('noteId');
const title = document.getElementById('title');
const content = document.getElementById('content');
const color = document.getElementById('color');
const isPinned = document.getElementById('isPinned');
const noteSubmitBtn = document.getElementById('noteSubmitBtn');

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