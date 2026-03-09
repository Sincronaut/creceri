<?php

if (!defined('ABSPATH')) {
  exit;
}

/* Lightweight getters */
function ccon_val($a, $k, $d = null)
{
  return (is_array($a) && array_key_exists($k, $a)) ? $a[$k] : $d;
}
function ccon_str($a, $k, $d = '')
{
  $v = ccon_val($a, $k, $d);
  return is_string($v) ? $v : (is_null($v) ? '' : (string)$v);
}
function ccon_arr($a, $k)
{
  $v = ccon_val($a, $k, array());
  return is_array($v) ? $v : array();
}
function ccon_bool($a, $k, $d = false)
{
  return (bool)ccon_val($a, $k, $d);
}

/* Attributes */
$A = is_array($attributes ?? null) ? $attributes : array();
$title = ccon_str($A, 'title', __('Contact Us', 'vite-ttf-child-creceri'));
$lead = ccon_str($A, 'lead', __('Ready to talk? Share a few details and we’ll get back to you shortly', 'vite-ttf-child-creceri'));
$buttonText = ccon_str($A, 'buttonText', __('Submit', 'vite-ttf-child-creceri'));
$showPhone = ccon_bool($A, 'showPhone', true);
$requiredNote = ccon_str($A, 'requiredNote', __('* Required fields', 'vite-ttf-child-creceri'));
$enableParallax = ccon_bool($A, 'enableParallax', true);

$labels = array_merge(array(
  'firstName' => __('First Name', 'vite-ttf-child-creceri'),
  'lastName' => __('Last Name', 'vite-ttf-child-creceri'),
  'email' => __('Email', 'vite-ttf-child-creceri'),
  'phone' => __('Phone', 'vite-ttf-child-creceri'),
  'subject' => __('Subject', 'vite-ttf-child-creceri'),
  'message' => __('Message', 'vite-ttf-child-creceri'),
), ccon_arr($A, 'labels'));

$info = array_merge(array(
  'addressLabel' => __('Address', 'vite-ttf-child-creceri'),
  'address' => '#123 Sample Street, City, Country',
  'phoneLabel' => __('Phone', 'vite-ttf-child-creceri'),
  'phone' => '(+63) 123-456-7989',
  'emailLabel' => __('Email', 'vite-ttf-child-creceri'),
  'email' => 'info@creceri.com',
  'openHoursLabel' => __('Open Hours', 'vite-ttf-child-creceri'),
  'weekdayHours' => __('Monday - Friday : 8 AM to 5 PM', 'vite-ttf-child-creceri'),
  'weekendHours' => __('Saturday - Sunday : CLOSED', 'vite-ttf-child-creceri'),
  'socialLabel' => __('Stay Connected', 'vite-ttf-child-creceri'),
), ccon_arr($A, 'info'));

$social = array_merge(array(
  'facebook' => '',
  'x' => '',
  'instagram' => '',
  'reddit' => '',
), ccon_arr($A, 'social'));

$map = array_merge(array(
  'title' => __('Office Map', 'vite-ttf-child-creceri'),
  'src' => 'https://www.openstreetmap.org/export/embed.html?bbox=-122.4394%2C37.7549%2C-122.3994%2C37.7949&layer=mapnik&marker=37.7749%2C-122.4194',
), ccon_arr($A, 'map'));

/* Instance ids */
$instance_id = 'contact-' . wp_generate_password(6, false, false);
$title_id = $instance_id . '-title';
$note_id = $instance_id . '-note';

/* Helpers */
$tel_clean = preg_replace('/[^+0-9]/', '', ccon_str($info, 'phone', ''));
$email_safe = sanitize_email(ccon_str($info, 'email', ''));

