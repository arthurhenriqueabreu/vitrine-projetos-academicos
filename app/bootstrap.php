<?php
declare(strict_types=1);
define('BASE', dirname(__DIR__));
spl_autoload_register(function ($class) {
    $file = BASE . '/app/' . str_replace('\\', '/', $class) . '.php';
    if (is_file($file)) require $file;
});
if (is_file(BASE . '/.env')) {
    foreach (file(BASE . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if ($line[0] === '#' || !str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        if (getenv(trim($key)) === false) putenv(trim($key) . '=' . trim(trim($value), '"'));
    }
}
date_default_timezone_set('America/Sao_Paulo');
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', BASE . '/storage/logs/php.log');
function env(string $key, string $default = ''): string { $v = getenv($key); return $v === false ? $default : $v; }
function e($v): string { return htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function url(string $path = ''): string { return rtrim(env('APP_URL', 'http://127.0.0.1:8080'), '/') . '/' . ltrim($path, '/'); }
function redirect(string $path): void { header('Location: ' . url($path), true, 303); exit; }
function flash(string $message, string $type = 'success'): void { $_SESSION['flash'] = [$message, $type]; }
function csrf(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
function csrfField(): string { return '<input type="hidden" name="csrf" value="' . e(csrf()) . '">'; }
function verifyCsrf(): void {
    if (!hash_equals(csrf(), (string) ($_POST['csrf'] ?? ''))) throw new HttpError(419, 'Formulário expirado. Recarregue a página.');
}
function view(string $name, array $data = []): void { global $path; extract($data, EXTR_SKIP); $renderingError=$name==='error'; ob_start(); require BASE . '/views/' . $name . '.php'; $content = ob_get_clean(); require BASE . '/views/layout.php'; }
class HttpError extends RuntimeException { public int $status; public function __construct(int $status, string $message) { parent::__construct($message); $this->status = $status; } }
if (PHP_SAPI !== 'cli') {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name('vitrine_session');
    session_set_cookie_params(['httponly' => true, 'secure' => env('SESSION_SECURE') === 'true', 'samesite' => 'Lax', 'path' => '/']);
    session_start();
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-Frame-Options: SAMEORIGIN');
    header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' data:; frame-src 'self' https://www.youtube-nocookie.com https://player.vimeo.com; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'self'");
    if (env('APP_ENV') === 'production') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') { http_response_code(400); exit('HTTPS é obrigatório em produção.'); }
    }
}
