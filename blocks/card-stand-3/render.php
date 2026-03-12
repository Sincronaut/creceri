<?php
defined('ABSPATH') || exit;

/** @var array $attributes */
$attrs = wp_parse_args($attributes ?? [], [
  'sectionId' => '',
  'title' => '',
  'intro' => '',
  'cards' => [],
  'ctaText' => '',
  'ctaUrl' => '',
  'gradient' => 'linear-gradient(180deg, #CEE6F2 0%, #ffffff 90%)',
  'extraClass' => '',
]);

$section_id = $attrs['sectionId'] ?: wp_unique_id('emerging-');
$heading_id = $section_id . '-heading';

$gradient = trim((string)$attrs['gradient']);
$style = $gradient ? (stripos($gradient, 'background') === false
  ? 'background: ' . $gradient . ' !important;'
  : $gradient) : '';

$cards = is_array($attrs['cards']) ? $attrs['cards'] : [];

$section_classes = trim('emerging ' . ($attrs['extraClass'] ?: ''));

// Begin render
?>
<section class="<?php echo esc_attr($section_classes); ?>"
         aria-labelledby="<?php echo esc_attr($heading_id); ?>"
         <?php echo $style ? 'style="' . esc_attr($style) . '"' : ''; ?> id="staffing-solutions">
  <div class="emerging__container">
    <?php if (!empty($attrs['title'])): ?>
      <h2 id="<?php echo esc_attr($heading_id); ?>" class="emerging__title">
        <?php echo esc_html($attrs['title']); ?>
      </h2>
    <?php
endif; ?>

    <?php if (!empty($attrs['intro'])): ?>
      <p><?php echo wp_kses($attrs['intro'], ['br' => []]); ?></p>
    <?php
endif; ?>

    <div class="emerging__grid">
      <?php foreach ($cards as $card): ?>
        <?php
  $card_title = isset($card['title']) ? (string)$card['title'] : '';
  $card_copy = isset($card['copy']) ? (string)$card['copy'] : '';
?>
        <article class="card">
          <?php if ($card_title): ?>
            <h3 class="card__title"><?php echo esc_html($card_title); ?></h3>
          <?php
  endif; ?>
          <?php if ($card_copy): ?>
            <p class="card__copy"><?php echo esc_html($card_copy); ?></p>
          <?php
  endif; ?>
        </article>
      <?php
endforeach; ?>

      <?php if (!empty($attrs['ctaText']) && !empty($attrs['ctaUrl'])): ?>
        <div class="emerging__cta">
          <a class="btn btn--primary btn-pill"
             href="<?php echo esc_url($attrs['ctaUrl']); ?>"
             aria-label="<?php echo esc_attr($attrs['ctaText']); ?>">
            <?php echo esc_html($attrs['ctaText']); ?>
          </a>
        </div>
      <?php
endif; ?>
    </div>
  </div>
</section>
