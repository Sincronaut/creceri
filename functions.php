<?php
/**
 * All Inclusive — Twenty Twenty-Five Child
 */

/* -----------------------  Assets  ----------------------- */

add_action('wp_enqueue_scripts', function () {
  // OPTIONAL: If you want Bootstrap to dominate and reduce block theme globals,
  // uncomment the next line. (It may affect core block styling.)
  // remove_action('wp_enqueue_scripts', 'wp_enqueue_global_styles');

  $dir = get_stylesheet_directory();
  $uri = get_stylesheet_directory_uri();

  // Small helpers
  $ver = function ($rel) use ($dir) {
      $f = $dir . $rel;
      return file_exists($f) ? filemtime($f) : null;
    }
      ;
    $add_style = function ($handle, $rel, $deps = []) use ($uri, $ver) {
      $file_uri = $uri . $rel;
      $v = $ver($rel);
      if ($v !== null) {
        wp_enqueue_style($handle, $file_uri, $deps, $v);
      }
    }
      ;
    $add_script = function ($handle, $rel, $deps = [], $in_footer = true) use ($uri, $ver) {
      $file_uri = $uri . $rel;
      $v = $ver($rel);
      if ($v !== null) {
        wp_enqueue_script($handle, $file_uri, $deps, $v, $in_footer);
      }
    }
      ;

    /* ---- CSS: vendor first, then your layers ---- */
    // Bootstrap
    wp_enqueue_style(
      'bootstrap',
      'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
    [],
      '5.3.3'
    );

    // Parent & child styles (keep light; your real CSS lives in /assets/css/*)
    // Parent first (optional but safe), then child (style.css with theme header / tiny globals)
    $parent_style_path = get_template_directory() . '/style.css';
    $parent_style_version = file_exists($parent_style_path)
      ? filemtime($parent_style_path)
      : wp_get_theme(get_template())->get('Version');

    wp_enqueue_style(
      'parent-style',
      get_template_directory_uri() . '/style.css',
    ['bootstrap'],
      $parent_style_version
    );

    // Version the child style header file so the CDN/browser picks up updates after deploys.
    $add_style('child-style', '/style.css', ['bootstrap', 'parent-style']);

    // Global layout CSS (site-wide)
    $add_style('ai-header', '/assets/css/layout/header.css', ['child-style']);
    $add_style('ai-footer', '/assets/css/layout/footer.css', ['ai-header']);

    // // Page-specific CSS
    // if ( is_front_page() ) {
    //   $add_style( 'ai-homepage', '/assets/css/pages/homepage.css', [ 'ai-footer' ] );
    // }
    // if ( is_page( 'about' ) ) {
    //   $add_style( 'ai-about', '/assets/css/pages/about.css', [ 'ai-footer' ] );
    // }
    // if ( is_page( 'blogs' ) || is_home() ) {
    //   $add_style( 'ai-blogs', '/assets/css/pages/blogs.css', [ 'ai-footer' ] );
    // }
  
    /* ---- JS: vendor then your script(s) ---- */
    wp_enqueue_script(
      'bootstrap',
      'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
    [],
      '5.3.3',
      true
    );

    // Your main JS (menus, mobile submenu, hero bg carousel init, etc.)
    $add_script('ai-main', '/assets/js/main.js', ['bootstrap'], true);
  });

function enqueue_fa_icons()
{
  wp_enqueue_style(
    'font-awesome',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'
  );
}
add_action('wp_enqueue_scripts', 'enqueue_fa_icons');

/* -------------------  Disable editor on 'home' (optional)  ------------------- */
// function ai_disable_editor_on_home($can_edit, $post) {
//   if (is_admin() && $post && $post->post_name === 'home') return false;
//   return $can_edit;
// }
// add_filter('use_block_editor_for_post', 'ai_disable_editor_for_post', 10, 2);

// Disable WP's automatic site icon tags if set (avoid duplicates)
// add_action('init', function () {
//   remove_action('wp_head', 'wp_site_icon', 99); // avoid WP's own tags
// });

// add_action('wp_head', function () {
//   $base = get_stylesheet_directory_uri() . '/assets/favicons';
//   echo "\n<!-- Favicons (child theme) -->\n";
//   echo '<link rel="icon" type="image/png" sizes="32x32" href="' . esc_url("$base/32px-32px-fav-icon.png?v=4") . '">' . "\n";
//   echo '<link rel="icon" type="image/png" sizes="16x16" href="' . esc_url("$base/16px-16px-fav-icon.png?v=4") . '">' . "\n";
//   echo '<link rel="apple-touch-icon" sizes="180x180" href="' . esc_url("$base/96px-96px-fav-icon.png?v=4") . '">' . "\n";
//   echo '<meta name="theme-color" content="#ffffff">' . "\n";
// }, 5 );

/* -----------------------  Custom blocks  ----------------------- */
// 1) Helpers available to all blocks
require_once get_stylesheet_directory() . '/inc/region-data.php';
// Image ALT helpers (ensure <img> has customizable alt via template/pages)
require_once get_stylesheet_directory() . '/inc/image-alt.php';

