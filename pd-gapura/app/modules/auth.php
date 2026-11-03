<?php
declare(strict_types=1);

/**
 * Handler endpoint module PD GAPURA.
 * Business logic dipisahkan per fitur; kontrak request/response tetap sama.
 * Variabel tersedia melalui parameter closure: $pdo, $route, $method, $input.
 */
return static function (PDO $pdo, string $route, string $method, array $input): bool {
    switch ($route) {
        case 'auth/login': {
            if ($method !== 'POST') respond(405, ['success' => false, 'message' => 'Gunakan method POST.']);
            $username = trim((string)($input['username'] ?? ''));
            $password = (string)($input['password'] ?? '');
            if ($username === '' || $password === '') respond(422, ['success' => false, 'message' => 'Username dan password wajib diisi.']);
            $stmt = $pdo->prepare("SELECT id_user, nama, username, password, role, status FROM users WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            if (!$user || !password_verify($password, $user['password'])) respond(401, ['success' => false, 'message' => 'Login gagal. Periksa username dan password.']);
            if ($user['status'] !== 'aktif') respond(403, ['success' => false, 'message' => 'Akun tidak aktif. Hubungi pemilik toko.']);
            session_regenerate_id(true);
            unset($user['password']);
            $user['can_view_reports'] = hasReportAccess($pdo, $user);
            $_SESSION['user'] = $user;
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            logActivity($pdo, (int)$user['id_user'], 'LOGIN', 'Pengguna berhasil login.');
            respond(200, ['success' => true, 'message' => 'Login berhasil.', 'data' => ['user' => $user, 'csrf_token' => $_SESSION['csrf_token']]]);
        }
        case 'auth/me': {
            if ($method !== 'GET') respond(405, ['success' => false, 'message' => 'Gunakan method GET.']);
            $user = currentUser();
            if ($user) $user['can_view_reports'] = hasReportAccess($pdo, $user);
            respond(200, ['success' => true, 'data' => ['user' => $user, 'csrf_token' => $_SESSION['csrf_token'] ?? null]]);
        }
        case 'auth/logout': {
            if ($method !== 'POST') respond(405, ['success' => false, 'message' => 'Gunakan method POST.']);
            $user = requireAuth();
            logActivity($pdo, (int)$user['id_user'], 'LOGOUT', 'Pengguna keluar dari sistem.');
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
            }
            session_destroy();
            respond(200, ['success' => true, 'message' => 'Anda berhasil logout.']);
        }
    }
    return false;
};
