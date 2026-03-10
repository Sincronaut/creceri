<?php
/**
 * Server-rendered block: Choice Compare
 * Enhancements:
 * - Per-card bullet toggle: cards[n].hasBullets = yes|no|true|false|1|0 (case/space-insensitive)
 * - Column bullet toggle: firstCardHasBullets / secondCardHasBullets (same parsing)
 * - Global bullets:      bulletStyle = "none" | "circle" | "check"   <-- added "none"
 * - First column override: firstColumnBulletStyle = "inherit" | "none" | "circle" | "check"
 * - Per-card override:   cards[n].bulletStyle   = "inherit" | "none" | "circle" | "check"
 * - Item model (more permissive):
 *      string
 *      { text }
 *      { title }
 *      { description }
 *      { title, description }
 *   Items render even if only one of title/description is present.
 * - CTA toggle:          showCta = bool (robust string parsing)
 * - Section description: description under title
 */

if (!defined('ABSPATH')) {
  exit;
}

$A = is_array($attributes ?? null) ? $attributes : array();

/** ---------------------------------------------------------------------------
 * Utilities
 * ------------------------------------------------------------------------- */
$parse_bool = static function ($v, $default = false) {
  if (is_bool($v))
    return $v;
  if (is_numeric($v))
    return ((int)$v) !== 0;
  if (!is_string($v))
    return (bool)$default;
  $s = strtolower(trim($v));
  if ($s === '')
    return (bool)$default;
  $truthy = array('1', 'true', 'yes', 'y', 'on', 'enable', 'enabled');
  $falsy = array('0', 'false', 'no', 'n', 'off', 'disable', 'disabled');
  if (in_array($s, $truthy, true))
    return true;
  if (in_array($s, $falsy, true))
    return false;
  return (bool)$default;
};

$sanitize_nonempty = static function ($val) {
  $str = is_string($val) ? trim(wp_strip_all_tags($val)) : '';
  return $str !== '' ? wp_kses_post($val) : '';
};

/** ---------------------------------------------------------------------------
 * Defaults
 * ------------------------------------------------------------------------- */
$title_default = __('When to Choose CMS vs. Custom Development', 'vite-ttf-child-creceri');

$cards_default = array(
    array(
    'heading' => __('Choose a CMS If&hellip;', 'vite-ttf-child-creceri'),
    'items' => array(
        array('title' => __('Emails or contacts becoming inaccessible during repairs', 'vite-ttf-child-creceri')),
        array('title' => __('Corrupted folders when errors aren&rsquo;t handled properly', 'vite-ttf-child-creceri')),
        array('title' => __('Lost drafts or unsaved changes after crashes', 'vite-ttf-child-creceri')),
        array('title' => __('Need a fast launch with built-in tools and templates', 'vite-ttf-child-creceri')),
    ),
    'ctaText' => __('Explore CMS Development Guides', 'vite-ttf-child-creceri'),
    'ctaUrl' => '#',
  ),
    array(
    'heading' => __('Choose Custom Development If&hellip;', 'vite-ttf-child-creceri'),
    'items' => array(
        array('title' => __('You require complex workflows or integrations', 'vite-ttf-child-creceri')),
        array('title' => __('Your design needs go beyond available templates', 'vite-ttf-child-creceri')),
        array('title' => __('You expect to scale with unique functionality', 'vite-ttf-child-creceri')),
        array('title' => __('Security and performance must be highly customised', 'vite-ttf-child-creceri')),
    ),
    'ctaText' => __('Explore Custom Development Guides', 'vite-ttf-child-creceri'),
    'ctaUrl' => '#',
  ),
);

/** ---------------------------------------------------------------------------
 * Title + description
 * ------------------------------------------------------------------------- */
$title_has_value = isset($A['title']) && is_string($A['title']) && trim(wp_strip_all_tags($A['title'])) !== '';
$title = $title_has_value ? wp_kses_post($A['title']) : $title_default;

$desc_raw = isset($A['description']) && is_string($A['description']) ? $A['description'] : '';
$description = trim(wp_strip_all_tags($desc_raw)) !== '' ? wp_kses_post($desc_raw) : '';

/** ---------------------------------------------------------------------------
 * Cards normalization (more permissive)
 * ------------------------------------------------------------------------- */
$cards_raw = isset($A['cards']) && is_array($A['cards']) ? $A['cards'] : array();
$cards = array();

