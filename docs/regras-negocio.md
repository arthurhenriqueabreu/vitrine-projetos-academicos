# Validação das 14 regras de negócio

As validações unitárias abaixo foram realmente executadas. As restrições relacionais e transações ainda precisam da suíte MySQL. Alterações diretas pelo DBA podem violar regras garantidas apenas pelo serviço (por exemplo, o mínimo de um autor).

| Código | Regra | Implementação | Evidência / estado |
|---|---|---|---|
| RN01 | Curso e unidade curricular obrigatórios | ProjectService e FK composta UC/curso | Validação unitária de curso aprovada; FK não executada |
| RN02 | Título, resumo e ao menos um autor | ProjectService::save/ids; transação | Mínimo de autores/título aprovados; persistência bloqueada |
| RN03 | Área obrigatória | ProjectService e FK de área | Revisão de código; banco bloqueado |
| RN04 | Máximo de cinco autores | ids(1,5), posição 1–5 e chave única | Limite de cinco aprovado em unitários; CHECK pendente |
| RN05 | Autor em vários trabalhos | Tabela N:N trabalho_autores | Modelado; integração bloqueada |
| RN06 | Orientação por um ou mais professores | Responsável e tabela trabalho_colaboradores | Implementado; fluxo persistente pendente |
| RN07 | Somente publicados na vitrine | ProjectRepository::where/detail | Predicado validado em unitário; consulta real pendente |
| RN08 | Arquivados fora da listagem pública | Mesmo predicado publicado | Implementado; execução bloqueada |
| RN09 | Exclusão autorizada | Auth + ProjectService::delete exige administrador | Negativa para editor aprovada em unitário |
| RN10 | Identificador único | Random 16 bytes em hex e UNIQUE | Implementado; teste de persistência/unique pendente |
| RN11 | Múltiplos materiais | Tabelas arquivos e links 1:N; upload e visualização | Implementado; HTTP/arquivos pendentes |
| RN12 | Pesquisa apenas sobre públicos | ProjectRepository::where compartilhado | Predicado presente em unitário; banco bloqueado |
| RN13 | Contadores por área e edição consistentes | counts usa mesmo where da listagem | Construção de filtros validada; contagem MySQL pendente |
| RN14 | Um responsável e até três colaboradores | FK obrigatória, ids(0,3), responsável não duplicado | Limite e duplicidade aprovados em unitários; integração pendente |
