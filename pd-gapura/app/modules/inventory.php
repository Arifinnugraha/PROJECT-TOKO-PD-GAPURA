<?php
declare(strict_types=1);

/**
 * Handler endpoint module PD GAPURA.
 * Business logic dipisahkan per fitur; kontrak request/response tetap sama.
 * Variabel tersedia melalui parameter closure: $pdo, $route, $method, $input.
 */
return static function (PDO $pdo, string $route, string $method, array $input): bool {
    switch ($route) {
        case 'products': {
            $user = requireAuth();
            if ($method === 'GET') {
                $search = trim((string)($_GET['q'] ?? ''));
                $sql = "SELECT * FROM barang WHERE status='aktif'";
                $params = [];
                if ($search !== '') { $sql .= " AND (kode_barang ILIKE ? OR nama_barang ILIKE ? OR kategori ILIKE ?)"; $term = "%{$search}%"; $params = [$term,$term,$term]; }
                $sql .= ' ORDER BY nama_barang ASC';
                $stmt = $pdo->prepare($sql); $stmt->execute($params);
                respond(200, ['success' => true, 'data' => $stmt->fetchAll()]);
            }
            if ($method === 'POST') {
                $user = requireRole(['pemilik']);
                $code = strtoupper(trim((string)($input['kode_barang'] ?? '')));
                $name = trim((string)($input['nama_barang'] ?? ''));
                $category = trim((string)($input['kategori'] ?? ''));
                $unit = trim((string)($input['satuan'] ?? 'pcs'));
                $cost = $input['harga_beli'] ?? 0; $price = $input['harga_satuan'] ?? null;
                $stock = $input['stok'] ?? 0; $minStock = $input['stok_minimum'] ?? 0;
                if ($code === '' || $name === '' || $category === '' || $price === null || $unit === '') respond(422, ['success' => false, 'message' => 'Kode, nama, kategori, satuan, dan harga jual wajib diisi.']);
                if (!money($cost) || !money($price) || !nonNegativeInt($stock) || !nonNegativeInt($minStock)) respond(422, ['success' => false, 'message' => 'Harga harus berupa angka minimal 0 dan stok harus bilangan bulat minimal 0.']);
                $check = $pdo->prepare('SELECT COUNT(*) FROM barang WHERE kode_barang=?'); $check->execute([$code]);
                if ((int)$check->fetchColumn() > 0) respond(422, ['success' => false, 'message' => 'Kode barang sudah digunakan.']);
                $stmt = $pdo->prepare('INSERT INTO barang (kode_barang,nama_barang,kategori,satuan,harga_beli,harga_satuan,stok,stok_minimum,lokasi_penyimpanan,keterangan) VALUES (?,?,?,?,?,?,?,?,?,?) RETURNING id_barang');
                $stmt->execute([$code,$name,$category,$unit,$cost,$price,$stock,$minStock,trim((string)($input['lokasi_penyimpanan'] ?? '')) ?: null,trim((string)($input['keterangan'] ?? '')) ?: null]);
                $newProductId = (int)$stmt->fetchColumn();
                logActivity($pdo, (int)$user['id_user'], 'TAMBAH BARANG', "Menambahkan barang {$code} - {$name}.");
                respond(201, ['success' => true, 'message' => 'Barang berhasil ditambahkan.', 'data' => ['id_barang' => $newProductId]]);
            }
            if ($method === 'PUT') {
                $user = requireRole(['pemilik']); $id = (int)($_GET['id'] ?? 0);
                if ($id < 1) respond(422, ['success' => false, 'message' => 'ID barang tidak valid.']);
                $code = strtoupper(trim((string)($input['kode_barang'] ?? ''))); $name = trim((string)($input['nama_barang'] ?? '')); $category = trim((string)($input['kategori'] ?? ''));
                $price = $input['harga_satuan'] ?? null; $cost = $input['harga_beli'] ?? 0; $stock = $input['stok'] ?? null; $minStock = $input['stok_minimum'] ?? 0;
                if ($code === '' || $name === '' || $category === '' || $price === null || $stock === null) respond(422, ['success' => false, 'message' => 'Lengkapi semua kolom wajib.']);
                if (!money($cost) || !money($price) || !nonNegativeInt($stock) || !nonNegativeInt($minStock)) respond(422, ['success' => false, 'message' => 'Harga atau stok tidak valid.']);
                $check=$pdo->prepare('SELECT COUNT(*) FROM barang WHERE kode_barang=? AND id_barang<>?');$check->execute([$code,$id]);
                if ((int)$check->fetchColumn()>0) respond(422,['success'=>false,'message'=>'Kode barang sudah digunakan barang lain.']);
                $stmt=$pdo->prepare('UPDATE barang SET kode_barang=?,nama_barang=?,kategori=?,satuan=?,harga_beli=?,harga_satuan=?,stok=?,stok_minimum=?,lokasi_penyimpanan=?,keterangan=? WHERE id_barang=? AND status=\'aktif\'');
                $stmt->execute([$code,$name,$category,trim((string)($input['satuan']??'pcs')),$cost,$price,$stock,$minStock,trim((string)($input['lokasi_penyimpanan']??''))?:null,trim((string)($input['keterangan']??''))?:null,$id]);
                logActivity($pdo,(int)$user['id_user'],'UBAH BARANG',"Memperbarui barang {$code}.");
                respond(200,['success'=>true,'message'=>'Data barang berhasil diperbarui.']);
            }
            if ($method === 'DELETE') {
                $user = requireRole(['pemilik']); $id=(int)($_GET['id']??0);
                if ($id<1) respond(422,['success'=>false,'message'=>'ID barang tidak valid.']);
                $stmt=$pdo->prepare("UPDATE barang SET status='nonaktif' WHERE id_barang=?");$stmt->execute([$id]);
                if (!$stmt->rowCount()) respond(404,['success'=>false,'message'=>'Barang tidak ditemukan.']);
                logActivity($pdo,(int)$user['id_user'],'NONAKTIFKAN BARANG',"Menonaktifkan barang ID {$id}.");
                respond(200,['success'=>true,'message'=>'Barang berhasil dinonaktifkan.']);
            }
            respond(405,['success'=>false,'message'=>'Method tidak didukung.']);
        }
    }
    return false;
};
