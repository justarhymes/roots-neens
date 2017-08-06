<?php get_template_part('templates/page', 'header'); ?>

<?php if (!have_posts()) : ?>
  <div class="alert alert-warning">
    <?php _e('Sorry, no results were found.', 'sage'); ?>
  </div>
  <?php get_search_form(); ?>
<?php endif; ?>

<div id="posts-content" class="row flex-row">
  <?php while (have_posts()) : the_post(); ?>
    <?php get_template_part('templates/content', get_post_type() != 'post' ? get_post_type() : get_post_format()); ?>
  <?php endwhile; ?>
</div>

<?php
  if (  $wp_query->max_num_pages > 1 ) {
    echo '<button type="button" class="loadmore">More posts</button>';
  }
?>

<?php the_posts_navigation(); ?>
