package id.pdgapura;

import java.time.LocalDate;
import java.util.List;

/** Interface agar business logic terpisah dari detail penyimpanan data. */
public interface LaporanRepository {
    List<TransaksiPenjualan> cariTransaksiByTanggal(LocalDate tanggal);
    void simpanLaporan(LaporanPenjualan laporan);
    void simpanLogAkses(LogAkses logAkses);
}
