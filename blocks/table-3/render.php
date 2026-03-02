<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

$A = is_array( $attributes ?? null ) ? $attributes : array();

$title_default = __( 'Choose the Right E-Commerce Model for Your Online Business', 'vite-ttf-child-creceri' );
$columns_default = array(
  array( 'key' => 'model', 'label' => __( 'Model', 'vite-ttf-child-creceri' ) ),
  array( 'key' => 'description', 'label' => __( 'Description', 'vite-ttf-child-creceri' ) ),
  array( 'key' => 'industries', 'label' => __( 'Common Industries', 'vite-ttf-child-creceri' ) ),
);
$rows_default = array(
  array(
    'cells' => array(
      array( 'text' => '<strong>B2C (Business to Consumer)</strong>' ),
      array( 'text' => __( 'Companies sell directly to individual customers.', 'vite-ttf-child-creceri' ) ),
      array( 'text' => __( 'Fashion, electronics, beauty, home goods', 'vite-ttf-child-creceri' ) ),
    ),
  ),
  array(
    'cells' => array(
      array( 'text' => '<strong>B2B (Business to Business)</strong>' ),
      array( 'text' => __( 'Businesses sell to other businesses, often in bulk or through contracts.', 'vite-ttf-child-creceri' ) ),
      array( 'text' => __( 'Industrial supplies, software, logistics', 'vite-ttf-child-creceri' ) ),
    ),
  ),
  array(
    'cells' => array(
      array( 'text' => '<strong>C2C (Consumer to Consumer)</strong>' ),
      array( 'text' => __( 'Individuals sell to other individuals, usually through marketplaces.', 'vite-ttf-child-creceri' ) ),
      array( 'text' => __( 'Vintage items, handmade crafts, resale', 'vite-ttf-child-creceri' ) ),
    ),
  ),
  array(
    'cells' => array(
      array( 'text' => '<strong>C2B / D2C (Consumer to Business / Direct to Consumer)</strong>' ),
      array( 'text' => __( 'Individuals offer services to companies (C2B), or brands sell directly to customers through their own sites (D2C).', 'vite-ttf-child-creceri' ) ),
      array( 'text' => __( 'Marketing, design, wellness, tech accessories', 'vite-ttf-child-creceri' ) ),
    ),
  ),
);

$title      = wp_kses_post( $A['title'] ?? $title_default );
$intro      = wp_kses_post( $A['intro'] ?? '' );
$aria_label = sanitize_text_field( $A['ariaLabel'] ?? __( 'E-commerce models', 'vite-ttf-child-creceri' ) );

$anchor     = sanitize_title( $A['anchor'] ?? '' );
$section_id = sanitize_title( $A['sectionId'] ?? '' );
$base_id    = $anchor ?: $section_id;
if ( $base_id === '' ) {
  $base_id = 'ecom-models-' . uniqid();
}

$classes = array( 'ecom-models' );
if ( ! empty( $A['align'] ) ) {
  $classes[] = 'align' . sanitize_html_class( $A['align'] );
}
if ( ! empty( $A['className'] ) ) {
  foreach ( preg_split( '/\s+/', $A['className'] ) as $class_name ) {
    $class_name = sanitize_html_class( $class_name );
    if ( $class_name ) {
      $classes[] = $class_name;
    }
  }
}
$classes = array_unique( $classes );

$box_size = $A['boxSize'] ?? 'wide';
if ( ! in_array( $box_size, array( 'wide', 'short' ), true ) ) {
  $box_size = 'wide';
}
$classes[] = 'ecom-models--size-' . $box_size;

$columns = array();
if ( isset( $A['columns'] ) && is_array( $A['columns'] ) ) {
  foreach ( $A['columns'] as $idx => $column_raw ) {
    if ( is_array( $column_raw ) ) {
      $label = sanitize_text_field( $column_raw['label'] ?? '' );
      if ( $label === '' ) {
        continue;
      }
      $key = sanitize_key( $column_raw['key'] ?? '' );
      if ( $key === '' ) {
        $key = 'col' . $idx;
      }
      $columns[] = array(
        'key'   => $key,
        'label' => $label,
      );
    } elseif ( is_string( $column_raw ) && $column_raw !== '' ) {
      $columns[] = array(
        'key'   => 'col' . $idx,
        'label' => sanitize_text_field( $column_raw ),
      );
    }
  }
}
if ( empty( $columns ) ) {
  $columns = $columns_default;
}
$column_count = count( $columns );

