# Vitrine Digital de Projetos Acadêmicos

Trabalho de recuperação de Full Stack e Engenharia de Software — Faculdade Senac Joinville.

**Integrantes:** Arthur Henrique Abreu e Matheus Fonte.

O sistema apresenta projetos acadêmicos publicados, conecta seus materiais e organiza a memória das edições. Visitantes não precisam criar conta. Administradores gerenciam cadastros e usuários; editores cadastram, editam e publicam trabalhos.

## Situação da entrega

Validação real em 26/11/2026: **34 unitários, 34 de integração, 48 HTTP e 18 de navegador, todos aprovados**. Schema e demonstração foram executados em MySQL 8.4.13, com PHP 8.3 no GitHub Actions. Os 12 critérios CA01–CA12 passaram no Chromium automatizado; CA12 usou viewport de smartphone (390×844), complementada por tablet (768×1024) e desktop (1440×900). Não houve teste manual em aparelho físico, nem operação manual do navegador do usuário. A inspeção visual foi feita sobre screenshots reais.

Execução auditável: [45889043842](https://github.com/arthurhenriqueabreu/vitrine-projetos-academicos/actions/runs/45889043842). Código testado: `4b4a1e7d58c84407b1390f49453c32bfef3c2f18`. Evidências em `docs/evidencias/ci/`, com JSONs e screenshots. As rodadas com falhas foram preservadas no histórico; nenhum resultado foi convertido de falha para aprovação sem nova execução.

O erro 10013 continua no contexto local restrito: quatro portas recusaram conexão enquanto um bind efêmero foi aceito. Não foi alterado firewall, antivírus, UAC ou ACL. Docker local não expôs um daemon utilizável; o navegador local de automação também estava indisponível. A alternativa autorizada de CI resolveu o bloqueio da validação, **não modificou a segurança do Windows nem comprovou execução local**. O banco de destino foi MySQL real, sem MariaDB, SQLite ou mocks no ambiente validado.

O repositório está publicado em [GitHub](https://github.com/arthurhenriqueabreu/vitrine-projetos-academicos). A pasta `.git` local permanece sem permissão de escrita nesta sessão; os commits foram criados pela API autorizada e pelo CI. RNF18 (servidor Senac), HTTPS de produção, testes em outros navegadores, auditoria completa de acessibilidade e restauração real de backup continuam pendentes.

Consulte [resultados e pendências](docs/testes-e-limitacoes.md) e as matrizes de requisitos.

## Funcionalidades

- Início institucional com contagem real, áreas e três destaques.
- Vitrine paginada; busca por título, autores, instituição, palavra-chave, curso e ano. Termos separados por espaço são combinados, podendo localizar autores diferentes no mesmo projeto.
- Filtros combinados de edição, área, subárea, curso, instituição e ano; limpeza, estado vazio e identificação da edição.
- Detalhes com autores, orientador responsável, colaboradores, curso, unidade curricular, instituição, resumo, objetivos, palavras-chave, imagens, PDFs, links e vídeos.
- Login, logout, expiração por inatividade, perfis, dashboard e auditoria administrativa.
- CRUD de projetos, autores, orientadores, instituições, áreas, subáreas, cursos, unidades curriculares, edições, palavras-chave e usuários.
- Rascunhos, publicados, arquivados e destaques; controle de edição concorrente por versão.
- Upload real de imagens e PDFs, otimização de imagens, descrição acessível e exclusão de materiais.
- Favoritos anônimos persistidos no banco e vinculados à sessão; compartilhamento; contagem de visualizações por sessão.
- Catálogo completo com filtros e impressão para salvar em PDF pelo navegador. Não depende de serviço externo de PDF. QR Code não implementado (opcional).

## Tecnologias e arquitetura

PHP 8.0+ (execução local disponível: 8.0.30), PDO MySQL, MySQL 8, HTML5, CSS3 e JavaScript sem dependências de frontend. Para implantação, usar uma versão PHP com suporte e validar as extensões. Bootstrap era uma preferência no enunciado: optou-se por CSS próprio para a identidade visual, sem dependência de CDN.

Apresentação: `views/` e `public/assets/`. Aplicação e rotas: `public/index.php`. Regras: `ProjectService`, `CatalogService`, `UploadService` e `Auth`. Consultas: `ProjectRepository` e `Database`. Dados: banco relacional MySQL, InnoDB e arquivos fora da pasta pública. Configuração em `.env`.

```text
app/                  Serviços, autenticação, repositório, conexão e bootstrap
bin/                  Instalação, usuários, diagnóstico, manutenção e backup
database/             Schema, dados fictícios e provisionamento de exemplo
deploy/               VirtualHost Apache HTTPS
docs/                 Matrizes, diagramas e evidências reais
public/               Única raiz pública; controlador, roteador e assets
storage/uploads/      Arquivos privados entregues por rota autorizada
storage/backups/      Backups locais (ignorados no Git)
storage/logs/         Logs privados (ignorados no Git)
tests/                Testes unitários, integração MySQL e HTTP
views/                Templates públicos e administrativos
.github/workflows/    CI preparado para executar contra MySQL 8.4
```

## Instalação no Windows / XAMPP

1. Mantenha a pasta do projeto fora da raiz pública geral do Apache, ou aponte o VirtualHost exclusivamente para `public/`. Não exponha a raiz do projeto, `.env`, `database/`, `bin/` ou `storage/`.
2. Habilite `pdo_mysql`, `mbstring`, `fileinfo` e `gd` no `php.ini`. Configure `upload_max_filesize=10M` e `post_max_size=12M`. Reinicie o Apache após alterar o PHP.
3. Inicie um servidor **MySQL 8**. O MariaDB que acompanha muitas instalações do XAMPP não comprova o requisito estrito de MySQL. O PHP/Apache do XAMPP pode conectar normalmente a um servidor MySQL separado.
4. Copie o arquivo de configuração:

```powershell
Copy-Item .env.example .env
```

5. Execute `database/provision.example.sql` como administrador do MySQL, substituindo a senha de exemplo. Alternativamente, crie o banco e o usuário pelo MySQL Workbench/phpMyAdmin. O banco deve usar `utf8mb4` e InnoDB. Nunca use a senha literal do exemplo. Não utilize root como usuário permanente da aplicação.
6. Preencha `.env`: `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` e `APP_URL`. Para execução com o servidor PHP abaixo, use `APP_URL=http://127.0.0.1:8080` e `SESSION_SECURE=false`.
7. Para instalar demonstração em banco vazio, defina no `.env` duas senhas distintas de 12 a 72 caracteres em `DEMO_ADMIN_PASSWORD` e `DEMO_EDITOR_PASSWORD`, depois execute:

```powershell
& C:\xampp\php\php.exe -d extension=gd bin/setup.php --demo
```

O setup cria tabelas, 12 projetos fictícios (10 publicados, um rascunho e um arquivado), duas edições, três áreas, cadastros relacionados, imagens locais, um PDF e links de referência. Não execute duas vezes a carga demo. As imagens são ilustrações originais e os dados fictícios estão identificados. O canal de vídeo institucional é uma referência, não uma apresentação real dos projetos fictícios.

Para criar apenas tabelas, use `php bin/setup.php` sem `--demo`. Não há usuário ou senha embutido no banco. A criação de usuário pela CLI usa `NEW_USER_PASSWORD`, `ACTING_ADMIN_ID` e `bin/create-user.php`. Em banco sem usuários, a primeira conta deve ser administrador; a auditoria é vinculada à própria conta criada. Nas criações seguintes, `ACTING_ADMIN_ID` deve identificar um administrador ativo. A CLI não é uma porta de cadastro público.

```powershell
$env:NEW_USER_PASSWORD='DEFINA_UMA_SENHA_FORTE_AQUI'
& C:\xampp\php\php.exe bin/create-user.php 'Administrador' 'admin@vitrine.example' 'administrador'
Remove-Item Env:NEW_USER_PASSWORD
```

8. Remova `DEMO_ADMIN_PASSWORD` e `DEMO_EDITOR_PASSWORD` do `.env` após a instalação. Revogue permissões DDL do usuário da aplicação conforme o script de provisionamento.
9. Execute o diagnóstico e o servidor:

```powershell
& C:\xampp\php\php.exe -d extension=gd bin/diagnose.php
.\bin\start-local.ps1
```

Abra [http://127.0.0.1:8080](http://127.0.0.1:8080). A equipe acessa `/login` usando `admin@vitrine.example` ou `editor@vitrine.example` e as senhas que você definiu. Não existe senha padrão compartilhada. Em servidores com GD já habilitado, retire `-d extension=gd` para evitar carregamento duplicado.

O comando alternativo `bin/install.php` cria somente um banco novo com `INSTALL_DB_USER` e `INSTALL_DB_PASSWORD`; ele recusa bancos existentes e não concede privilégios. O fluxo SQL acima é suficiente e mais explícito.

## Apache

Ative `mod_rewrite` e permita `.htaccess` no diretório `public/`. O arquivo `public/.htaccess` encaminha rotas ao controlador e impede listagem. Para desenvolvimento, um VirtualHost pode usar:

```apache
<VirtualHost *:80>
    ServerName vitrine.local
    DocumentRoot "C:/Users/User/Documents/Dev/vitrine-projetos-academicos/public"
    <Directory "C:/Users/User/Documents/Dev/vitrine-projetos-academicos/public">
        AllowOverride All
        Options -Indexes
        Require all granted
    </Directory>
</VirtualHost>
```

Ajuste o arquivo hosts do computador se utilizar `vitrine.local`, e altere `APP_URL`. Para evitar mudanças no hosts, use o servidor PHP local. Não copie a raiz do projeto diretamente para `htdocs` sem a proteção indicada.

## Banco de dados e regras

O [schema](database/schema.sql) contém 19 tabelas. A entidade central é `trabalhos`. Autores e palavras-chave usam relações N:N. Um responsável é uma FK obrigatória no projeto; colaboradores são N:N com posição limitada a três. `trabalho_colaboradores` substitui a sugestão genérica `trabalho_orientadores`, distinguindo o responsável.

Há FKs compostas para manter curso/instituição, UC/curso e subárea/área consistentes. O cadastro usa transações, entre um e cinco autores distintos, até três colaboradores distintos do responsável, situação enumerada e identificador aleatório único. Contadores e buscas compartilham os mesmos predicados. Somente administradores excluem projetos; editores editam conteúdos de todos os trabalhos, conforme o perfil global definido no enunciado. Cadastros auxiliares e usuários são exclusivos do administrador.

O mínimo de um autor é uma garantia transacional da aplicação, não uma restrição SQL isolada: operações SQL externas devem respeitar o mesmo serviço ou processo de validação. As posições únicas e `CHECK` limitam as quantidades máximas. Exclusões de projetos removem relações/materiais; cadastros ainda utilizados são protegidos pelas FKs.

Diagrama completo em [docs/arquitetura-e-banco.md](docs/arquitetura-e-banco.md).

## Segurança

- PDO com parâmetros e emulação desligada; nomes dinâmicos de tabelas vêm de lista permitida.
- Escape de HTML com `htmlspecialchars`; não se aceita HTML ativo em resumos.
- CSRF obrigatório em todos os POSTs; ações destrutivas nunca usam GET.
- Senhas com `password_hash`/`password_verify`; mínimo de 12 caracteres; sessão regenerada no login, cookie HttpOnly/SameSite e inatividade de 30 minutos.
- Limite de dez falhas de login por hash de IP em 15 minutos. Redes compartilhadas podem dividir esse limite.
- Perfis verificados no backend; arquivos de rascunhos não são expostos ao visitante; ID de material deve pertencer ao projeto.
- Campos explicitamente permitidos em alterações, versão otimista e transações.
- Upload: extensão, MIME pelo `finfo`, tamanho, dimensões de imagens, descrição obrigatória e nomes aleatórios. Imagens são decodificadas e regravadas em JPEG; uploads ficam fora de `public/`.
- PDFs passam por verificação básica de cabeçalho e marcadores de conteúdo ativo. Isso **não é antivírus nem análise completa do formato PDF**; a instituição deve avaliar controles adicionais e conteúdo antes de publicar.
- URLs apenas HTTP/HTTPS, sem credenciais; iframes de vídeo apenas YouTube sem cookies e Vimeo, carregados por escolha do visitante.
- CSP, nosniff, proteção de frames, política de referenciador, HTTPS obrigatório e HSTS quando `APP_ENV=production`.
- Erros técnicos em logs privados; mensagens públicas genéricas. `.env`, backups e uploads não entram no Git.

## Backup, recuperação e LGPD

Configure `MYSQL_BACKUP_CONFIG` apontando para um arquivo protegido fora do repositório com seção `[client]`, host, port, user e password; configure `DB_DATABASE` no ambiente. Execute `bin/backup.ps1`. O script gera SQL consistente (`--single-transaction`), checksum SHA-256 e ZIP com uploads. Mantenha uma cópia criptografada fora do servidor e com acesso restrito.

Para recuperação, crie um banco vazio separado, ajuste `DB_DATABASE` para esse destino e execute `bin/restore.ps1 -SqlFile CAMINHO.sql`. Restaure também `storage/uploads/` do mesmo backup. Compare contagem de projetos, relações e arquivos antes de alterar a configuração de produção. Nunca teste restauração sobre o banco em uso. Esse procedimento está implementado, mas não foi executado neste ambiente.

Agende `php bin/maintenance.php` diariamente para remover tentativas antigas e favoritos com mais de 30 dias. Antes de usar dados reais, a instituição define base legal, autorizações de divulgação/imagem, política de retenção e canal de direitos do titular. Há uma página `/privacidade`; o contato institucional definitivo precisa ser definido pela coordenação. Não coletar CPF, telefone ou dados sensíveis. Não há rastreadores de publicidade.

## Testes reproduzíveis

```powershell
& C:\xampp\php\php.exe -d extension=gd tests/unit.php
```

Para integração, provisione um banco **descartável** como `vitrine_test`, configure as variáveis do shell e carregue a demo. Os testes recusam nomes que não terminam em `_test`; geram e removem registros de teste, mas não prometem preservar a base demo intacta.

```powershell
$env:DB_DATABASE='vitrine_test'
$env:DB_USERNAME='USUARIO_TESTE'
$env:DB_PASSWORD='SENHA_TESTE'
$env:DEMO_ADMIN_PASSWORD='SENHA_ADMIN_DEMO'
$env:DEMO_EDITOR_PASSWORD='SENHA_EDITOR_DEMO'
& C:\xampp\php\php.exe -d extension=gd bin/setup.php --demo
& C:\xampp\php\php.exe -d extension=gd tests/integration.php
# Em outro terminal, inicie o servidor com essas MESMAS variáveis:
.\bin\serve.ps1
# No terminal de testes, mantenha DB_DATABASE e senhas acima:
python tests/http_test.py
```

As senhas dos exemplos precisam ter 12 a 72 caracteres e ser distintas. `TEST_URL` permite alterar a URL dos testes. `tests/integration.php` testa persistência, FKs, CRUD, concorrência, contadores e permissões. `tests/http_test.py` cobre formulários, sessões, autorização, upload e rotas públicas. [CA01–CA12](docs/criterios-aceitacao.md) incluem o roteiro manual de responsividade; não há alegação de teste em smartphone sem execução real. O workflow CI foi executado com MySQL real; resultados e screenshots estão versionados em `docs/evidencias/ci/`.

## Publicação no Senac

Siga [docs/implantacao.md](docs/implantacao.md) e adapte `deploy/apache-vhost.conf`. Exponha somente `public/`, instale certificado, configure MySQL e diretórios graváveis, aplique HTTPS e remova a demonstração se forem usados dados reais. **RNF18 permanece pendente por ausência de acesso/credenciais ao servidor.**

## Referência e autoria

Enunciado original: `Recuperação(1).pdf`, 14 páginas (texto comparado à versão `Recuperação(2).pdf`, com conteúdo extraído idêntico), versão disponibilizada na conversa; matriz dos 47 RF, 18 RNF, 14 RN e 12 CA em `docs/`. A referência [FECCIF 2025](https://feccif.com.br/vitrine-de-trabalhos-2025/?area=exatas) foi consultada; o conteúdo recuperável mostrou carregamento e campo de busca. A organização por áreas e busca serviu de inspiração funcional; não foram copiados marca, textos ou código do site. A inspeção textual do PDF não identificou instruções alheias ao trabalho, conforme [docs/analise-enunciado.md](docs/analise-enunciado.md).
