"use strict";

function dzsajx_get_query_arg(purl, key){
	//console.info(purl);
	if(purl.indexOf(key+'=')>-1){
		//faconsole.log('testtt');
		var regexS = "[?&]"+key + "(.+?)(?=&|$)";
		var regex = new RegExp(regexS);
		var regtest = regex.exec(purl);


		// console.info(regex, regtest);
		if(regtest != null){
			//var splitterS = regtest;


			if(regtest[1]){
				var aux = regtest[1].replace( /=/g, '');
				return aux;
			}else{
				return '';
			}


		}
		//$('.zoombox').eq
	}
}


var readyList = [];
var loadList = [];
// var $ = jQuery.noConflict();
(function(){

// Store a reference to the original ready method.
// 	var originalReadyMethod = jQuery.fn.ready;
//
// // Override jQuery.fn.ready
// 	jQuery.fn.ready = function(){
//
// 		try{
// 		if(arguments.length && arguments.length > 0 && typeof arguments[0] === 'function') {
// 			readyList.push(arguments[0]);
// 		}
//
// // Execute the original method.
// 		originalReadyMethod.apply( this, arguments );
//
// 		}catch(err){
// 			console.info(err);
// 		}
// 		// super(this,argument);
// 	};



	// console.info(jQuery('body').hasClass('dzsajx-enable-reinit'));
	// console.info(jQuery('body').attr('class'));


	var scripts = document.getElementsByTagName( 'script' );
	var thisScriptTag = scripts[ scripts.length - 1 ];

	// console.info(thisScriptTag.src);


	// -- let's see if scripts_reinit_document_ready is set to ON
	if(String(thisScriptTag.src).indexOf('9.012')){
		var originalReady = jQuery.fn.ready;
		var originalLoad = jQuery.fn.load;
		jQuery.fn.extend({
			ready: function() {
				if(arguments.length && arguments.length > 0 && typeof arguments[0] === 'function') {
					readyList.push(arguments[0]);
				}
				// console.log('custom ready');
				return originalReady.apply(this, arguments);
			}
			,load: function() {
				if(arguments.length && arguments.length > 0 && typeof arguments[0] === 'function') {
					loadList.push(arguments[0]);
				}
				// console.log('custom load', arguments[0]);
				return originalLoad.apply(this, arguments);
			}
		});

// Used to trigger all ready events
		jQuery.triggerReady = function() {
			// window.$ = jQuery;
			// window.$ = jQuery.noConflict();

			// console.info(readyList);
			jQuery(document).add('*').off();


			jQuery(readyList).each(function(){this(jQuery);});
		};
		jQuery.triggerLoad = function() {
			// window.$ = jQuery;
			// window.$ = jQuery.noConflict();

			jQuery(loadList).each(function(){
				// console.info(this);
				this();
			});
		};

		setTimeout(function(){
			// console.info(readyList, loadList);
		},2000);
		setTimeout(function(){
			// jQuery.triggerReady();
		},4000);
	}


})();

