async function handlePageClick(event) {
  const go=event.target.closest('[data-go]');if(go)return navigate(go.dataset.go);
  const removeRow=event.target.closest('.remove-row');if(removeRow){const rows=$('#receiptItems')?.querySelectorAll('.item-row')||[];if(rows.length>1)removeRow.closest('.item-row').remove();else notify('Minimal satu baris barang harus tersedia.','error');return;}
  const button=event.target.closest('[data-action]');if(!button)return;
  const action=button.dataset.action,id=Number(button.dataset.id||0);
  try {
    if(action==='add-product')openProductForm();
    if(action==='view-product'){const p=state.products.find(x=>Number(x.id_barang)===id);if(p)openProductDetail(p);}
    if(action==='edit-product'){const p=state.products.find(x=>Number(x.id_barang)===id);if(p)openProductForm(p);}
    if(action==='disable-product'&&confirm('Nonaktifkan barang ini? Data historis transaksi tetap disimpan.')){await runRequest(()=>api(`products&id=${id}`,{method:'DELETE',data:{}}),{success:true});await renderProducts();}
    if(action==='add-supplier')openSupplierForm();
    if(action==='edit-supplier'){const s=state.suppliers.find(x=>Number(x.id_supplier)===id);if(s)openSupplierForm(s);}
    if(action==='disable-supplier'&&confirm('Nonaktifkan supplier ini? Riwayat penerimaan tetap tersimpan.')){await runRequest(()=>api(`suppliers&id=${id}`,{method:'DELETE',data:{}}),{success:true});await renderSuppliers();}
    if(action==='add-receipt-row'){const products=state.products;$('#receiptItems').insertAdjacentHTML('beforeend',receiptItemRow(products.map(p=>`<option value="${p.id_barang}">${escapeHtml(p.nama_barang)} — stok ${p.stok}</option>`).join('')));}
    if(action==='clear-cart'){state.cart=[];updateCartView();}
    if(action==='add-cart'){const p=state.products.find(x=>Number(x.id_barang)===id);if(!p)return;if(Number(p.stok)<1)return notify('Stok barang kosong.','error');const current=state.cart.find(x=>Number(x.id_barang)===id);if(current){if(current.jumlah>=Number(p.stok))return notify('Jumlah melebihi stok tersedia.','error');current.jumlah++;}else state.cart.push({id_barang:id,jumlah:1});updateCartView();}
    if(action==='remove-cart'){state.cart=state.cart.filter(x=>Number(x.id_barang)!==id);updateCartView();}
    if(action==='view-sale'){const detail=await api(`sales&id=${id}`);const d=detail.data;openModal(`Nota Transaksi #${id}`,`<div class="receipt-print"><div style="text-align:center;border-bottom:1px dashed #b7c5bb;padding-bottom:12px;margin-bottom:12px"><h2 style="font-size:19px">PD GAPURA</h2><p class="muted" style="font-size:10px;margin:5px 0">TOKO BANGUNAN · NOTA PENJUALAN</p></div><div style="font-size:11px;line-height:1.8"><div>No. Transaksi: <b>#${d.sale.id_transaksi}</b></div><div>Tanggal: ${dateTime(d.sale.tanggal_transaksi)}</div><div>Kasir: ${escapeHtml(d.sale.kasir)}</div></div><div class="table-scroll" style="margin:12px 0"><table><thead><tr><th>Barang</th><th>Qty</th><th>Subtotal</th></tr></thead><tbody>${d.items.map(i=>`<tr><td>${escapeHtml(i.nama_barang)}</td><td>${i.jumlah}</td><td>${money(i.subtotal)}</td></tr>`).join('')}</tbody></table></div><div class="total-box"><span>Total</span><strong>${money(d.sale.total)}</strong></div><p style="font-size:11px">Status: ${statusBadge(d.sale.status_transaksi)}</p><p style="font-size:10px;color:#849087;text-align:center;margin-top:16px">Terima kasih telah berbelanja di PD GAPURA.</p></div><div class="form-actions receipt-actions"><button class="btn btn-outline" type="button" data-close-modal>Tutup</button><button class="btn btn-primary" type="button" data-action="print-receipt">Cetak Nota</button></div>`);}
    if(action==='print-receipt'){document.body.classList.add('printing');window.print();window.setTimeout(()=>document.body.classList.remove('printing'),800);}
    if(action==='pay-sale'){const total=Number(button.dataset.total);openModal(`Pembayaran Transaksi #${id}`,`<form id="paymentForm" data-id="${id}"><div class="notice">Total transaksi yang harus dibayar: <b>${money(total)}</b>. Pembayaran dicatat jika jumlah yang diterima sama dengan total.</div><div class="form-grid"><div class="field"><label>Jumlah Bayar *</label><input type="number" name="jumlah_bayar" min="0" step="0.01" value="${total}" required></div><div class="field"><label>Metode Pembayaran *</label><select name="metode_pembayaran" required><option>Tunai</option><option>Transfer</option><option>QRIS</option></select></div></div><div class="form-actions"><button class="btn btn-outline" type="button" data-close-modal>Batal</button><button class="btn btn-primary" type="submit">Simpan Pembayaran</button></div></form>`);}
    if(action==='decide-request'){await runRequest(()=>api(`access-requests&id=${id}`,{method:'PATCH',data:{status:button.dataset.status}}),{success:true});await renderRequests();}
    if(action==='print-report')window.print();
  } catch { /* notifikasi sudah ditampilkan oleh runRequest */ }
}
async function handlePageSubmit(event) {
  const form=event.target;
  try {
    if(form.id==='productForm'){
      event.preventDefault();const fd=new FormData(form);const data=Object.fromEntries(fd.entries());['harga_beli','harga_satuan','stok','stok_minimum'].forEach(k=>data[k]=Number(data[k]));
      const id=form.dataset.id;const result=await runRequest(()=>api(id?`products&id=${id}`:'products',{method:id?'PUT':'POST',data}),{success:true});closeModal();await renderProducts();return;
    }
    if(form.id==='supplierForm'){
      event.preventDefault();const fd=new FormData(form);const data=Object.fromEntries(fd.entries());const id=form.dataset.id;await runRequest(()=>api(id?`suppliers&id=${id}`:'suppliers',{method:id?'PUT':'POST',data}),{success:true});closeModal();await renderSuppliers();return;
    }
    if(form.id==='receiptForm'){
      event.preventDefault();const items=[...$('#receiptItems').querySelectorAll('.item-row')].map(row=>({id_barang:Number($('.receipt-product',row).value),jumlah:Number($('.receipt-qty',row).value),harga_beli:Number($('.receipt-cost',row).value)}));
      if(items.some(i=>!i.id_barang||!Number.isInteger(i.jumlah)||i.jumlah<1||!Number.isFinite(i.harga_beli)||i.harga_beli<0))return notify('Periksa kembali barang, jumlah, dan harga beli.','error');
      const data={id_supplier:Number(form.id_supplier.value),tanggal_masuk:form.tanggal_masuk.value,items};await runRequest(()=>api('receipts',{method:'POST',data}),{success:true});await renderReceipts();return;
    }
    if(form.id==='saleForm'){
      event.preventDefault();if(!state.cart.length)return notify('Keranjang transaksi masih kosong.','error');
      const method=form.metode_pembayaran.value;const paid=Number(form.jumlah_bayar.value);const total=cartTotal();
      if(!Number.isFinite(paid)||paid<0)return notify('Jumlah pembayaran tidak valid.','error');
      if(method==='Belum Dibayar'&&paid!==0)return notify('Isi Rp0 untuk transaksi yang belum dibayar.','error');
      if(method!=='Belum Dibayar'&&Math.abs(paid-total)>0.009)return notify('Jumlah bayar harus sama dengan total transaksi.','error');
      const data={items:state.cart,metode_pembayaran:method,jumlah_bayar:paid};await runRequest(()=>api('sales',{method:'POST',data}),{success:true});state.cart=[];await renderSales();return;
    }
    if(form.id==='paymentForm'){
      event.preventDefault();const fd=new FormData(form);const data={id_transaksi:Number(form.dataset.id),jumlah_bayar:Number(fd.get('jumlah_bayar')),metode_pembayaran:fd.get('metode_pembayaran')};await runRequest(()=>api('payments',{method:'POST',data}),{success:true});closeModal();await renderSales();return;
    }
    if(form.id==='reportFilter'){
      event.preventDefault();const fd=new FormData(form);if(fd.get('start')>fd.get('end'))return notify('Tanggal awal tidak boleh melebihi tanggal akhir.','error');await runRequest(()=>loadReport(fd.get('start'),fd.get('end')));return;
    }
    if(form.id==='requestForm'){
      event.preventDefault();const fd=new FormData(form);await runRequest(()=>api('access-requests',{method:'POST',data:{alasan:fd.get('alasan')}}),{success:true});await renderRequests();return;
    }
  } catch { /* notifikasi ditangani wrapper */ }
}
function handlePageInput(event) {
  if(event.target.id==='productSearch'){
    const q=event.target.value.toLowerCase();const filtered=state.products.filter(p=>(p.kode_barang+' '+p.nama_barang+' '+p.kategori).toLowerCase().includes(q));$('#productRows').innerHTML=productRows(filtered,state.user.role==='pemilik');
  }
  if(event.target.id==='saleSearch'){
    const q=event.target.value.toLowerCase();document.querySelectorAll('#saleProducts .product-card').forEach(card=>card.classList.toggle('hidden',!card.dataset.search.includes(q)));
  }
}
function handlePageChange(event) {
  if(event.target.matches('[data-cart-qty]')){
    const id=Number(event.target.dataset.cartQty);const item=state.cart.find(x=>Number(x.id_barang)===id);const p=state.products.find(x=>Number(x.id_barang)===id);let qty=Number(event.target.value);
    if(!Number.isInteger(qty)||qty<1)qty=1;if(qty>Number(p?.stok||0)){qty=Number(p.stok);notify('Jumlah disesuaikan dengan stok yang tersedia.','error');}
    if(item)item.jumlah=qty;updateCartView();
  }
  if(event.target.name==='metode_pembayaran'&&$('#saleForm')){
    const amount=$('#amountPaid');if(event.target.value==='Belum Dibayar')amount.value=0;else if(Number(amount.value)===0)amount.value=cartTotal();
  }
}

