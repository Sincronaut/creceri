<?php
if (!defined('ABSPATH')) {
  exit;
}

/* ───────── Helpers ───────── */
if (!function_exists('child_ecomdev_allowed_lede_tags')) {
  function child_ecomdev_allowed_lede_tags()
  {
    return array(
      'a' => array('href' => true, 'title' => true, 'target' => true, 'rel' => true),
      'strong' => array(), 'b' => array(), 'em' => array(), 'i' => array(),
      'br' => array(), 'span' => array('class' => true)
    );
  }
}

if (!function_exists('child_ecomdev_image_from_attr')) {
  function child_ecomdev_image_from_attr($img)
  {
    $img = is_array($img ?? null) ? $img : array();
    return array(
      'src' => esc_url($img['src'] ?? ''),
      'alt' => esc_attr($img['alt'] ?? ''),
      'width' => !empty($img['width']) ? intval($img['width']) : 0,
      'height' => !empty($img['height']) ? intval($img['height']) : 0,
      'loading' => esc_attr($img['loading'] ?? 'lazy'),
      'decoding' => esc_attr($img['decoding'] ?? 'async'),
    );
  }
}

/* Render description as <p> or <ul> */
if (!function_exists('child_ecomdev_render_desc')) {
  function child_ecomdev_render_desc($desc)
  {
    if (!is_array($desc)) {
      $text = trim(wp_strip_all_tags((string)$desc));
      if ($text === '') {
        return '';
      }
      return '<p class="ecom-dev__phase-desc">' . wp_kses_post($desc) . '</p>';
    }
    $clean = array();
    foreach ($desc as $d) {
      $t = trim(wp_strip_all_tags(is_array($d) ? ($d['text'] ?? '') : (string)$d));
      if ($t !== '') {
        $clean[] = $t;
      }
    }
    if (empty($clean)) {
      return '';
    }
    if (count($clean) === 1) {
      return '<p class="ecom-dev__phase-desc">' . wp_kses_post($clean[0]) . '</p>';
    }
    $out = '<ul class="ecom-dev__phase-desc ecom-dev__phase-desc--list">';
    foreach ($clean as $li) {
      $out .= '<li>' . wp_kses_post($li) . '</li>';
    }
    $out .= '</ul>';
    return $out;
  }
}

/* ───────── Attributes ───────── */
$A = is_array($attributes ?? null) ? $attributes : array();

$section_id = sanitize_title($A['sectionId'] ?? 'ecom-dev');
$className = sanitize_html_class($A['className'] ?? '');

$title = wp_kses_post($A['title'] ?? 'What is E-commerce development?');
$description = wp_kses_post($A['description'] ?? '');
$lede_raw = $A['lede'] ?? ($A['intro'] ?? '');

$lineStatus = (isset($A['lineStatus']) && strtolower($A['lineStatus']) === 'off') ? 'Off' : 'On';
$items = (isset($A['items']) && is_array($A['items'])) ? $A['items'] : array();

