<div class="dash-panel" id="panelAdminOrders">
  <div class="dash-section-head">
    <div class="panel-title" style="margin:0">Kelola Pesanan</div>
    <div class="search-bar"><input type="text" placeholder="Cari pesanan..." id="orderSearch" oninput="renderAdminOrders()"></div>
  </div>
  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th>No. Pesanan</th>
          <th>Pelanggan</th>
          <th>Items</th>
          <th>Total</th>
          <th>Bayar</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody id="adminOrdersBody"></tbody>
    </table>
  </div>
</div>
