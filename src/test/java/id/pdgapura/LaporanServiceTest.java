package id.pdgapura;

import java.time.LocalDate;

/** Tes ringan tanpa library eksternal; jalankan dengan java -ea. */
public class LaporanServiceTest {
    public static void main(String[] args) {
        LocalDate tanggal = LocalDate.of(2026, 10, 27);
        InMemoryLaporanRepository repo = new InMemoryLaporanRepository();
        Barang barang = new Barang(1, "Cat", 30000, 40000);
        TransaksiPenjualan transaksi = new TransaksiPenjualan(1, tanggal, 2);
        transaksi.tambahDetail(new DetailTransaksi(1, 1, barang, 3));
        transaksi.simpanTransaksi();
        repo.tambahTransaksi(transaksi);

        Pemilik pemilik = new Pemilik(1, "Owner", "owner", "hash");
        pemilik.login("owner", "hash");
        LaporanPenjualan laporan = new LaporanService(repo).hasilkanLaporanHarian(pemilik, tanggal);
        assert laporan.getJumlahTransaksi() == 1 : "Jumlah transaksi seharusnya 1";
        assert Math.abs(laporan.getTotalPenjualan() - 120000) < 0.001 : "Total seharusnya 120000";
        assert "TERSIMPAN".equals(laporan.getStatus()) : "Status laporan seharusnya TERSIMPAN";
        assert repo.getSemuaLogAkses().size() == 1 : "Satu log akses seharusnya tersimpan";

        Kasir kasir = new Kasir(2, "Kasir", "kasir", "hash2");
        kasir.login("kasir", "hash2");
        boolean aksesDitolak = false;
        try { new LaporanService(repo).hasilkanLaporanHarian(kasir, tanggal); }
        catch (AksesDitolakException expected) { aksesDitolak = true; }
        assert aksesDitolak : "Kasir tanpa izin harus ditolak";

        boolean dataKosong = false;
        try { new LaporanService(repo).hasilkanLaporanHarian(pemilik, tanggal.plusDays(1)); }
        catch (DataLaporanTidakDitemukanException expected) { dataKosong = true; }
        assert dataKosong : "Tanggal tanpa transaksi harus menghasilkan exception";

        System.out.println("SEMUA TES BERHASIL (4 assertions). ");
    }
}
