<div class="dash-panel" id="panelProfile">
  <div class="panel-title">Profil Saya</div>
  <div class="profile-card">
    <div class="profile-av-row">
      <div class="profile-av" id="profileAv">U</div>
      <div>
        <div style="font-weight:700;font-size:16px" id="profileName">User</div>
        <div style="font-size:12px;color:var(--muted);margin-top:2px" id="profileEmail">email</div>
        <span class="dash-role" id="profileRole" style="margin-top:6px;display:inline-block">User</span>
      </div>
    </div>
    <div class="form-group">
      <label>Nama Lengkap</label>
      <input type="text" id="editName" placeholder="Nama lengkap">
    </div>
    <div class="form-group">
      <label>Email</label>
      <input type="email" id="editEmail" placeholder="Email" readonly style="background:var(--cream2)">
    </div>
    <div class="form-group">
      <label>No. Telepon</label>
      <input type="tel" id="editPhone" placeholder="08xx-xxxx-xxxx">
    </div>
    <div class="form-group">
      <label>Password Baru (kosongkan jika tidak diubah)</label>
      <div class="pass-box">
        <input type="password" id="editPass" placeholder="Password baru">
        <button type="button" class="pass-toggle" aria-label="Tampilkan password" aria-pressed="false" onclick="togglePass('editPass', this)">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"></path><circle cx="12" cy="12" r="3"></circle></svg>
        </button>
      </div>
    </div>
    <button class="save-btn" onclick="saveProfile()">Simpan Perubahan</button>
  </div>
</div>