jQuery(document).ready(function($){

	// $.getReadyList = function() {
	// 	if(this.readyList != null) { this.myreadylist = [].concat(this.readyList); }
	// 	return this.myreadylist;
	// };

	// setTimeout(function(){
	// 	console.info('jquery ready', $.getReadyList());
	// },2000);
	// $ = jQuery;


	// -- ajax vars
	var new_page_html = '';
	var newclass_body = '';
	var currclass_body = '';

	var scripts_loaded_arr = [];
	var scripts_tobeloaded = [];
	var stylesheets_tobeloaded = [];
	var stylesheets_tobeadded = [];
	var scripts_tobeexecuted = [];
	var pages_caches = [];

	var state_curr_menu_items_links = [];
	var extra_items = [];
	var extra_items_tobeadded = [];

	var busy_main_transition = false
		,new_page_not_loaded_yet = false // -- we use this to force load a page even if all scripts are not loaded
		,page_change_ind = 0
		,content_wrapper_transitioned_out = false
		,refresh_menu = false // -- we will need to refresh menu object if it is included in the content element
		;

	var ___response = null;


	var windowhref = ''
		,ajax_site_url = ''
		,curr_html = window.location.href
		,newtitle = ''
		,newbodyclass = ''
		,curr_html_with_clear_cache = false
		,history_first_pushed_state = false // -- check if the first history state has been pushed so we can back to a outer page before the first one
		,has_custom_outside_content_1= false // -- import custom content
		;

	var _theActualNav = null
		,_content = null
		,_contentTransitioning = null
		,_contentContainer = null
		,_dzsajx_styles_con = null
		,_dzsajx_scripts_tbe_con = null
		,_dzsajx_caches_con = null
		,_preloader = null
		,content_selector = 'body'
		,old_href = ''
		,old_class_body = ''
		,scripts_execute_after_ajax_call = 'off'
		;


	var ceva = 'ceva'
		,_html_for_classes = $('html').eq(0)
		,_body = $('body').eq(0)

	// -- ajax vars END


	if(window.dzsajx_settings) {


		if (dzsajx_settings.site_url) {

			ajax_site_url = dzsajx_settings.site_url;
		}
		if (dzsajx_settings.content_container_selector) {

			_content = $(dzsajx_settings.content_container_selector).eq(0);
			content_selector = dzsajx_settings.content_container_selector;
		}
		if (dzsajx_settings.menu_selector) {

			_theActualNav = $(dzsajx_settings.menu_selector);


			if(_content.find(dzsajx_settings.menu_selector).length>0){
				refresh_menu=true;
			}


			if (dzsajx_settings.menu_move_on_top_of_content && dzsajx_settings.menu_move_on_top_of_content=='on') {

				if(_content){

					_content.before(_theActualNav);
				}
			}
		}
		if (dzsajx_settings.transition) {


			_html_for_classes.addClass('dzsajx-transition-'+dzsajx_settings.transition);
			_html_for_classes.addClass('dzsajx-preloader-'+dzsajx_settings.preloader);
		}
		if (dzsajx_settings.scripts_execute_after_ajax_call) {


			scripts_execute_after_ajax_call = dzsajx_settings.scripts_execute_after_ajax_call;
		}




		if(dzsajx_settings.script_call_on_ready){
			try{
				eval(dzsajx_settings.script_call_on_ready)
			}catch(err){
				console.info('eval ready error - ',err, err.stack);
			}
		}


	}

	// console.info(_theActualNav);

	if(_content==null){
		_content = $('body');
	}

	_content.addClass('dzsajx-content');
	// console.info(_content);

	if(window.dzsajx_settings) {
		if (dzsajx_settings.preloader=='bar' || dzsajx_settings.preloader=='bars' || dzsajx_settings.preloader=='custom') {

			var aux ='<div class="dzsajx-preloader dzsajx-preloader-'+dzsajx_settings.preloader+'">';

			if(dzsajx_settings.preloader=='bar'){
				aux+='<div class="the-bar"></div>'
			}
			if(dzsajx_settings.preloader=='bars'){
				aux+='<div id="loading-center-absolute"> <div class="object"></div> <div class="object"></div> <div class="object"></div> <div class="object"></div> <div class="object"></div> <div class="object"></div> <div class="object"></div> <div class="object"></div> <div class="object"></div> <div class="object"></div> </div>'
			}
			if(dzsajx_settings.preloader=='custom'){
				aux+='<div id="loading-center-absolute"> '+dzsajx_settings.custom_preloader_html+' </div>'
			}


			aux+='</div>';
			if(_content.prev().hasClass('dzsajx-preloader')==false){

				_content.before(aux);
			}
			_preloader = $('.dzsajx-preloader').eq(0);
		}
	}


	// console.info(ajax_site_url);

	_body.append('<div class="dzsajx-styles-con"></div>');

	_dzsajx_styles_con = _body.children('.dzsajx-styles-con');

	if(scripts_execute_after_ajax_call=='on'){

		_body.append('<div class="dzsajx-scripts-con"></div>');

		_dzsajx_scripts_tbe_con = _body.children('.dzsajx-scripts-con');
	}

	if(dzsajx_settings.cache_pages=='on'){

		_body.append('<div class="dzsajx-caches-con"></div>');

		_dzsajx_caches_con = _body.children('.dzsajx-caches-con');
	}


	var selecta = 'a:not(.no-ajaxer,.bp-secondary-action,.bp-primary-action';


	if(dzsajx_settings.classes_to_ignore){

		selecta+=','+dzsajx_settings.classes_to_ignore;
	}

	selecta+=')';

	$(document).delegate(selecta, 'click', click_menu_anchor);

	if(dzsajx_settings.comment_form_selector){
		$(document).delegate(dzsajx_settings.comment_form_selector, 'submit',handle_submit);
	}
	if(dzsajx_settings.search_form_selector){
		$(document).delegate(dzsajx_settings.search_form_selector, 'submit',handle_submit_search);
	}


	window.dzsajx_click_menu_anchor = click_menu_anchor;


	if(window.addEventListener){

		window.addEventListener('popstate', handle_popstate);
	}




	$('script').each(function(){
		var _t = $(this);


		if(_t.attr('src')){
			scripts_loaded_arr.push(_t.attr('src'));
		}
		//console.info(_t.attr('src'));

		if(String(_t.attr('src')).indexOf('https://maps.googleapis.com/maps/api')==0){
			window.google_maps_loaded = true;
		}
	});


	$('link').each(function(){
		var _t = $(this);

		//console.info(_t);

		if(_t.attr('rel')=='stylesheet' && _t.attr('href')){
			var aux_href = _t.attr('href');
			if(aux_href.indexOf('./')==0){
				aux_href = aux_href.replace('./','');
			}
			scripts_loaded_arr.push(aux_href);
		}
	});






	function handle_popstate(e){
		// console.log(e, e.state, e.state.href);

		if(e.state && e.state.href){


			var args = {
				'force_href': e.state.href
				,force_no_ajax:'on'
			};

			if(dzsajx_settings.use_ajax_on_back_button=='on'){
				args.force_no_ajax = 'off';
			}

			click_menu_anchor(null, args)

			//console.info(e.state.curr_menu_items,Object(e.state.curr_menu_items).size,Object.size(e.state.curr_menu_items));

			//console.log(e.state.curr_menu_items, e.state, history);

			if(Object.size && e.state.curr_menu_items && Object.size(e.state.curr_menu_items)>0){


				// console.info('my docs', _theActualNav.find('.current-menu-item'))
				if(_theActualNav) {
					_theActualNav.find('.current-menu-item').removeClass('current-menu-item');

					for (var i2 = 0; i2 < Object.size(e.state.curr_menu_items); i2++) {
						_theActualNav.find('li').eq(e.state.curr_menu_items[i2]).addClass('current-menu-item');
					}
				}
			}
			return false;
		}
	}


	function convert_to_html(arg, pargs){


		var margs = {

			'convert_script_tags' : "off"
		};

		if(pargs){
			margs = $.extend(margs, pargs);
		}

		var fout = '';

		fout = String(arg).replace(/<\!DOCTYPE.*?>/gi, '');
		fout = String(fout).replace(/<html.*?>/gi, '');
		fout = String(fout).replace(/<\/html.*?>/gi, '');
		fout = String(fout).replace(/<head>([\s|\S]*?)<\/head>/igm, '<div id="html-head">$1</div>');
		fout = String(fout).replace(/<body.*?>([\s|\S]*?)<\/body>/igm, '<div id="html-body">$1</div>');

		if(margs.convert_script_tags=='on'){

			// console.warn(fout);
			fout = String(fout).replace(/<script(.*?)>([\s|\S]*?)<\/script>/igm, '<!--start dzsajx-script-tag--><div class="dzsajx-script-tag" $1>$2</div><!--end dzsajx-script-tag-->');

			// console.warn(fout);
		}

		fout = '<div>'+fout+'</div>';

		return fout;
	}

	function handle_submit_search(e,pargs) {


		var _t = $(this);


		if (e.type == 'submit') {


			// console.info(_t.find('*[name="s"]'));

			var url = _t.attr('action')+'?s='+encodeURIComponent(_t.find('*[name="s"]').val());
			e.preventDefault();



			click_menu_anchor(null, {
				force_href: url
			});
		}
	}

	function handle_submit(e,pargs) {


		var _t = $(this);


		if(e.type=='submit'){


			var comments_post_url = _t.attr('action');
			console.info(_t);

			e.preventDefault();



			console.warn(comments_post_url);
			var req = $.ajax({
				url: comments_post_url,
				type: "POST",
				data: _t.serialize(),
				beforeSend: function(xhr){ xhr.setRequestHeader('X-WPAC-REQUEST', '1'); },
				success: function (data,ts) {

					var headers = req.getAllResponseHeaders();

					console.info("headers",headers);
					// console.info(data);

					console.info(req.getResponseHeader("X-WPAC-ERROR"), data.success, ts);





					var response_html = convert_to_html(data);


					var __response = $(response_html);


					// console.info(response_html, __response);


					console.info(__response.find('#html-body').html());

					if(__response.find('#html-body').html().indexOf('ERROR')<30 && __response.find('#html-body').html().indexOf('ERROR')>-1){


						console.warn("IS ERROR");

						$('.comment-feedbacker').html(__response.find('#html-body').html());
						$('.comment-feedbacker').fadeIn("fast");
						$('.comment-feedbacker').addClass('active is-error');

						setTimeout(function(){

							$('.comment-feedbacker').fadeOut("slow");
							$('.comment-feedbacker').removeClass('active');
						},3000)

						return false;
					}

					if(dzsajx_settings.comment_list_selector && curr_html.indexOf('replytocom')==-1  && $(dzsajx_settings.comment_list_selector).eq(0).length>0){




						if(__response.find(dzsajx_settings.comment_list_selector).eq(0).length>0){
							$(dzsajx_settings.comment_list_selector).append(__response.find(dzsajx_settings.comment_list_selector).eq(0).children().last());

							$(dzsajx_settings.comment_list_selector).children().last().css('opacity','0');
							$(dzsajx_settings.comment_list_selector).children().last().animate(
								{'opacity':'1'}
								,{queue:false
									,complete: function(e){

										$(this).css('opacity','');
									}});
						}

						console.info(__response, __response.find(dzsajx_settings.comment_list_selector).eq(0));
					}else{
						click_menu_anchor(null, {
							force_href: curr_html
							,do_not_scroll: "on"
							,do_not_change_menu: "on"
						});
					}

					_t.find('textarea').val('');

					$('.comment-feedbacker').removeClass('is-error');;
					$('.comment-feedbacker').html(dzsajx_settings.translate_comment_submitted);
					$('.comment-feedbacker').fadeIn("fast");
					$('.comment-feedbacker').addClass('active');;

					setTimeout(function(){

						$('.comment-feedbacker').fadeOut("slow");
						$('.comment-feedbacker').removeClass('active');;
					},3000)



				},
				error: function (jqXhr, textStatus, errorThrown) {

					$('.comment-feedbacker').html("error "+errorThrown);
					$('.comment-feedbacker').fadeIn("fast");
					$('.comment-feedbacker').addClass('active is-error');

					setTimeout(function(){

						$('.comment-feedbacker').fadeOut("slow");
						$('.comment-feedbacker').removeClass('active');
					},3000)

				}
			});
		}

	}

	function loadScript(src, callback){
		var s,
			r,
			t;
		r = false;
		s = document.createElement('script');
		s.type = 'text/javascript';
		s.src = src;
		s.async = true;
		s.onload = function() {
			console.warn( this.readyState ); //uncomment this line to see which ready states are called.
			if ( !r && (!this.readyState || this.readyState == 'complete') ){
			// if (this.readyState == 'complete'){
				r = true;
				callback(s);
			}
		};

		if(_dzsajx_scripts_tbe_con){
			_dzsajx_scripts_tbe_con.get(0).appendChild(s);
		}else{

			t = document.getElementsByTagName('script')[0];
			t.parentNode.insertBefore(s, t);
		}
	}
	function click_menu_anchor(e,pargs) {

		// console.info('click_menu_anchor()', pargs);




		var _t = $(this);
		var thehref = _t.attr('href');
		var isselectoption = false;
		var sw_found_content = false; // -- set true if found content selector








		var margs = {
			_t: null
			, force_href: ''
			, force_no_ajax: 'off'
			, from_zoombox: 'off'
			, force_pushState: false
			, do_not_scroll: "off"
			, do_not_change_menu: "off"
		};


		if (pargs) {
			margs = $.extend(margs, pargs);
		}




		function onajaxcompleted(response,textStatus,jqXHR){

			console.info('onajaxcompleted()')
			if (_t) {
				//console.info(_t.parent().parent().parent());
				if (_t.parent().parent().parent().hasClass('menu-toggler-target')) {
					_t.parent().parent().parent().removeClass('active');
				}
				if (_t.parent().parent().parent().parent().parent().hasClass('menu-toggler-target')) {
					_t.parent().parent().parent().parent().parent().removeClass('active');
				}
			}

			//console.info(_t);

			if(jqXHR=='Not Found'){
				response = response.responseText;
			}

			var ___response_raw = $(response);

			// console.warn(response);
			// console.info(___response_raw);


			response = String(response).replace(/<!--[\s|\S]*?-->/gim, '');


			if(dzsajx_settings.update_body_class=='on'){


				var regex_links = /<body.*?class=['|"](.*?)['|"]/gim;

				var match;

				if(match = regex_links.exec(response)) {

					// console.info(match);

					if(match[1]){
						newclass_body=match[1];

						// console.info(newclass_body);
					}

				}


				// newclass_body =
			}



			var regex_scripts = /<script.*?src=['|"](.*?)['|"][\s|\S]*?\/script>/gim;

			//


			// console.info(scripts_loaded_arr, scripts_loaded_arr.length);
			if(dzsajx_settings.scripts_to_reload){



				var aux_split = String(dzsajx_settings.scripts_to_reload).split(',');


				for ( var i233 in aux_split){
					$('script').each(function(){
						var _t = $(this);
						// console.info(_t);

						if(_t.attr('src')){


							// && _t.attr('src').indexOf('ajaxer.js')==0
							if(_t.attr('src').indexOf(aux_split[i233])>-1){

								console.info("REMOVING SCRIPT",_t);
								_t.get(0).parentNode.removeChild(_t.get(0));
							}
						}
					})

					for(var i24 in scripts_loaded_arr){
						// console.info(scripts_loaded_arr[i24]);


						if(scripts_loaded_arr[i24].indexOf(aux_split[i233])>-1){

							// console.warn(i24, scripts_loaded_arr[i24])
							scripts_loaded_arr.splice(i24,1);
						}
					}
				}


			}


			var match;
			while (match = regex_scripts.exec(response)) {

				// console.info(match);

				if (match[1]) {


					var sw = false;

					for (var j = 0; j < scripts_loaded_arr.length; j++) {

						//console.info(_t4.src, scripts_loaded_arr[j], (qcreative_options.site_url + scripts_loaded_arr[j]));

						// console.info(_t4.src, scripts_loaded_arr[j], ajax_site_url);

						// var auxa = scripts_loaded_arr[j].explode('?');

						var aux = match[1];
						if (aux.indexOf('&') > -1) {
							aux = aux.split('&')[0];
						}

						if (match[1] == '' || scripts_loaded_arr[j] == match[1] || ajax_site_url + scripts_loaded_arr[j] == match[1]) {
							sw = true;
						}
					}

					// console.info(_t4.src);

					if (sw == false && match[1]) {

						scripts_tobeloaded.push(match[1]);
					}

				}
			}

			var regex_links = /(<!--\[if lt IE \d*\]>[\S|\s]{0,1})?<link.*?href=['|"](.*?)['|"][\s|\S]*?\/{0,1}>/gim;


			while (match = regex_links.exec(response)) {

				// console.info(match);

				if (match[2]) {


					var sw = false;

					for (var j = 0; j < scripts_loaded_arr.length; j++) {

						//console.info(_t4.src, scripts_loaded_arr[j], (qcreative_options.site_url + scripts_loaded_arr[j]));

						// console.info(_t4.src, scripts_loaded_arr[j], ajax_site_url);
						if (match[1] || match[2] == '' || scripts_loaded_arr[j] == match[2] || ajax_site_url + scripts_loaded_arr[j] == match[2] || String(match[0]).indexOf("stylesheet")==-1 ) {
							sw = true;
						}
					}

					// console.info(_t4.src);

					if (sw == false && match[2]) {

						stylesheets_tobeloaded.push(match[2]);
					}

				}
			}





			function load_script_cb(arg){
				console.info('script loaded',arg,nr_scripts_tobeloaded);
				nr_scripts_tobeloaded--;
				//console.info(nr_scripts_tobeloaded);

				if (nr_scripts_tobeloaded <= 0) {
					//console.info('loadnewpage');

					//console.info($.zfolio);


					ajax_load_new_page();
				}

				// console.info(arg,i4);

				var aux = arg.src;

				if (aux.indexOf('?') > -1) {
					aux = aux.split('?')[0];
				}

				//console.info(aux);
				scripts_loaded_arr.push(aux);
			}

			for (var i2 = 0; i2 < ___response_raw.length; i2++) {
				var _t4 = ___response_raw[i2];


				// console.info(_t3.nodeName);
				if (_t4 && _t4.nodeName == 'TITLE') {
					newtitle = _t4.innerHTML;

					// console.info(newtitle);
				}






				if (_t4 && _t4.nodeName == 'STYLE') {


					// -- stylesheets check



					if(_t4.type && _t4.type=='text/css'){
						// console.info(_t4, _t4.outerHTML);

						stylesheets_tobeadded.push(_t4.outerHTML);
					}


				}


				// if (_t4 && _t4.nodeName == 'LINK') {
				//
				//
				// 	// -- stylesheets check
				//
				//
				//
				// 	if (_t4.rel != 'stylesheet') {
				// 		continue;
				// 	}
				//
				//
				//
				//
				//
				// 	var sw = false;
				//
				//
				// 	for (var j = 0; j < scripts_loaded_arr.length; j++) {
				//
				//
				// 		//console.info(aux_href, scripts_loaded_arr[j],ajax_site_url, scripts_loaded_arr[j], (ajax_site_url + scripts_loaded_arr[j]));
				// 		if (aux_href == '' || scripts_loaded_arr[j] == aux_href || ajax_site_url + scripts_loaded_arr[j] == aux_href) {
				// 			sw = true;
				// 		}
				// 	}
				//
				// 	if (sw == false) {
				//
				// 		stylesheets_tobeloaded.push(_t4.href);
				// 	}
				// }
			}

			// console.info(scripts_tobeloaded, stylesheets_tobeloaded);

			var args = {

			};

			if(scripts_execute_after_ajax_call=='on'){
				args.convert_script_tags='on';
			}

			// console.warn(response);

			// console.info(jqXHR)


			var response_html = convert_to_html(response,args);

			// console.info( response_html, $(response_html), $(response_html).find('.dzsajx-script-tag'));


			// console.warn(response_html);




			if(scripts_execute_after_ajax_call=='on') {
				var regex_script_tag = /<!--start dzsajx-script-tag--><div class="dzsajx-script-tag"(.*?)>([\s|\S]*?)<\/div><!--end dzsajx-script-tag-->/igm;


				var match;
				while (match = regex_script_tag.exec(response_html)) {

					// console.info(match);

					if (match[2]) {

						var obj = {
							attr:match[1]
							,content: match[2]
						}

						scripts_tobeexecuted.push(obj);
					}
				}



				response_html = String(response_html).replace(/<!--start dzsajx-script-tag--><div class="dzsajx-script-tag">([\s|\S]*?)<\/div><!--end dzsajx-script-tag-->/igm, '');

				// console.warn(response_html);
			}





			// $(response_html).find('.dzsajx-script-tag').each(function(){
			//
			// 	var _t24 = $(this);
			//
			// 	console.info(_t24.html());
			//
			// 	if(_t24.html()){
			//
			// 		scripts_tobeexecuted.push(_t24) ;
			// 	}
			// })


			___response = $(response_html);


			if (dzsajx_settings.menu_selector) {
				if(dzsajx_settings.menu_move_on_top_of_content=='on'){

					___response.find(dzsajx_settings.menu_selector).eq(0).remove();
				}
			}


			// console.info(___response);


			var regex_bodyclass = /<body.*?class=".*?(page-.*?)[ |"]/g;


			var aux23 = regex_bodyclass.exec(response);

			// newclass_body = '';
			// if (aux23) {
			// 	if (aux23[1]) {
			// 		newclass_body = aux23[1];
			// 	}
			// }


			// console.log(response, ___response, ___response.find(content_selector));

			new_page_html = ___response.find(content_selector).eq(0).html();

			// console.info(response, response_html, ___response, ___response.find(content_selector).length)

			if(___response.find(content_selector).length>0){

				sw_found_content = true;
			}else{

				setTimeout(function () {
					args = {};
					if (_t) {
						args._t = _t;
						args.force_href = thehref;
						args.force_no_ajax = "on";
					}
					;

					click_menu_anchor(e, args);
				},50);
			}

			if(dzsajx_settings.extra_items_to_be_recovered){
				___response.find(dzsajx_settings.extra_items_to_be_recovered).each(function(){



					var _t23 = $(this);

					extra_items.push(_t23.html());
				})


			}
			if(dzsajx_settings.extra_items_to_be_added){

				// console.info(dzsajx_settings.extra_items_to_be_added, ___response.find(dzsajx_settings.extra_items_to_be_added), ___response.find(dzsajx_settings.extra_items_to_be_added).eq(0).html());

				var aux_split = String(dzsajx_settings.extra_items_to_be_added).split(',');

				for ( var i233 in aux_split){
					___response.find(aux_split[i233]).each(function(){



						var _t234 = $(this);


						var obj = {
							'selector': aux_split[i233]
							,'html': _t234.html()
							,'outer_html': _t234.get(0).outerHTML
						}

						extra_items_tobeadded.push(obj);

						// console.info(_t234, extra_items_tobeadded, _t234.html());
					})
				}




			}

			//console.log(scripts_loaded_arr);
			for (var i = 0; i < ___response.length; i++) {
				var _t3 = ___response[i];

				if (_t3.attr && _t3.attr('class') == 'mainoptions') {
					//continue;
				}


				var aux_href = '';
				if (_t3.href) {
					aux_href = _t3.href;

					if (aux_href.indexOf('./') == 0) {
						aux_href = aux_href.replace('./', '');
					}
				}

				//console.info(_t3);


				if (has_custom_outside_content_1) {
					//console.info(_t3, _t3.className);
					if (_t3.className == 'custom-outside-content-1') {
						$('.custom-outside-content-1').html(_t3.innerHTML);

						//console.info();
					}
				}



				if (_t3.className == 'social-scripts') {
					//console.info(_t3);


					//if(social_scripts_loaded==false){
					//
					//    _body.append(_t3);
					//    social_scripts_loaded=true;
					//}
					//social_scripts_reinit = true;

					//console.info('social_scripts_reinit INIT', social_scripts_reinit)
				}

				if (_t3.className == 'content-wrapper') {


				}

				if (_t3.className == 'portfolio-fulscreen--items') {
					//console.info(_t3);


					_html_for_classes.append(_t3);

					//console.info('social_scripts_reinit INIT', social_scripts_reinit)
				}



			}

			//console.info(scripts_loaded_arr);
			// console.info(scripts_tobeloaded, stylesheets_tobeloaded);


			//console.info($.zfolio);
			setTimeout(function () {
				var i = 0;
				nr_scripts_tobeloaded = scripts_tobeloaded.length;

				// console.info(scripts_loaded_arr);
				// console.info(scripts_tobeloaded);

				function loadFunc(e) {
					//console.info(e);
				}


				if(_contentContainer){

				}else{

					// console.warn("ADDING" );
					// console.warn(new_page_html);
					_content.after(_content.clone());
					// _content.next().hide();
					_content.next().children().remove();
					_content.next().append(new_page_html);


					// jQuery('.edd_downloads_list:not(".slick-slider,.has-slick")').each(function(){
                    //
					// 	var _t = jQuery(this);
					// 	console.info("LIST ", _t);
					// 	_t.attr('data-columns','3');
                    //
					// 	/*
					// 	 if(_t.children('.col-md-4').length==0){
					// 	 _t.append('<div class="col-md-4"></div>');
					// 	 _t.append('<div class="col-md-4"></div>');
					// 	 _t.append('<div class="col-md-4"></div>');
					// 	 _t.children('.col-md-4').eq(0).append(_t.children('.edd_download:nth-child(3n+1)').clone());
					// 	 _t.children('.col-md-4').eq(1).append(_t.children('.edd_download:nth-child(3n+2)').clone());
					// 	 _t.children('.col-md-4').eq(2).append(_t.children('.edd_download:nth-child(3n+3)').clone());
                    //
					// 	 _t.children('.edd_download').remove();
					// 	 }
					// 	 */
					// 	_t.attr('data-columns','4');
                    //
					// 	if(_t.children('.col-md-3').length==0){
					// 		_t.append('<div class="col-md-3"></div>');
					// 		_t.append('<div class="col-md-3"></div>');
					// 		_t.append('<div class="col-md-3"></div>');
					// 		_t.append('<div class="col-md-3"></div>');
                    //
					// 		_t.children('.edd_download:nth-child(4n+1)').attr('dzsaux','1');
					// 		_t.children('.edd_download:nth-child(4n+2)').attr('dzsaux','2');
					// 		_t.children('.edd_download:nth-child(4n+3)').attr('dzsaux','3');
					// 		_t.children('.edd_download:nth-child(4n+4)').attr('dzsaux','4');
                    //
					// 		_t.children('.col-md-3').eq(0).append(_t.children('.edd_download[dzsaux=1]'));
					// 		_t.children('.col-md-3').eq(1).append(_t.children('.edd_download[dzsaux=2]'));
					// 		_t.children('.col-md-3').eq(2).append(_t.children('.edd_download[dzsaux=3]'));
					// 		_t.children('.col-md-3').eq(3).append(_t.children('.edd_download[dzsaux=4]'));
                    //
					// 		_t.children('.edd_download').remove();
					// 	}
                    //
                    //
					// });
				}

				if (nr_scripts_tobeloaded <= 0) {


					ajax_load_new_page();
					return false;
				}else{
					new_page_not_loaded_yet = true;

					setTimeout(function(){
						if(new_page_not_loaded_yet){

							ajax_load_new_page();
						}
					},2000);
				}


				// console.info($('.featured-popular-slick'));
				// console.info('scripts to be loaded - ',scripts_tobeloaded,scripts_tobeloaded.length);


				var i4 = 0;
				for (i4 = 0; i4 < scripts_tobeloaded.length; i4++) {

					// $.getScript(scripts_tobeloaded[i4], function (data, textStatus, jqxhr) {
					// 	//console.log( data ); // Data returned
					// 	//console.log( textStatus ); // Success
					// 	//console.log( jqxhr.status ); // 200
					// 	//console.log( "Load was performed." );
                    //
                    //
					// 	//eval(data);
                    //
					// 	//console.log(this, data,textStatus, jqxhr);
                    //
                    //
					// 	if (String(this.url).indexOf('http://maps.googleapis.com/maps') > -1) {
                    //
					// 		window.google_maps_loaded = true;
					// 		window.gooogle_maps_must_init = true;
					// 	}
                    //
                    //
					// 	nr_scripts_tobeloaded--;
					// 	//console.info(nr_scripts_tobeloaded);
                    //
					// 	if (nr_scripts_tobeloaded <= 0) {
					// 		//console.info('loadnewpage');
                    //
					// 		//console.info($.zfolio);
                    //
                    //
					// 		ajax_load_new_page();
					// 	}
                    //
					// 	//console.info(this,i4);
                    //
					// 	var aux = this.url;
                    //
					// 	if (aux.indexOf('&') > -1) {
					// 		aux = aux.split('&')[0];
					// 	}
                    //
					// 	// console.info(aux, nr_scripts_tobeloaded);
					// 	scripts_loaded_arr.push(aux);
                    //
					// })


					var the_script = scripts_tobeloaded[i4];

					if(the_script.indexOf('?')>-1){
						the_script = the_script+'&zoomit='+Math.random();
					}







                    //
					// console.warn(the_script);
					// $.ajax({
					// 	url: the_script,
					// 	dataType: 'script',
					// 	success: function (data, textStatus, jqxhr) {
					// 		// console.log( data ); // Data returned
					// 		//console.log( textStatus ); // Success
					// 		//console.log( jqxhr.status ); // 200
					// 		//console.log( "Load was performed." );
                    //
                    //
					// 		//eval(data);
                    //
					// 		//console.log(this, data,textStatus, jqxhr);
                    //
                    //
					// 		if (String(this.url).indexOf('http://maps.googleapis.com/maps') > -1) {
                    //
					// 			window.google_maps_loaded = true;
					// 			window.gooogle_maps_must_init = true;
					// 		}
                    //
                    //
					// 		nr_scripts_tobeloaded--;
					// 		console.info(nr_scripts_tobeloaded);
                    //
					// 		if (nr_scripts_tobeloaded <= 0) {
					// 			//console.info('loadnewpage');
                    //
					// 			//console.info($.zfolio);
                    //
                    //
					// 			ajax_load_new_page();
					// 		}
                    //
					// 		//console.info(this,i4);
                    //
					// 		var aux = this.url;
                    //
					// 		if (aux.indexOf('&') > -1) {
					// 			aux = aux.split('&')[0];
					// 		}
                    //
					// 		// console.info(aux, nr_scripts_tobeloaded);
					// 		scripts_loaded_arr.push(aux);
                    //
					// 	},
					// 	async: false
					// });







					setTimeout(function(i5){
						var the_script = scripts_tobeloaded[i5];

						if(the_script.indexOf('?')>-1){
							the_script = the_script+'&zoomit='+Math.random();
						}
						loadScript(the_script,load_script_cb);
					},i4*10,i4);










					// $.getScript(scripts_tobeloaded[i4], function (data, textStatus, jqxhr) {
					// 	//console.log( data ); // Data returned
					// 	//console.log( textStatus ); // Success
					// 	//console.log( jqxhr.status ); // 200
					// 	//console.log( "Load was performed." );
                    //
                    //
					// 	//eval(data);
                    //
					// 	//console.log(this, data,textStatus, jqxhr);
                    //
                    //
                    //
                    //

					// })
					// setTimeout(function(){
					// },i4*3);
                    //
					// ;
				}
				for (i4 = 0; i4 < stylesheets_tobeloaded.length; i4++) {

					$('<link/>', {
						rel: 'stylesheet',
						type: 'text/css',
						href: stylesheets_tobeloaded[i4]
					}).appendTo('head');


					// console.warn(stylesheets_tobeloaded[i4]);
					scripts_loaded_arr.push(stylesheets_tobeloaded[i4]);


				}


				setTimeout(function () {
					//console.info(window.dzsprx_init);
					//console.info($.zfolio,jQuery.zfolio);
				}, 1000)


//                            console.info(___response, ___response_scriptmo);
			}, 100);


			//console.info(thehref)


			//console.info('destroy zoombox');


			// -- we destroy it but only if we init another one
			//if(window.api_destroy_zoombox){
			//    window.api_destroy_zoombox();
			//}

			//console.info()

			//console.info('STATE CURR MENU ITEMS LINKS2',state_curr_menu_items_links);


			// console.info(" FOUND CONTENT ",sw_found_content);
			if ( ( _t.get(0) != window || margs.force_pushState ) && sw_found_content) {


				if (history_first_pushed_state == false) {

					if (window.location.href.indexOf('file://') === -1) {

						var aux = curr_html;
						if (aux == 'index.php') {
							aux = '';
						}

						var stateObj = {href: curr_html};

						history.pushState(stateObj, null, aux);
					}
					history_first_pushed_state = true;
				}

				var aux_arr = state_curr_menu_items_links.slice(0);

				var stateObj = {foo: page_change_ind, href: thehref, 'curr_menu_items': aux_arr};

				page_change_ind++;

				old_href = window.location.href;
				old_class_body = _body.attr('class');
				// console.info("OLD URL", window.location.href);
				//console.info('PUSH STATE', stateObj, thehref)
				history.pushState(stateObj, newtitle, thehref);
				if (newtitle) {
					document.title = newtitle;
				}

				curr_html = thehref;
				// console.info('curr_html is - ', curr_html)
			}


		}



		if (margs._t) {
			_t = margs._t;
			thehref = _t.attr('href');
		}

		//console.info(_html_for_classes.hasClass('page-playlistadd'), currclass_html_for_classes);
		if (_html_for_classes.hasClass('page-playlistadd')) {

			var args = {
				_t: _t
				, 'from_zoombox': 'on'
			};

			if (parent && parent.click_menu_anchor) {
				parent.click_menu_anchor(e, args);
			}


			return false;
		}
		// console.info(margs.force_href, margs.force_no_ajax, thehref, curr_html);


		// console.info(_t.parent().hasClass('current-menu-item'));
		if (_t && _t.parent().hasClass('current-menu-item') && margs.force_no_ajax != 'on') {
			return false;
		}

		if (margs.from_zoombox == 'on') {
			//console.info("CLOSE ZOOMBOS");
			window.api_close_zoombox();
		}

		if (_t.hasClass && _t.hasClass('result-con')) {

			$('.bigsearch-con').removeClass('activated');
			_html_for_classes.removeClass('big-search-activated');
		}


		if (_t && _t.get(0) && _t.get(0).nodeName == 'SELECT') {

			isselectoption = true;
			thehref = _t.val();
			//thehref = _t.find(':selected').attr('value');

		}

		if (_t && _t.get(0) && _t.get(0).nodeName == 'OPTION') {

			isselectoption = true;
			thehref = _t.val();
			//thehref = _t.find(':selected').attr('value');

		}

		//console.info(_t.hasClass('current-menu-item'), _t, _t.attr('class'));
		//if(_t&&_t.parent().hasClass('current-menu-item') && margs.force_no_ajax!='on'){
		//
		//    return false;
		//}

		// console.log(thehref, curr_html);

		if (thehref === curr_html && margs.force_no_ajax!='on') {
			return false;
		}


		if (window.dzsajx_settings.disable_ajax_on_touch_devices && window.dzsajx_settings.disable_ajax_on_touch_devices=='on' && is_touch_device()) {
			margs.force_no_ajax = 'on';
			window.location.href = thehref;
		}
		//console.info(_t[0]==window, thehref, margs);
		//if(_t[0]==window){
		//    return false;
		//}

		if (margs.force_href) {
			thehref = margs.force_href;

			console.info(margs.force_no_ajax, thehref);

			if (margs.force_no_ajax == 'on') {
				window.location.href = thehref;
			}
		}


		//console.info(busy_main_transition);
		if (busy_main_transition) {

			setTimeout(function () {
				var args = {};
				if (_t) {
					args._t = _t;
					args.force_href = thehref
				}
				;

				click_menu_anchor(e, args);
			}, 1000);

			return false;
		}


		//console.info(_t,_t.val(), isselectoption,thehref);
		if (isselectoption) {
			//return false;
		}
//        console.info(_t);

		//==== well test if it's an outer link, if its an outside link we dont need any ajax.


		//console.info(ajax_site_url); return;
		//console.info(window.dzsap_settings.enable_ajax == 'on', window, margs.force_no_ajax)
		if (window.dzsajx_settings.enable_ajax == 'on' && window && margs.force_no_ajax != 'on') {


			//console.info(thehref);
			if (thehref.indexOf('#') == 0 || thehref.indexOf('javascript') == 0 || thehref.indexOf('.jpg')>thehref.length-5) {

			} else {
				//console.info( window.location.href, thehref )

				// console.info(ajax_site_url, thehref.indexOf(ajax_site_url));
				if (window.location.href.indexOf('file://') == 0 || ( (thehref.indexOf('http://') > -1||thehref.indexOf('https://') > -1) && thehref.indexOf(ajax_site_url) != 0) || (thehref.indexOf('admin.php')>-1) || (thehref.indexOf('wp-admin')>-1) )   {


				} else {
					//if indeed we are going to history api it

					//console.info(scripts_loaded_arr);
					$('body').removeClass('loaded');
					$('.toexecute-from-dzsajx').remove();
					if (can_history_api()) {
						scripts_tobeloaded = [];
						stylesheets_tobeloaded = [];
						var nr_scripts_tobeloaded = 0;


						content_wrapper_transitioned_out = false;

						if (_t.hasClass('ajax-link-user')) {


						} else {

							_html_for_classes.addClass('dzsajx-ajax-transitioning-out ');
							_html_for_classes.addClass('dzsajx-ajax-loading ');


							if(margs.do_not_scroll!='on'){
								$('html, body').animate({
									scrollTop: 0
								}, 100);
							}


							if (dzsajx_settings.transition=='scaledown'  || dzsajx_settings.transition=='slide') {
								_content.wrap('<div id="dzsajx-content-container"></div>');

								_contentContainer = _content.parent();
							}else{

							}

							_content.addClass('last-content');

							if(_preloader){
								_preloader.addClass('active');

								if (dzsajx_settings.preloader=='bar') {

									_preloader.find('.the-bar').css({
										'width': '0'
									})
									_preloader.find('.the-bar').animate({
										'width': '60%'
									},{
										queue:false
										,duration: 1000

									})
								}
							}


						}


						setTimeout(function () {
							content_wrapper_transitioned_out = true;
						}, 300);

						var sw = false;
						if(_dzsajx_caches_con){

							// for(var i25 in pages_caches){
							_dzsajx_caches_con.children().each(function(){

								// var _t23 = pages_caches[i25];
								var _t23 = $(this);


								console.warn(_t23.attr('the_href'), thehref);

								if(_t23.attr('the_href')==thehref){
									console.info("FOUND CONTENT");
									newclass_body = _t23.attr('class_body');
									sw=true;



									if(_contentContainer){

									}else{

										// console.warn("ADDING" );
										// console.warn(new_page_html);
										console.info("NEW CONTENT - ",_t23);
										_content.after(_t23);
										_content.next().removeClass('in-cache');
										// _content.next().hide();
										// _content.next().children().remove();
										// _content.next().append(new_page_html);




										if ( ( _t.get(0) != window || margs.force_pushState ) ) {


											if (history_first_pushed_state == false) {

												if (window.location.href.indexOf('file://') === -1) {

													var aux = curr_html;
													if (aux == 'index.php') {
														aux = '';
													}

													var stateObj = {href: curr_html};

													history.pushState(stateObj, null, aux);
												}
												history_first_pushed_state = true;
											}

											var aux_arr = state_curr_menu_items_links.slice(0);

											var stateObj = {foo: page_change_ind, href: thehref, 'curr_menu_items': aux_arr};

											page_change_ind++;

											old_href = window.location.href;
											old_class_body = _body.attr('class');
											// console.info("OLD URL", window.location.href);
											//console.info('PUSH STATE', stateObj, thehref)
											history.pushState(stateObj, newtitle, thehref);
											if (newtitle) {
												document.title = newtitle;
											}

											curr_html = thehref;
											// console.info('curr_html is - ', curr_html)
										}
									}



									if(_theActualNav) {
										console.info('current menu items',_theActualNav.find('.current-menu-item'));
										_theActualNav.find('.current-menu-item').removeClass('current-menu-item current_page_item');
									}
									ajax_load_new_page();


									setTimeout(function(){

										$(window).trigger('resize');
									},2000);

									// break;
								}
							// }
							})



						}else{

						}


						if(sw==false){
							$.ajax({
								url: thehref,
								context: document.body
								, success: function (response, textStatus, jqXHR) {

									onajaxcompleted(response,textStatus,jqXHR);

									// return false;
								}

								,error: function(response,textStatus, xhr){
									// console.info('error - ',response,textStatus,xhr);
									onajaxcompleted(response,textStatus,xhr);
								}

								//console.info(_t.parent());


								//console.info(state_curr_menu_items_links);

								//state_curr_menu_items_links = _theActualNav.find('.current-menu-item');



								// if(_t && _t.hasClass('ajax-link')){
								// 	_theActualNav.find('li > a').each(function(){
								// 		var _t3 = $(this);
								//
								// 		//console.log(_t3, _t3.attr('href'), thehref);
								//
								// 		if(_t3.attr('href')==thehref||_t3.attr('href')==ajax_site_url+thehref){
								// 			_theActualNav.find('li').removeClass('current-menu-item');
								// 			_t3.parent().addClass('current-menu-item');
								// 		}
								// 	})
								// }
								//
								//
								// return false;

							})
						}



						if(_t.get(0)!=window && margs.do_not_change_menu!='on'){



							state_curr_menu_items_links = [];

							if(_theActualNav) {
								_theActualNav.find('.current-menu-item').each(function () {
									var _t2 = $(this);


									// console.info(_t2);
									//console.log(_t,_theActualNav.find('*').index(_t));


									state_curr_menu_items_links.push(_theActualNav.find('li').index(_t2));
								})
							}

							//console.info('STATE CURR MENU ITEMS LINKS', state_curr_menu_items_links);

							if(_theActualNav){

								// console.warn(_theActualNav.find('.current-menu-item'))
								_theActualNav.find('.current-menu-item').removeClass('current-menu-item current_page_item');
								_theActualNav.find('.current-menu-ancestor').removeClass('current-menu-ancestor current-menu-parent current_page_ancestor');
							}

							if(_t.parent().get(0) && _t.parent().get(0).nodeName=="LI"){
								_t.parent().addClass('current-menu-item');
							}
							if(_t.parent().parent().parent().get(0) && _t.parent().parent().parent().get(0).nodeName=="LI"){
								_t.parent().parent().parent().addClass('current-menu-ancestor');
							}

							//if(_t.parent().parent().parent().parent().hasClass('the-actual-nav')){
							//    _t.parent().parent().parent().parent().find('.current-menu-item').removeClass('current-menu-item');
							//    _t.parent().parent().parent().addClass('current-menu-item');
							//    _t.parent().addClass('current-menu-item');
							//}
						}


						return false;

					}

				}
			}

		}
	}

	function ajax_load_new_page(){


		// console.info('whaa');
		// console.info('ajax_load_new_page()', new_page_html, content_wrapper_transitioned_out);

		if(content_wrapper_transitioned_out==false){
			setTimeout(ajax_load_new_page, 300);

			return false;
		}

		// destroy listeners for all objects hier


		// -- tbc

		console.info(currclass_body, newclass_body);


		if(_dzsajx_caches_con){

		}else{
			_content.find('.audioplayer').each(function(){

				var _t = $(this);

				console.info("DESTROYING" , _t);

				if(_t.get(0) && _t.get(0).api_destroy_listeners){
					_t.get(0).api_destroy_listeners();
				}
			});
		}


		if(newclass_body){
			_body.attr('class',newclass_body);
		}



		var i23 = 0;
		if(dzsajx_settings.extra_items_to_be_recovered){
			$(dzsajx_settings.extra_items_to_be_recovered).each(function(){

				// console.info(extra_items[i23]);

				var _t23 = $(this);

				if(extra_items[i23]){
					_t23.html(extra_items[i23]);
				}

				i23++;
			})

			extra_items = [];
		}



		i23 = 0;




		if(dzsajx_settings.extra_items_to_be_added){


			var aux_split = String(dzsajx_settings.extra_items_to_be_added).split(',');

			for ( var i233 in aux_split){


				// console.info(aux_split[i233], extra_items_tobeadded);



				var sw = false; // -- if we find
				var k_found = null;

				for(var i24 in extra_items_tobeadded){


					// console.info(extra_items_tobeadded[i24]);

					if(extra_items_tobeadded[i24].selector == aux_split[i233]){
						sw = true;

						// console.info("K FOUND");
						k_found = i24;
						break;
					}





				}


				if(sw){
					var _c = extra_items_tobeadded[k_found];
					if($(_c.selector).length>0){

						$(_c.selector).eq(0).html(_c.html);
					}else{


						if(dzsajx_settings.extra_items_to_be_added_before_elements){

							$(dzsajx_settings.extra_items_to_be_added_before_elements).before(_c.outer_html);

						}else{

							_body.append(_c.outer_html);
						}
						// _content.before(_c.outer_html)
					}
				}else{

					// -- if we do not find it in the new page we remove it from the current one
					if($(aux_split[i233]).length>0){

						$(aux_split[i233]).eq(0).remove();;
					}
				}



			}
			extra_items_tobeadded = [];




		}


		// if(dzsajx_settings.extra_items_to_be_added){
		//
		// 	// console.info(extra_items_tobeadded);
		// 	for(var i24 in extra_items_tobeadded){
		//
		//
		// 		// console.info(extra_items_tobeadded[i24]);
		//
		//
		// 		var _c = extra_items_tobeadded[i24];
		//
		//
		// 		if($(_c.selector).length>0){
		//
		// 			$(_c.selector).eq(0).html(_c.html);
		// 		}else{
		// 			_body.append(_c.outer_html);
		// 		}
		// 	}
		// 	extra_items_tobeadded = [];
		// }


		if(_preloader){
			_preloader.find('.the-bar').animate({
				'width': '100%'
			},{
				queue:false
				,duration: 300

			})
			_preloader.removeClass('active');
		}


		_dzsajx_styles_con.children().remove();


		for(var i3 = 0; i3<stylesheets_tobeadded.length;i3++){
			_dzsajx_styles_con.append(stylesheets_tobeadded[i3]);
		}


		// -- here we add new page html
		if(_html_for_classes.hasClass('dzsajx-ajax-user-transitioning-out')){



			_content.find('.user-query').eq(0).after(new_page_html);
			_content.find('.user-query').eq(0).remove();
			//_content.append(new_page_html);

		}else{



			if(_contentContainer){
				_contentContainer.append(_content.clone());
				_contentContainer.children().last().removeClass('curr-content');
				_contentContainer.children().last().addClass('transitioning-content');
				_contentContainer.children().last().html(new_page_html);






				// console.info(_contentContainer.outerHeight())
				//
				// _contentContainer.css({
				// 	'height': _contentContainer.outerHeight()
				// })


				_contentTransitioning = _contentContainer.children('.transitioning-content').eq(0);

				_contentTransitioning.css({
					'height' : 'auto'
					,'position':'relative'
				})

				var auxh = _contentTransitioning.outerHeight();


				// console.info(auxh);


				_contentTransitioning.css({
					'height' : ''
					,'position':''
				})


				_contentContainer.animate({
					'height': auxh
				},{
					duration: 800
					,complete: function(){
						// console.info('ceva',this);
					}
				})


			}else{

				_content=_content.next();
				_content.removeClass('last-content');
				_content.addClass('curr-content');
				_content.show();

				// console.info("REMOVING CONTENT");
				// -- removing content

				if(_dzsajx_caches_con){

					_content.parent().find('.dzsajx-content.last-content:not(".in-cache")').attr('the_href',old_href);
					_content.parent().find('.dzsajx-content.last-content:not(".in-cache")').attr('class_body',old_class_body);

					console.info("SAVED CLASS BODY",old_class_body);
					// console.info(old_href);

					var sw = false;
					// for(var i25 in pages_caches){
					_dzsajx_caches_con.children().each(function() {

						// var _t23 = pages_caches[i25];
						var _t23 = $(this);
						;

						// if (_t23.attr('the_href') == old_href) {
						// 	_t23 = $('.dzsajx-content.last-content');
						// 	sw = true;
						// 	// break;
						// }
						// }
					})


					if(sw==false){

						// pages_caches.push(_content.prev());

						console.info('last content - ', $('.dzsajx-content.last-content'));
						_dzsajx_caches_con.append($('.dzsajx-content.last-content'));
						_dzsajx_caches_con.children().addClass('in-cache');
						_content.parent().find('.dzsajx-content.last-content:not(".in-cache")').remove()

					}



				}else{

					_content.prev().remove();
				}

				setTimeout(function(){
					console.warn(pages_caches);
				},1000);
				execute_scripts_to_be_executed();
			}
			setTimeout(function(){

				_html_for_classes.addClass('dzsajx-ajax-transitioning-in');
			},50)

		}


		reinit();

		_html_for_classes.removeClass('dzsajx-ajax-loading ');
		setTimeout(function(){

			_html_for_classes.removeClass('dzsajx-ajax-transitioning-out');
		},500);





		setTimeout(function(){


			if(dzsajx_settings.scripts_reinit_document_ready=='on'){

				// console.info("TRIGGER READY");
				jQuery.triggerReady();

				setTimeout(function(){
					jQuery.triggerLoad();
				},1000);
			}



			if(dzsajx_settings.script_call_on_reinit){
				try{
					eval(dzsajx_settings.script_call_on_reinit)
				}catch(err){
					console.warn(err);
					console.info('eval error - ',err, err.stack);
				}
			}


			// return;
			_html_for_classes.removeClass('dzsajx-ajax-transitioning-in');
			_html_for_classes.removeClass('dzsajx-ajax-user-transitioning-out');

			if(_contentContainer){

				_contentContainer.children().first().remove();
				_contentContainer.children().last().removeClass('transitioning-content');

				_content = _contentContainer.children().last();

				_content.unwrap();
				_contentContainer = null;
				// _contentContainer.children().last().removeClass('transitioning-content');

				execute_scripts_to_be_executed();
			}



		},1000);



		// if(bg_transition=='fade'){
		// 	//_mainBg.addClass('for-remove');
		// 	//
		// 	//var aux9000 = _mainBg;
		// 	//
		// 	//setTimeout(function(){
		// 	//
		// 	//    if(aux9000.get(0) && aux9000.get(0).api_destroy){
		// 	//
		// 	//        aux9000.get(0).api_destroy();
		// 	//    }
		// 	//},300);
		//
		//
		// }else{
		//
		// 	//if(_mainBg.get(0) && _mainBg.get(0).api_destroy){
		// 	//
		// 	//    _mainBg.get(0).api_destroy();
		// 	//}
		// }

		busy_main_transition=true;
		new_page_not_loaded_yet = false;
	}




	function execute_scripts_to_be_executed(){
		if(scripts_tobeexecuted.length>0){
			// if(scripts_tobeexecuted.each){
			//
			// }
			for(var i in scripts_tobeexecuted){
				var _t23 = scripts_tobeexecuted[i];
				// var _t_html = _t23.html();

				if(_t23){

					// console.warn(_t23);


					try{
						var sn = document.createElement('script');



						var aux = _t23.content;
						if(dzsajx_settings.remove_document_ready && dzsajx_settings.remove_document_ready=='on'){
							// aux = aux.replace()

							// console.info(aux);

							if(aux.indexOf('jQuery(document).ready')>-1){

							aux = aux.replace(/jQuery\(document\).ready\(function.*?{/g, '');


							var n = aux.lastIndexOf('})');

							window.$ = jQuery;

							// console.info(n, '})'.length, aux.length);
							if (n >= 0 && n + '})'.length <= aux.length) {
								// console.info("ALCEVA");
								aux = aux.substring(0, n) + "";
							}
							// console.warn(aux);
							}
						}
						sn.appendChild(document.createTextNode(aux));


						// type=


						var regexS = /['|"](.*?)['|"]/gim;
						var regtest = regexS.exec(_t23.attr);


						// console.info(regex, regtest);
						if(regtest != null){
							//var splitterS = regtest;


							// console.info(regtest);
							if(regtest[1]){
								$(sn).attr('type', regtest[1]);
							}else{

							}


						}
						// $(sn).attr('async', 'async');

						sn.async=true;

						try{
							_dzsajx_scripts_tbe_con.get(0).appendChild(sn);
						}catch(err){
							console.info('err', err);
						}
					}catch(err){
						// console.log(err);
					}

				}
			}

			scripts_tobeexecuted = [];
		}

		setTimeout(function(){

			$(window).trigger('resize');
		},1000);
	}




	function reinit(){

		console.log('reinit()');


		$("html, body").animate({ scrollTop: 0 }, "fast");







		setTimeout(function(){
			reinit_all_loaded();
		},700);



	}



	function reinit_all_loaded(pargs){


		var margs = {
			check_tracks_list: true
			,animate_shortcode_user: true
		};

		if(pargs){
			margs = $.extend(margs,pargs);
		}

		_html_for_classes.addClass('reinited');

		//console.info($('.audioplayer'));

		busy_main_transition=false;

		if(refresh_menu){

			if(_theActualNav) {
				_theActualNav = $(dzsajx_settings.menu_selector);
			}
		}

		//console.info($('.audioplayer'))


	}

});



function is_touch_device() {
	//return true;
	return !!('ontouchstart' in window);
}


function can_history_api() {
	return !!(window.history && history.pushState);
}

function is_android() {
	return true;
	var ua = navigator.userAgent.toLowerCase();
	return (ua.indexOf("android") > -1);
}



