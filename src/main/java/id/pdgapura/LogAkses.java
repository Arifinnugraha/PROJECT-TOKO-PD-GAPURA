package id.pdgapura;

import java.time.LocalDateTime;

/** Kelas pendukung sequence diagram; tambahkan ke class diagram final karena tidak tampak pada diagram awal. */
public class LogAkses {
    private final int idLog;
    private final int idUser;
    private final LocalDateTime tanggal;
    private final String aktivitas;

    public LogAkses(int idLog, int idUser, LocalDateTime tanggal, String aktivitas) {
        if (idLog <= 0 || idUser <= 0 || tanggal == null || User.kosong(aktivitas)) throw new IllegalArgumentException("Data log akses tidak valid.");
        this.idLog = idLog; this.idUser = idUser; this.tanggal = tanggal; this.aktivitas = aktivitas;
    }
    public int getIdLog() { return idLog; }
    public int getIdUser() { return idUser; }
    public LocalDateTime getTanggal() { return tanggal; }
    public String getAktivitas() { return aktivitas; }
}
