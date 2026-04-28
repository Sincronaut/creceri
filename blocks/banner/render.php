<?php 
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Allowlists */
if ( ! function_exists( 'child_banner_allowed_line_html' ) ) {
  function child_banner_allowed_line_html() {
    return array(
      'br' => array(),
      'span' => array('class' => true),
      'strong' => array(), 'b' => array(),
      'em' => array(), 'i' => array()
    );
  }
}
if ( ! function_exists( 'child_banner_allowed_lead_html' ) ) {
  function child_banner_allowed_lead_html() {
    return array(
      'a' => array('href'=>true,'title'=>true,'target'=>true,'rel'=>true),
      'br' => array(),
      'strong' => array(), 'b' => array(),
      'em' => array(), 'i' => array(),
      'span' => array('class' => true)
    );
  }
}

/** Normalize attributes */
$A = is_array($attributes ?? null) ? $attributes : array();
$anchor     = $A['anchor'] ?? '';
$className  = $A['className'] ?? '';

/** Layout knobs */
$margin_top   = isset($A['margin-top']) ? trim($A['margin-top']) : '0px';
$margin_right = isset($A['margin-right']) ? trim($A['margin-right']) : '0px';
$margin_bottom = isset($A['margin-bottom']) ? trim($A['margin-bottom']) : '0px';

$bgStatus = (($A['bgStatus'] ?? 'Off') === 'On') ? 'On' : 'Off';

$brand     = $A['brand'] ?? 'Creceri';
$brandSize = isset($A['brandSize']) ? floatval($A['brandSize']) : 0; // px contract

$line = $A['line'] ?? 'E-commerce, UX,<br>and Digital Knowledge';
$rotatingLines = $A['rotatingLines'] ?? '';
$rotatingBg = $A['rotatingBg'] ?? 'rgba(150, 46, 42, 0.1)';
$lead = $A['lead'] ?? '';
$lead_weight = $A['lead_weight'] ?? '';

$buttonType = in_array(($A['buttonType'] ?? 'Button'), array('Button','Search','none'), true) ? $A['buttonType'] : 'Button';
$btnText  = $A['buttonText'] ?? 'Search';
$btnUrl   = $A['buttonUrl'] ?? '#explore';
$btnClass = $A['buttonClass'] ?? 'btn btn-pill text-white btn-pill';

$image   = is_array($A['image'] ?? null) ? $A['image'] : array();
$img_src = $image['src'] ?? 'wp-content/uploads/2025/10/693a7a2703163f47b412f648b6da08ad8485fd2a.webp';
$img_alt = $image['alt'] ?? '3D illustration';
$img_w   = !empty($image['width']) ? intval($image['width']) : 0;
$img_h   = !empty($image['height']) ? intval($image['height']) : 0;
$img_dec = $image['decoding'] ?? 'async';
$img_load= $image['loading'] ?? 'eager';

$imagePosition = in_array(($A['imagePosition'] ?? 'right'), array('left','right'), true) ? $A['imagePosition'] : 'right';
$imageLocation = in_array(($A['imageLocation'] ?? 'center'), array('top','center','bottom'), true) ? $A['imageLocation'] : 'center';
$imageSize     = in_array(($A['imageSize'] ?? 'l'), array('xs','s','m','l','xl'), true) ? $A['imageSize'] : 'l';
$imageFlip     = in_array(($A['imageFlip'] ?? 'none'), array('none','mirror'), true) ? $A['imageFlip'] : 'none';

$textAlign = in_array(($A['textAlign'] ?? 'auto'), array('auto','left','center','right'), true) ? $A['textAlign'] : 'auto';
$textScale = isset($A['textScale']) ? floatval($A['textScale']) : 1.0;
$uiScale   = isset($A['uiScale']) ? floatval($A['uiScale']) : 1.0;

$resolvedAlign = ('auto' === $textAlign)
  ? ( 'right' === $imagePosition ? 'left' : 'right' )
  : $textAlign;