foreach ($cards_raw as $card_raw) {
  if (!is_array($card_raw))
    continue;

  $heading = $sanitize_nonempty($card_raw['heading'] ?? '');

  $items = array();
  $items_raw = isset($card_raw['items']) && is_array($card_raw['items']) ? $card_raw['items'] : array();

  foreach ($items_raw as $item_raw) {
    if (is_array($item_raw)) {
      $t = $item_raw['title'] ?? ($item_raw['text'] ?? '');
      $d = $item_raw['description'] ?? ($item_raw['desc'] ?? '');
    }
    else {
      $t = $item_raw;
      $d = '';
    }

    $t_clean = is_string($t) ? trim(wp_strip_all_tags($t)) : '';
    $d_clean = is_string($d) ? trim(wp_strip_all_tags($d)) : '';

    if ($t_clean !== '' || $d_clean !== '') {
      $items[] = array(
        'title' => $t_clean !== '' ? wp_kses_post($t) : '',
        'description' => $d_clean !== '' ? wp_kses_post($d) : '',
      );
    }
  }

  $cta_text_raw = is_string($card_raw['ctaText'] ?? '') ? $card_raw['ctaText'] : '';
  $cta_url_raw = is_string($card_raw['ctaUrl'] ?? '') ? $card_raw['ctaUrl'] : '';

  // Bullet config on card
  $card_bullet_style = 'inherit';
  if (isset($card_raw['bulletStyle']) && in_array($card_raw['bulletStyle'], array('inherit', 'none', 'circle', 'check'), true)) {
    $card_bullet_style = $card_raw['bulletStyle'];
  }

  $card_has_bullets = null;
  if (array_key_exists('hasBullets', $card_raw)) {
    $card_has_bullets = $parse_bool($card_raw['hasBullets'], true);
  }

  if ($heading === '' && empty($items) && $cta_text_raw === '')
    continue;

  $cards[] = array(
    'heading' => $heading,
    'items' => $items,
    'ctaText' => wp_kses_post($cta_text_raw),
    'ctaUrl' => esc_url($cta_url_raw ?: '#'),
    'bulletStyle' => $card_bullet_style,
    'hasBullets' => $card_has_bullets, // null means "no preference"
  );
}

if (empty($cards)) {
  $cards = array_map(
  static function ($card_default) {
    $normalized_items = array();
    foreach ($card_default['items'] as $it) {
      if (is_array($it)) {
        $t = $it['title'] ?? ($it['text'] ?? '');
        $d = $it['description'] ?? ($it['desc'] ?? '');
      }
      else {
        $t = $it;
        $d = '';
      }
      $t_clean = is_string($t) ? trim(wp_strip_all_tags($t)) : '';
      $d_clean = is_string($d) ? trim(wp_strip_all_tags($d)) : '';
      if ($t_clean !== '' || $d_clean !== '') {
        $normalized_items[] = array(
          'title' => $t_clean !== '' ? wp_kses_post($t) : '',
          'description' => $d_clean !== '' ? wp_kses_post($d) : '',
        );
      }
    }

    $card_bullet_style = 'inherit';
    if (isset($card_default['bulletStyle']) && in_array($card_default['bulletStyle'], array('inherit', 'none', 'circle', 'check'), true)) {
      $card_bullet_style = $card_default['bulletStyle'];
    }

    return array(
    'heading' => wp_kses_post($card_default['heading']),
    'items' => $normalized_items,
    'ctaText' => wp_kses_post($card_default['ctaText']),
    'ctaUrl' => esc_url($card_default['ctaUrl']),
    'bulletStyle' => $card_bullet_style,
    'hasBullets' => null,
    );
  },
    $cards_default
  );
}

/** ---------------------------------------------------------------------------
 * Background / gradient
 * ------------------------------------------------------------------------- */
$gradient_raw = '';
if (isset($A['gradient']) && is_string($A['gradient']) && trim($A['gradient']) !== '') {
  $gradient_raw = $A['gradient'];
}
elseif (isset($A['background']) && is_string($A['background']) && trim($A['background']) !== '') {
  $gradient_raw = $A['background'];
}
$gradient = $gradient_raw !== '' ? sanitize_text_field($gradient_raw) : '';

/** ---------------------------------------------------------------------------
 * Bullets: global + first-column override + yes/no toggles
 * ------------------------------------------------------------------------- */
$bullet_style = 'circle';
if (isset($A['bulletStyle']) && in_array($A['bulletStyle'], array('none', 'circle', 'check'), true)) {
  $bullet_style = $A['bulletStyle'];
}

// Column-level yes/no toggles (optional)
$first_card_has_bullets = array_key_exists('firstCardHasBullets', $A) ? $parse_bool($A['firstCardHasBullets'], true) : null;
$second_card_has_bullets = array_key_exists('secondCardHasBullets', $A) ? $parse_bool($A['secondCardHasBullets'], true) : null;

$first_col_style = 'inherit';
if (isset($A['firstColumnBulletStyle']) && in_array($A['firstColumnBulletStyle'], array('inherit', 'none', 'circle', 'check'), true)) {
  $first_col_style = $A['firstColumnBulletStyle'];
}

/** ---------------------------------------------------------------------------
 * Global CTA toggle
 * ------------------------------------------------------------------------- */
