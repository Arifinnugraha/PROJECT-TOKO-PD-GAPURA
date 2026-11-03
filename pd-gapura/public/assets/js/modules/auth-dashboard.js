async function init() {
  $('#loginForm').addEventListener('submit', handleLogin);
  $('#logoutBtn').addEventListener('click', handleLogout);
  $('#drawerLogout').addEventListener('click', handleLogout);
  $('#drawerToggle').addEventListener('click', () => setDrawer(true));
  $('#drawerClose').addEventListener('click', () => setDrawer(false));
  $('#drawerBackdrop').addEventListener('click', () => setDrawer(false));
  document.addEventListener('keydown', e => { if (e.key === 'Escape') { setDrawer(false); closeModal(); } });
  $('#sideNav').addEventListener('click', e => { const button = e.target.closest('[data-page]'); if (button) { setDrawer(false); navigate(button.dataset.page); } });
  $('#modal').addEventListener('click', e => { if (e.target.matches('[data-close-modal]')) closeModal(); else handlePageClick(e); });
  $('#modal').addEventListener('submit', handlePageSubmit);
  $('#pageContent').addEventListener('click', handlePageClick);
  $('#pageContent').addEventListener('submit', handlePageSubmit);
  $('#pageContent').addEventListener('input', handlePageInput);
  $('#pageContent').addEventListener('change', handlePageChange);
  try {
    const me = await api('auth/me');
    state.csrf = me.data.csrf_token || '';
    if (me.data.user) { setUser(me.data.user); toggleApp(true); await navigate('dashboard'); }
    else toggleApp(false);
  } catch { toggleApp(false); }
}
async function handleLogin(event) {
  event.preventDefault(); const form = new FormData(event.currentTarget);
  $('#loginMessage').textContent = '';
  try {
    showLoading(true);
    const result = await api('auth/login', { method: 'POST', data: { username: form.get('username'), password: form.get('password') } });
    state.csrf = result.data.csrf_token; setUser(result.data.user); toggleApp(true); event.currentTarget.reset(); await navigate('dashboard');
  } catch (error) { $('#loginMessage').textContent = error.message; }
  finally { showLoading(false); }
}
async function handleLogout() {
  try { await runRequest(() => api('auth/logout', { method: 'POST', data: {} })); }
  catch { /* sesi mungkin sudah habis */ }
  state.user = null; state.csrf = ''; state.cart = []; toggleApp(false);
}
async function navigate(page) {
  const allowed = ['dashboard','products','suppliers','receipts','sales','reports','requests','logs'];
  if (!allowed.includes(page)) return;
  if (state.user?.role === 'kasir' && page === 'logs') return notify('Log aktivitas hanya dapat diakses pemilik.', 'error');
  if (state.user?.role === 'kasir' && page === 'reports' && !state.user.can_view_reports) return notify('Laporan memerlukan persetujuan pemilik terlebih dahulu.', 'error');
  state.page = page;
  const titles = {dashboard:'Dashboard',products:'Data Barang',suppliers:'Data Supplier',receipts:'Penerimaan Barang',sales:'Transaksi Penjualan',reports:'Laporan',requests:'Permintaan Akses Laporan',logs:'Log Aktivitas'};
  setPageTitle(titles[page]); $('#pageContent').innerHTML = '<div class="panel"><div class="empty-state">Memuat data...</div></div>';
  try {
    switch (page) {
      case 'dashboard': await renderDashboard(); break;
      case 'products': await renderProducts(); break;
      case 'suppliers': await renderSuppliers(); break;
      case 'receipts': await renderReceipts(); break;
      case 'sales': await renderSales(); break;
      case 'reports': await renderReports(); break;
      case 'requests': await renderRequests(); break;
      case 'logs': await renderLogs(); break;
    }
  } catch (error) { $('#pageContent').innerHTML = `<div class="panel"><div class="empty-state">${escapeHtml(error.message)}</div></div>`; }
}

