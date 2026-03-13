<?php 
  $title = $attributes['title'] ?? '';
  if (!$title) {
    $is_ko = preg_match('~/ko(/|$)~', $_SERVER['REQUEST_URI'] ?? '');
    $title = $is_ko ? '중소기업 혁신의 실제 사례' : 'Real-World Examples of Small Business Innovation';
  }
  $sec_id = $attributes['sectionId'] ?? 'innovation-examples';
  $heading_id = $sec_id . '-heading';
  $items  = $attributes['items'] ?? [];
?>
<section class="emerging" id="<?php echo esc_attr($sec_id); ?>" aria-labelledby="<?php echo esc_attr($heading_id); ?>">
  <div class="emerging__container">
    <h2 id="<?php echo esc_attr($heading_id); ?>" class="emerging__title">
      <?php echo esc_html($title); ?>
    </h2>

    <div class="emerging__grid">
      <?php foreach ($items as $index => $item): 
        $img = $item['image'] ?? [];
        $src = $img['src'] ?? '';
        $alt = $img['alt'] ?? '';
        $class = $item['className'] ?? 'centerpiece-' . ($index + 1);
      ?>
        <article class="card card--feature <?php echo esc_attr($class); ?>">
          <div class="card__media" aria-hidden="true">
            <?php if ($src): ?>
              <img src="<?php echo esc_url($src); ?>" alt="<?php echo esc_attr($alt); ?>" />
            <?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
