<?php
final class Auth {
    public static function user(): ?array {
        if (empty($_SESSION['uid'])) return null;
        if (time() - ($_SESSION['last_activity'] ?? 0) > 1800) { unset($_SESSION['uid']); return null; }
        $_SESSION['last_activity'] = time();
        return Database::one('SELECT id,nome,email,perfil FROM usuarios WHERE id=? AND ativo=1', [$_SESSION['uid']]);
    }
    public static function requireUser(bool $admin = false): array {
        $u = self::user(); if (!$u) redirect('login');
        if ($admin && $u['perfil'] !== 'administrador') throw new HttpError(403, 'Ação exclusiva de administradores.');
        return $u;
    }
    public static function login(string $email, string $password): bool {
        $ip = hash('sha256', $_SERVER['REMOTE_ADDR'] ?? 'cli');
        $attempts = Database::one('SELECT COUNT(*) AS n FROM login_tentativas WHERE ip_hash=? AND criado_em > DATE_SUB(NOW(), INTERVAL 15 MINUTE)', [$ip]);
        if ($attempts['n'] >= 10) throw new HttpError(429, 'Muitas tentativas. Tente novamente em 15 minutos.');
        $u = Database::one('SELECT * FROM usuarios WHERE email=? AND ativo=1', [$email]);
        $hash = $u['senha_hash'] ?? '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';
        if (!password_verify($password, $hash) || !$u) {
            Database::run('INSERT INTO login_tentativas (ip_hash) VALUES (?)', [$ip]); return false;
        }
        session_regenerate_id(true); $_SESSION['uid'] = $u['id']; $_SESSION['last_activity'] = time(); $_SESSION['csrf'] = bin2hex(random_bytes(32));
        Database::run('DELETE FROM login_tentativas WHERE criado_em < DATE_SUB(NOW(), INTERVAL 1 DAY)');
        return true;
    }
    public static function logout(): void { $_SESSION = []; session_regenerate_id(true); session_destroy(); }
}
