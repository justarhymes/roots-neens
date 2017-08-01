<?php $grid = array('col-md-6','col-xl-4'); ?>
<article <?php post_class($grid); ?>>
  <div class="entry">
    <header>
      <a href="<?php the_permalink(); ?>">
        <h2 class="entry-title"><?php the_title(); ?></h2>
        <?php get_template_part('templates/entry-meta'); ?>
      </a>
    </header>
    <div class="entry-image">
      <?php
        if ( has_post_thumbnail() ) {
          the_post_thumbnail( 'thumbnail', ['class' => 'img-responsive b-lazy'] );
        }
      ?>
    </div>
  </div>
</article>
