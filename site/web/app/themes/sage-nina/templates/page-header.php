<?php use Roots\Sage\Titles; ?>

<?php if (Titles\title()) : ?>
  <div class="page-header">
    <h1><?= Titles\title(); ?></h1>
    <a href="/" class="home-link"><i class="fa fa-angle-left"></i> Home</a>
  </div>
<?php endif; ?>
