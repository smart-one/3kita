/**
* @version 3.2.7.1
* @package PWebContact
* @copyright © 2014 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
* @license GNU General Public License http://www.gnu.org/licenses/gpl-3.0.html
* @author Piotr Moćko
*/

if (typeof jQuery !== "undefined") jQuery(document).ready(function($){
	
	// show/hide description
	$('.pweb-colapse a').click(function(e) {
		e.preventDefault();
		$(this).parent().find('.pweb-content').toggleClass('hide');
	});
	
	// validate single email
	$('.pweb-filter-email').on('change', function() {
		if (this.value) {
			var regex=/^[a-zA-Z0-9.!#$%&‚Äô*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;
			if (regex.test(this.value)) {
				$(this).removeClass('invalid').closest('.control-group').removeClass('error');
			} else {
				$(this).addClass('invalid').closest('.control-group').addClass('error');
			}
		}
	});
	
	// validate coma separated emails
	$('.pweb-filter-emails').on('change', function() {
		if (this.value) {
			var regex=/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*\.\w{2,4}(,[ ]*\w+([\.-]?\w+)*@\w+([\.-]?\w+)*\.\w{2,4})*$/;
			if (regex.test(this.value)) {
				$(this).removeClass('invalid').closest('.control-group').removeClass('error');
			} else {
				$(this).addClass('invalid').closest('.control-group').addClass('error');
			}
		}
	});
	
	// validate list of email recipients
	$('.pweb-filter-emailRecipients').on('change', function() {
		if (this.value) {
			var regex=/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*\.\w{2,4}[\|]{1}[^\r\n\|]+([\r]?\n\w+([\.-]?\w+)*@\w+([\.-]?\w+)*\.\w{2,4}[\|]{1}[^\r\n\|]+)*$/;
			if (regex.test(this.value)) {
				$(this).removeClass('invalid').closest('.control-group').removeClass('error');
			} else {
				$(this).addClass('invalid').closest('.control-group').addClass('error');
			}
		}
	});
	
	// validate int
	$('.pweb-filter-int').on('change', function() {
		if (this.value && this.value !== 'auto') {
			var value = parseInt(this.value);
			this.value = isNaN(value) ? '' : value;
		}
	});
	
	// validate float
	$('.pweb-filter-float').on('change', function() {
		if (this.value && this.value !== 'auto') {
			var value = parseFloat(this.value);
			this.value = isNaN(value) ? '' : value;
		}
	});
	
	// validate unit
	$('.pweb-filter-unit').on('change', function() {
		var regex = /^\d+(px|em|ex|cm|mm|in|pt|pc|%){1}$/i;
		if (!this.value || this.value === 'auto' || regex.test(this.value)) {
			$(this).removeClass('invalid').closest('.control-group').removeClass('error');
		} else {
			var value = parseInt(this.value);
			if (!isNaN(value)) {
				this.value = value+'px';
				$(this).removeClass('invalid').closest('.control-group').removeClass('error');
			} else {
				$(this).addClass('invalid').closest('.control-group').addClass('error');
			}
		}
	});
	
	// validate color
	$('.pweb-filter-color').on('change', function() {
		var regex = /^(\w|#[0-9a-f]{3}|#[0-9a-f]{6}|rgb\(\d{1,3},[ ]?\d{1,3},[ ]?\d{1,3}\)|rgba\(\d{1,3},[ ]?\d{1,3},[ ]?\d{1,3},[ ]?[0]?\.\d{1}\))$/i;
		if (!this.value || regex.test(this.value)) {
			$(this).removeClass('invalid').closest('.control-group').removeClass('error');
		} else {
			$(this).addClass('invalid').closest('.control-group').addClass('error');
		}
	});
	
	// validate url
	$('.pweb-filter-url').on('change', function() {
		var regexp = /^((http|https):){0,1}\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?$/i;
		if (!this.value || regex.test(this.value)) {
			$(this).removeClass('invalid').closest('.control-group').removeClass('error');
		} else {
			$(this).addClass('invalid').closest('.control-group').addClass('error');
		}
	});
	
	// validate upload file size
	$('.pweb-filter-upload-max-size').on('change', function() {
		if (this.value) {
			var maxSize = pwebUploadMaxSize || 0,
				value = parseFloat(this.value);
			value = isNaN(value) ? 1 : value;
			if (value > maxSize) value = maxSize;
			this.value = value;
		}
	});
	
	// Validate upload files extensions
	$('.pweb-filter-ext').on('change', function(){
		this.value = this.value.toLowerCase().replace(/[^a-z0-9|?]+/g, '');
	});
	
	// Validate JavaScript code
	$('.pweb-filter-javascript').on('change', function(){
		var valid = true;
		try {
			if (this.value) eval(this.value);
		} catch(err) {
			if (err.message.indexOf('is not defined') == -1 && err.message.indexOf('is undefined') == -1)
				valid = false;
		}
	    if (valid) {
			$(this).removeClass('invalid').closest('.control-group').removeClass('error');
		} else {
			$(this).addClass('invalid').closest('.control-group').addClass('error');
		}
	});
	
	// Warn about limited display of contact form
	$('.pweb-component-view').click(function(){
		if (this.value == 2)
			alert(Joomla.JText._('MOD_PWEBCONTACT_INTEGRATION_COMPONENT_VIEW'));
	});
	
	// Email HTML template preview 
	$('.pweb-email-preview').each(function(){
		$('<a>', {
			href: '#',
			html: '<i class="icon-eye"></i> '+Joomla.JText._('MOD_PWEBCONTACT_PREVIEW_BUTTON'),
			'class': 'pweb-email-preview-button'
		}).click(function(e) {
			e.preventDefault();
			
			var tmpl = $(this).parent().find('select').val();
			Joomla.popupWindow('../media/mod_pwebcontact/email_tmpl/'+tmpl+'.html', 'Preview', 700, 500, 1);
			
		}).appendTo($(this).parent());
	});
	
	// Add copy button to Analytics sample codes
	$('.pweb-analytics-code code').each(function(){
		$('<a>', {
			href: '#',
			html: '<i class="icon-copy"></i> '+Joomla.JText._('MOD_PWEBCONTACT_COPY_BUTTON'),
			'class': 'pweb-copy-code-button'
		}).click(function(e) {
			e.preventDefault();
			
			var code = $(this).prev().html(),
				field = $('#jform_params_oncomplete'),
				old_code = field.val(),
				pos = field.offset().top,
				maxPos = pos + field.outerHeight() - $(window).height();
			field.val((old_code ? old_code+'\r\n' : '') + code);
			if (pos > maxPos) pos = maxPos;
			$('html, body').animate({ scrollTop: pos }, 500);
		
		}).insertAfter($(this));
	});
	
	// Set module position
	$('.pweb-set-position').click(function(e){
		e.preventDefault();
		
		var $jform_position = $('#jform_position'),
			value = $(this).data('position');
		if ($jform_position.val() != value)
		{
			$jform_position.val(value);
			if ($jform_position.val() != value
				&& typeof $.fn.chosen === 'function' 
				&& $jform_position.prop('tagName').toLowerCase() == 'select' 
			) {
				var Chosen = $jform_position.data('chosen'),
					group = Chosen.add_unique_custom_group(),
	        		option = $('<option value="' + value + '">' + value + '</option>');
	        	$jform_position.append( group.append(option) )
	        		.val(value)
	        		.trigger('chosen:updated')
	        		.trigger('liszt:updated');
			}
		}
		
		alert(Joomla.JText._('MOD_PWEBCONTACT_POSITION_SET'));
	});
	
	// Assign module to all menu items
	$('.pweb-menuitems-all').click(function(e){
		e.preventDefault();
		
		$('#jform_assignment').val(0).trigger('change');
		if (typeof document.id === 'function')
			document.id('jform_assignment').fireEvent('change');
		
		if (typeof $.fn.chosen === 'function') {
			$('#jform_assignment').trigger('chosen:updated').trigger('liszt:updated');
		}
		
		alert(Joomla.JText._('MOD_PWEBCONTACT_ASSIGNED_TO_ALL_MENU_ITEMS'));
	});
});