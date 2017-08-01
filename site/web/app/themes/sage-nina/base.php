<?php

use Roots\Sage\Setup;
use Roots\Sage\Wrapper;

?>

<!doctype html>
<html <?php language_attributes(); ?>>
  <?php get_template_part('templates/head'); ?>
  <body <?php body_class(); ?>>
    <!--[if IE]>
      <div class="alert alert-warning">
        <?php _e('You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.', 'sage'); ?>
      </div>
    <![endif]-->
    <div class="wrap container-fluid" role="document">
      <div class="content row flex-row-reverse">
        <header class="mobile-header menu-links">
          <?php include Wrapper\sidebar_path(); ?>
        </header>
        <main class="main">
          <div id="main-content" class="inner-content">
            <?php include Wrapper\template_path(); ?>
            <div class="loading">
              <div class="load-content">
                <img src="<?php bloginfo('template_directory') ?>/assets/images/loady.gif" alt="Loading...">
                <p>Loading...</p>
              </div>
            </div>
          </div>
          <?php
            do_action('get_footer');
            get_template_part('templates/footer');
          ?>
        </main><!-- /.main -->
        <?php if (Setup\display_sidebar()) : ?>
          <aside class="sidebar menu-links">
            <?php include Wrapper\sidebar_path(); ?>
          </aside><!-- /.sidebar -->
        <?php endif; ?>
      </div><!-- /.content -->
    </div><!-- /.wrap -->
  </body>
  <?php wp_footer(); ?>
</html>
