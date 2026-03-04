<?php
/**
 * Render callback: child/card-check
 */
if ( ! defined('ABSPATH') ) { exit; }
if ( empty($attributes) || ! is_array($attributes) ) { return; }

/* ======================
   Attributes
   ====================== */
$A = $attributes;
$padding_left  = isset($A['padding_left']) ? wp_kses_post($A['padding_left']) : '';
$padding_right = isset($A['padding_right']) ? wp_kses_post($A['padding_right']) : '';
$anchor     = isset($A['anchor']) ? sanitize_title($A['anchor']) : '';
$className  = isset($A['className']) ? sanitize_html_class($A['className']) : '';

$content_tag = isset($A['contentTag']) ? sanitize_text_field($A['contentTag']) : '';
$title       = isset($A['title'])  ? wp_kses_post($A['title'])  : '';
$intro       = isset($A['intro'])  ? wp_kses_post($A['intro']) : '';
$intro1      = isset($A['intro1']) ? wp_kses_post($A['intro1']) : '';
$intro2      = isset($A['intro2']) ? wp_kses_post($A['intro2']) : '';
$intro3      = isset($A['intro3']) ? wp_kses_post($A['intro3']) : '';
$intro4      = (isset($A['intro4']) && is_array($A['intro4'])) ? $A['intro4'] : array();

$background_style = '';
if ( ! empty( $A['background'] ) ) {
  if ( 'on' === strtolower( $A['background'] ) ) {
    $background_style = 'background:linear-gradient(180deg,#CEE6F2 0%,#FFFFFF 80%) !important;';
  } elseif('mid' === strtolower( $A['background'] ) ) {
    $background_style = 'background:linear-gradient(180deg,#E9B796 0%,#FFFFFF 80%) !important;';
  } else {
    $background_style = $A['background'];
  }
}

$reverse     = ! empty($A['reverse']);
$section_id  = isset($A['sectionId']) ? sanitize_title($A['sectionId']) : 'who-we-are';

$btn_name  = isset($A['btnName']) ? sanitize_text_field($A['btnName']) : '';
$btn_url   = isset($A['btnUrl'])  ? esc_url($A['btnUrl']) : '#';
$btn_class = isset($A['btnClass']) ? sanitize_text_field($A['btnClass']) : 'btn btn-pill btn-pill';

$image     = is_array($A['image'] ?? null) ? $A['image'] : array();
$img_src   = isset($image['src']) ? esc_url($image['src']) : '';
$img_alt   = isset($image['alt']) ? esc_attr($image['alt']) : '';
$img_load  = isset($image['loading']) ? esc_attr($image['loading']) : 'lazy';
$img_dec   = isset($image['decoding']) ? esc_attr($image['decoding']) : 'async';

$bullets   = (isset($A['bullets'])  && is_array($A['bullets']))  ? $A['bullets']  : array();
$features  = (isset($A['features']) && is_array($A['features'])) ? $A['features'] : array();

$list_mode = isset($A['listMode']) ? $A['listMode'] : 'auto'; // "auto" | "bullets" | "features"
$list_h3   = isset($A['listHeading']) ? sanitize_text_field($A['listHeading']) : 'What We Share';

$content_style_parts = array();
if ( $padding_left !== '' ) {
  $content_style_parts[] = '--content-pad:' . trim( esc_attr( $padding_left ) );
}
if ( $padding_right !== '' ) {
  $content_style_parts[] = '--content-pad-end:' . trim( esc_attr( $padding_right ) );
}
if ( $content_style_parts ) {
  $style_string = implode( ';', $content_style_parts );
  if ( substr( $style_string, -1 ) !== ';' ) {
    $style_string .= ';';
  }
  $content_style_attr = ' style="' . esc_attr( $style_string ) . '"';
} else {
  $content_style_attr = '';
}

/* ======================
   Which list to render
   ====================== */
$render_features = ($list_mode === 'features') || ($list_mode === 'auto' && !empty($features));
$render_bullets  = ($list_mode === 'bullets')  || ($list_mode === 'auto' && !$render_features && !empty($bullets));

/* ======================
   Icon helper (guarded)
   ====================== */
if ( ! function_exists('child_cc_icon_svg') ) {
  function child_cc_icon_svg($key){
    $stroke = 'currentColor';
    $icons = array(
      'one'      => '<b>1</b>',
      'two'      => '<b>2</b>',
      'three'    => '<b>3</b>',
      'four'     => '<b>4</b>',
      'five'     => '<b>5</b>',
      'book'     => '<img src="https://creceri.com/wp-content/uploads/2025/10/octicon_book-16.png" alt="%s">',
      'platform' => '<img src="https://creceri.com/wp-content/uploads/2025/10/game-icons_platform.png" alt="%s">',
      'ux'       => '<img src="https://creceri.com/wp-content/uploads/2025/10/iconoir_design-nib-solid.png" alt="%s">',
      'growth'   => '<img src="https://creceri.com/wp-content/uploads/2025/10/fluent-mdl2_market.png" alt="%s">',
      'team'     => '<img src="https://creceri.com/wp-content/uploads/2025/10/ri_team-line.png" alt="%s">',
      'check'         => '<img src="https://creceri.com/wp-content/uploads/2025/10/gg_check-o.png" alt="%s">',
      'check-circle'  => '<img src="https://creceri.com/wp-content/uploads/2025/10/gg_check-o.png" alt="%s">'
    );
    return $icons[$key] ?? '';
  }
}