/* Render guard */
if ($title === '' && $lead === '') {
  return;
}
?>
<section id="<?php echo esc_attr($instance_id); ?>" class="contact-wrapper" aria-labelledby="<?php echo esc_attr($title_id); ?>">
  

  <?php if ($enableParallax): ?>
  <div class="contact-parallax" aria-hidden="true">
    <span class="parallax__layer p1" data-speed="0.25"></span>
    <span class="parallax__layer p2" data-speed="0.45"></span>
    <span class="parallax__layer p3" data-speed="0.15"></span>
  </div>
  <?php
endif; ?>

  <div class="contact-grid">
    <!-- Left: Form card -->
    <div class="contact-card">
      <form class="contact-form" action="<?php echo esc_url(home_url('/')); ?>" method="post" novalidate aria-describedby="<?php echo esc_attr($note_id); ?>">
        <?php wp_nonce_field('child_contact_submit', '_cnonce'); ?>
        <input type="hidden" name="action" value="child_contact_submit"/>

        <!-- Honeypot -->
        <div class="hp-field" aria-hidden="true">
          <label for="<?php echo esc_attr($instance_id); ?>-website">Website</label>
          <input id="<?php echo esc_attr($instance_id); ?>-website" name="website" type="text" tabindex="-1" autocomplete="off" />
        </div>

        <div class="form-row">
          <div class="form-field">
            <label for="<?php echo esc_attr($instance_id); ?>-firstName"><?php echo esc_html($labels['firstName']); ?> *</label>
            <input id="<?php echo esc_attr($instance_id); ?>-firstName" name="firstName" type="text" autocomplete="given-name" placeholder="Enter your First name" required />
          </div>
          <div class="form-field">
            <label for="<?php echo esc_attr($instance_id); ?>-lastName"><?php echo esc_html($labels['lastName']); ?> *</label>
            <input id="<?php echo esc_attr($instance_id); ?>-lastName" name="lastName" type="text" autocomplete="family-name" placeholder="Enter your Last name" required />
          </div>
        </div>

        <div class="form-row">
          <div class="form-field">
            <label for="<?php echo esc_attr($instance_id); ?>-email"><?php echo esc_html($labels['email']); ?> *</label>
            <input id="<?php echo esc_attr($instance_id); ?>-email" name="email" type="email" inputmode="email" autocomplete="email" placeholder="Enter your Email address" required />
          </div>

          <?php if ($showPhone): ?>
          <div class="form-field">
            <label for="<?php echo esc_attr($instance_id); ?>-phone"><?php echo esc_html($labels['phone']); ?> *</label>
            <input id="<?php echo esc_attr($instance_id); ?>-phone" name="phone" type="tel" inputmode="tel" autocomplete="tel" placeholder="(+63) 123-456-7989" />
          </div>
          <?php
endif; ?>
        </div>

        <div class="form-field">
          <label for="<?php echo esc_attr($instance_id); ?>-subject"><?php echo esc_html($labels['subject']); ?> *</label>
          <input id="<?php echo esc_attr($instance_id); ?>-subject" name="subject" type="text" placeholder="How can we help?" required />
        </div>

        <div class="form-field">
          <label for="<?php echo esc_attr($instance_id); ?>-message"><?php echo esc_html($labels['message']); ?> *</label>
          <textarea id="<?php echo esc_attr($instance_id); ?>-message" name="message" placeholder="Share a few details so we can route your message to the right person." required></textarea>
        </div>

        <div class="form-foot">
          <div class="checkbox-wrapper">
            <input type="checkbox" id="<?php echo esc_attr($instance_id); ?>-privacy" name="privacy" required>
            <label for="<?php echo esc_attr($instance_id); ?>-privacy">
              I agree to be contacted about my inquiry and accept the Privacy Policy.
            </label>
          </div>

          <button class="btn btn-pill" type="submit">
            <?php echo esc_html($buttonText); ?>
          </button>
        </div>

        <p id="<?php echo esc_attr($note_id); ?>" class="screen-reader-text">
          <?php echo esc_html($requiredNote); ?>
        </p>
      </form>
    </div>

    <!-- Right: Info card -->
    <aside class="contact-card contact-info" aria-label="<?php esc_attr_e('Contact details', 'vite-ttf-child-creceri'); ?>">
      <div class="info-section">
        <span class="info-label"><?php echo esc_html(ccon_str($info, 'addressLabel', '')); ?></span>
        <div class="info-text">
          <p><?php echo esc_html(ccon_str($info, 'address', '')); ?></p>
        </div>
      </div>

      <div class="info-section">
        <span class="info-label"><?php echo esc_html(ccon_str($info, 'phoneLabel', '')); ?></span>
        <div class="info-text">
          <?php if ($tel_clean): ?>
            <a class="info-value" href="tel:<?php echo esc_attr($tel_clean); ?>">
              <?php echo esc_html(ccon_str($info, 'phone', '')); ?>
            </a>
          <?php
