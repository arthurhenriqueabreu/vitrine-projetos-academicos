# Matriz dos 18 requisitos não funcionais

Qualidade operacional não é comprovada só pela existência de código. Os estados distinguem implementação e evidência.

| Código | Requisito | Implementação / evidência | Estado e limite |
|---|---|---|---|
| RNF01 | Responsividade | CSS com breakpoints 1100/800/520 px; menu móvel | Validado em Chromium a 390/768/1440 px; aparelho físico não executado |
| RNF02 | Interface intuitiva e cores Senac | CSS próprio azul/laranja, navegação e hierarquia | Inspeção visual das capturas; teste de usabilidade com usuários não executado |
| RNF03 | Legibilidade | Fontes locais, espaçamento, cores, tamanhos relativos | Legibilidade observada nas capturas; contraste não auditado integralmente |
| RNF04 | Autenticação e acesso | Auth, perfis e rotas backend; testes unitários de negativas | Validado por HTTP e navegador (login, sessão, editor e administrador) |
| RNF05 | Hash de senhas | password_hash/password_verify; senha não armazenada em claro | Login real com password_verify e contas persistidas; hash implementado no cadastro |
| RNF06 | HTTPS em produção | APP_ENV=production exige HTTPS; HSTS; VirtualHost TLS | Preparado; TLS de produção não implantado |
| RNF07 | Validação de formulários | Serviços no backend e constraints HTML/JS | Regras exercitadas em unitários, integração, HTTP e formulários do navegador |
| RNF08 | Banco relacional | Schema InnoDB, PK/FK/UNIQUE/CHECK, transações PDO | Validado: schema, transações e FKs em MySQL 8.4.13 |
| RNF09 | Carregamento adequado | Paginação 12 itens, índices, imagens lazy | Navegação executada com 12 projetos; carga e grande volume não testados |
| RNF10 | Imagens otimizadas | GD reprocessa JPEG até 1920 px, qualidade 85 | Upload PNG convertido em JPEG e visualizado em testes reais; rotina de limite presente |
| RNF11 | Acessibilidade | Labels, alt obrigatório, skip link, foco, teclado e reduced-motion | Teclado, skip link e menu exercitados; leitor de tela/WCAG completa pendentes |
| RNF12 | Navegadores atuais | HTML/CSS/JS padrão e fallbacks de compartilhamento | Chromium validado; Firefox, Safari e Edge não executados |
| RNF13 | Erros seguros | Erros genéricos públicos, log privado | Erros 403/404/419 e rejeições exercitados sem exposição de credenciais; falhas gerais não exaustivas |
| RNF14 | Backup e recuperação | bin/backup.ps1 e bin/restore.ps1 | Scripts presentes; backup/restauração real não executados |
| RNF15 | Código organizado e documentado | Camadas app/views/database; README e docs | Arquivos organizados, documentação e commits publicados |
| RNF16 | LGPD | Minimização, dados fictícios, privacidade e manutenção de retenção | Dados fictícios e minimização; base legal e canal definitivo dependem da instituição |
| RNF17 | MySQL obrigatório | Driver PDO mysql; SQL para MySQL 8; CI MySQL 8.4 | Validado em MySQL Community Server 8.4.13 no CI; local Windows permanece bloqueado |
| RNF18 | Publicação no Senac | VirtualHost e procedimento de implantação | Pendente: sem acesso ao servidor Senac |

Evidências: `evidencias/ci/`, execução https://github.com/arthurhenriqueabreu/vitrine-projetos-academicos/actions/runs/45888237933. Estados parciais não foram transformados em aprovação por mera existência de código.

Evidências: `evidencias/ci/`, execução https://github.com/arthurhenriqueabreu/vitrine-projetos-academicos/actions/runs/45889043842. Estados parciais não foram transformados em aprovação por mera existência de código.
