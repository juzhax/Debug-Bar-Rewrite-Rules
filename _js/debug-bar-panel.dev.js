jQuery(window).ready(function(){

	jQuery(window)
		.bind('resize', RRAT.url_widnow_width)
		.bind('ready',  RRAT.url_widnow_width);

	jQuery('#debug-menu-link-Debug_Bar_Rewrite_Rules')
		.bind('click', RRAT.url_widnow_width)
		.bind('click', function(){
			window.setTimeout(function(){RRAT.url_widnow_width()}, 100);
		});
		
	
	jQuery('#dbrr h2').bind('click', function(){
		window.location.href = '#' + jQuery(this).attr('for');
	});
	
		
	jQuery('.mono.pattern_search')
		.bind('focus',		function(){ 
			jQuery(this).parent().addClass('active');
		
			if ( !jQuery('#rewrite_rules_array_table thead tr th.col-match').length ){
				jQuery('<th />')
					.addClass('col-match')
					.css('width', '30%')
					.text(RRAT.__matches_col_title)
					.appendTo(jQuery('#rewrite_rules_array_table thead tr'))
					.parent()
					.find('th.col-data')
					.css('width', '35%');
				
					jQuery('<td />')
						.addClass('col-match')
						.html(' ')
						.appendTo(jQuery('#rewrite_rules_array_table tbody tr'));
			}
				
		})
		.bind('blur',		function(){ 
			if (jQuery.trim(jQuery(this).val()) == "" && jQuery('.col-match').length){
				jQuery(this).parent().removeClass('active');
				jQuery('.col-match').remove();
			}
		})
		.bind('keyup',		RRAT._filter_match)
		.bind('keyup',		RRAT._filter_sarched)
	 	.bind('keypress',	RRAT._filter_match);
	
	jQuery('.mono.pattern_domain')
		.bind('click', function(){
			jQuery('.mono.pattern_search').focus();
		});

	jQuery('.mono.matches_field')
		.bind('focus',  function(){
			if (jQuery.trim(jQuery('.pattern_search').val()) != "" && jQuery('.col-match').length){
				jQuery('.filter_url').removeClass('active');
				jQuery('.col-match').remove();
				jQuery('th.col-data').css({width: '50%'});
			}
		})
		.bind('keypress', RRAT._search_for_match)
		.bind('keyup',	  RRAT._search_for_match);
		
	jQuery('.flush_rewrite_rules_trigger').monitor('on', 'click', function(){
		jQuery(this).find('.spinner').show();
		
		jQuery.post(
			RRAT.ajax, 
			{
				action:'DEBUG_BAR_REWRITE_RULES_ajax_flush_rewrite_rules',
				request:'json'
			}, 
			function(html){
				jQuery('.flush_rewrite_rules_trigger .spinner').hide();
				if ( typeof html.message != 'undefined'){
					
					console.log('OOPS! Something gone wrong...');
					//alert(html.message);
				
				} else {
					
					
					jQuery('.mono.pattern_search').val('');
					jQuery('.filter_url.active').removeClass('active');
						
					/*** Rewrite Rules List ***/
					if (jQuery('h2[for=dbrr_rewrite_rules_array]').length == 1){
						
						var dbrr_rewrite_rules_array_title	=	jQuery('h2[for=dbrr_rewrite_rules_array]').html();
						jQuery('h2[for=dbrr_rewrite_rules_array]').html( dbrr_rewrite_rules_array_title.split('</span>')[0] + '</span>' + html.rewrite_rules_array_n);
						
						jQuery('#rewrite_rules_array_holder').html(html.rewrite_rules_array);
				 
					}
					
					
					/*** Rewrite Rules Filters List ***/
					
					if ( html.dbrr_rewrites_filters_n == 0 ){
						
						jQuery('h2[for=dbrr_rewrites_filters]').remove();
						jQuery('#dbrr_rewrites_filters_holder').html('');
						
					} else {
						 jQuery('h2[for=dbrr_rewrites_filters]').remove();
						 
						if ( jQuery('h2[for=dbrr_rewrites_filters]').length == 0)
						  	 jQuery( "<h2 for='dbrr_rewrites_filters'><span> </span> 0 </h2>" ).insertAfter( "h2[for=dbrr_rewrite_rules_array]" );
						
					 	var dbrr_rewrites_filters_title 	=	jQuery('h2[for=dbrr_rewrites_filters]').html();
						jQuery('h2[for=dbrr_rewrites_filters]').html( dbrr_rewrites_filters_title.split('</span>')[0] + '</span>' + html.dbrr_rewrites_filters_n);
					 	
						jQuery('#dbrr_rewrites_filters_holder').html(html.dbrr_rewrites_filters);
						
					}
					 
					
					RRAT.el = jQuery("#rewrite_rules_array_table tbody tr");
					 
				}
			},
			'json'
		);
		
		return false;
	});

});


 

