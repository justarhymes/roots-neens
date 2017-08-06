<?php

class DZSAjax {

    public $main_options = '';
    public $thepath;
    public $base_url;
    public $base_path;
    public $indexnr = 0;
    public $shortcode = 'pricingtable';
    public $pluginmode = "plugin";
    public $dboptionsname = 'dzsajx_options';
    public $alwaysembed = 'on';
    public $mode = 'plugin';
    public $adminpagename_mainoptions = 'dzsajx_menu';
    public $admin_capability = 'manage_options';
    private $fe_colinner_not_set_yet = true;


    public $db_mainoptions = '';
    public $dbname_mainoptions = 'dzsajx_mo';

    public $options_for_each_theme = array();   



    public $is_preview = true; // -- set this to "true" for preview mode


    function __construct() {




        $defaultOpts = array(
            'enable_ajax' => 'on',
            'scripts_reinit_document_ready' => 'off',
            'content_container_selector' => '',
            'extra_css' => '',
            'menu_selector' => '',
            'menu_move_on_top_of_content' => 'off',
            'use_ajax_on_back_button' => 'off',
            'update_body_class' => 'off',
            'transition' => 'fade',
            'preloader' => 'none',
            'custom_preloader_html' => '',
            'extra_items_to_be_recovered' => '',
            'extra_items_to_be_added' => '',
            'extra_items_to_be_added_before_elements' => '',
            'scripts_execute_after_ajax_call' => 'on',
            'remove_document_ready' => 'off',
            'cache_pages' => 'off',
            'scripts_to_reload' => '',
            'classes_to_ignore' => '',
            'script_call_on_reinit' => '',
            'script_call_on_ready' => '',
            'comment_form_selector' => '.comment-form',
            'comment_list_selector' => '.comments-list',
            'search_form_selector' => '.searchform',
        );
        $this->db_mainoptions = get_option($this->dbname_mainoptions);

        //==== default opts / inject into db
        if ($this->db_mainoptions == '') {
            $this->db_mainoptions = $defaultOpts;
            update_option($this->dbname_mainoptions, $this->db_mainoptions);
        }else{

            $this->db_mainoptions = array_merge($defaultOpts, $this->db_mainoptions);
        }


        $this->base_url = plugins_url('',__FILE__).'/';
        $this->base_path = dirname(__FILE__).'/';



        if($this->db_mainoptions['scripts_reinit_document_ready']=='on'){

            add_filter('body_class',array($this, 'browser_body_class') );
        }


        add_action('wp_ajax_dzsajx_ajax_mo', array($this, 'post_save_mo'));
        add_action('wp_ajax_dzsajx_dismiss_notice', array($this, 'post_save_notice'));

        add_action('init', array($this, 'handle_init'));
        add_action('wp_head', array($this, 'handle_wp_head'));
        add_action('wp_footer', array($this, 'handle_wp_footer'));
        add_action('admin_head', array($this, 'handle_admin_head'));
        add_action('admin_menu', array($this, 'handle_admin_menu'));


        add_action( 'admin_notices', array($this, 'handle_admin_notices') );
        
    }
    function browser_body_class($classes = '') {

        $classes[] = 'dzsajx-enable-reinit';
        return $classes;
    }

    function handle_init() {
        wp_enqueue_script('jquery');
        if (is_admin()) {
            wp_enqueue_script('dzsajx-admin-global', $this->base_url . 'admin/admin-global.js');
            wp_enqueue_style('dzsajx-admin-global', $this->base_url . 'admin/admin-global.css');
            if (isset($_GET['page']) && $_GET['page'] == $this->adminpagename_mainoptions) {

                if(isset($_GET['dzsajx_shortcode_builder']) && $_GET['dzsajx_shortcode_builder']=='on'){


                    wp_enqueue_media();
                }else{

                    wp_enqueue_script('admin-mo', $this->base_url . 'admin/admin-mo.js');
                    wp_enqueue_style('admin-mo', $this->base_url . 'admin/admin-mo.css');
//                    wp_enqueue_script('admin-mo', $this->base_url . 'assets/dzscheckbox/admin-mo.js');
                    wp_enqueue_style('dzscheckbox', $this->base_url . 'assets/dzscheckbox/dzscheckbox.css');
                    wp_enqueue_script('dzsselector', $this->base_url . 'assets/dzsselector/dzsselector.js');
                    wp_enqueue_style('dzsselector', $this->base_url . 'assets/dzsselector/dzsselector.css');
                }
            }




        }else{

            $ver = false;
            if($this->db_mainoptions['scripts_reinit_document_ready']=='on'){
                $ver = '9.012';
            }

            wp_enqueue_script('dzsajx', $this->base_url . 'assets/ajaxer.js', array('jquery'),$ver);
            wp_enqueue_style('dzsajx', $this->base_url . 'assets/ajaxer.css');


            if($this->is_preview){

                wp_enqueue_script('dzsajx.preseter', $this->base_url . 'assets/preseter/preseter.js');
                wp_enqueue_style('dzsajx.preseter', $this->base_url . 'assets/preseter/preseter.css');
            }
        }
    }

    function handle_admin_notices(){

        if($this->db_mainoptions['content_container_selector']=='') {
            if ((get_option('dzsajx-notice-dismissed'))=='') {
                ?>

                <div class="notice  dzsajx-notice is-dismissible">
                    <p><?php printf(__('DZS Ajaxer needs configuration. Set it up %1$shere%2$s.'), '<a href="' . admin_url("admin.php?page=" . $this->adminpagename_mainoptions) . '">', '</a>'); ?></p>
                </div>
                <?php
            }
        }
    }

