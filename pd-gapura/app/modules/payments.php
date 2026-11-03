<?php
declare(strict_types=1);

/**
 * Handler endpoint module PD GAPURA.
 * Business logic dipisahkan per fitur; kontrak request/response tetap sama.
 * Variabel tersedia melalui parameter closure: $pdo, $route, $method, $input.
 */
return static function (PDO $pdo, string $route, string $method, array $input): bool {
    switch ($route) {
        case 'payments': {
            $user=requireAuth();
            if($method==='GET'){
                $rows=$pdo->query("SELECT p.*,t.total,t.status_transaksi FROM pembayaran p JOIN transaksi t ON t.id_transaksi=p.id_transaksi ORDER BY p.id_pembayaran DESC LIMIT 100")->fetchAll();
                respond(200,['success'=>true,'data'=>$rows]);
            }
            if($method!=='POST')respond(405,['success'=>false,'message'=>'Gunakan GET atau POST.']);
            $saleId=(int)($input['id_transaksi']??0);$amount=$input['jumlah_bayar']??null;$payMethod=trim((string)($input['metode_pembayaran']??''));
            if($saleId<1||!money($amount)||!in_array($payMethod,['Tunai','Transfer','QRIS'],true))respond(422,['success'=>false,'message'=>'Transaksi, jumlah pembayaran, dan metode pembayaran wajib valid.']);
            $pdo->beginTransaction();$stmt=$pdo->prepare("SELECT * FROM transaksi WHERE id_transaksi=? FOR UPDATE");$stmt->execute([$saleId]);$sale=$stmt->fetch();
            if(!$sale)throw new DomainException('Transaksi tidak ditemukan.');
            if($sale['status_transaksi']==='Lunas')throw new DomainException('Transaksi ini sudah lunas.');
            $amount=(float)$amount;if(abs($amount-(float)$sale['total'])>0.009)throw new DomainException('Pembayaran harus sama dengan total transaksi.');
            $stmt=$pdo->prepare("INSERT INTO pembayaran (id_transaksi,jumlah_bayar,metode_pembayaran,status_pembayaran) VALUES (?,?,?,'Lunas')");$stmt->execute([$saleId,$amount,$payMethod]);
            $pdo->prepare("UPDATE transaksi SET status_transaksi='Lunas' WHERE id_transaksi=?")->execute([$saleId]);$pdo->commit();
            logActivity($pdo,(int)$user['id_user'],'PEMBAYARAN',"Melunasi transaksi #{$saleId}.");
            respond(201,['success'=>true,'message'=>'Pembayaran berhasil dicatat.']);
        }
    }
    return false;
};
