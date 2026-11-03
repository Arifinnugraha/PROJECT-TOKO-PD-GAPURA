<?php
declare(strict_types=1);

/**
 * Handler endpoint module PD GAPURA.
 * Business logic dipisahkan per fitur; kontrak request/response tetap sama.
 * Variabel tersedia melalui parameter closure: $pdo, $route, $method, $input.
 */
return static function (PDO $pdo, string $route, string $method, array $input): bool {
    switch ($route) {
        case 'suppliers': {
            $user = requireAuth();
            if ($method === 'GET') {
                $stmt=$pdo->query("SELECT * FROM supplier WHERE status='aktif' ORDER BY nama_supplier ASC");
                respond(200,['success'=>true,'data'=>$stmt->fetchAll()]);
            }
            $user = requireRole(['pemilik']);
            if ($method === 'POST') {
                $name=trim((string)($input['nama_supplier']??''));
                if ($name==='') respond(422,['success'=>false,'message'=>'Nama supplier wajib diisi.']);
                $stmt=$pdo->prepare('INSERT INTO supplier (nama_supplier,alamat,kontak) VALUES (?,?,?)');
                $stmt->execute([$name,trim((string)($input['alamat']??''))?:null,trim((string)($input['kontak']??''))?:null]);
                logActivity($pdo,(int)$user['id_user'],'TAMBAH SUPPLIER',"Menambahkan supplier {$name}.");
                respond(201,['success'=>true,'message'=>'Supplier berhasil ditambahkan.']);
            }
            if ($method === 'PUT') {
                $id=(int)($_GET['id']??0);$name=trim((string)($input['nama_supplier']??''));
                if($id<1||$name==='')respond(422,['success'=>false,'message'=>'ID dan nama supplier wajib valid.']);
                $stmt=$pdo->prepare('UPDATE supplier SET nama_supplier=?,alamat=?,kontak=? WHERE id_supplier=?');
                $stmt->execute([$name,trim((string)($input['alamat']??''))?:null,trim((string)($input['kontak']??''))?:null,$id]);
                logActivity($pdo,(int)$user['id_user'],'UBAH SUPPLIER',"Memperbarui supplier {$name}.");
                respond(200,['success'=>true,'message'=>'Supplier berhasil diperbarui.']);
            }
            if ($method === 'DELETE') {
                $id=(int)($_GET['id']??0); if($id<1)respond(422,['success'=>false,'message'=>'ID supplier tidak valid.']);
                $stmt=$pdo->prepare("UPDATE supplier SET status='nonaktif' WHERE id_supplier=?");$stmt->execute([$id]);
                logActivity($pdo,(int)$user['id_user'],'NONAKTIFKAN SUPPLIER',"Menonaktifkan supplier ID {$id}.");
                respond(200,['success'=>true,'message'=>'Supplier berhasil dinonaktifkan.']);
            }
            respond(405,['success'=>false,'message'=>'Method tidak didukung.']);
        }
    }
    return false;
};