var RRAT  = {
	/* calculate the domain URL input width */ 
	url_widnow_width : function(){
		
		width = jQuery('<span />').attr('id', 'http_width_dbg')
			.text(RRAT.domain)
			.css('padding', jQuery('inpit.mono.pattern_domain')
			.css('padding'))
			.addClass('mono')
			.appendTo('body').outerWidth() + 10;
			
		jQuery('#http_width_dbg').remove();
		
		var dbrr_obj = jQuery('#querylist').css('display') != 'none' && jQuery('body.tools_page_permalinks').length == 1 
			? jQuery('body.tools_page_permalinks #wpbody-content') : jQuery('#dbrr');
	 
		
		jQuery('.mono.pattern_domain', dbrr_obj).css('width', width);
		var patter_url_width = jQuery('.filter_url', dbrr_obj).width() - (width + 10);
	 	jQuery('.mono.pattern_search', dbrr_obj).css('width', patter_url_width > 100 ? patter_url_width : 100);
	},
	
	/* parse_url clone */
	parse_url : function (url, t) {
	 	/*	Dom parsing of A	*/
		var parser = document.createElement('a');
		parser.href = url;
			
		/*	Exception for user:password	*/
		var re = /(\/\/)(.+?)((:(.+?))?)@/i;
		if ( re.exec(url) ){
			var result = re.exec(url)
			if (typeof result[2] == "string" && result[2].length > 0)
				parser.user = result[2];
			if (typeof result[5] == "string" && result[5].length > 0)
				parser.pass = result[5];
		}
				
		var urls = new Object();
		var urls_php = ['scheme',	'host',		'port',	'user',	'pass', 'path',		'query',  'fragment'];
		var urls_js  = ['protocol',	'hostname',	'port',	'user',	'pass', 'pathname',	'search', 'hash'];
		
		urls_js.forEach(function(value, index){
			if (typeof parser[value] == "string" && parser[value].length > 0)
				switch(value){
					case "protocol":
						urls[ urls_php[index] ] =  parser[value].replace(":", "");
					break;
					case "hash":
						urls[ urls_php[index] ] = parser[value].replace("#", "");
					break;	
					case "pathname":
						if (parser[value] != "/")
							urls[ urls_php[index] ] = parser[value];
					break;	
					default:
						urls[ urls_php[index] ] = parser[value];
					break;
				}
		});	
		return urls;
	},
	
	/* Filter Table For Matches */
	/* @todo  - no results message */
	_search_for_match : function(e){
		// do nothing
		if ( 13 == e.which) {
			e.preventDefault();
			return false;
		}
		
		// do something
		
		// search value....
		RRAT.search_for_match = jQuery.trim(jQuery(this).val());
		
		RRAT._tbl();
		
		if (RRAT.search_for_match != ""){
			RRAT.el.each(function(){

				if (jQuery(this).text().indexOf(RRAT.search_for_match) == -1){
					jQuery(this).addClass('hidden');
				} else {
					jQuery('td', jQuery(this)).each(function(){
						text = jQuery(this).text().replace(RRAT.search_for_match, '<span>' + RRAT.search_for_match + '</span>');;
					 	jQuery(this).html(text);
					});
				}
			});
		}
		
		RRAT._tbl_zebra();
			
	},
	/* filter for search */
	_filter_sarched : function(e){
		if ( 13 == e.which) {
		 	e.preventDefault();
		}
		
		RRAT._tbl();
		RRAT._tbl_zebra();
		
		RRAT.el.each(function(i){
		
			jQuery('td:eq(0),td:eq(1)', jQuery(this)).each(function(){
			 	jQuery(this).html(jQuery(this).text());
			});
		
			jQuery('td:eq(2)', jQuery(this)).html('');
		
		});	
		
		if (jQuery.trim(jQuery('.mono.pattern_search').val()) != ""){
				
				if (typeof RRAT.rules == 'undefined'){
					RRAT.rules = {};	
					RRAT.el.each(function(){
						RRAT.rules[ jQuery(this).attr('id') ] = {
						rule  : jQuery('td:eq(0)', jQuery(this) ).text(),
						match : jQuery('td:eq(1)', jQuery(this) ).text()
						}
					});
				}
					
				
			 	
				jQuery.ajax({
					async 		: false,
					url   		: RRAT.validator,
					crossDomain : true,
					dataType	: 'json',
					type		: 'POST',
					data 		: {
						rules   : RRAT.rules,
						search  : jQuery('.mono.pattern_search').val(),
						use_verbose_page_rules : RRAT.use_verbose_page_rules
					}
				})
				.done(function(data){
					
					RRAT._tbl();
					RRAT.el.each(function(i){
						if (data.rules[ (i+1) ].result == false){
							jQuery(this).addClass('hidden');
						} else {
							RRAT.current = RRAT.el.eq(i);
							console.log(data.rules[(i+1)].vars);
							if (typeof data.rules[(i+1)].vars != "undefined")
								jQuery.each(data.rules[(i+1)].vars, function(_index, _value){
									jQuery('td:eq(2)', RRAT.current).append("<div><strong>" + _index + "</strong> : " + _value + "</div>");
								});
						}
						 
					});
					RRAT._tbl_zebra(); 
				});
			
		}
		
	},
	/* filter url value to only path and query...*/
	_filter_match : function(e){
	 
		if (typeof RRAT.base == 'undefined'){
			RRAT.base = RRAT.parse_url(RRAT.domain);
		}
		if ( 13 == e.which && jQuery.trim(jQuery(this).val()) != "") {
			e.preventDefault();
			return false;
		}
		var current = RRAT.parse_url(jQuery('.mono.pattern_search').val());
	
		jQuery.each(['scheme','://','host', 'port', 'path'], function(index, value) { 
			val = jQuery('.mono.pattern_search').val().split('?')[0].split('#')[0];
			o = val;
			if (typeof current[value] == 'string' && current[value] == RRAT.base[value] && val.indexOf(current[value]) != -1){
				val = val.substring(val.indexOf(current[value]) + current[value].length);
			} else if (value == '://' && val.indexOf(value) != -1) {
				val = val.substring(val.indexOf(value) + value.length);
			} else if (value == 'path' && val.indexOf(RRAT.base[value]) == 0){
				val = val.substring(val.indexOf(RRAT.base[value]) + RRAT.base[value].length);	
			}  
			val = jQuery.trim(val);
			if (o != val)
				jQuery('.mono.pattern_search').val(val);
		});
	},
	
	/* table back */
	_tbl : function(){
		
		RRAT.el.removeClass('hidden');
		RRAT.el.removeClass('alt');
 		RRAT.el.each(function(){
			jQuery('td', jQuery(this)).each(function(){
			 	jQuery(this).html(jQuery(this).text());
			});
		});
	},
	
	/* table zabra*/
	_tbl_zebra : function(){
		RRAT.el
		.filter(':visible')
		.each(function(i){
			if ((i+2)%2 == 1)
				jQuery(this).addClass('alt');
		});
	}
};