/* ---------- Fallback subscribe handler (if MU plugin missing) ---------- */
if (!function_exists('child_subscribe_handle_ajax')) {
  add_action('init', function () {
    if (!post_type_exists('creceri_subscriber')) {
      register_post_type('creceri_subscriber', array(
        'labels' => array(
          'name' => 'Subscribers',
          'singular_name' => 'Subscriber',
          'menu_name' => 'Subscribers',
        ),
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_admin_bar' => false,
        'show_in_nav_menus' => false,
        'exclude_from_search' => true,
        'publicly_queryable' => false,
        'has_archive' => false,
        'supports' => array('title'),
        'menu_position' => 25,
        'menu_icon' => 'dashicons-email-alt2',
        'capability_type' => 'post',
      ));
    }
  });

  add_action('wp_ajax_child_subscribe_submit', 'child_subscribe_handle_ajax');
  add_action('wp_ajax_nopriv_child_subscribe_submit', 'child_subscribe_handle_ajax');

  function child_subscribe_handle_ajax()
  {
    $nonce = isset($_POST['child_subscribe_nonce']) ? sanitize_text_field(wp_unslash($_POST['child_subscribe_nonce'])) : '';
    // Soft-fail nonce to avoid user-facing errors if cache served an old nonce
    if ($nonce && !wp_verify_nonce($nonce, 'child_subscribe')) {
      wp_send_json_error(array('message' => 'Security check failed.'), 400);
    }

    $name = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
    $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';

    if (empty($email) || !is_email($email)) {
      wp_send_json_error(array('message' => 'Please enter a valid email.'), 400);
    }

    $post_id = wp_insert_post(array(
      'post_type' => 'creceri_subscriber',
      'post_status' => 'publish',
      'post_title' => $email,
    ));

    if (!is_wp_error($post_id) && $post_id) {
      update_post_meta($post_id, '_subscriber_name', $name);
      update_post_meta($post_id, '_subscriber_email', $email);
    }

    wp_send_json_success(array(
      'message' => 'Thank you for subscribing to our News Letter',
      'email' => $email,
      'name' => $name,
    ));
  }
}

// 2) Register every block that has a block.json inside /blocks/*/
//    Guard against duplicate registration and invalid names (no namespace).
add_action('init', function () {
  $base = get_stylesheet_directory() . '/blocks';
  $registry = WP_Block_Type_Registry::get_instance();

  foreach (glob($base . '/*/block.json') as $json) {
    $data = json_decode(file_get_contents($json), true);
    if (!is_array($data) || empty($data['name'])) {
      continue;
    }

    $name = $data['name'];

    // Skip invalid names with no namespace to avoid "must contain a namespace prefix" notice
    if (strpos($name, '/') === false) {
      // Optional: surface a debug hint for admins.
      if (is_admin() && current_user_can('manage_options')) {
        error_log("Child theme blocks: skipped invalid block name '{$name}' in {$json}");
      }
      continue;
    }

    if (!$registry->is_registered($name)) {
      register_block_type(dirname($json));
    }
  }
});

add_action('wp_head', function () {
  if (!current_user_can('manage_options')) {
    return;
  }
  if (!function_exists('child_resolve_region_country')) {
    return;
  }
  [$region, $country] = child_resolve_region_country();
  echo "\n<!-- only-allowed debug: region={$region} country={$country} -->\n";
});

add_action('wp_head', function () {
  if (!current_user_can('manage_options')) {
    return;
  }
  [$region, $country] = child_resolve_region_country();
  $file = child_get_data_file();
  echo "\n<!-- region={$region} country={$country} file={$file} -->\n";
});

/* -------- Redirect unknown front-end pages to Home and show a toast -------- */
// add_action('template_redirect', function () {
//   if (is_admin() || wp_doing_ajax()) return;
//
//   $rest_prefix = function_exists('rest_get_url_prefix') ? rest_get_url_prefix() : 'wp-json';
//   $uri = $_SERVER['REQUEST_URI'] ?? '';
//
//   // Skip REST requests
//   if (
//     (defined('REST_REQUEST') && constant('REST_REQUEST')) ||
//     strncmp($uri, '/' . $rest_prefix . '/', strlen('/' . $rest_prefix . '/')) === 0 ||
//     (isset($_GET['rest_route']) && $_GET['rest_route'] !== '')
//   ) return;
//
//   $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
//   $wants_html = (stripos($accept, 'text/html') !== false);
//
//   if ($wants_html && is_404() && !is_front_page()) {
//     $target = add_query_arg('notice', 'missing', home_url('/'));
//     wp_safe_redirect($target, 302);
//     exit;
//   }
// });

add_action('wp_footer', function () {
  if (!isset($_GET['notice']) || $_GET['notice'] !== 'missing') {
    return;
  }?>
  <div class="toast-container position-fixed top-0 end-0 p-4" style="z-index:2000">
    <div id="missingToast" class="toast text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="toast-body">
        <p class="text-white fs-6 fw-semibold mb-0">
          The page you're looking for doesn't exist or has moved. You were redirected to Home.
        </p>
      </div>
    </div>
  </div>
  <script>
    (function () {
      var el = document.getElementById('missingToast');
      if (el && window.bootstrap && bootstrap.Toast) {
        new bootstrap.Toast(el, { delay: 5000 }).show();
      } else {
        el && (el.style.display = 'block');
        setTimeout(function(){ el && (el.style.display='none'); }, 3000);
      }
      if (history.replaceState) {
        var url = new URL(window.location.href);
        url.searchParams.delete('notice');
        history.replaceState({}, '', url.pathname + (url.search ? '?' + url.search : '') + url.hash);
      }
    })();
  </script>
<?php
});

