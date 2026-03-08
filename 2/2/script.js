// Încarcă datele din data/resources.json (URL relativ)
const RESOURCES_URL = 'data/resources.json';

let allResources = [];

async function loadResources() {
  try {
    const response = await fetch(RESOURCES_URL);
    const data = await response.json();
    allResources = data.resources;
    renderResourceList(allResources);
    renderFilterResults(allResources);
    renderTags(allResources);
  } catch (err) {
    document.getElementById('resource-container').innerHTML =
      '<p style="color: red;">Eroare la încărcarea resurselor: ' + err.message + '</p>';
  }
}

function renderResourceList(resources) {
  const container = document.getElementById('resource-container');
  container.innerHTML = resources
    .map(
      (r) => `
    <div class="resource-card">
      <h3>${r.name}</h3>
      <p><strong>Tip:</strong> ${r.type}</p>
      <p><strong>Locație:</strong> ${r.location}</p>
      <p><strong>Program:</strong> ${r.program}</p>
      <p><strong>Tags:</strong> ${r.tags.join(', ')}</p>
    </div>
  `
    )
    .join('');
}

function renderFilterResults(resources) {
  const container = document.getElementById('filter-results');
  if (resources.length === 0) {
    container.innerHTML = '<p>Nicio resursă găsită.</p>';
    return;
  }
  container.innerHTML = resources
    .map(
      (r) => `
    <div class="resource-card">
      <h3>${r.name}</h3>
      <p><strong>Locație:</strong> ${r.location}</p>
    </div>
  `
    )
    .join('');
}

function renderTags(resources) {
  const tagsSet = new Set();
  resources.forEach((r) => r.tags.forEach((t) => tagsSet.add(t)));
  const container = document.getElementById('tags-container');
  container.innerHTML = [...tagsSet]
    .sort()
    .map((t) => `<span class="tag">${t}</span>`)
    .join('');
}

document.addEventListener('DOMContentLoaded', () => {
  loadResources();

  document.getElementById('filter-study').addEventListener('click', () => {
    const studyResources = allResources.filter(
      (r) =>
        r.tags.includes('studiere') ||
        r.type === 'biblioteca' ||
        r.type === 'spațiu-studiere'
    );
    renderFilterResults(studyResources);
  });

  document.getElementById('filter-all').addEventListener('click', () => {
    renderFilterResults(allResources);
  });
});
