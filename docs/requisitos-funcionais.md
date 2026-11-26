# Matriz dos 47 requisitos funcionais

Fonte: Recuperação(1).pdf, 14 páginas; comparação textual conferida com a versão (2).

**Legenda:** V = fluxo básico exercitado em MySQL/HTTP/Chromium no CI indicado abaixo. Não significa cobertura exaustiva de entradas, navegadores ou produção. A coluna aponta a evidência específica.

Execução: https://github.com/arthurhenriqueabreu/vitrine-projetos-academicos/actions/runs/45889043842. Código: `4b4a1e7d58c84407b1390f49453c32bfef3c2f18`.

| Código | Requisito | Implementação | Estado | Evidência |
|---|---|---|---|---|
| RF01 | Identificação institucional na página inicial | `views/home.php; views/layout.php` | V | browser CA01–CA02; screenshots início; integration destaques |
| RF02 | Descrição e objetivos da mostra | `views/home.php` | V | browser CA01–CA02; screenshots início; integration destaques |
| RF03 | Total de trabalhos publicados | `ProjectRepository::listing; views/home.php` | V | browser CA01–CA02; screenshots início; integration destaques |
| RF04 | Áreas disponíveis | `ProjectRepository::counts; views/home.php` | V | browser CA01–CA02; screenshots início; integration destaques |
| RF05 | Acesso à vitrine | `views/home.php; views/layout.php` | V | browser CA01–CA02; screenshots início; integration destaques |
| RF06 | Projetos em destaque | `ProjectRepository::listing(featured); views/home.php` | V | browser CA01–CA02; screenshots início; integration destaques |
| RF07 | Menu de navegação | `views/layout.php; public/assets/app.js` | V | browser CA01–CA02; screenshots início; integration destaques |
| RF08 | Listagem dos trabalhos publicados | `ProjectRepository::listing; views/catalog.php` | V | http pesquisa/filtros/histórico; integration contadores/palavra-chave; browser CA03–CA05 |
| RF09 | Organização por área | `ProjectRepository::where; views/catalog.php` | V | http pesquisa/filtros/histórico; integration contadores/palavra-chave; browser CA03–CA05 |
| RF10 | Contagem por área | `ProjectRepository::counts` | V | http pesquisa/filtros/histórico; integration contadores/palavra-chave; browser CA03–CA05 |
| RF11 | Filtro por área | `ProjectRepository::where; views/catalog.php` | V | http pesquisa/filtros/histórico; integration contadores/palavra-chave; browser CA03–CA05 |
| RF12 | Pesquisa por título | `ProjectRepository::where` | V | http pesquisa/filtros/histórico; integration contadores/palavra-chave; browser CA03–CA05 |
| RF13 | Pesquisa por um ou mais autores | `ProjectRepository::where; testes de termos múltiplos` | V | http pesquisa/filtros/histórico; integration contadores/palavra-chave; browser CA03–CA05 |
| RF14 | Pesquisa por instituição | `ProjectRepository::where` | V | http pesquisa/filtros/histórico; integration contadores/palavra-chave; browser CA03–CA05 |
| RF15 | Pesquisa por palavras-chave | `ProjectRepository::where` | V | http pesquisa/filtros/histórico; integration contadores/palavra-chave; browser CA03–CA05 |
| RF16 | Atualização conforme filtros | `public/index.php; views/catalog.php` | V | http pesquisa/filtros/histórico; integration contadores/palavra-chave; browser CA03–CA05 |
| RF17 | Mensagem sem resultados | `views/catalog.php` | V | http pesquisa/filtros/histórico; integration contadores/palavra-chave; browser CA03–CA05 |
| RF18 | Remover filtros | `views/catalog.php` | V | http pesquisa/filtros/histórico; integration contadores/palavra-chave; browser CA03–CA05 |
| RF19 | Selecionar projeto e ver detalhes | `views/partials/cards.php; rota projeto/{id}` | V | browser CA06; http detalhes, PDF e materiais; screenshots projeto |
| RF20 | Título completo | `views/detail.php` | V | browser CA06; http detalhes, PDF e materiais; screenshots projeto |
| RF21 | Nomes dos autores | `ProjectRepository::detail; views/detail.php` | V | browser CA06; http detalhes, PDF e materiais; screenshots projeto |
| RF22 | Nome do orientador | `ProjectRepository::detail; views/detail.php` | V | browser CA06; http detalhes, PDF e materiais; screenshots projeto |
| RF23 | Instituição e localização | `ProjectRepository::detail; views/detail.php` | V | browser CA06; http detalhes, PDF e materiais; screenshots projeto |
| RF24 | Área e subárea | `views/detail.php` | V | browser CA06; http detalhes, PDF e materiais; screenshots projeto |
| RF25 | Resumo | `views/detail.php` | V | browser CA06; http detalhes, PDF e materiais; screenshots projeto |
| RF26 | Palavras-chave | `views/detail.php` | V | browser CA06; http detalhes, PDF e materiais; screenshots projeto |
| RF27 | Galeria de imagens | `views/detail.php; rota arquivo/{id}` | V | browser CA06; http detalhes, PDF e materiais; screenshots projeto |
| RF28 | Links para vídeos | `UploadService::link; views/detail.php` | V | browser CA06; http detalhes, PDF e materiais; screenshots projeto |
| RF29 | Visualização/download de PDF | `views/detail.php; rota arquivo/{id}` | V | browser CA06; http detalhes, PDF e materiais; screenshots projeto |
| RF30 | Links externos | `UploadService::link; views/detail.php` | V | browser CA06; http detalhes, PDF e materiais; screenshots projeto |
| RF31 | Administração autenticada | `Auth::requireUser; rotas admin` | V | http login/CSRF/perfis; browser CA07/LOGIN |
| RF32 | Cadastrar trabalhos | `ProjectService::save; views/admin/project-form.php` | V | integration CA08–CA10/cascata/arquivado; http CRUD; browser CA08–CA10 |
| RF33 | Editar trabalhos | `ProjectService::save; versão otimista` | V | integration CA08–CA10/cascata/arquivado; http CRUD; browser CA08–CA10 |
| RF34 | Excluir trabalhos | `ProjectService::delete; administrador` | V | integration CA08–CA10/cascata/arquivado; http CRUD; browser CA08–CA10 |
| RF35 | Rascunho, publicado, arquivado | `ProjectService::save; schema.sql` | V | integration CA08–CA10/cascata/arquivado; http CRUD; browser CA08–CA10 |
| RF36 | CRUD de áreas | `CatalogService; views/admin/catalog.php` | V | integration CRUD da entidade; screenshots cadastros |
| RF37 | CRUD de instituições | `CatalogService; views/admin/catalog.php` | V | integration CRUD da entidade; screenshots cadastros |
| RF38 | Gerenciar autores e orientadores | `CatalogService; views/admin/catalog.php` | V | integration CRUD da entidade; screenshots cadastros |
| RF39 | Upload de imagens e documentos | `UploadService::store; views/admin/project-form.php` | V | http uploads e CRUD de link/vídeo; browser UPLOAD |
| RF40 | Cadastro de vídeos e links | `UploadService::link; views/admin/project-form.php` | V | http uploads e CRUD de link/vídeo; browser UPLOAD |
| RF41 | Indicadores administrativos | `rota admin; views/admin/dashboard.php` | V | browser LOGIN compara indicadores 12/10/1/1; screenshot dashboard |
| RF42 | Gerenciar edições | `CatalogService; views/admin/catalog.php` | V | integration CRUD edicoes; browser CA11; http seleção e histórico de edições |
| RF43 | Cadastrar diferentes edições | `CatalogService; database/schema.sql` | V | integration CRUD edicoes; browser CA11; http seleção e histórico de edições |
| RF44 | Associar projeto à edição | `ProjectService::save; FK trabalhos.edicao_id` | V | integration CRUD edicoes; browser CA11; http seleção e histórico de edições |
| RF45 | Selecionar edição pública | `views/editions.php; views/catalog.php` | V | integration CRUD edicoes; browser CA11; http seleção e histórico de edições |
| RF46 | Histórico de edições | `rota edicoes; views/editions.php` | V | integration CRUD edicoes; browser CA11; http seleção e histórico de edições |
| RF47 | Identificar visualmente edição selecionada | `views/catalog.php: edition-selected` | V | integration CRUD edicoes; browser CA11; http seleção e histórico de edições |

Limites: RF28 valida cadastro/link e geração de incorporação, não reprodução audiovisual remota; RF29 valida bytes e cabeçalhos do download e existência do visualizador, não renderização nativa de PDF em todos os navegadores. Os testes completos e screenshots estão em `evidencias/ci/`.