/* Fallback content */
if (empty($items)) {
  $items = array(
      array('type' => 'phase', 'title' => 'Strategy & Planning', 'label' => '1',
      'img' => array('src' => 'https://creceri.com/wp-content/uploads/2025/10/1.png', 'alt' => 'Strategy & Planning icon', 'width' => 150, 'height' => 150),
      'desc' => array('Market and competitor analysis', 'Requirements gathering', 'Roadmapping & KPIs')),
      array('type' => 'connector', 'img' => array('src' => 'https://creceri.com/wp-content/uploads/2025/10/Screenshot-2025-10-13-082149-1.png', 'alt' => 'arrow', 'width' => 160, 'height' => 60)),
      array('type' => 'phase', 'title' => 'Design & User Experience', 'label' => '2',
      'img' => array('src' => 'https://creceri.com/wp-content/uploads/2025/10/2.png', 'alt' => 'Design & UX icon', 'width' => 96, 'height' => 96),
      'desc' => 'Wireframes, UI, and interaction patterns that reduce friction.'),
      array('type' => 'connector', 'img' => array('src' => 'https://creceri.com/wp-content/uploads/2025/10/output-onlinepngtools-lines.png', 'alt' => 'arrow', 'width' => 160, 'height' => 60)),
      array('type' => 'phase', 'title' => 'Technical Development', 'label' => '3',
      'img' => array('src' => 'https://creceri.com/wp-content/uploads/2025/10/3.png', 'alt' => 'Technical Development icon', 'width' => 96, 'height' => 96),
      'desc' => array('Platform setup & integrations', 'Payments, tax, and shipping', 'Performance & security baselines')),
      array('type' => 'connector', 'img' => array('src' => 'https://creceri.com/wp-content/uploads/2025/10/Screenshot-2025-10-13-082149-1.png', 'alt' => 'arrow', 'width' => 160, 'height' => 60)),
      array('type' => 'phase', 'title' => 'Launch & Optimization', 'label' => '4',
      'img' => array('src' => 'https://creceri.com/wp-content/uploads/2025/10/4.png', 'alt' => 'Launch & Optimization icon', 'width' => 96, 'height' => 96),
      'desc' => 'Go-live, QA, analytics, and ongoing CRO.')
  );
}

/* Classes */
$classes = array('ecom-dev');
if ($className) {
  $classes[] = $className;
}

/* Flags */
$has_connector = false;
foreach ($items as $t) {
  if (sanitize_key($t['type'] ?? '') === 'connector') {
    $has_connector = true;
    break;
  }
}

/* Track connector count to target the 2nd connector */
$connector_index = 0;
?>
<section class="<?php echo esc_attr(implode(' ', $classes)); ?>" aria-labelledby="<?php echo esc_attr($section_id); ?>">
  <header class="ecom-dev__header">
    <h2 id="<?php echo esc_attr($section_id); ?>"><?php echo wp_kses_post($title); ?></h2>

    <?php if (trim(wp_strip_all_tags($description)) !== ''): ?>
      <p class="ecom-dev__description"><?php echo wp_kses_post($description); ?></p>
    <?php
endif; ?>

    <?php if (!empty($lede_raw) && trim(wp_strip_all_tags($lede_raw)) !== ''): ?>
      <div class="ecom-dev__lede">
        <?php echo do_shortcode(wpautop(wp_kses($lede_raw, child_ecomdev_allowed_lede_tags()))); ?>
      </div>
    <?php
endif; ?>
  </header>

  <ol class="ecom-dev__phases" role="list">
    <?php
$valid_phases = array_values(array_filter($items, function ($item) {
  return sanitize_key($item['type'] ?? 'phase') === 'phase';
}));
$total_phases = count($valid_phases);

if ($total_phases > 1): ?>
      <svg class="cn-connectors" aria-hidden="true" preserveAspectRatio="none">
        <?php for ($c = 0; $c < $total_phases - 1; $c++): ?>
          <path class="cn-arc"
                data-from="<?php echo $c; ?>"
                data-to="<?php echo $c + 1; ?>"
                fill="none"
                stroke="#962E2A"
                stroke-width="3"
                stroke-dasharray="7 7" />
        <?php
  endfor; ?>
      </svg>
    <?php
endif; ?>
    <?php foreach ($valid_phases as $idx => $raw):
  $item_title = wp_kses_post($raw['title'] ?? '');
  $item_desc = $raw['desc'] ?? '';
  $label = sanitize_text_field($raw['label'] ?? '');
  $img = child_ecomdev_image_from_attr($raw['img'] ?? array());
