# Implantação no servidor Senac

**Estado:** preparação concluída no código; implantação não realizada. Sem host, domínio, credenciais de SSH/painel/FTP, configuração MySQL e autorização técnica do servidor. RNF18 pendente.

1. Confirmar com a equipe Senac domínio, versão PHP suportada, MySQL 8, HTTPS, extensões PDO MySQL/mbstring/fileinfo/GD e limites de upload. Ajustar fuso `America/Sao_Paulo`.
2. Transferir código, sem `.git`, `runtime`, bases de teste, logs, backups ou credenciais locais. O diretório raiz do site deve ser `public/`.
3. Criar banco `utf8mb4`, usuário de migração e usuário de aplicação com privilégio mínimo. Usar `schema.sql` para estrutura; a demo só é adequada para avaliação identificada como fictícia.
4. Criar `.env` fora da raiz pública: `APP_ENV=production`, `APP_URL=https://DOMINIO`, `SESSION_SECURE=true`, credenciais MySQL e limite de upload. Restringir leitura do arquivo à equipe e processo PHP.
5. Adaptar `deploy/apache-vhost.conf` com diretórios e certificados reais, ativar SSL/rewrite e validar a configuração Apache antes de recarregar. O exemplo não contém certificado funcional.
6. Conceder escrita ao processo PHP somente em `storage/uploads`, `storage/logs` e `storage/backups`. Código e configuração permanecem sem escrita pelo servidor web. Não usar permissão global 777.
7. Criar contas individuais; desabilitar/remover contas demo em operação real. Remover as variáveis de senhas demo. Revisar materiais e autorizações de divulgação com a instituição.
8. Configurar backup externo, retenção e rotina diária `bin/maintenance.php`. Executar restauração em ambiente separado e verificar banco + uploads do mesmo conjunto.
9. Executar testes unitários, integração no banco `_test` separado e HTTP na instância de homologação; realizar os 12 CA, teclado e resoluções 390/768/1440 px. Confirmar PDF, vídeos, downloads, links e inexistência de dados privados na raiz pública.
10. Conferir HTTP→HTTPS, cookies Secure/HttpOnly, CSP, logs e mensagens genéricas. Só apontar o domínio definitivo depois de validar homologação.

## Recuperação e reversão

Antes de atualizar código ou schema, guardar versão do código e backup íntegro. Para reverter, interromper gravações, restaurar banco e uploads correspondentes em destino isolado, validar e trocar configuração. Não importar SQL por cima da base em produção sem plano de migração. As rotinas PowerShell atendem XAMPP/Windows; em servidor Linux, a equipe deve usar `mysqldump`/cliente MySQL e seu agendador com as mesmas opções e proteção de segredos.

## Evidência necessária para encerrar RNF18

URL HTTPS do domínio Senac, data/hora da publicação, versão implantada, resultado dos CA e verificação de backup. Nada disso foi inventado ou substituído por uma URL fictícia.
