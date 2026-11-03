<?php
declare(strict_types=1);

/**
 * Handler endpoint module PD GAPURA.
 * Business logic dipisahkan per fitur; kontrak request/response tetap sama.
 * Variabel tersedia melalui parameter closure: $pdo, $route, $method, $input.
 */
return static function (PDO $pdo, string $route, string $method, array $input): bool {
    switch ($route) {
        case 'access-requests': {
            $user=requireAuth();
            if($method==='GET'){
                if($user['role']==='pemilik')$stmt=$pdo->query('SELECT r.*,u.nama AS pemohon,owner.nama AS pemeriksa FROM permintaan_akses_laporan r JOIN users u ON u.id_user=r.id_user LEFT JOIN users owner ON owner.id_user=r.diproses_oleh ORDER BY r.dibuat_pada DESC');
                else {$stmt=$pdo->prepare('SELECT r.*,u.nama AS pemohon FROM permintaan_akses_laporan r JOIN users u ON u.id_user=r.id_user WHERE r.id_user=? ORDER BY r.dibuat_pada DESC');$stmt->execute([$user['id_user']]);}
                respond(200,['success'=>true,'data'=>$stmt->fetchAll()]);
            }
            if($method==='POST'){
                $reason=trim((string)($input['alasan']??''));if(mb_strlen($reason)<5)respond(422,['success'=>false,'message'=>'Alasan permintaan minimal 5 karakter.']);
                $stmt=$pdo->prepare("INSERT INTO permintaan_akses_laporan (id_user,alasan) VALUES (?,?)");$stmt->execute([$user['id_user'],$reason]);
                logActivity($pdo,(int)$user['id_user'],'PERMINTAAN AKSES LAPORAN','Mengajukan permintaan akses laporan.');
                respond(201,['success'=>true,'message'=>'Permintaan akses laporan berhasil dikirim kepada pemilik.']);
            }
            if($method==='PATCH'){
                $owner=requireRole(['pemilik']);$id=(int)($_GET['id']??0);$decision=(string)($input['status']??'');
                if($id<1||!in_array($decision,['Disetujui','Ditolak'],true))respond(422,['success'=>false,'message'=>'Keputusan atau ID permintaan tidak valid.']);
                $stmt=$pdo->prepare("UPDATE permintaan_akses_laporan SET status=?,diproses_oleh=?,diproses_pada=NOW() WHERE id_permintaan=? AND status='Menunggu'");$stmt->execute([$decision,$owner['id_user'],$id]);
                if(!$stmt->rowCount())respond(404,['success'=>false,'message'=>'Permintaan tidak ditemukan atau sudah diproses.']);
                logActivity($pdo,(int)$owner['id_user'],'KEPUTUSAN AKSES LAPORAN',"Mengubah permintaan #{$id} menjadi {$decision}.");
                respond(200,['success'=>true,'message'=>"Permintaan berhasil {$decision}."]);
            }
            respond(405,['success'=>false,'message'=>'Method tidak didukung.']);
        }
    }
    return false;
};
