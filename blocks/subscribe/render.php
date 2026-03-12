<?php
/**
 * Server-rendered block: child/subscribe
 * Renders either the designed signup section ("code") or a static image ("image").
 */

$attrs = $attributes ?? [];

// Attributes with fallbacks
$variant = $attrs['variant'] ?? 'code'; // 'code' | 'image'
$section_id = $attrs['sectionId'] ?? 'subscribe';
$subhead = $attrs['subhead'] ?? '';
$subhead1 = $attrs['subhead1'] ?? '';
$title = $attrs['title'] ?? '';
$title1 = $attrs['title1'] ?? '';
$kicker = $attrs['kicker'] ?? '';
$kicker1 = $attrs['kicker1'] ?? '';

$bullets = is_array($attrs['bullets'] ?? null) ? $attrs['bullets'] : [
  ['text' => ''],
  ['text' => ''],
  ['text' => ''],
  ['text' => ''],
  ['text' => ''],
];

$form_heading = $attrs['formHeading'] ?? 'Want updates delivered directly to you?';
$name_ph = $attrs['namePlaceholder'] ?? 'Name';
$email_ph = $attrs['emailPlaceholder'] ?? 'Email';
$button_text = $attrs['buttonText'] ?? 'Send Me Updates';
$disclaimer = $attrs['disclaimer'] ?? '';
$disclaimer1 = $attrs['disclaimer1'] ?? 'No spam, just insights and trends.';
$form_action = $attrs['formAction'] ?? '#';
$form_method = $attrs['formMethod'] ?? 'post';

$image = $attrs['image'] ?? [];
$image_src = $image['src'] ?? '';
$image_alt = $image['alt'] ?? '';
$image_loading = $image['loading'] ?? 'lazy';
$image_decoding = $image['decoding'] ?? 'async';

// Helpers
function child_subscribe_safe_text($v)
{
  return esc_html(wp_strip_all_tags((string)$v));
}
function child_subscribe_bullets($items)
{
  $out = '';
  foreach ($items as $item) {
    $text = isset($item['text']) ? $item['text'] : '';
    if ($text === '')
      continue;
    $out .= '<li><span class="li-icon" aria-hidden="true"></span><span class="li-text">' . child_subscribe_safe_text($text) . '</span></li>';
  }
  return $out;
}

// Variant: Image only
if ($variant === 'image'): ?>
<section id="<?php echo esc_attr($section_id); ?>" class="insight-section insight-section--imageonly" data-variant="image">
  <?php if (!empty($image_src)): ?>
    <img class="insight-image" src="<?php echo esc_url($image_src); ?>"
         alt="<?php echo esc_attr($image_alt); ?>"
         loading="<?php echo esc_attr($image_loading); ?>"
         decoding="<?php echo esc_attr($image_decoding); ?>" />
  <?php
  else: ?>
    <div class="insight-image--placeholder" role="img" aria-label="Subscribe section image placeholder"></div>
  <?php
  endif; ?>
</section>
<?php return;
endif;
// Variant: Code (designed section)
?>
<section id="<?php echo esc_attr($section_id); ?>" class="insight-section" data-variant="code" aria-labelledby="<?php echo esc_attr($section_id); ?>-title">
  <div class="content">
    <h2 class="subhead"><?php echo child_subscribe_safe_text($subhead); ?></h2>
    <h2 class="subhead1"><?php echo child_subscribe_safe_text($subhead1); ?></h2>
    

    <div class="what-youll-find">
      <p id="<?php echo esc_attr($section_id); ?>-title" class="headline"><?php echo child_subscribe_safe_text($title1); ?></p>
      <p class="list-heading"><?php echo child_subscribe_safe_text($kicker); ?></p>
      <p class="list-heading1"><?php echo child_subscribe_safe_text($kicker1); ?></p>
      <ul class="value-list">
        <?php echo wp_kses_post(child_subscribe_bullets($bullets)); ?>
      </ul>
       <p id="<?php echo esc_attr($section_id); ?>-title" class="headline"><?php echo child_subscribe_safe_text($title); ?></p>
    
    </div>
    
  </div>

  <div class="signup-box" role="form" aria-label="<?php echo esc_attr($form_heading); ?>">
    <h3 class="form-title"><?php echo child_subscribe_safe_text($form_heading); ?></h3>
    <form
      action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>"
      data-endpoint="<?php echo esc_url(admin_url('admin-ajax.php')); ?>"
      method="post"
      novalidate
      data-subscribe-form="true"
      data-success="Thank you for subscribing to our News Letter">
      <input type="hidden" name="action" value="child_subscribe_submit" />
      <input type="hidden" name="child_subscribe_nonce" value="<?php echo esc_attr(wp_create_nonce('child_subscribe')); ?>" />
      
    <?php if (child_subscribe_safe_text($name_ph) != null): ?>
      <label class="sr-only" for="<?php echo esc_attr($section_id); ?>-name"><?php echo child_subscribe_safe_text($name_ph); ?></label>
      <input id="<?php echo esc_attr($section_id); ?>-name" type="text" name="name" placeholder="<?php echo esc_attr($name_ph); ?>" autocomplete="name" />
    <?php
