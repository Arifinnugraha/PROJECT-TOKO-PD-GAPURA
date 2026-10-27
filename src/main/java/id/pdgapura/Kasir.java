package id.pdgapura;

import java.time.LocalDate;

/** Pengguna operasional; akses laporan harus diberikan oleh pemilik. */
public class Kasir extends User {
    private boolean aksesLaporan;

    public Kasir(int idUser, String nama, String username, String passwordHash) {
        super(idUser, nama, username, passwordHash, "KASIR");
        this.aksesLaporan = false;
    }

    @Override public boolean bolehAksesLaporan() { return aksesLaporan; }
    public void setAksesLaporan(boolean aksesLaporan) { this.aksesLaporan = aksesLaporan; }

    public void menerimaBarang(SuratJalan suratJalan, PO po) {
        if (suratJalan == null || po == null) throw new IllegalArgumentException("Surat jalan dan PO wajib ada.");
        if (!suratJalan.verifikasiSuratJalan(po)) throw new IllegalStateException("Surat jalan tidak valid untuk PO tersebut.");
        po.updateStatusPO("DITERIMA");
    }

    public void mencatatPenjualan(TransaksiPenjualan transaksi) {
        if (transaksi == null) throw new IllegalArgumentException("Transaksi tidak boleh null.");
        transaksi.simpanTransaksi();
    }

    public AksesLaporan mengajukanAksesLaporan(int idAkses, String alasan, LocalDate awal, LocalDate akhir) {
        return new AksesLaporan(idAkses, getIdUser(), alasan, awal, akhir);
    }

    public Notifikasi kirimNotifikasi(int idNotifikasi, String isi) {
        Notifikasi notifikasi = new Notifikasi(idNotifikasi, java.time.LocalDateTime.now(), "INFO", isi);
        notifikasi.simpanNotifikasi();
        return notifikasi;
    }
}
