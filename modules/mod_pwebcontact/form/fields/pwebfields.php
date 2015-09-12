<?php
/**
* @version 3.2.4
* @package PWebContact
* @copyright © 2014 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
* @license GNU General Public License http://www.gnu.org/licenses/gpl-3.0.html
* @author Piotr Moćko
*/

defined('_JEXEC') or die( 'Restricted access' );

/**
 * Custom fields
 */
class JFormFieldPwebFields extends JFormField
{
	protected $type = 'PwebFields';
	
	
	protected function getInput()
	{
		if (class_exists('JHtmlJquery')) 
		{
			JHtml::_('stylesheet', 'jui/icomoon.css', array(), true);
			
			JHtml::_('jquery.framework');
			JHtml::_('jquery.ui', array('core', 'sortable'));
			
			JText::script('MOD_PWEBCONTACT_FIELD_VALIDATION');
			JText::script('MOD_PWEBCONTACT_FIELD_VALIDATION_DESC');
			JText::script('MOD_PWEBCONTACT_FIELD_VALIDATION_REGEXP_ERROR');
			JText::script('MOD_PWEBCONTACT_FIELD_DATE_FORMAT');
			JText::script('MOD_PWEBCONTACT_FIELD_DATE_FORMAT_DESC');
			JText::script('MOD_PWEBCONTACT_FIELD_RULES_URL');
			JText::script('MOD_PWEBCONTACT_FIELD_RULES_URL_DESC');
			JText::script('MOD_PWEBCONTACT_FIELD_COLUMNS');
			JText::script('MOD_PWEBCONTACT_FIELD_COLUMNS_DESC');
			JText::script('MOD_PWEBCONTACT_FIELD_ROWS');
			JText::script('MOD_PWEBCONTACT_FIELD_ROWS_DESC');
			JText::script('MOD_PWEBCONTACT_FIELD_SELECT_DEFAULT_OPTION');
			JText::script('MOD_PWEBCONTACT_FIELD_SELECT_DEFAULT_OPTION_DESC');
			JText::script('MOD_PWEBCONTACT_FIELD_ROWS_CHARS_LIMIT');
			JText::script('MOD_PWEBCONTACT_FIELD_ROWS_CHARS_LIMIT_DESC');
			
			JText::script('MOD_PWEBCONTACT_FIELD_SEPARATOR_TEXT');
			JText::script('MOD_PWEBCONTACT_FIELD_SEPARATOR_TEXT_DESC');
			JText::script('MOD_PWEBCONTACT_FIELD_SEPARATOR_COLUMN');
			JText::script('MOD_PWEBCONTACT_FIELD_SEPARATOR_COLUMN_DESC');
			JText::script('MOD_PWEBCONTACT_FIELD_SEPARATOR_PAGE');
			JText::script('MOD_PWEBCONTACT_FIELD_SEPARATOR_PAGE_DESC');
			JText::script('MOD_PWEBCONTACT_FIELD_SEPARATOR_UPLOAD');
			JText::script('MOD_PWEBCONTACT_FIELD_SEPARATOR_UPLOAD_DESC');
			JText::script('MOD_PWEBCONTACT_FIELD_SEPARATOR_SYSTEM_TOP');
			JText::script('MOD_PWEBCONTACT_FIELD_SEPARATOR_SYSTEM_TOP_DESC');
			JText::script('MOD_PWEBCONTACT_FIELD_SEPARATOR_SYSTEM_BOTTOM');
			JText::script('MOD_PWEBCONTACT_FIELD_SEPARATOR_SYSTEM_BOTTOM_DESC');
			
			JText::script('MOD_PWEBCONTACT_CONFIRM_FIELD_REMOVAL');
		}
		
		JText::script('MOD_PWEBCONTACT_ERROR_FIELDS_SORTING');
		
		$doc = JFactory::getDocument();
		$doc->addScript(JUri::root(true).'/media/mod_pwebcontact/js/jquery.admin.fields.js'); 
		$doc->addScriptDeclaration(
			 'if(typeof jQuery!=="undefined")'
			.'jQuery(document).ready(function($){'
				.'new pwebFields($("#'.$this->id.'"),{jversion:'.floatval(JVERSION).'})'
			.'});'
		);
		
		$value = json_decode($this->value);
		if (is_array($value) AND count($value) <= 3) {
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('MOD_PWEBCONTACT_ERROR_ADD_FIELDS'), 'error');
		}
		
		//TODO enable adding new pages
		return 
		
'
<div id="'.$this->id.'">
	
	<input type="hidden" name="'.$this->name.'" class="data" value="'.htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8').'">
	
	<div class="pweb-fields-tip badge badge-important" id="'.$this->id.'_error">'.JText::_('MOD_PWEBCONTACT_ERROR_FIELDS_JQUERY').'</div>
	<script type="text/javascript">if(typeof jQuery!=="undefined")document.getElementById("'.$this->id.'_error").style.display="none"</script>
	
