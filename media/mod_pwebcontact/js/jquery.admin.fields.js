/**
* @version 3.2.4
* @package PWebContact
* @copyright © 2014 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
* @license GNU General Public License http://www.gnu.org/licenses/gpl-3.0.html
* @author Piotr Moćko
*/

(function($){
	
	pwebFields = function(element, options)
	{
		return this.initialize(element, options);
	};
	
	pwebFields.prototype = (function() {
	
		return {
		
		constructor: pwebFields,
		
		options: {
			jversion: 3.0
		},
		
		id: 	null,
		hidden: false,
		sort: 	false,
		fields: [],
		sysFields: {
			separator_system_top: null,
			separator_system_bottom: null,
			separator_upload: null
		},
		
		initialize: function(element, options)
		{
			var that = this;
			
			this.options 	= $.extend({}, this.options, options);
			this.$element 	= $(element);
			this.id 		= this.$element.attr('id');
			
			// Move fields container to proper place
			if (this.options.jversion >= 3) {
				this.$element.insertAfter(this.$element.parent().parent());
				this.$element.prev().remove();
			}
			
			// Tooltips
			this.tooltips = typeof Tips === 'function' ? new Tips(null, {"maxTitleChars": 50, "fixed": false}) : null;
			
			// Get fields DOM container
			this.$fields = this.$element.find('.pweb-container');
			
			
			// Load saved fields
			var fields = this.$element.find('input.data').val();
			if (fields) {
				try {
					fields = $.parseJSON(fields);
				} catch (e) {
					fields = false;
				}
				if (fields && fields.length) {
					$.each(fields, function(key, el) {
						if (el.type == 'separator_text') 
							that.addSeparator(el.type, el.name);
						else if (el.type.indexOf('separator_') == 0) 
							that.addSeparator(el.type);
						else
							that.addField(el.type, el.name, el.alias, el.values, el.tooltip, el.params, el.required, false, true);
					});
				}
			}
			
			// load system fields if missing
			if (!this.sysFields.separator_system_top) 
				this.addSeparator('separator_system_top', undefined, false, false);
			if (!this.sysFields.separator_upload) 
				this.addSeparator('separator_upload');
			if (!this.sysFields.separator_system_bottom) 
				this.addSeparator('separator_system_bottom');
			
			
			// Toggle fields button
			this.$element.find('.pweb-action-toggleall').click(function(e) {
				e.preventDefault();
				// Change icon
				$(this).find('i')[0].className = that.hidden ? 'icon-arrow-up' : 'icon-arrow-down';
				// toggle all fields except the first one
				that.$fields.find('.control-container').each(function() {
					var i = $(this).data('index');
					if (typeof that.fields[i].hidden !== 'undefined' && that.fields[i].hidden === that.hidden) {
						that.fields[i].toggle.trigger('click');
					}
				});
				// Change state
				that.hidden = !that.hidden;
				
			}).trigger('click'); // Hide all fields
			
			
			// Creates an interface for drag and drop sorting of fields
			if (typeof $.fn.sortable === 'function')
			{
				this.$fields.sortable({
					axis: 'y',
					opacity: 0.7,
					cancel: 'input,textarea,button,select,option,a,div.chzn-container',
					start: function( event, ui ) {
						ui.item.addClass('pweb-dragged');
					},
					stop: function( event, ui ) {
						ui.item.removeClass('pweb-dragged');
					}
				});
				this.sort = this.$fields.sortable('widget');
			}
			else
			{
				$('#'+this.id+'_error').text(Joomla.JText._('MOD_PWEBCONTACT_ERROR_FIELDS_SORTING')).show();
			}
			
			
			// Add new field button
			this.$element.find('.pweb-action-add').click(function(e) {
				e.preventDefault();
				// create new empty field
				var field = that.addField();
				// scroll to new field
				that.scrollToElementEdge(field.container);
			});
			
			// Load sample fields button
			this.$element.find('.pweb-action-sample').click(function(e) {
				e.preventDefault();
				$(this).hide();
				
				var field = that.addField('name', 'MOD_PWEBCONTACT_NAME', 'name', null, null, null, true);
				that.addField('email', 'MOD_PWEBCONTACT_EMAIL', 'email', null, null, null, true);
				that.addField('phone', 'MOD_PWEBCONTACT_PHONE', 'phone', null, null, null, false);
				that.addField('textarea', 'MOD_PWEBCONTACT_MESSAGE', 'message', null, null, null, true);
				// scroll to fields
				that.scrollToElementEdge(field.container);
			});
			
			// Add new separator text button
			this.$element.find('.pweb-action-addtext').click(function(e) {
				e.preventDefault();
				// create new separator
				var field = that.addSeparator('separator_text', undefined, true);			
				// scroll to new separator
				that.scrollToElementEdge(field.container);
			});
			
			// Add new separator column button
			this.$element.find('.pweb-action-addcolumn').click(function(e) {		
				e.preventDefault();
				// create new separator
				var field = that.addSeparator('separator_column');	
				// scroll to new separator
				that.scrollToElementEdge(field.container, undefined, true);
			});
			
			// Add new separator page button
			this.$element.find('.pweb-action-addpage').click(function(e) {
				e.preventDefault();
				// create new separator
				var field = that.addSeparator('separator_page');
				// scroll to new separator
				that.scrollToElementEdge(field.container, undefined, true);
			});
			
			// Save fields on submit
			$('form[name=adminForm]')[0].onsubmit = function() {
				var data = [];
				// create array with fields data in order as DOM elements
				that.$fields.children('.control-container').each(function(index) {
					
					var i = $(this).data('index'),
						type = that.fields[i].type.val();
					
					if (type == 'separator_text') {
						data.push({
							type: 	type,
							name: 	that.fields[i].name.val()
						});
					}
					else if (type.indexOf('separator_') == 0) {
						data.push({
							type: 	type
						});
					}
					else {
						// Create field alias if missing
						if (!that.fields[i].alias.val()) {
							var alias = that.fields[i].name.val().replace(/[^a-z0-9\_]+/gi, '').toLowerCase();
							that.fields[i].alias.val(alias ? alias : 'field_'+index);
						}
						
						// Set default value for single checkbox
						if (type == 'checkbox' && !that.fields[i].values.val()) {
							that.fields[i].values.val('JYES');
						}
							
						data.push({
							type: 		type,
							name: 		that.fields[i].name.val(),
							alias: 		that.fields[i].alias.val(),
							values: 	that.fields[i].values.val(),
							tooltip: 	that.fields[i].tooltip.val(), 
							params: 	that.fields[i].params.val(), 
							required: 	that.fields[i].required[0].checked
						});
					}
				});
				
				// Save fields in hidden field with JSON encoding
				that.$element.find('input.data').val(JSON.stringify(data));
			};
		},
	
		
		addField: function(type, name, alias, values, tooltip, params, required, ordering, noDefault)
		{
			if (typeof ordering === 'undefined') ordering = true;
			
			var that = this,
				inserted = false,
				i = this.fields.length,
				selector = '#'+this.id+'_'+i,
				html = this.$element.find('.template_field').html().replace(/pwebx/g, i);
						
			// Add new item to fields array
			this.fields[i] = {
				hidden: false, 
				container: $(html)
			};
			this.fields[i].container.data('index', i);
			
			// Name
			this.fields[i].name = this.fields[i].container.find(selector+'_name');
			if (typeof name !== 'undefined') {
				this.fields[i].name.val(name);
			}
			
			// Alias
			this.fields[i].alias = this.fields[i].container.find(selector+'_alias');
			this.fields[i].alias.change(function(){
				this.value = this.value.replace(/[^a-z0-9\_]+/gi, '').toLowerCase();
			});
			if (typeof alias !== 'undefined') this.fields[i].alias.val(alias);
			
			// Type of field
			this.fields[i].type = this.fields[i].container.find(selector+'_type');
			this.fields[i].type.removeClass('chzn-done').next('.chzn-container').remove();
			this.fields[i].type.data('index', i).change(function() {
				that.changeFieldType($(this).data('index'));
			});
			
			// Values
			this.fields[i].values = this.fields[i].container.find(selector+'_values');
			if (typeof values !== 'undefined') this.fields[i].values.val(values);
			
			// Tooltip
			this.fields[i].tooltip = this.fields[i].container.find(selector+'_tooltip');
			if (typeof tooltip !== 'undefined') this.fields[i].tooltip.val(tooltip);
			
			// Params
			this.fields[i].params = this.fields[i].container.find(selector+'_params');
			this.fields[i].params.change(function() {
				$(this).removeClass('invalid');
				if ($(this).hasClass('pweb-validate-regexp') && this.value) {
					// validate RegExp
					try {
						var result = new RegExp(eval(this.value));
					} catch (err) {
						$(this).addClass('invalid');
						alert(Joomla.JText._('MOD_PWEBCONTACT_FIELD_VALIDATION_REGEXP_ERROR')+': '+err);
					}
				}
			});
			this.fields[i].paramsLabel = this.fields[i].container.find(selector+'_params-lbl');
			this.fields[i].paramsGroup = this.fields[i].params.parent().parent();
			if (typeof params !== 'undefined') this.fields[i].params.val(params);
			
			// Required
			this.fields[i].container.find(selector+'_required').addClass('btn-group');
			this.fields[i].required = this.fields[i].container.find(selector+'_required1');
			// Make selection of required radio button
			if (typeof required !== 'undefined' && required)
				this.fields[i].required[0].checked = true;
			
			// Remove field button
			this.fields[i].remove = this.fields[i].container.find('.pweb-action-remove');
			this.fields[i].remove.data('index', i).click(function(e) {
				e.preventDefault();
				
				var i = $(this).data('index'),
					name = that.fields[i].name.val();
				
				// Confirm removal of field
				if (confirm(Joomla.JText._('MOD_PWEBCONTACT_CONFIRM_FIELD_REMOVAL')+(name ? ': '+name : '')+'?')) {
					// destroy DOM element and empty array
					that.fields[i].container.remove();
					that.fields[i] = null;
					// remove field from sort list
					that.sort.sortable('refresh');
				}
			});
			
			// Toggle field button
			this.fields[i].toggle = this.fields[i].container.find('.pweb-action-toggle');
			this.fields[i].toggle.data('index', i).click(function(e) {
				if (e) e.preventDefault();
				
				var i = $(this).data('index'),
					hidden = that.fields[i].hidden;
				
				// Change icon
				$(this).find('i')[0].className = hidden ? 'icon-arrow-up' : 'icon-arrow-down';
				// Change styles for hidden field
				that.fields[i].container[hidden ? 'removeClass' : 'addClass']('pweb-draggable');
				that.fields[i].container[hidden ? 'removeClass' : 'addClass']('pweb-hidden');
				
				// Change state
				that.fields[i].hidden = !hidden;
			});
			
			// set Type field value
			if (typeof type !== 'undefined') {
				this.fields[i].type.val(type);
				this.changeFieldType(i, noDefault);
			}
			
			if (ordering && this.sort) 
			{		
				var order = this.sort.sortable('toArray');
				// Insert new field before upload field or system bottom if those fields where loaded
				if (order.length >= 3) {
					if (order[order.length-2] == this.sysFields.separator_upload[0].id) {
						this.fields[i].container.insertBefore(this.sysFields.separator_upload);
						inserted = true;
					}
					else if (order[order.length-1] == this.sysFields.separator_system_bottom[0].id 
							|| order[order.length-2] == this.sysFields.separator_system_bottom[0].id) {
						
						this.fields[i].container.insertBefore(this.sysFields.separator_system_bottom);
						inserted = true;
					}
				}
			}
			
			// Insert new field
			if (!inserted) this.fields[i].container.appendTo(this.$fields);
			
			// Load UI
			this.mootoolsTips(this.fields[i].container[0]);
			if (this.options.jversion >= 3) {
				this.isisUI(selector);
			}
			
			// Add field to sort list
			if (this.sort) this.sort.sortable('refresh');
			
			// Set focus on new field name
			if (typeof name === 'undefined') {
				this.fields[i].name.focus();
			}
			
			return this.fields[i];
		},
		
		addSeparator: function(type, name, ordering, append)
		{
			if (typeof ordering === 'undefined') ordering = false;
			if (typeof append === 'undefined') append = true;
			
			var that = this,
				inserted = false,
				i = this.fields.length,
				selector = '#'+this.id+'_'+i,
				html = this.$element.find('.template_separator').html().replace(/pwebx/g, i);
						
			// Add new item to fields array
			this.fields[i] = {
				container: $(html)
			};
			this.fields[i].container.data('index', i);
			
			// Type
			this.fields[i].type = this.fields[i].container.find(selector+'_type');
			this.fields[i].type.val(type);
			
			// Label
			this.fields[i].container.find(selector+'_name-lbl')
				.attr('title', '::'+Joomla.JText._('MOD_PWEBCONTACT_FIELD_'+type.toUpperCase()+'_DESC'))
				.find('span').text(Joomla.JText._('MOD_PWEBCONTACT_FIELD_'+type.toUpperCase()));
			
			// Name
			if (type == 'separator_text') {
				this.fields[i].name = this.fields[i].container.find(selector+'_name');
				if (typeof name !== 'undefined') {
					this.fields[i].name.val(name);
				}
				else {
					// Set focus on Name field for new fields
					this.fields[i].name.focus();
				}
			} else {
				this.fields[i].container.find(selector+'_name').remove();
			}
			
			if (type == 'separator_text' || type == 'separator_column' || type == 'separator_page')
			{
				// Remove field button
				this.fields[i].remove = this.fields[i].container.find('.pweb-action-remove');
				this.fields[i].remove.data('index', i).click(function(e) {
					e.preventDefault();
					
					var i = $(this).data('index');
										
					// Confirm removal of field
					if (confirm(Joomla.JText._('MOD_PWEBCONTACT_CONFIRM_FIELD_REMOVAL')+'?')) {
						// destroy DOM element and empty array
						that.fields[i].container.remove();
						that.fields[i] = null;
						// remove field from sort list
						that.sort.sortable('refresh');
					}
				});
						
				if (ordering && this.sort) 
				{		
					var order = this.sort.sortable('toArray');
					// Insert new field before upload field or system bottom if those fields where loaded
					if (order.length >= 3) {
						if (order[order.length-2] == this.sysFields.separator_upload[0].id) {
							this.fields[i].container.insertBefore(this.sysFields.separator_upload);
							inserted = true;
						}
						else if (order[order.length-1] == this.sysFields.separator_system_bottom[0].id 
								|| order[order.length-2] == this.sysFields.separator_system_bottom[0].id) {
							
							this.fields[i].container.insertBefore(this.sysFields.separator_system_bottom);
							inserted = true;
						}
					}
				}
			} else {
				this.fields[i].container.find('.pweb-action-remove').remove();
				this.sysFields[type] = this.fields[i].container;
			}
			
			// Insert new field 
			if (!inserted) this.fields[i].container[append ? 'appendTo' : 'prependTo'](this.$fields);
			
			// Load UI
			this.mootoolsTips(this.fields[i].container[0]);
			
			// Add field to sort list
			if (this.sort) this.sort.sortable('refresh');
			
			return this.fields[i];
		},
		
		changeFieldType: function(i, noDefault) 
		{
			var type = this.fields[i].type[0].value,
				labelText = '',
				labelTitle = '',
				fieldClass = '',
				regexpValidation = false,
				hidden = false;
				
			if (typeof noDefault === 'undefined') noDefault = false;
			
			if (!noDefault) {
				// Clear field value
				if (type != 'date' && this.fields[i].params[0].value.indexOf('%') == 0) {
					this.fields[i].params[0].value = '';
				}
				if (type != 'text' && type != 'name' && type != 'phone' && type != 'subject' && type != 'password' && this.fields[i].params[0].value.indexOf('/') == 0) {
					this.fields[i].params[0].value = '';
				}
				if (type != 'select' && this.fields[i].params[0].value.indexOf('MOD_PWEBCONTACT_SELECT') == 0) {
					this.fields[i].params[0].value = '';
				}
			}
	
			
			// Set field label, tooltip, value, validation
			if (type == 'checkboxes' || type == 'radio') {
				labelText = Joomla.JText._('MOD_PWEBCONTACT_FIELD_COLUMNS');
				labelTitle = '::'+Joomla.JText._('MOD_PWEBCONTACT_FIELD_COLUMNS_DESC');
				fieldClass = 'pweb-params-columns';
			}
			else if (type == 'textarea') {
				labelText = Joomla.JText._('MOD_PWEBCONTACT_FIELD_ROWS_CHARS_LIMIT');
				labelTitle = '::'+Joomla.JText._('MOD_PWEBCONTACT_FIELD_ROWS_CHARS_LIMIT_DESC');
				fieldClass = 'pweb-params-rows-charslimit';
				if (!noDefault && !this.fields[i].params[0].value) {
					this.fields[i].params[0].value = '5|1000';
				}
			}
			else if (type == 'select') {
				labelText = Joomla.JText._('MOD_PWEBCONTACT_FIELD_SELECT_DEFAULT_OPTION');
				labelTitle = '::'+Joomla.JText._('MOD_PWEBCONTACT_FIELD_SELECT_DEFAULT_OPTION_DESC');
				fieldClass = 'pweb-params-select';
				if (!noDefault && !this.fields[i].params[0].value) {
					this.fields[i].params[0].value = 'MOD_PWEBCONTACT_SELECT';
				}
			}
			else if (type == 'multiple') {
				labelText = Joomla.JText._('MOD_PWEBCONTACT_FIELD_ROWS');
				labelTitle = '::'+Joomla.JText._('MOD_PWEBCONTACT_FIELD_ROWS_DESC');
				fieldClass = 'pweb-params-rows';
			}
			else if (type == 'date' || type == 'date_range') {
				labelText = Joomla.JText._('MOD_PWEBCONTACT_FIELD_DATE_FORMAT');
				labelTitle = '::'+Joomla.JText._('MOD_PWEBCONTACT_FIELD_DATE_FORMAT_DESC');
				fieldClass = 'pweb-params-dateformat';
				if (!noDefault && !this.fields[i].params[0].value) {
					this.fields[i].params[0].value = '%d-%m-%Y';
				}
			}
			else if (type == 'checkbox') {
				labelText = Joomla.JText._('MOD_PWEBCONTACT_FIELD_RULES_URL');
				labelTitle = '::'+Joomla.JText._('MOD_PWEBCONTACT_FIELD_RULES_URL_DESC');
				fieldClass = 'pweb-params-rulesurl';
			}
			else if (type == 'email') {
				this.fields[i].params[0].value = '';
				fieldClass = 'pweb-params-hidden';
				hidden = true;	
				if (!noDefault && !this.fields[i].name[0].value) {
					this.fields[i].name[0].value = 'MOD_PWEBCONTACT_EMAIL';
				}
				if (!this.fields[i].alias[0].value) {
					this.fields[i].alias[0].value = 'email_'+i;
				}
			}
			else {
				labelText = Joomla.JText._('MOD_PWEBCONTACT_FIELD_VALIDATION');
				labelTitle = '::'+Joomla.JText._('MOD_PWEBCONTACT_FIELD_VALIDATION_DESC');
				fieldClass = 'pweb-params-regexp';
				regexpValidation = true;
				
				if (!noDefault) {
					// Clear regular expression if not valid
					if (this.fields[i].params[0].value && this.fields[i].params[0].value.indexOf('/') != 0) {
						this.fields[i].params[0].value = '';
					}
					
					if (type == 'name') {
						if (!this.fields[i].name[0].value) {
							this.fields[i].name[0].value = 'MOD_PWEBCONTACT_NAME';
						}
						if (!this.fields[i].alias[0].value) {
							this.fields[i].alias[0].value = type;
						}
					}
					else if (type == 'phone') {
						if (!this.fields[i].name[0].value) {
							this.fields[i].name[0].value = 'MOD_PWEBCONTACT_PHONE';
						}
						if (!this.fields[i].alias[0].value) {
							this.fields[i].alias[0].value = type;
						}
						if (!this.fields[i].params[0].value) {
							this.fields[i].params[0].value = '/[\\d\\-\\+() ]+/';
						}
					}
					else if (type == 'subject') {
						if (!this.fields[i].name[0].value) {
							this.fields[i].name[0].value = 'MOD_PWEBCONTACT_SUBJECT';
						}
						if (!this.fields[i].alias[0].value) {
							this.fields[i].alias[0].value = type;
						}
					}
				}
			}
			
			// Change field options
			if (!this.fields[i].paramsGroup.hasClass(fieldClass)) {
				
				// Turn on/off RegExp validation
				this.fields[i].params[regexpValidation ? 'addClass' : 'removeClass']('pweb-validate-regexp');
				// Remove invalid state
				this.fields[i].params.removeClass('invalid');
				// Hide/Show params row
				this.fields[i].paramsGroup.css('display', hidden ? 'none' : '');
				// Set type of params row
				this.fields[i].paramsGroup[0].className = 'control-group '+fieldClass;
				
				// Change params label
				if (!hidden && !this.fields[i].paramsLabel.hasClass(fieldClass)) {
					// Set params label
					this.fields[i].paramsLabel.find('span').text(labelText);
					// Create tooltip for params label
					this.fields[i].paramsLabel.attr('title', labelTitle);
					this.fields[i].paramsLabel[0].className = 'pweb-hasTip '+fieldClass;
					this.mootoolsTips(this.fields[i].paramsLabel.parent()[0], false);
				}
			}
		},
		
		scrollToElementEdge: function(element)
		{
			var pos = element.offset().top;
			var maxPos = pos + element.outerHeight() - $(window).height();
			if (pos > maxPos) pos = maxPos;
			$('html, body').animate({ scrollTop: pos }, 500);
		},
		
		mootoolsTips: function(element, attach)
		{
			// Mootools Tips
			if (typeof Tips === 'undefined') return;
			if (typeof attach === 'undefined') attach = true;
			
			// Enbale tooltips for new field
			var elements = document.id(element).getElements('.pweb-hasTip');
			elements.each(function(el) {
				var title = el.get('title');
				if (title) {
					var parts = title.split('::', 2);
					el.store('tip:title', parts[0]);
					el.store('tip:text', parts[1]);
					el.set('title', '');
				}
			});
			
			// Attach elements to tooltips
			if (attach) {
				this.tooltips.attach(elements);
			}
		},
		
		isisUI: function(selector)
		{
			// Turn radios into btn-group for new field
			$(selector+' .radio.btn-group label').addClass('btn');
			$(selector+' .btn-group label:not(.active)').click(function()
			{
				var label = $(this);
				var input = $('#' + label.attr('for'));
		
				if (!input.prop('checked')) {
					label.closest('.btn-group').find('label').removeClass('active btn-success btn-danger btn-primary');
					if (input.val() == '') {
						label.addClass('active btn-primary');
					} else if (input.val() == 0) {
						label.addClass('active btn-danger');
					} else {
						label.addClass('active btn-success');
					}
					input.prop('checked', true);
				}
			});
			$(selector+' .btn-group input:checked').each(function()
			{
				if ($(this).val() == '') {
					$('label[for=' + $(this).attr('id') + ']').addClass('active btn-primary');
				} else if ($(this).val() == 0) {
					$('label[for=' + $(this).attr('id') + ']').addClass('active btn-danger');
				} else {
					$('label[for=' + $(this).attr('id') + ']').addClass('active btn-success');
				}
			});
			
			// Turn drop-down lists for new field
			if (typeof $.fn.chosen === 'function')
			{
				$(selector+' select').chosen({
					disable_search_threshold : 50,
					allow_single_deselect : true
				});
			}
		}
		
		};
	})();

	pwebFields.options = pwebFields.prototype.options;

	$.fn.pwebFields = function(options) {
		return new pwebFields(this, options);
	};
	
})(window.jQuery);