$show_cta = true;
if (array_key_exists('showCta', $A)) {
  $show_cta = $parse_bool($A['showCta'], true);
}

/** ---------------------------------------------------------------------------
 * Classes / anchor
 * ------------------------------------------------------------------------- */
$anchor = sanitize_title($A['anchor'] ?? '');
$classes = array('choice-compare', 'bullets-' . sanitize_html_class($bullet_style));
if (!$show_cta) {
  $classes[] = 'has-no-cta';
}

if (!empty($A['align'])) {
  $classes[] = 'align' . sanitize_html_class($A['align']);
}
if (!empty($A['className'])) {
  foreach (preg_split('/\s+/', $A['className']) as $cls) {
    $cls = sanitize_html_class($cls);
    if ($cls) {
      $classes[] = $cls;
    }
  }
}
$classes = array_unique($classes);

$style_attr = $gradient !== '' ? ' style="background:' . esc_attr($gradient) . ';"' : '';

/** ---------------------------------------------------------------------------
 * Resolve per-card bullet class
 * Priority: explicit yes/no -> per-card style -> first-column override -> global
 * ------------------------------------------------------------------------- */
$resolve_bullets = static function ($idx, $card, $global_style, $first_col_style, $first_yesno, $second_yesno) {
  // Explicit yes/no toggles
  $yesno = $card['hasBullets'];
  if ($yesno === null) {
    if ($idx === 0 && $first_yesno !== null)
      $yesno = $first_yesno;
    if ($idx === 1 && $second_yesno !== null)
      $yesno = $second_yesno;
  }
  if ($yesno === false)
    return 'none';

  // Style overrides
  $resolved = $global_style;
  if ($idx === 0 && $first_col_style !== 'inherit') {
    $resolved = $first_col_style;
  }
  if (isset($card['bulletStyle']) && $card['bulletStyle'] !== 'inherit') {
    $resolved = $card['bulletStyle'];
  }

  // Clamp to allowed set
  return in_array($resolved, array('none', 'circle', 'check'), true) ? $resolved : 'circle';
};

/** ---------------------------------------------------------------------------
 * Render
 * ------------------------------------------------------------------------- */
?>
<section<?php if ($anchor)
  echo ' id="' . esc_attr($anchor) . '"'; ?> class="<?php echo esc_attr(implode(' ', $classes)); ?>"<?php echo $style_attr; ?>>
  <div class="choice-compare__inner">
    <?php if ($title !== ''): ?>
      <h2 class="choice-compare__title"><?php echo $title; ?></h2>
      <?php if ($description !== ''): ?>
        <p class="choice-compare__description"><?php echo $description; ?></p>
      <?php
  endif; ?>
    <?php
endif; ?>

    <?php if (!empty($cards)): ?>
      <div class="choice-compare__grid">
        <?php foreach ($cards as $i => $card):
    $resolved = $resolve_bullets($i, $card, $bullet_style, $first_col_style, $first_card_has_bullets, $second_card_has_bullets);
    $card_classes = array('choice-card', 'bullets-' . $resolved);
?>
          <div class="<?php echo esc_attr(implode(' ', $card_classes)); ?>">
            <?php if ($card['heading'] !== ''): ?>
              <h3 class="choice-card__heading"><?php echo $card['heading']; ?></h3>
            <?php
    endif; ?>

            <?php if (!empty($card['items'])): ?>
              <ul class="choice-card__list">
                <?php foreach ($card['items'] as $item):
        $has_title = (isset($item['title']) && trim(wp_strip_all_tags($item['title'])) !== '');
        $has_desc = (isset($item['description']) && trim(wp_strip_all_tags($item['description'])) !== '');
        if (!$has_title && !$has_desc)
          continue;
?>
                  <li>
                    <?php if ($resolved === 'check'): ?>
                      <span class="check-icon" aria-hidden="true"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg></span>
                    <?php
        endif; ?>
                    <div class="choice-item__content">
                      <?php if ($has_title): ?>
                        <p class="choice-item__title"><?php echo $item['title']; ?></p>
                      <?php
        endif; ?>
                      <?php if ($has_desc): ?>
                        <p class="choice-item__desc"><?php echo $item['description']; ?></p>
                      <?php
        endif; ?>
                    </div>
                  </li>
                <?php
      endforeach; ?>
              </ul>
            <?php
    endif; ?>

            <?php if ($show_cta && $card['ctaText'] !== ''): ?>
              <a class="choice-card__cta" href="<?php echo $card['ctaUrl']; ?>">
                <?php echo $card['ctaText']; ?> <b>&rsaquo;</b>
              </a>
            <?php
    endif; ?>
          </div>
        <?php
  endforeach; ?>
      </div>
    <?php
endif; ?>
  </div></div>
</section>
