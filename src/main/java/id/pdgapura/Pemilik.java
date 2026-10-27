package id.pdgapura;

import java.time.LocalDate;

/** Pengguna dengan hak akses manajerial. */
public class Pemilik extends User {
    public Pemilik(int idUser, String nama, String username, String passwordHash) {
        super(idUser, nama, username, passwordHash, "PEMILIK");
    }

    @Override public boolean bolehAksesLaporan() { return true; }

    public void beriAksesLaporan(AksesLaporan permintaan) {
        if (permintaan == null) throw new IllegalArgumentException("Permintaan akses wajib diisi.");
        permintaan.updateStatus("DISETUJUI", true);
    }

    /** Menyetujui permintaan dan mengaktifkan izin untuk kasir yang mengajukan. */
    public void beriAksesLaporan(AksesLaporan permintaan, Kasir kasir) {
        if (permintaan == null || kasir == null) throw new IllegalArgumentException("Permintaan dan kasir wajib diisi.");
        if (permintaan.getIdUser() != kasir.getIdUser()) throw new IllegalArgumentException("Permintaan bukan milik kasir tersebut.");
        permintaan.updateStatus("DISETUJUI", true);
        kasir.setAksesLaporan(true);
    }

    public LaporanPenjualan hasilkanLaporanHarian(LaporanService service, LocalDate tanggal) {
        if (service == null) throw new IllegalArgumentException("Layanan laporan belum tersedia.");
        return service.hasilkanLaporanHarian(this, tanggal);
    }

    public void terimaNotifikasi(Notifikasi notifikasi) {
        if (notifikasi == null) throw new IllegalArgumentException("Notifikasi tidak boleh null.");
        System.out.println("Notifikasi untuk pemilik: " + notifikasi.getIsi());
    }

    public void tindakLanjutNotifikasi(Notifikasi notifikasi) {
        if (notifikasi == null) throw new IllegalArgumentException("Notifikasi tidak boleh null.");
        notifikasi.tandaiSudahBaca();
    }
}