// Render Gutenberg blocks inside excerpts (so SSR blocks appear).
add_filter('the_excerpt', 'do_blocks', 9);

/**
 * [travel_cat_label] – prints one mapped category label based on slug.
 * Usage: [travel_cat_label] or [travel_cat_label link="0"]
 */
// add_shortcode('travel_cat_label', function ($atts = []) {
//   if (!is_singular()) return '';
//
//   $atts = shortcode_atts([
//     'link' => '1', // "1" to link to category archive, "0" for plain text
//   ], $atts, 'travel_cat_label');
//
//   // Map slugs -> the labels you want to show
//   $map = [
//     'travel-guide'        => 'Travel Guide',
//     'travel-insights'     => 'Travel Insights',
//     'travel-stories'      => 'Travel Stories',
//     'travel-experiences'  => 'Travel Experiences',
//   ];
//
//   $terms = get_the_terms(get_the_ID(), 'category');
//   if (empty($terms) || is_wp_error($terms)) return '';
//
//   // Prefer the first mapped slug if multiple categories
//   $chosen = null;
//   foreach ($terms as $t) {
//     if (isset($map[$t->slug])) { $chosen = $t; break; }
//   }
//   if (!$chosen) $chosen = $terms[0];
//
//   $label = $map[$chosen->slug] ?? $chosen->name;
//
//   if ($atts['link'] !== '0') {
//     // Always point to /blogs/ (instead of get_term_link)
//     $url = home_url('/blogs/');
//     return '<a class="meta-cat" href="' . esc_url($url) . '">' . esc_html($label) . '</a>';
//   }
//   return esc_html($label);
// });

/* -----------------------  Programmatic TOC (no editor block)  ----------------------- */
/**
 * Render the Table of Contents anywhere without inserting a block in the editor.
 * Place this in your template where the TOC should appear:
 *   <?php if ( function_exists('child_render_toc') ) child_render_toc(); ?>
 * Optional args: ['title'=>'Table of Contents','levels'=>['h2','h3'],'intro'=>true]
 */
