async function fetchProducts() { const r=await api('products'); state.products=r.data; return r.data; }
async function renderProducts() {
  const products=await fetchProducts(); const owner=state.user.role==='pemilik';
  $('#pageContent').innerHTML = `${pageHeader('Manajemen Stok','Pantau ketersediaan barang, harga jual, dan batas minimum.',owner?'<button class="btn btn-primary" data-action="add-product">+ Tambah Barang</button>':'')}
    <section class="panel"><div class="stock-toolbar"><div class="stock-search"><input class="search-box" id="productSearch" placeholder="Cari nama atau kode barang..."></div><span class="filter-chip active">Semua Item</span><span class="filter-chip">Daftar Barang</span><span class="muted" style="font-size:11px;margin-left:auto">${products.length} item aktif</span></div>
    <div class="stock-list" id="productRows">${productRows(products,owner)}</div></section>`;
}
function productRows(products, owner) {
  if(!products.length)return '<div class="empty-state">Data barang belum tersedia.</div>';
  return products.map(p=>`<article class="stock-list-item"><div class="stock-thumb">${productImage(p)}</div><div class="stock-info"><button type="button" class="stock-name-button" data-action="view-product" data-id="${p.id_barang}">${escapeHtml(p.nama_barang)}</button><small>${escapeHtml(p.kode_barang)} · ${escapeHtml(p.kategori)} · ${escapeHtml(p.lokasi_penyimpanan||'Lokasi belum diatur')}</small><div class="stock-meta">${statusBadge(Number(p.stok)<=Number(p.stok_minimum)?'Menipis':'Aman')}<small>${money(p.harga_satuan)} / ${escapeHtml(p.satuan)}</small></div></div><div class="stock-side"><strong class="stock-qty">${p.stok} ${escapeHtml(p.satuan)}</strong><small class="stock-price">${money(p.harga_satuan)}</small>${owner?`<div class="stock-actions"><button class="btn btn-light btn-sm" data-action="edit-product" data-id="${p.id_barang}">Edit</button><button class="btn btn-danger btn-sm" data-action="disable-product" data-id="${p.id_barang}">Nonaktif</button></div>`:''}</div></article>`).join('');
}
function openProductDetail(p) {
  const owner = state.user.role === 'pemilik';
  openModal('Detail Barang', `<div class="product-detail"><div class="product-detail-image">${productImage(p)}</div><h3>${escapeHtml(p.nama_barang)}</h3><div class="product-detail-grid"><div><small>Harga Beli</small><b>${money(p.harga_beli)}</b></div><div><small>Harga Jual</small><b>${money(p.harga_satuan)}</b></div><div><small>Stok Tersedia</small><b>${p.stok} ${escapeHtml(p.satuan)}</b></div><div><small>Stok Minimum</small><b>${p.stok_minimum} ${escapeHtml(p.satuan)}</b></div><div><small>Kategori</small><b>${escapeHtml(p.kategori)}</b></div><div><small>Kode Barang</small><b>${escapeHtml(p.kode_barang)}</b></div><div><small>Letak Rak</small><b>${escapeHtml(p.lokasi_penyimpanan || '-')}</b></div><div><small>Status Stok</small><b>${Number(p.stok)<=Number(p.stok_minimum)?'Menipis':'Aman'}</b></div></div><div class="product-detail-note"><small>Keterangan</small><p>${escapeHtml(p.keterangan || 'Tidak ada keterangan.')}</p></div><div class="form-actions"><button class="btn btn-outline" type="button" data-close-modal>Tutup</button>${owner?`<button class="btn btn-primary" type="button" data-action="edit-product" data-id="${p.id_barang}">Edit Barang</button>`:''}</div></div>`);
}
function openProductForm(product = null) {
  const p=product||{}; const editing=!!product;
  openModal(editing?'Ubah Data Barang':'Tambah Barang',`<form id="productForm" data-id="${p.id_barang||''}"><div class="form-grid">
    ${formField('Kode Barang','kode_barang',p.kode_barang||'')}${formField('Nama Barang','nama_barang',p.nama_barang||'')}
    ${formField('Kategori','kategori',p.kategori||'')}${formField('Satuan','satuan',p.satuan||'pcs')}
    ${formField('Harga Beli','harga_beli',p.harga_beli??0,'number',true,'min="0" step="0.01"')}${formField('Harga Jual','harga_satuan',p.harga_satuan??'','number',true,'min="0" step="0.01"')}
    ${formField('Stok','stok',p.stok??0,'number',true,'min="0" step="1"')}${formField('Stok Minimum','stok_minimum',p.stok_minimum??0,'number',true,'min="0" step="1"')}
    ${formField('Lokasi Penyimpanan','lokasi_penyimpanan',p.lokasi_penyimpanan||'', 'text',false)}
    <div class="field full"><label>Keterangan</label><textarea name="keterangan">${escapeHtml(p.keterangan||'')}</textarea></div></div><p class="help-text">Validasi: kolom wajib, format harga numerik, stok bilangan bulat minimal 0, dan kode barang tidak boleh duplikat.</p><div class="form-actions"><button type="button" class="btn btn-outline" data-close-modal>Batal</button><button class="btn btn-primary" type="submit">Simpan Barang</button></div></form>`);
}

