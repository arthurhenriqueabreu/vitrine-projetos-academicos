# Validação das 14 regras de negócio

As regras foram exercitadas em unitários, MySQL real, HTTP e navegador. A evidência abaixo especifica o alcance: um cenário não executado não é tratado como aprovado. Operações diretas por DBA podem violar regras garantidas apenas pelo serviço.

| Código | Regra | Implementação | Evidência / estado |
|---|---|---|---|
| RN01 | Curso e unidade curricular obrigatórios | ProjectService e FK composta UC/curso | Validado: curso/UC persistidos; FK incompatível recusada com SQLSTATE/código esperado |
| RN02 | Título, resumo e ao menos um autor | ProjectService::save/ids; transação | Unitários de título/autores + CA08 persistente; validação de resumo presente no serviço |
| RN03 | Área obrigatória | ProjectService e FK de área | Vínculo de área persistido e FK instalada; cenário negativo isolado de área ausente não executado |
| RN04 | Máximo de cinco autores | ids(1,5), posição 1–5 e chave única | Limites unitários e persistência real de cinco autores; teste direto do CHECK não isolado |
| RN05 | Autor em vários trabalhos | Tabela N:N trabalho_autores | Autores existentes reutilizados nos trabalhos criados na integração |
| RN06 | Orientação por um ou mais professores | Responsável e tabela trabalho_colaboradores | Responsável e colaboradores persistidos; detalhes exibidos em CA06 |
| RN07 | Somente publicados na vitrine | ProjectRepository::where/detail | Vitrine com dez publicados; rascunhos inacessíveis por ID e pesquisa |
| RN08 | Arquivados fora da listagem pública | Mesmo predicado publicado | Arquivado recusado por ID público e preservado no painel |
| RN09 | Exclusão autorizada | Auth + ProjectService::delete exige administrador | Editor recebe 403; administrador exclui e remove dependências |
| RN10 | Identificador único | Random 16 bytes em hex e UNIQUE | Dois identificadores persistidos distintos e formato verificado; input forjado ignorado |
| RN11 | Múltiplos materiais | Tabelas arquivos e links 1:N; upload e visualização | Imagem/PDF/link vinculados e servidos em HTTP; CRUD de link/vídeo executado |
| RN12 | Pesquisa apenas sobre públicos | ProjectRepository::where compartilhado | Busca de rascunho retorna zero; detalhe oculto retorna 404 |
| RN13 | Contadores por área e edição consistentes | counts usa mesmo where da listagem | Contagem com área/edição comparada à listagem no MySQL; filtros exercitados |
| RN14 | Um responsável e até três colaboradores | FK obrigatória, ids(0,3), responsável não duplicado | Unitários de duplicidade/limite e persistência de três colaboradores distintos |

Execução: https://github.com/arthurhenriqueabreu/vitrine-projetos-academicos/actions/runs/45888237933. JSONs em `evidencias/ci/` e testes de mutação em `evidencias/mutation.json`.

Execução: https://github.com/arthurhenriqueabreu/vitrine-projetos-academicos/actions/runs/45889043842. JSONs em `evidencias/ci/` e testes de mutação em `evidencias/mutation.json`.
