    <div id="katalog">
      <div class="section">
        <div class="sec-header reveal">
          <div>
            <h2 class="sec-title">The <em>Collection</em></h2>
            <p class="sec-sub">Explore our range of premium floral designs.</p>
          </div>
          <button class="view-all-btn" id="viewAllBtn" onclick="openFullCatalog()">
            <span id="viewAllTxt">Lihat Semua</span> <span id="viewAllIcon">→</span>
          </button>
        </div>
        <div class="filter-bar" id="filterBar">
          <button class="fbtn on" onclick="filterProd('semua',this)">Semua</button>
          <button class="fbtn" onclick="filterProd('mawar',this)">Mawar</button>
          <button class="fbtn" onclick="filterProd('tulip',this)">Tulip</button>
          <button class="fbtn" onclick="filterProd('buket',this)">Buket</button>
          <button class="fbtn" onclick="filterProd('eksotis',this)">Eksotis</button>
          <button class="fbtn" onclick="filterProd('tanaman',this)">Tanaman</button>
        </div>
        <div class="prod-grid" id="prodGrid"></div>
      </div>
    </div>