$section_id = $anchor ?: 'hero-' . wp_generate_password(6, false, false);
$title_id   = ($A['titleId'] ?? '') ?: $section_id . '-title';
$line_html  = wp_kses($line, child_banner_allowed_line_html());
$lead_html  = wp_kses($lead, child_banner_allowed_lead_html());

/** Classes */
$reveal = $A['reveal'] ?? '';
$classes = array(
  'hero',
  'banner-block',
  'banner-block--v1',
  'hero--img-' . $imagePosition,
  'hero--imgloc-' . $imageLocation,
  'hero--imgsize-' . $imageSize,
  'hero--align-' . ($textAlign === 'auto' ? ($imagePosition === 'right' ? 'left' : 'right') : $textAlign),
  'hero--flip-' . $imageFlip,
  'hero--bg-' . ($A['bgSpot'] ?? 'left'),
  'hero--shadow-' . (($A['imgShadow'] ?? 'On') === 'Off' ? 'off' : 'on')
);
if ( $bgStatus === 'On' ) { $classes[] = 'hero--bleed-top'; }
if ( $className ) { $classes[] = $className; }
if ( $reveal && $reveal !== 'split' ) { $classes[] = 'reveal-' . sanitize_html_class($reveal); }

/** Style vars */
$style_vars = array(
  '--ui-scale:' . ($uiScale ?: 1),
  '--text-scale:' . ($textScale ?: 1),
  '--bg-angle:'   . (float)($A['bgAngle'] ?? 230),
  '--bg-spot-w:'  . (float)($A['bgSpotW'] ?? 1200),
  '--bg-spot-h:'  . (float)($A['bgSpotH'] ?? 600),
  '--bg-spot-x:'  . (float)($A['bgSpotX'] ?? 20),
  '--bg-spot-y:'  . (float)($A['bgSpotY'] ?? 50),
  '--bg-spot-fade:' . (float)($A['bgSpotFade'] ?? 60),
  '--bg-left:'  . ($A['bgLeft'] ?? '#cee6f2'),
  '--bg-mid:'   . ($A['bgMid'] ?? '#f1f7fb'),
  '--bg-right:' . ($A['bgRight'] ?? '#e3867d'),
  '--bleed-offset:' . (float)($A['bleedOffset'] ?? 88),
  '--rotating-bg:' . $rotatingBg,
);

/** Brand size: emit px, set enabling class */
if ($brandSize > 0) {
  $brandSize = min(300, $brandSize);
  $style_vars[] = '--brand-size:' . $brandSize . 'px';
  $classes[] = 'hero--brand-dynamic';
}

/** Image scale map */
$image_scale_map = array('xs' => 0.6, 's' => 0.8, 'm' => 1.0, 'l' => 1.2, 'xl' => 1.5);
$style_vars[] = '--image-scale:' . ($image_scale_map[$imageSize] ?? 1);

$style_attr = implode(';', $style_vars);

