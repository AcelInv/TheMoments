  <div class="screen active" id="homeScreen">

    <!-- HERO -->
    <section class="hero" id="heroSec">
      <div class="hero-text">
        <div class="sec-tag"
          style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.2)">Toko Bunga
          Premium</div>
        <h1>Beautifully <em>Curated</em><br>Blooms for You</h1>
        <p>Premium floral arrangements delivered to your doorstep. Fresh from the farm, designed with elegance.</p>
        <div class="hero-btns">
          <button class="btn-primary" onclick="scrollSection('katalog')">Shop Collection</button>
          <button class="btn-ghost" onclick="scrollSection('cabang')">Our Locations</button>
        </div>
      </div>
    </section>

    <div class="home-trust-strip" aria-label="Keunggulan The Moments">
      <span>Bunga segar pilihan</span>
      <span>Dirangkai dengan detail</span>
      <span>Terpercaya</span>
    </div>

    <!-- KATALOG -->
    <?php include __DIR__ . '/katalog.php'; ?>

    <!-- FEATURED -->
    <?php include __DIR__ . '/featured.php'; ?>

    <!-- CABANG / MAP -->
    <?php include __DIR__ . '/cabang.php'; ?>

    <!-- ULASAN PELANGGAN -->
    <?php include __DIR__ . '/testimonials.php'; ?>

    <!-- FOOTER -->
    <?php include __DIR__ . '/footer.php'; ?>

  </div><!-- end homeScreen -->
