/*
** Backeards Compatibility for WP Plugins that use jQuery event listners in   
** WordPress from v2.9 (jQuery v1.3.2) till v3.5 (jQuery v1.8) 
*/

jQuery.fn.monitor = function(type, event, handler){
	var currentJqueryVersion = jQuery.fn.jquery.substring(0,5);    
 
	switch(type){
		case 'on':
			if  (versioncompare(currentJqueryVersion, "1.7")) {
				return jQuery(this).on(event,  handler);
			} else  {
				return jQuery(this).live(event, handler);
			}
		break;
		case 'off':
			if  (versioncompare(currentJqueryVersion, "1.7")) {
				return jQuery(this).off(event, handler);
			} else  {
				return jQuery(this).die(event, handler);
			}
		break;
	} 
};


/*
** Backeards Compatibility for WP Plugins that use jQuery property readers in   
** WordPress from v2.9 (jQuery v1.3.2) till v3.5 (jQuery v1.8) 
*/
jQuery.fn.property = function(type, property, data){
	var currentJqueryVersion = parseFloat(jQuery.fn.jquery.substring(0,5));    
	switch(type){
		case 'get':
			if  (versioncompare(currentJqueryVersion, "1.6")) {
				return jQuery(this).prop(property);
			} else  {
				return jQuery(this).attr(property);
			}
		break;
		case 'set':
			if  (versioncompare(currentJqueryVersion, "1.6")) {
				return jQuery(this).prop(property, data);
			} else  {
				return jQuery(this).attr(property, data);
			}
		break;
		
		case 'delete':
			if  (versioncompare(currentJqueryVersion, "1.6")) {
				return jQuery(this).removeProp(property);
			} else  {
				return jQuery(this).attr(property, '');
			}
		break;
		default:
			var data = property;
			var property = type;
			if (typeof data != 'undefined'){
				if  (versioncompare(currentJqueryVersion, "1.6")) {
					return jQuery(this).prop(property, data);
				} else  {
					return jQuery(this).attr(property, data);
				}
			} else {
				if  (versioncompare(currentJqueryVersion, "1.6")) {
					return jQuery(this).prop(property);
				} else  {
					return jQuery(this).attr(property);
				}
			}
		break;
	} 
};

/*
** Block of html as form reader (serialize analogue)
*/

jQuery.fn.read = function(){
	var obj = new Object();
    jQuery(this).find("input[type='text'],input[type='password'],input[type='hidden'],textarea").each(function () {
        if (jQuery(this).attr('disabled') == false || jQuery(this).attr('disabled') == undefined) {
            var k = this.name || this.id || this.parentNode.name || this.parentNode.id;
            obj[k] = jQuery(this).val()
        }
    });
    jQuery(this).find("input[type='checkbox']").filter(":checked").each(function () {
        if (jQuery(this).attr('disabled') == false || jQuery(this).attr('disabled') == undefined) {
            var k = this.name || this.id || this.parentNode.name || this.parentNode.id;
            obj[k] = jQuery(this).val()
        }
    });
    jQuery(this).find("input[type='radio']").filter(":checked").each(function () {
        if (jQuery(this).attr('disabled') == false || jQuery(this).attr('disabled') == undefined) {
            var k = this.name || this.id || this.parentNode.name || this.parentNode.id;
            obj[k] = jQuery(this).val()
        }
    });
    jQuery(this).find("select").each(function () {
        if (jQuery(this).attr('disabled') == false || jQuery(this).attr('disabled') == undefined) {
            var k = this.name || this.id || this.parentNode.name || this.parentNode.id;
            obj[k] = jQuery(this).val()
        }
    });
    return obj;
}


jQuery.fn.message = function(message, status, deley){
	var messageId 		= 'id' + parseInt(Math.random() * 1000000);
	 	if (typeof status == "undefined")
		status = 'error';
	
	if (status != 'error')
		status += ' updated'; 
	
	
	if (typeof deley == "undefined"){
		if (typeof status != "undefined" && parseInt(status) == status){
			delay = status;
		} else {
			delay = 3000; 
		}
	} 
		  
	message = '<div id="'+messageId+'" class="'+status+'"><p>'+message+'</p></div>';
	
	window.setTimeout(function(){ jQuery('#'+messageId).fadeOut(400, function(){ jQuery(this).remove(); }) }, delay);
	
	return jQuery(message).insertAfter(this);
}


// Return 1 if a > b
// Return -1 if a < b
// Return 0 if a == b
function versioncompare(a, b) {
    if (a === b) {
       return 0;
    }

    var a_components = a.split(".");
    var b_components = b.split(".");

    var len = Math.min(a_components.length, b_components.length);

    // loop while the components are equal
    for (var i = 0; i < len; i++) {
        // A bigger than B
        if (parseInt(a_components[i]) > parseInt(b_components[i])) {
            return 1;
        }

        // B bigger than A
        if (parseInt(a_components[i]) < parseInt(b_components[i])) {
            return -1;
        }
    }

    // If one's a prefix of the other, the longer one is greater.
    if (a_components.length > b_components.length) {
        return 1;
    }

    if (a_components.length < b_components.length) {
        return -1;
    }

    // Otherwise they are the same.
    return 0;
}