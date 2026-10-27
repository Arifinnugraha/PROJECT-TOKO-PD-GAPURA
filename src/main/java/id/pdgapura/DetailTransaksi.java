package id.pdgapura;

/** Rincian barang pada transaksi penjualan. */
public class DetailTransaksi {
    private final int idDetail;
    private final int idTransaksi;
    private final int idBarang;
    private int qty;
    protected double subtotal;
    private final Barang barang;

    public DetailTransaksi(int idDetail, int idTransaksi, Barang barang, int qty) {
        if (idDetail <= 0 || idTransaksi <= 0 || barang == null || qty <= 0) throw new IllegalArgumentException("Detail transaksi tidak valid.");
        this.idDetail = idDetail; this.idTransaksi = idTransaksi; this.barang = barang;
        this.idBarang = barang.getIdBarang(); this.qty = qty; this.subtotal = hitungSubtotal();
    }
    public double hitungSubtotal() {
        subtotal = qty * barang.getHargaJual();
        return subtotal;
    }
    public int getIdDetail() { return idDetail; }
    public int getIdTransaksi() { return idTransaksi; }
    public int getIdBarang() { return idBarang; }
    public int getQty() { return qty; }
    public double getSubtotal() { return subtotal; }
    public void setQty(int qty) {
        if (qty <= 0) throw new IllegalArgumentException("Qty harus lebih dari 0.");
        this.qty = qty; hitungSubtotal();
    }
    public Barang getBarang() { return barang; }
}