?>
        <li class="ecom-dev__phase" data-step="<?php echo $idx; ?>">
          <div class="ecom-dev__icon-wrap">
            <figure class="ecom-dev__figure">
              <?php if (!empty($img['src'])): ?>
                <img src="<?php echo $img['src']; ?>" alt="<?php echo $img['alt']; ?>"
                     <?php if ($img['width'])
      echo 'width="' . intval($img['width']) . '"'; ?>
                     <?php if ($img['height'])
      echo 'height="' . intval($img['height']) . '"'; ?>
                     loading="<?php echo $img['loading']; ?>" decoding="<?php echo $img['decoding']; ?>" />
              <?php
  endif; ?>
              <?php if ($label !== ''): ?>
                <figcaption class="ecom-dev__label"></figcaption>
              <?php
  endif; ?>
            </figure>
          </div>

          <?php if ($item_title !== ''): ?>
            <h3 class="ecom-dev__phase-title"><?php echo $item_title; ?></h3>
          <?php
  endif; ?>

          <?php
  $desc_html = child_ecomdev_render_desc($item_desc);
  if ($desc_html !== '') {
    echo $desc_html;
  }
?>
        </li>
    <?php
endforeach; ?>
  </ol>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.ecom-dev__phases').forEach(list => {
    const svg = list.querySelector('.cn-connectors');
    if (!svg) return;

    function drawArcs() {
      // In mobile view (where elements stack in a 2x2 grid), we might want to hide the SVG instead.
      // E.g. check width
      if (window.innerWidth <= 1168) {
          svg.style.display = 'none';
          return;
      } else {
          svg.style.display = 'block';
      }

      const steps = list.querySelectorAll('.ecom-dev__phase');
      const paths = svg.querySelectorAll('.cn-arc');
      const listRect = list.getBoundingClientRect();

      svg.setAttribute('width', list.offsetWidth);
      svg.setAttribute('height', list.offsetHeight);
      svg.style.width = list.offsetWidth + 'px';
      svg.style.height = list.offsetHeight + 'px';

      paths.forEach(path => {
        const fromIdx = parseInt(path.dataset.from, 10);
        const toIdx = parseInt(path.dataset.to, 10);
        const from = steps[fromIdx];
        const to = steps[toIdx];
        if (!from || !to) return;

        const fromImg = from.querySelector('.ecom-dev__figure img');
        const toImg = to.querySelector('.ecom-dev__figure img');
        if (!fromImg || !toImg) return;

        const fRect = fromImg.getBoundingClientRect();
        const tRect = toImg.getBoundingClientRect();

        const gap = 15; // Padding between image and start of dashed line
        
        let x1, y1, x2, y2, cx, cy, curveHeight;

        // Horizontally start from the edge of the images + gap
        x1 = fRect.right - listRect.left + gap;
        x2 = tRect.left - listRect.left - gap;
        
        if (fromIdx === 0) {
           // Arc 1 (1->2): Starts mid/high-ish, curves UP
           y1 = fRect.top + (fRect.height * 0.40) - listRect.top;
           y2 = tRect.top + (tRect.height * 0.40) - listRect.top;
           curveHeight = -50; 
        } else if (fromIdx === 1) {
           // Arc 2 (2->3): Starts low near the chair, curves DOWN
           y1 = fRect.top + (fRect.height * 0.80) - listRect.top;
           y2 = tRect.top + (tRect.height * 0.80) - listRect.top;
           curveHeight = 50; 
        } else {
           // Arc 3 (3->4): Starts mid/high-ish, curves UP
           y1 = fRect.top + (fRect.height * 0.40) - listRect.top;
           y2 = tRect.top + (tRect.height * 0.40) - listRect.top;
           curveHeight = -50; 
        }
        
        cx = x1 + (x2 - x1) / 2;
        cy = Math.min(y1, y2) + curveHeight; 
        // For arc 2 which curves down, cy should actually just be an offset from the line
        if (curveHeight > 0) {
            cy = Math.max(y1, y2) + curveHeight;
        }

        path.setAttribute('d', `M ${x1} ${y1} Q ${cx} ${cy} ${x2} ${y2}`);
      });
    }

    // Call once with a slight delay to allow images to load
    setTimeout(drawArcs, 200);
    window.addEventListener('resize', drawArcs);
  });
});
</script>
