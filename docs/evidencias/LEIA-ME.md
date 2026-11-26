# Evidências da validação

Código testado: `4b4a1e7d58c84407b1390f49453c32bfef3c2f18`.

Execução aprovada: [GitHub Actions 45889043842](https://github.com/arthurhenriqueabreu/vitrine-projetos-academicos/actions/runs/45889043842).

- `ci/unit.json`: 34 aprovações, 0 falhas; PHP 8.3.
- `ci/integration.json`: 34 aprovações, 0 falhas; MySQL Community Server 8.4.13.
- `ci/http.json`: 48 aprovações, 0 falhas.
- `ci/browser.json`: 18 aprovações, 0 falhas; contém CA01–CA12 e 49 combinações página/viewport.
- `ci/setup.txt` e `ci/diagnostico.txt`: instalação e conexão efetivamente executadas.
- `ci/screenshots/`: 57 capturas reais do Chromium, com dados fictícios da aplicação; nenhuma foi gerada por IA.
- `ci-proveniencia.json`: SHA do código, commit de evidências e blobs originais dos 65 arquivos do CI. Todos os 65 blobs foram conferidos ao copiar para esta entrega.
- `mutation.json`: sete guardas removidas em cópias isoladas, sete defeitos detectados pelos cenários pretendidos.
- `rede-windows.json`: diagnóstico local real do erro 10013, sem mudança em mecanismos de segurança.
- `comparacao-enunciados.json`: comparação textual integral de Recuperação(1).pdf e Recuperação(2).pdf.
- `historico/`: saídas anteriores, incluindo falhas e bloqueios; não representam o resultado final.
- `manifesto-sha256.json`: checksums dos arquivos da entrega, sem incluir o próprio manifesto.

## Inspeção visual

Foram examinadas folhas de revisão das páginas públicas e administrativas, além de capturas integrais selecionadas de início, vitrine, painel, formulário e detalhe. A revisão revelou texto cortado na home mobile e biografia duplicada no detalhe, corrigidos e cobertos por regressão. A captura final da home em 390 px mostra título, parágrafo e cartões decorativos dentro da largura disponível. As tabelas administrativas mantêm rolagem interna horizontal no celular.

As capturas podem mostrar mais edições e um rascunho adicional, pois os testes cadastram esses registros em base descartável. Contadores diferentes entre capturas anteriores e posteriores refletem essas operações reais. O setup inicial possui 12 projetos, dez publicados e duas edições.

O navegador foi automatizado em CI. Não houve operação manual do navegador do usuário, teste em aparelho físico nem auditoria de acessibilidade completa. A renderização nativa de PDF e a reprodução remota dos vídeos não são certificadas por estes screenshots; o download de PDF, links e CRUD dos materiais foram testados. Publicar código no GitHub não significa implantar o site no Senac.