/* ======================
   Classes
   ====================== */
$direction = $reverse ? 'left' : 'right';
$classes = array('who-we-are', $direction);
if ($className) { $classes[] = $className; }
if ($anchor)    { $classes[] = $anchor; }
?>
<section id="<?php echo esc_attr($section_id); ?>"
         class="<?php echo esc_attr(implode(' ', $classes)); ?>"
         <?php echo $background_style ? 'style="' . esc_attr( $background_style ) . '"' : ''; ?>>

  <!-- Inner card: centers, constrains width, adds small radius -->
  <div class="who-we-are__inner">

    <?php if ( $reverse ) : ?>
      <div class="image">
        <?php if ($img_src): ?>
          <img src="<?php echo $img_src; ?>" alt="<?php echo $img_alt; ?>" loading="<?php echo $img_load; ?>" decoding="<?php echo $img_dec; ?>">
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <div class="content" <?php echo $content_style_attr; ?> >
      <?php if ($content_tag !== ''): ?>
        <h2 class="content-tag"><?php echo esc_html($content_tag); ?></h2>
      <?php endif; ?>

      <?php if ($title): ?>
        <p class="title_card"><?php echo $title; ?></p>
      <?php endif; ?>
      <?php if ($intro): ?>
        <p class="intro_strong intro--1"><?php echo $intro; ?></p>
      <?php endif; ?>
      <?php if ($intro1): ?>
        <p class="intro intro--1"><?php echo $intro1; ?></p>
      <?php endif; ?>

      <?php if ($intro2): ?>
        <p class="intro intro--2"><?php echo $intro2; ?></p>
      <?php endif; ?>
      <?php if ($intro3): ?>
        <p class="intro intro--3"><?php echo $intro3; ?></p>
      <?php endif; ?>
      <?php if (!empty($intro4)) : ?>
        <ul class="intro-list intro--4" role="list">
          <?php foreach ($intro4 as $it) { $t = wp_kses_post($it); if ($t==='') continue; ?>
            <li><span class="check-icon"><img src="/wp-content/uploads/2025/10/checkmark-white-icon.webp" alt="" aria-hidden="true"></span><span class="intro-li-text"><?php echo $t; ?></span></li>
          <?php } ?>
        </ul>
      <?php endif; ?>

      <?php if ($render_features): ?>
        <?php if ($list_h3 !== ''): ?><h3 class="cc-subhead"><?php echo esc_html($list_h3); ?></h3><?php endif; ?>
        <ul class="feature-list" role="list">
          <?php foreach ($features as $feat):
            $f_icon  = sanitize_key($feat['icon'] ?? '');
            $f_title = wp_kses_post($feat['title'] ?? '');
            $f_desc  = wp_kses_post($feat['desc']  ?? '');
            if ($f_title === '') { continue; }
          ?>
            <li class="feature-item">
              <span class="feature-icon" aria-hidden="true"><?php echo child_cc_icon_svg($f_icon); ?></span>
              <div class="feature-copy">
                <span class="feature-title"><?php echo $f_title; ?></span>
                <?php if ($f_desc !== ''): ?><span class="feature-desc"><?php echo $f_desc; ?></span><?php endif; ?>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php elseif ($render_bullets): ?>
        <div class="bullet-list">
          <?php foreach ($bullets as $index => $item):
            $text = wp_kses_post($item['text'] ?? '');
            if ($text === '') { continue; }
            $side = ($index % 2 === 0) ? 'left' : 'right';
          ?>
            <div class="bullet-item <?php echo esc_attr($side); ?>">
              <span class="check-icon"><img src="/wp-content/uploads/2025/10/checkmark-white-icon.webp" alt="" aria-hidden="true"></span>
              <p><?php echo $text; ?></p>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if ($btn_name !== ''): ?><br>
        <a href="<?php echo $btn_url; ?>" class="<?php echo esc_attr($btn_class); ?>"><?php echo esc_html($btn_name); ?></a>
      <?php endif; ?>
    </div>

    <?php if ( ! $reverse ) : ?>
      <div class="image">
        <?php if ($img_src): ?>
          <img src="<?php echo $img_src; ?>" alt="<?php echo $img_alt; ?>" loading="<?php echo $img_load; ?>" decoding="<?php echo $img_dec; ?>">
        <?php endif; ?>
      </div>
    <?php endif; ?>

  </div><!-- /.who-we-are__inner1 -->
</section>


