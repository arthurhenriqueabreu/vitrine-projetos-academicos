import { chromium } from 'playwright';
import { mkdir, writeFile } from 'node:fs/promises';
import assert from 'node:assert/strict';

const base = process.env.TEST_URL || 'http://127.0.0.1:8080';
if (!(process.env.DB_DATABASE || '').endsWith('_test')) throw new Error('Use somente banco descartável _test.');
const out = 'docs/evidencias/ci';
await mkdir(`${out}/screenshots`, { recursive: true });
const browser = await chromium.launch();
const context = await browser.newContext({ viewport: { width: 1440, height: 900 }, locale: 'pt-BR' });
const page = await context.newPage();
const results = [], errors = [], pages = [];
page.on('pageerror', e => errors.push(e.message));
page.on('dialog', dialog => dialog.accept());
let projectId = null;
const unique = `Navegador ${Date.now()}`;
async function test(code, name, fn) {
  const start = Date.now();
  try { await fn(); results.push({ codigo: code, teste: name, status: 'aprovado', ms: Date.now() - start }); }
  catch (e) { await shot(`falha-${code}`).catch(()=>{}); results.push({ codigo: code, teste: name, status: 'reprovado', erro: e.message }); }
}
async function open(path) {
  const response = await page.goto(`${base}/${path}`, { waitUntil: 'networkidle' });
  assert.equal(response.status(), 200, `HTTP ${response.status()} em ${path}`);
  assert.equal(new URL(page.url()).pathname, new URL(`${base}/${path}`).pathname, `Redirecionamento inesperado em ${path}`);
}
async function shot(name) {
  // A captura completa não dispara, sozinha, o carregamento de imagens lazy fora da viewport.
  await page.evaluate(async()=>{for(let y=0;y<document.documentElement.scrollHeight;y+=innerHeight){scrollTo({top:y,behavior:'instant'});await new Promise(resolve=>setTimeout(resolve,80));}scrollTo({top:0,behavior:'instant'});});
  try { await page.waitForFunction(()=>[...document.images].every(img=>img.complete && img.naturalWidth>0),null,{timeout:5000}); }
  catch { const missing=await page.locator('img').evaluateAll(images=>images.filter(img=>!img.complete || !img.naturalWidth).map(img=>img.src));throw new Error(`Imagens não carregadas em ${page.url()}: ${JSON.stringify(missing)}`); }
  await page.screenshot({ path: `${out}/screenshots/${name}.jpg`, type: 'jpeg', quality: 70, fullPage: true });
}
async function bodyIncludes(...texts) { const text = (await page.locator('body').innerText()).normalize('NFKC').toLocaleLowerCase('pt-BR'); for (const value of texts) assert.ok(text.includes(value.normalize('NFKC').toLocaleLowerCase('pt-BR')), `Texto ausente: ${value}`); }
async function login(role) {
  await open('login');
  await page.getByLabel('E-mail', { exact: true }).fill(`${role}@vitrine.example`);
  await page.getByLabel('Senha', { exact: true }).fill(process.env[role === 'admin' ? 'DEMO_ADMIN_PASSWORD' : 'DEMO_EDITOR_PASSWORD']);
  await page.getByRole('button', { name: 'Entrar no painel' }).click();
  await page.waitForURL(`${base}/admin`);
}
try {
  await test('CA01', 'Página institucional no navegador', async () => { await open(''); await bodyIncludes('Faculdade Senac Joinville', 'Grandes ideias.', '10'); assert.equal((await page.locator('.stat-grid strong').first().textContent()).trim(),'10'); assert.equal(await page.locator('.project-card').count(),3); await shot('01-inicio-desktop'); });
  await test('CA02', 'Vitrine lista publicados', async () => { await open(''); await page.locator('.hero-actions a.button').click(); await page.waitForURL(`${base}/vitrine`); assert.equal(await page.locator('.project-card').count(), 10); await bodyIncludes('Conecta Campus'); await shot('02-vitrine-desktop'); });
  await test('CA03', 'Filtro por área pela interface', async () => { await page.locator('#filter-area').selectOption('1'); await page.getByRole('button', { name: 'Aplicar filtros' }).click(); assert.ok((await page.locator('.project-card').count()) > 0); for (const text of await page.locator('.project-card .eyebrow').allTextContents()) assert.equal(text.trim(), 'Tecnologia e inovação'); });
  await test('CA04', 'Busca por título pela interface', async () => { await open('vitrine'); await page.locator('#q').fill('Conecta Campus'); await page.getByRole('button', { name: 'Pesquisar', exact: false }).click(); assert.equal(await page.locator('.project-card').count(), 1); await bodyIncludes('Conecta Campus'); });
  await test('CA05', 'Busca por autor pela interface', async () => { await page.locator('#q').fill('Clara Martins'); await page.getByRole('button', { name: 'Pesquisar', exact: false }).click(); await bodyIncludes('Conecta Campus'); assert.ok((await page.locator('.project-card').count()) > 0); });
  await test('CA06', 'Detalhes e materiais no navegador', async () => { await open('vitrine'); await page.locator('.project-card h3 a').filter({hasText:'Conecta Campus'}).click(); await page.waitForURL(`${base}/projeto/1`); assert.equal((await page.locator('h1').textContent()).trim(),'Conecta Campus: oportunidades que aproximam'); await bodyIncludes('Clara Martins', 'Lucas Ferreira', 'Helena Duarte', 'Resumo', 'Documentos do projeto', 'Unidade curricular', 'Desenvolvimento de software', 'Palavras que conectam', 'Joinville'); assert.equal(await page.getByText('Professor fictício. Orientação em inovação, sustentabilidade e gestão.',{exact:true}).count(),1); assert.ok((await page.locator('.gallery img').count()) > 0); assert.ok((await page.locator('.pdf-frame').count()) > 0); await shot('03-projeto-desktop'); });
  await test('CA07', 'Painel bloqueia visitante', async () => { await page.goto(`${base}/admin`); assert.equal(new URL(page.url()).pathname, '/login'); await shot('04-login-desktop'); });
  await test('LOGIN', 'Autenticação e dashboard reais', async () => { await login('admin'); await bodyIncludes('Visão geral', 'Cadastros', 'Sair'); assert.deepEqual((await page.locator('.dashboard-stats strong').allTextContents()).slice(0,4),['12','10','1','1']); await shot('05-dashboard-desktop'); });
  await test('CA11', 'Cadastro de edição e disponibilidade no projeto', async () => {
    await open('admin/cadastros/edicoes/novo'); await page.locator('#field-nome').fill(unique); await page.locator('#field-ano').fill('2027'); await page.locator('#field-descricao').fill('Edição de teste por navegador'); await page.getByRole('button', { name: 'Salvar registro' }).click(); await bodyIncludes('Cadastro atualizado'); await open('admin/projetos/novo'); assert.ok((await page.locator('#edicao_id').innerText()).includes(unique));
  });
  await test('CA08', 'Cadastro de trabalho pelo formulário', async () => {
    await open('admin/projetos/novo');
    await page.locator('#titulo').fill(unique); await page.locator('#resumo').fill('Resumo criado em teste real do navegador.'); await page.locator('#objetivos').fill('Validar fluxo completo com MySQL.');
    for (const [id, value] of Object.entries({ edicao_id:'2', instituicao_id:'1', curso_id:'1', unidade_curricular_id:'1', area_id:'1', subarea_id:'1', responsavel_id:'1' })) await page.locator(`#${id}`).selectOption(value);
    await page.locator('#autores').selectOption(['1','2']); await page.locator('#colaboradores').selectOption(['2']); await page.locator('#situacao').selectOption('publicado'); await page.getByRole('button', { name: 'Salvar projeto' }).click();
    await page.waitForURL(/\/admin\/projetos\/\d+$/); projectId=Number(page.url().split('/').pop()); await bodyIncludes('Projeto salvo com sucesso'); await shot('06-editor-projeto-desktop'); await open(`projeto/${projectId}`); await bodyIncludes(unique);
  });
  await test('CA09', 'Edição de trabalho pelo formulário', async () => {
    assert.ok(projectId); await open(`admin/projetos/${projectId}`); await page.locator('#titulo').fill(`${unique} editado`); await page.getByRole('button', { name: 'Salvar projeto' }).click(); await bodyIncludes('Projeto salvo com sucesso'); await open(`projeto/${projectId}`); await bodyIncludes(`${unique} editado`);
  });
  await test('UPLOAD', 'Upload e visualização de imagem pelo formulário', async () => {
    assert.ok(projectId); await open(`admin/projetos/${projectId}`);
    await page.locator('#arquivo').setInputFiles('public/assets/mark.svg'); await page.locator('#texto_alternativo').fill('SVG não permitido'); await page.getByRole('button', { name:'Enviar arquivo' }).click(); await bodyIncludes('conteúdo compatíveis');
    const imageBytes=await page.screenshot({type:'png'});
    await page.locator('#arquivo').setInputFiles({ name:'evidencia-teste.png', mimeType:'image/png', buffer:imageBytes }); await page.locator('#texto_alternativo').fill('Imagem de teste do próprio painel acadêmico'); await page.getByRole('button', { name:'Enviar arquivo' }).click(); await bodyIncludes('Materiais atualizados', 'image/jpeg');
    await open(`projeto/${projectId}`); const image=page.getByAltText('Imagem de teste do próprio painel acadêmico'); assert.ok(await image.isVisible()); assert.ok(await image.evaluate(img=>img.complete && img.naturalWidth>0));
  });
  await test('CA10', 'Rascunho removido da vitrine', async () => { assert.ok(projectId); await open(`admin/projetos/${projectId}`); await page.locator('#situacao').selectOption('rascunho'); await page.getByRole('button', { name:'Salvar projeto' }).click(); const response=await page.goto(`${base}/projeto/${projectId}`); assert.equal(response.status(),404); await open(`vitrine?q=${encodeURIComponent(unique)}`); assert.equal(await page.locator('.project-card').count(),0); });

  const publicPages=['','vitrine','edicoes','favoritos','projeto/1','privacidade','catalogo','login'];
  const adminPages=['admin','admin/projetos','admin/projetos/novo','admin/auditoria', ...['usuarios','areas','subareas','instituicoes','cursos','unidades_curriculares','autores','orientadores','edicoes','palavras_chave'].map(x=>`admin/cadastros/${x}`)];
  await test('DESKTOP', 'Todas as páginas públicas e administrativas em desktop', async () => {
    for (const path of [...publicPages,...adminPages]) { await open(path); assert.equal(await page.locator('h1').count(),1,`Título em ${path}`); const dimensions=await page.evaluate(()=>({scroll:document.documentElement.scrollWidth,width:innerWidth})); assert.ok(dimensions.scroll<=dimensions.width+1,`Overflow em ${path}: ${JSON.stringify(dimensions)}`); await shot(`desktop-${path.replaceAll('/','-')||'inicio'}`); pages.push({pagina:path||'/',viewport:'1440x900',status:'aprovado'}); }
    await open('edicoes'); await shot('07-edicoes-desktop'); await open('admin/cadastros/autores'); await shot('08-autores-desktop');
  });
  await test('CA12', 'Todas as páginas em viewport de smartphone e menu acessível', async () => {
    await page.setViewportSize({width:390,height:844});
    for (const path of [...publicPages,...adminPages]) { await open(path); const d=await page.evaluate(()=>({scroll:document.documentElement.scrollWidth,width:innerWidth})); assert.ok(d.scroll<=d.width+1,`Overflow mobile em ${path}: ${JSON.stringify(d)}`); await shot(`mobile-${path.replaceAll('/','-')||'inicio'}`); pages.push({pagina:path||'/',viewport:'390x844',status:'aprovado'}); }
    for (const [path,name] of [['','09-inicio-mobile'],['vitrine','10-vitrine-mobile'],['projeto/1','11-projeto-mobile'],['admin','12-dashboard-mobile'],['admin/projetos/novo','13-formulario-mobile']]) { await open(path); await shot(name); }
    await open('vitrine'); const menu=page.getByRole('button',{name:/Menu/}); assert.equal(await menu.getAttribute('aria-expanded'),'false'); await menu.click(); assert.equal(await menu.getAttribute('aria-expanded'),'true'); await page.keyboard.press('Escape'); assert.equal(await menu.getAttribute('aria-expanded'),'false');
  });
  await test('TABLET', 'Páginas principais em tablet', async () => { await page.setViewportSize({width:768,height:1024}); for(const path of ['','vitrine','projeto/1','admin','admin/projetos/novo']) {await open(path);assert.ok(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1));pages.push({pagina:path||'/',viewport:'768x1024',status:'aprovado'});} });
  await test('TECLADO', 'Link de pular conteúdo utilizável por teclado', async () => { await open('vitrine'); await page.keyboard.press('Tab'); assert.equal(await page.evaluate(()=>document.activeElement.getAttribute('href')),'#conteudo'); await page.keyboard.press('Enter'); assert.equal(await page.evaluate(()=>document.activeElement.id),'conteudo'); });
  await test('SEM_ERROS_JS','Nenhuma exceção JavaScript durante navegação',async()=>assert.deepEqual(errors,[]));
} finally {
  const failed=results.filter(r=>r.status==='reprovado').length;
  await writeFile(`${out}/browser.json`,JSON.stringify({suite:'browser',data:new Date().toISOString(),browser:'Chromium Playwright',source_sha:process.env.GITHUB_SHA,aprovados:results.length-failed,reprovados:failed,resultados:results,paginas:pages,limitacao:'Navegador real automatizado em CI; não é teste manual em aparelho físico nem auditoria WCAG.'},null,2));
  await browser.close();
  console.log(JSON.stringify({suite:'browser',aprovados:results.length-failed,reprovados:failed,resultados:results},null,2));
  process.exitCode=failed?1:0;
}
