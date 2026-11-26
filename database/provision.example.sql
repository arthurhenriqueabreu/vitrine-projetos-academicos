-- Executar como DBA no MySQL 8; substituir os valores antes de executar.
-- O nome corresponde ao .env.example. Não usar esta senha literal.
CREATE DATABASE vitrine_academica CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'vitrine_app'@'127.0.0.1' IDENTIFIED BY 'SUBSTITUA_POR_SENHA_ALEATORIA';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, REFERENCES, INDEX, ALTER ON vitrine_academica.* TO 'vitrine_app'@'127.0.0.1';
-- Depois do setup, executar:
-- REVOKE CREATE, REFERENCES, INDEX, ALTER ON vitrine_academica.* FROM 'vitrine_app'@'127.0.0.1';
-- O usuário de backup precisa de permissões próprias; não reutilizar root no .env.
