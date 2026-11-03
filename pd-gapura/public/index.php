<?php
// Halaman utama PD GAPURA. Data diproses oleh PHP API dan Supabase PostgreSQL.
?><!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#36b765">
  <title>PD GAPURA | Sistem Informasi Manajemen Toko Bangunan</title>
  <link rel="stylesheet" href="assets/style.css">
  <link rel="stylesheet" href="assets/sdd-theme.css">
</head>
<body>
  <section id="loginView" class="login-shell">
    <div class="login-card">
      <div class="login-brand-wrap"><img class="login-logo" src="assets/logo-pd-gapura.png" alt="PD GAPURA"></div>
      <p class="login-subtitle">SISTEM INFORMASI MANAJEMEN TOKO BANGUNAN</p>
      <form id="loginForm" class="form-stack">
        <label>Username<input name="username" autocomplete="username" required placeholder="Masukkan username"></label>
        <label>Password<input name="password" type="password" autocomplete="current-password" required placeholder="Masukkan password"></label>
        <p id="loginMessage" class="form-message" aria-live="polite"></p>
        <button class="btn btn-primary btn-full" type="submit">Masuk</button>
      </form>
      <div class="demo-accounts"><strong>Akun demonstrasi praktikum</strong><span>Pemilik: <code>owner / owner123</code></span><span>Kasir: <code>kasir / kasir123</code></span></div>
    </div>
  </section>

  <div id="appView" class="app-shell hidden">
    <div class="app-frame">
      <header class="app-topbar">
        <button id="drawerToggle" class="hamburger-button" type="button" aria-label="Buka menu" aria-expanded="false"><span></span><span></span><span></span></button>
        <div class="app-brand"><img src="assets/logo-pd-gapura.png" alt="PD GAPURA"></div>
        <div class="topbar-user">
          <div class="avatar" id="userAvatar">PG</div>
          <div class="topbar-user-copy"><b id="userName">Pengguna</b><small id="userRole">Role</small></div>
          <button id="logoutBtn" class="topbar-logout" type="button" title="Keluar" aria-label="Keluar">↪</button>
        </div>
      </header>

      <div id="drawerBackdrop" class="drawer-backdrop"></div>
      <aside id="sideDrawer" class="side-drawer" aria-label="Menu navigasi" aria-hidden="true">
        <div class="drawer-user">
          <div class="avatar drawer-avatar" id="drawerAvatar">PG</div>
          <div><b id="drawerName">Pengguna</b><small id="drawerRole">Role</small></div>
          <button id="drawerClose" class="drawer-close" type="button" aria-label="Tutup menu">×</button>
        </div>
        <div class="nav-caption">MENU UTAMA</div>
        <nav id="sideNav">
          <button class="nav-link active" data-page="dashboard"><span>⌂</span> Dashboard</button>
          <button class="nav-link" data-page="products"><span>▣</span> Manajemen Stok</button>
          <button class="nav-link" data-page="suppliers"><span>♙</span> Data Supplier</button>
          <button class="nav-link" data-page="receipts"><span>⇩</span> Penerimaan Barang</button>
          <button class="nav-link" data-page="sales"><span>🛒</span> Transaksi Penjualan</button>
          <button class="nav-link" data-page="reports"><span>▤</span> Laporan</button>
          <button class="nav-link" data-page="requests"><span>◷</span> Permintaan Akses Laporan</button>
          <button class="nav-link" data-page="logs" data-owner-only><span>≋</span> Log Aktivitas</button>
        </nav>
        <div class="drawer-bottom"><div class="drawer-note"><b>PD GAPURA</b><small>Sistem Informasi Manajemen Toko Bangunan</small></div><button id="drawerLogout" class="nav-link logout" type="button"><span>↪</span> Keluar dari akun</button></div>
      </aside>

      <main class="main-area">
        <div class="current-page-bar"><div><span>PD GAPURA</span><i>/</i><h1 id="pageTitle">Dashboard</h1></div><span class="online-dot">Sistem aktif</span></div>
        <div id="globalMessage" class="global-message hidden" role="status"></div>
        <section id="pageContent" class="page-content"></section>
        <footer class="app-footer"><b>PD GAPURA</b><span>Sistem Informasi Manajemen Toko Bangunan</span><small>© 2026 PD GAPURA. Semua hak cipta dilindungi.</small></footer>
      </main>
    </div>
  </div>

  <div id="modal" class="modal hidden" aria-hidden="true"><div class="modal-backdrop" data-close-modal></div><div class="modal-card"><div class="modal-head"><h2 id="modalTitle">Form</h2><button class="icon-button" data-close-modal aria-label="Tutup">×</button></div><div id="modalBody"></div></div></div>
  <div id="loadingOverlay" class="loading-overlay hidden"><div class="spinner"></div><span>Memproses data...</span></div>
  <!-- Shared state/helpers dimuat lebih dahulu, lalu modul fitur, terakhir entry point. -->
  <script src="assets/app.js" defer></script>
  <script src="assets/js/modules/auth-dashboard.js" defer></script>
  <script src="assets/js/modules/products.js" defer></script>
  <script src="assets/js/modules/suppliers-receipts.js" defer></script>
  <script src="assets/js/modules/sales.js" defer></script>
  <script src="assets/js/modules/reports.js" defer></script>
  <script src="assets/js/modules/events.js" defer></script>
  <script src="assets/js/app-init.js" defer></script>
</body>
</html>
