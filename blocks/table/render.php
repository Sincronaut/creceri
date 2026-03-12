<?php
/**
 * Dynamic render: Small Business Trends section.
 * Expected $attributes:
 * - align, sectionId, padding_left, padding_right
 * - title_top, title_bottom
 * - col_driver, col_reason
 * - drivers: [ ['driver'=>string, 'reason'=>string], ... ]
 * - slides:  [ ['title'=>string, 'copy'=>string], ... ]
 * - show_dots: bool (default true)
 */

if (!isset($attributes) || !is_array($attributes)) { $attributes = []; }

$align_class = !empty($attributes['align']) ? ' align' . sanitize_html_class($attributes['align']) : '';
$section_id  = !empty($attributes['sectionId']) ? sanitize_html_class($attributes['sectionId']) : '';
$uid         = function_exists('wp_unique_id') ? wp_unique_id('trends-') : uniqid('trends-');
$root_id     = $section_id ?: $uid;

$title_top    = isset($attributes['title_top'])    && $attributes['title_top']    !== '' ? $attributes['title_top']    : 'Why Small Business Trends Matter Right Now';
$title_bottom = isset($attributes['title_bottom']) && $attributes['title_bottom'] !== '' ? $attributes['title_bottom'] : 'Current Small Business Trends You Need to Know';

$col_driver = isset($attributes['col_driver']) ? $attributes['col_driver'] : 'Trend Driver';
$col_reason = isset($attributes['col_reason']) ? $attributes['col_reason'] : 'Why It Matters';

$pad_left  = isset($attributes['padding_left'])  ? $attributes['padding_left']  : '';
$pad_right = isset($attributes['padding_right']) ? $attributes['padding_right'] : '';
$inner_style = [];
if ($pad_left !== '')  { $inner_style[] = 'padding-left:' . esc_attr($pad_left); }
if ($pad_right !== '') { $inner_style[] = 'padding-right:' . esc_attr($pad_right); }
$inner_style_attr = $inner_style ? ' style="' . esc_attr(implode(';', $inner_style)) . '"' : '';

$drivers = (isset($attributes['drivers']) && is_array($attributes['drivers'])) ? $attributes['drivers'] : [
  ['driver'=>'Economic Pressure',        'reason'=>'Forces small businesses to rethink cash flow, pricing, and operational efficiency.'],
  ['driver'=>'Technology Acceleration',  'reason'=>'Digital tools are now central to how businesses operate, market, and grow.'],
  ['driver'=>'Consumer Expectations',    'reason'=>'Audiences expect personalization, transparency, and seamless service.'],
  ['driver'=>'Policy & Regulation Shifts','reason'=>'Tax updates, compliance rules, and incentives directly shape business strategy.'],
  ['driver'=>'Workforce Evolution',      'reason'=>'Remote and hybrid models redefine hiring, retention, and team structure.'],
];

$slides = (isset($attributes['slides']) && is_array($attributes['slides'])) ? $attributes['slides'] : [
  ['title'=>'Digital tools are now essential',     'copy'=>'From payments to marketing, small businesses are streamlining operations through tech.'],
  ['title'=>'Sustainability is expected',          'copy'=>'Eco-friendly practices are everyday expectations, not niche differentiators.'],
  ['title'=>'Personalization drives loyalty',      'copy'=>'Tailored experiences lift retention and lifetime value across channels.'],
  ['title'=>'Omnichannel becomes table stakes',    'copy'=>'Customers want consistent journeys across in-store, web, and social.'],
];

$show_dots = !isset($attributes['show_dots']) || (bool)$attributes['show_dots'];

$heading_top_id    = $root_id . '-heading-top';
$heading_bottom_id = $root_id . '-heading-bottom';
$track_id          = $root_id . '-track';
?>
<section class="trends<?php echo esc_attr($align_class); ?>" id="<?php echo esc_attr($root_id); ?>" aria-labelledby="<?php echo esc_attr($heading_top_id); ?>">
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
            if ($d === '' && $r === '') { continue; }
          ?>
          <tr>
            <th scope="row"><?php echo esc_html($d); ?></th>
            <td><?php echo esc_html($r); ?></td>
          </tr>
          <?php endforeach; ?>
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
          $c = isset($s['copy'])  ? $s['copy']  : '';
          if ($t === '' && $c === '') { continue; }
        ?>
        <article class="trendcard">
          <?php if ($t !== ''): ?><h3 class="trendcard__title"><?php echo esc_html($t); ?></h3><?php endif; ?>
          <?php if ($c !== ''): ?><p class="trendcard__copy"><?php echo esc_html($c); ?></p><?php endif; ?>
        </article>
        <?php endforeach; ?>
      </div>

      <button class="carousel__arrow carousel__arrow--next" type="button" aria-label="Next slide">❯</button>

      <?php if ($show_dots): ?>
      <div class="carousel__dots" role="tablist" aria-label="Slide indicators">
        <?php $i = 1; foreach ($slides as $_): ?>
          <button class="dot<?php echo $i === 1 ? ' is-active' : ''; ?>" aria-label="<?php echo esc_attr('Slide ' . $i); ?>"></button>
        <?php $i++; endforeach; ?>
      </div>
      <?php endif; ?>
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
