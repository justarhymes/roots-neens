<?php

if (!function_exists('dzs_savemeta')) {

    function dzs_savemeta($id, $arg2, $arg3 = '') {
        //echo htmlentities($_POST[$arg2]);
        if ($arg3 == 'html') {
            update_post_meta($id, $arg2, htmlentities($_POST[$arg2]));
            return;
        }


        if (isset($_POST[$arg2]))
            update_post_meta($id, $arg2, esc_attr(strip_tags($_POST[$arg2])));
        else
        if ($arg3 == 'checkbox')
            update_post_meta($id, $arg2, "off");
    }

}



if (!function_exists('dzs_checked')) {

    function dzs_checked($arg1, $arg2, $arg3 = 'checked', $echo = true) {
        $func_output = '';
        if (isset($arg1) && $arg1 == $arg2) {
            $func_output = $arg3;
        }
        if ($echo == true)
            echo $func_output;
        else
            return $func_output;
    }

}

if (!function_exists('dzs_find_string')) {

    function dzs_find_string($arg, $arg2) {
        $pos = strpos($arg, $arg2);

        if ($pos === false)
            return false;

        return true;
    }

}


if (!function_exists('dzs_get_excerpt')) {

    //echo 'dzs_get_excerpt 
    //version 1.2';
    function dzs_get_excerpt($pid = 0, $pargs = array()) {
        //print_r($pargs);
        global $post;
        $fout = '';
        $excerpt = '';
        if ($pid == 0 && isset($post->ID)) {
            $pid = $post->ID;
        }
        //echo $pid;
        if(function_exists('get_post')){
            $po = (get_post($pid));
        }
        

        $margs = array(
            'maxlen' => 400
            , 'striptags' => false
            , 'stripshortcodes' => false
            , 'forceexcerpt' => false //if set to true will ignore the manual post excerpt
            , 'try_to_close_unclosed_tags' => false // -- this will try to close unclosed tags
            , 'readmore' => 'auto'
            , 'readmore_markup' => ''
            , 'content' => ''
        );
        $margs = array_merge($margs, $pargs);

//        print_r($margs);

        if ($margs['content'] != '') {
            if($margs['readmore']=='auto'){

                $margs['readmore'] = 'off';
            }
            $margs['forceexcerpt'] = true;
        }


        if (isset($po->post_excerpt) && $po->post_excerpt != '' && $margs['forceexcerpt'] == false) {
            $fout = $po->post_excerpt;


            //==== replace the read more with given markup or theme function or default
            if ($margs['readmore_markup'] != '') {
                $fout = str_replace('{readmore}', $margs['readmore_markup'], $fout);
            } else {
                if (function_exists('continue_reading_link')) {
                    $fout = str_replace('{readmore}', continue_reading_link($pid), $fout);
                } else {
                    $fout = str_replace('{readmore}', '<div class="readmore-con"><a href="' . get_permalink($pid) . '">' . __('Read More') . '</a></div>', $fout);
                }
            }
            //==== replace the read more with given markup or theme function or default END
            return $fout;
        }

        $content = '';
        if ($margs['content'] != '') {
            $content = $margs['content'];
        } else {
            if ($margs['striptags'] != true) {
                $content = $po->post_content;
            } else {
                $content = strip_tags($po->post_content);
                ;
            }
        }


        $maxlen = intval($margs['maxlen']);
        if ($margs['stripshortcodes'] === true) {
            if(function_exists('strip_shortcodes')){

                $excerpt = strip_shortcodes(stripslashes($excerpt));
            }
        }

        if (strlen($content) > $maxlen) {
            //===if the content is longer then the max limit
//            echo 'initial content - '.$content. ' ||| '. $maxlen .' ||| ';
            $excerpt.=substr($content, 0, $maxlen);


//            echo 'initial excerpt - '.$excerpt;

            if ($margs['striptags'] != true && $margs['try_to_close_unclosed_tags']) {


//                echo 'leeen - '.strpos($excerpt, '</').' '.strlen($excerpt).' '.substr($excerpt,0,strlen($excerpt)-2);
                if(strpos($excerpt, '<')===strlen($excerpt)-1){

                    $excerpt = substr($excerpt,0,strlen($excerpt)-1);
                }
                if(strpos($excerpt, '</')===strlen($excerpt)-2){

                    $excerpt = substr($excerpt,0,strlen($excerpt)-2);
                }
                if(strpos($excerpt, '</p')===strlen($excerpt)-3){

                    $excerpt = substr($excerpt,0,strlen($excerpt)-3);
                }

                if(class_exists('DOMDocument')){
                    $doc = new DOMDocument();
                    @$doc->loadHTML($excerpt);

                    $aux_body_html = '';


                    $children = $doc->childNodes;
                    $scriptTags = $doc->getElementsByTagName('script');


                    foreach ($scriptTags as $script) {
                        if ($script->childNodes->length && $script->firstChild->nodeType == 4) {
                            $cdata = $script->removeChild($script->firstChild);
                            $text = $doc->createTextNode($cdata->nodeValue);
                            $script->appendChild($text);
                        }
                    }

                    foreach ($children as $child) {
//                        print_r($child);
//                        echo $child->ownerDocument->saveXML( $child );
                        $aux_body_html .= $child->ownerDocument->saveXML($child);
                    }


                    $aux_body_html = str_replace('<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd"><html><body>','',$aux_body_html);
                    $aux_body_html = str_replace('</body></html>','',$aux_body_html);

                    $aux_body_html = str_replace(array('<![CDATA['),'',$aux_body_html);
                    $aux_body_html = str_replace(array('&#13;'),'',$aux_body_html);
//                    echo 'final excerpt - '.$aux_body_html;
                }
            }

//            echo 'initialz excerpt - '.$excerpt.' ||| '.$margs['striptags'].' ||| ';

            if ($margs['striptags'] == true) {
                $excerpt = strip_tags($excerpt);
            }
//            echo 'final excerpt - '.$excerpt;
            if ($margs['stripshortcodes'] == false && function_exists('do_shortcode')) {
                $excerpt = do_shortcode(stripslashes($excerpt));
            }

            $fout.=$excerpt;
            if ($margs['readmore'] == 'auto' || $margs['readmore'] == 'on') {
                $fout .= '{readmore}';
            }
        } else {
            //===if the content is not longer then the max limit just add the content
            $fout.=$content;
            if ($margs['readmore'] == 'on') {
                $fout .= '{readmore}';
            }
        }

//        echo $fout.' <-- fout';
        //==== replace the read more with given markup or theme function or default
        if ($margs['readmore_markup'] != '') {
            $fout = str_replace('{readmore}', $margs['readmore_markup'], $fout);
        } else {
            if (function_exists('continue_reading_link')) {
                $fout = str_replace('{readmore}', continue_reading_link($pid), $fout);
            } else {
                if(function_exists('get_permalink')){
                    $fout = str_replace('{readmore}', '<div class="readmore-con"><a href="' . get_permalink($pid) . '">' . __('read more') . ' &raquo;</a></div>', $fout);
                }
                
            }
        }


//        echo ' final fout -- '. $fout;
        //==== replace the read more with given markup or theme function or default END
        return $fout;
    }

}


