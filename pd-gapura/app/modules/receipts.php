<?php
declare(strict_types=1);

/**
 * Handler endpoint module PD GAPURA.
 * Business logic dipisahkan per fitur; kontrak request/response tetap sama.
 * Variabel tersedia melalui parameter closure: $pdo, $route, $method, $input.
 */
return static function (PDO $pdo, string $route, string $method, array $input): bool {
    switch ($route) {
        case 'receipts': {
            $user=requireAuth();
            if ($method==='GET') {
                $rows=$pdo->query("SELECT bm.id_barang_masuk,bm.tanggal_masuk,s.nama_supplier,u.nama AS petugas,COUNT(dbm.id_detail_barang_masuk) AS jumlah_item,SUM(dbm.jumlah*dbm.harga_beli) AS total FROM barang_masuk bm JOIN supplier s ON s.id_supplier=bm.id_supplier JOIN users u ON u.id_user=bm.id_user JOIN detail_barang_masuk dbm ON dbm.id_barang_masuk=bm.id_barang_masuk GROUP BY bm.id_barang_masuk,bm.tanggal_masuk,s.nama_supplier,u.nama ORDER BY bm.id_barang_masuk DESC LIMIT 100")->fetchAll();
                respond(200,['success'=>true,'data'=>$rows]);
            }
            if ($method!=='POST') respond(405,['success'=>false,'message'=>'Gunakan GET atau POST.']);
            $supplierId=(int)($input['id_supplier']??0);$items=$input['items']??[];$date=trim((string)($input['tanggal_masuk']??date('Y-m-d')));
            if($supplierId<1||!is_array($items)||count($items)<1)respond(422,['success'=>false,'message'=>'Supplier dan minimal satu barang wajib dipilih.']);
            if(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)||!checkdate((int)substr($date,5,2),(int)substr($date,8,2),(int)substr($date,0,4)))respond(422,['success'=>false,'message'=>'Tanggal penerimaan tidak valid.']);
            $sup=$pdo->prepare("SELECT COUNT(*) FROM supplier WHERE id_supplier=? AND status='aktif'");$sup->execute([$supplierId]);
            if((int)$sup->fetchColumn()===0)respond(422,['success'=>false,'message'=>'Supplier tidak ditemukan atau sudah nonaktif.']);
            foreach($items as $item){
                if(!positiveInt($item['id_barang']??null)||!positiveInt($item['jumlah']??null)||!money($item['harga_beli']??null))respond(422,['success'=>false,'message'=>'Setiap detail barang harus memiliki produk, jumlah bilangan bulat minimal 1, dan harga beli minimal 0.']);
            }
            $pdo->beginTransaction();
            $stmt=$pdo->prepare('INSERT INTO barang_masuk (tanggal_masuk,id_supplier,id_user) VALUES (?,?,?) RETURNING id_barang_masuk');$stmt->execute([$date,$supplierId,$user['id_user']]);$receiptId=(int)$stmt->fetchColumn();
            $detail=$pdo->prepare('INSERT INTO detail_barang_masuk (id_barang_masuk,id_barang,jumlah,harga_beli) VALUES (?,?,?,?)');
            $update=$pdo->prepare('UPDATE barang SET stok=stok+?,harga_beli=? WHERE id_barang=? AND status=\'aktif\'');
            foreach($items as $item){$pid=(int)$item['id_barang'];$qty=(int)$item['jumlah'];$cost=(float)$item['harga_beli'];$detail->execute([$receiptId,$pid,$qty,$cost]);$update->execute([$qty,$cost,$pid]);if(!$update->rowCount())throw new DomainException('Salah satu barang tidak ditemukan atau nonaktif.');}
            $pdo->commit();
            logActivity($pdo,(int)$user['id_user'],'PENERIMAAN BARANG',"Mencatat barang masuk #{$receiptId}.");
            respond(201,['success'=>true,'message'=>'Penerimaan barang berhasil disimpan dan stok diperbarui.','data'=>['id_barang_masuk'=>$receiptId]]);
        }
    }
    return false;
};
