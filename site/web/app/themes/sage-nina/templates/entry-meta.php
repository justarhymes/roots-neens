<?php if (is_single()) : ?>
  <div class="entry-meta">
    <div class="entry-cats">
      <?php $categories = get_the_category();
        $separator = '';
        $output = '';
        if ( ! empty( $categories ) ) {
          foreach( $categories as $category ) {
            $output .= '<a class="cat btn" href="' . esc_url( get_category_link( $category->term_id ) ) . '" alt="' . esc_attr( sprintf( __( 'View all posts in %s', 'textdomain' ), $category->name ) ) . '"><i class="fa fa-tags"></i>' . esc_html( $category->name ) . '</a>' . $separator;
          }
          echo trim( $output, $separator );
        }
      ?>
    </div>
    <?php $posttags = get_the_tags();
      if ($posttags) {
        echo '<div class="entry-tags">';
        foreach($posttags as $tag) {
          echo '<a class="btn tag" href="' . esc_url( get_tag_link( $tag->term_id ) ) . '" alt="' . esc_attr( sprintf( __( 'View all posts in %s', 'textdomain' ), $tag->name ) ) . '"><i class="fa fa-hashtag"></i>' . esc_html( $tag->name ) . '</a>';
        }
        echo '</div>';
      }
    ?>
  </div>
<?php else : ?>
  <div class="entry-meta">In <?php $categories = get_the_category();
    if ( ! empty( $categories ) ) {
        echo '<span class="cat">' . esc_html( $categories[0]->name ) . '</span>';
    }
  ?>, on <time class="updated" datetime="<?= get_post_time('c', true); ?>"><?= get_the_date(); ?></time></div>
<?php endif; ?>
