(function () {
  'use strict';

  var allResources = [];
  var currentFilter = 'all';

  var resourceContainer = document.getElementById('resource-container');
  var filterResults = document.getElementById('filter-results');
  var tagsContainer = document.getElementById('tags-container');
  var btnAll = document.getElementById('filter-all');
  var btnStudy = document.getElementById('filter-study');

  function escapeHtml(text) {
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  function renderResource(resource) {
    var tagsHtml = resource.tags
      .map(function (tag) { return '<span class="tag">' + escapeHtml(tag) + '</span>'; })
      .join('');
    return (
      '<div class="resource-card">' +
        '<h3>' + escapeHtml(resource.name) + '</h3>' +
        '<p><strong>Tip:</strong> ' + escapeHtml(resource.type) + ' · ' +
        '<strong>Locație:</strong> ' + escapeHtml(resource.location) + ' · ' +
        '<strong>Program:</strong> ' + escapeHtml(resource.program) + '</p>' +
        '<div class="tags">' + tagsHtml + '</div>' +
      '</div>'
    );
  }

  function getFilteredResources() {
    if (currentFilter === 'study') {
      return allResources.filter(function (r) { return r.type === 'study'; });
    }
    return allResources;
  }

  function renderList() {
    var filtered = getFilteredResources();
    if (!resourceContainer) return;
    if (filtered.length === 0) {
      resourceContainer.innerHTML = '<p class="loading">Nu există resurse care să corespundă filtrului.</p>';
      resourceContainer.classList.add('loading');
      return;
    }
    resourceContainer.classList.remove('loading');
    resourceContainer.innerHTML = filtered.map(renderResource).join('');

    if (filterResults) {
      var msg = currentFilter === 'study'
        ? 'Se afișează ' + filtered.length + ' spațiu/spații de studiu.'
        : 'Se afișează toate cele ' + filtered.length + ' resurse.';
      filterResults.innerHTML = '<p class="text-muted">' + msg + '</p>';
    }
  }

  function getAllTags() {
    var tagsSet = {};
    allResources.forEach(function (r) {
      r.tags.forEach(function (tag) {
        tagsSet[tag] = true;
      });
    });
    return Object.keys(tagsSet).sort();
  }

  function renderTags() {
    if (!tagsContainer) return;
    var tags = getAllTags();
    if (tags.length === 0) {
      tagsContainer.innerHTML = '<p class="loading">Nu există tags.</p>';
      return;
    }
    tagsContainer.innerHTML = tags
      .map(function (tag) { return '<span class="tag">' + escapeHtml(tag) + '</span>'; })
      .join('');
  }

  function setFilter(filter) {
    currentFilter = filter;
    if (btnAll) btnAll.classList.toggle('active', filter === 'all');
    if (btnStudy) btnStudy.classList.toggle('active', filter === 'study');
    renderList();
  }

  var FALLBACK_DATA = {
    resources: [
      { name: "Biblioteca Centrală", type: "study", location: "Clădirea A, Etaj 1", program: "L-V: 8:00-22:00", tags: ["studiu", "citit", "tăcere"] },
      { name: "Cantina Studențească", type: "dining", location: "Clădirea B", program: "L-V: 11:00-15:00", tags: ["mâncare", "prânz"] },
      { name: "Sala de Evenimente", type: "events", location: "Clădirea C", program: "Program variabil", tags: ["conferințe", "workshop-uri"] },
      { name: "Sala de Lectură", type: "study", location: "Clădirea A, Etaj 2", program: "L-V: 9:00-20:00, S: 10:00-14:00", tags: ["studiu", "tăcere", "grupuri mici"] },
      { name: "Cafeneaua Campus", type: "dining", location: "Lângă Biblioteca", program: "L-V: 7:30-18:00", tags: ["cafea", "gustări", "socializare"] },
      { name: "Laborator Informatică", type: "study", location: "Clădirea D, Etaj 1", program: "L-V: 8:00-20:00", tags: ["studiu", "calculatoare", "proiecte"] },
      { name: "Sala de Conferințe", type: "events", location: "Clădirea C", program: "La programare", tags: ["conferințe", "prezentări"] }
    ]
  };

  function loadResources() {
    fetch('data/resources.json')
      .then(function (response) {
        if (!response.ok) throw new Error('Eroare: ' + response.status);
        return response.json();
      })
      .then(function (data) {
        allResources = (data && data.resources) ? data.resources : FALLBACK_DATA.resources;
        renderList();
        renderTags();
      })
      .catch(function () {
        allResources = FALLBACK_DATA.resources;
        renderList();
        renderTags();
      });
  }

  if (btnAll) btnAll.addEventListener('click', function () { setFilter('all'); });
  if (btnStudy) btnStudy.addEventListener('click', function () { setFilter('study'); });

  loadResources();
})();
