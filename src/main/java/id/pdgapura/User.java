package id.pdgapura;

/** Kelas induk pengguna sesuai class diagram PD Gapura. */
public abstract class User {
    private final int idUser;
    private String nama;
    private final String username;
    private String passwordHash;
    protected final String role;
    private boolean sesiAktif;

    protected User(int idUser, String nama, String username, String passwordHash, String role) {
        if (idUser <= 0) throw new IllegalArgumentException("ID user harus lebih dari 0.");
        if (kosong(nama) || kosong(username) || kosong(passwordHash) || kosong(role)) {
            throw new IllegalArgumentException("Data user tidak boleh kosong.");
        }
        this.idUser = idUser;
        this.nama = nama.trim();
        this.username = username.trim();
        this.passwordHash = passwordHash;
        this.role = role;
    }

    public boolean login(String username, String passwordHash) {
        sesiAktif = this.username.equals(username) && this.passwordHash.equals(passwordHash);
        return sesiAktif;
    }

    public void logout() { sesiAktif = false; }
    public abstract boolean bolehAksesLaporan();

    public int getIdUser() { return idUser; }
    public String getNama() { return nama; }
    public void setNama(String nama) {
        if (kosong(nama)) throw new IllegalArgumentException("Nama tidak boleh kosong.");
        this.nama = nama.trim();
    }
    public String getUsername() { return username; }
    public String getPasswordHash() { return passwordHash; }
    public void setPasswordHash(String passwordHash) {
        if (kosong(passwordHash)) throw new IllegalArgumentException("Password hash tidak boleh kosong.");
        this.passwordHash = passwordHash;
    }
    public String getRole() { return role; }
    public boolean isSesiAktif() { return sesiAktif; }

    protected static boolean kosong(String nilai) { return nilai == null || nilai.trim().isEmpty(); }
}
