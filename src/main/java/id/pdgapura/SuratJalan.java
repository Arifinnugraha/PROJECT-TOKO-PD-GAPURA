package id.pdgapura;

import java.time.LocalDate;

/** Dokumen pengiriman barang yang harus sesuai dengan PO. */
public class SuratJalan {
    private final int idSuratJalan;
    private final LocalDate tanggal;
    private final int idPO;
    protected boolean valid;

    public SuratJalan(int idSuratJalan, LocalDate tanggal, int idPO) {
        if (idSuratJalan <= 0 || tanggal == null || idPO <= 0) throw new IllegalArgumentException("Data surat jalan tidak valid.");
        this.idSuratJalan = idSuratJalan; this.tanggal = tanggal; this.idPO = idPO;
    }
    public boolean verifikasiSuratJalan(PO po) {
        valid = po != null && po.getIdPO() == idPO && !"DITOLAK".equals(po.getStatus());
        return valid;
    }
    public int getIdSuratJalan() { return idSuratJalan; }
    public LocalDate getTanggal() { return tanggal; }
    public int getIdPO() { return idPO; }
    public boolean isValid() { return valid; }
}
