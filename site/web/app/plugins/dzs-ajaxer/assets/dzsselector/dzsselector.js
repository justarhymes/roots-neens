

(function($) {


    $.fn.dzsselector = function(o) {

        //==default options
        var defaults = {
            'opener': ''

        };

//        console.info(this, o);

        if (typeof o == 'undefined') {
            if (typeof $(this).attr('data-options') != 'undefined' && $(this).attr('data-options') != '') {
                var aux = $(this).attr('data-options');
                aux = 'var aux_opts = ' + aux;
                eval(aux);
                o = aux_opts;
            }
        }
        o = $.extend(defaults, o);
        this.each(function () {
            var _t = $(this);
            var _t_class = String(_t.attr('class'));
            var _theSelect = null;
            var _feeder = null

            var _wrapper = null;



            _t_class = _t_class.replace('dzs-style-me', '');

            if(_t.hasClass('opener-listbuttons')){
                o.opener = 'opener-listbuttons';
            }
            if(_t.hasClass('opener-bigoptions')){
                o.opener = 'opener-bigoptions';
            }
            if(_t.hasClass('opener-radio')){
                o.opener = 'opener-radio';
            }


            //console.info(o);


            if(_t.next().hasClass('dzs-style-me-feeder')){
                _feeder = _t.next();
            }
            //console.info(_t);

            if(_t.hasClass('treated') || _t.parent().hasClass('dzs-select-wrapper')){

                if(_t.hasClass('skin-justvisible')){

                    _wrapper = _t.parent();
                    _theSelect = _wrapper.find('select').eq(0);

                    if(_wrapper){
                        _wrapper.find('.dzs-select-wrapper-head').unbind('click');
                        _wrapper.find('.dzs-select-wrapper-head').bind('click', function(){
                            //console.info(_theSelect);
                            //_theSelect.click(function(){
                            //    //do stuff here
                            //});
                            //_theSelect.click();

                            openSelect(_theSelect);
                        })
                    }
                }

                return false;
            }
            //console.info(_t);
            //_t.addClass('treated');

            var str_w = '';

            if(_t.get(0).style && _t.get(0).style.width!='' && !isNaN(parseInt(_t.get(0).style.width,10))){
                str_w = 'width: '+parseInt(_t.get(0).style.width,10)+'px';
            }

            _t.wrap('<span class="dzs-select-wrapper '+_t_class+'" style="'+str_w+'"></span>');

            _wrapper = _t.parent();

            _wrapper.prepend('<span class="dzs-select-wrapper-head"><span class="the-text">'+_t.val()+'</span></span>');

            if(_t.get(0) && _t.get(0).nodeName=='UL'){
                var aux3 = '<select name="'+_t.attr('data-name')+'">';

                for(i=0;i<_t.children().length;i++){
                    aux3+='<option value="'+_t.children().eq(i).attr('data-value')+'">'+_t.children().eq(i).attr('data-value')+'</option>';
                }

                aux3+='</select>';
                _t.after(aux3);
            }

            _theSelect = _t.parent().find('select').eq(0);

            _theSelect.bind('change', function(){

                //console.info(_t.parent().find('.dzs-select-wrapper-head .the-text'));
                _t.parent().find('.dzs-select-wrapper-head .the-text').html($(this).find(':selected').html());
            })

            _theSelect.trigger('change');

            _theSelect.bind('focus', function(){

                if(_wrapper.hasClass('skin-beige')){
                    _wrapper.addClass('select-focused');
                }
            })

            _theSelect.bind('focusout', function(){
                _wrapper.removeClass('select-focused');

            })



            if(String(_t_class).indexOf('opener-')>-1){
                _t.parent().bind('click',function(){
                    var _t2 = $(this);
                    //console.info(_t2);

                    if(_t2.hasClass('active-animation')){

                        _t2.removeClass('active-animation');
                        setTimeout(function(){
                            _t2.removeClass('active');
                        },500);
                    }else{
                        _t2.addClass('active');
                        setTimeout(function(){
                            _t2.addClass('active-animation');
                        },50);
                    }

                    setTimeout(function(){

                        var auxoptions = {
                            columnWidth: 1,
                            itemSelector: '.masonry-gallery--item'
                        };

                        //console.log( _t.parent().find('.dzslayouter .items-con'));
                        if($.fn.masonry){

                            _t.parent().find('.dzslayouter .items-con').masonry(auxoptions);
                        }
                    },500)

                })
            }else{

                //console.info(_wrapper);
                if(_wrapper){
                    _wrapper.find('.dzs-select-wrapper-head').bind('click', function(){
                        //console.info(_theSelect);
                        //_theSelect.click(function(){
                        //    //do stuff here
                        //});
                        //_theSelect.click();

                        openSelect(_theSelect);
                    })
                }
            }


            //console.log(_wrapper);
            _wrapper.addClass('init-readyall')

            if(_t.parent().hasClass('opener-bigoptions') || o.opener == ('opener-listbuttons') || o.opener == ('opener-radio')){

                var aux = '<span class="'+ String(o.opener)+'-wrap">';

                if(o.opener=='opener-bigoptions'){
                    aux+='<span>';
                }
                //console.info(o, o.opener,aux);

                var selectedind = 0;
                for(i=0;i<_t.children().length;i++){
                    var _c = _t.children().eq(i);
                    if(_c.prop('selected')==true){
                        selectedind = i;
                    }
                }

                if(_feeder){
                    for(i=0;i<_feeder.children().length;i++){
                        //console.log(_t.children().eq(i));
                        aux+='<span class="bigoption select-option">'+_feeder.children().eq(i).html();

                        if(o.opener=='opener-radio'){
                            aux+='<span class="small-bubble"></span>';
                        }

                        aux+='</span>';
                    }
                }else{
                    for(i=0;i<_t.children().length;i++){
                        //console.log(_t.children().eq(i));
                        aux+='<span class="bigoption select-option">'+_t.children().eq(i).html()+'</span>';
                    }
                }


                if(o.opener=='opener-bigoptions'){
                    aux+='</span>';
                }

                aux+='</span>';





                _t.parent().append(aux);
                _t.parent().find('.select-option').eq(selectedind).addClass('active');
                _t.parent().find('.select-option').bind('click', function(){
                    var _t2 = $(this);

                    //console.log(_t2);

                    _t2.parent().children().removeClass('active');
                    _t2.addClass('active');

                    var ind = _t2.parent().children().index(_t2);

                    _theSelect.children().eq(ind).prop('selected',true);

                    _theSelect.trigger('change');

                })
            }

        });

    };



    window.dzssel_init = function(selector, settings) {
        if(typeof(settings)!="undefined" && typeof(settings.init_each)!="undefined" && settings.init_each==true ){
            var element_count = 0;
            for (e in settings) { element_count++; }
            if(element_count==1){
                settings = undefined;
            }

            $(selector).each(function(){
                var _t = $(this);
                _t.dzsselector(settings)
            });
        }else{
            $(selector).dzsselector(settings);
        }

    };
})(jQuery);

jQuery(document).ready(function($){

    //console.info($('select.dzs-style-me'));
    dzssel_init('select.dzs-style-me', {init_each: true});
});

var openSelect = function(selector){
    var element = jQuery(selector)[0], worked = false;
    if (document.createEvent) { // all browsers
        var e = document.createEvent("MouseEvents");
        e.initMouseEvent("mousedown", true, true, window, 0, 0, 0, 0, 0, false, false, false, false, 0, null);
        worked = element.dispatchEvent(e);
    } else if (element.fireEvent) { // ie
        worked = element.fireEvent("onmousedown");
    }
    if (!worked) { // unknown browser / error
        alert("It didn't worked in your browser.");
    }
}