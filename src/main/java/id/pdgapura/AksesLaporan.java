package id.pdgapura;

import java.time.LocalDate;
import java.time.LocalDateTime;

/** Permintaan akses laporan dari pengguna operasional. */
public class AksesLaporan {
    private final int idAkses;
    private final int idUser;
    private final String alasan;
    private final LocalDate periodeAwal;
    private final LocalDate periodeAkhir;
    protected String status;
    protected boolean aksesSementara;

    public AksesLaporan(int idAkses, int idUser, String alasan, LocalDate periodeAwal, LocalDate periodeAkhir) {
        if (idAkses <= 0 || idUser <= 0 || User.kosong(alasan) || periodeAwal == null || periodeAkhir == null || periodeAkhir.isBefore(periodeAwal)) {
            throw new IllegalArgumentException("Permintaan akses laporan tidak valid.");
        }
        this.idAkses = idAkses; this.idUser = idUser; this.alasan = alasan.trim();
        this.periodeAwal = periodeAwal; this.periodeAkhir = periodeAkhir; this.status = "MENUNGGU";
    }
    public Notifikasi sendNotifikasi(int idNotif) {
        Notifikasi n = new Notifikasi(idNotif, LocalDateTime.now(), "PERMINTAAN_AKSES", "Permintaan akses laporan dari user " + idUser);
        n.simpanNotifikasi(); return n;
    }
    public void updateStatus(String status, boolean aksesSementara) {
        if (!"DISETUJUI".equals(status) && !"DITOLAK".equals(status) && !"MENUNGGU".equals(status)) {
            throw new IllegalArgumentException("Status akses tidak valid.");
        }
        this.status = status; this.aksesSementara = aksesSementara && "DISETUJUI".equals(status);
    }
    public int getIdAkses() { return idAkses; }
    public int getIdUser() { return idUser; }
    public String getAlasan() { return alasan; }
    public LocalDate getPeriodeAwal() { return periodeAwal; }
    public LocalDate getPeriodeAkhir() { return periodeAkhir; }
    public String getStatus() { return status; }
    public boolean isAksesSementara() { return aksesSementara; }
}
