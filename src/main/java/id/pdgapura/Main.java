package id.pdgapura;

import java.time.LocalDate;
import java.text.NumberFormat;
import java.util.Locale;

/** Demo console untuk menjalankan sequence laporan harian penjualan. */
public class Main {
    public static void main(String[] args) {
        LocalDate tanggalLaporan = LocalDate.of(2026, 10, 27);
        InMemoryLaporanRepository repository = new InMemoryLaporanRepository();
        isiContohTransaksi(repository, tanggalLaporan);

        Pemilik pemilik = new Pemilik(1, "Pemilik PD Gapura", "pemilik", "hash-demo");
        pemilik.login("pemilik", "hash-demo");
        LaporanService service = new LaporanService(repository);

        System.out.println("=== SISTEM INFORMASI PD GAPURA ===");
        System.out.println("Sequence: Pilih Laporan Harian -> requestLaporan");
        try {
            LaporanPenjualan laporan = pemilik.hasilkanLaporanHarian(service, tanggalLaporan);
            tampilkanLaporan(laporan);
            System.out.println("Log akses tercatat: " + repository.getSemuaLogAkses().size());
        } catch (AksesDitolakException | DataLaporanTidakDitemukanException | IllegalArgumentException e) {
            System.out.println("Gagal menghasilkan laporan: " + e.getMessage());
        }

        System.out.println("\n=== UJI AKSES KASIR TANPA IZIN ===");
        Kasir kasir = new Kasir(2, "Kasir PD Gapura", "kasir", "hash-kasir");
        kasir.login("kasir", "hash-kasir");
        try {
            service.hasilkanLaporanHarian(kasir, tanggalLaporan);
        } catch (AksesDitolakException e) {
            System.out.println("Hasil pengujian: " + e.getMessage());
        }

        System.out.println("\n=== UJI PERIODE TANPA TRANSAKSI ===");
        try {
            service.hasilkanLaporanHarian(pemilik, tanggalLaporan.plusDays(1));
        } catch (DataLaporanTidakDitemukanException e) {
            System.out.println("Hasil pengujian: " + e.getMessage());
        }
    }

    private static void isiContohTransaksi(InMemoryLaporanRepository repository, LocalDate tanggal) {
        Barang semen = new Barang(101, "Semen 50 kg", 50000, 65000);
        Barang paku = new Barang(102, "Paku 1 kg", 15000, 25000);

        TransaksiPenjualan t1 = new TransaksiPenjualan(1001, tanggal, 2);
        t1.tambahDetail(new DetailTransaksi(1, 1001, semen, 2));
        t1.simpanTransaksi();
        repository.tambahTransaksi(t1);

        TransaksiPenjualan t2 = new TransaksiPenjualan(1002, tanggal, 2);
        t2.tambahDetail(new DetailTransaksi(2, 1002, paku, 1));
        t2.simpanTransaksi();
        repository.tambahTransaksi(t2);
    }

    private static void tampilkanLaporan(LaporanPenjualan laporan) {
        NumberFormat rupiah = NumberFormat.getCurrencyInstance(Locale.forLanguageTag("id-ID"));
        System.out.println("Tanggal laporan  : " + laporan.getTanggal());
        System.out.println("Jumlah transaksi : " + laporan.getJumlahTransaksi());
        System.out.println("Total penjualan  : " + rupiah.format(laporan.getTotalPenjualan()));
        System.out.println("Status laporan   : " + laporan.getStatus());
    }
}
