package id.pdgapura;

import java.time.LocalDate;
import java.util.ArrayList;
import java.util.Collections;
import java.util.List;
import java.util.stream.Collectors;

/** Repository untuk demo praktikum; belum terhubung ke DBMS. */
public class InMemoryLaporanRepository implements LaporanRepository {
    private final List<TransaksiPenjualan> transaksi = new ArrayList<>();
    private final List<LaporanPenjualan> laporan = new ArrayList<>();
    private final List<LogAkses> logAkses = new ArrayList<>();

    public void tambahTransaksi(TransaksiPenjualan transaksiPenjualan) {
        if (transaksiPenjualan == null) throw new IllegalArgumentException("Transaksi tidak boleh null.");
        transaksi.add(transaksiPenjualan);
    }
    @Override public List<TransaksiPenjualan> cariTransaksiByTanggal(LocalDate tanggal) {
        if (tanggal == null) throw new IllegalArgumentException("Tanggal wajib diisi.");
        return transaksi.stream().filter(t -> tanggal.equals(t.getTanggal())).collect(Collectors.toList());
    }
    @Override public void simpanLaporan(LaporanPenjualan laporanPenjualan) {
        if (laporanPenjualan == null) throw new IllegalArgumentException("Laporan tidak boleh null.");
        laporan.add(laporanPenjualan);
    }
    @Override public void simpanLogAkses(LogAkses log) {
        if (log == null) throw new IllegalArgumentException("Log akses tidak boleh null.");
        logAkses.add(log);
    }
    public List<TransaksiPenjualan> getSemuaTransaksi() { return Collections.unmodifiableList(transaksi); }
    public List<LaporanPenjualan> getSemuaLaporan() { return Collections.unmodifiableList(laporan); }
    public List<LogAkses> getSemuaLogAkses() { return Collections.unmodifiableList(logAkses); }
}
