<section class="info">
  <h1><?php bloginfo('name'); ?></h1>
  <h6><?php bloginfo('description'); ?></h6>
</section>
<section class="page-links">
  <?php
    $pages = get_pages();
    foreach ( $pages as $page ) {
      $pageitem = '<a href="/' . $page->post_name . '">';
      $pageitem .= $page->post_title;
      $pageitem .= '</a>';
      echo $pageitem;
    }
  ?>
</section>
<?php dynamic_sidebar('sidebar-primary'); ?>
