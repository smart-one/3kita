<?php
/**
* @version 3.2.5
* @package PWebContact
* @copyright © 2014 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
* @license GNU General Public License http://www.gnu.org/licenses/gpl-3.0.html
* @author Piotr Moćko
*/

defined('_JEXEC') or die('Restricted access');

$user = JFactory::getUser();

$separators = 0;
$column 	= 0;
$page 		= 0;
$pages 		= array(array(null));

$toggler = 
	 '<div id="pwebcontact'.$module->id.'_toggler" class="pwebcontact'.$module->id.'_toggler pwebcontact_toggler pweb-closed '.$params->get('togglerClass').'">'
	.'<span class="pweb-text">'.(!$params->get('toggler_vertical', 0) ? $params->get('toggler_name_open') : ' ').'</span>'
	.'<span class="pweb-icon"></span>'
	.'</div>';
	
$message =
	 '<div class="pweb-msg pweb-msg-'.$params->get('msg_position', 'after').'"><div id="pwebcontact'.$module->id.'_msg" class="pweb-progress">'
	.'<script type="text/javascript">document.getElementById("pwebcontact'.$module->id.'_msg").innerHTML="'.JText::_('MOD_PWEBCONTACT_INIT').'"</script>'
	.'</div></div>';

?>
<!-- PWebContact -->

<?php if ($params->get('toggler_position') == 'static') : ?>
<div class="<?php echo $moduleClass; ?>" dir="<?php echo $params->get('rtl', 0) ? 'rtl' : 'ltr'; ?>">
	<?php echo $toggler; ?>
</div>
<?php endif; ?>

