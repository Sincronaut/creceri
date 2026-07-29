<?php
if (!isset($attributes) || !is_array($attributes)) {
  $attributes = [];
}

if (!empty($attributes['columns'])) {
  // This is the table-2 (E-Commerce Models) variant.
  $title_default = __('Choose the Right E-Commerce Model for Your Online Business', 'vite-ttf-child-creceri');
  $columns_default = array(
      array('key' => 'model', 'label' => __('Model', 'vite-ttf-child-creceri')),
      array('key' => 'description', 'label' => __('Description', 'vite-ttf-child-creceri')),
      array('key' => 'industries', 'label' => __('Common Industries', 'vite-ttf-child-creceri')),
  );
  $rows_default = array(
      array(
      'cells' => array(
          array('text' => '<strong>B2C (Business to Consumer)</strong>'),
          array('text' => __('Companies sell directly to individual customers.', 'vite-ttf-child-creceri')),
          array('text' => __('Fashion, electronics, beauty, home goods', 'vite-ttf-child-creceri')),
      ),
    ),
      array(
      'cells' => array(
          array('text' => '<strong>B2B (Business to Business)</strong>'),
          array('text' => __('Businesses sell to other businesses, often in bulk or through contracts.', 'vite-ttf-child-creceri')),
          array('text' => __('Industrial supplies, software, logistics', 'vite-ttf-child-creceri')),
      ),
    ),
      array(
      'cells' => array(
          array('text' => '<strong>C2C (Consumer to Consumer)</strong>'),
          array('text' => __('Individuals sell to other individuals, usually through marketplaces.', 'vite-ttf-child-creceri')),
          array('text' => __('Vintage items, handmade crafts, resale', 'vite-ttf-child-creceri')),
      ),
    ),
      array(
      'cells' => array(
          array('text' => '<strong>C2B / D2C (Consumer to Business / Direct to Consumer)</strong>'),
          array('text' => __('Individuals offer services to companies (C2B), or brands sell directly to customers through their own sites (D2C).', 'vite-ttf-child-creceri')),
          array('text' => __('Marketing, design, wellness, tech accessories', 'vite-ttf-child-creceri')),
      ),
    ),
  );

  $title = wp_kses_post($attributes['title'] ?? $title_default);
  $intro = wp_kses_post($attributes['intro'] ?? '');
  $aria_label = sanitize_text_field($attributes['ariaLabel'] ?? __('E-commerce models', 'vite-ttf-child-creceri'));

  $anchor = sanitize_title($attributes['anchor'] ?? '');
  $section_id = sanitize_title($attributes['sectionId'] ?? '');
  $base_id = $anchor ?: $section_id;
  if ($base_id === '') {
    $base_id = 'ecom-models-' . uniqid();
  }

  $classes = array('ecom-models');
  if (!empty($attributes['align'])) {
    $classes[] = 'align' . sanitize_html_class($attributes['align']);
  }
  if (!empty($attributes['className'])) {
    foreach (preg_split('/\s+/', $attributes['className']) as $class_name) {
      $class_name = sanitize_html_class($class_name);
      if ($class_name) {
        $classes[] = $class_name;
      }
    }
  }
  $classes = array_unique($classes);

  $box_size = $attributes['boxSize'] ?? 'wide';
  if (!in_array($box_size, array('wide', 'short'), true)) {
    $box_size = 'wide';
  }
  $classes[] = 'ecom-models--size-' . $box_size;

  $bg = isset($attributes['background']) ? (string)$attributes['background'] : 'on';
  if ($bg === 'off') {
    $classes[] = 'ecom-models--bg-off';
  }

  $columns = array();
  if (isset($attributes['columns']) && is_array($attributes['columns'])) {
    foreach ($attributes['columns'] as $idx => $column_raw) {
      if (is_array($column_raw)) {
        $label = sanitize_text_field($column_raw['label'] ?? '');
        if ($label === '')
          continue;
        $key = sanitize_key($column_raw['key'] ?? '');
        if ($key === '')
          $key = 'col' . $idx;
        $columns[] = array('key' => $key, 'label' => $label);
      }
      elseif (is_string($column_raw) && $column_raw !== '') {
        $columns[] = array('key' => 'col' . $idx, 'label' => sanitize_text_field($column_raw));
      }
    }
  }
  if (empty($columns)) {
    $columns = $columns_default;
  }
  $column_count = count($columns);

  $rows = array();
  if (isset($attributes['rows']) && is_array($attributes['rows'])) {
    foreach ($attributes['rows'] as $row_raw) {
      if (!is_array($row_raw))
        continue;
      $cells = array();
      if (isset($row_raw['cells']) && is_array($row_raw['cells'])) {
        foreach ($columns as $index => $column) {
          $cell_raw = $row_raw['cells'][$index] ?? '';
          $cells[] = is_array($cell_raw) ? wp_kses_post($cell_raw['text'] ?? '') : wp_kses_post($cell_raw);
        }
      }
      else {
        foreach ($columns as $column) {
          $key = $column['key'];
          $text = '';
          if ($key !== '' && isset($row_raw[$key])) {
            $source = $row_raw[$key];
            $text = is_array($source) ? wp_kses_post($source['text'] ?? '') : wp_kses_post($source);
          }
          $cells[] = $text;
        }
      }
      $has_content = array_filter($cells, static function ($cell) {
        return trim(wp_strip_all_tags($cell)) !== ''; });
      if (!empty($has_content)) {
        $rows[] = $cells;
      }
    }
  }

  if (empty($rows)) {
    foreach ($rows_default as $row_default) {
      $cells = array();
      foreach ($columns as $idx => $column) {
        $cells[] = wp_kses_post($row_default['cells'][$idx]['text'] ?? '');
      }
      $rows[] = $cells;
    }
  }
  $row_count = count($rows);

  if ($column_count === 0 || $row_count === 0) {
    return;
  }

  $section_id_attr = ' id="' . esc_attr($base_id) . '"';
  $heading_id = sanitize_html_class($base_id . '-title');
  $table_style = ' style="--column-count:' . intval($column_count) . ';"';
?>
    <section<?php echo $section_id_attr; ?> class="<?php echo esc_attr(implode(' ', $classes)); ?>" aria-labelledby="<?php echo esc_attr($heading_id); ?>">
      <?php if ($title !== ''): ?>
        <h2 id="<?php echo esc_attr($heading_id); ?>" class="ecom-models__title"><?php echo $title; ?></h2>
      <?php
  endif; ?>

      <?php if ($intro !== ''): ?>
        <p class="ecom-models__intro"><?php echo $intro; ?></p>
      <?php
  endif; ?>

      <div class="ecom-table" role="table" aria-label="<?php echo esc_attr($aria_label); ?>"<?php echo $table_style; ?>>
        <div class="ecom-table__header" role="rowgroup" aria-hidden="true">
          <?php foreach ($columns as $column): ?>
            <div class="ecom-table__cell ecom-table__cell--head" role="columnheader"><?php echo esc_html($column['label']); ?></div>
          <?php
  endforeach; ?>
        </div>

        <div class="ecom-table__body" role="rowgroup">
          <?php foreach ($rows as $row_index => $row_cells): ?>
            <div class="ecom-table__row" role="row">
              <?php foreach ($columns as $col_index => $column):
      $text = $row_cells[$col_index] ?? '';
      $is_last_col = ($col_index === $column_count - 1);
      $is_last_row = ($row_index === $row_count - 1);
      $is_first_col = ($col_index === 0);
      $cell_classes = array('ecom-table__cell');
      if ($is_last_col) {
        $cell_classes[] = 'ecom-table__cell--last-col';
      }
      if ($is_last_row) {
        $cell_classes[] = 'ecom-table__cell--last-row';
      }
      if ($is_last_row && $is_first_col) {
        $cell_classes[] = 'ecom-table__cell--last-row-first';
      }
      if ($is_last_row && $is_last_col) {
        $cell_classes[] = 'ecom-table__cell--last-row-last';
      }
?>
                <div class="<?php echo esc_attr(implode(' ', $cell_classes)); ?>" role="cell" data-label="<?php echo esc_attr($column['label']); ?>">
                  <?php echo $text; ?>
                </div>
              <?php
    endforeach; ?>
            </div>
          <?php
  endforeach; ?>
        </div>
      </div>
    </section>
    <?php
  return; // Exit here if we rendered table-2
}

