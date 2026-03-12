<?php
/**
 * Render callback: child/card-check-4
 */
if ( ! defined('ABSPATH') ) { exit; }
if ( empty($attributes) || ! is_array($attributes) ) { return; }

$A = $attributes;

$anchor     = isset($A['anchor']) ? sanitize_title($A['anchor']) : '';
$className  = isset($A['className']) ? sanitize_html_class($A['className']) : '';

$title  = isset($A['title'])  ? wp_kses_post($A['title'])  : '';
$intro1 = isset($A['intro1']) ? wp_kses_post($A['intro1']) : '';

$reverse     = ! empty($A['reverse']);
$section_id  = isset($A['sectionId']) ? sanitize_title($A['sectionId']) : 'team-extension-model';

$btn_name  = isset($A['btnName']) ? sanitize_text_field($A['btnName']) : '';
$btn_url   = isset($A['btnUrl'])  ? esc_url($A['btnUrl']) : '#';
$btn_class = isset($A['btnClass']) ? sanitize_text_field($A['btnClass']) : 'btn btn-pill btn-custom';

$image     = is_array($A['image'] ?? null) ? $A['image'] : array();
$img_src   = isset($image['src']) ? esc_url($image['src']) : '';
$img_alt   = isset($image['alt']) ? esc_attr($image['alt']) : '';
$img_load  = isset($image['loading']) ? esc_attr($image['loading']) : 'lazy';
$img_dec   = isset($image['decoding']) ? esc_attr($image['decoding']) : 'async';

$works    = (isset($A['works'])    && is_array($A['works']))    ? $A['works']    : array();
$useCases = (isset($A['useCases']) && is_array($A['useCases'])) ? $A['useCases'] : array();
$benefits = (isset($A['benefits']) && is_array($A['benefits'])) ? $A['benefits'] : array();

/** NEW: configurable H3 subheads with safe defaults */
$works_heading    = isset($A['worksHeading'])    ? sanitize_text_field($A['worksHeading'])    : 'How It Works';
$usecases_heading = isset($A['useCasesHeading']) ? sanitize_text_field($A['useCasesHeading']) : 'Ideal Use Cases';
$benefits_heading = isset($A['benefitsHeading']) ? sanitize_text_field($A['benefitsHeading']) : 'What You Get';

$cta_rendered_inline = false;

if ( ! function_exists('child_cc4_arrow_svg') ) {
  function child_cc4_arrow_svg(){
    return '<svg class="cc-arrow-svg" width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h12M13 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
  }
}

$direction = $reverse ? 'left' : 'right';
$classes = array('who-we-are', $direction);
if ($className) { $classes[] = $className; }
if ($anchor)    { $classes[] = $anchor; }
?>
<section id="<?php echo esc_attr($section_id); ?>"
         class="<?php echo esc_attr(implode(' ', $classes)); ?>">
  <div class="who-we-are__inner">

    <?php if ( $reverse ) : ?>
      <div class="image">
        <?php if ($img_src): ?>
          <img src="<?php echo $img_src; ?>" alt="<?php echo $img_alt; ?>" loading="<?php echo $img_load; ?>" decoding="<?php echo $img_dec; ?>">
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <div class="content">
      <?php if ($title): ?>
        <h4 class="title_card"><?php echo $title; ?></h4>
      <?php endif; ?>
      <?php if ($intro1): ?>
        <p class="intro"><?php echo $intro1; ?></p>
      <?php endif; ?>

      <div class="cc4-columns" role="group" aria-label="Key details">
        <div class="cc4-col">
          <h3 class="cc-subhead cc-subhead--accent"><?php echo esc_html($works_heading); ?></h3>
          <?php if (!empty($works)): ?>
            <ul class="feature-list" role="list">
              <?php foreach ($works as $w): $t = wp_kses_post(is_array($w)? ($w['text'] ?? '') : $w); if (trim(wp_strip_all_tags($t))==='') continue; ?>
                <li class="feature-item">
                  <span class="feature-icon" aria-hidden="true">•</span>
                  <div class="feature-copy"><span class="feature-desc"><?php echo $t; ?></span></div>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>

        <div class="cc4-col">
          <h3 class="cc-subhead cc-subhead--accent"><?php echo esc_html($usecases_heading); ?></h3>
          <?php if (!empty($useCases)): ?>
            <ul class="feature-list" role="list">
              <?php foreach ($useCases as $u): $t = wp_kses_post(is_array($u)? ($u['text'] ?? '') : $u); if (trim(wp_strip_all_tags($t))==='') continue; ?>
                <li class="feature-item">
                  <span class="feature-icon" aria-hidden="true">•</span>
                  <div class="feature-copy"><span class="feature-desc"><?php echo $t; ?></span></div>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      </div>

      <?php if (!empty($benefits)): ?>
        <div class="cc4-benefits">
          <div class="cc4-headrow">
            <h3 class="cc-subhead cc-subhead--accent"><?php echo esc_html($benefits_heading); ?></h3>
          </div>
          <ul class="feature-list" role="list">
          <?php foreach ($benefits as $b): $t = wp_kses_post(is_array($b)? ($b['text'] ?? '') : $b); if (trim(wp_strip_all_tags($t))==='') continue; ?>
            <li class="feature-item">
              <span class="feature-icon" aria-hidden="true">•</span>
              <div class="feature-copy"><span class="feature-desc"><?php echo $t; ?></span></div>
            </li>
          <?php endforeach; ?>
          </ul>
          <?php if ($btn_name !== ''): $cta_rendered_inline = true; ?>
            <div class="cc4-cta-float">
              <a href="<?php echo $btn_url; ?>" class="<?php echo esc_attr($btn_class); ?>">
                <span class="btn-label"><?php echo esc_html($btn_name); ?></span>
                <span class="btn-arrow" aria-hidden="true"><?php echo child_cc4_arrow_svg(); ?></span>
              </a>
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <?php if ($btn_name !== '' && !$cta_rendered_inline): ?>
        <div class="cc4-cta feature-cta">
          <a href="<?php echo $btn_url; ?>" class="<?php echo esc_attr($btn_class); ?>">
            <span class="btn-label"><?php echo esc_html($btn_name); ?></span>
            <span class="btn-arrow" aria-hidden="true"><?php echo child_cc4_arrow_svg(); ?></span>
          </a>
        </div>
      <?php endif; ?>
    </div>

    <?php if ( ! $reverse ) : ?>
      <div class="image">
        <?php if ($img_src): ?>
          <img src="<?php echo $img_src; ?>" alt="<?php echo $img_alt; ?>" loading="<?php echo $img_load; ?>" decoding="<?php echo $img_dec; ?>">
        <?php endif; ?>
      </div>
    <?php endif; ?>

  </div>
</section>
