<?php
/**
 * Block: Section · Card Image Animation
 * Dynamic render for child/card-stand
 */

$title = $attributes['title'] ?? 'What’s Emerging';
$intro = $attributes['intro'] ?? '';
$items = $attributes['items'] ?? [];
$anchor = $attributes['anchor'] ?? 'emerging-heading';

// Design defaults
$cta_text = $attributes['readLinkText'] ?? 'Learn More';
?>
<section class="emerging" aria-labelledby="<?php echo esc_attr($anchor); ?>" id="<?php echo esc_attr($anchor); ?>">
  <div class="emerging__container">
    <h2 id="<?php echo esc_attr($anchor); ?>" class="emerging__title"><?php echo esc_html($title); ?></h2>
    <?php if ($intro): ?>
      <p class="emerging__intro" style="text-align:center; max-width:600px; margin: 0 auto 40px; color: var(--muted);"><?php echo esc_html($intro); ?></p>
    <?php endif; ?>

    <div class="emerging__grid">
      <?php 
      $count = count($items);
      foreach ( $items as $index => $item ) :
        $it_title = $item['title'] ?? '';
        $it_text  = $item['text']  ?? '';
        $it_url   = $item['url']   ?? '#';
        
        // Special class for the last item (centerpiece) if there are 7 items as per original design
        $is_centerpiece = ($index === $count - 1);
        $card_class = 'card' . ($is_centerpiece ? ' card--centerpiece' : '');
      ?>
        <article class="<?php echo esc_attr($card_class); ?>">
          <h3 class="card__title"><?php echo esc_html($it_title); ?></h3>
          <p class="card__copy"><?php echo esc_html($it_text); ?></p>
        </article>
      <?php endforeach; ?>
      
      <?php if ($count > 0): ?>
      <div class="emerging__cta">
        <a class="btn btn-pill" href="<?php echo esc_url($items[0]['url'] ?? '#'); ?>" aria-label="<?php echo esc_attr($cta_text); ?>">
          <?php echo esc_html($cta_text); ?> →
        </a>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>
