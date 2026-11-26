# Resultados reais, correções e pendências

Registro de 26/11/2026. Os JSONs e logs em `docs/evidencias/` são saídas das execuções locais. Não existem screenshots ou resultados de banco fabricados.

## Executado

| Verificação | Resultado | Evidência |
|---|---|---|
| Testes unitários PHP | 34 aprovados, 0 reprovados | `evidencias/unit.json` |
| Sintaxe dos arquivos PHP | Aprovada nos arquivos listados | `evidencias/php-lint.txt` |
| Sintaxe JavaScript | `node --check` concluído sem erro | `evidencias/validacao.json` |
| Sintaxe Python | Compilação concluída sem erro | `evidencias/validacao.json` |
| Diagnóstico das extensões | PDO MySQL, mbstring, fileinfo e GD carregados com `-d extension=gd` | `evidencias/diagnostico.txt` |
| Integração PDO/MySQL | Bloqueada antes de executar cenários | `evidencias/integration.json` |
| Cliente HTTP contra localhost | Bloqueado antes de executar cenários | `evidencias/http.json` |

O ambiente disponível usa PHP 8.0.30. O XAMPP contém MariaDB 10.4.32 e diretório de dados isolado em `runtime/mysql`, ignorado no Git. O processo do banco foi iniciado e indicou prontidão na porta 3308; a conexão do cliente e do PDO retornou Windows 10013 (socket proibido). Uma tentativa de pipe local também foi recusada com acesso negado. **Não foram criadas tabelas nem carregada a demo neste turno.** Não se afirma que os 12 projetos estejam instalados no banco deste computador.

O servidor PHP indicou início em `127.0.0.1:8080`, mas o cliente HTTP deste ambiente recebeu o mesmo bloqueio de socket. Isso não comprova navegação ou funcionamento do site. O diagnóstico final comprovou gravação real nas três pastas de storage. A consulta inicial is_writable retornava falso neste ambiente Windows apesar de a escrita funcionar; o diagnóstico passou a testar criação e remoção de um arquivo temporário. Confira as permissões novamente ao executar sob o usuário do Apache.

## Cobertura automatizada preparada, ainda não executada

- `tests/integration.php`: persistência e relacionamentos, edição concorrente, estados, autorização de exclusão, curso/instituição/UC/subárea incompatíveis, contagens, busca, mass assignment, cascata e CRUD de todos os cadastros.
- `tests/http_test.py`: página pública, busca/filtros, detalhes, acesso por ID, sessão, CSRF, login, SQLi, escape de XSS, cabeçalhos, CRUD, edição, upload válido/inválido, MIME, PDF ativo, descrição acessível e editor sem privilégio administrativo.
- `.github/workflows/tests.yml`: MySQL 8.4 real, PHP, setup e as três suítes. Workflow não publicado/não executado.
- `docs/criterios-aceitacao.md`: os 12 CA, com resultado real marcado como não executado; roteiro manual de celular, teclado, leitor de tela e múltiplos navegadores.

## Problemas encontrados e corrigidos

1. A rota atual não chegava ao escopo do template: o menu ativo e a barra administrativa podiam desaparecer. A função `view` agora compartilha a rota; o teste de navegação ativa passou.
2. Faltava reescrita de URLs no Apache dentro de `public/`. Acrescentado `.htaccess`; ainda requer homologação no Apache.
3. Os cards sem imagem apontavam para SVGs inexistentes. Quatro imagens locais foram criadas.
4. O total público possuía um sinal de `+` decorativo. Removido para exibir a quantidade exata.
5. A busca inicial tratava todos os nomes digitados como uma única sequência. Os termos agora são combinados, permitindo encontrar autores diferentes do mesmo projeto; teste unitário passou.
6. Serviços de projeto/upload receberam verificação explícita de perfil, além das proteções das rotas. IDs de associação com formatos não numéricos passaram a ser recusados.
7. Uma falha de banco ao renderizar erro poderia tentar consultar autenticação novamente. A página de erro agora dispensa nova consulta de sessão ao banco.
8. GD estava desativado no PHP CLI. Scripts de execução carregam a extensão explicitamente e o setup verifica sua presença antes da carga de demonstração. Não foi alterado o PHP global da máquina.
9. O nome inicial do teste Python sombreava o módulo padrão `http`. Renomeado para `http_test.py`; agora o teste inicia e registra o bloqueio real de socket.
10. O teste de escrita do diagnóstico e uma imagem PNG de teste tinham resultados enganosos (ACL e CRC). O diagnóstico agora grava e remove um arquivo real; a fixture PNG é gerada com CRC válido.
11. Senhas demo passaram a exigir valores distintos e limite de 12–72 caracteres. Não há senha padrão versionada na aplicação.

## Pendências e limites materiais

| Item | Situação | Como concluir |
|---|---|---|
| RNF17 / MySQL | Sem execução em MySQL real | Provisionar MySQL 8 e executar schema, demo e integração |
| Fluxos persistentes / CA01–CA11 | Código implementado, não homologado | Executar suites integration e HTTP em ambiente com acesso local |
| Responsividade / CA12 | CSS implementado; sem inspeção visual real | Testar resoluções e dispositivos do roteiro |
| Compatibilidade e acessibilidade | Práticas implementadas; sem auditoria completa | Testar navegadores, leitor de tela, contraste e zoom |
| Desempenho | Paginação/índices implementados; sem benchmark | Medir latência e carga com volume representativo |
| Backup/restauração | Scripts e procedimento presentes, não executados | Restaurar banco+uploads em destino isolado e comparar |
| HTTPS e RNF18 | Preparados; sem acesso ao servidor Senac | Configurar domínio/TLS, homologar e publicar |
| Git e GitHub | Sem remoto; gravação em `.git/index.lock` negada, nenhum commit criado; conector sem criação de repositório | Em terminal com permissão, criar commits, criar repositório vazio e enviar |
| LGPD em operação real | Página e minimização; definições institucionais pendentes | Definir base legal, responsáveis, contato e autorizações |
| QR Code | Opcional não implementado | Acrescentar após homologar todos os obrigatórios |

Favoritos expiram com a sessão e retenção; não são sincronizados entre dispositivos. Visualizações contam sessões e não pessoas únicas. Catálogo PDF usa a função imprimir/salvar do navegador; não há gerador PDF no servidor. Materiais demonstrativos não representam pesquisa realizada nem vídeo real dos projetos. O filtro básico de PDF não substitui verificação de malware. Não há certificação de conformidade integral com LGPD ou WCAG.

## Critério para considerar pronto

Não basta existirem 47 correspondências na matriz: é necessário executar o banco MySQL, testar os fluxos e os 12 CA, resolver falhas eventualmente encontradas e registrar a URL Senac. A entrega atual reúne implementação, testes reproduzíveis e evidências parciais honestas; as pendências acima impedem afirmar homologação completa.

## Controle de versão local

A tentativa real de `git add` retornou `Unable to create .git/index.lock: Permission denied`. Não houve commit nem push. Em um terminal normal com permissão de escrita no repositório, use `git add .` e `git commit -m "Implementa vitrine acadêmica, painel, segurança e documentação"`. Revise os arquivos ignorados e não force a inclusão de `.env`, `runtime` ou backups. O projeto não alterou nenhum outro repositório.
