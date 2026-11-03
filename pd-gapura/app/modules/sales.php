<?php
declare(strict_types=1);

/**
 * Handler endpoint module PD GAPURA.
 * Business logic dipisahkan per fitur; kontrak request/response tetap sama.
 * Variabel tersedia melalui parameter closure: $pdo, $route, $method, $input.
 */
return static function (PDO $pdo, string $route, string $method, array $input): bool {
    switch ($route) {
        case 'sales': {
            $user=requireAuth();
            if($method==='GET'){
                $detailId=(int)($_GET['id']??0);
                if($detailId>0){
                    $header=$pdo->prepare("SELECT t.*,u.nama AS kasir FROM transaksi t JOIN users u ON u.id_user=t.id_user WHERE t.id_transaksi=?");$header->execute([$detailId]);$sale=$header->fetch();
                    if(!$sale)respond(404,['success'=>false,'message'=>'Transaksi tidak ditemukan.']);
                    $details=$pdo->prepare("SELECT d.*,b.kode_barang,b.nama_barang,b.satuan FROM detail_transaksi d JOIN barang b ON b.id_barang=d.id_barang WHERE d.id_transaksi=?");$details->execute([$detailId]);
                    $payments=$pdo->prepare('SELECT * FROM pembayaran WHERE id_transaksi=? ORDER BY id_pembayaran DESC');$payments->execute([$detailId]);
                    respond(200,['success'=>true,'data'=>['sale'=>$sale,'items'=>$details->fetchAll(),'payments'=>$payments->fetchAll()]]);
                }
                $rows=$pdo->query("SELECT t.id_transaksi,t.tanggal_transaksi,t.total,t.status_transaksi,u.nama AS kasir,COALESCE(p.metode_pembayaran,'-') AS metode_pembayaran,COALESCE(p.jumlah_bayar,0) AS jumlah_bayar,COALESCE(p.status_pembayaran,'Belum Lunas') AS status_pembayaran,(SELECT COUNT(*) FROM detail_transaksi d WHERE d.id_transaksi=t.id_transaksi) AS jumlah_item FROM transaksi t JOIN users u ON u.id_user=t.id_user LEFT JOIN pembayaran p ON p.id_pembayaran=(SELECT MAX(p2.id_pembayaran) FROM pembayaran p2 WHERE p2.id_transaksi=t.id_transaksi) ORDER BY t.id_transaksi DESC LIMIT 100")->fetchAll();
                respond(200,['success'=>true,'data'=>$rows]);
            }
            if($method!=='POST')respond(405,['success'=>false,'message'=>'Gunakan GET atau POST.']);
            $items=$input['items']??[];$methodPay=trim((string)($input['metode_pembayaran']??''));$paid=$input['jumlah_bayar']??0;
            if(!is_array($items)||count($items)<1)respond(422,['success'=>false,'message'=>'Keranjang transaksi masih kosong.']);
            if(!in_array($methodPay,['Tunai','Transfer','QRIS','Belum Dibayar'],true))respond(422,['success'=>false,'message'=>'Pilih metode pembayaran yang tersedia.']);
            if(!money($paid))respond(422,['success'=>false,'message'=>'Jumlah pembayaran harus berupa angka minimal 0.']);
            $normalized=[];
            foreach($items as $item){
                if(!positiveInt($item['id_barang']??null)||!positiveInt($item['jumlah']??null))respond(422,['success'=>false,'message'=>'Produk wajib dipilih dan jumlah harus bilangan bulat minimal 1.']);
                $pid=(int)$item['id_barang'];
                if(!isset($normalized[$pid]))$normalized[$pid]=['id_barang'=>$pid,'jumlah'=>0];
                $normalized[$pid]['jumlah']+=(int)$item['jumlah'];
            }
            $items=array_values($normalized);
            $pdo->beginTransaction();
            $total=0.0;$validated=[];
            foreach($items as $item){
                $pid=(int)$item['id_barang'];$qty=(int)$item['jumlah'];
                $stmt=$pdo->prepare("SELECT id_barang,nama_barang,harga_satuan,stok FROM barang WHERE id_barang=? AND status='aktif' FOR UPDATE");$stmt->execute([$pid]);$product=$stmt->fetch();
                if(!$product)throw new DomainException('Salah satu barang tidak ditemukan atau sudah nonaktif.');
                if((int)$product['stok']<$qty)throw new DomainException("Stok {$product['nama_barang']} tidak mencukupi. Stok tersedia: {$product['stok']}.");
                $subtotal=(float)$product['harga_satuan']*$qty;$total+=$subtotal;
                $validated[]=['id_barang'=>$pid,'jumlah'=>$qty,'harga_satuan'=>(float)$product['harga_satuan'],'subtotal'=>$subtotal];
            }
            $paid=(float)$paid;
            if($paid>0 && abs($paid-$total)>0.009)throw new DomainException('Jumlah pembayaran harus sama dengan total transaksi. Pilih Belum Dibayar untuk mencatat transaksi yang belum dibayar.');
            if($paid===0.0 && $methodPay!=='Belum Dibayar')throw new DomainException('Gunakan metode Belum Dibayar jika belum ada pembayaran.');
            if($paid>0 && $methodPay==='Belum Dibayar')throw new DomainException('Pilih Tunai, Transfer, atau QRIS untuk pembayaran lunas.');
            $status=$paid>0?'Lunas':'Belum Lunas';
            $stmt=$pdo->prepare('INSERT INTO transaksi (tanggal_transaksi,id_user,total,status_transaksi) VALUES (NOW(),?,?,?) RETURNING id_transaksi');$stmt->execute([$user['id_user'],$total,$status]);$saleId=(int)$stmt->fetchColumn();
            $detail=$pdo->prepare('INSERT INTO detail_transaksi (id_transaksi,id_barang,jumlah,harga_satuan,subtotal) VALUES (?,?,?,?,?)');$stock=$pdo->prepare('UPDATE barang SET stok=stok-? WHERE id_barang=?');
            foreach($validated as $item){$detail->execute([$saleId,$item['id_barang'],$item['jumlah'],$item['harga_satuan'],$item['subtotal']]);$stock->execute([$item['jumlah'],$item['id_barang']]);}
            $payment=$pdo->prepare('INSERT INTO pembayaran (id_transaksi,jumlah_bayar,metode_pembayaran,status_pembayaran) VALUES (?,?,?,?)');$payment->execute([$saleId,$paid,$methodPay,$status]);
            $pdo->commit();
            logActivity($pdo,(int)$user['id_user'],'TRANSAKSI PENJUALAN',"Menyimpan transaksi #{$saleId} dengan total {$total}.");
            respond(201,['success'=>true,'message'=>'Transaksi berhasil disimpan dan stok telah diperbarui.','data'=>['id_transaksi'=>$saleId,'total'=>$total,'status_transaksi'=>$status]]);
        }
    }
    return false;
};
