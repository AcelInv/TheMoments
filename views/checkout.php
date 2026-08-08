  <div class="screen" id="checkoutScreen">
    <div class="section" style="padding-top:100px; max-width: 1200px; margin: 0 auto;">
      <button class="btn-ghost" onclick="goHome()" style="padding:10px 20px;border-radius:50px;margin-bottom:20px">←
        Kembali ke Beranda</button>

      <div class="checkout-layout">
        <div class="co-left">
          <h2 class="co-title">Detail Pengiriman &amp; Pembayaran</h2>

          <div class="co-sec-title">Informasi Pemesan:</div>
          <div class="co-user-card" id="coUserCard">
            <div class="co-user-avatar" id="coUserAvatar">U</div>
            <div class="co-user-info">
              <div class="co-user-name" id="coUserName">—</div>
              <div class="co-user-meta">
                <span id="coUserPhone">—</span>
                <span class="co-user-sep">·</span>
                <span id="coUserEmail">—</span>
              </div>
            </div>
            <div class="co-user-badge">Terverifikasi ✓</div>
          </div>

          <!-- hidden inputs still used by doPaymentNew() -->
          <input type="hidden" id="coName">
          <input type="hidden" id="coPhone">
          <input type="hidden" id="coEmail">

          <div class="co-group" style="margin-bottom:24px">
            <label>Tanggal &amp; Jam Pengantaran/Pengambilan*</label>
            <input type="datetime-local" id="coDate">
          </div>

          <!-- Hidden input for coAddress compatibility -->
          <input type="hidden" id="coAddress" value="">


          <div class="co-group">
            <label>Catatan Pesanan / Isi Kartu Ucapan (Opsional)</label>
            <textarea id="coNote" rows="3" placeholder="Tulis pesan untuk penerima atau catatan khusus lainnya."></textarea>
          </div>

        </div>

        <div class="co-right">
          <table class="co-items-table">
            <thead>
              <tr>
                <th>Produk</th>
                <th>Nama</th>
                <th>Harga</th>
                <th>Qty</th>
                <th>Total</th>
              </tr>
            </thead>
            <tbody id="coItemsBody"></tbody>
          </table>

          <div class="co-summary-box">
            <h3 class="co-summary-title">Total Pesanan</h3>
            <div class="co-sum-row">
              <span>Subtotal (Harga Awal):</span>
              <span id="coSubtotal">Rp0</span>
            </div>
            <div class="co-sum-row">
              <span>Subtotal (Setelah Diskon):</span>
              <span id="coSubtotalDisc">Rp0</span>
            </div>
            <div class="co-sum-row">
              <span>Pengiriman:</span>
              <div style="text-align:right">
                <div id="coShipCost">Konsultasi via WA</div>
              </div>
            </div>
            <div class="co-sum-total">
              <span>TOTAL</span>
              <span id="coTotal">Rp0</span>
            </div>

            <button class="co-btn-submit" id="coSubmitBtn" type="button" onclick="doPaymentNew()">Buat Pesanan</button>
          </div>
        </div>
      </div>
    </div>
  </div>
