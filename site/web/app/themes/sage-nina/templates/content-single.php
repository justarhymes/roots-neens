<?php while (have_posts()) : the_post(); ?>
  <article <?php post_class(); ?>>
    <header>
      <h1 class="entry-title"><?php the_title(); ?></h1>
      <a href="/" class="home-link"><i class="fa fa-angle-left"></i> Home</a>
    </header>
    <div class="entry-image">
      <?php
        if ( has_post_thumbnail() ) {
          the_post_thumbnail( 'full', ['class' => 'img-responsive'] );
        }
      ?>
    </div>
    <?php if ( get_the_content() ) : ?>
      <div class="entry-content">
        <?php the_content(); ?>
      </div>
    <?php endif; ?>
    <footer>
      <?php get_template_part('templates/entry-meta'); ?>
    </footer>
  </article>
<?php endwhile; ?>
