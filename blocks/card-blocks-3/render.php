<?php
/**
 * Ecom Picks — dynamic render
 * Usage pattern mirrors the provided "Tek Stories" sample.
 * Expected $attributes schema:
 * - title: string
 * - items: array of {
 *     slug: string (used as CSS modifier, e.g., "magento", "shopify", "woo")
 *     title: string
 *     logo: string (image URL)
 *     description: string (allows <br>, <strong>, <em>, <a>)
 *     trusted: array of { image: string, alt: string, width: int }
 *     ctaText: string (default "Explore")
 *     ctaUrl: string (URL for CTA)
 *     hideCta: bool
 *   }
 */

if (!function_exists('tek_val')) {
  function tek_val($arr,$key,$default=null){ return (is_array($arr)&&array_key_exists($key,$arr))?$arr[$key]:$default; }
}
if (!function_exists('tek_bool')) {
  function tek_bool($arr,$key,$default=false){ return (bool)tek_val($arr,$key,$default); }
}
if (!function_exists('tek_str')) {
  function tek_str($arr,$key,$default=''){ $v=tek_val($arr,$key,$default); return is_string($v)?$v:((is_null($v))?'':strval($v)); }
}
if (!function_exists('tek_url_from')) {
  function tek_url_from($node, $fallback='#'){
    if(!is_array($node)) return $fallback;
    $direct = tek_str($node,'ctaUrl','');
    $link   = tek_val($node,'link',[]);
    $u2     = tek_str($link,'url','');
    $u      = $direct !== '' ? $direct : $u2;
    return $u !== '' ? $u : $fallback;
  }
}

/** Fallback assets */
$FALLBACK_LOGO  = 'https://via.placeholder.com/120x40?text=Logo';
$FALLBACK_BRAND = 'https://via.placeholder.com/70x30?text=Brand';

/** Attributes */
$title = tek_str($attributes,'title','Top Picked E-Commerce Platforms in 2025');
$items = is_array($attributes['items'] ?? null) ? $attributes['items'] : [];

/** Normalize + prune */
$items = array_values(array_filter($items, function($it){
  if (!is_array($it)) return false;
  $hasCore = (trim(tek_str($it,'title','')) !== '') || (trim(tek_str($it,'logo','')) !== '');
  return $hasCore;
}));

/** Placeholder if empty */
if (empty($items)) {
  $items = [[
    'slug'        => 'coming-soon',
    'title'       => 'Coming Soon',
    'logo'        => $FALLBACK_LOGO,
    'description' => 'We’re curating the best platforms. Check back shortly.',
    'trusted'     => [],
    'ctaText'     => '',
    'ctaUrl'      => '',
    'hideCta'     => true,
  ]];
}

/** Render */
?>
<section class="ecom-picks">
  <div class="wrap_card">
    <?php if ($title !== ''): ?>
      <h2><?php echo esc_html($title); ?></h2>
    <?php endif; ?>

    <div class="cards">
      <?php foreach ($items as $item):
        $slug     = sanitize_html_class(tek_str($item,'slug',''));
        $name     = trim(tek_str($item,'title',''));
        $logo     = trim(tek_str($item,'logo','')) ?: $FALLBACK_LOGO;
        $raw_desc = tek_str($item,'description','');
        // Allow minimal inline markup in descriptions
        $desc = wp_kses(
          $raw_desc,
          [
            'br'     => [],
            'strong' => [],
            'em'     => [],
            'a'      => ['href'=>[], 'target'=>[], 'rel'=>[]],
          ]
        );

        $trusted  = tek_val($item,'trusted',[]);
        $trusted  = is_array($trusted) ? array_values(array_filter($trusted, function($b){
          return is_array($b) && (trim(tek_str($b,'image','')) !== '' || trim(tek_str($b,'alt','')) !== '');
        })) : [];

        $ctaText  = trim(tek_str($item,'ctaText','Explore'));
        $ctaUrl   = tek_url_from($item,'');
        $hideCta  = tek_bool($item,'hideCta',false) || ($ctaText === '' || $ctaUrl === '');
        $cardCls  = 'card'.($slug ? ' '.$slug : '');
      ?>
        <article class="<?php echo esc_attr($cardCls); ?>">
          <div class="hero_card">
            <div class="logo">
              <img src="<?php echo esc_url($logo); ?>" alt="<?php echo esc_attr($name !== '' ? $name.' logo' : 'Platform logo'); ?>">
            </div>
          </div>

          <?php if ($name !== ''): ?>
            <h3><?php echo esc_html($name); ?></h3>
          <?php endif; ?>

          <?php if ($desc !== ''): ?>
            <p><?php echo $desc; // already kses filtered ?></p>
          <?php endif; ?>

          <?php if (!empty($trusted)): ?>
            <div class="trusted">
              <small>Trusted by:</small>
              <?php foreach ($trusted as $b):
                $bImg   = trim(tek_str($b,'image','')) ?: $FALLBACK_BRAND;
                $bAlt   = tek_str($b,'alt','');
                $bWidth = intval(tek_val($b,'width',70));
                $bWidth = $bWidth > 0 ? $bWidth : 70;
              ?>
                <img src="<?php echo esc_url($bImg); ?>"
                     style="<?php echo esc_attr('width:'.$bWidth.'px;'); ?>"
                     alt="<?php echo esc_attr($bAlt !== '' ? $bAlt : 'Brand'); ?>">
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <?php if (!$hideCta): ?>
            <a class="btn-cta btn-pill" href="<?php echo esc_url($ctaUrl); ?>">
              <?php echo esc_html($ctaText); ?>
            </a>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
