# Relatório dos 12 critérios de aceitação

Validação real individual em Chromium + PHP + MySQL 8.4.13. Os 12 códigos têm cenário aprovado em `evidencias/ci/browser.json`. CA12 usa viewport de smartphone, não aparelho físico. Não houve sessão manual no navegador do usuário.

Execução: https://github.com/arthurhenriqueabreu/vitrine-projetos-academicos/actions/runs/45889043842. Código testado: `4b4a1e7d58c84407b1390f49453c32bfef3c2f18`.

| Código | Cenário | Procedimento | Resultado esperado | Automação/roteiro | Resultado real |
|---|---|---|---|---|---|
| CA01 | Página inicial | Acessar / | Identificação, descrição e objetivos da mostra | `tests/browser_test.mjs`; `tests/http_test.py` ; MySQL real | Aprovado em CI |
| CA02 | Vitrine | Acessar /vitrine | Somente trabalhos publicados e paginação | `tests/browser_test.mjs`; `tests/http_test.py` ; MySQL real | Aprovado em CI |
| CA03 | Filtro por área | Selecionar Tecnologia e inovações | Somente trabalhos da área, contadores consistentes | `tests/browser_test.mjs`; `tests/http_test.py` ; MySQL real | Aprovado em CI |
| CA04 | Pesquisa por título | Pesquisar Conecta Campus | Projeto correspondente localizado | `tests/browser_test.mjs`; `tests/http_test.py` ; MySQL real | Aprovado em CI |
| CA05 | Pesquisa por autor | Pesquisar Clara Martins | Trabalhos com essa autora | `tests/browser_test.mjs`; `tests/http_test.py` ; MySQL real | Aprovado em CI |
| CA06 | Detalhes de trabalho | Abrir /projeto/1 | Autores, orientadores, resumo, instituição, materiais | `tests/browser_test.mjs`; `tests/http_test.py` ; MySQL real | Aprovado em CI |
| CA07 | Acesso sem login | Abrir /admin em sessão anônima | Redirecionamento para /login | `tests/browser_test.mjs`; `tests/http_test.py` ; MySQL real | Aprovado em CI |
| CA08 | Cadastrar trabalho | Preencher formulário válido com autor e associações | Dados e relações persistidos no MySQL | `tests/browser_test.mjs`; `tests/http_test.py` ; MySQL real | Aprovado em CI |
| CA09 | Editar trabalho | Alterar título e salvar com versão atual | Título atualizado no MySQL e na tela | `tests/browser_test.mjs`; `tests/http_test.py` ; MySQL real | Aprovado em CI |
| CA10 | Rascunho | Alterar publicado para rascunho | Sai da pesquisa/listagem e ID público retorna 404 | `tests/browser_test.mjs`; `tests/http_test.py` ; MySQL real | Aprovado em CI |
| CA11 | Nova edição | Cadastrar edição no painel | Cadastro persiste e fica disponível na associação | `tests/browser_test.mjs`; `tests/http_test.py` ; MySQL real | Aprovado em CI |
| CA12 | Smartphone | Abrir início/vitrine/detalhes/painel a 390×844; testar 768×1024 e 1440×900 | Sem corte de campos; menu operável; scroll horizontal só em tabelas | `tests/browser_test.mjs`; `tests/http_test.py` | Aprovado em CI — viewport 390×844, tablet e desktop; físico não executado |

## Ainda não executado

Testes manuais com smartphone físico; leitor de tela; matriz multi navegador; restauração real e implantação Senac. As capturas reais complementam as asserções de overflow e navegação, sem substituir essas validações pendentes.
