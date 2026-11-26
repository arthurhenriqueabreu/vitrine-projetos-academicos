# Matriz dos 47 requisitos funcionais

Fonte: enunciado de 14 páginas, RF01–RF47. Os textos abaixo sintetizam cada item sem mudar seu objetivo.

**Legenda:** IC = implementado no código, integração ainda não validada. Todos os 47 estão em IC; isso não significa 47 critérios aprovados. Testes unitários aprovam partes de RF13/RF31 e proteções, mas não comprovam o fluxo completo. A coluna de arquivo permite revisão objetiva.

| Código | Requisito | Implementação | Estado | Validação |
|---|---|---|---|---|
| RF01 | Identificação institucional na página inicial | `views/home.php; views/layout.php` | IC | Revisão de código; execução E2E bloqueada |
| RF02 | Descrição e objetivos da mostra | `views/home.php` | IC | Revisão de código; execução E2E bloqueada |
| RF03 | Total de trabalhos publicados | `ProjectRepository::listing; views/home.php` | IC | Revisão de código; execução E2E bloqueada |
| RF04 | Áreas disponíveis | `ProjectRepository::counts; views/home.php` | IC | Revisão de código; execução E2E bloqueada |
| RF05 | Acesso à vitrine | `views/home.php; views/layout.php` | IC | Revisão de código; execução E2E bloqueada |
| RF06 | Projetos em destaque | `ProjectRepository::listing(featured); views/home.php` | IC | Revisão de código; execução E2E bloqueada |
| RF07 | Menu de navegação | `views/layout.php; public/assets/app.js` | IC | Revisão de código; execução E2E bloqueada |
| RF08 | Listagem dos trabalhos publicados | `ProjectRepository::listing; views/catalog.php` | IC | Revisão de código; execução E2E bloqueada |
| RF09 | Organização por área | `ProjectRepository::where; views/catalog.php` | IC | Revisão de código; execução E2E bloqueada |
| RF10 | Contagem por área | `ProjectRepository::counts` | IC | Revisão de código; execução E2E bloqueada |
| RF11 | Filtro por área | `ProjectRepository::where; views/catalog.php` | IC | Revisão de código; execução E2E bloqueada |
| RF12 | Pesquisa por título | `ProjectRepository::where` | IC | Revisão de código; execução E2E bloqueada |
| RF13 | Pesquisa por um ou mais autores | `ProjectRepository::where; testes de termos múltiplos` | IC | Revisão de código; execução E2E bloqueada |
| RF14 | Pesquisa por instituição | `ProjectRepository::where` | IC | Revisão de código; execução E2E bloqueada |
| RF15 | Pesquisa por palavras-chave | `ProjectRepository::where` | IC | Revisão de código; execução E2E bloqueada |
| RF16 | Atualização conforme filtros | `public/index.php; views/catalog.php` | IC | Revisão de código; execução E2E bloqueada |
| RF17 | Mensagem sem resultados | `views/catalog.php` | IC | Revisão de código; execução E2E bloqueada |
| RF18 | Remover filtros | `views/catalog.php` | IC | Revisão de código; execução E2E bloqueada |
| RF19 | Selecionar projeto e ver detalhes | `views/partials/cards.php; rota projeto/{id}` | IC | Revisão de código; execução E2E bloqueada |
| RF20 | Título completo | `views/detail.php` | IC | Revisão de código; execução E2E bloqueada |
| RF21 | Nomes dos autores | `ProjectRepository::detail; views/detail.php` | IC | Revisão de código; execução E2E bloqueada |
| RF22 | Nome do orientador | `ProjectRepository::detail; views/detail.php` | IC | Revisão de código; execução E2E bloqueada |
| RF23 | Instituição e localização | `ProjectRepository::detail; views/detail.php` | IC | Revisão de código; execução E2E bloqueada |
| RF24 | Área e subárea | `views/detail.php` | IC | Revisão de código; execução E2E bloqueada |
| RF25 | Resumo | `views/detail.php` | IC | Revisão de código; execução E2E bloqueada |
| RF26 | Palavras-chave | `views/detail.php` | IC | Revisão de código; execução E2E bloqueada |
| RF27 | Galeria de imagens | `views/detail.php; rota arquivo/{id}` | IC | Revisão de código; execução E2E bloqueada |
| RF28 | Links para vídeos | `UploadService::link; views/detail.php` | IC | Revisão de código; execução E2E bloqueada |
| RF29 | Visualização/download de PDF | `views/detail.php; rota arquivo/{id}` | IC | Revisão de código; execução E2E bloqueada |
| RF30 | Links externos | `UploadService::link; views/detail.php` | IC | Revisão de código; execução E2E bloqueada |
| RF31 | Administração autenticada | `Auth::requireUser; rotas admin` | IC | Revisão de código; execução E2E bloqueada |
| RF32 | Cadastrar trabalhos | `ProjectService::save; views/admin/project-form.php` | IC | Revisão de código; execução E2E bloqueada |
| RF33 | Editar trabalhos | `ProjectService::save; versão otimista` | IC | Revisão de código; execução E2E bloqueada |
| RF34 | Excluir trabalhos | `ProjectService::delete; administrador` | IC | Revisão de código; execução E2E bloqueada |
| RF35 | Rascunho, publicado, arquivado | `ProjectService::save; schema.sql` | IC | Revisão de código; execução E2E bloqueada |
| RF36 | CRUD de áreas | `CatalogService; views/admin/catalog.php` | IC | Revisão de código; execução E2E bloqueada |
| RF37 | CRUD de instituições | `CatalogService; views/admin/catalog.php` | IC | Revisão de código; execução E2E bloqueada |
| RF38 | Gerenciar autores e orientadores | `CatalogService; views/admin/catalog.php` | IC | Revisão de código; execução E2E bloqueada |
| RF39 | Upload de imagens e documentos | `UploadService::store; views/admin/project-form.php` | IC | Revisão de código; execução E2E bloqueada |
| RF40 | Cadastro de vídeos e links | `UploadService::link; views/admin/project-form.php` | IC | Revisão de código; execução E2E bloqueada |
| RF41 | Indicadores administrativos | `rota admin; views/admin/dashboard.php` | IC | Revisão de código; execução E2E bloqueada |
| RF42 | Gerenciar edições | `CatalogService; views/admin/catalog.php` | IC | Revisão de código; execução E2E bloqueada |
| RF43 | Cadastrar diferentes edições | `CatalogService; database/schema.sql` | IC | Revisão de código; execução E2E bloqueada |
| RF44 | Associar projeto à edição | `ProjectService::save; FK trabalhos.edicao_id` | IC | Revisão de código; execução E2E bloqueada |
| RF45 | Selecionar edição pública | `views/editions.php; views/catalog.php` | IC | Revisão de código; execução E2E bloqueada |
| RF46 | Histórico de edições | `rota edicoes; views/editions.php` | IC | Revisão de código; execução E2E bloqueada |
| RF47 | Identificar visualmente edição selecionada | `views/catalog.php: edition-selected` | IC | Revisão de código; execução E2E bloqueada |
