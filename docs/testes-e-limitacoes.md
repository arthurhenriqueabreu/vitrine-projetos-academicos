# Resultados reais, correções e limites

Validação real em 26/11/2026: **34 unitários, 34 de integração, 48 HTTP e 18 de navegador, todos aprovados**. Schema e demonstração foram executados em MySQL 8.4.13, com PHP 8.3 no GitHub Actions. Os 12 critérios CA01–CA12 passaram no Chromium automatizado; CA12 usou viewport de smartphone (390×844), complementada por tablet (768×1024) e desktop (1440×900). Não houve teste manual em aparelho físico, nem operação manual do navegador do usuário. A inspeção visual foi feita sobre screenshots reais.

Execução auditável: [45889043842](https://github.com/arthurhenriqueabreu/vitrine-projetos-academicos/actions/runs/45889043842). Código testado: `4b4a1e7d58c84407b1390f49453c32bfef3c2f18`. Evidências em `docs/evidencias/ci/`, com JSONs e screenshots. As rodadas com falhas foram preservadas no histórico; nenhum resultado foi convertido de falha para aprovação sem nova execução.

O erro 10013 continua no contexto local restrito: quatro portas recusaram conexão enquanto um bind efêmero foi aceito. Não foi alterado firewall, antivírus, UAC ou ACL. Docker local não expôs um daemon utilizável; o navegador local de automação também estava indisponível. A alternativa autorizada de CI resolveu o bloqueio da validação, **não modificou a segurança do Windows nem comprovou execução local**. O banco de destino foi MySQL real, sem MariaDB, SQLite ou mocks no ambiente validado.

O repositório está publicado em [GitHub](https://github.com/arthurhenriqueabreu/vitrine-projetos-academicos). A pasta `.git` local permanece sem permissão de escrita nesta sessão; os commits foram criados pela API autorizada e pelo CI. RNF18 (servidor Senac), HTTPS de produção, testes em outros navegadores, auditoria completa de acessibilidade e restauração real de backup continuam pendentes.

## Execuções e evidências

| Suíte | Aprovados | Reprovados | Evidência |
|---|---:|---:|---|
| Unitários PHP | 34 | 0 | `evidencias/ci/unit.json` |
| Integração MySQL | 34 | 0 | `evidencias/ci/integration.json` |
| HTTP | 48 | 0 | `evidencias/ci/http.json` |
| Navegador Chromium | 18 | 0 | `evidencias/ci/browser.json` |
| Mutação local | 7 detectadas | 0 não detectadas | `evidencias/mutation.json` |

Foram verificadas 49 combinações de página/viewport: 22 páginas em desktop, as mesmas 22 em mobile e cinco páginas principais em tablet. Há screenshots das páginas públicas e administrativas, gerados após rolagem para carregar as imagens lazy. O código e os JSONs identificam o commit efetivamente testado. Logs e artefatos completos também estão na execução acima. O servidor usado no CI é temporário e não representa hospedagem pública do site.

## Falhas encontradas e correções

1. Fixtures unitários podiam conter erro adicional de colaborador duplicado. A base passou a ser válida; asserções conferem mensagem/status específico e exigem retorno `true`.
2. Testes de integração que aceitavam qualquer exceção passaram a exigir a rejeição correta (403/404, mensagem ou SQLSTATE 23000 e códigos FK 1451/1452).
3. Uma URL de teste com acento não estava codificada; a primeira execução HTTP teve 39 aprovações e uma falha. Corrigida a URL e refeita a execução.
4. Saída passada por `tee` escondia o exit code das suítes. O workflow agora usa Bash com `pipefail` e resultado conjunto obrigatório.
5. Ubuntu latest mudou para uma versão não suportada pelo Playwright fixado. Ambiente de teste fixado em Ubuntu 24.04.
6. Um rótulo em caixa alta por CSS gerava falso negativo em CA06. O teste normaliza caixa visual mantendo a exigência de conteúdo.
7. A captura de página inteira não carregava todas as imagens lazy; a rolagem suave atrasava a visita às partes inferiores. A captura agora percorre a página com rolagem instantânea e exige imagens completas. Não foi alterado o CSS da aplicação.
8. A inspeção de screenshots reais revelou biografia/instituição do colaborador duplicada no detalhe. A repetição foi removida do template e CA06 recebeu uma asserção de regressão.
9. Evidências de execuções anteriores podiam permanecer no diretório do CI. Cada execução agora limpa apenas seu diretório gerado e grava o SHA da origem.
10. A inspeção visual da home mobile revelou texto cortado pelo grid apesar de o documento não ter overflow horizontal. Corrigida somente a coluna mobile com `minmax(0,1fr)` e largura mínima zero; CA12 passou a verificar os retângulos de texto, além da largura da página. Aprovações anteriores de CA12 não cobriam esse defeito visual.

Histórico: execuções com falhas são consultáveis no GitHub; resultados anteriores selecionados estão em `evidencias/historico/`. Não são evidências de aprovação do código final.

## Windows 10013 e execução local

O diagnóstico `bin/diagnose-network.py` registrou 10013 nas portas 3306, 3308, 8080 e 49153, com bind efêmero bem sucedido e token de baixa integridade. Isso é compatível com restrição do contexto de execução; a regra específica de WFP/firewall não foi determinada. Não se atribui o erro a uma senha MySQL ou a uma porta ocupada sem evidência. Docker não disponibilizou o pipe do daemon, e a conexão de automação do navegador local estava indisponível. Nenhuma regra de segurança foi desligada. A alternativa expressamente autorizada de GitHub Actions permitiu a validação real.

## Limites restantes

- RNF18 e HTTPS de produção: sem credenciais/domínio/certificado do servidor Senac; não implantado.
- Testes manuais de interação no computador do usuário e em smartphone físico: não executados. Os fluxos foram automatizados em navegador real no CI.
- Firefox, Safari e Edge, leitor de tela e conformidade WCAG completa: não auditados. Teclado e menu foram exercitados no Chromium.
- PDFs: bytes, tipo MIME e download foram validados; o iframe existe. A renderização nativa de PDF em headless/múltiplos navegadores não foi homologada e pode aparecer vazia na captura. Há links para visualizar e baixar.
- Vídeos: cadastro/edição/remoção e domínio de incorporação verificados; reprodução e disponibilidade remota não certificadas. O canal institucional demo não é vídeo real do projeto fictício.
- Backup/restauração: scripts presentes, mas restauração real não executada. Desempenho em grande volume não medido.
- LGPD operacional: base legal, contato e autorizações definitivas dependem da instituição. Dados de demonstração permanecem fictícios.
- QR Code opcional não implementado. Favoritos permanecem por sessão, sem sincronização entre aparelhos.

O sistema foi publicado como código no GitHub, com commits descritivos. Isso não equivale à publicação do site no servidor do Senac. A identidade visual foi preservada; as mudanças de apresentação corrigiram a biografia duplicada e o texto cortado na home mobile.
