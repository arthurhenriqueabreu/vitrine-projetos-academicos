'use strict';
document.querySelector('.menu-toggle')?.addEventListener('click', function () {
  const expanded = this.getAttribute('aria-expanded') === 'true';
  this.setAttribute('aria-expanded', String(!expanded));
  document.getElementById('main-nav').classList.toggle('open', !expanded);
});
document.addEventListener('keydown', event => {
  if (event.key === 'Escape') {
    document.querySelectorAll('details[open]').forEach(el => el.removeAttribute('open'));
    const menu = document.querySelector('.menu-toggle');
    if (menu?.getAttribute('aria-expanded') === 'true') { menu.click(); menu.focus(); }
  }
});
document.querySelectorAll('[data-confirm]').forEach(button => button.addEventListener('click', event => {
  if (!confirm(button.dataset.confirm)) event.preventDefault();
}));
document.querySelector('[data-share]')?.addEventListener('click', async function () {
  const status = document.getElementById('share-status');
  try {
    if (navigator.share) await navigator.share({ title: document.title, url: this.dataset.share });
    else { await navigator.clipboard.writeText(this.dataset.share); status.textContent = 'Link copiado!'; }
  } catch (error) { if (error.name !== 'AbortError') status.textContent = 'Copie o endereço desta página para compartilhar.'; }
});
document.querySelectorAll('[data-print]').forEach(el => el.addEventListener('click', () => window.print()));
document.querySelectorAll('[data-video]').forEach(button => button.addEventListener('click', function () {
  const iframe = document.createElement('iframe');
  iframe.src = this.dataset.video;
  iframe.title = this.dataset.title;
  iframe.className = 'video-frame';
  iframe.allow = 'fullscreen; picture-in-picture';
  iframe.referrerPolicy = 'strict-origin-when-cross-origin';
  iframe.setAttribute('allowfullscreen', '');
  this.replaceWith(iframe);
}));
function constrain(childId, parentId, property, required = false) {
  const child = document.getElementById(childId), parent = document.getElementById(parentId);
  if (!child || !parent) return;
  const update = () => {
    for (const option of child.options) {
      if (!option.value) continue;
      const hide = !!parent.value && option.dataset[property] !== parent.value;
      option.hidden = hide; option.disabled = hide;
      if (hide && option.selected) { option.selected = false; child.value = ''; }
    }
    child.dispatchEvent(new Event('change'));
  };
  parent.addEventListener('change', update); update();
}
constrain('subarea_id', 'area_id', 'area');
constrain('curso_id', 'instituicao_id', 'instituicao');
constrain('unidade_curricular_id', 'curso_id', 'curso');
constrain('filter-subarea', 'filter-area', 'area');
document.querySelector('.project-form')?.addEventListener('submit', event => {
  const authors = document.getElementById('autores').selectedOptions.length;
  const collaborators = document.getElementById('colaboradores').selectedOptions.length;
  const responsible = document.getElementById('responsavel_id').value;
  const duplicate = [...document.getElementById('colaboradores').selectedOptions].some(o => o.value === responsible);
  if (authors < 1 || authors > 5 || collaborators > 3 || duplicate) {
    event.preventDefault(); alert('Selecione entre 1 e 5 autores e até 3 colaboradores distintos do professor responsável.');
    document.getElementById(authors < 1 || authors > 5 ? 'autores' : 'colaboradores').focus();
  }
});
