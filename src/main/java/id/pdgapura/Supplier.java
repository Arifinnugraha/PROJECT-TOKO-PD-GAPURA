package id.pdgapura;

/** Pemasok barang toko. */
public class Supplier {
    private final int idSupplier;
    private String namaSupplier;
    private String kontak;

    public Supplier(int idSupplier, String namaSupplier, String kontak) {
        if (idSupplier <= 0 || User.kosong(namaSupplier)) throw new IllegalArgumentException("ID dan nama supplier wajib valid.");
        this.idSupplier = idSupplier;
        this.namaSupplier = namaSupplier.trim();
        this.kontak = kontak == null ? "" : kontak.trim();
    }
    public boolean kirimBarang(PO po) {
        if (po == null || po.getIdSupplier() != idSupplier) throw new IllegalArgumentException("PO bukan milik supplier ini.");
        po.updateStatusPO("DIKIRIM");
        return true;
    }
    public void terimaNotifikasiPenolakan(Notifikasi notifikasi) {
        if (notifikasi == null) throw new IllegalArgumentException("Notifikasi tidak boleh null.");
        System.out.println("Notifikasi supplier " + namaSupplier + ": " + notifikasi.getIsi());
    }
    public int getIdSupplier() { return idSupplier; }
    public String getNamaSupplier() { return namaSupplier; }
    public void setNamaSupplier(String namaSupplier) {
        if (User.kosong(namaSupplier)) throw new IllegalArgumentException("Nama supplier wajib diisi.");
        this.namaSupplier = namaSupplier.trim();
    }
    public String getKontak() { return kontak; }
    public void setKontak(String kontak) { this.kontak = kontak == null ? "" : kontak.trim(); }
}
