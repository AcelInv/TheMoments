  <!-- Keranjang Belanja -->
  <div class="cart-overlay" id="cartOverlay" onclick="toggleCart()"></div>
  <div class="cart-panel" id="cartPanel">
    <div class="cp-head">
      <h2>Keranjang</h2>
      <button class="cp-close" onclick="toggleCart()">✕</button>
    </div>
    <div class="cp-items" id="cpItems"></div>
    <div class="cp-foot">
      <div class="cp-sub"><span>Total</span><span class="amt" id="cpTotal">Rp 0</span></div>
      <div class="cp-info">
        <span>Bunga Segar Garansi</span>
        <span>Gratis &gt; Rp 200rb</span>
      </div>
      <button class="checkout-btn" id="checkoutBtn" onclick="openCheckout()" disabled>Lanjut ke Pembayaran →</button>
    </div>
  </div>

  <!-- Modal Berhasil Ditambahkan ke Keranjang -->
  <div class="modal-bg" id="successCartModal" onclick="closeModal('successCartModal',event)">
    <div class="modal success-cart-modal">
      <div class="sc-head">
        <h3>Berhasil Ditambahkan</h3>
        <button class="sc-close" onclick="closeModal('successCartModal')">✕</button>
      </div>
      <div class="sc-body">
        <div class="sc-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"></polyline>
          </svg>
        </div>
        <p id="scMessage">Produk berhasil ditambahkan ke keranjang!</p>
      </div>
      <div class="sc-foot">
        <button class="sc-btn-cart" onclick="closeModal('successCartModal'); toggleCart()">Lihat Keranjang</button>
        <button class="sc-btn-shop" onclick="closeModal('successCartModal')">Lanjut Belanja</button>
      </div>
    </div>
  </div>

  <!-- Modal Autentikasi (Login / Daftar) -->
  <div class="modal-bg" id="authModal" onclick="closeModal('authModal',event)">
    <div class="modal auth-modal">
      <div class="auth-inner">
        <div class="auth-logo">
          <h2>The Moments</h2>
          <p id="authSubtitle">Masuk untuk melanjutkan belanja</p>
        </div>
        <div class="auth-tabs">
          <button class="auth-tab on" id="tabLogin" onclick="switchAuthTab('login')">Masuk</button>
          <button class="auth-tab" id="tabRegister" onclick="switchAuthTab('register')">Daftar</button>
        </div>
        <!-- Formulir Login -->
        <div id="loginForm">
          <div class="form-group"><label>Email</label><input type="email" id="loginEmail" placeholder="email@kamu.com"></div>
          <div class="form-group">
            <label>Password</label>
            <div class="pass-box">
              <input type="password" id="loginPass" placeholder="••••••••" onkeyup="if(event.key==='Enter') doLogin()">
              <button type="button" class="pass-toggle" aria-label="Tampilkan password" aria-pressed="false" onclick="togglePass('loginPass', this)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"></path><circle cx="12" cy="12" r="3"></circle></svg>
              </button>
            </div>
          </div>
          <button class="auth-submit" onclick="doLogin()">Masuk →</button>
        </div>
        <!-- Formulir Daftar -->
        <div id="registerForm" style="display:none">
          <div class="form-row-2">
            <div class="form-group"><label>Nama Lengkap</label><input type="text" id="regName" placeholder="Nama kamu"></div>
            <div class="form-group"><label>No. Telepon</label><input type="tel" id="regPhone" placeholder="08xx-xxxx"></div>
          </div>
          <div class="form-group"><label>Email</label><input type="email" id="regEmail" placeholder="email@kamu.com"></div>
          <div class="form-group">
            <label>Password</label>
            <div class="pass-box">
              <input type="password" id="regPass" placeholder="Min. 10 karakter" onkeyup="if(event.key==='Enter') doRegister()">
              <button type="button" class="pass-toggle" aria-label="Tampilkan password" aria-pressed="false" onclick="togglePass('regPass', this)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"></path><circle cx="12" cy="12" r="3"></circle></svg>
              </button>
            </div>
          </div>
          <button class="auth-submit" onclick="doRegister()">Buat Akun →</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Detail Produk -->
  <div class="modal-bg" id="detailModal" onclick="closeModal('detailModal',event)">
    <div class="modal detail-modal">
      <div class="detail-layout">
        <div class="detail-img-wrap" id="detailImgWrap"></div>
        <div class="detail-body">
          <div class="detail-cat" id="dCat">Kategori</div>
          <div class="detail-name" id="dName">Nama</div>
          <div class="detail-price" id="dPrice">Rp 0</div>
          <div class="detail-tags" id="dTags"></div>
          <p class="detail-desc" id="dDesc">Deskripsi</p>
          <div class="qty-row">
            <label>Jumlah:</label>
            <div class="qty-ctrl">
              <button class="qbtn" onclick="detQty(-1)">−</button>
              <input type="number" class="qnum-input" id="dQty" value="1" min="1" oninput="setDetailQtyFromInput(this.value)" onblur="validateDetailQtyInput(this.value)">
              <button class="qbtn" onclick="detQty(1)">+</button>
            </div>
            <span class="detail-stock-badge" id="dStockBadge">Stok: 100</span>
          </div>
          <button class="detail-add" id="dAddBtn">Tambah ke Keranjang</button>
          <button class="btn-back" style="width:100%;margin-top:8px;border-radius:12px" onclick="closeModal('detailModal')">← Kembali</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Ulasan Produk -->
  <div class="modal-bg" id="reviewModal" onclick="closeModal('reviewModal',event)">
    <div class="modal review-modal">
      <button type="button" class="modal-x" aria-label="Tutup formulir ulasan" onclick="closeModal('reviewModal')">×</button>
      <div class="review-modal-head">
        <div class="sec-tag">Ulasan Pembelian</div>
        <h3>Tulis ulasan</h3>
        <p id="reviewProductName">Produk</p>
      </div>
      <input type="hidden" id="reviewProductId">
      <div class="review-rating" role="group" aria-label="Pilih rating">
        <button type="button" class="review-star-btn" data-rating="1" aria-label="Rating 1 dari 5" onclick="setReviewRating(1)"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.9-6.2-3.3-6.2 3.3 1.2-6.9-5-4.9 6.9-1L12 2Z"></path></svg></button>
        <button type="button" class="review-star-btn" data-rating="2" aria-label="Rating 2 dari 5" onclick="setReviewRating(2)"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.9-6.2-3.3-6.2 3.3 1.2-6.9-5-4.9 6.9-1L12 2Z"></path></svg></button>
        <button type="button" class="review-star-btn" data-rating="3" aria-label="Rating 3 dari 5" onclick="setReviewRating(3)"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.9-6.2-3.3-6.2 3.3 1.2-6.9-5-4.9 6.9-1L12 2Z"></path></svg></button>
        <button type="button" class="review-star-btn" data-rating="4" aria-label="Rating 4 dari 5" onclick="setReviewRating(4)"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.9-6.2-3.3-6.2 3.3 1.2-6.9-5-4.9 6.9-1L12 2Z"></path></svg></button>
        <button type="button" class="review-star-btn" data-rating="5" aria-label="Rating 5 dari 5" onclick="setReviewRating(5)"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.9-6.2-3.3-6.2 3.3 1.2-6.9-5-4.9 6.9-1L12 2Z"></path></svg></button>
      </div>
      <p class="review-rating-note" id="reviewRatingNote">Pilih rating untuk produk ini.</p>
      <label class="review-comment-label" for="reviewComment">Komentar</label>
      <textarea id="reviewComment" maxlength="1000" rows="4" placeholder="Ceritakan pengalaman Anda dengan produk ini."></textarea>
      <button type="button" class="review-submit" onclick="submitReview()">Kirim Ulasan</button>
    </div>
  </div>

  <!-- Modal Custom Buket -->
  <div class="modal-bg" id="customModal" onclick="closeModal('customModal',event)">
    <div class="modal" style="max-width:500px">
      <div class="pm-body">
        <h2 class="pm-title">Custom Buket Bunga</h2>
        <p style="font-size:13px;color:var(--muted);margin-bottom:20px">Pilih bunga favoritmu untuk dijadikan satu buket cantik!</p>
        <div id="customFlowersList" style="display:flex;flex-direction:column;gap:12px;max-height:300px;overflow-y:auto;padding-right:8px;margin-bottom:20px"></div>
        <div style="border-top:1px solid var(--cream3);padding-top:16px">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
            <span style="font-weight:600">Total Harga:</span>
            <span id="customTotalPrice" style="font-size:20px;font-weight:700;color:var(--sage)">Rp 0</span>
          </div>
          <p style="font-size:11px;color:var(--muted);margin-bottom:12px">*Termasuk jasa rangkai premium Rp 50.000</p>
          <button class="pm-submit" onclick="addCustomToCart()" id="addCustomBtn" disabled>+ Tambahkan ke Keranjang</button>
        </div>
      </div>
    </div>
  </div>


  <!-- Modal Tambah/Edit Produk (Admin) -->
  <div class="modal-bg" id="prodModal" onclick="closeModal('prodModal',event)">
    <div class="modal prod-modal">
      <div class="pm-head">
        <div style="display:flex; align-items:center; gap:14px;">
          <div class="pm-icon-badge">+</div>
          <div>
            <h3 class="pm-title" id="pmTitle">Tambah Produk Baru</h3>
            <p class="pm-subtitle" id="pmSubtitle">Kelola informasi produk, harga, stok, dan tag katalog</p>
          </div>
        </div>
        <button class="pm-close" onclick="closeModal('prodModal')">✕</button>
      </div>
      
      <div class="pm-body">
        <input type="hidden" id="pmId">
        
        <div class="form-row-2">
          <div class="form-group">
            <label>Nama Produk <span class="req">*</span></label>
            <input type="text" id="pmName" placeholder="Contoh: Mawar Merah Premium">
          </div>
          <div class="form-group">
            <label>Kategori <span class="req">*</span></label>
            <select id="pmCat" class="custom-select">
              <option value="mawar"> Mawar</option>
              <option value="tulip"> Tulip</option>
              <option value="buket"> Buket</option>
              <option value="eksotis"> Eksotis</option>
              <option value="tanaman"> Tanaman</option>
              <option value="satuan"> Bunga Satuan (Custom)</option>
            </select>
          </div>
        </div>

        <div class="form-row-2">
          <div class="form-group">
            <label>Harga Normal (Rp) <span class="req">*</span></label>
            <input type="number" id="pmPrice" placeholder="85000">
          </div>
          <div class="form-group">
            <label>Harga Promo (Rp)</label>
            <input type="number" id="pmOldPrice" placeholder="Opsional (misal: 85000)">
          </div>
        </div>

        <div class="form-row-2">
          <div class="form-group">
            <label>Badge Promo</label>
            <select id="pmBadge" class="custom-select">
              <option value="">Tanpa Badge</option>
              <option value="new">Baru (NEW)</option>
              <option value="sale">Diskon (SALE)</option>
              <option value="popular">Populer</option>
            </select>
          </div>
          <div class="form-group">
            <label>Stok Produk</label>
            <input type="number" id="pmStock" placeholder="100">
          </div>
        </div>

        <div class="form-group">
          <label style="font-weight: 600; font-size: 13px; color: var(--ink); margin-bottom: 6px; display: block;">Foto Produk</label>
          <div class="pm-upload-wrapper" style="background: var(--cream, #FAF9F6); border: 1.5px dashed var(--cream3, #E8E5DF); border-radius: 10px; padding: 14px; margin-top: 4px; box-sizing: border-box; width: 100%;">
            <input type="file" id="pmImgFile" accept="image/*" style="display:none" onchange="handleProdImgUpload(event)">
            <input type="hidden" id="pmImageUrl">
            
            <div class="pm-upload-control" style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
              <button type="button" class="pm-btn-upload" onclick="document.getElementById('pmImgFile').click()" style="display: inline-flex; align-items: center; gap: 8px; padding: 9px 18px; background: #ffffff; border: 1.5px solid var(--sage, #5D5B3A); color: var(--sage, #5D5B3A); border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all .2s ease; box-shadow: 0 2px 6px rgba(0,0,0,0.06);">
                Pilih Foto Produk
              </button>
              <span class="pm-upload-hint" id="pmUploadHint" style="font-size: 12px; color: var(--muted, #7A786E); font-weight: 500; word-break: break-word;">Belum ada foto dipilih (Format: PNG, JPG, WEBP)</span>
            </div>

            <div class="pm-img-preview-card" id="pmImgPreviewCard" style="display:none; margin-top: 12px; border-radius: 10px; overflow: hidden; border: 1px solid var(--cream3, #E8E5DF); background: #ffffff; padding: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); box-sizing: border-box; width: 100%; flex-direction: column; gap: 10px;">
              <div class="pm-preview-img-container" style="width: 100%; max-height: 200px; height: 180px; border-radius: 8px; overflow: hidden; background: var(--cream2, #F4F1EA); display: flex; align-items: center; justify-content: center; border: 1px solid var(--cream3, #E8E5DF); box-sizing: border-box;">
                <img id="pmImgPreview" src="" alt="Preview Foto Produk" style="width: 100%; height: 100%; object-fit: cover; display: block; max-width: 100%;">
              </div>
              <div class="pm-preview-info-row" style="display: flex; align-items: center; justify-content: space-between; gap: 10px; width: 100%; flex-wrap: wrap; box-sizing: border-box;">
                <div class="pm-preview-meta" style="display: flex; flex-direction: column; gap: 2px; overflow: hidden; min-width: 0;">
                  <div style="font-size: 12.5px; font-weight: 600; color: var(--ink, #1A1512); text-overflow: ellipsis; overflow: hidden; white-space: nowrap;" id="pmImgFileName">Pratinjau Gambar</div>
                  <div style="font-size: 11px; color: var(--sage, #5D5B3A); font-weight: 500;">✓ Berhasil diunggah</div>
                </div>
                <button type="button" class="pm-btn-remove-img" onclick="removeProdImage()" style="padding: 7px 14px; background: var(--rose3, #FDE8E8); color: var(--rose, #C84B4B); border: 1.5px solid var(--rose3, #FDE8E8); border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; transition: all .2s ease; white-space: nowrap; flex-shrink: 0;">✕ Hapus Foto</button>
              </div>
            </div>

          </div>
        </div>



        <div class="form-group">
          <label>Deskripsi Produk <span class="req">*</span></label>
          <textarea id="pmDesc" rows="3" placeholder="Tuliskan deskripsi lengkap, keunggulan, dan perawatan produk..."></textarea>
        </div>

        <div class="form-group" style="margin-bottom:0">
          <label>Tags Katalog (Pisahkan dengan koma)</label>
          <input type="text" id="pmTags" placeholder="Segar, Lokal, Grade A, Gift">
        </div>

      </div>

      <div class="pm-foot">
        <button class="pm-btn-cancel" onclick="closeModal('prodModal')">Batal</button>
        <button class="pm-submit" onclick="saveProd()">Simpan Produk</button>
      </div>
    </div>
  </div>


  <!-- Modal Invoice Sukses (Setelah Checkout) -->
  <div class="modal-bg" id="orderSuccessModal" onclick="closeModal('orderSuccessModal',event)">
    <div class="modal" style="max-width: 600px; padding: 32px; border-radius: 0; background: var(--cream);">
      <div style="text-align: center; margin-bottom: 24px;">
        <div style="width: 64px; height: 64px; background: var(--sage3); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
          <svg viewBox="0 0 24 24" fill="none" stroke="var(--sage)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="width: 32px; height: 32px;">
            <polyline points="20 6 9 17 4 12"></polyline>
          </svg>
        </div>
        <h2 style="font-family: 'Cormorant Garamond', serif; font-size: 32px; color: var(--ink); margin-bottom: 8px;">Pesanan Berhasil!</h2>
        <p style="font-size: 14px; color: var(--muted);">Terima kasih atas kepercayaan Anda pada The Moments.</p>
      </div>

      <!-- Detail Invoice -->
      <div style="background: var(--cream2); padding: 20px; margin-bottom: 24px; border: 1px solid var(--cream3); border-radius: 0; text-align: left;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 12px; border-bottom: 1px dashed var(--cream3); padding-bottom: 8px;">
          <span style="font-size: 13px; color: var(--muted); font-weight: 500;">No. Invoice</span>
          <span style="font-size: 13px; color: var(--ink); font-weight: 700; font-family: monospace;" id="invNumber">-</span>
        </div>
        
        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
          <span style="font-size: 13px; color: var(--muted);">Penerima</span>
          <span style="font-size: 13px; color: var(--ink); font-weight: 500;" id="invName">-</span>
        </div>
        
        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
          <span style="font-size: 13px; color: var(--muted);">No. Telepon</span>
          <span style="font-size: 13px; color: var(--ink); font-weight: 500;" id="invPhone">-</span>
        </div>

        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
          <span style="font-size: 13px; color: var(--muted);">Metode Pembayaran</span>
          <span style="font-size: 13px; color: var(--ink); font-weight: 500;" id="invPayment">-</span>
        </div>

        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
          <span style="font-size: 13px; color: var(--muted);">Tipe Pengantaran</span>
          <span style="font-size: 13px; color: var(--ink); font-weight: 500;" id="invType">-</span>
        </div>

        <div style="display: flex; justify-content: space-between; margin-bottom: 12px; border-bottom: 1px dashed var(--cream3); padding-bottom: 8px;">
          <span style="font-size: 13px; color: var(--muted);">Waktu Pengantaran</span>
          <span style="font-size: 13px; color: var(--ink); font-weight: 500;" id="invDate">-</span>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 12px;">
          <span style="font-size: 14px; font-weight: 600; color: var(--ink);">Total Tagihan</span>
          <span style="font-size: 18px; font-weight: 700; color: var(--sage);" id="invTotal">Rp 0</span>
        </div>
      </div>

      <div style="font-size: 12px; color: var(--muted); text-align: center; margin-bottom: 24px; line-height: 1.5;">
        Silakan klik tombol di bawah untuk melanjutkan pembayaran dan konfirmasi pesanan via WhatsApp.
      </div>

      <div style="display: flex; gap: 12px;">
        <button onclick="closeModal('orderSuccessModal')" style="flex: 1; padding: 14px; background: transparent; border: 1px solid var(--ink); color: var(--ink); font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; cursor: pointer; transition: all 0.2s;">Ke Beranda</button>
        <button id="waRedirectBtn" style="flex: 1; padding: 14px; background: var(--sage); border: 1px solid var(--sage); color: #fff; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 6px;">Buka WhatsApp</button>
      </div>
    </div>
  </div>

  <!-- Modal Konfirmasi Batalkan Pesanan -->
  <div class="modal-bg" id="confirmCancelModal" onclick="closeModal('confirmCancelModal', event)">
    <div class="modal" style="max-width: 400px;">
      <div style="padding: 36px 32px; text-align: center;">
        <h3 style="font-family: 'Playfair Display', serif; font-size: 22px; font-weight: 600; color: var(--ink); margin-bottom: 10px;">Batalkan Pesanan?</h3>
        <p style="color: var(--muted); font-size: 14px; line-height: 1.6; margin-bottom: 28px;">Pesanan yang dibatalkan tidak dapat dikembalikan. Apakah kamu yakin ingin membatalkan pesanan ini?</p>
        <div style="display: flex; gap: 12px;">
          <button onclick="closeModal('confirmCancelModal')" style="flex: 1; padding: 13px; background: transparent; border: 1.5px solid var(--cream3); border-radius: 10px; font-size: 14px; font-weight: 600; color: var(--muted); cursor: pointer; transition: all .2s;">Kembali</button>
          <button id="confirmCancelBtn" style="flex: 1; padding: 13px; background: var(--rose); border: none; border-radius: 10px; font-size: 14px; font-weight: 600; color: #fff; cursor: pointer; transition: all .2s;">Ya, Batalkan</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Konfirmasi Hapus Produk (Admin) -->
  <div class="modal-bg" id="confirmDeleteProdModal" onclick="closeModal('confirmDeleteProdModal', event)">
    <div class="modal" style="max-width: 420px; border-radius: 12px;">
      <div style="padding: 36px 32px; text-align: center;">
        <div style="width: 56px; height: 56px; background: var(--rose3); color: var(--rose); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; margin: 0 auto 16px;">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
        </div>
        <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 24px; font-weight: 600; color: var(--ink); margin-bottom: 8px;">Hapus Produk?</h3>
        <p style="color: var(--muted); font-size: 13px; line-height: 1.6; margin-bottom: 24px;" id="confirmDeleteProdMsg">Apakah kamu yakin ingin menghapus produk ini dari katalog?</p>
        <div style="display: flex; gap: 12px;">
          <button onclick="closeModal('confirmDeleteProdModal')" style="flex: 1; padding: 12px; background: transparent; border: 1.5px solid var(--cream3); border-radius: 8px; font-size: 13px; font-weight: 600; color: var(--muted); cursor: pointer; transition: all .2s;">Batal</button>
          <button id="confirmDeleteProdBtn" style="flex: 1; padding: 12px; background: var(--rose); border: none; border-radius: 8px; font-size: 13px; font-weight: 600; color: #fff; cursor: pointer; transition: all .2s;">Ya, Hapus</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Toast Notifikasi -->
  <div class="toast" id="toastEl"></div>
