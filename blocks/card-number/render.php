<?php
/**
 * Block: Card Number – Zigzag numbered process steps
 */
if (!defined('ABSPATH'))
  exit;

$attrs = is_array($attributes ?? null) ? $attributes : [];
$title = isset($attrs['title']) ? $attrs['title'] : 'How It Works';
$intro = isset($attrs['intro']) ? $attrs['intro'] : '';
$steps = is_array($attrs['steps'] ?? null) ? $attrs['steps'] : [];
$className = isset($attrs['className']) ? $attrs['className'] : '';

$img_base = get_stylesheet_directory_uri() . '/assets/images/card-number/';

/* Mapping for descriptive names */
$img_map = [
  1 => 'define-need.webp',
  2 => 'select-talent.webp',
  3 => 'integrate-talent.webp',
  4 => 'maintain-control.webp',
  5 => 'scale-talent.webp'
];

/* Fallback: if no steps provided in attributes, use defaults */
if (empty($steps)) {
  $steps = [
    ['heading' => 'Define the Need', 'text' => 'Identify roles, skills, and timelines based on project goals.'],
    ['heading' => 'Select Talent', 'text' => 'Match vetted professionals to your requirements.'],
    ['heading' => 'Integrate Seamlessly', 'text' => 'Onboard talent into your tools, processes, and communication channels.'],
    ['heading' => 'Maintain Control', 'text' => 'Internal teams lead the work while extended members support execution.'],
    ['heading' => 'Scale as Needed', 'text' => 'Adjust team size based on evolving demands or delivery phases.'],
  ];
}

$total = count($steps);
$wrapper_classes = trim('cn-section ' . $className);
?>
<section class="<?php echo esc_attr($wrapper_classes); ?>">
  <div class="cn-container">

    <?php if ($title !== ''): ?>
      <h2 class="cn-title"><?php echo esc_html($title); ?></h2>
    <?php
endif; ?>

    <?php if ($intro !== ''): ?>
      <p class="cn-intro"><?php echo esc_html($intro); ?></p>
    <?php
endif; ?>

    <div class="cn-steps" data-count="<?php echo esc_attr($total); ?>">
      <?php
/* ---------- SVG connector arcs (between items) ---------- */
if ($total > 1):
?>
      <svg class="cn-connectors" aria-hidden="true" preserveAspectRatio="none">
        <?php
  for ($c = 1; $c < $total; $c += 2):
    /* Arc from left top node (c - 1) to bottom node (c) */
?>
          <path class="cn-arc cn-arc--left-to-bottom"
                data-from="<?php echo $c - 1; ?>"
                data-to="<?php echo $c; ?>"
                fill="none"
                stroke="#962E2A"
                stroke-width="3"
                stroke-dasharray="7 7" />
        <?php
    /* Arc from right top node (c + 1) to bottom node (c) */
    if ($c + 1 < $total):
?>
          <path class="cn-arc cn-arc--right-to-bottom"
                data-from="<?php echo $c + 1; ?>"
                data-to="<?php echo $c; ?>"
                fill="none"
                stroke="#962E2A"
                stroke-width="3"
                stroke-dasharray="7 7" />
        <?php
    endif;
  endfor;
?>
      </svg>
      <?php
endif; ?>

      <?php foreach ($steps as $i => $step):
  $num = $i + 1;
  $heading = isset($step['heading']) ? $step['heading'] : '';
  $text = isset($step['text']) ? $step['text'] : '';
  $is_top = ($i % 2 === 0); /* 1,3,5 = top row; 2,4 = bottom row */
  
  /* Resolve image source */
  $step_img = '';
  if (isset($step['image']['src'])) {
    $step_img = $step['image']['src'];
  } elseif (isset($step['img']['src'])) {
    $step_img = $step['img']['src'];
  } elseif (isset($step['src'])) {
    $step_img = $step['src'];
  } else {
    $img_file = isset($img_map[$num]) ? $img_map[$num] : $num . '.webp';
    $step_img = $img_base . $img_file;
  }
  
  $alt = isset($step['image']['alt']) ? $step['image']['alt'] : 
         (isset($step['img']['alt']) ? $step['img']['alt'] : 
         (isset($step['alt']) ? $step['alt'] : (string)$num));
?>
        <div class="cn-step cn-step--<?php echo $is_top ? 'top' : 'bottom'; ?>"
             data-step="<?php echo $num; ?>">
          <div class="cn-num">
            <img src="<?php echo esc_url($step_img); ?>"
                 alt="<?php echo esc_attr($alt); ?>"
                 width="100" height="100" loading="lazy" />
          </div>
          <h3 class="cn-step-title"><?php echo esc_html($heading); ?></h3>
          <p class="cn-step-text"><?php echo esc_html($text); ?></p>
        </div>
      <?php
endforeach; ?>
    </div>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.cn-steps').forEach(grid => {
    const svg = grid.querySelector('.cn-connectors');
    if (!svg) return;

    function drawArcs() {
      const steps = grid.querySelectorAll('.cn-step');
      const paths = svg.querySelectorAll('.cn-arc');
      const gridRect = grid.getBoundingClientRect();

      /* Size SVG to overlay the grid */
      svg.setAttribute('width', grid.offsetWidth);
      svg.setAttribute('height', grid.offsetHeight);
      svg.style.width = grid.offsetWidth + 'px';
      svg.style.height = grid.offsetHeight + 'px';

      paths.forEach(path => {
        const fromIdx = parseInt(path.dataset.from, 10);
        const toIdx = parseInt(path.dataset.to, 10);
        const from = steps[fromIdx];
        const to = steps[toIdx];
        if (!from || !to) return;

        const fromNum = from.querySelector('.cn-num');
        const toNum = to.querySelector('.cn-num');
        if (!fromNum || !toNum) return;

        const fRect = fromNum.getBoundingClientRect();
        const tRect = toNum.getBoundingClientRect();
        const isLeftToBottom = path.classList.contains('cn-arc--left-to-bottom');

        let x1, y1, x2, y2, cx, cy;
        const gap = 15; /* Space between the arc and the numbers */

        if (isLeftToBottom) {
          /* Top step (left) → Bottom step */
          x1 = fRect.right - gridRect.left + gap;
          y1 = fRect.top + fRect.height / 2 - gridRect.top;
        } else {
          /* Top step (right) → Bottom step */
          x1 = fRect.left - gridRect.left - gap;
          y1 = fRect.top + fRect.height / 2 - gridRect.top;
        }

        /* End: top edge, horizontally centered of "to" number */
        x2 = tRect.left + tRect.width / 2 - gridRect.left;
        y2 = tRect.top - gridRect.top - gap;

        /* Control point: intersection of horizontal start and vertical arrive */
        cx = x2; /* End X - makes line vertical at end */
        cy = y1; /* Start Y - makes line horizontal at start */

        path.setAttribute('d', `M ${x1} ${y1} Q ${cx} ${cy} ${x2} ${y2}`);
      });
    }

    drawArcs();
    window.addEventListener('resize', drawArcs);
  });
});
</script>
