<?php
/*
  Plugin Name: DZS Ajaxer
  Plugin URI: http://digitalzoomstudio.net/
  Description: Ajaxify your site with this simple plugin.
  Version: 1.05
  Author: Digital Zoom Studio
  Author URI: http://digitalzoomstudio.net/
 */
include_once(dirname(__FILE__).'/dzs_functions.php');
if(!class_exists('DZSAjax')){
    include_once(dirname(__FILE__).'/class-dzsajx.php');
}



$dzsajx = new DZSAjax();

$dzsajx->is_preview = false;