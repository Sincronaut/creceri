<?php
/**
 * Dynamic render for CMS Benefits section.
 *
 * Expected $attributes (aligned with your wp-structure):
 * - align            (string)  e.g., "wide"
 * - sectionId        (string)  HTML id
 * - background       (string)  e.g., "mid" -> adds modifier class
 * - title            (string)  Section H2 title
 * - padding_left     (string)  e.g., "200px"
 * - padding_right    (string)  e.g., "20px"
 * - features         (array)   Each item supports:
 *      - icon        (array|string) If array: ['src','alt','width','height']; if string, theme can map it.
 *      - img         (array)        Fallback to support {img:{src,alt,width,height}}
 *      - title       (string)       Benefit heading (H3)
 *      - desc        (string)       Benefit copy
 *      - variant     (string)       "wide" to add .benefit--wide
 *
 * Safe defaults mirror the provided static HTML.
 */

if (!isset($attributes) || !is_array($attributes)) {
    $attributes = [];
}

$align_class  = !empty($attributes['align']) ? ' align' . sanitize_html_class($attributes['align']) : '';
$bg_modifier  = !empty($attributes['background']) ? ' cms-benefits--' . sanitize_html_class($attributes['background']) : '';
$section_id   = !empty($attributes['sectionId']) ? sanitize_html_class($attributes['sectionId']) : '';
$title        = isset($attributes['title']) && $attributes['title'] !== '' ? $attributes['title'] : 'Why Does a CMS Matter for Your Website?';

$pad_left     = isset($attributes['padding_left'])  ? $attributes['padding_left']  : '';
$pad_right    = isset($attributes['padding_right']) ? $attributes['padding_right'] : '';
$inner_style  = [];
if ($pad_left !== '')  { $inner_style[] = 'padding-left:' . esc_attr($pad_left); }
if ($pad_right !== '') { $inner_style[] = 'padding-right:' . esc_attr($pad_right); }
$inner_style_attr = $inner_style ? ' style="' . esc_attr(implode(';', $inner_style)) . '"' : '';

$features = isset($attributes['features']) && is_array($attributes['features']) ? $attributes['features'] : [
    [
        'img'    => ['src' => 'https://creceri.com/wp-content/uploads/2025/10/ad85fe43ae3059e1aca2847a4308b28004bddceb.png', 'alt' => ''],
        'title'  => 'Easy Updates',
        'desc'   => 'Change text, images, or products without touching code.',
    ],
    [
        'img'    => ['src' => 'https://creceri.com/wp-content/uploads/2025/10/8b23bfe7-52fa-4911-a9b9-3f678fb824a5.png', 'alt' => ''],
        'title'  => 'Faster Launches',
        'desc'   => 'Spin up new pages or blog posts in minutes.',
    ],
    [
        'img'    => ['src' => 'https://creceri.com/wp-content/uploads/2025/10/faf165860befeeb940a121b6e999a565ba3236a9.png', 'alt' => ''],
        'title'  => 'Lower Costs',
        'desc'   => 'Reduce ongoing developer support.',
    ],
    [
        'img'    => ['src' => 'https://creceri.com/wp-content/uploads/2025/10/77664790d6e446b987639ed2559fb99a9ce9e0af.png', 'alt' => ''],
        'title'  => 'Scalable',
        'desc'   => 'Grow seamlessly as the business expands.',
    ],
    [
        'img'    => ['src' => 'https://creceri.com/wp-content/uploads/2025/10/88e339e65da3836ff1fef35991884247f01985a1.png', 'alt' => ''],
        'title'  => 'Consistent Design',
        'desc'   => 'Templates keep everything looking professional.',
        'variant'=> 'wide',
    ],
];

// Optional: map string icons (e.g., "one","two") to theme assets.
$icon_map = apply_filters('creceri/cms_benefits/icon_map', [
    // 'one'   => get_template_directory_uri() . '/assets/icons/one.png',
    // 'two'   => get_template_directory_uri() . '/assets/icons/two.png',
]);

?>
<section class="cms-benefits<?php echo esc_attr($bg_modifier . $align_class); ?>"<?php echo $section_id ? ' id="' . esc_attr($section_id) . '"' : ''; ?>>
  <div class="cms-benefits__inner"<?php echo $inner_style_attr; ?>>
    <h2 class="cms-benefits__title"><?php echo esc_html($title); ?></h2>

    <div class="cms-benefits__grid">
      <?php foreach ($features as $item): ?>
        <?php
          // Normalize icon/img payload
          $media = [];
          if (isset($item['icon']) && is_array($item['icon'])) {
              $media = $item['icon'];
          } elseif (isset($item['img']) && is_array($item['img'])) {
              $media = $item['img'];
          } elseif (!empty($item['icon']) && is_string($item['icon']) && isset($icon_map[$item['icon']])) {
              $media = ['src' => $icon_map[$item['icon']], 'alt' => ''];
          }

          $src    = isset($media['src']) ? $media['src'] : '';
          $alt    = isset($media['alt']) ? $media['alt'] : '';
          $width  = isset($media['width']) ? (int) $media['width'] : 0;
          $height = isset($media['height']) ? (int) $media['height'] : 0;

          $heading = isset($item['title']) ? $item['title'] : '';
          $copy    = isset($item['desc'])  ? $item['desc']  : '';
          $variant = !empty($item['variant']) ? ' benefit--' . sanitize_html_class($item['variant']) : '';
        ?>
        <article class="benefit<?php echo esc_attr($variant); ?>">
          <?php if ($src): ?>
            <img
              class="benefit__icon"
              src="<?php echo esc_url($src); ?>"
              alt="<?php echo esc_attr($alt !== '' ? $alt : $heading); ?>"
              <?php echo $width  ? 'width="' . esc_attr($width) . '"'   : ''; ?>
              <?php echo $height ? 'height="' . esc_attr($height) . '"' : ''; ?>
            />
          <?php endif; ?>

          <?php if ($heading !== ''): ?>
            <h3 class="benefit__heading"><?php echo esc_html($heading); ?></h3>
          <?php endif; ?>

          <?php if ($copy !== ''): ?>
            <p class="benefit__copy"><?php echo esc_html($copy); ?></p>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