/** Split reveal classes */
$copy_reveal_class = '';
$art_reveal_class  = '';
if ($reveal === 'split') {
  $copy_reveal_class = ($imagePosition === 'right' ? 'reveal-left' : 'reveal-right');
  $art_reveal_class  = ($imagePosition === 'right' ? 'reveal-right' : 'reveal-left');
}
?>
<section class="<?php echo esc_attr(implode(' ', $classes)); ?>"
         style="<?php echo esc_attr($style_attr); ?>;"
         aria-labelledby="<?php echo esc_attr($title_id); ?>"
         id="<?php echo esc_attr($section_id); ?>">

  <div class="hero__inner">
    <div class="hero__copy <?php echo esc_attr($copy_reveal_class); ?>" >
      <h1 id="<?php echo esc_attr($title_id); ?>" class="title_h1">
       <span class="hero__brand"><?php echo $brand; ?></span>
       <?php if (!empty($rotatingLines)) : 
         $lines = array_map('trim', explode(',', $rotatingLines));
       ?>
         <span class="hero__line hero__line--rotating">
           <span class="rotating-text-scroller">
             <?php foreach ($lines as $index => $item) : ?>
               <span class="rotating-text-item <?php echo $index === 0 ? 'active' : ''; ?>"><?php echo esc_html($item); ?></span>
             <?php endforeach; ?>
           </span>
         </span>
       <?php elseif (!empty($line_html)) : ?>
         <span class="hero__line"><?php echo $line_html; ?></span>
       <?php endif; ?>
      </h1>

      <?php if (!empty($lead_weight)) : ?>
        <p class="hero__lead hero__lead--hook"><?php echo $lead_weight; ?></p>
      <?php endif; ?>

      <?php if (!empty($lead)) : ?>
        <p class="hero__lead"><?php echo $lead_html; ?></p>
      <?php endif; ?>

      <?php if ($buttonType === 'Button') : ?>
        <a class="<?php echo esc_attr($btnClass); ?>" href="<?php echo esc_url($btnUrl); ?>">
          <?php echo esc_html($btnText); ?>
        </a>
      <?php elseif ($buttonType === 'Search') : ?>
        <form class="hero__search" action="<?php echo esc_url(home_url('/')); ?>" method="get" role="search">
          <label class="screen-reader-text" for="<?php echo esc_attr($section_id . '-s'); ?>">Search</label>
          <input id="<?php echo esc_attr($section_id . '-s'); ?>" class="hero__input" type="search" name="s" placeholder="<?php echo esc_attr($btnText); ?>" />
          <button type="submit" class="hero__icon" aria-label="<?php echo esc_attr($btnText); ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
              <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
          </button>
        </form>
      <?php endif; ?>
    </div>

    <div class="hero__art1 <?php echo esc_attr($art_reveal_class); ?>">
        <img
          class="hero__image1"
          src="<?php echo esc_url($img_src); ?>"
          alt="<?php echo esc_attr($img_alt); ?>"
          loading="<?php echo esc_attr($img_load); ?>"
          decoding="<?php echo esc_attr($img_dec); ?>" />
    </div>
  </div>
</section>

<?php if (!empty($rotatingLines)) : ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const section = document.getElementById('<?php echo esc_js($section_id); ?>');
    if (!section) return;
    
    const items = section.querySelectorAll('.rotating-text-item');
    const wrapper = section.querySelector('.hero__line--rotating');
    const scroller = section.querySelector('.rotating-text-scroller');
    if (!items.length || !wrapper || !scroller) return;
    
    let currentIndex = 0;
    const itemHeight = items[0].offsetHeight || 0; // Fallback to CSS or measure
    const getWrapperWidth = () => {
      const widths = Array.from(items).map(item => item.scrollWidth || item.offsetWidth || 0);
      const maxItemWidth = widths.length ? Math.max(...widths) : 0;
      const availableWidth = wrapper.parentElement ? wrapper.parentElement.clientWidth : section.clientWidth;

      return Math.min(maxItemWidth + 12, availableWidth || maxItemWidth || 0);
    };
    
    function updateWidthAndScroll() {
        // Clear all active classes first
        items.forEach(item => item.classList.remove('active'));
        
        const currentItem = items[currentIndex];
        if (currentItem) {
            currentItem.classList.add('active');
            
            const isHome = section.classList.contains('home-hero-banner');
            
            if (!isHome) {
                // Original logic for non-home banners: dynamic width
                const width = currentItem.offsetWidth + 5;
                wrapper.style.width = width + 'px';
            }

            // Scroll to the current index
            const offsetFactor = isHome ? 1.6 : 1.5;
            const offset = currentIndex * offsetFactor; // Based on em from CSS
            scroller.style.transform = `translateY(-${offset}em)`;
        }
    }
    
    // Initial setup
    const isHome = section.classList.contains('home-hero-banner');
    if (isHome) {
        wrapper.style.width = getWrapperWidth() + 'px';
    }
    updateWidthAndScroll();
    window.addEventListener('resize', () => {
      if (isHome) {
          wrapper.style.width = getWrapperWidth() + 'px';
      }
      updateWidthAndScroll();
    });
    
    setInterval(() => {
        currentIndex++;
        
        // Return to start if reached the end
        if (currentIndex >= items.length) {
            currentIndex = 0;
        }
        
        updateWidthAndScroll();
    }, 3000);
});
</script>
<?php endif; ?>
