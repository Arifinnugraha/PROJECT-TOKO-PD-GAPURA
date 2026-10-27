package id.pdgapura;

/** Data master barang. */
public class Barang {
    private final int idBarang;
    private String namaBarang;
    private double hargaBeli;
    private double hargaJual;

    public Barang(int idBarang, String namaBarang, double hargaBeli, double hargaJual) {
        if (idBarang <= 0 || User.kosong(namaBarang)) throw new IllegalArgumentException("ID dan nama barang wajib valid.");
        this.idBarang = idBarang;
        updateDataBarang(namaBarang, hargaBeli, hargaJual);
    }
    public void updateDataBarang(String namaBarang, double hargaBeli, double hargaJual) {
        if (User.kosong(namaBarang) || hargaBeli < 0 || hargaJual < 0 || !Double.isFinite(hargaBeli) || !Double.isFinite(hargaJual)) {
            throw new IllegalArgumentException("Nama dan harga barang tidak valid.");
        }
        this.namaBarang = namaBarang.trim(); this.hargaBeli = hargaBeli; this.hargaJual = hargaJual;
    }
    public int getIdBarang() { return idBarang; }
    public String getNamaBarang() { return namaBarang; }
    public double getHargaBeli() { return hargaBeli; }
    public double getHargaJual() { return hargaJual; }
}