$rows = array();
if ( isset( $A['rows'] ) && is_array( $A['rows'] ) ) {
  foreach ( $A['rows'] as $row_raw ) {
    if ( ! is_array( $row_raw ) ) {
      continue;
    }

    $cells = array();
    if ( isset( $row_raw['cells'] ) && is_array( $row_raw['cells'] ) ) {
      foreach ( $columns as $index => $column ) {
        $cell_raw = $row_raw['cells'][ $index ] ?? '';
        if ( is_array( $cell_raw ) ) {
          $cells[] = wp_kses_post( $cell_raw['text'] ?? '' );
        } else {
          $cells[] = wp_kses_post( $cell_raw );
        }
      }
    } else {
      foreach ( $columns as $column ) {
        $key  = $column['key'];
        $text = '';
        if ( $key !== '' && isset( $row_raw[ $key ] ) ) {
          $source = $row_raw[ $key ];
          $text   = is_array( $source ) ? wp_kses_post( $source['text'] ?? '' ) : wp_kses_post( $source );
        }
        $cells[] = $text;
      }
    }

    $has_content = array_filter(
      $cells,
      static function ( $cell ) {
        return trim( wp_strip_all_tags( $cell ) ) !== '';
      }
    );

    if ( ! empty( $has_content ) ) {
      $rows[] = $cells;
    }
  }
}

if ( empty( $rows ) ) {
  foreach ( $rows_default as $row_default ) {
    $cells = array();
    foreach ( $columns as $idx => $column ) {
      $cells[] = wp_kses_post( $row_default['cells'][ $idx ]['text'] ?? '' );
    }
    $rows[] = $cells;
  }
}
$row_count = count( $rows );

if ( $column_count === 0 || $row_count === 0 ) {
  return;
}

$section_id_attr = ' id="' . esc_attr( $base_id ) . '"';
$heading_id      = sanitize_html_class( $base_id . '-title' );
$table_style     = ' style="--column-count:' . intval( $column_count ) . ';"';
?>
<section<?php echo $section_id_attr; ?> class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
  <?php if ( $title !== '' ) : ?>
    <h2 id="<?php echo esc_attr( $heading_id ); ?>" class="ecom-models__title"><?php echo $title; ?></h2>
  <?php endif; ?>

  <?php if ( $intro !== '' ) : ?>
    <p class="ecom-models__intro"><?php echo $intro; ?></p>
  <?php endif; ?>

  <div class="ecom-table" role="table" aria-label="<?php echo esc_attr( $aria_label ); ?>"<?php echo $table_style; ?>>
    <div class="ecom-table__header" role="rowgroup" aria-hidden="true">
      <?php foreach ( $columns as $column ) : ?>
        <div class="ecom-table__cell ecom-table__cell--head" role="columnheader"><?php echo esc_html( $column['label'] ); ?></div>
      <?php endforeach; ?>
    </div>

    <div class="ecom-table__body" role="rowgroup">
      <?php foreach ( $rows as $row_index => $row_cells ) : ?>
        <div class="ecom-table__row" role="row">
          <?php foreach ( $columns as $col_index => $column ) :
            $text          = $row_cells[ $col_index ] ?? '';
            $is_last_col   = ( $col_index === $column_count - 1 );
            $is_last_row   = ( $row_index === $row_count - 1 );
            $is_first_col  = ( $col_index === 0 );
            $cell_classes  = array( 'ecom-table__cell' );
            if ( $is_last_col ) {
              $cell_classes[] = 'ecom-table__cell--last-col';
            }
            if ( $is_last_row ) {
              $cell_classes[] = 'ecom-table__cell--last-row';
            }
            if ( $is_last_row && $is_first_col ) {
              $cell_classes[] = 'ecom-table__cell--last-row-first';
            }
            if ( $is_last_row && $is_last_col ) {
              $cell_classes[] = 'ecom-table__cell--last-row-last';
            }
            ?>
            <div class="<?php echo esc_attr( implode( ' ', $cell_classes ) ); ?>" role="cell" data-label="<?php echo esc_attr( $column['label'] ); ?>">
              <?php echo $text; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
