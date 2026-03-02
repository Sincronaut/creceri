<div class="wrapper">
  <h1>Lorem Ipsum Dolor Sit Amet</h1>

  <div class="grid">
    <main>
      <div class="hero" aria-hidden="true"></div>

      <section id="intro">
        <p class="callout">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer nec odio. Praesent libero. Sed cursus ante dapibus diam. Nulla quis sem at nibh elementum imperdiet. Duis sagittis ipsum. Praesent mauris. Fusce nec tellus sed augue semper porta. Mauris massa. Vestibulum lacinia arcu eget nulla.
            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi leo risus, porta ac consectetur ac, vestibulum at eros. Curabitur blandit tempus porttitor. Maecenas faucibus mollis interdum.</p>
      </section>
      <?php
          /**
           * Block: Blog Content - Sections
           * Path: https://creceri.com/wp-content/themes/your-child-theme/blocks/blog-content/render.php
           */
          if ( ! defined( 'ABSPATH' ) ) { exit; }

          /* Helpers */
          function bc_slug( $str, $fallback = 'sec' ) {
            $slug = sanitize_title( (string) $str );
            return $slug ? $slug : $fallback . '-' . wp_rand(100,999);
          }
          function bc_allowed_html() {
            return array(
              'a' => array('href'=>true,'title'=>true,'target'=>true,'rel'=>true),
              'br'=>true,'em'=>true,'strong'=>true,'b'=>true,'i'=>true,'u'=>true,
              'span'=>array('class'=>true),
              'p'=>array(),'ul'=>array(),'ol'=>array(),'li'=>array()
            );
          }

          /* Read + normalize attributes */
          $attrs    = is_array( $attributes ?? null ) ? $attributes : array();
          $sections = $attrs['section'] ?? array();

          /* Normalize: allow a single object or a proper array */
          if ( is_array($sections) ) {
            $is_assoc = array_keys($sections) !== range(0, count($sections)-1);
            if ( $is_assoc && isset($sections['design']) ) {
              $sections = array( $sections );
            } elseif ( $is_assoc ) {
              $sections = array();
            }
          } else {
            $sections = array();
          }

          $khtml = bc_allowed_html();

          /* Render each section by its design */
          foreach ( $sections as $i => $s ) {

            $design = $s['design'] ?? 'section_1';
            $title  = $s['title']  ?? '';
            $intro  = $s['intro']  ?? '';
            $id     = $s['id']     ?? bc_slug( $title ?: ($design . '-' . ($i+1)) );

            switch ( $design ) {

              /* ===============================
                SECTION 1 -- Text + subsections
                =============================== */
              case 'section_1':
              default:
                $subsection = is_array( $s['subsection'] ?? null ) ? $s['subsection'] : array();
                // Optional top-level bullets (not in your sample, but supported)
                $top_ul     = is_array( $s['ul'] ?? null ) ? $s['ul'] : array();
                ?>
                <section id="<?php echo esc_attr($id); ?>" class="section-one section_1">
                  <?php if ( $title !== '' ) : ?>
                    <h2><?php echo esc_html($title); ?></h2>
                  <?php endif; ?>

                  <?php if ( $intro !== '' ) : ?>
                    <p><?php echo wp_kses($intro, $khtml); ?></p>
                  <?php endif; ?>

                  <?php if ( !empty($top_ul) ) : ?>
                    <ul>
                      <?php foreach ( $top_ul as $li ) : ?>
                        <li><?php echo wp_kses($li, $khtml); ?></li>
                      <?php endforeach; ?>
                    </ul>
                  <?php endif; ?>

                  <?php if ( !empty($subsection) ) : ?>
                    <?php foreach ( $subsection as $j => $sub ) :
                      $stitle = $sub['sub_title'] ?? ($sub['title'] ?? '');
                      $stext  = $sub['text']      ?? '';
                      $ul     = is_array($sub['ul'] ?? null) ? $sub['ul'] : array();
                      $sid    = $sub['id'] ?? bc_slug( $stitle ?: 'sub', $id . '-sub' );
                    ?>
                      <section id="<?php echo esc_attr($sid); ?>" class="section-one__sub">
                        <?php if ( $stitle !== '' ) : ?><h3><?php echo esc_html($stitle); ?></h3><?php endif; ?>
                        <?php if ( $stext  !== '' ) : ?><p><?php echo wp_kses($stext, $khtml); ?></p><?php endif; ?>

                        <?php if ( !empty($ul) ) : ?>
                          <ul>
                            <?php foreach ( $ul as $li ) : ?>
                              <li><?php echo wp_kses($li, $khtml); ?></li>
                            <?php endforeach; ?>
                          </ul>
                        <?php endif; ?>
                      </section>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </section>
                <?php
                break;

              /* ===============================
                SECTION 2 -- Video
                =============================== */
              case 'section_2':
                $link = $s['link'] ?? ''; // video URL
                ?>
                <section id="<?php echo esc_attr($id); ?>" class="section-two section_2">
                  <?php if ( $title !== '' ) : ?>
                    <h2><?php echo esc_html($title); ?></h2>
                  <?php endif; ?>

                  <?php if ( $intro !== '' ) : ?>
                    <p><?php echo wp_kses($intro, $khtml); ?></p>
                  <?php endif; ?>

                  <?php if ( $link ) : ?>
                    <section id="<?php echo esc_attr($id); ?>-1" class="section-two__embed">
                      <?php
                        $embed = wp_oembed_get( esc_url( $link ) );
                        if ( $embed ) {
                          echo $embed; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        } else {
                          // Fallback iframe (kept simple; wrap with your responsive styles)
                          ?>
                          <iframe
                            src="<?php echo esc_url( $link ); ?>"
                            width="800" height="450"
                            frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            referrerpolicy="strict-origin-when-cross-origin"
                            allowfullscreen loading="lazy">
                          </iframe>
                          <?php
                        }
                      ?>
                    </section>
                  <?php endif; ?>
                </section>
                <?php
                break;
            }
          }
      ?>

      <section id="conclusion"><h2>Conclusion</h2>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed posuere consectetur est at lobortis. Cras mattis consectetur purus sit amet fermentum. Vestibulum id ligula porta felis euismod semper. Nulla vitae elit libero, a pharetra augue. Donec ullamcorper nulla non metus auctor fringilla.</p>
      </section>

      <section id="conclusion"><h2>Resources</h2>
        <ul>
            <li><b>Resource 1:</b> Lorem ipsum dolor sit amet, consectetur adipiscing elit.</li>
            <li><b>Resource 2:</b> Aenean lacinia bibendum nulla sed consectetur.</li>
            <li><b>Resource 3:</b> Curabitur blandit tempus porttitor sed posuere.</li>
            <li><b>Resource 4:</b> Cras mattis consectetur purus sit amet fermentum.</li>
            <li><b>Resource 5:</b> Vestibulum id ligula porta felis euismod semper.</li>
        </ul>
      </section>

      <section id="resources"><h2>Sample Resources</h2><p>Links.</p></section>
    </main>

    <aside class="toc">
        <nav class="toc-nav" aria-label="Table of contents">
          <h2 class="toc-title" aria-hidden="true">Table of Contents</h2>
          <ol>
            <li><a href="#intro">1. Introduction</a></li>
            <li>
              <a href="#sec1">2. Section One: Lorem Ipsum</a>
              <ul class="sub">
                <li><a href="#sec1-1">Subsection 1.1</a></li>
                <li><a href="#sec1-2">Subsection 1.2</a></li>
              </ul>
            </li>
            <li>
              <a href="#sec2">3. Section Two</a>
              <ul class="sub">
                <li><a href="#sec2-1">Subsection 2.1</a></li>
                <li><a href="#sec2-2">Subsection 2.2</a></li>
              </ul>
            </li>
            <li>
              <a href="#sec3">4. Section Three</a>
              <ul class="sub">
                <li><a href="#sec3-1">Subsection 3.1</a></li>
                <li><a href="#sec3-2">Subsection 3.2</a></li>
              </ul>
            </li>
            <li><a href="#conclusion">5. Conclusion</a></li>
            <li><a href="#resources">6. Sample Resources</a></li>
          </ol>
        </nav>
    </aside>
    
  </div>
</div>

<script>
  // Keep TOC open on desktop, collapsible on mobile
  const toc = document.getElementById('toc');
  const mq = window.matchMedia('(max-width: 900px)');
  function setTocMode() {
    if (!toc) return;
    toc.open = !mq.matches;      // open on desktop, closed on mobile
  }
  setTocMode();
  mq.addEventListener?.('change', setTocMode);

  // Smooth scroll + auto-collapse on mobile after clicking a link
  document.querySelectorAll('.toc-nav a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
      e.preventDefault();
      const id = a.getAttribute('href');
      document.querySelector(id)?.scrollIntoView({behavior:'smooth', block:'start'});
      if (mq.matches && toc) toc.open = false;   // collapse after navigation on mobile
      history.replaceState(null, '', id);
    });
  });
</script>