if (!function_exists('dzs_print_menu')) {

    function dzs_print_menu() {
        $args = array('menu' => 'mainnav', 'menu_class' => 'menu sf-menu', 'container' => false, 'theme_location' => 'primary', 'echo' => '0');
        $aux = wp_nav_menu($args);
        $aux = preg_replace('/<ul>/', '<ul class="sf-menu">', $aux, 1);
        if (preg_match('/<div class="sf-menu">/', $aux)) {
            $aux = preg_replace('/<div class="sf-menu">/', '', $aux, 1);
            $aux = $rest = substr($aux, 0, -7);
        }
        // $aux_char = '/';
        //$aux = preg_replace('/<div>/', '', $aux, 1);
        print_r($aux);
    }

}
if (!function_exists('dzs_post_date')) {

    function dzs_post_date($pid) {
        $po = get_post($pid);
        //print_r($po);
        if ($po) {
            echo mysql2date('l M jS, Y', $po->post_date);
        }
    }

}


if (!function_exists('dzs_pagination')) {

    function dzs_pagination($pages = '', $range = 2) {
        global $paged;
        $fout = '';
        $showitems = ($range * 2) + 1;

        if (empty($paged))
            $paged = 1;

        if ($pages == '') {
            global $wp_query;
            $pages = $wp_query->max_num_pages;
            if (!$pages) {
                $pages = 1;
            }
        }

        if (1 != $pages) {
            $fout.= "<div class='dzs-pagination'>";
            if ($paged > 2 && $paged > $range + 1 && $showitems < $pages)
                $fout.= "<a href='" . get_pagenum_link(1) . "'>&laquo;</a>";
            if ($paged > 1 && $showitems < $pages)
                $fout.= "<a href='" . get_pagenum_link($paged - 1) . "'>&lsaquo;</a>";

            for ($i = 1; $i <= $pages; $i++) {
                if (1 != $pages && (!($i >= $paged + $range + 1 || $i <= $paged - $range - 1) || $pages <= $showitems )) {
                    $fout.= ( $paged == $i) ? "<span class='current'>" . $i . "</span>" : "<a href='" . get_pagenum_link($i) . "' class='inactive' >" . $i . "</a>";
                }
            }

            if ($paged < $pages && $showitems < $pages)
                $fout.= "<a href='" . get_pagenum_link($paged + 1) . "'>&rsaquo;</a>";
            if ($paged < $pages - 1 && $paged + $range - 1 < $pages && $showitems < $pages)
                $fout.= "<a href='" . get_pagenum_link($pages) . "'>&raquo;</a>";
            $fout.= '<div class="clearfix"></div>';
            $fout.= "</div>
                ";
        }
        return $fout;
    }

}