	<div class="pweb-fields-tip">'.JText::_('MOD_PWEBCONTACT_FIELDS_TIP').'</div>
	
	<div class="pweb-actions">
		<button type="button" class="pweb-action-add btn btn-small btn-primary"><i class="icon-new"></i> '.JText::_('MOD_PWEBCONTACT_ADD_FIELD').'</button> 
		<button type="button" class="pweb-action-addtext btn btn-small hasTip" title="::'.JText::_('MOD_PWEBCONTACT_FIELD_SEPARATOR_TEXT_DESC').'"><i class="icon-comment"></i> '.JText::_('MOD_PWEBCONTACT_ADD_SEPARATOR_TEXT').'</button> 
		<button type="button" class="pweb-action-addcolumn btn btn-small hasTip" title="::'.JText::_('MOD_PWEBCONTACT_FIELD_SEPARATOR_COLUMN_DESC').'"><i class="icon-grid-view"></i> '.JText::_('MOD_PWEBCONTACT_ADD_SEPARATOR_COLUMN').'</button> 
		<!--<button type="button" class="pweb-action-addpage btn btn-small hasTip" title="::'.JText::_('MOD_PWEBCONTACT_FIELD_SEPARATOR_PAGE_DESC').'"><i class="icon-file-add"></i> '.JText::_('MOD_PWEBCONTACT_ADD_SEPARATOR_PAGE').'</button>--> 
		<button type="button" class="pweb-action-toggleall btn btn-small hasTip" title="::'.JText::_('MOD_PWEBCONTACT_TOGGLE_FIELDS_DESC').'"><i class="icon-arrow-up"></i> '.JText::_('MOD_PWEBCONTACT_TOGGLE_FIELDS').'</button> 
		<button type="button" class="pweb-action-sample btn btn-small hasTip" title="::'.JText::_('MOD_PWEBCONTACT_LOAD_SAMPLE_FIELDS_DESC').'"><i class="icon-cube"></i> '.JText::_('MOD_PWEBCONTACT_LOAD_SAMPLE_FIELDS').'</button> 
	</div>
	
	<div class="pweb-container"></div>
	
	<div class="template_separator" style="display:none" hidden>
	<div class="control-container pweb-group-name pweb-separator pweb-draggable" id="'.$this->id.'_pwebx">
		<div class="control-group">
			<div class="control-label">
				<label for="'.$this->id.'_pwebx_name" id="'.$this->id.'_pwebx_name-lbl" class="pweb-hasTip"><span></span> <i class="icon-question-sign"></i></label>
			</div>
			<div class="controls">
				<textarea name="'.$this->formControl.'[fields][pwebx][name]" id="'.$this->id.'_pwebx_name" cols="50" rows="5" class="input-xxlarge"></textarea>
				<input type="hidden" name="'.$this->formControl.'[fields][pwebx][type]" id="'.$this->id.'_pwebx_type">
				<a href="#" title="'.JText::_('MOD_PWEBCONTACT_REMOVE_FIELD').':: " class="pweb-btn pweb-action-remove pweb-hasTip"><i class="icon-remove"></i>'.JText::_('MOD_PWEBCONTACT_REMOVE').'</a>
			</div>
		</div>
	</div>
	</div>
		
