<?php
declare(strict_types=1);

/**
 * Handler endpoint module PD GAPURA.
 * Business logic dipisahkan per fitur; kontrak request/response tetap sama.
 * Variabel tersedia melalui parameter closure: $pdo, $route, $method, $input.
 */
return static function (PDO $pdo, string $route, string $method, array $input): bool {
    switch ($route) {
        case 'dashboard': {
            if ($method !== 'GET') respond(405, ['success' => false, 'message' => 'Gunakan method GET.']);
            $user = requireAuth();
            $todaySales = (float)$pdo->query("SELECT COALESCE(SUM(total),0) FROM transaksi WHERE tanggal_transaksi::date=CURRENT_DATE AND status_transaksi='Lunas'")->fetchColumn();
            $todayCount = (int)$pdo->query("SELECT COUNT(*) FROM transaksi WHERE tanggal_transaksi::date=CURRENT_DATE")->fetchColumn();
            $productsCount = (int)$pdo->query("SELECT COUNT(*) FROM barang WHERE status='aktif'")->fetchColumn();
            $lowStock = (int)$pdo->query("SELECT COUNT(*) FROM barang WHERE status='aktif' AND stok <= stok_minimum")->fetchColumn();
            $stockRows = $pdo->query("SELECT id_barang,kode_barang,nama_barang,stok,stok_minimum,satuan FROM barang WHERE status='aktif' AND stok <= stok_minimum ORDER BY stok ASC LIMIT 8")->fetchAll();
            $recent = $pdo->query("SELECT t.id_transaksi,t.tanggal_transaksi,t.total,t.status_transaksi,u.nama AS kasir FROM transaksi t JOIN users u ON u.id_user=t.id_user ORDER BY t.id_transaksi DESC LIMIT 6")->fetchAll();
            respond(200, ['success' => true, 'data' => compact('todaySales','todayCount','productsCount','lowStock','stockRows','recent')]);
        }
    }
    return false;
};
