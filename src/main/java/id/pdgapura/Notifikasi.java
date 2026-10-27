package id.pdgapura;

import java.time.LocalDateTime;

/** Notifikasi internal sistem. */
public class Notifikasi {
    private final int idNotif;
    private final LocalDateTime tanggal;
    private final String tipe;
    private final String isi;
    protected boolean statusBaca;
    private boolean tersimpan;

    public Notifikasi(int idNotif, LocalDateTime tanggal, String tipe, String isi) {
        if (idNotif <= 0 || tanggal == null || User.kosong(tipe) || User.kosong(isi)) throw new IllegalArgumentException("Data notifikasi tidak valid.");
        this.idNotif = idNotif; this.tanggal = tanggal; this.tipe = tipe; this.isi = isi;
    }
    public void simpanNotifikasi() { tersimpan = true; }
    public void tandaiSudahBaca() { statusBaca = true; }
    public int getIdNotif() { return idNotif; }
    public LocalDateTime getTanggal() { return tanggal; }
    public String getTipe() { return tipe; }
    public String getIsi() { return isi; }
    public boolean isStatusBaca() { return statusBaca; }
    public boolean isTersimpan() { return tersimpan; }
}
