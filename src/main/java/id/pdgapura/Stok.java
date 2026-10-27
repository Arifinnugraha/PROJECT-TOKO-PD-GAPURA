package id.pdgapura;

/** Jumlah persediaan untuk satu barang. */
public class Stok {
    private final int idStok;
    private final int idBarang;
    protected int jumlah;

    public Stok(int idStok, int idBarang, int jumlah) {
        if (idStok <= 0 || idBarang <= 0 || jumlah < 0) throw new IllegalArgumentException("Data stok tidak valid.");
        this.idStok = idStok; this.idBarang = idBarang; this.jumlah = jumlah;
    }
    public void tambahStok(int jumlahTambah) {
        if (jumlahTambah <= 0) throw new IllegalArgumentException("Jumlah penambahan harus lebih dari 0.");
        jumlah = Math.addExact(jumlah, jumlahTambah);
    }
    public void kurangiStok(int jumlahKurang) {
        if (jumlahKurang <= 0) throw new IllegalArgumentException("Jumlah pengurangan harus lebih dari 0.");
        if (jumlahKurang > jumlah) throw new IllegalStateException("Stok tidak mencukupi.");
        jumlah -= jumlahKurang;
    }
    public int cekStok() { return jumlah; }
    public int getIdStok() { return idStok; }
    public int getIdBarang() { return idBarang; }
}
