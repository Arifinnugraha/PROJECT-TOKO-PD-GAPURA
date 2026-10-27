package id.pdgapura;

import java.time.LocalDate;
import java.util.Set;

/** Purchase Order dari toko ke supplier. */
public class PO {
    private final int idPO;
    private final LocalDate tanggal;
    protected String status;
    private final int idSupplier;
    private static final Set<String> STATUS_VALID = Set.of("DIBUAT", "DISETUJUI", "DIKIRIM", "DITERIMA", "DITOLAK");

    public PO(int idPO, LocalDate tanggal, String status, int idSupplier) {
        if (idPO <= 0 || tanggal == null || idSupplier <= 0) throw new IllegalArgumentException("Data PO tidak valid.");
        this.idPO = idPO; this.tanggal = tanggal; this.idSupplier = idSupplier; updateStatusPO(status);
    }
    public void updateStatusPO(String status) {
        if (status == null || !STATUS_VALID.contains(status.toUpperCase())) throw new IllegalArgumentException("Status PO tidak dikenal.");
        this.status = status.toUpperCase();
    }
    public int getIdPO() { return idPO; }
    public LocalDate getTanggal() { return tanggal; }
    public String getStatus() { return status; }
    public int getIdSupplier() { return idSupplier; }
}
