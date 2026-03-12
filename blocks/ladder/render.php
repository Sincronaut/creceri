<?php
/**
 * Render callback: child/blog-cats
 * Purpose: Categories accordion with intro and per-category bullets + CTA.
 */
if ( ! defined('ABSPATH') ) { exit; }
if ( empty($attributes) || ! is_array($attributes) ) { return; }

$A = $attributes;

/** Core */
$anchor     = isset($A['anchor']) ? sanitize_title($A['anchor']) : '';
$className  = isset($A['className']) ? sanitize_html_class($A['className']) : '';
$section_id = isset($A['sectionId']) ? sanitize_title($A['sectionId']) : 'blog-categories';

$title = isset($A['title']) ? wp_kses_post($A['title'])
  : 'Categories We Cover in Our Technology Blogs';

$intro = isset($A['intro']) ? wp_kses_post($A['intro'])
  : "Our technology blogs cover a wide range of topics that reflect the tools, platforms, and strategies shaping today’s digital landscape. Each category is written to offer clear, practical takeaways, whether you’re building something new, improving what you have, or exploring fresh ideas.";

/** Items schema:
 * items: [
 *   {
 *     "heading": "E-Commerce Development",
 *     "open": true,
 *     "ctaLabel": "Explore",
 *     "ctaUrl": "#",
 *     "bullets": [
 *        {"title":"Magento Development","desc":"Scalable, customizable setups for complex online stores"},
 *        ...
 *     ]
 *   }, ...
 * ]
 */
$default_items = array(
  array(
    'heading'  => 'E-Commerce Development',
    'open'     => true,
    'ctaLabel' => 'Explore',
    'ctaUrl'   => '#',
    'bullets'  => array(
      array('title'=>'Magento Development',  'desc'=>'Scalable, customizable setups for complex online stores'),
      array('title'=>'Shopify Development',  'desc'=>'Streamlined storefronts built for smooth shopping experiences'),
      array('title'=>'WooCommerce Development','desc'=>'Flexible eCommerce within the WordPress ecosystem'),
      array('title'=>'B2B eCommerce',        'desc'=>'Solutions tailored for business-to-business transactions'),
      array('title'=>'B2C eCommerce',        'desc'=>'Approaches that connect directly with consumer audiences'),
    ),
  ),
  array(
    'heading'  => 'Web & Custom Development',
    'open'     => false,
    'ctaLabel' => 'Explore',
    'ctaUrl'   => '#',
    'bullets'  => array(
      array('title'=>'CMS Development',       'desc'=>'A CMS (like WordPress, Shopify, or Magento) helps you build and manage a website without heavy coding.'),
      array('title'=>'Custom Web Development','desc'=>'Custom development means building your site from scratch to fit your exact needs.'),
    ),
  ),
  array(
    'heading'  => 'Design & Prototyping',
    'open'     => false,
    'ctaLabel' => 'Explore',
    'ctaUrl'   => '#',
    'bullets'  => array(
      array('title'=>'UI/UX Design & Prototyping', 'desc'=>'Turn ideas into interactive journeys with wireframes and prototypes that reveal how users really move through your product.'),
      array('title'=>'UI/UX Design',               'desc'=>'Craft interfaces that are clean, intuitive, and user-first. Strong design makes navigation effortless and experiences memorable.'),
      array('title'=>'Figma Prototyping',          'desc'=>'Test, iterate, and collaborate in real time to align design with development.'),
      array('title'=>'App Design',                 'desc'=>'Build mobile and web apps that balance usability with style and keep users engaged.'),
    ),
  ),
  array(
    'heading'  => 'Digital Marketing & Growth',
    'open'     => false,
    'ctaLabel' => 'Explore',
    'ctaUrl'   => '#',
    'bullets'  => array(
      array('title'=>'Search Engine Optimization (SEO)', 'desc'=>'Helps websites show up on Google and other search engines'),
      array('title'=>'Social Media Marketing',           'desc'=>'Build relationships, spark conversations, and create engagement'),
      array('title'=>'Search Engine Marketing (SEM)',    'desc'=>'Use paid ads on search engines to get instant visibility'),
      array('title'=>'Lead Generation',                  'desc'=>'Turn interest into action through forms, sign-ups, and campaigns'),
    ),
  ),
  array(
    'heading'  => 'Team & Talent Solutions',
    'open'     => false,
    'ctaLabel' => 'Explore',
    'ctaUrl'   => '#',
    'bullets'  => array(
      array('title'=>'Team Extension Model','desc'=>'Scale smarter with the right talent integrated directly into your workflow.'),
    ),
  ),
);

$items = (isset($A['items']) && is_array($A['items'])) ? $A['items'] : $default_items;

/** Classes */
$classes = array('blog-cats');
if ($className) { $classes[] = $className; }
if ($anchor)    { $classes[] = $anchor; }

/** Label id for a11y */
$label_id = $section_id ? $section_id . '-title' : 'blog-cats-title';

?>
<section id="<?php echo esc_attr($section_id); ?>" style="margin-top:50px;"
         class="<?php echo esc_attr(implode(' ', $classes)); ?>"
         aria-labelledby="<?php echo esc_attr($label_id); ?>">
  <div class="blog-cats__container">
    <?php if ($title): ?>
      <h2 id="<?php echo esc_attr($label_id); ?>" class="blog-cats__title"><?php echo $title; ?></h2>
    <?php endif; ?>

    <?php if ($intro): ?>
      <p class="blog-cats__intro"><?php echo $intro; ?></p>
    <?php endif; ?>

    <div class="blog-cats__list" role="list">
      <?php foreach ($items as $it):
        $heading  = isset($it['heading'])  ? sanitize_text_field($it['heading']) : '';
        $open     = !empty($it['open']);
        $ctaLabel = isset($it['ctaLabel']) ? sanitize_text_field($it['ctaLabel']) : '';
        $ctaUrl   = isset($it['ctaUrl'])   ? esc_url($it['ctaUrl']) : '';
        $bullets  = (isset($it['bullets']) && is_array($it['bullets'])) ? $it['bullets'] : array();

        if (trim($heading)==='' && empty($bullets)) { continue; }
      ?>
        <details class="bc-acc" <?php echo $open ? 'open' : ''; ?> role="listitem">
          <summary><span><?php echo esc_html($heading); ?></span></summary>
          <div class="bc-acc__panel">
            <?php if (!empty($bullets)): ?>
              <ul class="bc-acc__bullets">
                <?php foreach ($bullets as $b):
                  $bt = isset($b['title']) ? sanitize_text_field($b['title']) : '';
                  $bd = isset($b['desc'])  ? wp_kses_post($b['desc']) : '';
                  if (trim($bt.$bd)==='') { continue; }
                ?>
                  <li>
                    <?php if ($bt): ?><strong><?php echo esc_html($bt); ?></strong><?php endif; ?>
                    <?php if ($bd): ?> &ndash; <?php echo $bd; ?><?php endif; ?>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>

            <?php if ($ctaLabel && $ctaUrl): ?>
              <a class="bc-acc__cta" href="<?php echo $ctaUrl; ?>"><?php echo esc_html($ctaLabel); ?></a>
            <?php endif; ?>
          </div>
        </details>
      <?php endforeach; ?>
    </div>
  </div>
</section>