    function post_save_mo() {


        $auxarray = array();
        //parsing post data
        parse_str($_POST['postdata'], $auxarray);

        $auxarray = array_merge($this->db_mainoptions, $auxarray);

        update_option($this->dbname_mainoptions, $auxarray);
        die();
    }
    function post_save_notice() {


        update_option('dzsajx-notice-dismissed', 'on');
        die();
    }

    function handle_wp_footer() {

            if($this->is_preview){

        ?>
        <script>


            window.preseter_options= {
                'delay_time_to_autohide': 1000000
                ,init_on_document_ready : false
            };

            jQuery(document).ready(function($){

//            console.info(window.preseter_init);
                        if(window.preseter_init){

                $('.preseter.align-right').addClass('activated');
                window.preseter_init()


                            if(dzsajx_get_query_arg(window.location.href,'transition')){

//                                console.info(dzsajx_get_query_arg(window.location.href,'transition'))
                                $('*[name="transition"]').val(dzsajx_get_query_arg(window.location.href,'transition'));
                            }
                            if(dzsajx_get_query_arg(window.location.href,'preloader')){

//                                console.info(dzsajx_get_query_arg(window.location.href,'transition'))
                                $('*[name="preloader"]').val(dzsajx_get_query_arg(window.location.href,'preloader'));
                            }







            }

            });





</script>
        <form class="preseter align-right wait-for-activate preseter-opened-by-user" style="">

            <div class="the-icon-con">
                <div class=" btn-show-customizer"><svg class="the-icon" style="enable-background:new 0 0 500 500;" version="1.1" viewBox="0 0 500 500" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><g id="pencil-gear"><g><rect height="500" style="" width="500" y="0"/><g id="_x32_9"><g><path d="M407.573,132.556L363.13,88.12c-9.243-9.163-24.097-9.163-33.325,0.021L106.493,311.457      c-4.256,4.255-6.504,9.727-6.834,15.293l-10.378,58.923v0.703c-0.015,3.275,0.71,6.365,1.992,9.17l-9.793,9.785      c-3.062,3.069-3.062,8.043,0,11.111c3.069,3.076,8.064,3.076,11.118,0l9.793-9.785c2.79,1.274,5.889,2,9.141,2h0.85      l59.099-12.869l-0.007-0.029c4.665-0.783,9.162-2.938,12.788-6.533l57.451-57.451c-2.791-0.879-5.361-2.344-7.471-4.453      l-5.061-5.031c-0.036-0.037-0.051-0.088-0.095-0.117l-55.942,55.935c-3.011,3.003-8.094,3.024-11.118,0l-8.877-8.884      l76.494-76.494h-0.403c-3.677,0-7.09-1.106-9.968-2.975l-72.796,72.803l-14.436-14.45l78.771-78.772v-2.175      c0-10.137,8.27-18.398,18.428-18.398h2.139l1.399-1.392l-1.062-1.055c-2.007-2.021-3.391-4.409-4.277-6.929L125.375,341.442      l-7.778-7.772c-3.003-3.024-3.003-8.078,0-11.125l186.661-186.65l13.315,13.352l-61.682,61.678      c2.575,0.908,4.948,2.307,6.926,4.285l1.069,1.062l2.593-2.593c0.762-8.877,7.808-15.923,16.685-16.699l41.074-41.066      l14.458,14.436l-32.944,32.93c2.183,2.534,3.706,5.64,4.263,9.097l35.347-35.361l14.443,14.443l-23.921,23.921      c1.011,0.674,1.978,1.45,2.871,2.329l5.098,5.098c1.172,1.172,2.095,2.513,2.9,3.919l60.82-60.82      C416.743,156.675,416.743,141.799,407.573,132.556z M146.776,385.08l-35.911,7.829c-1.201-0.131-2.3-0.593-3.237-1.303      c-0.183-0.242-0.373-0.506-0.586-0.726c-0.227-0.234-0.483-0.381-0.718-0.586c-0.739-0.96-1.216-2.095-1.325-3.34l6.54-37.112      L146.776,385.08z" style="fill:#FFFFFF;"/></g><g><g><g><path d="M350.957,277.897v-7.156c0-3.977-3.208-7.155-7.163-7.155h-17.065        c-0.747-3.545-1.948-6.899-3.486-10.042l12.627-12.656c2.827-2.805,2.827-7.338,0.015-10.129l-5.054-5.068        c-2.812-2.791-7.324-2.791-10.122,0l-11.543,11.543c-3.091-2.176-6.519-3.882-10.122-5.171v-16.81        c0-3.962-3.208-7.162-7.148-7.17h-7.178c-3.94,0.008-7.148,3.208-7.148,7.17v14.971c-4.204,0.682-8.159,2-11.836,3.816        l-10.866-10.869c-2.789-2.791-7.338-2.791-10.144-0.008l-5.061,5.062c-2.783,2.783-2.783,7.324,0.021,10.137l9.727,9.733        c-2.505,3.626-4.482,7.596-5.786,11.924h-14.385c-3.962,0-7.178,3.201-7.178,7.142v7.155c0,3.963,3.216,7.171,7.178,7.171        h13.477c0.938,4.395,2.564,8.555,4.746,12.363l-10.319,10.319c-2.776,2.806-2.812,7.353-0.029,10.145l5.083,5.061        c2.79,2.791,7.346,2.776,10.145,0l10.125-10.137c3.56,2.154,7.412,3.802,11.514,4.842v15.732c0,3.947,3.208,7.163,7.163,7.163        h7.163c3.955,0,7.148-3.216,7.148-7.163v-15.732c3.765-0.982,7.324-2.417,10.605-4.285l12.114,12.093        c2.783,2.79,7.324,2.798,10.107,0l5.068-5.039c2.798-2.798,2.798-7.339,0-10.122l-11.924-11.939        c1.831-3.018,3.34-6.284,4.424-9.72h17.944C347.749,285.067,350.957,281.853,350.957,277.897z M302.646,272.536        c0,9.888-8.027,17.886-17.915,17.886c-9.888,0-17.886-7.998-17.886-17.886c0-9.91,7.998-17.908,17.886-17.908        C294.619,254.628,302.646,262.626,302.646,272.536z" style="fill:#FFFFFF;"/></g></g><g><g><path d="M420.815,329.87v-4.556c-0.015-2.534-2.065-4.563-4.57-4.563h-10.854        c-0.469-2.256-1.23-4.379-2.227-6.394l8.027-8.042c1.816-1.787,1.816-4.68,0.029-6.453l-3.237-3.223        c-1.773-1.779-4.658-1.779-6.431,0l-7.339,7.347c-1.978-1.384-4.16-2.476-6.445-3.281v-10.708c0-2.52-2.051-4.556-4.556-4.562        h-4.556c-2.505,0.007-4.556,2.043-4.556,4.562v9.529c-2.666,0.432-5.2,1.273-7.515,2.424l-6.929-6.914        c-1.773-1.772-4.673-1.772-6.46,0l-3.223,3.208c-1.758,1.787-1.758,4.673,0,6.46l6.211,6.196        c-1.611,2.308-2.856,4.834-3.677,7.588h-9.155c-2.52,0-4.57,2.036-4.57,4.541v4.548c0,2.535,2.051,4.564,4.57,4.564h8.569        c0.586,2.806,1.626,5.449,3.032,7.866l-6.562,6.57c-1.772,1.794-1.802,4.68-0.029,6.452l3.237,3.223        c1.787,1.78,4.673,1.773,6.431,0l6.46-6.445c2.256,1.362,4.731,2.417,7.324,3.076v10.013c0,2.512,2.051,4.562,4.57,4.562        h4.541c2.52,0,4.57-2.051,4.57-4.562v-10.013c2.388-0.615,4.658-1.538,6.738-2.725l7.705,7.69        c1.773,1.772,4.658,1.787,6.445,0l3.208-3.208c1.787-1.772,1.787-4.658,0-6.439l-7.573-7.595        c1.157-1.919,2.109-3.999,2.798-6.182h11.426C418.75,334.426,420.801,332.375,420.815,329.87z M390.054,326.442        c0,6.299-5.098,11.382-11.396,11.382c-6.284,0-11.397-5.083-11.397-11.382c0-6.299,5.112-11.397,11.397-11.397        C384.956,315.046,390.054,320.143,390.054,326.442z" style="fill:#FFFFFF;"/></g></g></g></g></g></g><g id="Layer_1"/></svg></div>
                <div class="btn-close-customizer"><svg class="the-icon" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="100px" height="100px" viewBox="0 25 100 100" enable-background="new 0 25 100 100" xml:space="preserve"> <g id="Layer_2"> <rect y="25" fill="#3D576B" width="100" height="100"/> </g> <g id="Layer_1"> <g id="pencil-gear"> <g> <g id="_x32_9"> <g> <path fill="#FFFFFF" d="M81.515,51.511l-8.889-8.887c-1.849-1.833-4.819-1.833-6.665,0.004L21.299,87.291 c-0.852,0.852-1.301,1.945-1.367,3.059l-2.075,11.785v0.141c-0.003,0.654,0.142,1.273,0.398,1.834l-1.958,1.957 c-0.612,0.613-0.612,1.608,0,2.223c0.614,0.615,1.613,0.615,2.224,0l1.958-1.957c0.558,0.254,1.178,0.399,1.829,0.399h0.17 l11.82-2.573l-0.001-0.006c0.933-0.157,1.832-0.588,2.558-1.307l11.49-11.49c-0.559-0.176-1.072-0.469-1.494-0.891 l-1.012-1.007c-0.007-0.007-0.01-0.018-0.019-0.023l-11.188,11.188c-0.603,0.601-1.619,0.604-2.224,0l-1.775-1.776 l15.299-15.299h-0.081c-0.735,0-1.418-0.222-1.994-0.596L29.295,97.512l-2.887-2.891l15.754-15.754v-0.435 c0-2.028,1.654-3.68,3.686-3.68h0.428l0.28-0.278l-0.212-0.211c-0.401-0.404-0.678-0.881-0.855-1.386L25.075,93.289 l-1.556-1.555c-0.601-0.605-0.601-1.616,0-2.226l37.332-37.33l2.663,2.67L51.178,67.185c0.516,0.182,0.99,0.461,1.386,0.857 l0.214,0.212l0.519-0.519c0.152-1.775,1.562-3.185,3.337-3.34l8.215-8.213l2.892,2.887l-6.589,6.586 c0.437,0.506,0.741,1.128,0.853,1.819l7.069-7.072l2.889,2.889l-4.784,4.784c0.202,0.135,0.396,0.29,0.574,0.466l1.02,1.02 c0.234,0.234,0.419,0.502,0.58,0.784l12.164-12.164C83.349,56.335,83.349,53.36,81.515,51.511z M29.355,102.016l-7.182,1.566 c-0.24-0.026-0.46-0.119-0.647-0.261c-0.037-0.048-0.074-0.101-0.117-0.146c-0.045-0.047-0.096-0.076-0.144-0.117 c-0.147-0.191-0.243-0.419-0.265-0.668l1.308-7.422L29.355,102.016z"/> </g> <g> <g> <g> <path fill="#FFFFFF" d="M65.771,81.142v-0.912c-0.003-0.506-0.413-0.912-0.914-0.912h-2.171 c-0.094-0.451-0.246-0.876-0.445-1.279l1.605-1.607c0.363-0.357,0.363-0.937,0.006-1.291l-0.647-0.645 c-0.354-0.355-0.932-0.355-1.286,0l-1.468,1.469c-0.396-0.276-0.832-0.495-1.289-0.656v-2.141 c0-0.504-0.41-0.912-0.911-0.913h-0.911c-0.501,0.001-0.911,0.409-0.911,0.913v1.905c-0.533,0.087-1.041,0.255-1.504,0.485 l-1.385-1.383c-0.355-0.355-0.936-0.355-1.293,0l-0.645,0.641c-0.352,0.357-0.352,0.936,0,1.293l1.242,1.238 c-0.322,0.462-0.57,0.967-0.734,1.518h-1.832c-0.504,0-0.914,0.408-0.914,0.908v0.91c0,0.507,0.41,0.912,0.914,0.912h1.715 c0.117,0.562,0.324,1.09,0.605,1.574l-1.312,1.313c-0.354,0.358-0.359,0.937-0.006,1.29l0.648,0.645 c0.357,0.356,0.934,0.355,1.285,0l1.293-1.289c0.451,0.273,0.945,0.484,1.465,0.615v2.003c0,0.503,0.41,0.913,0.914,0.913 h0.908c0.504,0,0.914-0.41,0.914-0.913v-2.003c0.477-0.123,0.932-0.307,1.348-0.545l1.541,1.539 c0.354,0.354,0.932,0.356,1.288,0l0.642-0.643c0.357-0.354,0.357-0.932,0-1.287l-1.514-1.52 c0.23-0.384,0.421-0.8,0.559-1.236h2.285C65.357,82.052,65.768,81.642,65.771,81.142z M59.618,80.456 c0,1.26-1.02,2.275-2.279,2.275c-1.257,0-2.279-1.016-2.279-2.275s1.022-2.28,2.279-2.28 C58.599,78.177,59.618,79.196,59.618,80.456z"/> </g> </g> </g> </g> </g> </g> <g id="Layer_1_1_"> </g> </g> <g id="Layer_3"> <path fill="#FFFFFF" d="M83.991,87.433L77.843,75.35c-0.562-1.106-1.931-1.551-3.038-0.989l-1.009,0.514 c-1.105,0.562-1.552,1.931-0.988,3.037l4.689,9.221l-9.219,4.691c-1.107,0.563-1.553,1.932-0.989,3.037l0.513,1.008 c0.563,1.108,1.932,1.554,3.039,0.989l12.082-6.147c0.595-0.303,0.991-0.838,1.149-1.438 C84.297,88.693,84.294,88.028,83.991,87.433z"/> </g> </svg> </div>
            </div>
            <div class="preseter-content-con auto-height overflow-x-visible" style="width: 160px; height: auto;"> <div class=" the-content-inner-con"> <div class="the-content inner" style=" " data-targetw="-160"> <div class="the-content-inner-inner">
                            <div class="the-bg"></div>
                            <div class="setting">
                                <h6>Transition</h6>
                                <?php
                                $arr_opts = array(
                                    array(
                                        'label'=>__('No Transition'),
                                        'value'=>"none",
                                    ),
                                    array(
                                        'label'=>__('Fade Transition'),
                                        'value'=>"fade",
                                    ),
                                    array(
                                        'label'=>__('Scale Down'),
                                        'value'=>"scaledown",
                                    ),
                                    array(
                                        'label'=>__('Slide'),
                                        'value'=>"slide",
                                    ),
                                );


                                $lab = "transition";
                                echo DZSHelpers::generate_select($lab,array(
                                    "options"=>$arr_opts,
                                    "seekval"=>$this->db_mainoptions[$lab],
                                ))
                                ?>

                            </div>

                            <div class="setting">

                                <h6><?php echo __("Preloader"); ?></h6>

                                <?php
                                $arr_opts = array(
                                    array(
                                        'label'=>__('No Preloader'),
                                        'value'=>"none",
                                    ),
                                    array(
                                        'label'=>__('Cursor Preloader'),
                                        'value'=>"cursor",
                                    ),
                                    array(
                                        'label'=>__('Wide Bar'),
                                        'value'=>"bar",
                                    ),
                                    array(
                                        'label'=>__('Fullscreen Bars'),
                                        'value'=>"bars",
                                    ),
                                );


                                $lab = "preloader";
                                echo DZSHelpers::generate_select($lab,array(
                                    "options"=>$arr_opts,
                                    "seekval"=>$this->db_mainoptions[$lab],
                                ))
                                ?>


                            </div>
                            <div style="white-space: nowrap; position: relative;"> <button class="preseter-button preseter-button--save">Customize</button>
                            </div> <div class="clear"></div>
                        </div><!--end the-content-->
                    </div>
                </div>
                </div>
                    </form>
        <?php

                if($this->db_mainoptions['comment_form_selector']){
                    ?>

                    <div class="comment-feedbacker"><?php echo __("Comment Submitted"); ?></div>


                    <?php
                }
}
    }

