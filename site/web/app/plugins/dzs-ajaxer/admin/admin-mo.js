
function dzsajx_mo_ready($){

    jQuery('.saveconfirmer').fadeOut('slow');
    jQuery(document).delegate(".picker-con .the-icon", "click", function(){
        var _t = jQuery(this);
        var _c = _t.parent().children('.picker');
        if(_c.css('display')=='none'){
            _c.fadeIn('fast');
        }else{
            _c.fadeOut('fast');
        };
    });
	
        


    if(window.dzsajx_theme_settings){
        var _t =jQuery('.install-default-settings-for-theme');

        _t.html(_t.attr('data-starttext')+ window.dzsajx_theme_settings.theme_nicename);
        
        _t.show();

    }






	setTimeout(reskin_select, 10);

    jQuery(document).delegate('.install-default-settings-for-theme','click',handle_mouse);

    jQuery('#main-ajax-loading').css('visibility', 'hidden');

    jQuery('.save-mainoptions').unbind('click');
    jQuery('.save-mainoptions').bind('click', mo_saveall);


    function handle_mouse(e){
        var _t = $(this);


        if(e.type=='click'){
            if(_t.hasClass('install-default-settings-for-theme')){
                console.info(_t);


                if(window.dzsajx_theme_settings){
                    for(var lab in window.dzsajx_theme_settings){
                        console.info(lab);

                        var val =  window.dzsajx_theme_settings[lab];
                        if(lab=='menu_move_on_top_of_content' || lab=='scripts_reinit_document_ready'){


                            if(val=='on'){

                                // console.info(lab, $('*[name="'+lab+'"]'));
                                $('*[name="'+lab+'"]').prop('checked',true);
                            }
                            if(val=='off'){
                                $('*[name="'+lab+'"]').prop('checked',false);
                            }

                        }else{

                            $('*[name="'+lab+'"]').val(val);
                        }

                        $('*[name="'+lab+'"]').trigger('change');

                    }
                }
                $('.save-mainoptions').trigger('click');


                return false;
            }
        }
    }
}

function mo_saveall(){
    jQuery('#save-ajax-loading').css('visibility', 'visible');
    var mainarray = jQuery('.mainsettings').serialize();
    var data = {
        action: 'dzsajx_ajax_mo',
        postdata: mainarray
    };
    jQuery('.saveconfirmer').html('Options saved.');
    jQuery('.saveconfirmer').fadeIn('fast').delay(2000).fadeOut('fast');
    jQuery.post(ajaxurl, data, function(response) {
        if(window.console !=undefined ){
            console.log('Got this from the server: ' + response);
        }
        jQuery('#save-ajax-loading').css('visibility', 'hidden');
    });

    return false;
}

jQuery(document).ready(function($){
    reskin_select();
})


function reskin_select(){
    for(i=0;i<jQuery('select').length;i++){
        var _cache = jQuery('select').eq(i);
        //console.log(_cache.parent().attr('class'));
		
        if(_cache.hasClass('styleme')==false || _cache.parent().hasClass('select_wrapper') || _cache.parent().hasClass('select-wrapper')){
            continue;
        }
        var sel = (_cache.find(':selected'));
        _cache.wrap('<div class="select-wrapper"></div>')
        _cache.parent().prepend('<span>' + sel.text() + '</span>')
    }
    jQuery(document).undelegate(".select-wrapper select", "change");
    jQuery(document).delegate(".select-wrapper select", "change",  change_select);
        

    function change_select(){
        var selval = (jQuery(this).find(':selected').text());
        jQuery(this).parent().children('span').text(selval);
    }

}