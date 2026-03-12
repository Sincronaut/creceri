<?php
/**
 * Render callback: child/card-check
 */
if (!defined('ABSPATH')) {
  exit;
}
if (empty($attributes) || !is_array($attributes)) {
  return;
}

/* ======================
 Attributes
 ====================== */
$A = $attributes;
$padding_left = isset($A['padding_left']) ? wp_kses_post($A['padding_left']) : '';
$padding_right = isset($A['padding_right']) ? wp_kses_post($A['padding_right']) : '';
$anchor = isset($A['anchor']) ? sanitize_title($A['anchor']) : '';
$className = isset($A['className']) ? sanitize_html_class($A['className']) : '';

$content_tag = isset($A['contentTag']) ? sanitize_text_field($A['contentTag']) : '';
$title = isset($A['title']) ? wp_kses_post($A['title']) : '';
$intro = isset($A['intro']) ? wp_kses_post($A['intro']) : '';
$intro1 = isset($A['intro1']) ? wp_kses_post($A['intro1']) : '';
$intro2 = isset($A['intro2']) ? wp_kses_post($A['intro2']) : '';

$background_style = '';
if (!empty($A['background'])) {
  if ('on' === strtolower($A['background'])) {
    $background_style = 'background:linear-gradient(180deg,#CEE6F2 0%,#FFFFFF 80%) !important;';
  }
  elseif ('mid' === strtolower($A['background'])) {
    $background_style = 'background:linear-gradient(180deg,#E9B796 0%,#FFFFFF 80%) !important;';
  }
  else {
    $background_style = $A['background'];
  }
}

$reverse = !empty($A['reverse']);
$section_id = '';
if (!empty($A['sectionId'])) {
  $section_id = sanitize_title($A['sectionId']);
}
elseif (!empty($anchor)) {
  $section_id = $anchor;
}
else {
  if (function_exists('wp_unique_id')) {
    $section_id = 'who-we-are-approach-' . preg_replace('/[^a-z0-9\-]/', '', strtolower(wp_unique_id()));
  }
  else {
    $section_id = 'who-we-are-approach-' . strtolower(wp_generate_password(6, false, false));
  }
}

$btn_name = isset($A['btnName']) ? sanitize_text_field($A['btnName']) : '';
$btn_url = isset($A['btnUrl']) ? esc_url($A['btnUrl']) : '#';
$btn_class = isset($A['btnClass']) ? sanitize_text_field($A['btnClass']) : 'btn btn-pill btn-pill';

$image = is_array($A['image'] ?? null) ? $A['image'] : array();
$img_src = isset($image['src']) ? esc_url($image['src']) : '';
$img_alt = isset($image['alt']) ? esc_attr($image['alt']) : '';
$img_load = isset($image['loading']) ? esc_attr($image['loading']) : 'lazy';
$img_dec = isset($image['decoding']) ? esc_attr($image['decoding']) : 'async';

$bullets = (isset($A['bullets']) && is_array($A['bullets'])) ? $A['bullets'] : array();
$features = (isset($A['features']) && is_array($A['features'])) ? $A['features'] : array();

$list_mode = isset($A['listMode']) ? $A['listMode'] : 'auto'; // "auto" | "bullets" | "features"
$list_h3 = isset($A['listHeading']) ? sanitize_text_field($A['listHeading']) : 'What We Share';

$content_style_parts = array();
if ($padding_left !== '') {
  $content_style_parts[] = '--content-pad:' . trim(esc_attr($padding_left));
}
if ($padding_right !== '') {
  $content_style_parts[] = '--content-pad-end:' . trim(esc_attr($padding_right));
}
if ($content_style_parts) {
  $style_string = implode(';', $content_style_parts);
  if (substr($style_string, -1) !== ';') {
    $style_string .= ';';
  }
  $content_style_attr = ' style="' . esc_attr($style_string) . '"';
}
else {
  $content_style_attr = '';
}

/* ======================
 Which list to render
 ====================== */
$render_features = ($list_mode === 'features') || ($list_mode === 'auto' && !empty($features));
$render_bullets = ($list_mode === 'bullets') || ($list_mode === 'auto' && !$render_features && !empty($bullets));

/* ======================
 Icon helper (guarded)
 ====================== */
if (!function_exists('child_cc_icon_svg')) {
  function child_cc_icon_svg($key, $alt = '')
  {
    $icons = array(
      'one' => '<b>1</b>',
      'two' => '<b>2</b>',
      'three' => '<b>3</b>',
      'four' => '<b>4</b>',
      'five' => '<b>5</b>',
      'book' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-label="%s"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>',
      'platform' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-label="%s"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>',
      'ux' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-label="%s"><path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><path d="M2 2l7.586 7.586"/><circle cx="11" cy="11" r="2"/></svg>',
      'growth' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-label="%s"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>',
      'team' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-label="%s"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
      'check' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-label="%s"><path d="M20 6L9 17l-5-5"/></svg>',
      'check-circle' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="#ffffff" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-label="%s"><circle cx="12" cy="12" r="10"/><path d="M8 12l3 3 5-5" stroke-width="2"/></svg>'
    );
    if (!isset($icons[$key]))
      return '';
    $tpl = $icons[$key];
    if (strpos($tpl, '%s') !== false) {
      $alt_text = esc_attr($alt);
      return sprintf($tpl, $alt_text);
    }
    return $tpl;
  }
}