if (!function_exists('dzs_curr_url')) {

    function dzs_curr_url($pargs=array()) {

        $margs = array(

            'get_page_url_too'=>true,
            'get_script_name'=>false,
        );


        if($pargs){
            $margs = array_merge($margs,$pargs);
        }

//        print_r($margs); print_r($pargs);

        $page_url = '';
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $page_url .= "https://";
        } else {
            $page_url = 'http://';
        }


        $request_uri = $_SERVER["REQUEST_URI"];

        if($margs['get_script_name']){

            if($_SERVER['SCRIPT_NAME']){
                $request_uri = $_SERVER['SCRIPT_NAME'];
            }
        }

        if ($_SERVER["SERVER_PORT"] != "80") {
            $page_url .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $request_uri;
        } else {
            $page_url .= $_SERVER["SERVER_NAME"] . $request_uri;
        }

        if($margs['get_page_url_too']===false){
            $aux_arr = explode('/',$page_url);

//            print_r($aux_arr);

            $page_url = '';
            for($i=0;$i<count($aux_arr)-1;$i++){
                $page_url.=$aux_arr[$i].'/';
            }
        }

//        print_r($_SERVER);


        return $page_url;
    }

}

//print_r($_SERVER);
//echo dzs_curr_url();


if (!function_exists('dzs_addAttr')) {

    function dzs_addAttr($arg1, $arg2) {
        $fout = '';
        //$arg2 = str_replace('\\', '', $arg2);
        if (isset($arg2) && $arg2 != "undefined" && $arg2 != '')
            $fout.= ' ' . $arg1 . "='" . $arg2 . "' ";
        return $fout;
    }

}


if(!function_exists('dzs_addSwfAttr')){
    function dzs_addSwfAttr($arg1, $arg2, $first=false) {
        $fout='';
        //$arg2 = str_replace('\\', '', $arg2);

        //sanitaze for object input
        $lb   = array('"' ,"\r\n", "\n", "\r", "&", "`", '???', "'");
        $arg2 = str_replace(' ', '%20', $arg2);
        //$arg2 = str_replace('<', '', $arg2);
        $arg2 = str_replace($lb, '', $arg2);

        if (isset ($arg2)  && $arg2 != "undefined" && $arg2 != ''){
            if($first==false){
                $fout.='&amp;';
            }
            $fout.= $arg1 . "=" . $arg2 . "";
        }
        return $fout;
    }
}


if (!function_exists('dzs_clean')) {

    function dzs_clean($var) {
        if (!function_exists('sanitize_text_field')) {
            return $var;
        } else {
            return sanitize_text_field($var);
        }
    }

}