if (!function_exists('child_render_toc')) {
  function child_render_toc($args = array())
  {
    $attrs = wp_parse_args(
      $args,
      array(
      'title' => 'Table of Contents',
      'levels' => array('h2', 'h3'),
      'intro' => true,
    )
    );

    // Build a block comment and let WordPress render the dynamic block server-side.
    $comment = sprintf(
      '<!-- wp:child/table-of-content %s /-->',
      wp_json_encode($attrs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
    );

    echo do_blocks($comment); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
  }
}

/**
 * Optional shortcode for legacy templates/widgets:
 *   [child_toc title="Contents" levels="h2,h3" intro="1"]
 */
add_shortcode(
  'child_toc',
  function ($atts = array()) {
    $atts = shortcode_atts(
      array(
      'title' => 'Table of Contents',
      'levels' => 'h2,h3',
      'intro' => '1',
    ),
      $atts,
      'child_toc'
    );
    $args = array(
      'title' => (string)$atts['title'],
      'levels' => array_map('trim', explode(',', (string)$atts['levels'])),
      'intro' => $atts['intro'] !== '0',
    );
    ob_start();
    child_render_toc($args);
    return ob_get_clean();
  }
);

/* -----------------------  Block assets for specific blocks  ----------------------- */

/* Card Block editor assets (/blocks/card-block) */
add_action(
  'init',
    function () {
    $dir_path = get_stylesheet_directory() . '/blocks/card-block';
    $dir_uri = get_stylesheet_directory_uri() . '/blocks/card-block';

    $style_file = $dir_path . '/editor.css';
    $script_file = $dir_path . '/index.js';
    $style_version = file_exists($style_file) ? filemtime($style_file) : null;
    $script_version = file_exists($script_file) ? filemtime($script_file) : null;

    if ($style_version) {
      wp_register_style(
        'card-block-editor-style',
        $dir_uri . '/editor.css',
        array('wp-edit-blocks'),
        $style_version
      );
    }

    if ($script_version) {
      wp_register_script(
        'card-block-editor',
        $dir_uri . '/index.js',
        array('wp-blocks', 'wp-element', 'wp-i18n', 'wp-components', 'wp-block-editor'),
        $script_version,
        true
      );
    }

  // Block type itself is registered by the generic /blocks/*/block.json loader above.
  }
);

/* Card Update Data (/blocks/card) */
add_action(
  'init',
    function () {
    $dir_path = get_stylesheet_directory() . '/blocks/card';
    $dir_uri = get_stylesheet_directory_uri() . '/blocks/card';

    // Register styles and script handles referenced by block.json.
    wp_register_style(
      'card-style',
      $dir_uri . '/style.css',
      array(),
      '1.0'
    );

    wp_register_style(
      'card-editor-style',
      $dir_uri . '/editor.css',
      array('wp-edit-blocks'),
      '1.0'
    );

    wp_register_script(
      'card-editor',
      $dir_uri . '/index.js',
      array('wp-blocks', 'wp-element', 'wp-i18n', 'wp-components', 'wp-block-editor'),
      '1.0',
      true
    );

    // Hide admin bar on front-end.
    add_filter('show_admin_bar', '__return_false');

    wp_register_script(
      'card-view',
      $dir_uri . '/index.js',
      array(),
      '1.0',
      true
    );

  // Block type itself is registered by the generic /blocks/*/block.json loader above.
  }
);

add_action(
  'init',
    function () {
    $dir_path = get_stylesheet_directory() . '/blocks/banner';
    $dir_uri = get_stylesheet_directory_uri() . '/blocks/banner';

    $style_file = $dir_path . '/style.css';
    $editor_file = $dir_path . '/editor.css';
    $script_file = $dir_path . '/index.js';

    wp_register_style(
      'banner-style',
      $dir_uri . '/style.css',
      array(),
      file_exists($style_file) ? filemtime($style_file) : '1.0'
    );

    wp_register_style(
      'banner-editor-style',
      $dir_uri . '/editor.css',
      array('wp-edit-blocks'),
      file_exists($editor_file) ? filemtime($editor_file) : '1.0'
    );

    wp_register_script(
      'banner-editor',
      $dir_uri . '/index.js',
      array('wp-blocks', 'wp-element', 'wp-i18n', 'wp-components', 'wp-block-editor'),
      file_exists($script_file) ? filemtime($script_file) : '1.0',
      true
    );

    wp_register_script(
      'banner-view',
      $dir_uri . '/index.js',
      array(),
      file_exists($script_file) ? filemtime($script_file) : '1.0',
      true
    );

  // Block type itself is registered by the generic /blocks/*/block.json loader above.
  }
);

add_action(
  'init',
    function () {
    $dir_path = get_stylesheet_directory() . '/blocks/banner-2';
    $dir_uri = get_stylesheet_directory_uri() . '/blocks/banner-2';

    $style_file = $dir_path . '/style.css';
    $editor_file = $dir_path . '/editor.css';
    $script_file = $dir_path . '/index.js';

    wp_register_style(
      'banner2-style',
      $dir_uri . '/style.css',
      array(),
      file_exists($style_file) ? filemtime($style_file) : '1.0'
    );

    wp_register_style(
      'banner2-editor-style',
      $dir_uri . '/editor.css',
      array('wp-edit-blocks'),
      file_exists($editor_file) ? filemtime($editor_file) : '1.0'
    );

    wp_register_script(
      'banner2-editor',
      $dir_uri . '/index.js',
      array('wp-blocks', 'wp-element', 'wp-i18n', 'wp-components', 'wp-block-editor'),
      file_exists($script_file) ? filemtime($script_file) : '1.0',
      true
    );

    wp_register_script(
      'banner2-view',
      $dir_uri . '/index.js',
      array(),
      file_exists($script_file) ? filemtime($script_file) : '1.0',
      true
    );

  // Block type itself is registered by the generic /blocks/*/block.json loader above.
  }
);

add_action(
  'init',
    function () {
    $dir_path = get_stylesheet_directory() . '/blocks/banner-3';
    $dir_uri = get_stylesheet_directory_uri() . '/blocks/banner-3';

    $style_file = $dir_path . '/style.css';
    $editor_file = $dir_path . '/editor.css';
    $script_file = $dir_path . '/index.js';

    $style_version = file_exists($style_file) ? filemtime($style_file) : '1.0';
    $editor_version = file_exists($editor_file) ? filemtime($editor_file) : '1.0';
    $script_version = file_exists($script_file) ? filemtime($script_file) : '1.0';

    if ($style_version) {
      wp_register_style(
        'banner3-style',
        $dir_uri . '/style.css',
        array(),
        $style_version
      );
    }

    if ($editor_version) {
      wp_register_style(
        'banner3-editor-style',
        $dir_uri . '/editor.css',
        array('wp-edit-blocks'),
        $editor_version
      );
    }

    if ($script_version) {
      wp_register_script(
        'banner3-editor',
        $dir_uri . '/index.js',
        array('wp-blocks', 'wp-element', 'wp-i18n', 'wp-components', 'wp-block-editor'),
        $script_version,
        true
      );
    }

  // Block type itself is registered by the generic /blocks/*/block.json loader above.
  }
);

add_action(
  'init',
    function () {
    $dir_path = get_stylesheet_directory() . '/blocks/blog-content';
    $dir_uri = get_stylesheet_directory_uri() . '/blocks/blog-content';
    $asset = $dir_path . '/index.js';
    $version = file_exists($asset) ? filemtime($asset) : null;

    if ($version) {
      wp_register_script(
        'blog-content-editor',
        $dir_uri . '/index.js',
        array('wp-blocks', 'wp-element', 'wp-i18n', 'wp-components', 'wp-block-editor'),
        $version,
        true
      );
    }

  // Block type itself is registered by the generic /blocks/*/block.json loader above.
  }
);

add_action(
  'init',
    function () {
    $dir_path = get_stylesheet_directory() . '/blocks/faq';
    $dir_uri = get_stylesheet_directory_uri() . '/blocks/faq';
    $asset = $dir_path . '/index.js';
    $version = file_exists($asset) ? filemtime($asset) : null;

    if ($version) {
      wp_register_script(
        'faq-editor',
        $dir_uri . '/index.js',
        array('wp-blocks', 'wp-element', 'wp-i18n', 'wp-components', 'wp-block-editor'),
        $version,
        true
      );
    }

  // Block type itself is registered by the generic /blocks/*/block.json loader above.
  }
);

/* -----------------------  Admin-bar Clear Cache button  ----------------------- */

// Add a Clear Cache button in the admin bar
function add_clear_cache_button($wp_admin_bar)
{
  if (!current_user_can('manage_options')) {
    return;
  }

  $args = array(
    'id' => 'clear_cache_button',
    'title' => '🧹 Clear Cache',
    'href' => wp_nonce_url(admin_url('?clear-cache=true'), 'clear-cache'),
    'meta' => array('class' => 'clear-cache-button'),
  );
  $wp_admin_bar->add_node($args);
}
add_action('admin_bar_menu', 'add_clear_cache_button', 100);

// Handle the cache clearing when button is clicked
function handle_clear_cache_request()
{
  if (!isset($_GET['clear-cache']) || !wp_verify_nonce($_GET['_wpnonce'] ?? '', 'clear-cache')) {
    return;
  }

  // Clear WordPress object cache
  if (function_exists('wp_cache_flush')) {
    wp_cache_flush();
  }

  // Optional: clear plugin or transients cache
  global $wpdb;
  $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_%'");

  // Redirect back with success message
  wp_safe_redirect(remove_query_arg(array('clear-cache', '_wpnonce')));
  exit;
}
add_action('admin_init', 'handle_clear_cache_request');

/* -----------------------  Editor crash guards (polyfills)  ----------------------- */

add_action(
  'enqueue_block_editor_assets',
    function () {
    $inline = <<<'JS'
(function (w) {
  var wp = w.wp || {};
  if (!wp.components) {
    wp.components = {};
  }
  var components = wp.components;
  var motion = components.__unstableMotion || {};
  if (!components.__unstableMotion) {
    components.__unstableMotion = motion;
  }

  // Gutenberg 16+ expects __unstableMotion.create; older framer-motion builds don't ship it.
  if (motion && typeof motion.create !== 'function') {
    try {
      motion.create = function (Component) {
        // If motion is callable, prefer that as the wrapper.
        if (typeof motion === 'function') {
          try { return motion(Component); } catch (e) {}
        }
        // Fallback to a tag-specific motion element if present.
        var tag = '';
        if (Component) {
          tag = (Component.displayName || Component.name || '').toLowerCase();
        }
        if (tag && typeof motion === 'object' && motion[tag]) {
          return motion[tag];
        }
        return Component;
      };
    } catch (e) {
      // Swallow — this is a best-effort compatibility shim.
    }
  }

  // Soften privateApis.unlock calls that receive undefined in older bundles.
  if (wp.privateApis && typeof wp.privateApis.unlock === 'function') {
    var originalUnlock = wp.privateApis.unlock;
    wp.privateApis.unlock = function (target) {
      if (target == null) return target;
      try { return originalUnlock(target); } catch (e) { return target; }
    };
  }

  // Guard against inline scripts assuming wp.editPost.initializeEditor exists.
  var editPost = wp.editPost = wp.editPost || {};
  if (typeof editPost.initializeEditor !== 'function') {
    editPost.initializeEditor = function () {
      return {
        setAvailableMetaBoxesPerLocation: function () {},
        setAvailableTemplates: function () {},
        setTemplate: function () {},
        onChange: function () {},
        save: function () {}
      };
    };
  }
})(window);
JS;

    // Attach before multiple core handles to guarantee it runs ahead of the bundles that throw.
    foreach (array('wp-components', 'wp-editor', 'wp-edit-post') as $handle) {
      wp_add_inline_script($handle, $inline, 'before');
    }
  }
);

/* -----------------------  Custom Search SEO  ----------------------- */
// 1. Standard WP Title
add_filter('document_title_parts', function ($title) {
  if (is_search()) {
    // Check if URL has /ko/
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    if (strpos($uri, '/ko/') !== false) {
      $title['title'] = '검색 결과';
    }
    else {
      $title['title'] = 'Search Results';
    }
  }
  return $title;
});

// 2. Rank Math Title Override
add_filter('rank_math/frontend/title', function ($title) {
  if (is_search()) {
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    if (strpos($uri, '/ko/') !== false) {
      return '검색 결과 | Creceri';
    }
    return 'Search Results | Creceri';
  }
  return $title;
});

// 3. Meta Description (Standard + Rank Math)
add_action('wp_head', function () {
  if (is_search()) {
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $is_ko = (strpos($uri, '/ko/') !== false);
    $content = $is_ko
      ? "Creceri의 디지털 지식 허브에서 기사, 리소스, 인사이트를 찾아보세요. 비즈니스에 중요한 주제를 검색해 보세요."
      : "Find articles, resources, and insights across Creceri’s digital knowledge hub. Search for topics that matter to your business. ";

    // Note: Rank Math might output its own, so we filter that too below.
    // This direct echo is a fallback if RM is off.
    if (!class_exists('RankMath')) {
      echo '<meta name="description" content="' . esc_attr($content) . '">' . "\n";
    }
  }
}, 1);

add_filter('rank_math/frontend/description', function ($desc) {
  if (is_search()) {
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    if (strpos($uri, '/ko/') !== false) {
      return 'Creceri의 디지털 지식 허브에서 기사, 리소스, 인사이트를 찾아보세요. 비즈니스에 중요한 주제를 검색해 보세요.';
    }
    return 'Find articles, resources, and insights across Creceri’s digital knowledge hub. Search for topics that matter to your business.';
  }
  return $desc;
});

/* -----------------------  Korean Search Template Routing  ----------------------- */
add_filter('template_include', function ($template) {
  if (is_search()) {
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    if (strpos($uri, '/ko/') !== false) {
      $ko_template = locate_template('templates/ko-search-loader.php');
      if ($ko_template) {
        return $ko_template;
      }
    }
  }

  /* -----------------------  Korean 404 Template Routing  ----------------------- */
  if (is_404()) {
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    if (strpos($uri, '/ko/') !== false) {
      $ko_404_template = locate_template('templates/ko-404-loader.php');
      if ($ko_404_template) {
        return $ko_404_template;
      }
    }
  }

  return $template;
});

/* -----------------------  Robots.txt Fix  ----------------------- */
add_filter('robots_txt', function ($output, $public) {
  // Remove invalid "Content-signal: search=yes,ai-train=no" from RankMath
  $output = str_replace("Content-signal: search=yes,ai-train=no\n", "", $output);
  $output = str_replace("Content-signal: search=yes,ai-train=no", "", $output);
  return $output;
}, 999, 2);

/* ==========================================================
 * SCHEMA: DYNAMIC PAGE SCHEMAS (JSON-LD @graph)
 * ========================================================== */
add_action('wp_head', function () {
  // Dynamic base variables (reused for all pages)
  $site = trailingslashit(home_url('/'));
  $org_name = 'Creceri';
  $logo_url = $site . 'logo.png';

  $schema = []; // Initialize empty schema array

  // ---------------------------------------------------
  // 1. HOMEPAGE SCHEMA
  // ---------------------------------------------------
  if (is_front_page()) {
    $schema = [
      '@context' => 'https://schema.org',
      '@graph' => [
        [
          '@type' => 'Organization',
          '@id' => $site . '#organization',
          'name' => $org_name,
          'url' => $site,
          'logo' => $logo_url,
          'description' => 'A digital knowledge hub dedicated to researching and publishing trusted insights across the tech and digital ecosystem.',
          'knowsAbout' => [
            'E-commerce Frameworks',
            'CMS Logic',
            'UI/UX Principles',
            'Search Engine Optimization',
            'Digital Innovation'
          ]
        ],
        [
          '@type' => 'WebSite',
          '@id' => $site . '#website',
          'url' => $site,
          'name' => 'Creceri Knowledge Hub',
          'publisher' => ['@id' => $site . '#organization'],
          'description' => 'Explorations in digital innovation, platform reviews, and technical breakdowns.'
        ],
        [
          '@type' => 'WebPage',
          '@id' => $site . '#webpage',
          'url' => $site,
          'name' => 'Creceri | Digital Knowledge Hub & Tech Insights',
          'isPartOf' => ['@id' => $site . '#website'],
          'about' => ['@id' => $site . '#organization'],
          'mainEntity' => [
            '@type' => 'CreativeWork',
            'name' => 'Creceri Digital Research Library'
          ],
          'hasPart' => [
            [
              '@type' => 'WebPage',
              '@id' => $site . 'about/',
              'name' => 'About Creceri',
              'description' => 'Mission as a digital knowledge hub.'
            ],
            [
              '@type' => 'WebPage',
              '@id' => $site . 'ecommerce-development/',
              'name' => 'E-commerce Insights',
              'description' => 'Magento features and backend logic research.'
            ],
            [
              '@type' => 'WebPage',
              '@id' => $site . 'website-cms-development/',
              'name' => 'CMS Knowledge',
              'description' => 'Analysis of WordPress and web frameworks.'
            ],
            [
              '@type' => 'WebPage',
              '@id' => $site . 'ui-ux-design/',
              'name' => 'UX Design Principles',
              'description' => 'Research on user-centered principles.'
            ],
            [
              '@type' => 'WebPage',
              '@id' => $site . 'digital-marketing/',
              'name' => 'SEO & Marketing Logic',
              'description' => 'Foundations of search optimization and intent.'
            ],
            [
              '@type' => 'WebPage',
              '@id' => $site . 'team-extension/',
              'name' => 'Industry Roles',
              'description' => 'Studies on digital team models.'
            ]
          ],
          'significantLink' => [
            $site . 'stories/',
            $site . 'whats-new/'
          ]
        ]
      ]
    ];
  }

  // ---------------------------------------------------
  // 2. ABOUT US PAGE SCHEMA
  // ---------------------------------------------------
  elseif (is_page('about')) {
    $schema = [
      '@context' => 'https://schema.org',
      '@graph' => [
        [
          '@type' => 'AboutPage',
          '@id' => $site . 'about/#webpage',
          'url' => $site . 'about/',
          'name' => 'About Creceri | Learn Who We Are & What We Share',
          'description' => 'Explore Creceri’s mission as a digital knowledge hub. Learn how we research and publish trusted information across the web and tech ecosystem.',
          'mainEntity' => ['@id' => $site . '#organization'],
          'isPartOf' => ['@id' => $site . '#website'],
          'breadcrumb' => ['@id' => $site . 'about/#breadcrumb']
        ],
        [
          '@type' => 'Organization',
          '@id' => $site . '#organization',
          'name' => $org_name,
          'url' => $site,
          'description' => 'A digital knowledge hub dedicated to technical research and tech ecosystem insights.',
          'knowsAbout' => [
            'E-commerce Development',
            'CMS Logic',
            'UI/UX Design Principles',
            'Digital Marketing',
            'Staffing Models'
          ]
        ],
        [
          '@type' => 'BreadcrumbList',
          '@id' => $site . 'about/#breadcrumb',
          'itemListElement' => [
            [
              '@type' => 'ListItem',
              'position' => 1,
              'name' => 'Home',
              'item' => $site
            ],
            [
              '@type' => 'ListItem',
              'position' => 2,
              'name' => 'About'
            ]
          ]
        ]
      ]
    ];
  }

  // ---------------------------------------------------
  // 3. DIGITAL MARKETING PAGE SCHEMA
  // ---------------------------------------------------
  elseif (is_page('digital-marketing')) {
    $schema = [
      '@context' => 'https://schema.org',
      '@graph' => [
        [
          '@type' => 'CollectionPage',
          '@id' => $site . 'digital-marketing/#webpage',
          'url' => $site . 'digital-marketing/',
          'name' => 'Digital Marketing & SEO Research Hub',
          'description' => 'Comprehensive knowledge base for Semantic SEO, search intent, and technical optimization.',
          'publisher' => ['@id' => $site . '#organization'],
          'isPartOf' => ['@id' => $site . '#website'],
          'mainEntity' => [
            '@type' => 'ItemList',
            'name' => 'Vertical Knowledge Pillars',
            'description' => 'Deep-dive research articles within the Digital Marketing category.',
            'itemListElement' => [
              [
                '@type' => 'ListItem',
                'position' => 1,
                'item' => [
                  '@type' => 'WebPage',
                  '@id' => $site . 'digital-marketing/semantic-seo/',
                  'url' => $site . 'digital-marketing/semantic-seo/',
                  'name' => 'Semantic SEO'
                ]
              ],
              [
                '@type' => 'ListItem',
                'position' => 2,
                'item' => [
                  '@type' => 'WebPage',
                  '@id' => $site . 'digital-marketing/what-is-topical-authority-seo/',
                  'url' => $site . 'digital-marketing/what-is-topical-authority-seo/',
                  'name' => 'Topical Authority'
                ]
              ],
              [
                '@type' => 'ListItem',
                'position' => 3,
                'item' => [
                  '@type' => 'WebPage',
                  '@id' => $site . 'digital-marketing/what-is-google-knowledge-graph/',
                  'url' => $site . 'digital-marketing/what-is-google-knowledge-graph/',
                  'name' => 'Google Knowledge Graph'
                ]
              ]
            ]
          ],
          'relatedLink' => [
            $site . 'ecommerce-development/',
            $site . 'ui-ux-design/'
          ],
          'mentions' => [
            [
              '@type' => 'Thing',
              'name' => 'Semantic SEO',
              'sameAs' => 'https://www.wikidata.org/wiki/Q180711'
            ],
            [
              '@type' => 'Thing',
              'name' => 'Core Web Vitals',
              'description' => 'LCP, FID, CLS metrics for search ranking.'
            ]
          ]
        ]
      ]
    ];
  }

  // ---------------------------------------------------
  // 4. ECOMMERCE DEVELOPMENT PAGE SCHEMA
  // ---------------------------------------------------
  elseif (is_page('ecommerce-development')) {
    $schema = [
      '@context' => 'https://schema.org',
      '@graph' => [
        [
          '@type' => 'CollectionPage',
          '@id' => $site . 'ecommerce-development/#webpage',
          'url' => $site . 'ecommerce-development/',
          'name' => 'E-commerce Development Hub | Systems & Strategy',
          'description' => 'Research hub for e-commerce architecture, platform logic, and scalability models.',
          'publisher' => ['@id' => $site . '#organization'],
          'isPartOf' => ['@id' => $site . '#website'],
          'mainEntity' => [
            '@type' => 'ItemList',
            'name' => 'Platform-Specific Research Guides',
            'itemListElement' => [
              [
                '@type' => 'ListItem',
                'position' => 1,
                'item' => [
                  '@type' => 'WebPage',
                  '@id' => $site . 'ecommerce-development-en/what-is-shopify-ecommerce-guide-2025/',
                  'url' => $site . 'ecommerce-development-en/what-is-shopify-ecommerce-guide-2025/',
                  'name' => 'Shopify E-commerce Guide 2025: Analysis & Features'
                ]
              ],
              [
                '@type' => 'ListItem',
                'position' => 2,
                'item' => [
                  '@type' => 'WebPage',
                  '@id' => $site . 'ecommerce-development-en/what-is-woocommerce-ecommerce/',
                  'url' => $site . 'ecommerce-development-en/what-is-woocommerce-ecommerce/',
                  'name' => 'Understanding WooCommerce: Ecosystem & Logic'
                ]
              ]
            ]
          ],
          'relatedLink' => [
            $site . 'digital-marketing/what-is-topical-authority-seo/',
            $site . 'ui-ux-design/'
          ],
          'mentions' => [
            ['@type' => 'Thing', 'name' => 'Shopify', 'sameAs' => 'https://www.wikidata.org/wiki/Q7501238'],
            ['@type' => 'Thing', 'name' => 'WooCommerce', 'sameAs' => 'https://www.wikidata.org/wiki/Q13100806'],
            ['@type' => 'Thing', 'name' => 'Magento', 'sameAs' => 'https://www.wikidata.org/wiki/Q1163773']
          ]
        ]
      ]
    ];
  }

  // ---------------------------------------------------
  // 5. UI/UX PAGE SCHEMA
  // ---------------------------------------------------
  elseif (is_page('ui-ux-design')) {
    $schema = [
      '@context' => 'https://schema.org',
      '@graph' => [
        [
          '@type' => 'CollectionPage',
          '@id' => $site . 'ui-ux-design/#webpage',
          'url' => $site . 'ui-ux-design/',
          'name' => 'UI/UX & App Design | User-Centered Principles',
          'description' => 'Research hub exploring user interaction, prototyping logic, and the impact of AI on the design ecosystem.',
          'publisher' => ['@id' => $site . '#organization'],
          'isPartOf' => ['@id' => $site . '#website'],
          'mainEntity' => [
            '@type' => 'ItemList',
            'name' => 'UI/UX Research Articles',
            'itemListElement' => [
              [
                '@type' => 'ListItem',
                'position' => 1,
                'item' => [
                  '@type' => 'WebPage',
                  '@id' => $site . 'ui-ux-design/are-ai-tools-expensive-for-beginners/',
                  'url' => $site . 'ui-ux-design/are-ai-tools-expensive-for-beginners/',
                  'name' => 'Are AI Tools Expensive for Beginners?'
                ]
              ]
            ]
          ],
          'relatedLink' => [
            $site . 'digital-marketing/user-experience-ux/',
            $site . 'website-cms-development/',
            $site . 'ecommerce-development/'
          ],
          'mentions' => [
            ['@type' => 'Thing', 'name' => 'User Experience', 'sameAs' => 'https://www.wikidata.org/wiki/Q1055535'],
            ['@type' => 'Thing', 'name' => 'Prototyping', 'sameAs' => 'https://www.wikidata.org/wiki/Q216398']
          ]
        ]
      ]
    ];
  }

  // ---------------------------------------------------
  // OUTPUT: Safely encode and print if a schema exists
  // ---------------------------------------------------
  if (!empty($schema)) {
    echo "\n" . '<script type="application/ld+json">' . "\n"
    . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n"
    . '</script>' . "\n";
  }

}, 20); // Priority 20 prevents blocking critical CSS




/* Custom Dynamic Breadcrumbs */
add_shortcode('custom_breadcrumbs', function () {
  if (is_front_page() || is_home())
    return '';

  // Detect current language
  $uri = $_SERVER['REQUEST_URI'] ?? '';
  $is_ko = (strpos($uri, '/ko/') !== false);

  $separator = ' <span class="separator">/</span> ';
  $home_label = $is_ko ? '홈' : 'Home';
  $home = '<a href="' . home_url('/') . '">' . $home_label . '</a>';

  $breadcrumbs = '<p class="breadcrumb">' . $home;

  if (is_single()) {
    $categories = get_the_category();
    if (!empty($categories)) {
      $cat = $categories[0];
      $cat_name = $cat->name;
      $cat_slug = $cat->slug;

      // EN: /blogs/  KO: /ko/블로그/
      $blog_path = $is_ko ? 'ko/%EB%B8%94%EB%A1%9C%EA%B7%B8/' : 'blogs/';
      $cat_url = home_url($blog_path) . '?filter=' . urlencode($cat_slug) . '#category-list';

      $breadcrumbs .= $separator . '<a href="' . $cat_url . '">' . esc_html($cat_name) . '</a>';
    } else {
      $blog_label = $is_ko ? '블로그' : 'Blogs';
      $blog_path = $is_ko ? 'ko/%EB%B8%94%EB%A1%9C%EA%B7%B8/' : 'blogs/';
      $breadcrumbs .= $separator . '<a href="' . home_url($blog_path) . '">' . $blog_label . '</a>';
    }
    $breadcrumbs .= $separator . '<span class="current">' . get_the_title() . '</span>';
  }
  elseif (is_page()) {
    $breadcrumbs .= $separator . '<span class="current">' . get_the_title() . '</span>';
  }
  elseif (is_category()) {
    $blog_label = $is_ko ? '블로그' : 'Blogs';
    $blog_path = $is_ko ? 'ko/%EB%B8%94%EB%A1%9C%EA%B7%B8/' : 'blog/';
    $breadcrumbs .= $separator . '<a href="' . home_url($blog_path) . '">' . $blog_label . '</a>';
    $breadcrumbs .= $separator . '<span class="current">' . single_cat_title('', false) . '</span>';
  }

  $breadcrumbs .= '</p>';

  return $breadcrumbs;
});