/* ======================
 Classes
 ====================== */
$direction = $reverse ? 'is-left' : 'is-right';
$classes = array('who-we-are-approach', $direction, 'who-we-are-approach--' . $section_id);
if ($className) {
  $classes[] = $className;
}
if ($anchor) {
  $classes[] = $anchor;
}
?>
<section id="<?php echo esc_attr($section_id); ?>"
         class="<?php echo esc_attr(implode(' ', $classes)); ?>"
         <?php echo $background_style ? 'style="' . esc_attr($background_style) . '"' : ''; ?>>

  <!-- Inner card: centers, constrains width, adds small radius -->
  <div class="who-we-are-approach__inner">

    <?php if ($reverse): ?>
      <div class="who-we-are-approach__image">
        <?php if ($img_src): ?>
          <img src="<?php echo $img_src; ?>" alt="<?php echo $img_alt; ?>" loading="<?php echo $img_load; ?>" decoding="<?php echo $img_dec; ?>">
        <?php
  endif; ?>
      </div>
    <?php
endif; ?>

    <div class="who-we-are-approach__content" <?php echo $content_style_attr; ?> >
      <?php if ($content_tag !== ''): ?>
        <h2 class="content-tag"><?php echo esc_html($content_tag); ?></h2>
      <?php
endif; ?>

      <?php if ($title): ?>
        <p class="title_card"><?php echo $title; ?></p>
      <?php
endif; ?>
      <?php if ($intro): ?>
        <p class="intro_strong intro--1"><?php echo $intro; ?></p>
      <?php
endif; ?>
      <?php if ($intro1): ?>
        <p class="intro intro--1"><?php echo $intro1; ?></p>
      <?php
endif; ?>

      <?php if ($intro2): ?>
        <p class="intro intro--2"><?php echo $intro2; ?></p>
      <?php
endif; ?>

      <?php if ($render_features): ?>
        <?php if ($list_h3 !== ''): ?><h3 class="cc-subhead"><?php echo esc_html($list_h3); ?></h3><?php
  endif; ?>
        <ul class="feature-list" role="list">
          <?php foreach ($features as $feat):
    $f_icon = sanitize_key($feat['icon'] ?? '');
    $f_title = wp_kses_post($feat['title'] ?? '');
    $f_desc = wp_kses_post($feat['desc'] ?? '');
    if ($f_title === '') {
      continue;
    }
?>
            <li class="feature-item">
              <?php $icon_alt = $f_title ? wp_strip_all_tags($f_title) : ucfirst($f_icon); ?>
              <span class="feature-icon"><?php echo child_cc_icon_svg($f_icon, $icon_alt); ?></span>
              <div class="feature-copy">
                <span class="feature-title"><?php echo $f_title; ?></span>
                <?php if ($f_desc !== ''): ?><span class="feature-desc"><?php echo $f_desc; ?></span><?php
    endif; ?>
              </div>
            </li>
          <?php
  endforeach; ?>
        </ul>
      <?php
elseif ($render_bullets): ?>
        <div class="bullet-list">
          <?php foreach ($bullets as $index => $item):
    $text = wp_kses_post($item['text'] ?? '');
    if ($text === '') {
      continue;
    }
    $side = ($index % 2 === 0) ? 'left' : 'right';
?>
            <div class="bullet-item <?php echo esc_attr($side); ?>">
              <span class="check-icon"><?php echo child_cc_icon_svg('check-circle', ''); ?></span>
              <p><?php echo $text; ?></p>
            </div>
          <?php
  endforeach; ?>
        </div>
      <?php
endif; ?>

      <?php if ($btn_name !== ''): ?><br>
        <a href="<?php echo $btn_url; ?>" class="btn-pill <?php echo esc_attr($btn_class); ?>"><?php echo esc_html($btn_name); ?> </a>
      <?php
endif; ?>
    </div>

    <?php if (!$reverse): ?>
      <div class="who-we-are-approach__image">
        <?php if ($img_src): ?>
          <img src="<?php echo $img_src; ?>" alt="<?php echo $img_alt; ?>" loading="<?php echo $img_load; ?>" decoding="<?php echo $img_dec; ?>">
        <?php
  endif; ?>
      </div>
    <?php
endif; ?>

  </div><!-- /.who-we-are__inner -->
</section>