async function renderDashboard() {
  const [result, productResult] = await Promise.all([api('dashboard'), fetchProducts()]);
  const d = result.data;
  const products = productResult.slice(0, 2);
  const shortcuts = [
    ['▣','Manajemen Stok','products'],
    ['⇩','Barang Masuk','receipts'],
    ['▤','Laporan','reports'],
    ['🛒','Transaksi','sales']
  ].filter(item => !(item[2] === 'reports' && state.user.role !== 'pemilik' && !state.user.can_view_reports));
  $('#pageContent').innerHTML = `
    <div class="dashboard-screen">
      <div class="dashboard-welcome"><div><h2>Selamat datang, ${escapeHtml(state.user.nama.split(' ')[0])}!</h2><p>Ringkasan operasional Toko Bangunan PD GAPURA hari ini.</p></div><span class="welcome-chip">${state.user.role === 'pemilik' ? 'Pemilik Toko' : 'Kasir'}</span></div>
      <h2 class="dashboard-section-title">Produk Pilihan</h2>
      <div class="popular-strip">${products.length ? products.map(p=>`<article class="popular-item"><div class="popular-thumb">${productImage(p)}</div><div><b>${escapeHtml(p.nama_barang)}</b><small>${money(p.harga_satuan)} · Stok ${p.stok} ${escapeHtml(p.satuan)}</small></div></article>`).join('') : '<div class="popular-item">Belum ada data produk.</div>'}</div>
      <h2 class="dashboard-section-title">Menu Utama</h2>
      <div class="dashboard-shortcuts">${shortcuts.map(([icon,label,page])=>`<button class="shortcut-tile" type="button" data-go="${page}"><span class="shortcut-icon">${icon}</span><span>${label}</span></button>`).join('')}</div>
      <div class="dashboard-stats">
        <div class="dashboard-stat"><span>Penjualan Hari Ini</span><strong>${money(d.todaySales)}</strong><small>Transaksi yang sudah lunas</small></div>
        <div class="dashboard-stat"><span>Transaksi Hari Ini</span><strong>${d.todayCount}</strong><small>Seluruh transaksi tercatat</small></div>
        <div class="dashboard-stat"><span>Barang Aktif</span><strong>${d.productsCount}</strong><small>Jenis barang dalam sistem</small></div>
        <div class="dashboard-stat"><span>Stok Menipis</span><strong>${d.lowStock}</strong><small>Barang mencapai stok minimum</small></div>
      </div>
      <div class="dashboard-lower">
        <section class="dashboard-white-panel"><div class="panel-head"><h3>Transaksi Terbaru</h3><button class="btn btn-light btn-sm" data-go="sales">Lihat semua</button></div><div class="table-scroll"><table><thead><tr><th>No. Transaksi</th><th>Tanggal</th><th>Kasir</th><th>Total</th><th>Status</th></tr></thead><tbody>${d.recent.length ? d.recent.map(r=>`<tr><td>#${r.id_transaksi}</td><td>${dateTime(r.tanggal_transaksi)}</td><td>${escapeHtml(r.kasir)}</td><td>${money(r.total)}</td><td>${statusBadge(r.status_transaksi)}</td></tr>`).join('') : emptyRow(5)}</tbody></table></div></section>
        <section class="dashboard-white-panel"><div class="panel-head"><h3>Stok Perlu Perhatian</h3></div><div class="table-scroll"><table><thead><tr><th>Barang</th><th>Stok</th><th>Minimum</th></tr></thead><tbody>${d.stockRows.length ? d.stockRows.map(p=>`<tr><td><b>${escapeHtml(p.nama_barang)}</b><br><small class="muted-cell">${escapeHtml(p.kode_barang)}</small></td><td><span class="badge warn">${p.stok} ${escapeHtml(p.satuan)}</span></td><td>${p.stok_minimum}</td></tr>`).join('') : emptyRow(3,'Tidak ada stok menipis.')}</tbody></table></div></section>
      </div>
    </div>`;
}
function statusBadge(value) { const red = ['Belum Lunas','Ditolak','Menipis','nonaktif'].includes(value); const warn = ['Menunggu'].includes(value); return `<span class="badge ${red?'red':warn?'warn':''}">${escapeHtml(value)}</span>`; }

