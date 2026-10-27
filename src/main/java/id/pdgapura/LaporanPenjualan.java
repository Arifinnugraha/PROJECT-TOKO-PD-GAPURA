package id.pdgapura;

import java.time.LocalDate;
import java.util.ArrayList;
import java.util.Collections;
import java.util.List;

/** Hasil agregasi penjualan pada satu tanggal. */
public class LaporanPenjualan {
    private final int idLaporan;
    private final LocalDate tanggal;
    protected String status;
    private int jumlahTransaksi;
    private double totalPenjualan;
    private final List<TransaksiPenjualan> transaksi = new ArrayList<>();

    public LaporanPenjualan(int idLaporan, LocalDate tanggal) {
        if (idLaporan <= 0 || tanggal == null) throw new IllegalArgumentException("ID laporan dan tanggal wajib valid.");
        this.idLaporan = idLaporan; this.tanggal = tanggal; this.status = "DRAFT";
    }
    public void agregasiDataPenjualan(List<TransaksiPenjualan> dataTransaksi) {
        if (dataTransaksi == null) throw new IllegalArgumentException("Data transaksi tidak boleh null.");
        transaksi.clear(); totalPenjualan = 0; jumlahTransaksi = 0;
        for (TransaksiPenjualan t : dataTransaksi) {
            if (t == null) throw new IllegalArgumentException("Daftar mengandung transaksi null.");
            if (tanggal.equals(t.getTanggal())) {
                transaksi.add(t); totalPenjualan += t.getTotalBayar(); jumlahTransaksi++;
            }
        }
        if (jumlahTransaksi == 0) throw new DataLaporanTidakDitemukanException("Tidak ada data untuk periode ini.");
        status = "TERAGREGASI";
    }
    public void simpanLaporan(LaporanRepository repository) {
        if (repository == null) throw new IllegalArgumentException("Repository laporan belum tersedia.");
        if (jumlahTransaksi == 0) throw new IllegalStateException("Laporan harus diagregasi sebelum disimpan.");
        repository.simpanLaporan(this);
        status = "TERSIMPAN";
    }
    public void tandaiProvisional() { status = "PROVISIONAL"; }
    public int getIdLaporan() { return idLaporan; }
    public LocalDate getTanggal() { return tanggal; }
    public String getStatus() { return status; }
    public int getJumlahTransaksi() { return jumlahTransaksi; }
    public double getTotalPenjualan() { return totalPenjualan; }
    public List<TransaksiPenjualan> getTransaksi() { return Collections.unmodifiableList(transaksi); }
}
