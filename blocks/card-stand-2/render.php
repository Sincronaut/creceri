<section class="emerging" aria-labelledby="emerging-heading">
  <div class="emerging__container">
    <h2 id="emerging-heading" class="emerging__title">
      <?php 
        // Title strings
$text_ko = '중소기업 혁신의 실제 사례';
$text_en = 'Real-World Examples of Small Business Innovation';

// Fallback: sniff URL for `/ko/` segment.
$req = $_SERVER['REQUEST_URI'] ?? '';
echo esc_html(preg_match('~/ko(/|$)~', $req) ? $text_ko : $text_en);
      ?>
    </h2>

    <div class="emerging__grid">
      <!-- 1) Feature / logo -->
      <article class="card card--feature centerpiece-1">
        <div class="card__media" aria-hidden="true">
          <img src="https://creceri.com/wp-content/uploads/2025/10/Screenshot-2025-10-10-102256-1.png" alt="" />
        </div>
      </article>

      <!-- 2) -->
      <article class="card card--feature centerpiece-2">
        <div class="card__media" aria-hidden="true">
          <img src="https://creceri.com/wp-content/uploads/2025/10/Screenshot-2025-10-10-102301-1.png" alt="" />
        </div>
      </article>

      <!-- 3) -->
      <article class="card card--feature centerpiece-3">
        <div class="card__media" aria-hidden="true">
          <img src="https://creceri.com/wp-content/uploads/2025/10/Screenshot-2025-10-10-102326-1.png" alt="" />
        </div>
      </article>

      <!-- 4) -->
      <article class="card card--feature centerpiece-4">
        <div class="card__media" aria-hidden="true">
          <img src="https://creceri.com/wp-content/uploads/2025/10/9d4c66168fca7fec93d4fcd2a3823245e211d20b.png" alt="" />
        </div>
      </article>

      <!-- 5) -->
      <article class="card card--feature centerpiece-5">
        <div class="card__media" aria-hidden="true">
          <img src="https://creceri.com/wp-content/uploads/2025/10/Screenshot-2025-10-10-102335-1.png" alt="" />
        </div>
      </article>

      <!-- CTA to the right of #7 on desktop -->
    </div>
  </div>
</section>