/**
 * Dynamic render: Small Business Trends section (Table 1 variant).
 */
$align_class = !empty($attributes['align']) ? ' align' . sanitize_html_class($attributes['align']) : '';
$section_id = !empty($attributes['sectionId']) ? sanitize_html_class($attributes['sectionId']) : '';
$uid = function_exists('wp_unique_id') ? wp_unique_id('trends-') : uniqid('trends-');
$root_id = $section_id ?: $uid;

$title_top = isset($attributes['title_top']) && $attributes['title_top'] !== '' ? $attributes['title_top'] : 'Why Small Business Trends Matter Right Now';
$title_bottom = isset($attributes['title_bottom']) && $attributes['title_bottom'] !== '' ? $attributes['title_bottom'] : 'Current Small Business Trends You Need to Know';

$col_driver = isset($attributes['col_driver']) ? $attributes['col_driver'] : 'Trend Driver';
$col_reason = isset($attributes['col_reason']) ? $attributes['col_reason'] : 'Why It Matters';

$pad_left = isset($attributes['padding_left']) ? $attributes['padding_left'] : '';
$pad_right = isset($attributes['padding_right']) ? $attributes['padding_right'] : '';
$inner_style = [];
if ($pad_left !== '') {
  $inner_style[] = 'padding-left:' . esc_attr($pad_left);
}
if ($pad_right !== '') {
  $inner_style[] = 'padding-right:' . esc_attr($pad_right);
}
$inner_style_attr = $inner_style ? ' style="' . esc_attr(implode(';', $inner_style)) . '"' : '';

