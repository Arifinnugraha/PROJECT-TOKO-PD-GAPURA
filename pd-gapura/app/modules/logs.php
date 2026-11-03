<?php
declare(strict_types=1);

/**
 * Handler endpoint module PD GAPURA.
 * Business logic dipisahkan per fitur; kontrak request/response tetap sama.
 * Variabel tersedia melalui parameter closure: $pdo, $route, $method, $input.
 */
return static function (PDO $pdo, string $route, string $method, array $input): bool {
    switch ($route) {
        case 'logs': {
            requireRole(['pemilik']);
            if($method!=='GET')respond(405,['success'=>false,'message'=>'Gunakan method GET.']);
            $rows=$pdo->query('SELECT l.*,u.nama,u.username FROM aktivitas_log l JOIN users u ON u.id_user=l.id_user ORDER BY l.waktu DESC LIMIT 150')->fetchAll();
            respond(200,['success'=>true,'data'=>$rows]);
        }
    }
    return false;
};
