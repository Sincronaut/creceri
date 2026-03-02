<?php
if ( ! defined('ABSPATH') ) { exit; }
if ( empty($attributes) || ! is_array($attributes) ) { return; }

$A = $attributes;

$anchor     = isset($A['anchor']) ? sanitize_title($A['anchor']) : '';
$className  = isset($A['className']) ? sanitize_html_class($A['className']) : '';
$section_id = '';
if ( ! empty( $A['sectionId'] ) ) {
  $section_id = sanitize_title( $A['sectionId'] );
} elseif ( ! empty( $anchor ) ) {
  $section_id = $anchor;
} else {
  if ( function_exists('wp_unique_id') ) {
    $section_id = 'ecom-security-' . preg_replace('/[^a-z0-9\-]/', '', strtolower( wp_unique_id() ) );
  } else {
    $section_id = 'ecom-security-' . strtolower( wp_generate_password( 6, false, false ) );
  }
}

$content_tag = isset($A['contentTag']) ? wp_kses_post($A['contentTag']) : '';
$title       = isset($A['title'])      ? wp_kses_post($A['title'])      : '';
$intro1      = isset($A['intro1'])     ? wp_kses_post($A['intro1'])     : '';
$intro2      = isset($A['intro2'])     ? wp_kses_post($A['intro2'])     : '';

$image_position = isset($A['imagePosition']) ? sanitize_text_field($A['imagePosition']) : '';
$li_gap         = isset($A['liGap']) ? trim(wp_kses_post($A['liGap'])) : '';

if ($image_position === '' && !empty($A['reverse'])) { $image_position = 'left'; }
$image_position = ($image_position === 'right') ? 'right' : 'left';

$image   = is_array($A['image'] ?? null) ? $A['image'] : array();
$img_src = isset($image['src']) ? esc_url($image['src']) : '';
$img_alt = isset($image['alt']) ? esc_attr($image['alt']) : '';
$img_load= isset($image['loading']) ? esc_attr($image['loading']) : 'lazy';
$img_dec = isset($image['decoding']) ? esc_attr($image['decoding']) : 'async';

$bullets = (isset($A['bullets']) && is_array($A['bullets'])) ? $A['bullets'] : array();

/* Split for desktop parity; tablet/mobile collapses automatically via CSS */
$left_items = $right_items = array();
$half = (int) ceil(count($bullets) / 2);
$left_items  = array_slice($bullets, 0, $half);
$right_items = array_slice($bullets, $half);

$classes = array('ecom-security', 'ecom-security--' . $section_id);
if ($className) { $classes[] = $className; }
if ($anchor)    { $classes[] = $anchor; }

$style_attr = ($li_gap !== '') ? ' style="--li-gap: ' . esc_attr($li_gap) . ';"' : '';
?>
<section id="<?php echo esc_attr($section_id); ?>" class="<?php echo esc_attr(implode(' ', $classes)); ?>">
  <div class="wrap">
    <div class="security-grid" data-visual="<?php echo esc_attr($image_position); ?>"<?php echo $style_attr; ?>>
      <?php if ($img_src): ?>
        <figure class="security-visual" style="border-radius:20px !important;">
          <img  src="<?php echo $img_src; ?>" alt="<?php echo $img_alt; ?>" loading="<?php echo $img_load; ?>" decoding="<?php echo $img_dec; ?>">
        </figure>
      <?php endif; ?>

      <div class="security-header">
        <?php if ($content_tag !== ''): ?>
          <h2><?php echo $content_tag; ?></h2>
        <?php elseif ($title !== ''): ?>
          <h2><?php echo $title; ?></h2>
        <?php endif; ?>
        <?php if ($intro1 !== ''): ?><p class="subhead"><?php echo $intro1; ?></p><?php endif; ?>
        <?php if ($intro2 !== ''): ?><p class="subhead"><?php echo $intro2; ?></p><?php endif; ?>
      </div>

      <div class="features-grid">
        <?php if ($left_items): ?>
          <ul class="feature-col left" role="list">
            <?php foreach ($left_items as $item){
              $text = wp_kses_post($item['text'] ?? '');
              if ($text === '') { continue; } ?>
              <li><?php echo $text; ?></li>
            <?php } ?>
          </ul>
        <?php endif; ?>

        <?php if ($right_items): ?>
          <ul class="feature-col right" role="list">
            <?php foreach ($right_items as $item){
              $text = wp_kses_post($item['text'] ?? '');
              if ($text === '') { continue; } ?>
              <li><?php echo $text; ?></li>
            <?php } ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
