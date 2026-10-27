package id.pdgapura;

import java.time.LocalDate;
import java.time.LocalDateTime;
import java.util.List;
import java.util.concurrent.atomic.AtomicInteger;

/** Pengendali alur sequence diagram: request -> baca transaksi -> agregasi -> simpan -> log. */
public class LaporanService {
    private final LaporanRepository repository;
    private final AtomicInteger idLaporanBerikutnya = new AtomicInteger(1);
    private final AtomicInteger idLogBerikutnya = new AtomicInteger(1);

    public LaporanService(LaporanRepository repository) {
        if (repository == null) throw new IllegalArgumentException("Repository wajib tersedia.");
        this.repository = repository;
    }

    public LaporanPenjualan hasilkanLaporanHarian(User pengguna, LocalDate tanggal) {
        if (pengguna == null) throw new AksesDitolakException("Pengguna belum terautentikasi.");
        if (!pengguna.isSesiAktif()) throw new AksesDitolakException("Sesi pengguna tidak aktif. Silakan login terlebih dahulu.");
        if (!pengguna.bolehAksesLaporan()) throw new AksesDitolakException("Akses laporan ditolak untuk role " + pengguna.getRole() + ".");
        if (tanggal == null) throw new IllegalArgumentException("Tanggal laporan wajib dipilih.");

        List<TransaksiPenjualan> transaksiHarian = repository.cariTransaksiByTanggal(tanggal);
        if (transaksiHarian.isEmpty()) throw new DataLaporanTidakDitemukanException("Tidak ada data untuk periode ini.");

        LaporanPenjualan laporan = new LaporanPenjualan(idLaporanBerikutnya.getAndIncrement(), tanggal);
        laporan.agregasiDataPenjualan(transaksiHarian);
        laporan.simpanLaporan(repository);
        repository.simpanLogAkses(new LogAkses(idLogBerikutnya.getAndIncrement(), pengguna.getIdUser(),
                LocalDateTime.now(), "MENGHASILKAN_LAPORAN_HARIAN tanggal=" + tanggal));
        return laporan;
    }
}