	<div class="template_field" style="display:none" hidden>
	<div class="control-container pweb-field" id="'.$this->id.'_pwebx">
		<div class="control-group pweb-group-name">
			<div class="control-label">
				<label for="'.$this->id.'_pwebx_name" id="'.$this->id.'_pwebx_name-lbl" class="pweb-hasTip" title="::'.JText::_('MOD_PWEBCONTACT_FIELD_LABEL_DESC').'">'.JText::_('MOD_PWEBCONTACT_FIELD_LABEL').' <i class="icon-question-sign"></i></label>
			</div>
			<div class="controls">
				<input type="text" name="'.$this->formControl.'[fields][pwebx][name]" id="'.$this->id.'_pwebx_name" size="40">
				<a href="#" title="'.JText::_('MOD_PWEBCONTACT_TOGGLE_FIELD').'::" class="pweb-btn pweb-action-toggle pweb-hasTip"><i class="icon-arrow-up"></i></a>
				<a href="#" title="'.JText::_('MOD_PWEBCONTACT_REMOVE_FIELD').'::" class="pweb-btn pweb-action-remove pweb-hasTip"><i class="icon-remove"></i> '.JText::_('MOD_PWEBCONTACT_REMOVE').'</a>
			</div>
		</div>
		<div class="pweb-subcontainer">
			<div class="pweb-subcontainer-tip"></div>
			<div class="control-group">
				<div class="control-label">
					<label for="'.$this->id.'_pwebx_alias" id="'.$this->id.'_pwebx_alias-lbl" class="pweb-hasTip" title="::'.JText::_('MOD_PWEBCONTACT_FIELD_ALIAS_DESC').'">'.JText::_('MOD_PWEBCONTACT_FIELD_ALIAS').' <i class="icon-question-sign"></i></label>
				</div>
				<div class="controls">
					<input type="text" name="'.$this->formControl.'[fields][pwebx][alias]" id="'.$this->id.'_pwebx_alias" size="50">
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<label for="'.$this->id.'_pwebx_type" id="'.$this->id.'_pwebx_type-lbl">'.JText::_('MOD_PWEBCONTACT_FIELD_TYPE').'</label>
				</div>
				<div class="controls">
					<select name="'.$this->formControl.'[fields][pwebx][type]" id="'.$this->id.'_pwebx_type">
						<option value="text">'.JText::_('MOD_PWEBCONTACT_FIELD_TYPE_TEXT').'</option>
						<option value="email">'.JText::_('MOD_PWEBCONTACT_FIELD_TYPE_EMAIL').'</option>
						<option value="name">'.JText::_('MOD_PWEBCONTACT_FIELD_TYPE_NAME').'</option>
						<option value="phone">'.JText::_('MOD_PWEBCONTACT_FIELD_TYPE_PHONE').'</option>
						<option value="subject">'.JText::_('MOD_PWEBCONTACT_FIELD_TYPE_SUBJECT').'</option>
						<option value="textarea">'.JText::_('MOD_PWEBCONTACT_FIELD_TYPE_TEXTAREA').'</option>
						<option value="select">'.JText::_('MOD_PWEBCONTACT_FIELD_TYPE_SELECT').'</option>
						<option value="multiple">'.JText::_('MOD_PWEBCONTACT_FIELD_TYPE_MULTIPLE').'</option>
						<option value="radio">'.JText::_('MOD_PWEBCONTACT_FIELD_TYPE_RADIO').'</option>
						<option value="checkboxes">'.JText::_('MOD_PWEBCONTACT_FIELD_TYPE_CHECKBOXES').'</option>
						<option value="checkbox">'.JText::_('MOD_PWEBCONTACT_FIELD_TYPE_CHECKBOX').'</option>
						<option value="date">'.JText::_('MOD_PWEBCONTACT_FIELD_TYPE_DATE').'</option>
						<option value="password">'.JText::_('MOD_PWEBCONTACT_FIELD_TYPE_PASSWORD').'</option>
					</select>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<label for="'.$this->id.'_pwebx_values" id="'.$this->id.'_pwebx_values-lbl" class="pweb-hasTip" title="::'.JText::_('MOD_PWEBCONTACT_FIELD_VALUES_DESC').'">'.JText::_('MOD_PWEBCONTACT_FIELD_VALUES').' <i class="icon-question-sign"></i></label>
				</div>
				<div class="controls">
					<input type="text" name="'.$this->formControl.'[fields][pwebx][values]" id="'.$this->id.'_pwebx_values" class="input-xxlarge" size="60">
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<label for="'.$this->id.'_pwebx_tooltip" id="'.$this->id.'_pwebx_tooltip-lbl">'.JText::_('MOD_PWEBCONTACT_FIELD_TOOLTIP').'</label>
				</div>
				<div class="controls">
					<input type="text" name="'.$this->formControl.'[fields][pwebx][tooltip]" id="'.$this->id.'_pwebx_tooltip" size="50">
				</div>
			</div>
			<div class="control-group pweb-params-regexp">
				<div class="control-label">
					<label for="'.$this->id.'_pwebx_params" id="'.$this->id.'_pwebx_params-lbl" class="pweb-hasTip pweb-params-regexp" title="::'.JText::_('MOD_PWEBCONTACT_FIELD_VALIDATION_DESC').'"><span>'.JText::_('MOD_PWEBCONTACT_FIELD_VALIDATION').'</span> <i class="icon-question-sign"></i></label>
				</div>
				<div class="controls">
					<input type="text" name="'.$this->formControl.'[fields][pwebx][params]" id="'.$this->id.'_pwebx_params" class="pweb-validate-regexp" size="50">
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<label for="'.$this->id.'_pwebx_required" id="'.$this->id.'_pwebx_required-lbl">'.JText::_('MOD_PWEBCONTACT_FIELD_REQUIRED').'</label>
				</div>
				<div class="controls">
					<fieldset class="radio" id="'.$this->id.'_pwebx_required">
						<input type="radio" name="'.$this->formControl.'[fields][pwebx][required]" id="'.$this->id.'_pwebx_required0" value="0" checked="checked">
						<label for="'.$this->id.'_pwebx_required0">'.JText::_('JNO').'</label>
						<input type="radio" name="'.$this->formControl.'[fields][pwebx][required]" id="'.$this->id.'_pwebx_required1" value="1">
						<label for="'.$this->id.'_pwebx_required1">'.JText::_('JYES').'</label>
					</fieldset>
				</div>
			</div>
		</div>
	</div>
	</div>
</div>';
	}
	
	
	protected function getLabel()
	{
		return null;
	}
}