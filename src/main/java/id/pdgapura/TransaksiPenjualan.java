package id.pdgapura;

import java.time.LocalDate;
import java.util.ArrayList;
import java.util.Collections;
import java.util.List;

/** Header transaksi penjualan. */
public class TransaksiPenjualan {
    private final int idTransaksi;
    private final LocalDate tanggal;
    private final int idUser;
    protected double totalBayar;
    private final List<DetailTransaksi> detailTransaksi = new ArrayList<>();
    private boolean tersimpan;
    private boolean dibayar;

    public TransaksiPenjualan(int idTransaksi, LocalDate tanggal, int idUser) {
        if (idTransaksi <= 0 || tanggal == null || idUser <= 0) throw new IllegalArgumentException("Data transaksi tidak valid.");
        this.idTransaksi = idTransaksi; this.tanggal = tanggal; this.idUser = idUser;
    }
    public void tambahDetail(DetailTransaksi detail) {
        if (detail == null || detail.getIdTransaksi() != idTransaksi) throw new IllegalArgumentException("Detail tidak sesuai dengan transaksi.");
        detailTransaksi.add(detail); hitungTotal();
    }
    public double hitungTotal() {
        totalBayar = detailTransaksi.stream().mapToDouble(DetailTransaksi::hitungSubtotal).sum();
        return totalBayar;
    }
    public void prosesPembayaran(double jumlahBayar) {
        hitungTotal();
        if (detailTransaksi.isEmpty()) throw new IllegalStateException("Transaksi belum memiliki barang.");
        if (!Double.isFinite(jumlahBayar) || jumlahBayar < totalBayar) throw new IllegalArgumentException("Pembayaran kurang dari total transaksi atau tidak valid.");
        dibayar = true;
    }
    public void simpanTransaksi() {
        if (detailTransaksi.isEmpty()) throw new IllegalStateException("Transaksi tanpa detail tidak dapat disimpan.");
        hitungTotal(); tersimpan = true;
    }
    public int getIdTransaksi() { return idTransaksi; }
    public LocalDate getTanggal() { return tanggal; }
    public int getIdUser() { return idUser; }
    public double getTotalBayar() { return totalBayar; }
    public List<DetailTransaksi> getDetailTransaksi() { return Collections.unmodifiableList(detailTransaksi); }
    public boolean isTersimpan() { return tersimpan; }
    public boolean isDibayar() { return dibayar; }
}
