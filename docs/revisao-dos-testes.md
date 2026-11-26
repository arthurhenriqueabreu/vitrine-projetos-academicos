# Revisão da especificidade dos testes

Autoria do projeto preservada: Arthur Henrique Abreu e Matheus Fonte.

## Unitários: 34 cenários mantidos

A revisão não aumentou artificialmente a quantidade de cenários. O fixture base agora usa colaborador 2 e responsável 1, uma combinação válida. Somente o teste de duplicidade troca o colaborador para 1. Assim, se a guarda de situação, título ou curso for removida, outra regra inválida não mascara o defeito.

O executor exige retorno booleano `true`: retorno `null`, ausência de asserção e valores verdadeiros de outro tipo não aprovam um cenário. Rejeições de negócio conferem classe e mensagem da regra pretendida. Casos de autorização conferem status 403, entidade inexistente confere 404 e CSRF confere 419. Geração de token e identificador de favoritos verifica estabilidade dentro da sessão e diferença depois de reiniciar o valor da sessão.

| Grupo | Cenários | O que a asserção observa |
|---|---|---|
| Escape | 2 | Codificação exata de HTML/aspas e substituição de bytes inválidos |
| CSRF | 4 | Formato e renovação do token; ausência/incorreção com 419; valor correto aceito |
| IDs e cardinalidade | 7 | Zero/seis autores, cinco válidos, deduplicação, negativo, quatro colaboradores e lista opcional |
| Regras de projeto | 4 | Responsável duplicado, título vazio, curso ausente e situação fora da lista, cada um com mensagem própria |
| Autorização e catálogo | 5 | Perfil sem edição, editor sem exclusão, editor sem usuários, entidade fora da lista e upload sem perfil |
| Busca | 4 | Entrada não interpolada, escape LIKE, múltiplos termos e filtros parametrizados |
| Sessão de favoritos | 1 | Estabilidade por sessão e renovação ao trocar identificador |
| Incorporação de vídeos | 4 | YouTube/Vimeo permitidos, domínio parecido bloqueado e canal sem vídeo sem iframe |
| Templates | 2 | Escape real no card e classe de navegação ativa na rota correspondente |
| Limite de busca | 1 | Limite multibyte de 150 caracteres |

O teste de construção de SQL não substitui executar a consulta. Os testes de templates não substituem navegador ou acessibilidade. Essas verificações são complementadas por integração, HTTP e navegador no CI.

## Mutação: verificação adicional executada

`python tests/mutation.py` cria cópias isoladas e remove, uma por vez, sete guardas: título, curso, situação, responsável duplicado, máximo de IDs, exclusão por editor e CSRF. Cada cenário específico deve reprovar na cópia alterada. Resultado local: **7 de 7 mutações detectadas** em `docs/evidencias/mutation.json`. O código da aplicação não é modificado por esse teste. Isso não mede cobertura de todas as possíveis mutações do sistema.

## Integração e HTTP

As rejeições relacionais foram refinadas para exigir SQLSTATE `23000` e código MySQL 1452 (associação inexistente/incompatível) ou 1451 (exclusão restrita). Qualquer exceção de conexão ou sintaxe deixa de contar como regra respeitada. Concorrência exige a mensagem de versão desatualizada; os casos de acesso exigem seus status.

O cliente HTTP codifica caracteres acentuados nas URLs. O teste de cookie exige conjuntamente HttpOnly e SameSite=Lax. O workflow utiliza Bash com `pipefail`: uma suíte com erro não aparece aprovada apenas porque o comando `tee` conseguiu gravar a saída. A primeira execução real revelou esse risco e foi mantida como histórico, sem declarar o conjunto aprovado.

O navegador confere status HTTP e destino final de navegação, evitando que uma página de login recebida por redirecionamento seja confundida com uma página administrativa validada. Os screenshots são gerados do navegador que acessa PHP e MySQL reais; não são renderizações de fixtures.