else: ?>
            <p class="info-text"><?php echo esc_html(ccon_str($info, 'phone', '')); ?></p>
          <?php
endif; ?>
        </div>
      </div>

      <div class="info-section">
        <span class="info-label"><?php echo esc_html(ccon_str($info, 'emailLabel', '')); ?></span>
        <div class="info-text">
          <?php if ($email_safe): ?>
            <a class="info-value" href="mailto:<?php echo esc_attr($email_safe); ?>">
              <?php echo esc_html($email_safe); ?>
            </a>
          <?php
endif; ?>
        </div>
      </div>

      <div class="info-section">
        <span class="info-label"><?php echo esc_html(ccon_str($info, 'openHoursLabel', '')); ?></span>
        <div class="info-text">
          <p><?php echo esc_html(ccon_str($info, 'weekdayHours', '')); ?></p>
          <p><?php echo esc_html(ccon_str($info, 'weekendHours', '')); ?></p>
        </div>
      </div>

      <div class="info-section">
        <span class="info-label"><?php echo esc_html(ccon_str($info, 'socialLabel', '')); ?></span>
        <div class="social-icons">
            <a href="#" aria-label="Facebook"><span aria-hidden="true">F</span></a>
          
          
            <a href="#" aria-label="X (Twitter)"><span aria-hidden="true">X</span></a>
          
            <a href="#" aria-label="Instagram"><span aria-hidden="true">IG</span></a>
          
            <a href="#" aria-label="Reddit"><span aria-hidden="true">R</span></a>
          
        </div>
      </div>
    </aside>
  </div>

  <!-- Map full width below cards -->
  <div class="contact-card contact-map" role="region" aria-label="<?php echo esc_attr(ccon_str($map, 'title', '')); ?>">
    <iframe
      title="<?php echo esc_attr(ccon_str($map, 'title', '')); ?>"
      src="<?php echo esc_url(ccon_str($map, 'src', '')); ?>"
      loading="lazy"
      referrerpolicy="no-referrer-when-downgrade"
    ></iframe>
  </div>
</section>

<?php if ($enableParallax): ?>
<script>
(function(){
  const root = document.getElementById('<?php echo esc_js($instance_id); ?>');
  if(!root) return;
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  const layers = root.querySelectorAll('.parallax__layer');
  if(!layers.length) return;

  let active = false, lastY = window.scrollY;

  const onScroll = () => {
    lastY = window.scrollY;
    if (!active) {
      active = true;
      requestAnimationFrame(tick);
    }
  };

  const tick = () => {
    layers.forEach(el => {
      const speed = parseFloat(el.getAttribute('data-speed') || '0.2');
      el.style.transform = 'translate3d(0,' + Math.round(lastY * speed) + 'px,0)';
    });
    active = false;
  };

  const observer = new IntersectionObserver((entries)=>{
    entries.forEach(entry=>{
      if(entry.isIntersecting){
        window.addEventListener('scroll', onScroll, { passive: true });
      } else {
        window.removeEventListener('scroll', onScroll, { passive: true });
      }
    });
  }, { rootMargin: '100px' });

  observer.observe(root);
})();
</script>
<?php
endif; ?>