endif; ?>
    <?php if (!empty($disclaimer)): ?>
        <p class="disclaimer"><?php echo child_subscribe_safe_text($disclaimer); ?></p>
    <?php
endif; ?>
    <?php if (child_subscribe_safe_text($email_ph) != null): ?>
    <?php if (!empty($disclaimer1)): ?><br><?php
  endif; ?>
      <label class="sr-only" for="<?php echo esc_attr($section_id); ?>-email"><?php echo child_subscribe_safe_text($email_ph); ?></label>
      <input id="<?php echo esc_attr($section_id); ?>-email" class="customize-input" type="email" name="email" placeholder="<?php echo esc_attr($email_ph); ?>" autocomplete="email" required />
    <?php
endif; ?>
    <?php if (!empty($disclaimer1)): ?><br><?php
endif; ?>
      <button type="submit" class="btn btn-pill"><?php echo child_subscribe_safe_text($button_text); ?></button>
     
    <?php if (!empty($disclaimer1)): ?>
        <p class="disclaimer"><?php echo child_subscribe_safe_text($disclaimer1); ?></p>
    <?php
endif; ?>
      <p class="signup-box__notice" role="status" aria-live="polite"></p>
    </form>
  </div>
</section>

<script>
(function(){
  const form = document.currentScript?.previousElementSibling?.querySelector?.('form[data-subscribe-form="true"]')
    || document.querySelector('form[data-subscribe-form="true"]');
    if(!form || form.dataset.enhanced) return;
    form.dataset.enhanced = '1';
    const btn    = form.querySelector('button[type="submit"]');
    const notice = form.querySelector('.signup-box__notice');
    const successMsg = form.dataset.success || 'Thank you for subscribing to our News Letter';

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    if(!btn) return;
    const fd = new FormData(form);
    const email = (fd.get('email') || '').toString().trim();
    if(!email){
      if(notice){ notice.textContent = 'Please enter your email.'; }
      return;
    }
    const actionAttr = (form.getAttribute('data-endpoint') || form.getAttribute('action') || '').trim();
    let endpoint = actionAttr && !actionAttr.startsWith('[object') ? actionAttr : (window.ajaxurl || '/wp-admin/admin-ajax.php');
    if(!endpoint){
      if(notice){ notice.textContent = 'Missing endpoint.'; }
      return;
    }

    btn.disabled = true;
    btn.setAttribute('aria-busy','true');
    if(notice){ notice.textContent = ''; }

    try{
      const res = await fetch(endpoint, { method:'POST', body: fd, credentials:'same-origin' });
      let data;
      try { data = await res.json(); }
      catch(parseErr){
        const txt = await res.text();
        if(notice){ notice.textContent = txt || 'Something went wrong. Please try again.'; }
        return;
      }
      if(res.ok && data?.success){
        if(notice){ notice.textContent = successMsg; }
        form.reset();
      } else {
        const msg = data?.data?.message || data?.message || `Error ${res.status || ''}. Please try again.`;
        if(notice){ notice.textContent = msg; }
      }
    } catch(err){
      if(notice){ notice.textContent = 'Something went wrong. Please try again.'; }
    } finally {
      btn.disabled = false;
      btn.removeAttribute('aria-busy');
    }
  });
})();
</script>
