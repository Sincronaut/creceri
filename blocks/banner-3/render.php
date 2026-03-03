<?php
if ( ! defined('ABSPATH') ) { exit; }

/** Reusable helpers (aligns with your existing pattern) */
function cban_val($arr, $key, $default = null){ return (is_array($arr) && array_key_exists($key,$arr)) ? $arr[$key] : $default; }
function cban_str($arr, $key, $default = ''){ $v = cban_val($arr,$key,$default); return is_string($v) ? $v : (is_null($v) ? '' : (string)$v); }
function cban_bool($arr, $key, $default = false){ return (bool)cban_val($arr,$key,$default); }
function cban_arr($arr, $key){ $v = cban_val($arr,$key,array()); return is_array($v) ? $v : array(); }

/** Attributes */
$A = is_array($attributes ?? null) ? $attributes : array();

$brand        = cban_str($A, 'brand', '');
$line         = cban_str($A, 'line', '');
$brandSize    = max(1, intval(cban_val($A, 'brandSize', 48)));
$bgSpot       = strtolower(cban_str($A, 'bgSpot', 'left'));   // left|right|center
$bgAngle      = is_numeric(cban_val($A,'bgAngle', 180)) ? intval($A['bgAngle']) : 180;
$bgStatus     = cban_str($A, 'bgStatus', 'Off');              // On|Off
$imgShadow    = cban_str($A, 'imgShadow', 'Off');             // On|Off
$lead         = cban_str($A, 'lead', '');
$imagePos     = strtolower(cban_str($A, 'imagePosition', 'right')); // right|left
$imageLoc     = strtolower(cban_str($A, 'imageLocation', 'bottom')); // top|center|bottom
$imageFlip    = strtolower(cban_str($A, 'imageFlip', 'none'));       // none|x|y|xy
$imageSize    = strtolower(cban_str($A, 'imageSize', 'l'));          // s|m|l
$buttonType   = cban_str($A, 'buttonType', 'Button');          // Button|Link|None
$buttonText   = cban_str($A, 'buttonText', '');
$buttonUrl    = cban_str($A, 'buttonUrl', '#');
$buttonClass  = cban_str($A, 'buttonClass', 'btn btn-custom text-white');
$textAlign    = strtolower(cban_str($A, 'textAlign', 'left'));
$textScale    = floatval(cban_val($A, 'textScale', 1.0));
$image        = cban_arr($A, 'image');
$imgSrc       = esc_url( cban_str($image, 'src', '') );
$imgAlt       = cban_str($image, 'alt', '');
$imgLoading   = in_array(cban_str($image, 'loading', 'eager'), array('lazy','eager'), true) ? $image['loading'] : 'eager';
$imgDecoding  = in_array(cban_str($image, 'decoding', 'async'), array('async','auto'), true) ? $image['decoding'] : 'async';

/** Instance scoping */
$instance_id  = 'banner-' . wp_generate_password(6, false, false);
$hero_title_id = $instance_id . '-title';

/** Class assembly */
$hero_classes = array('hero','banner-block','banner-block--v3');
$hero_classes[] = ($imagePos === 'left') ? 'hero--img-left' : 'hero--img-right';
$hero_classes[] = ($bgStatus === 'On') ? 'hero--bg-on' : 'hero--bg-off';
$hero_classes[] = 'ta-' . ($textAlign === 'center' ? 'center' : ($textAlign === 'right' ? 'right' : 'left'));

$art_classes = array('hero__art','mt-60','mt-sm-80','mt-md-100','mt-lg-120','mt-xl-140','mt-xxl-150');
$img_classes = array('hero__image');
$img_classes[] = 'hero__image--' . (in_array($imageSize, array('s','m','l'), true) ? $imageSize : 'l');
if ($imgShadow === 'On') { $img_classes[] = 'hero__image--shadow'; }
if ($imageFlip !== 'none') { $img_classes[] = 'flip-' . $imageFlip; }

$copy_classes = array('hero__copy','scale-' . str_replace('.', '-', number_format(max(0.5,min(1.5,$textScale)),2)));
$brand_mt_classes = array('title_h1-3','mt-60a','mt-sm-80a','mt-md-100a','mt-lg-120a','mt-xl-140a','mt-xxl-150a');

/** Inline style tokens */
$wrapper_style = '';
if ($bgStatus === 'On') {
  $spot = in_array($bgSpot, array('left','right','center'), true) ? $bgSpot : 'left';
  $wrapper_style = sprintf(
    'style="--banner-bg-spot:%s;--banner-bg-angle:%sdeg;"',
    esc_attr($spot),
    esc_attr($bgAngle)
  );
}
$brand_style = sprintf('style="font-size:%dpx;line-height:1.05;"', $brandSize);

/** Output guard: nothing to render if no copy and no image */
if ($brand === '' && $line === '' && $lead === '' && $imgSrc === '') { return; }

