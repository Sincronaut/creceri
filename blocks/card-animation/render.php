<?php
/**
 * Block: Guides & Insights (Rail Cards)
 * Path: https://creceri.com/wp-content/themes/your-child-theme/blocks/guides/render.php
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* Helpers */
function gi_allowed_text_html() {
  return array(
    'a' => array('href'=>true,'title'=>true,'target'=>true,'rel'=>true),
    'br'=>array(), 'strong'=>array(), 'b'=>array(), 'em'=>array(), 'i'=>array(),
    'span'=>array('class'=>true)
  );
}
function gi_slug( $str, $fallback = 'guides' ) {
  $slug = sanitize_title( (string) $str );
  return $slug ? $slug : $fallback . '-' . wp_rand(100,999);
}

/* Read attributes */
$attrs      = is_array( $attributes ?? null ) ? $attributes : array();
$className  = $attrs['className'] ?? '';

$blockTitle = $attrs['title']   ?? 'Guides & Insights';
$intro      = $attrs['intro']   ?? 'Explore actionable guides and expert tips covering e-commerce, design, marketing, and more.';
$titleId    = ($attrs['titleId'] ?? '') ?: 'guides-title';

$items      = is_array($attrs['items'] ?? null) ? $attrs['items'] : array();
$ctaDefault = $attrs['ctaText'] ?? 'Check more';
$khtml      = gi_allowed_text_html();

/* Ensure at least one active item (fallback to first) */
$has_active = false;
foreach ($items as $it) { if (!empty($it['active'])) { $has_active = true; break; } }
if (!$has_active && !empty($items)) { $items[0]['active'] = true; }

/* Wrapper classes + unique section id for scoping */
$wrapper_classes = trim('guides ' . $className);
$section_uid     = 'guides-' . gi_slug($titleId);
?>
<section class="<?php echo esc_attr($wrapper_classes); ?>" aria-labelledby="<?php echo esc_attr($titleId); ?>" id="<?php echo esc_attr($section_uid); ?>" data-guides-id="<?php echo esc_attr($section_uid); ?>">
  <div class="g-wrap">
    <header class="g-head"><br>
      <h2 id="<?php echo esc_attr($titleId); ?>"><?php echo esc_html($blockTitle); ?></h2>
      <?php if ( $intro ) : ?><p><?php echo wp_kses( $intro, $khtml ); ?></p><?php endif; ?>
    </header>

    <ul class="g-rail" role="list">
      <?php
      $i = 0;
      foreach ( $items as $item ) :
        $i++;
        $is_active  = !empty($item['active']);
        $title      = $item['title'] ?? '';
        $text       = $item['text']  ?? '';
        $url        = $item['url']   ?? '';
        $ctaText    = $item['ctaText'] ?? $ctaDefault;
        $target     = !empty($item['target']) ? $item['target'] : '';
        $img        = is_array($item['image'] ?? null) ? $item['image'] : array();
        $img_src    = $img['src'] ?? '';
        $img_alt    = trim($img['alt'] ?? '');
        $bgColor    = $item['bgColor'] ?? '';
        $li_cls     = 'g-item' . ( $is_active ? ' active' : '' );
        $aria       = $title ? sprintf( 'Open %s', $title ) : 'Open';
        $style_bg   = $bgColor ? 'background:' . esc_attr($bgColor) . ';' : '';

        $rel_parts  = array();
        if ( $target === '_blank' ) { $rel_parts[] = 'noopener'; $rel_parts[] = 'noreferrer'; }
        $rel_attr   = implode( ' ', $rel_parts );
      ?>
        <li class="<?php echo esc_attr($li_cls); ?>" data-index="<?php echo esc_attr($i); ?>" style="<?php echo esc_attr($style_bg); ?>">
          <div class="g-link" role="button" tabindex="0" aria-label="<?php echo esc_attr($aria); ?>" aria-expanded="<?php echo $is_active ? 'true' : 'false'; ?>">
            <?php if ($img_src) : ?>
              <img class="g-media" src="<?php echo esc_url($img_src); ?>" alt="<?php echo esc_attr($img_alt); ?>" />
            <?php endif; ?>

            <div class="g-overlay">
              <?php if ( $title ) : ?><h3><?php echo esc_html($title); ?></h3><?php endif; ?>
              <?php if ( $text  ) : ?><p class="g-desc"><?php echo wp_kses($text, $khtml); ?></p><?php endif; ?>

              <?php if ( $url ) : ?>
                <div class="g-cta-wrap">
                  <a class="g-cta btn-pill"
                     href="<?php echo esc_url($url); ?>"
                     aria-label="<?php echo esc_attr( $title ? 'Learn more about ' . $title : $ctaText ); ?>"
                     <?php if ($target) : ?> target="<?php echo esc_attr($target); ?>"<?php endif; ?>
                     <?php if ($rel_attr) : ?> rel="<?php echo esc_attr($rel_attr); ?>"<?php endif; ?>>
                    <?php echo esc_html($ctaText); ?>
                  </a>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<script>
/**
 * Fixes:
 * 1) Per-instance scoping (supports multiple blocks).
 * 2) Mobile tap reliability via pointer events and pointerup.
 * 3) CTA anchors always navigate.
 */
(function(){
  const sections = document.querySelectorAll('section.guides[data-guides-id]');
  if (!sections.length) return;

  sections.forEach((section) => {
    const rail = section.querySelector('.g-rail');
    if (!rail) return;

    const isMobile = () => window.matchMedia('(max-width: 760px)').matches;

    function activate(li) {
      // clear existing
      rail.querySelectorAll('.g-item.active').forEach((n) => {
        if (n !== li) {
          n.classList.remove('active');
          const b = n.querySelector('.g-link');
          if (b) b.setAttribute('aria-expanded', 'false');
        }
      });

      if (isMobile()) {
        // Move to top so :nth-child(1) rules apply reliably
        rail.insertBefore(li, rail.firstElementChild);
      }

      li.classList.add('active');
      const btn = li.querySelector('.g-link');
      if (btn) btn.setAttribute('aria-expanded', 'true');
    }

    // Hover support for desktop
    rail.addEventListener('mousemove', function(e) {
      if (isMobile()) return;
      const btn = e.target.closest('.g-link');
      if (!btn) return;
      const li = btn.closest('.g-item');
      if (li && !li.classList.contains('active')) {
        activate(li);
      }
    });

    // Delegate activation — pointerup is friendlier on mobile than click
    const onActivate = (e) => {
      // ignore CTA taps
      if (e.target.closest('.g-cta')) return;
      const btn = e.target.closest('.g-link');
      if (!btn || !rail.contains(btn)) return;
      const li = btn.closest('.g-item');
      if (!li) return;
      activate(li);
    };

    rail.addEventListener('pointerup', onActivate);
    rail.addEventListener('click', onActivate); // fallback

    // Keyboard support
    rail.addEventListener('keydown', function(e){
      if (e.key !== 'Enter' && e.key !== ' ') return;
      const btn = e.target.closest('.g-link');
      if (!btn || !rail.contains(btn)) return;
      e.preventDefault();
      const li = btn.closest('.g-item');
      if (!li) return;
      activate(li);
    });

    // Ensure CTA inside this section always navigates (no rail activation)
    section.addEventListener('click', function(e){
      if (e.target.closest('.g-cta')) {
        e.stopPropagation();
      }
    }, true);
  });
})();
</script>
