<?php
declare(strict_types=1);

/**
 * Handler endpoint module PD GAPURA.
 * Business logic dipisahkan per fitur; kontrak request/response tetap sama.
 * Variabel tersedia melalui parameter closure: $pdo, $route, $method, $input.
 */
return static function (PDO $pdo, string $route, string $method, array $input): bool {
    switch ($route) {
        case 'reports': {
            $user=requireAuth();
            if (!hasReportAccess($pdo, $user)) respond(403,['success'=>false,'message'=>'Laporan hanya dapat diakses pemilik atau kasir yang permintaannya telah disetujui.']);
            $start=$_GET['start']??date('Y-m-01');$end=$_GET['end']??date('Y-m-d');
            if(!preg_match('/^\d{4}-\d{2}-\d{2}$/',(string)$start)||!preg_match('/^\d{4}-\d{2}-\d{2}$/',(string)$end)||$start>$end)respond(422,['success'=>false,'message'=>'Periode laporan tidak valid. Gunakan YYYY-MM-DD dan tanggal awal tidak boleh melewati tanggal akhir.']);
            $sales=$pdo->prepare("SELECT t.id_transaksi,t.tanggal_transaksi,u.nama AS kasir,t.total,t.status_transaksi FROM transaksi t JOIN users u ON u.id_user=t.id_user WHERE DATE(t.tanggal_transaksi) BETWEEN ? AND ? ORDER BY t.tanggal_transaksi DESC");$sales->execute([$start,$end]);
            $receipts=$pdo->prepare("SELECT bm.id_barang_masuk,bm.tanggal_masuk,s.nama_supplier,SUM(d.jumlah*d.harga_beli) AS total FROM barang_masuk bm JOIN supplier s ON s.id_supplier=bm.id_supplier JOIN detail_barang_masuk d ON d.id_barang_masuk=bm.id_barang_masuk WHERE bm.tanggal_masuk BETWEEN ? AND ? GROUP BY bm.id_barang_masuk,bm.tanggal_masuk,s.nama_supplier ORDER BY bm.tanggal_masuk DESC");$receipts->execute([$start,$end]);
            $stock=$pdo->query("SELECT kode_barang,nama_barang,kategori,stok,satuan,stok_minimum,CASE WHEN stok<=stok_minimum THEN 'Menipis' ELSE 'Aman' END AS status_stok FROM barang WHERE status='aktif' ORDER BY nama_barang")->fetchAll();
            $sum=$pdo->prepare("SELECT COALESCE(SUM(total),0) FROM transaksi WHERE DATE(tanggal_transaksi) BETWEEN ? AND ? AND status_transaksi='Lunas'");$sum->execute([$start,$end]);
            respond(200,['success'=>true,'data'=>['sales'=>$sales->fetchAll(),'receipts'=>$receipts->fetchAll(),'stock'=>$stock,'total_sales'=>(float)$sum->fetchColumn(),'start'=>$start,'end'=>$end]]);
        }
    }
    return false;
};
