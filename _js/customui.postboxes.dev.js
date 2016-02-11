$HIDDEN_POST_BOXES = function(){
	jQuery(this).each(function(){
	 
		if (jQuery(this).property('checked')){
			jQuery('#'+jQuery(this).val()).removeClass('hidden').css('display', 'block');
		} else {
			jQuery('#'+jQuery(this).val()).addClass('hidden').css('display', 'none');
		}
	});
	 
	if (arguments.length){
	 	$SAVE_BOST_BOXES.apply();
	}
	
};	
$CLOSED_POST_BOXES = function(){
	
	jQuery(this).each(function(){
		if (jQuery(this).parent().parent().hasClass('widget_ui')){
			jQuery(this).parent().parent().toggleClass('pasive').toggleClass('active')	
		} else {
			jQuery(this).parent().toggleClass('closed');
		}
	});
	 
 	$SAVE_BOST_BOXES.apply();
};	
 

$SAVE_BOST_BOXES = function(){
	
	var order = {};
	jQuery('.ui-sortable').each(function(){
		id =jQuery(this).attr('id').split('-sortable')[0];
		order[ id  ] = jQuery('.widget_ui,.postbox', this).map(function() {return this.id;}).get().join(',');
	});
		 
		
	jQuery.post(ajaxurl, {
			action   :'save_postboxes_settings_api',
			screen   : pagenow,
			sortings : order,
			test	 :'demo',
			hidden 	 : jQuery('.postbox.hidden,.widget_ui.hidden').map(function() {return this.id;}).get().join(','),
			closed 	 : jQuery('.widget_ui.pasive,.postbox.closed').map(function() {return this.id;}).get().join(','),
		} 
	);
}  
	
	
	
	
jQuery(window).ready(function(){
	jQuery('.meta-box-sortables').sortable({
		placeholder			: 'sortable-placeholder',
		items				: '.postbox,.widget_ui',
		handle				: '.hndle,.widget_ui_header',
		cursor				: 'move',
		delay				: 0,
		distance			: 2,
		tolerance			: 'pointer',
		forcePlaceholderSize: true,
		helper				: 'clone',
		opacity				: 0.65,
		stop				: function(e,ui) {
				$SAVE_BOST_BOXES.apply();
		}
	});
		
	$HIDDEN_POST_BOXES.apply(jQuery('.hide-postbox-tog'));
		
	jQuery('.hide-postbox-tog').monitor('on', 'change', function(){
	 	$HIDDEN_POST_BOXES.apply(jQuery('.hide-postbox-tog'), ['true']);
	});
		
	jQuery('.widget_ui .widget_ui_header h3,.postbox .handlediv').monitor('on', 'click', $CLOSED_POST_BOXES );

});