$drivers = (isset($attributes['drivers']) && is_array($attributes['drivers'])) ? $attributes['drivers'] : [
  ['driver' => 'Economic Pressure', 'reason' => 'Forces small businesses to rethink cash flow, pricing, and operational efficiency.'],
  ['driver' => 'Technology Acceleration', 'reason' => 'Digital tools are now central to how businesses operate, market, and grow.'],
  ['driver' => 'Consumer Expectations', 'reason' => 'Audiences expect personalization, transparency, and seamless service.'],
  ['driver' => 'Policy & Regulation Shifts', 'reason' => 'Tax updates, compliance rules, and incentives directly shape business strategy.'],
  ['driver' => 'Workforce Evolution', 'reason' => 'Remote and hybrid models redefine hiring, retention, and team structure.'],
];

$slides = (isset($attributes['slides']) && is_array($attributes['slides'])) ? $attributes['slides'] : [
  ['title' => 'Digital tools are now essential', 'copy' => 'From payments to marketing, small businesses are streamlining operations through tech.'],
  ['title' => 'Sustainability is expected', 'copy' => 'Eco-friendly practices are everyday expectations, not niche differentiators.'],
  ['title' => 'Personalization drives loyalty', 'copy' => 'Tailored experiences lift retention and lifetime value across channels.'],
  ['title' => 'Omnichannel becomes table stakes', 'copy' => 'Customers want consistent journeys across in-store, web, and social.'],
];

$show_dots = !isset($attributes['show_dots']) || (bool)$attributes['show_dots'];