    function handle_add_meta_boxes() {
        
    }

    function handle_wp_head(){

        if($this->is_preview){
            if(isset($_GET['transition'])){
                $this->db_mainoptions['transition'] = $_GET['transition'];
            }
            if(isset($_GET['preloader'])){
                $this->db_mainoptions['preloader'] = $_GET['preloader'];
            }
        }
?><script>

        window.dzsajx_settings = {
            site_url: "<?php echo site_url(); ?>"
            ,enable_ajax: "<?php echo $this->db_mainoptions['enable_ajax']; ?>"
            ,unload_all_scripts_before_ajax: "on"
            ,content_container_selector: "<?php echo $this->db_mainoptions['content_container_selector']; ?>"
            ,menu_selector: "<?php echo $this->db_mainoptions['menu_selector']; ?>"
            ,menu_move_on_top_of_content: "<?php echo $this->db_mainoptions['menu_move_on_top_of_content']; ?>"
            ,use_ajax_on_back_button: "<?php echo $this->db_mainoptions['use_ajax_on_back_button']; ?>"
            ,transition: "<?php echo $this->db_mainoptions['transition']; ?>"
            ,preloader: "<?php echo $this->db_mainoptions['preloader']; ?>"
            ,custom_preloader_html: "<?php echo $this->db_mainoptions['custom_preloader_html']; ?>"
            ,extra_items_to_be_recovered: "<?php echo $this->db_mainoptions['extra_items_to_be_recovered']; ?>"
            ,extra_items_to_be_added: "<?php echo $this->db_mainoptions['extra_items_to_be_added']; ?>"
            ,extra_items_to_be_added_before_elements: "<?php echo $this->db_mainoptions['extra_items_to_be_added_before_elements']; ?>"
            ,scripts_execute_after_ajax_call: "<?php echo $this->db_mainoptions['scripts_execute_after_ajax_call']; ?>"
            ,remove_document_ready: "<?php echo $this->db_mainoptions['remove_document_ready']; ?>"
            ,scripts_reinit_document_ready: "<?php echo $this->db_mainoptions['scripts_reinit_document_ready']; ?>"
            ,scripts_to_reload: "<?php echo $this->db_mainoptions['scripts_to_reload']; ?>"
            ,cache_pages: "<?php echo $this->db_mainoptions['cache_pages']; ?>"
            ,classes_to_ignore: "<?php echo $this->db_mainoptions['classes_to_ignore']; ?>"
            ,script_call_on_reinit: "<?php $aux = stripslashes($this->db_mainoptions['script_call_on_reinit']); $aux = str_replace('"', '\"',$aux); $aux=str_replace(array("\n", "\r"),'',$aux); echo $aux; ?>"
            ,script_call_on_ready: "<?php $aux = stripslashes($this->db_mainoptions['script_call_on_ready']); $aux = str_replace('"', '\"',$aux); $aux=str_replace(array("\n", "\r"),'',$aux); echo $aux; ?>"
            ,disable_ajax_on_touch_devices: "off"
            ,update_body_class: "<?php echo $this->db_mainoptions['update_body_class']; ?>"
            ,comment_form_selector: "<?php echo $this->db_mainoptions['comment_form_selector']; ?>"
            ,comment_list_selector: "<?php echo $this->db_mainoptions['comment_list_selector']; ?>"
            ,search_form_selector: "<?php echo $this->db_mainoptions['search_form_selector']; ?>"
            ,translate_comment_submitted: "<?php echo __("Comment Submited") ?>"
        };</script>
        <?php
    }

