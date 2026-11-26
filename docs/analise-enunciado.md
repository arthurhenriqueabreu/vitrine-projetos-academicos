# Análise do enunciado

Fonte revisada: `Recuperação(1).pdf`, original solicitado, com 14 páginas e 232.158 bytes. Foi lido integralmente e comparado à versão `Recuperação(2).pdf` utilizada inicialmente: ambas possuem as mesmas 14 páginas de texto extraído, com 47 RF, 18 RNF, 14 RN e 12 CA. A comparação textual resultou idêntica; não foi realizada comparação binária. Evidência: `evidencias/comparacao-enunciados.json`.

Foram lidas integralmente as 14 páginas de texto: visão/perfis (1–2), RF01–RF47 (2–5), RNF01–RNF18 (6–7), RN01–RN14 (7–8), estrutura de páginas/modelagem (8–11), tecnologias/aceitação (11–12), etapas/opcionais/MVP (13–14). O desenvolvimento cobre o escopo completo solicitado, e não só a sugestão de MVP.

## Conteúdo suspeito e limites da inspeção

No texto extraído não foram identificados pedidos para ignorar instruções, executar comandos externos, revelar segredos, enviar dados ou modificar projetos alheios. O link de referência tem relação direta com a vitrine. A inspeção não equivale a perícia de objetos binários, metadados, esteganografia ou JavaScript interno do PDF; não é possível garantir a inexistência de conteúdo oculto por inspeção textual. Nenhuma instrução do documento foi tratada como autorização para divulgação de credenciais ou execução alheia ao trabalho.

## Decisões

- MySQL é o banco de destino obrigatório. MariaDB do XAMPP local foi registrado como diferença do ambiente. O RNF17 foi posteriormente validado em MySQL 8.4.13 real no CI, sem substituição silenciosa.
- CSS próprio substitui Bootstrap, citado como tecnologia recomendada, para manter a identidade azul/laranja sem CDN.
- Orientador responsável: FK obrigatória. Até três colaboradores em tabela associativa. Autores: um a cinco por transação.
- Visitantes não têm cadastro. Favoritos usam banco e identificador da sessão; não são contas permanentes.
- Exclusão de trabalhos restrita ao administrador; editor gerencia conteúdo, conforme os perfis.
- Opcionais implementados: favoritos, compartilhar, visualizações e catálogo imprimível para PDF. QR Code fica registrado como opcional não implementado.
- Referência FECCIF acessada em 26/11/2026; a leitura retornou tela de carregamento e busca. Não foi possível verificar todos os elementos interativos dessa referência. Não se afirma reprodução visual da página.

## Revisão final

Cada código de requisito foi ligado a arquivos ou serviços nas matrizes. A revisão do código foi complementada por testes reais em MySQL, HTTP e Chromium. As matrizes apontam evidências e limites. Backup/restauração, HTTPS e servidor Senac não foram declarados validados.