<div id="pwebcontact<?php echo $module->id; ?>" class="pwebcontact <?php echo $positionClass.' '.$moduleClass; ?>" dir="<?php echo $params->get('rtl', 0) ? 'rtl' : 'ltr'; ?>">
	
	<?php if ($params->get('toggler_position') == 'fixed') echo $toggler; ?>
	
	<?php if ($layout == 'modal') : ?><div id="pwebcontact<?php echo $module->id; ?>_modal" class="pwebcontact-modal modal hide fade" style="display:none"><?php endif; ?>
	<div id="pwebcontact<?php echo $module->id; ?>_box" class="pwebcontact-box <?php echo $moduleClass.' '.$params->get('boxClass'); ?>" dir="<?php echo $params->get('rtl', 0) ? 'rtl' : 'ltr'; ?>">
	<div id="pwebcontact<?php echo $module->id; ?>_container" class="pwebcontact-container">
	
		<?php 
		if ($params->get('toggler_position') == 'slide') echo $toggler;
		
		if ($layout == 'accordion' OR ($layout == 'modal' AND !$params->get('modal_disable_close', 0))) : ?>
		<button type="button" class="pwebcontact<?php echo $module->id; ?>_toggler pweb-button-close" aria-hidden="true"<?php if ($value = $params->get('toggler_name_close')) echo ' title="'.$value.'"' ?> data-role="none">&times;</button>
		<?php endif; ?>
		
		<?php if ($layout == 'accordion') : ?><div class="pweb-arrow"></div><?php endif; ?>
		
		<form name="pwebcontact<?php echo $module->id; ?>_form" id="pwebcontact<?php echo $module->id; ?>_form" class="pwebcontact-form" action="<?php echo JUri::getInstance()->toString(); ?>" method="post" accept-charset="utf-8">
			
			<?php if ($params->get('msg_position', 'after') == 'before') echo $message; ?>
			
			<div class="pweb-fields">
			<?php 
			
			/* ----- Form --------------------------------------------------------------------------------------------- */
			foreach ($fields as $field) :
			
				/* ----- Separators ----- */
				if ($field->type == 'separator_column') : 
					$column++;
					$pages[$page][$column] = null;
					
				elseif ($field->type == 'separator_page') : 
					$page++;
					$column = 0;
					$pages[$page] = array(null);
				
				
				else :
					
					ob_start();
					
					/* ----- Text separator --------------------------------------------------------------------------- */
					if ($field->type == 'separator_text') : 
						$fieldId = 'pwebcontact'.$module->id.'_text-'.$separators++;
					?>
					<div class="pweb-field-container pweb-separator-text" id="<?php echo $fieldId; ?>">
						<?php echo JText::_($field->name); ?>
					</div>
					<?php 
					
					
					/* ----- System top ------------------------------------------------------------------------------- */
					elseif ($field->type == 'separator_system_top') :
						
						/* ----- Acymailing Mail to list -------------------------------------------------------------- */
						if (($acymailing = strtolower($params->get('acymailing_mailto'))) == 'all' OR strpos($acymailing, ',') !== false) :
							
							$lists 		=  modPWebContactAcymailingHelper::getMailto($acymailing);
							$fieldId 	= 'pwebcontact'.$module->id.'_acymailing_mailto';
					?>
					<div class="pweb-field-container pweb-field-select pweb-field-acymailing-mailto">
						<div class="pweb-label">
							<label for="<?php echo $fieldId; ?>" id="<?php echo $fieldId; ?>-lbl">
								<?php echo JText::_('MOD_PWEBCONTACT_MAILTO'); ?>
								<span class="pweb-asterisk">*</span>
							</label>
						</div>
						<div class="pweb-field">
							<select name="acymailing_mailto" id="<?php echo $fieldId; ?>" class="required" data-role="none">
								<option value=""><?php echo JText::_('MOD_PWEBCONTACT_SELECT'); ?></option>
							<?php foreach ($lists as $list) : ?>
								<option value="<?php echo $list->id; ?>"><?php echo $list->name; ?></option>
							<?php endforeach; ?>
							</select>
						</div>
					</div>
					<?php 
						endif;
						
						/* ----- Mail to list ------------------------------------------------------------------------- */
						if ($params->get('email_to_list')) : 
							
							$optValues = @explode(PHP_EOL, $params->get('email_to_list'));
							if (count($optValues)) :
								
								$fieldId 	= 'pwebcontact'.$module->id.'_mailto';
								$i 			= 1;
					?>
					<div class="pweb-field-container pweb-field-select pweb-field-mailto">
						<div class="pweb-label">
							<label for="<?php echo $fieldId; ?>" id="<?php echo $fieldId; ?>-lbl">
								<?php echo JText::_('MOD_PWEBCONTACT_MAILTO'); ?>
								<span class="pweb-asterisk">*</span>
							</label>
						</div>
						<div class="pweb-field">
							<select name="mailto" id="<?php echo $fieldId; ?>" class="required" data-role="none">
								<option value=""><?php echo JText::_('MOD_PWEBCONTACT_SELECT'); ?></option>
							<?php foreach ($optValues as $value) : 
								// Skip empty rows
								if (empty($value)) continue;
								// Get recipient
								$recipient = @explode('|', $value);
								// Skip incorrect rows
								if (!array_key_exists(1, $recipient)) continue;
							?>
								<option value="<?php echo $i++; ?>"><?php echo $recipient[1]; ?></option>
							<?php endforeach; ?>
							</select>
						</div>
					</div>
					<?php 
							endif;
						endif;
					
					
					/* ----- System bottom ---------------------------------------------------------------------------- */
					elseif ($field->type == 'separator_system_bottom') :
						
						/* ----- Captcha ------------------------------------------------------------------------------ */
						if ($captcha_plugin = $params->get('captcha', 0)) : 
							
							$fieldId = 'pwebcontact'.$module->id.'_captcha';
							// reCaptcha fix - load Mootools
							if ($captcha_plugin == 'recaptcha') JHtml::_('behavior.framework');
					?>
					<div class="pweb-field-container pweb-field-captcha">
						<div class="pweb-label">
							<label for="<?php echo $fieldId; ?>" id="<?php echo $fieldId; ?>-lbl">
								<?php echo JText::_('MOD_PWEBCONTACT_CAPTCHA'); ?>
								<span class="pweb-asterisk">*</span>
							</label>
						</div>
						<div class="pweb-field pweb-captcha">
							<?php echo $captcha->display('captcha', $fieldId, 'pweb-input required'); ?>
						</div>
					</div>
					<?php 
						endif;
					
						/* ----- Acymailing subscribe ----------------------------------------------------------------- */
						if (($acymailing = strtolower($params->get('acymailing_subscribe'))) == 'all' OR strpos($acymailing, ',') !== false) : 
							
							$fieldId = 'pwebcontact'.$module->id.'_acymailing_subscribe';
					?>
					<div class="pweb-field-container pweb-field-checkbox pweb-field-acymailing-subscribe">
						<div class="pweb-field">
							<input type="checkbox" name="acymailing_subscribe" id="<?php echo $fieldId; ?>" value="1" class="pweb-checkbox" <?php if ($params->get('acymailing_subscribe_checked')) echo 'checked="checked"'; ?> data-role="none">
							<label for="<?php echo $fieldId; ?>">
								<?php echo JText::_($params->get('acymailing_subscribe_label', 'MOD_PWEBCONTACT_SUBSCRIBE_TO_NEWSLETTER')); ?>
							</label>
						</div>
					</div>
					<?php 
						endif;
						
						/* ----- Email copy --------------------------------------------------------------------------- */
						if ($params->get('email_copy', 0)) : 
							
							$fieldId = 'pwebcontact'.$module->id.'_copy';
					?>
					<div class="pweb-field-container pweb-field-checkbox pweb-field-copy">
						<div class="pweb-field">
							<input type="checkbox" name="copy" id="<?php echo $fieldId; ?>" value="1" class="pweb-checkbox" data-role="none">
							<label for="<?php echo $fieldId; ?>" id="<?php echo $fieldId; ?>-lbl">
								<?php echo JText::_('MOD_PWEBCONTACT_EMAIL_COPY'); ?>
							</label>
						</div>
					</div>
					<?php 
						endif;
					
					
					/* ----- Upload ----------------------------------------------------------------------------------- */
					elseif ($field->type == 'separator_upload') :
						
						if ($params->get('show_upload', 0)) : 
						
							$fieldId = 'pwebcontact'.$module->id.'_uploader';
							
							$field->attributes = null;
							$field->class = null;
							$field->title = array();
							if ($params->get('upload_show_limits')) {
								$exts = @explode('|', $params->get('upload_allowed_ext'));
								$types = array();
								foreach ($exts as $ext) {
									$tmp = @explode('?', $ext);
									$types[] = $tmp[0];
									if (array_key_exists(1, $tmp)) $types[] = $tmp[0].$tmp[1];
								}
								$field->title[] = JText::sprintf('MOD_PWEBCONTACT_UPLOAD_LIMITS', 
									floatval($params->get('upload_size_limit', 1)).'MB',
									intval($params->get('upload_files_limit', 5)),
									implode(', ', $types)
								);
							}
							if ($value = $params->get('upload_tooltip')) {
								$field->title[] = JText::_($value);
							}
							if (count($field->title)) {
								$field->class = ' pweb-tooltip';
								$field->attributes .= ' title="'.htmlspecialchars(implode(' ', $field->title), ENT_COMPAT, 'UTF-8').'"';
							}
					?>
					<div class="pweb-field-container pweb-field-uploader">
						<div class="pweb-label">
							<label for="<?php echo $fieldId; ?>" id="<?php echo $fieldId; ?>-lbl">
								<?php echo JText::_($params->get('upload_label', 'MOD_PWEBCONTACT_ATTACHMENT')); ?>
								<?php if ($params->get('show_upload', 0) == 2) : ?><span class="pweb-asterisk">*</span><?php endif; ?>
							</label>
						</div>
						<div class="pweb-field pweb-uploader" id="<?php echo $fieldId; ?>_container">
							<div class="fileupload-buttonbar">
								<span class="fileinput-button btn<?php echo $field->class; ?>"<?php echo $field->attributes; ?>>
				                    <i class="icon-plus-sign icon-white"></i>
				                    <span><?php echo JText::_('MOD_PWEBCONTACT_ADD_FILES'); ?></span>
				                    <input type="file" name="files[]" multiple="multiple" id="<?php echo $fieldId; ?>"<?php if ($params->get('show_upload', 0) == 2) echo ' class="pweb-validate-uploader"'; ?> data-role="none">
				                </span>
							</div>
							<div class="files"></div>
							<div class="templates" style="display:none" aria-hidden="true">
								<div class="template-upload fade">
									<span class="ready"><i class="icon-upload"></i></span>
									<span class="warning"><i class="icon-warning-sign"></i></span>
				                	<span class="name"></span>
				                	<span class="size"></span>
				                	<span class="error invalid"></span>
				                	<a href="#" class="cancel"><i class="icon-remove"></i><?php echo JText::_('MOD_PWEBCONTACT_CANCEL'); ?></a>
				                	<div class="progress progress-striped active"><div class="bar progress-bar" style="width:0%"></div></div>
				                </div>
								<div class="template-download fade">
									<span class="success"><i class="icon-ok"></i></span>
									<span class="warning"><i class="icon-warning-sign"></i></span>
				                	<span class="name"></span>
				                    <span class="size"></span>
				                    <span class="error invalid"></span>
				                    <a href="#" class="delete"><i class="icon-trash"></i><?php echo JText::_('MOD_PWEBCONTACT_DELETE'); ?></a>
				                </div>
							</div>
						</div>
					</div>
					<?php 
						endif;
					
					
					/* ----- Fields ----------------------------------------------------------------------------------- */
					else : 
						
						$fieldId = 'pwebcontact'.$module->id.'_field-'.$field->alias;
						$fieldName = 'fields['.$field->alias.']';
					?>
					<div class="pweb-field-container pweb-field-<?php echo $field->type; ?> pweb-field-<?php echo $field->alias; ?>">
						<?php 
						
						if ($field->type != 'checkbox') : 
						/* ----- Label -------------------------------------------------------------------------------- */ ?>
						<div class="pweb-label">
							<label id="<?php echo $fieldId; ?>-lbl"<?php if ($field->type != 'checkboxes' AND $field->type != 'radio') echo ' for="'.$fieldId.'"'; ?>>
								<?php echo JText::_($field->name); ?>
								<?php if ($field->required) : ?><span class="pweb-asterisk">*</span><?php endif; ?>
							</label>
						</div>
						<?php endif; ?>
						<div class="pweb-field">
							<?php 
							
							
							/* ----- Text fields: text, name, email, phone, subject, password, date ------------------------- */
							if (in_array($field->type, array('text', 'name', 'email', 'phone', 'subject', 'password', 'date'))) : 
								
								if ($user->id AND ($field->type == 'name' OR $field->type == 'email') AND $params->get('user_data', 1)) {
									$field->values = $user->{$field->type};
								}
								
								$field->attributes = null;
								$field->classes = array('pweb-input');
								if ($field->required) 
									$field->classes[] = 'required';
								
								if ($field->params AND $field->type != 'email') 
									$field->classes[] = 'pweb'.$module->id.'-validate-'.$field->alias;
								
								if ($field->tooltip) {
									$field->classes[] = 'pweb-tooltip';
									$field->attributes .= ' title="'.htmlspecialchars($field->tooltip, ENT_COMPAT, 'UTF-8').'"';
								}
	
								if (count($field->classes))
									$field->attributes .= ' class="'.implode(' ', $field->classes).'"';
								
								switch ($field->type) {
									case 'email':
										$field->classes[] = 'email';
										$type = 'email';
										break;
									case 'password':
										$type = 'password';
										break;
									case 'phone':
										$type = 'tel';
										break;
									default:
										$type = 'text';
								}
							?>
							<input type="<?php echo $type; ?>" name="<?php echo $fieldName; ?>" id="<?php echo $fieldId; ?>"<?php echo $field->attributes; ?> value="<?php echo htmlspecialchars($field->values, ENT_COMPAT, 'UTF-8'); ?>" data-role="none">
							<?php if ($field->type == 'date') : ?>
							<span class="pweb-calendar-btn" id="<?php echo $fieldId; ?>_btn"><i class="icon-calendar"></i></span>
							<?php endif;
							
							
							/* ----- Textarea ------------------------------------------------------------------------- */
							elseif ($field->type == 'textarea') :
								$field->attributes = null;
								$field->classes = array();
								$field->maxlength = 0;
								
								$field->params = is_array($field->params) ? $field->params : @explode('|', $field->params);
								$field->attributes .= ' rows="'.(array_key_exists(0, $field->params) ? (int)$field->params[0] : 5).'"';
								if (array_key_exists(1, $field->params) AND (int)$field->params[1] > 0) {
									$field->maxlength = (int)$field->params[1];
									$field->attributes .= ' maxlength="'.$field->maxlength.'"';
								}
								if ($field->required) 
									$field->classes[] = 'required';
								
								if ($field->tooltip) {
									$field->classes[] = 'pweb-tooltip';
									$field->attributes .= ' title="'.htmlspecialchars($field->tooltip, ENT_COMPAT, 'UTF-8').'"';
								}
								if (count($field->classes))
									$field->attributes .= ' class="'.implode(' ', $field->classes).'"';
							?>
							<textarea name="<?php echo $fieldName; ?>" id="<?php echo $fieldId; ?>" cols="50"<?php echo $field->attributes; ?> data-role="none"><?php echo htmlspecialchars($field->values, ENT_COMPAT, 'UTF-8'); ?></textarea>
							<?php if ($field->maxlength) : ?>
							<div class="pweb-chars-counter"><?php echo JText::sprintf('MOD_PWEBCONTACT_CHARS_LEFT', '<span id="'.$fieldId.'-limit">'.$field->maxlength.'</span>'); ?></div>
							<?php endif; ?>	
							<?php 
							
							
							/* ----- Select and Multiple select ------------------------------------------------------- */
							elseif ($field->type == 'select' OR $field->type == 'multiple') : 
								$optValues = is_array($field->values) ? $field->values : @explode('|', $field->values);
								$field->attributes = null;
								$field->classes = array();
								
								if ($field->required) 
									$field->classes[] = 'required';
								
								if ($field->type == 'multiple') 
								{
									$field->classes[] = 'pweb-multiple';
									$fieldName 		 .= '[]';
									
									$optCount 		= count($optValues);
									$field->params 	= $field->params ? (int)$field->params : 5;
									$field->params 	= $field->params > $optCount ? $optCount : $field->params;
									
									$field->attributes .= ' multiple="multiple" size="'.$field->params.'"';
								}
								else {
									$field->classes[] = 'pweb-select';
								}
								
								if ($field->tooltip) {
									$field->classes[] = 'pweb-tooltip';
									$field->attributes .= ' title="'.htmlspecialchars($field->tooltip, ENT_COMPAT, 'UTF-8').'"';
								}
								
								if (count($field->classes))
									$field->attributes .= ' class="'.implode(' ', $field->classes).'"';
							?>
							<select name="<?php echo $fieldName; ?>" id="<?php echo $fieldId; ?>"<?php echo $field->attributes; ?> data-role="none">
							<?php if ($field->type == 'select' AND $field->params) : ?>
								<option value=""><?php echo JText::_($field->params); ?></option>
							<?php endif; ?>
							<?php foreach ($optValues as $value) : ?>
								<option value="<?php echo htmlspecialchars($value, ENT_COMPAT, 'UTF-8'); ?>"><?php echo JText::_($value); ?></option>
							<?php endforeach; ?>
							</select>
							<?php 
							
							
							/* ----- Checkboxes and Radio group ------------------------------------------------------- */
							elseif ($field->type == 'checkboxes' OR $field->type == 'radio') : 
								$i 			= 0;
								
								$type 		= $field->type == 'checkboxes' ? 'checkbox' : 'radio';
								$optValues 	= is_array($field->values) ? $field->values : @explode('|', $field->values);
								
								$optCount 	= count($optValues);
								$optColumns = (int)$field->params;
								$optRows	= false;
								if ($optColumns > 1 AND $optCount >= $optColumns) 
								{
									$optCount 	= count($optValues);
									$optRows 	= ceil($optCount / $optColumns);
									$width 		= floor(100 / $optColumns);
									$cols 		= 1;
								}
								if ($field->type == 'checkboxes') 
									$fieldName .= '[]';
							?>
							<fieldset id="<?php echo $fieldId; ?>" class="pweb-fields-group<?php if ($field->tooltip) echo ' pweb-tooltip" title="'.htmlspecialchars($field->tooltip, ENT_COMPAT, 'UTF-8'); ?>">
							<?php 
							/* ----- Options in multiple columns ----- */
							if ($optRows) : ?>
							<div class="pweb-column pweb-width-<?php echo $width; ?>">
							<?php foreach ($optValues as $value) : ?>
								<input type="<?php echo $type; ?>" name="<?php echo $fieldName; ?>" id="<?php echo $fieldId.'_'.$i; ?>" value="<?php echo htmlspecialchars($value, ENT_COMPAT, 'UTF-8'); ?>" class="pweb-<?php echo $type; ?> pweb-fieldset<?php if ($i == 0 AND $field->required) echo ' required'; ?>" data-role="none">
								<label for="<?php echo $fieldId.'_'.$i++; ?>">
									<?php echo JText::_($value); ?>
								</label>
							<?php // Column separator
							if (($i % $optRows) == 0 AND $cols < $optColumns) : $cols++; ?>
							</div><div class="pweb-column pweb-width-<?php echo $width; ?>">
							<?php endif;
							endforeach; ?>
							</div>
							<?php 
							/* ----- Options in one column ----- */
							else :
							foreach ($optValues as $value) : ?>
								<input type="<?php echo $type; ?>" name="<?php echo $fieldName; ?>" id="<?php echo $fieldId.'_'.$i; ?>" value="<?php echo htmlspecialchars($value, ENT_COMPAT, 'UTF-8'); ?>" class="pweb-<?php echo $type; ?> pweb-fieldset<?php if ($i == 0 AND $field->required) echo ' required'; ?>" data-role="none">
								<label for="<?php echo $fieldId.'_'.$i++; ?>">
									<?php echo JText::_($value); ?>
								</label>
							<?php endforeach;
							endif; ?>
							</fieldset>
							<?php 
							
							
							/* ----- Single checkbox ------------------------------------------------------------------ */
							elseif ($field->type == 'checkbox') : ?>
								<input type="checkbox" name="<?php echo $fieldName; ?>" id="<?php echo $fieldId; ?>" class="pweb-checkbox pweb-single-checkbox<?php if ($field->required) echo ' required'; ?>" value="<?php echo $field->values ? htmlspecialchars($field->values, ENT_COMPAT, 'UTF-8') : 'JYes'; ?>" data-role="none">
								<label for="<?php echo $fieldId; ?>" id="<?php echo $fieldId; ?>-lbl"<?php if ($field->tooltip) echo ' class="pweb-tooltip" title="'.htmlspecialchars($field->tooltip, ENT_COMPAT, 'UTF-8').'"'; ?>>
								<?php if ($field->params) : ?>
									<a href="<?php echo $field->params; ?>" target="_blank"><?php echo JText::_($field->name); ?> <span class="icon-out"></span></a>
								<?php else : 
									echo JText::_($field->name); 
								endif; ?>
								<?php if ($field->required) : ?>
									<span class="pweb-asterisk">*</span>
								<?php endif; ?>
								</label>
							<?php endif; ?>
						</div>
					</div>
					<?php endif;
				
					$pages[$page][$column] .= ob_get_clean(); 
				
				endif;
			endforeach; 
			
			
			/* ----- Buttons ------------------------------------------------------------------------------------------ */
				ob_start(); ?>
					<div class="pweb-field-container pweb-field-buttons">
						<div class="pweb-field">
							<button id="pwebcontact<?php echo $module->id; ?>_send" type="button" class="btn" data-role="none"><?php echo JText::_($params->get('button_send', 'MOD_PWEBCONTACT_SEND')) ?></button>
							<?php if ($params->get('reset_form', 1) == 3) : ?>
							<button id="pwebcontact<?php echo $module->id; ?>_reset" type="reset" class="btn" style="display:none" data-role="none"><i class="icon-remove-sign icon-white"></i> <?php echo JText::_($params->get('button_reset', 'MOD_PWEBCONTACT_RESET')) ?></button>
							<?php endif; ?>
							<?php if ($params->get('msg_position', 'after') == 'button' OR $params->get('msg_position', 'after') == 'popup') echo $message; ?>
						</div>
					</div>
			<?php 
				$pages[$page][$column] .= ob_get_clean();
	
	
			/* ----- Display form pages and columns ------------------------------------------------------------------- */
				$pages_count = count($pages);
				foreach ($pages as $page => $columns) 
				{
					if ($pages_count > 1) echo '<div class="pweb-page" id="pwebcontact'.$module->id.'_page-'.$page.'">';
					
					$width = floor(100 / count($columns));
					foreach ($columns as $column) 
					{
						if ($width < 100) 
							echo '<div class="pweb-column pweb-width-'.$width.'">'.$column.'</div>';
						else
							echo $column;
					}
	
					if ($pages_count > 1) echo '</div>';
				}
				
			/* ----- Display pages navigation ------------------------------------------------------------------------- */
				if ($pages_count > 1) : ?>
					<div class="pweb-pagination">
						<button id="pwebcontact<?php echo $module->id; ?>_prev" class="btn pweb-prev" type="button" data-role="none"><span class="icon-chevron-left"></span> <?php echo JText::_('MOD_PWEBCONTACT_PREV'); ?></button>
						<div class="pweb-counter">
							<span id="pwebcontact<?php echo $module->id; ?>_page_counter">1</span>
							<?php echo JText::_('MOD_PWEBCONTACT_OF'); ?>
							<span><?php echo $pages_count; ?></span>
						</div>
						<button id="pwebcontact<?php echo $module->id; ?>_next" class="btn pweb-next" type="button" data-role="none"><?php echo JText::_('MOD_PWEBCONTACT_NEXT'); ?> <span class="icon-chevron-right"></span></button>
					</div>
				<?php endif;
			?>
			</div>
			
			<?php if ($params->get('msg_position', 'after') == 'after') echo $message; ?>
			
			<?php echo modPwebcontactHelper::getHiddenFields(); ?>
			<input type="hidden" name="<?php echo JSession::getFormToken(); ?>" value="1" id="pwebcontact<?php echo $module->id; ?>_token">
		</form>
		
		<?php if ($params->get('show_upload', 0)) : ?>
		<div class="pweb-dropzone" aria-hidden="true"><div><?php echo JText::_('MOD_PWEBCONTACT_DROP_FILES'); ?></div></div>
		<?php endif; ?>
	
	</div>
	</div>
	<?php if ($layout == 'modal') : ?></div><?php endif; ?>
</div>

<script type="text/javascript">
<?php echo $script; ?>
</script>
<?php if ($params->get('user_data', 1) AND $user->id) echo '<!-- {emailcloak=off} -->'; ?>
<!-- PWebContact end -->