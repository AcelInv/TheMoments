<div class="dash-panel" id="panelAdminReports">
  <div class="panel-title">Laporan</div>

  <!-- Tab bar -->
  <div class="rep-tabs" id="repTabs">
    <button class="rep-tab on" id="rtab-sales"    onclick="switchReport('sales')">Penjualan</button>
    <button class="rep-tab"    id="rtab-finance"  onclick="switchReport('finance')">Keuangan</button>
    <button class="rep-tab"    id="rtab-trend"    onclick="switchReport('trend')">Tren Musiman</button>
  </div>

  <!-- ── Panel: Penjualan ── -->
  <div class="rep-section" id="rep-sales">
    <div class="rep-kpi-row" id="repSalesKpi"></div>
    <div class="rep-chart-wrap">
      <div class="rep-chart-title">Tren Penjualan (Per Status)</div>
      <div id="repSalesChart" class="rep-bar-chart"></div>
    </div>
    <div class="rep-sub-title">Detail Pesanan Selesai</div>
    <div class="table-wrap">
      <table class="data-table">
        <thead><tr><th>No. Pesanan</th><th>Pelanggan</th><th>Items</th><th>Total</th><th>Tanggal</th></tr></thead>
        <tbody id="repSalesBody"></tbody>
      </table>
    </div>
  </div>

  <!-- ── Panel: Keuangan ── -->
  <div class="rep-section" id="rep-finance" style="display:none">
    <div class="rep-kpi-row" id="repFinKpi"></div>
    <div class="rep-chart-wrap">
      <div class="rep-chart-title">Ringkasan Pembayaran</div>
      <div id="repFinChart" class="rep-bar-chart"></div>
    </div>
    <div class="rep-sub-title">Detail Pembayaran</div>
    <div class="table-wrap">
      <table class="data-table">
        <thead><tr><th>No. Pesanan</th><th>Pelanggan</th><th>Total</th><th>Status Bayar</th><th>Metode</th><th>Tanggal</th></tr></thead>
        <tbody id="repFinBody"></tbody>
      </table>
    </div>
  </div>

  <!-- ── Panel: Tren Musiman ── -->
  <div class="rep-section" id="rep-trend" style="display:none">
    <!-- Header with year selector -->
    <div class="trend-header">
      <div class="trend-header-left">
        <div class="trend-main-title">Analisis Tren Penjualan Musiman</div>
        <div class="trend-subtitle">Pola lonjakan penjualan berdasarkan momen spesial sepanjang tahun</div>
      </div>
      <div class="trend-controls">
        <select class="trend-year-select" id="trendYearSelect" onchange="loadSalesTrend(this.value)">
          <option>Memuat...</option>
        </select>
        <select class="trend-metric-select" id="trendMetricSelect" onchange="renderTrendChart()">
          <option value="jumlah_pesanan">Jumlah Pesanan</option>
          <option value="jumlah_item">Jumlah Item</option>
          <option value="total_penjualan">Total Penjualan (Rp)</option>
        </select>
      </div>
    </div>

    <!-- KPI summary -->
    <div class="rep-kpi-row" id="trendKpi"></div>

    <!-- Line Chart Canvas -->
    <div class="trend-chart-container">
      <div class="trend-chart-inner">
        <canvas id="trendCanvas" width="900" height="420"></canvas>
      </div>
      <!-- Seasonal event legend -->
      <div class="trend-legend" id="trendLegend"></div>
    </div>

    <!-- Event Insights -->
    <div class="trend-insights" id="trendInsights"></div>

    <!-- Top Products -->
    <div class="rep-sub-title" style="margin-top:28px">Produk Terlaris Tahun Ini</div>
    <div class="table-wrap">
      <table class="data-table">
        <thead><tr><th>Produk</th><th>Qty Terjual</th><th>Revenue</th></tr></thead>
        <tbody id="trendTopBody"></tbody>
      </table>
    </div>
  </div>

  <!-- ── Panel: Perlu Diambil ── -->
  <!-- ── Panel: Perlu Diantar ── -->
</div>