    function ajax_preparePreview() {

    }
    function handle_admin_menu() {

        $dzsajx_page = add_menu_page(__('Ajaxer', 'dzsajx'), __('Ajaxer Settings', 'dzsajx'), $this->admin_capability, $this->adminpagename_mainoptions, array($this, 'admin_page_mainoptions'), 'div');

    }

    function admin_page_mainoptions() {



        if (isset($_POST['dzsajx_delete_alloptions']) && $_POST['dzsajx_delete_alloptions'] == 'on') {
            //delete_option('dzsajx_items');
            delete_option($this->dboptionsname);
            //delete_option('dzsajx_biggalleries');
        }

//        echo 'the theme - '.get_stylesheet();


        include("class_parts/themes_options.php");



        if(isset($_GET['dzsajx_shortcode_builder']) && $_GET['dzsajx_shortcode_builder']=='on'){
            dzsajx_shortcode_builder();
        }else {

            ?>

            <div class="wrap">
                <h2><?php echo __('Main Settings', 'dzsajx'); ?></h2>
                <p><a href="<?php echo $this->base_url; ?>readme/index.html" class="button-secondary"><?php echo __("Documentation"); ?></a> <button class="install-default-settings-for-theme button-primary" data-starttext="<?php echo __("Install Default Settings for "); ?>" style="display: none;">Install Settings for </button></p>

                <br>
                <form class="mainsettings">




                    <h3><?php echo __('Ajax Options', 'dzsajx'); ?></h3>
                    <div class="setting">

                        <?php
                        $lab = 'enable_ajax';
                        echo DZSHelpers::generate_input_text($lab,array('id' => $lab, 'val' => 'off','input_type'=>'hidden'));
                        ?>
                        <h4 class="setting-label"><?php echo __('Enable Ajax?','dzsapp'); ?></h4>
                        <div class="dzscheckbox skin-nova">
                            <?php
                            echo DZSHelpers::generate_input_checkbox($lab,array('id' => $lab, 'val' => 'on','seekval' => $this->db_mainoptions[$lab])); ?>
                            <label for="<?php echo $lab; ?>"></label>
                        </div>
                        <div class="sidenote"><?php echo __('enable the plugin','dzsajx'); ?></div>
                    </div>

                    <?php $lab = 'content_container_selector'; ?>
                    <div class="setting">
                        <h4 class="label"><?php echo __('Content Selector', 'dzsajx'); ?></h4>
                        <?php echo DZSHelpers::generate_input_text($lab, array('class' => 'textfield', 'seekval' => $this->db_mainoptions[$lab])); ?>
                        <div class="sidenote"><?php echo __('this is the content identifier, this content will be updated on each page change ', 'dzsajx'); ?></div>
                    </div>
                    <?php $lab = 'menu_selector'; ?>
                    <div class="setting">
                        <h4 class="label"><?php echo __('Menu Selector', 'dzsajx'); ?></h4>
                        <?php echo DZSHelpers::generate_input_text($lab, array('class' => 'textfield', 'seekval' => $this->db_mainoptions[$lab])); ?>
                        <div class="sidenote"><?php echo __('this is the menu identifier, define this in order for the menu to highlight and select the current menu item ', 'dzsajx'); ?></div>
                    </div>


                    <div class="setting">

                        <?php
                        $lab = 'menu_move_on_top_of_content';
                        echo DZSHelpers::generate_input_text($lab,array('id' => $lab, 'val' => 'off','input_type'=>'hidden'));
                        ?>
                        <h4 class="setting-label"><?php echo __('Move Menu on Top of Content','dzsapp'); ?></h4>
                        <div class="dzscheckbox skin-nova">
                            <?php
                            echo DZSHelpers::generate_input_checkbox($lab,array('id' => $lab, 'val' => 'on','seekval' => $this->db_mainoptions[$lab])); ?>
                            <label for="<?php echo $lab; ?>"></label>
                        </div>
                        <div class="sidenote"><?php echo __('move the menu on top of the content container so it will not be affected by Ajaxer changes ','dzsajx'); ?></div>
                    </div>


                    <div class="setting">

                        <?php
                        $lab = 'use_ajax_on_back_button';
                        echo DZSHelpers::generate_input_text($lab,array('id' => $lab, 'val' => 'off','input_type'=>'hidden'));
                        ?>
                        <h4 class="setting-label"><?php echo __('Use Ajax on Back Button ?','dzsapp'); ?></h4>
                        <div class="dzscheckbox skin-nova">
                            <?php
                            echo DZSHelpers::generate_input_checkbox($lab,array('id' => $lab, 'val' => 'on','seekval' => $this->db_mainoptions[$lab])); ?>
                            <label for="<?php echo $lab; ?>"></label>
                        </div>
                        <div class="sidenote"><?php echo __('','dzsajx'); ?></div>
                    </div>


                    


                    <div class="setting">
                        <h4><?php echo __("Transition"); ?></h4>
                        <?php
                        $lab = 'transition';

                        echo DZSHelpers::generate_select($lab, array(
                            'class'=>'dzs-style-me  skin-beige',
                            'seekval'=> $this->db_mainoptions[$lab],
                            'options' => array(

                                array(
                                    'lab'=>__("No Transition"),
                                    'val'=>"none",
                                ),
                                array(
                                    'lab'=>__("Fade Transition"),
                                    'val'=>"fade",
                                ),
                                array(
                                    'lab'=>__("Scale Down"),
                                    'val'=>"scaledown",
                                ),
                                array(
                                    'lab'=>__("Slide"),
                                    'val'=>"slide",
                                ),
                            ),
                        ))

                        ?>

                        <p class="sidenote"><?php echo __("select the transition"); ?></p>
                    </div>


                    <div class="setting">
                        <h4><?php echo __("Preloader"); ?></h4>
                        <?php
                        $lab = 'preloader';

                        echo DZSHelpers::generate_select($lab, array(
                            'class'=>'dzs-style-me  skin-beige',
                            'seekval'=> $this->db_mainoptions[$lab],
                            'options' => array(

                                array(
                                    'lab'=>__("No Preloader"),
                                    'val'=>"none",
                                ),
                                array(
                                    'label'=>__('Cursor Preloader'),
                                    'value'=>"cursor",
                                ),
                                array(
                                    'lab'=>__("Wide Bar"),
                                    'val'=>"bar",
                                ),
                                array(
                                    'lab'=>__("Center Bars"),
                                    'val'=>"bars",
                                ),
                                array(
                                    'lab'=>__("Custom Preloader"),
                                    'val'=>"custom",
                                ),
                            ),
                        ))

                        ?>

                        <p class="sidenote"><?php echo __("select the preloader style"); ?></p>
                    </div>




                    <?php $lab = 'custom_preloader_html'; ?>
                    <div class="setting">
                        <h4 class="label"><?php echo __('Custom Preloader html', 'dzsajx'); ?></h4>
                        <?php echo DZSHelpers::generate_input_textarea($lab, array('class' => 'textfield', 'extraattr' => ' style="resize:both;"', 'seekval' => stripslashes($this->db_mainoptions[$lab]))); ?>
                        <div class="sidenote"><?php echo __('only if preloader is set to custom - this html will take place', 'dzsajx'); ?></div>
                    </div>



                    <?php $lab = 'extra_items_to_be_recovered'; ?>
                    <div class="setting">
                        <h4 class="label"><?php echo __('Extra Items to be Replaced', 'dzsajx'); ?></h4>
                        <?php echo DZSHelpers::generate_input_text($lab, array('class' => 'textfield', 'seekval' => $this->db_mainoptions[$lab])); ?>
                        <div class="sidenote"><?php echo __('input the selector of some extra items to be replaced  like for example a header outside the content area. you can separate the selectors by <strong>,</strong>', 'dzsajx'); ?></div>
                    </div>

                    <?php $lab = 'extra_items_to_be_added'; ?>
                    <div class="setting">
                        <h4 class="label"><?php echo __('Extra Items to be Added', 'dzsajx'); ?></h4>
                        <?php echo DZSHelpers::generate_input_text($lab, array('class' => 'textfield', 'seekval' => $this->db_mainoptions[$lab])); ?>
                        <div class="sidenote"><?php echo __('input the selector of some extra items to be added if they do not exist, like a footer audio player', 'dzsajx'); ?></div>
                    </div>

                    <?php $lab = 'extra_items_to_be_added_before_elements'; ?>
                    <div class="setting">
                        <h4 class="label"><?php echo __('Extra Items will be Added Before Element', 'dzsajx'); ?></h4>
                        <?php echo DZSHelpers::generate_input_text($lab, array('class' => 'textfield', 'seekval' => $this->db_mainoptions[$lab])); ?>
                        <div class="sidenote"><?php echo __('by default extra items will be added at the bottom of the page, but inputing a selector here can add them before this selector', 'dzsajx'); ?></div>
                    </div>

                    <?php $lab = 'scripts_to_reload'; ?>
                    <div class="setting">
                        <h4 class="label"><?php echo __('Scripts to Reload', 'dzsajx'); ?></h4>
                        <?php echo DZSHelpers::generate_input_text($lab, array('class' => 'textfield', 'seekval' => $this->db_mainoptions[$lab])); ?>
                        <div class="sidenote"><?php echo __('these scripts will be discarded and loaded again when the new page loads', 'dzsajx'); ?></div>
                    </div>

                    <?php $lab = 'classes_to_ignore'; ?>
                    <div class="setting">
                        <h4 class="label"><?php echo __('Selectors to Ignore', 'dzsajx'); ?></h4>
                        <?php echo DZSHelpers::generate_input_text($lab, array('class' => 'textfield', 'seekval' => $this->db_mainoptions[$lab])); ?>
                        <div class="sidenote"><?php echo __('Extra Selectors to ignore when applying ajaxer to &lt;a&gt; tags', 'dzsajx'); ?></div>
                    </div>



                    <div class="setting">

                        <?php
                        $lab = 'scripts_execute_after_ajax_call';
                        echo DZSHelpers::generate_input_text($lab,array('id' => $lab, 'val' => 'off','input_type'=>'hidden'));
                        ?>
                        <h4 class="setting-label"><?php echo __('Scripts Execute after Ajax Call ?','dzsapp'); ?></h4>
                        <div class="dzscheckbox skin-nova">
                            <?php
                            echo DZSHelpers::generate_input_checkbox($lab,array('id' => $lab, 'val' => 'on','seekval' => $this->db_mainoptions[$lab])); ?>
                            <label for="<?php echo $lab; ?>"></label>
                        </div>
                        <div class="sidenote"><?php echo __('select this if javascript scripts in the page should execute after the ajax call is completed ? ','dzsajx'); ?></div>
                    </div>


                    <div class="setting">

                        <?php
                        $lab = 'remove_document_ready';
                        echo DZSHelpers::generate_input_text($lab,array('id' => $lab, 'val' => 'off','input_type'=>'hidden'));
                        ?>
                        <h4 class="setting-label"><?php echo __('Remove Document Ready From Reinited Scripts ? ','dzsapp'); ?></h4>
                        <div class="dzscheckbox skin-nova">
                            <?php
                            echo DZSHelpers::generate_input_checkbox($lab,array('id' => $lab, 'val' => 'on','seekval' => $this->db_mainoptions[$lab])); ?>
                            <label for="<?php echo $lab; ?>"></label>
                        </div>
                        <div class="sidenote"><?php echo __('select this if the javascript will not execute in the reinited scripts ','dzsajx'); ?></div>
                    </div>


                    <div class="setting">

                        <?php
                        $lab = 'cache_pages';
                        echo DZSHelpers::generate_input_text($lab,array('id' => $lab, 'val' => 'off','input_type'=>'hidden'));
                        ?>
                        <h4 class="setting-label"><?php echo __('Cache Pages ? ','dzsapp'); ?></h4>
                        <div class="dzscheckbox skin-nova">
                            <?php
                            echo DZSHelpers::generate_input_checkbox($lab,array('id' => $lab, 'val' => 'on','seekval' => $this->db_mainoptions[$lab])); ?>
                            <label for="<?php echo $lab; ?>"></label>
                        </div>
                        <div class="sidenote"><?php echo __('cache the pages into memory','dzsajx'); ?></div>
                    </div>


                    <div class="setting">

                        <?php
                        $lab = 'scripts_reinit_document_ready';
                        echo DZSHelpers::generate_input_text($lab,array('id' => $lab, 'val' => 'off','input_type'=>'hidden'));
                        ?>
                        <h4 class="setting-label"><?php echo __('Reinit All Scripts ?','dzsapp'); ?></h4>
                        <div class="dzscheckbox skin-nova">
                            <?php
                            echo DZSHelpers::generate_input_checkbox($lab,array('id' => $lab, 'val' => 'on','seekval' => $this->db_mainoptions[$lab])); ?>
                            <label for="<?php echo $lab; ?>"></label>
                        </div>
                        <div class="sidenote"><?php echo __('reinit all scripts on page load ? ajaxer will try a reloading of all scripts on page load - can be useful if some scripts of the theme do not apply on another page load','dzsajx'); ?></div>
                    </div>


                    <div class="setting">

                        <?php
                        $lab = 'update_body_class';
                        echo DZSHelpers::generate_input_text($lab,array('id' => $lab, 'val' => 'off','input_type'=>'hidden'));
                        ?>
                        <h4 class="setting-label"><?php echo __('Update Body Class','dzsapp'); ?></h4>
                        <div class="dzscheckbox skin-nova">
                            <?php
                            echo DZSHelpers::generate_input_checkbox($lab,array('id' => $lab, 'val' => 'on','seekval' => $this->db_mainoptions[$lab])); ?>
                            <label for="<?php echo $lab; ?>"></label>
                        </div>
                        <div class="sidenote"><?php echo __('update the body class with the one from the new page','dzsajx'); ?></div>
                    </div>



                    <?php $lab = 'script_call_on_reinit'; ?>
                    <div class="setting">
                        <h4 class="label"><?php echo __('Script Call on Page Change', 'dzsajx'); ?></h4>
                        <?php echo DZSHelpers::generate_input_textarea($lab, array('class' => 'textfield', 'extraattr' => ' style="resize:both;"', 'seekval' => stripslashes($this->db_mainoptions[$lab]))); ?>
                        <div class="sidenote"><?php echo __('javascript call on page change . - warning only modify if you know what you are doing', 'dzsajx'); ?></div>
                    </div>



                    <?php $lab = 'script_call_on_ready'; ?>
                    <div class="setting">
                        <h4 class="label"><?php echo __('Script Call on Page Ready', 'dzsajx'); ?></h4>
                        <?php echo DZSHelpers::generate_input_textarea($lab, array('class' => 'textfield', 'extraattr' => ' style="resize:both;"', 'seekval' => stripslashes($this->db_mainoptions[$lab]))); ?>
                        <div class="sidenote"><?php echo __('javascript call on page change . - warning only modify if you know what you are doing', 'dzsajx'); ?></div>
                    </div>






                    <?php $lab = 'comment_form_selector'; ?>
                    <div class="setting">
                        <h4 class="label"><?php echo __('Comment Form Selector', 'dzsajx'); ?></h4>
                        <?php echo DZSHelpers::generate_input_text($lab, array('class' => 'textfield', 'seekval' => $this->db_mainoptions[$lab])); ?>
                        <div class="sidenote"><?php echo __('', 'dzsajx'); ?></div>
                    </div>
                    <?php $lab = 'comment_list_selector'; ?>
                    <div class="setting">
                        <h4 class="label"><?php echo __('Comment Form Selector', 'dzsajx'); ?></h4>
                        <?php echo DZSHelpers::generate_input_text($lab, array('class' => 'textfield', 'seekval' => $this->db_mainoptions[$lab])); ?>
                        <div class="sidenote"><?php echo __('', 'dzsajx'); ?></div>
                    </div>
                    <?php $lab = 'search_form_selector'; ?>
                    <div class="setting">
                        <h4 class="label"><?php echo __('Search Form Selector', 'dzsajx'); ?></h4>
                        <?php echo DZSHelpers::generate_input_text($lab, array('class' => 'textfield', 'seekval' => $this->db_mainoptions[$lab])); ?>
                        <div class="sidenote"><?php echo __('', 'dzsajx'); ?></div>
                    </div>

                    <br/>
                    <a href='#'
                       class="button-primary save-btn save-mainoptions"><?php echo __('Save Options', 'dzsajx'); ?></a>
                </form>
                <br/><br/>

                <div class="dzsajx-theme-name"><?php echo '<strong>'.__("Current Theme - ").'</strong>'. get_template(); ?></div>

<!--                <form class="mainsettings" method="POST">-->
<!--                    <button name="dzsajx_delete_alloptions" value="on"-->
<!--                            class="button-secondary">--><?php //echo __('Delete All DZS Pricing Tables Plugin Data', 'dzsajx'); ?><!--</button>-->
<!--                </form>-->
                <div class="saveconfirmer" style=""><img alt="" style="" id="save-ajax-loading2"
                                                         src="<?php echo site_url(); ?>/wp-admin/images/wpspin_light.gif"/>
                </div>
                <script>
                    jQuery(document).ready(function ($) {
                        dzsajx_mo_ready($);
                    })
                </script>
            </div>
            <div class="clear"></div>
            <?php
        }
    }

    function handle_admin_head() {






        echo '<script>
        var dzsajx_settings = {
            thepath : "'.$this->base_url.'"
            ,the_url : "'.$this->base_url.'"
    ';

//        wp_enqueue_script('dzsajx_configreceiver', $this->thepath . 'tinymce/receiver.js');



            echo '
}
</script>';
    }


}