$heading_top_id = $root_id . '-heading-top';
$heading_bottom_id = $root_id . '-heading-bottom';
$track_id = $root_id . '-track';
$reveal = $attributes['reveal'] ?? '';
$reveal_class = $reveal ? ' reveal-' . sanitize_html_class($reveal) : '';
?>
<section class="trends<?php echo esc_attr($align_class . $reveal_class); ?>" id="<?php echo esc_attr($root_id); ?>" aria-labelledby="<?php echo esc_attr($heading_top_id); ?>">
  <div class="trends__container"<?php echo $inner_style_attr; ?>>
    <h2 id="<?php echo esc_attr($heading_top_id); ?>" class="trends__title"><?php echo esc_html($title_top); ?></h2>

    <!-- Table Card -->
    <div class="trends__tablewrap" role="region" aria-label="Trend drivers and why they matter">
      <table class="trends__table">
        <thead>
          <tr>
            <th scope="col"><?php echo esc_html($col_driver); ?></th>
            <th scope="col"><?php echo esc_html($col_reason); ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($drivers as $row):
  $d = isset($row['driver']) ? $row['driver'] : '';
  $r = isset($row['reason']) ? $row['reason'] : '';
  if ($d === '' && $r === '') {
    continue;
  }
?>
          <tr>
            <th scope="row"><?php echo esc_html($d); ?></th>
            <td><?php echo esc_html($r); ?></td>
          </tr>
          <?php
endforeach; ?>
        </tbody>
      </table>
    </div>

    <h2 id="<?php echo esc_attr($heading_bottom_id); ?>" class="trends__title trends__title--spaced"><?php echo esc_html($title_bottom); ?></h2>

    <!-- Carousel -->
    <div class="trends__carouselwrap" role="region" aria-label="Current trends carousel">
      <button class="carousel__arrow carousel__arrow--prev" type="button" aria-label="Previous slide">❮</button>

      <div class="trends__carousel" id="<?php echo esc_attr($track_id); ?>">
        <?php foreach ($slides as $s):
  $t = isset($s['title']) ? $s['title'] : '';
  $c = isset($s['copy']) ? $s['copy'] : '';
  if ($t === '' && $c === '') {
    continue;
  }
?>
        <article class="trendcard">
          <?php if ($t !== ''): ?><h3 class="trendcard__title"><?php echo esc_html($t); ?></h3><?php
  endif; ?>
          <?php if ($c !== ''): ?><p class="trendcard__copy"><?php echo esc_html($c); ?></p><?php
  endif; ?>
        </article>
        <?php
endforeach; ?>
      </div>

      <button class="carousel__arrow carousel__arrow--next" type="button" aria-label="Next slide">❯</button>

      <?php if ($show_dots): ?>
      <div class="carousel__dots" role="tablist" aria-label="Slide indicators">
        <?php $i = 1;
  foreach ($slides as $_): ?>
          <button class="dot<?php echo $i === 1 ? ' is-active' : ''; ?>" aria-label="<?php echo esc_attr('Slide ' . $i); ?>"></button>
        <?php $i++;
  endforeach; ?>
      </div>
      <?php
endif; ?>
    </div>
  </div>
</section>
<script>
(() => {
  const root = document.getElementById(<?php echo wp_json_encode($root_id); ?>);
  if (!root) return;

  const track = root.querySelector('#<?php echo esc_js($track_id); ?>');
  const prev  = root.querySelector('.carousel__arrow--prev');
  const next  = root.querySelector('.carousel__arrow--next');
  const dotsW = root.querySelector('.carousel__dots');
  const dots  = dotsW ? Array.from(dotsW.querySelectorAll('.dot')) : [];

  const getStep = () => {
    const first = track?.firstElementChild;
    if (!first) return track.clientWidth;
    const style = getComputedStyle(track);
    const gap = parseFloat(style.gap || style.columnGap || 16);
    return first.getBoundingClientRect().width + gap;
  };

  const updateDots = () => {
    if (!track || !dots.length) return;
    const page = Math.round(track.scrollLeft / getStep());
    dots.forEach((d,i) => d.classList.toggle('is-active', i === page));
  };

  prev?.addEventListener('click', () => track?.scrollBy({left: -getStep(), behavior:'smooth'}));
  next?.addEventListener('click', () => track?.scrollBy({left:  getStep(), behavior:'smooth'}));
  track?.addEventListener('scroll', () => { window.requestAnimationFrame(updateDots); });
  dots.forEach((d,i) => d.addEventListener('click', () => track?.scrollTo({left: i * getStep(), behavior:'smooth'})));

  updateDots();
})();
</script>