/** Scoped utilities (only once per instance) */
?>
<style>
  /* Scoped to this banner instance to avoid global bleed */
  #<?php echo esc_attr($instance_id); ?> .mt-60   { margin-top: 60px; }
  @media (min-width: 576px)  { #<?php echo esc_attr($instance_id); ?> .mt-sm-80  { margin-top: 80px; } }
  @media (min-width: 768px)  { #<?php echo esc_attr($instance_id); ?> .mt-md-100 { margin-top: 100px; } }
  @media (min-width: 992px)  { #<?php echo esc_attr($instance_id); ?> .mt-lg-120 { margin-top: 120px; } }
  @media (min-width: 1200px) { #<?php echo esc_attr($instance_id); ?> .mt-xl-140 { margin-top: 140px; } }
  @media (min-width: 1400px) { #<?php echo esc_attr($instance_id); ?> .mt-xxl-150{ margin-top: 150px; } }

  @media (min-width: 576px)  { #<?php echo esc_attr($instance_id); ?> .mt-sm-80a  { margin-top: 40px; } }
  @media (min-width: 768px)  { #<?php echo esc_attr($instance_id); ?> .mt-md-100a { margin-top: 50px; } }
  @media (min-width: 992px)  { #<?php echo esc_attr($instance_id); ?> .mt-lg-120a { margin-top: 60px; } }
  @media (min-width: 1200px) { #<?php echo esc_attr($instance_id); ?> .mt-xl-140a { margin-top: 70px; } }
  @media (min-width: 1400px) { #<?php echo esc_attr($instance_id); ?> .mt-xxl-150a{ margin-top: 80px; } }

  /* Optional: background hooks (implement in theme CSS if desired) */
  #<?php echo esc_attr($instance_id); ?>.hero--bg-on {
    /* Example gradient hook; override in theme */
    background:
      radial-gradient(600px 300px at var(--banner-bg-spot, left) 20%,
        rgba(0,0,0,0.10), transparent 60%)
      ,
      linear-gradient(var(--banner-bg-angle, 180deg),
        rgba(0,0,0,0.02), rgba(0,0,0,0.00));
  }

  /* Image flips */
  #<?php echo esc_attr($instance_id); ?> .flip-x  { transform: scaleX(-1); }
  #<?php echo esc_attr($instance_id); ?> .flip-y  { transform: scaleY(-1); }
  #<?php echo esc_attr($instance_id); ?> .flip-xy { transform: scale(-1,-1); }

  /* Text scaling hook */
  #<?php echo esc_attr($instance_id); ?> .scale-1-00 { transform: none; }
  /* class is present for semantics; real scaling should be handled by your typography system */
</style>

<section id="<?php echo esc_attr($instance_id); ?>"
  class="<?php echo esc_attr(implode(' ', $hero_classes)); ?>"
  aria-labelledby="<?php echo esc_attr($hero_title_id); ?>"
  style="margin-top:-130px;background:
    radial-gradient(
      calc(var(--bg-spot-w, 1200) * 1px)
      calc(var(--bg-spot-h, 600) * 1px)
      at calc(var(--bg-spot-x, 20) * 1% + var(--bg-spot-x-px, 0) * 1px)
         calc(var(--bg-spot-y, 50) * 1% + var(--bg-spot-y-px, 0) * 1px),
      var(--bg-left, #cee6f2) 0%,
      rgba(238,247,252,0) calc(var(--bg-spot-fade, 60) * 1%)
    ),
    linear-gradient(
      calc(var(--bg-angle, 230) * 1deg),
      var(--bg-mid, #f1f7fb) 40%,
      var(--bg-right, #e3867d) 100%
    );" <?php echo $wrapper_style; ?> >
  <div class="hero__inner">

    <?php if ($imgSrc !== ''): ?>
      <div class="<?php echo esc_attr(implode(' ', $art_classes)); ?>" style="align-self: <?php echo esc_attr($imageLoc); ?>;">
        <img
          class="<?php echo esc_attr(implode(' ', $img_classes)); ?>"
          src="<?php echo $imgSrc; ?>"
          alt="<?php echo esc_attr($imgAlt !== '' ? $imgAlt : $brand); ?>"
          loading="<?php echo esc_attr($imgLoading); ?>"
          decoding="<?php echo esc_attr($imgDecoding); ?>"
        />
      </div>
    <?php endif; ?>

    <div class="hero__copy">
      <?php if ($brand !== '' || $line !== ''): ?>
        <h1 id="<?php echo esc_attr($hero_title_id); ?>" class="<?php echo esc_attr(implode(' ', $brand_mt_classes)); ?> hero__heading">
          <?php if ($brand !== ''): ?>
            <span class="hero__brand"><?php echo esc_html($brand); ?></span>
          <?php endif; ?>
          <?php if ($line !== ''): ?>
            <span class="hero__line"><?php echo wp_kses_post($line); ?></span>
          <?php endif; ?>
        </h1>
      <?php endif; ?>

      <?php if ($lead !== ''): ?>
        <p class="hero__lead"><?php echo esc_html($lead); ?></p>
      <?php endif; ?>

      <?php if (strtolower($buttonType) !== 'none' && $buttonText !== ''): ?>
        <a class="<?php echo esc_attr($buttonClass); ?> btn-pill" href="<?php echo esc_url($buttonUrl); ?>">
          <?php echo esc_html($buttonText); ?>
        </a>
      <?php endif; ?>
    </div>
  </div>
</section>
