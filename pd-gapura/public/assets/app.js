const API = 'api.php?route=';
const state = { user: null, csrf: '', page: 'dashboard', products: [], suppliers: [], sales: [], receipts: [], cart: [], report: null };
const $ = (selector, root = document) => root.querySelector(selector);
const money = value => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(value || 0));
const dateTime = value => value ? new Date(String(value).replace(' ', 'T')).toLocaleString('id-ID') : '-';
const escapeHtml = value => String(value ?? '').replace(/[&<>"']/g, ch => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[ch]));

async function api(route, options = {}) {
  const method = options.method || 'GET';
  const headers = { 'Accept': 'application/json' };
  if (options.data !== undefined) headers['Content-Type'] = 'application/json';
  if (!['GET', 'HEAD'].includes(method) && route !== 'auth/login' && state.csrf) headers['X-CSRF-Token'] = state.csrf;
  const response = await fetch(API + route, { method, headers, body: options.data !== undefined ? JSON.stringify(options.data) : undefined, credentials: 'same-origin' });
  let payload;
  try { payload = await response.json(); } catch { throw new Error('Respons server tidak dapat dibaca.'); }
  if (!response.ok || payload.success === false) throw new Error(payload.message || `Request gagal (${response.status}).`);
  return payload;
}
function showLoading(show) { $('#loadingOverlay').classList.toggle('hidden', !show); }
function notify(message, type = 'success') {
  const el = $('#globalMessage'); el.textContent = message; el.className = `global-message ${type}`;
  window.clearTimeout(notify.timer); notify.timer = window.setTimeout(() => el.classList.add('hidden'), 4500);
}
async function runRequest(callback, {loading = true, success = false} = {}) {
  try { if (loading) showLoading(true); const result = await callback(); if (success && result?.message) notify(result.message, 'success'); return result; }
  catch (error) { notify(error.message || 'Terjadi kesalahan.', 'error'); throw error; }
  finally { if (loading) showLoading(false); }
}
function toggleApp(isLoggedIn) { $('#loginView').classList.toggle('hidden', isLoggedIn); $('#appView').classList.toggle('hidden', !isLoggedIn); setDrawer(false); }
function setDrawer(open) { document.body.classList.toggle('drawer-open', open); $('#drawerToggle')?.setAttribute('aria-expanded', open ? 'true' : 'false'); $('#sideDrawer')?.setAttribute('aria-hidden', open ? 'false' : 'true'); }
function productImage(product, extraClass = '') {
  const name = `${product?.nama_barang || ''} ${product?.kategori || ''}`.toLowerCase();
  let file = 'tool.png';
  if (name.includes('semen')) file = 'semen.png';
  else if (name.includes('cat') || name.includes('paint')) file = 'cat.png';
  else if (name.includes('pipa')) file = 'pipa.png';
  else if (name.includes('besi') || name.includes('baja')) file = 'besi.png';
  return `<img class="${extraClass}" src="assets/products/${file}" alt="${escapeHtml(product?.nama_barang || 'Barang')}" loading="lazy" onerror="this.style.display='none'">`;
}
function setUser(user) {
  state.user = user;
  $('#userName').textContent = user.nama;
  $('#userRole').textContent = user.role === 'pemilik' ? 'Pemilik Toko' : 'Kasir';
  $('#userAvatar').textContent = user.nama.split(' ').map(x => x[0]).slice(0,2).join('').toUpperCase();
  $('#drawerAvatar').textContent = $('#userAvatar').textContent;
  $('#drawerName').textContent = user.nama;
  $('#drawerRole').textContent = user.role === 'pemilik' ? 'Pemilik Toko' : 'Kasir';
  document.querySelectorAll('[data-owner-only]').forEach(el => el.classList.toggle('hidden', user.role !== 'pemilik'));
  const reportLink = $('#sideNav').querySelector('[data-page="reports"]');
  reportLink.classList.toggle('hidden', user.role !== 'pemilik' && !user.can_view_reports);
}
function openModal(title, html) { $('#modalTitle').textContent = title; $('#modalBody').innerHTML = html; $('#modal').classList.remove('hidden'); $('#modal').setAttribute('aria-hidden','false'); }
function closeModal() { $('#modal').classList.add('hidden'); $('#modal').setAttribute('aria-hidden','true'); }
function formField(label, name, value = '', type = 'text', required = true, attrs = '') {
  return `<div class="field"><label for="${name}">${label}${required ? ' *' : ''}</label><input id="${name}" name="${name}" type="${type}" value="${escapeHtml(value)}" ${required ? 'required' : ''} ${attrs}></div>`;
}
function emptyRow(cols, text = 'Belum ada data.') { return `<tr><td colspan="${cols}" class="empty-state">${text}</td></tr>`; }
function pageHeader(title, subtitle, action = '') { return `<div class="page-heading"><div><h2>${title}</h2><p>${subtitle}</p></div>${action}</div>`; }
function setPageTitle(title) { $('#pageTitle').textContent = title; document.querySelectorAll('.nav-link[data-page]').forEach(btn => btn.classList.toggle('active', btn.dataset.page === state.page)); }
