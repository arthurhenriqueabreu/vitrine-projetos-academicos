# Matriz dos 18 requisitos não funcionais

Qualidade operacional não é comprovada só pela existência de código. Os estados distinguem implementação e evidência.

| Código | Requisito | Implementação / evidência | Estado e limite |
|---|---|---|---|
| RNF01 | Responsividade | CSS com breakpoints 1100/800/520 px; menu móvel | Parcial: falta teste visual real em dispositivos |
| RNF02 | Interface intuitiva e cores Senac | CSS próprio azul/laranja, navegação e hierarquia | Parcial: implementação; usabilidade não avaliada com usuários |
| RNF03 | Legibilidade | Fontes locais, espaçamento, cores, tamanhos relativos | Parcial: revisão de CSS; contraste não medido em todas as telas |
| RNF04 | Autenticação e acesso | Auth, perfis e rotas backend; testes unitários de negativas | Implementado; testes HTTP bloqueados |
| RNF05 | Hash de senhas | password_hash/password_verify; senha não armazenada em claro | Implementado; persistência não testada |
| RNF06 | HTTPS em produção | APP_ENV=production exige HTTPS; HSTS; VirtualHost TLS | Preparado; certificado/servidor não configurados |
| RNF07 | Validação de formulários | Serviços no backend e constraints HTML/JS | Parcialmente validado: testes unitários; integração pendente |
| RNF08 | Banco relacional | Schema InnoDB, PK/FK/UNIQUE/CHECK, transações PDO | Implementado; execução do schema bloqueada |
| RNF09 | Carregamento adequado | Paginação 12 itens, índices, imagens lazy | Parcial: sem teste de carga ou medição em execução |
| RNF10 | Imagens otimizadas | GD reprocessa JPEG até 1920 px, qualidade 85 | Implementado; upload HTTP não executado |
| RNF11 | Acessibilidade | Labels, alt obrigatório, skip link, foco, teclado e reduced-motion | Parcial: sem auditoria WCAG/leitor de tela |
| RNF12 | Navegadores atuais | HTML/CSS/JS padrão e fallbacks de compartilhamento | Pendente de matriz Chrome/Firefox/Edge/Safari |
| RNF13 | Erros seguros | Erros genéricos públicos, log privado | Implementado; testes HTTP bloqueados |
| RNF14 | Backup e recuperação | bin/backup.ps1 e bin/restore.ps1 | Implementado; restauração real não executada |
| RNF15 | Código organizado e documentado | Camadas app/views/database; README e docs | Atendido por inspeção dos arquivos |
| RNF16 | LGPD | Minimização, dados fictícios, privacidade e manutenção de retenção | Parcial: base legal e canal definitivo dependem da instituição |
| RNF17 | MySQL obrigatório | Driver PDO mysql; SQL para MySQL 8; CI MySQL 8.4 | Pendente de validação real em MySQL; XAMPP local é MariaDB |
| RNF18 | Publicação no Senac | VirtualHost e procedimento de implantação | Pendente: sem acesso ao servidor |
