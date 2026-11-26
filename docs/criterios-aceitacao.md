# Relatório dos 12 critérios de aceitação

Data: 26/11/2026. **Nenhum CA foi declarado aprovado sem execução ponta a ponta.** CA01–CA11: não executados por bloqueio de sockets/ausência de banco provisionado. CA12: não executado em navegador/dispositivo; CSS responsivo existe, mas não substitui evidência visual. Os 34 unitários são relatados separadamente.

| Código | Cenário | Procedimento | Resultado esperado | Automação/roteiro | Resultado real |
|---|---|---|---|---|---|
| CA01 | Página inicial | Acessar / | Identificação, descrição e objetivos da mostra | `tests/http_test.py` | Não executado — ambiente bloqueado |
| CA02 | Vitrine | Acessar /vitrine | Somente trabalhos publicados e paginação | `tests/http_test.py` | Não executado — ambiente bloqueado |
| CA03 | Filtro por área | Selecionar Tecnologia e inovações | Somente trabalhos da área, contadores consistentes | `tests/http_test.py; tests/integration.php` | Não executado — ambiente bloqueado |
| CA04 | Pesquisa por título | Pesquisar Conecta Campus | Projeto correspondente localizado | `tests/http_test.py` | Não executado — ambiente bloqueado |
| CA05 | Pesquisa por autor | Pesquisar Clara Martins | Trabalhos com essa autora | `tests/http_test.py` | Não executado — ambiente bloqueado |
| CA06 | Detalhes de trabalho | Abrir /projeto/1 | Autores, orientadores, resumo, instituição, materiais | `tests/http_test.py` | Não executado — ambiente bloqueado |
| CA07 | Acesso sem login | Abrir /admin em sessão anônima | Redirecionamento para /login | `tests/http_test.py` | Não executado — ambiente bloqueado |
| CA08 | Cadastrar trabalho | Preencher formulário válido com autor e associações | Dados e relações persistidos no MySQL | `tests/http_test.py; tests/integration.php` | Não executado — ambiente bloqueado |
| CA09 | Editar trabalho | Alterar título e salvar com versão atual | Título atualizado no MySQL e na tela | `tests/http_test.py; tests/integration.php` | Não executado — ambiente bloqueado |
| CA10 | Rascunho | Alterar publicado para rascunho | Sai da pesquisa/listagem e ID público retorna 404 | `tests/http_test.py; tests/integration.php` | Não executado — ambiente bloqueado |
| CA11 | Nova edição | Cadastrar edição no painel | Cadastro persiste e fica disponível na associação | `tests/http_test.py; CRUD edicoes em tests/integration.php` | Não executado — ambiente bloqueado |
| CA12 | Smartphone | Abrir início/vitrine/detalhes/painel a 390×844; testar 768×1024 e 1440×900 | Sem corte de campos; menu operável; scroll horizontal só em tabelas | `Roteiro manual; sem execução visual` | Não executado — requer navegador/dispositivo |

## Complementos manuais

- Navegar do cabeçalho ao rodapé com Tab/Shift+Tab, ativar botões com Enter/Espaço e fechar menu/dropdown com Escape. Confirmar foco visível e ausência de armadilhas.
- Testar labels e erros com leitor de tela; alt de imagens, títulos de iframes e material PDF acessível.
- Testar zoom 200%, preferência por movimento reduzido, textos longos e contraste com ferramenta própria.
- Em Chrome/Firefox/Edge/Safari, testar download/visualização de PDF, upload e compartilhamento com fallback.
- Restaurar backup em banco vazio separado e comparar contagens, chaves e arquivos. Registrar data, versão, passos e resultado real ao executar.