if (!class_exists('DZSHelpers')) {

    class DZSHelpers {

        static function get_contents($url, $pargs = array()) {
            $margs = array(
                'force_file_get_contents' => 'off',
            );
            $margs = array_merge($margs, $pargs);
            if (function_exists('curl_init') && $margs['force_file_get_contents'] == 'off') { // if cURL is available, use it...
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                $cache = curl_exec($ch);
                curl_close($ch);
            } else {

                $ctx = stream_context_create(array(
                    'http'=>array(
                        'timeout' => 15,
                    )
                ));

                echo file_get_contents($url, false, $ctx);
            }
            return $cache;
        }

        static function replace_in_matrix($arg1, $arg2, &$argarray) {
            foreach ($argarray as &$newi) {
                //print_r($newi);
                if (is_array($newi)) {
                    foreach ($newi as &$newj) {
                        if (is_array($newj)) {
                            foreach ($newj as &$newk) {
                                if (!is_array($newk)) {
                                    $newk = str_replace($arg1, $arg2, $newk);
                                }
                            }
                        } else {
                            $newj = str_replace($arg1, $arg2, $newj);
                        }
                    }
                } else {
                    $newi = str_replace($arg1, $arg2, $newi);
                }
            }
        }

        static function remove_wpautop( $content, $autop = false ) {

            if ($autop && function_exists('wpautop')){
                $content = wpautop( preg_replace( '/<\/?p\>/', "\n", $content ) . "\n" );
            }
            if(function_exists('shortcode_unautop')){
                return do_shortcode( shortcode_unautop( $content) );
            }else{
                return $content;
            }
            
        }

        static function wp_savemeta($id, $arg2, $arg3 = '') {
            //echo htmlentities($_POST[$arg2]);
            if ($arg3 == 'html') {
                update_post_meta($id, $arg2, htmlentities($_POST[$arg2]));
                return;
            }


            if (isset($_POST[$arg2]))
                update_post_meta($id, $arg2, esc_attr(strip_tags($_POST[$arg2])));
            else
            if ($arg3 == 'checkbox')
                update_post_meta($id, $arg2, "off");
        }

        static function wp_get_excerpt($pid = 0, $pargs = array()) {
//            print_r($pargs);
            global $post;
            $fout = '';
            $excerpt = '';
            if ($pid == 0) {
                $pid = $post->ID;
            } else {
                $pid = $pid;
            }

//            echo $pid;
            $po = (get_post($pid));

            $margs = array(
                'maxlen' => 400
            , 'striptags' => false
            , 'stripshortcodes' => false
            , 'forceexcerpt' => false //if set to true will ignore the manual post excerpt
            , 'aftercutcontent_html' => '' // you can put here something like [..]
            , 'readmore' => 'auto'
            , 'readmore_markup' => ''
            , 'content' => '' // forced content
            );
            $margs = array_merge($margs, $pargs);

            if ($margs['content'] != '') {
                $margs['readmore'] = 'off';
                $margs['forceexcerpt'] = true;
            }


            $margs['readmore_markup'] = str_replace("{{theid}}", $pid, $margs['readmore_markup']);
            $margs['readmore_markup'] = str_replace("{{thepostpermalink}}", get_the_permalink($pid), $margs['readmore_markup']);





//                print_r($margs);

            if ($po->post_excerpt != '' && $margs['forceexcerpt'] == false) {
                $fout = do_shortcode($po->post_excerpt);


                //==== replace the read more with given markup or theme function or default
                if ($margs['readmore_markup'] != '') {
                    $fout = str_replace('{readmore}', $margs['readmore_markup'], $fout);
                } else {
                    if (function_exists('continue_reading_link')) {
                        $fout = str_replace('{readmore}', continue_reading_link($pid), $fout);
                    } else {
                        if (function_exists('dzs_excerpt_read_more')) {
                            $fout = str_replace('{readmore}', dzs_excerpt_read_more($pid), $fout);
                        } else {
                            //===maybe in the original function you can parse readmore
                            //$fout = str_replace('{readmore}', '<div class="readmore-con"><a href="' . get_permalink($pid) . '">' . __('read more') . ' &raquo;</a></div>', $fout);
                        }
                    }
                }
                //==== replace the read more with given markup or theme function or default END
                return $fout;
            }



            $content = '';
            if ($margs['content'] != '') {
                $content = $margs['content'];
            } else {
                if ($margs['striptags'] == false) {
                    if ($margs['stripshortcodes'] == false) {
                        $content = do_shortcode($po->post_content);
                    }else{
                        $content = $po->post_content;
                    }

                } else {
//                    echo 'pastcontent'.$content;
                    $content = strip_tags($po->post_content);
//                    echo 'nowcontent'.$content;
                }
            }

//            echo 'nowcontent'.$content.'/nowcontent';

            $maxlen = intval($margs['maxlen']);

//            echo 'maxlen'.$maxlen;

            if (strlen($content) > $maxlen) {
                //===if the content is longer then the max limit
                $excerpt.=substr($content, 0, $maxlen);

                if ($margs['striptags'] == true) {
                    $excerpt = strip_tags($excerpt);
                    //echo $excerpt;
                }
                if ($margs['stripshortcodes'] == false) {
                    $excerpt = do_shortcode(stripslashes($excerpt));
                } else {
                    $excerpt = strip_shortcodes(stripslashes($excerpt));
                    $excerpt = str_replace('[/one_half]', '', $excerpt);
                    $excerpt = str_replace("\n", " ", $excerpt);
                    $excerpt = str_replace("\r", " ", $excerpt);
                    $excerpt = str_replace("\t", " ", $excerpt);
                }

                $fout.=$excerpt.$margs['aftercutcontent_html'];
                if ($margs['readmore'] == 'auto') {
                    $fout .= '{readmore}';
                }
            } else {
                //===if the content is not longer then the max limit just add the content
                $fout.=$content;
                if ($margs['readmore'] == 'on') {
                    $fout .= '{readmore}';
                }
            }

            //==== replace the read more with given markup or theme function or default
            if ($margs['readmore_markup'] != '') {
                $fout = str_replace('{readmore}', $margs['readmore_markup'], $fout);
            } else {
                if (function_exists('continue_reading_link')) {
                    $fout = str_replace('{readmore}', continue_reading_link($pid), $fout);
                } else {
                    if (function_exists('dzs_excerpt_read_more')) {
                        $fout = str_replace('{readmore}', dzs_excerpt_read_more($pid), $fout);
                    } else {
                        //===maybe in the original function you can parse readmore
                        //$fout = str_replace('{readmore}', '<div class="readmore-con"><a href="' . get_permalink($pid) . '">' . __('read more') . ' &raquo;</a></div>', $fout);
                    }
                }
            }
            //echo $fout;
            //==== replace the read more with given markup or theme function or default END
            return $fout;
        }

        static function generate_input_text($argname, $otherargs = array()) {
            $fout = '';

            $margs = array(
                'class' => '',
                'val' => '', // === default value
                'seekval' => '', // ===the value to be seeked
                'type' => '',
                'extraattr'=>'',
                'input_type'=>'text',
            );
            $margs = array_merge($margs, $otherargs);

            $fout.='<input type="'.$margs['input_type'].'"';
            $fout.=' name="' . $argname . '"';


            if ($margs['type'] == 'colorpicker') {
                $margs['class'].=' with_colorpicker';
            }



            if ($margs['class'] != '') {
                $fout.=' class="' . $margs['class'] . '"';
            }
            if (isset($margs['seekval']) && $margs['seekval'] != '') {
                //echo $argval;
                $fout.=' value="' . $margs['seekval'] . '"';
            } else {
                $fout.=' value="' . $margs['val'] . '"';
            }
            
            
            if ($margs['extraattr'] != '') {
                $fout.='' . $margs['extraattr'] . '';
            }
            
            $fout.='/>';



            //print_r($args); print_r($otherargs);
            if ($margs['type'] == 'slider') {
                $fout.='<div id="' . $argname . '_slider" style="width:200px;"></div>';
                $fout.='<script>
jQuery(document).ready(function($){
$( "#' . $argname . '_slider" ).slider({
range: "max",
min: 8,
max: 72,
value: 15,
stop: function( event, ui ) {
//console.log($( "*[name=' . $argname . ']" ));
$( "*[name=' . $argname . ']" ).val( ui.value );
$( "*[name=' . $argname . ']" ).trigger( "change" );
}
});
});</script>';
            }
            if ($margs['type'] == 'colorpicker') {
                $fout.='<div class="picker-con"><div class="the-icon"></div><div class="picker"></div></div>';
                $fout.='<script>
jQuery(document).ready(function($){
jQuery(".with_colorpicker").each(function(){
        var _t = jQuery(this);
        if(_t.hasClass("treated")){
            return;
        }
        if(jQuery.fn.farbtastic){
        //console.log(_t);
        _t.next().find(".picker").farbtastic(_t);
            
        }else{ if(window.console){ console.info("declare farbtastic..."); } };
        _t.addClass("treated");

        _t.bind("change", function(){
            //console.log(_t);
            jQuery("#customstyle_body").html("body{ background-color:" + $("input[name=color_bg]").val() + "} .dzsportfolio, .dzsportfolio a{ color:" + $("input[name=color_main]").val() + "} .dzsportfolio .portitem:hover .the-title, .dzsportfolio .selector-con .categories .a-category.active { color:" + $("input[name=color_high]").val() + " }");
        });
        _t.trigger("change");
        _t.bind("click", function(){
            if(_t.next().hasClass("picker-con")){
                _t.next().find(".the-icon").eq(0).trigger("click");
            }
        })
    });
});</script>';
            }

            return $fout;
        }

        static function generate_input_checkbox($argname, $argopts) {
            $fout = '';
            $auxtype = 'checkbox';

            if (isset($argopts['type'])) {
                if ($argopts['type'] == 'radio') {
                    $auxtype = 'radio';
                }
            }
            $fout.='<input type="' . $auxtype . '"';
            $fout.=' name="' . $argname . '"';
            if (isset($argopts['class'])) {
                $fout.=' class="' . $argopts['class'] . '"';
            }

            if (isset($argopts['id'])) {
                $fout.=' id="' . $argopts['id'] . '"';
            }
            $theval = 'on';
            if (isset($argopts['val'])) {
                $fout.=' value="' . $argopts['val'] . '"';
                $theval = $argopts['val'];
            } else {
                $fout.=' value="on"';
            }
            //print_r($this->mainoptions); print_r($argopts['seekval']);
            if (isset($argopts['seekval'])) {
                $auxsw = false;
                if (is_array($argopts['seekval'])) {
                    //echo 'ceva'; print_r($argopts['seekval']);
                    foreach ($argopts['seekval'] as $opt) {
                        //echo 'ceva'; echo $opt; echo 
                        if ($opt == $argopts['val']) {
                            $auxsw = true;
                        }
                    }
                } else {
                    //echo $argopts['seekval']; echo $theval;
                    if ($argopts['seekval'] == $theval) {
                        //echo $argval;
                        $auxsw = true;
                    }
                }
                if ($auxsw == true) {
                    $fout.=' checked="checked"';
                }
            }
            $fout.='/>';
            return $fout;
        }

        static function generate_input_textarea($argname, $otherargs = array()) {
            $fout = '';
            $fout.='<textarea';
            $fout.=' name="' . $argname . '"';

            $margs = array(
                'class' => '',
                'val' => '', // === default value
                'seekval' => '', // ===the value to be seeked
                'type' => '',
                'extraattr'=>'',
            );
            $margs = array_merge($margs, $otherargs);



            if ($margs['class'] != '') {
                $fout.=' class="' . $margs['class'] . '"';
            }
            if ($margs['extraattr'] != '') {
                $fout.='' . $margs['extraattr'] . '';
            }
            $fout.='>';
            if (isset($margs['seekval']) && $margs['seekval'] != '') {
                $fout.='' . $margs['seekval'] . '';
            } else {
                $fout.='' . $margs['val'] . '';
            }
            $fout.='</textarea>';

            return $fout;
        }
        static function generate_select($argname, $pargopts) {
            //-- DZSHelpers::generate_select('label', array('options' => array('peritem','off', 'on'), 'class' => 'styleme', 'seekval' => $this->mainoptions[$lab]));
            
            $fout = '';
            $auxtype = 'select';

            if($pargopts==false){
                $pargopts = array();
            }
            
            $margs = array(
                'options' => array(),
                'class' => '',
                'seekval' => '',
                'extraattr'=>'',
            );

            $margs = array_merge($margs, $pargopts);

            $fout.='<select';
            $fout.=' name="' . $argname . '"';
            if (isset($margs['class'])) {
                $fout.=' class="'.$margs['class'].'"';
            }
            if ($margs['extraattr'] != '') {
                $fout.='' . $margs['extraattr'] . '';
            }
            
            $fout.='>';
            
            //print_r($margs['options']);

            foreach ($margs['options'] as $opt) {
                $val = '';
                $lab = '';



                if (is_array($opt) && isset($opt['lab']) && isset($opt['val'])) {
                    $val = $opt['val'];
                    $lab = $opt['lab'];
                } else {
                    if (is_array($opt) && isset($opt['label']) && isset($opt['value'])) {

                        $val = $opt['value'];
                        $lab = $opt['label'];
                    }else{
                        $val = $opt;
                        $lab = $opt;
                    }

                }
                

                $fout.='<option value="' . $val . '"';
                if ($margs['seekval'] != '' && $margs['seekval'] == $val) {
                    $fout.=' selected';
                }

                $fout.='>' . $lab . '</option>';
            }
            $fout.='</select>';
            return $fout;
        }
        static function get_query_arg($url, $key) {
            //-- DZSHelpers::get_query_arg('
//            echo 'ceva';
            if(strpos($url, $key)!==false){
                //faconsole.log('testtt');

//                $pattern = '/[?&]'.$key.'=(.+)/';
                $pattern = '/[?&]'.$key.'=(.+?)(?=&|$)/';
                preg_match($pattern, $url, $matches);

//                print_r($matches);
                if($matches && $matches[1]){
                    return $matches[1];
                }
                //$('.zoombox').eq
            }
        }




        static function safe_add_query_arg()
        {

            $args       = func_get_args();
            $total_args = count( $args );
            $uri        = $_SERVER['REQUEST_URI'];

            if( 3 <= $total_args ){
                $uri = add_query_arg( $args[0], $args[1], $args[2] );
            }
            elseif( 2 == $total_args ){
                $uri = add_query_arg( $args[0], $args[1] );
            }
            elseif( 1 == $total_args ){
                $uri = add_query_arg( $args[0] );
            }

            if(function_exists('esc_url')){

                return esc_url( $uri );
            }else{
                return $uri;
            }
        }
        static function add_query_arg()
        {
            $ret = '';
            $args = func_get_args();
            if (is_array($args[0])) {
                if (count($args) < 2 || false === $args[1])
                    $uri = $_SERVER['REQUEST_URI'];
                else
                    $uri = $args[1];
            } else {
                if (count($args) < 3 || false === $args[2])
                    $uri = $_SERVER['REQUEST_URI'];
                else
                    $uri = $args[2];
            }

            if ($frag = strstr($uri, '#'))
                $uri = substr($uri, 0, -strlen($frag));
            else
                $frag = '';

            if (0 === stripos($uri, 'http://')) {
                $protocol = 'http://';
                $uri = substr($uri, 7);
            } elseif (0 === stripos($uri, 'https://')) {
                $protocol = 'https://';
                $uri = substr($uri, 8);
            } else {
                $protocol = '';
            }

            if (strpos($uri, '?') !== false) {
                list($base, $query) = explode('?', $uri, 2);
                $base .= '?';
            } elseif ($protocol || strpos($uri, '=') === false) {
                $base = $uri . '?';
                $query = '';
            } else {
                $base = '';
                $query = $uri;
            }

            DZSHelpers::wp_parse_str($query, $qs);
            $qs = DZSHelpers::urlencode_deep($qs); // this re-URL-encodes things that were already in the query string
            if (is_array($args[0])) {
                $kayvees = $args[0];
                $qs = array_merge($qs, $kayvees);
            } else {
                $qs[$args[0]] = $args[1];
            }

            foreach ($qs as $k => $v) {
                if ($v === false)
                    unset($qs[$k]);
            }

            $ret = DZSHelpers::build_query($qs);
            $ret = trim($ret, '?');
            $ret = preg_replace('#=(&|$)#', '$1', $ret);
            $ret = $protocol . $base . $ret . $frag;
            $ret = rtrim($ret, '?');
            return $ret;
        }


        static function remove_query_arg($key, $query = false)
        {
            if (is_array($key)) { // removing multiple keys
                foreach ($key as $k)
                    $query = DZSHelpers::add_query_arg($k, false, $query);
                return $query;
            }
            return DZSHelpers::add_query_arg($key, false, $query);
        }


        static function stripslashes_deep($value)
        {
            if (is_array($value)) {
                $value = array_map('stripslashes_deep', $value);
            } elseif (is_object($value)) {
                $vars = get_object_vars($value);
                foreach ($vars as $key => $data) {
                    $value->{$key} = DZSHelpers::stripslashes_deep($data);
                }
            } elseif (is_string($value)) {
                $value = stripslashes($value);
            }

            return $value;
        }

        static function wp_parse_str($string, &$array)
        {
            parse_str($string, $array);
            if (function_exists('stripslashes_deep')) {
                if (get_magic_quotes_gpc()) {
                    $array = DZSHelpers::stripslashes_deep($array);
                }
            }


            /**
             * Filter the array of variables derived from a parsed string.
             *
             * @since 2.3.0
             *
             * @param array $array The array populated with variables.
             */
            $array = DZSHelpers::apply_filters('wp_parse_str', $array);
        }


        static function apply_filters($tag, $value)
        {
            global $wp_filter, $merged_filters, $wp_current_filter;

            $args = array();

            // Do 'all' actions first
            if (isset($wp_filter['all'])) {
                $wp_current_filter[] = $tag;
                $args = func_get_args();
//                _wp_call_all_hook($args);
            }

            if (!isset($wp_filter[$tag])) {
                if (isset($wp_filter['all']))
                    array_pop($wp_current_filter);
                return $value;
            }

            if (!isset($wp_filter['all']))
                $wp_current_filter[] = $tag;

            // Sort
            if (!isset($merged_filters[$tag])) {
                ksort($wp_filter[$tag]);
                $merged_filters[$tag] = true;
            }

            reset($wp_filter[$tag]);

            if (empty($args))
                $args = func_get_args();

            do {
                foreach ((array)current($wp_filter[$tag]) as $the_)
                    if (!is_null($the_['function'])) {
                        $args[1] = $value;
                        $value = call_user_func_array($the_['function'], array_slice($args, 1, (int)$the_['accepted_args']));
                    }

            } while (next($wp_filter[$tag]) !== false);

            array_pop($wp_current_filter);

            return $value;
        }

        /**
         * Navigates through an array and encodes the values to be used in a URL.
         *
         *
         * @since 2.2.0
         *
         * @param array|string $value The array or string to be encoded.
         * @return array|string $value The encoded array (or string from the callback).
         */
        static function urlencode_deep($value)
        {
            $value = is_array($value) ? array_map('DZSHelpers::urlencode_deep', $value) : urlencode($value);
            return $value;
        }

        static function build_query($data)
        {
            return DZSHelpers::_http_build_query($data, null, '&', '', false);
        }

        static function _http_build_query($data, $prefix = null, $sep = null, $key = '', $urlencode = true)
        {
            $ret = array();

            foreach ((array)$data as $k => $v) {
                if ($urlencode)
                    $k = urlencode($k);
                if (is_int($k) && $prefix != null)
                    $k = $prefix . $k;
                if (!empty($key))
                    $k = $key . '%5B' . $k . '%5D';
                if ($v === null)
                    continue;
                elseif ($v === FALSE)
                    $v = '0';

                if (is_array($v) || is_object($v))
                    array_push($ret, DZSHelpers::_http_build_query($v, '', $sep, $k, $urlencode));
                elseif ($urlencode)
                    array_push($ret, $k . '=' . urlencode($v));
                else
                    array_push($ret, $k . '=' . $v);
            }

            if (null === $sep)
                $sep = ini_get('arg_separator.output');

            return implode($sep, $ret);
        }
        static function transform_to_str_size($arg) {
            //-- DZSHelpers::transform_to_str_size(400%);
            $fout = $arg;
            if(strpos($arg,'auto')!==false || strpos($arg,'%')!==false){

            }else{
                $fout.='px';
            }
            return $fout;
        }

    }

}