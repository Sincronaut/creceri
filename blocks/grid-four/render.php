<?php
/**
 * Render callback: child/ts-value-prop
 * Markup: value prop section with title, intro, kicker, and cards.
 */
if ( ! defined('ABSPATH') ) { exit; }
if ( empty($attributes) || ! is_array($attributes) ) { return; }

$A = $attributes;

/** Core attributes */
$anchor     = isset($A['anchor']) ? sanitize_title($A['anchor']) : '';
$className  = isset($A['className']) ? sanitize_html_class($A['className']) : '';
$section_id = isset($A['sectionId']) ? sanitize_title($A['sectionId']) : 'tek-stories-value-prop';

$title  = isset($A['title'])  ? wp_kses_post($A['title'])  : 'What Makes Tek Stories a Leading Technology Blog Platform';
$intro  = isset($A['intro'])  ? wp_kses_post($A['intro'])  : "In a fast-moving digital world, staying informed isn’t optional — it’s the difference between leading and catching up. Tek Stories exists for those who want to understand technology beyond the headlines, with context that connects innovation to real-world impact.";
$kicker = isset($A['kicker']) ? wp_kses_post($A['kicker']) : 'Why Readers Rely on Us';

$items  = (isset($A['items']) && is_array($A['items'])) ? $A['items'] : array(
  array('title' => 'Clarity in Complexity',       'text' => 'We make sense of technical shifts so you can act with confidence.'),
  array('title' => 'Broad, Relevant Coverage',    'text' => 'From product design and software to digital culture and industry trends.'),
  array('title' => 'Original, Informed Perspectives','text' => 'Written by people who’ve worked in and around the technologies they cover.'),
  array('title' => 'Reader-Centric Structure',    'text' => 'Designed for quick scanning when you’re busy, and depth when you have time.'),
);

/** Build classes */
$classes = array('ts-value-prop');
if ($className) { $classes[] = $className; }
if ($anchor)    { $classes[] = $anchor; }

/** Accessible label id */
$label_id = $section_id ? $section_id . '-title' : 'ts-vp-title';

?>
<section id="<?php echo esc_attr($section_id); ?>"
         class="<?php echo esc_attr(implode(' ', $classes)); ?>"
         aria-labelledby="<?php echo esc_attr($label_id); ?>">
  <div class="ts-vp__container">
    <?php if ($title): ?>
      <h2 id="<?php echo esc_attr($label_id); ?>" class="ts-vp__title"><?php echo $title; ?></h2>
    <?php endif; ?>

    <?php if ($intro): ?>
      <p class="ts-vp__intro"><?php echo $intro; ?></p>
    <?php endif; ?>

    <?php if ($kicker): ?>
      <p class="ts-vp__kicker"><?php echo $kicker; ?></p>
    <?php endif; ?>

    <?php if (!empty($items)): ?>
      <div class="ts-vp__grid">
        <?php foreach ($items as $it):
          $t = array();
          if (is_array($it)) {
            $t['title'] = isset($it['title']) ? wp_kses_post($it['title']) : '';
            $t['text']  = isset($it['text'])  ? wp_kses_post($it['text'])  : '';
          } else {
            // Fallback: treat scalar as text only
            $t['title'] = '';
            $t['text']  = wp_kses_post($it);
          }
          if (trim(wp_strip_all_tags($t['title'] . $t['text'])) === '') { continue; }
        ?>
          <article class="ts-card">
            <?php if (!empty($t['title'])): ?>
              <h3 class="ts-card__title"><?php echo $t['title']; ?></h3>
            <?php endif; ?>
            <?php if (!empty($t['text'])): ?>
              <p class="ts-card__text"><?php echo $t['text']; ?></p>
            <?php endif; ?>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
