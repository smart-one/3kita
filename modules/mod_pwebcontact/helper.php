<?php
/**
* @version 3.2.8.1
* @package PWebContact
* @copyright © 2014 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
* @license GNU General Public License http://www.gnu.org/licenses/gpl-3.0.html
* @author Piotr Moćko
*/

// no direct access
defined('_JEXEC') or die('Restricted access');


require_once (dirname(__FILE__).'/helpers/comprofiler.php');
require_once (dirname(__FILE__).'/helpers/jomsocial.php');
require_once (dirname(__FILE__).'/helpers/sobipro.php');
require_once (dirname(__FILE__).'/helpers/zoo.php');

require_once (dirname(__FILE__).'/helpers/uploader.php');


class modPwebcontactHelper
{
	// current module ID
	protected static $module_id 	= 0;
	// multiple instances
	protected static $params 		= array();
	protected static $fields 		= array();
	// only one instance
	protected static $data 			= array();
	protected static $email_tmpls 	= array();
	protected static $email_vars 	= array();
	protected static $logs 			= array();
	
	protected static $sys_info 		= null;
	protected static $loaded 		= array(
										'text' 			=> false,
										'uploader_text' => false,
										'debug_js' 		=> false,
										'ie_css' 		=> false
									);


	public static function setLog($log) 
	{
		return self::$logs[] = $log;
	}
	

	public static function setParams(&$params) 
	{
		self::$module_id = (int)$params->get('id');
		self::$params[self::$module_id] = $params;
	}


	public static function getParams($module_id = 0) 
	{
		$module_id = $module_id ? $module_id : self::$module_id;
		if (!isset(self::$params[$module_id]))
		{
			jimport('joomla.registry.registry');
		
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->select('params')
				  ->from('#__modules')
				  ->where('id='.(int)$module_id)
				  ;
			$db->setQuery($query);
			
			try {
				$params_str = $db->loadResult();
			} catch (RuntimeException $e) {
				$params_str = null;
				if (defined('PWEBCONTACT_DEBUG') AND PWEBCONTACT_DEBUG) 
					self::setLog('Database query error: '.$e->getMessage());
			}
			
			$params = new JRegistry($params_str);
			$params->def('id', (int)$module_id);
				
			self::$params[$module_id] = $params;
			
			if (!self::$module_id AND $module_id) self::$module_id = $module_id;
		}
		return self::$params[$module_id];
	}


	public static function getFields($module_id = 0) 
	{
		$module_id = $module_id ? $module_id : self::$module_id;
		if (!isset(self::$fields[$module_id])) 
		{
			$params = self::getParams();
			self::$fields[$module_id] = json_decode($params->get('fields', '[]'));
		}
		return self::$fields[$module_id];
	}


	public static function initCssClassess()
	{
		$params = self::getParams();
		$layout = $params->get('layout_type', 'slidebox');
		
		$positionClasses = $togglerClasses = $boxClasses = array();
		$moduleClasses = array(
			'pweb-'.$layout,
			'pweb-labels-'.$params->get('labels_position', 'inline')
		);
		
		if (($class = $params->get('style_bg', 'white')) != -1) $moduleClasses[] = 'pweb-bg-'.$class;
		if (($class = $params->get('style_form', 'blue')) != -1) $moduleClasses[] = 'pweb-form-'.$class;
		
		if ($layout != 'static') 
		{
			if (in_array($layout, array('slidebox', 'modal')))
			{
				$positionClasses[] = 'pweb-'.$params->get('position', 'left');
				$positionClasses[] = 'pweb-offset-'.$params->get('offset_position', 'top');
				if ($params->get('toggler_vertical')) {
					$moduleClasses[] = 'pweb-vertical';
					if ($params->get('toggler_rotate', 1) == -1) $togglerClasses[] = 'pweb-rotate';
				} else {
					$moduleClasses[] = 'pweb-horizontal';
				}
				
				if ($layout == 'slidebox')
				{
					if (!$params->get('show_toggler', 1) AND $params->get('toggler_position') == 'fixed') $moduleClasses[] = 'pweb-toggler-hidden';
					if ($params->get('toggler_slide')) $moduleClasses[] = 'pweb-toggler-slide';
					if (!$params->get('debug')) $boxClasses[] = 'pweb-init';
				}
			}
			elseif ($layout == 'accordion') 
			{
				if ($params->get('accordion_boxed', 1)) $boxClasses[] = 'pweb-accordion-boxed';
				if (!$params->get('debug')) $boxClasses[] = 'pweb-init';
			}
			
			if (($class = $params->get('style_toggler', 'blue')) != -1) $togglerClasses[] = 'pweb-toggler-'.$class;
			if ($icon = $params->get('toggler_icon')) $togglerClasses[] = 'pweb-icon pweb-icon-'.$icon;
		}
		
		if ($icon = $params->get('icons', 'icomoon')) $moduleClasses[] = 'pweb-'.$icon;
		if ($params->get('rounded')) $moduleClasses[] = $togglerClasses[] = 'pweb-radius';
		if ($params->get('shadow')) $moduleClasses[] = $togglerClasses[] = 'pweb-shadow';
		if ($params->get('rtl', 0)) $moduleClasses[] = $togglerClasses[] = 'pweb-rtl';
		if ($params->get('user_data', 1) == 2) {
			$user = JFactory::getUser();
			if ($user->id) $moduleClasses[] = 'pweb-hide-user';
		}
		if ($moduleclass_sfx = $params->get('moduleclass_sfx')) {
			$moduleclasses_sfx = explode(' ', $moduleclass_sfx);
			for ($i = 0; $i < count($moduleclasses_sfx); $i++) 
				if (strpos($moduleclasses_sfx[$i], 'icon-') !== false) 
					unset($moduleclasses_sfx[$i]);
			$moduleClasses[] = $togglerClasses[] = htmlspecialchars(implode(' ', $moduleclasses_sfx));
		}
		
		$params->def('positionClass', implode(' ', $positionClasses));
		$params->def('togglerClass', implode(' ', $togglerClasses));
		$params->def('moduleClass', implode(' ', $moduleClasses));
		$params->def('boxClass', implode(' ', $boxClasses));
	}


	public static function getCssDeclaration()
	{
		$params 		= self::getParams();
		$module_id 		= (int)$params->get('id');
		$media_url 		= $params->get('media_url');
		$layout 		= $params->get('layout_type', 'slidebox');
		$css 			= null;
		$declarations 	= array();


		// Position offset
		if ($value = $params->get('offset'))
			$css .= '#pwebcontact'.$module_id.'{'.$params->get('offset_position', '').':'.$value.'}';
		
		
		// Layer level
		if ($value = (int)$params->get('zindex')) {
			// Slide box and Lightbox toggler
			$css .=  '#pwebcontact'.$module_id.'.pweb-left,'
					.'#pwebcontact'.$module_id.'.pweb-right,'
					.'#pwebcontact'.$module_id.'.pweb-top,'
					.'#pwebcontact'.$module_id.'.pweb-bottom'
					.'{z-index:'.$value.'}';
			// Lightbox window
			if ($layout == 'modal' AND $value > 1030) {
				$css .= '.pweb-modal-open .modal-backdrop{z-index:'.($value+10).'}';
				$css .= '.pwebcontact-modal.modal{z-index:'.($value+20).'}';
				$css .= '.ui-effects-transfer.pweb-genie{z-index:'.($value+19).'}';
			}
			// Calendar
			if ($value+20 >= 10000) {
				self::$loaded['calendar_zindex'] = $value+30;
				$css .= 'body div.calendar{z-index:'.($value+30).'}';
			}
			
		}
		
		
		if ($layout == 'slidebox' OR (in_array($layout, array('accordion', 'modal')) AND $params->get('show_toggler', 1)) )
		{
			// Toggler
			if ($value = $params->get('toggler_color'))
				$declarations[] = 'color:'.$value;
			if ($value = $params->get('toggler_bg')) {
				$declarations[] = 'background-image:none';
				$declarations[] = 'background-color:'.$value;
				$declarations[] = 'border-color:'.$value;
			}
			if ($value = $params->get('toggler_font_size'))
				$declarations[] = 'font-size:'.$value;
			if ($value = $params->get('toggler_font_family'))
				$declarations[] = 'font-family:'.$value;
			if ($value = $params->get('toggler_width'))
				$declarations[] = 'width:'.(int)$value.'px';
			if ($value = $params->get('toggler_height'))
				$declarations[] = 'height:'.(int)$value.'px';
			if (count($declarations)) {
				$css .= '#pwebcontact'.$module_id.'_toggler{'.implode(';', $declarations).'}';
				$declarations = array();
			}
			
			// Toggler icon
			if ($params->get('toggler_icon') == 'gallery') {
				if ($value = $params->get('toggler_icon_gallery'))
					$css .= '#pwebcontact'.$module_id.'_toggler .pweb-icon{background-image:url('.$media_url.'images/icons/'.rawurlencode($value).')}';
			}
			elseif ($params->get('toggler_icon') == 'custom') {
				if ($value = $params->get('toggler_icon_custom'))
					$css .= '#pwebcontact'.$module_id.'_toggler .pweb-icon{background-image:url('.JUri::base(true).'/'.implode('/', array_map('rawurlencode', explode('/', urldecode($value)))).')}';
			}
			elseif ($params->get('toggler_icon') == 'icomoon') {
				if ($value = $params->get('toggler_icomoon'))
					$css .= '#pwebcontact'.$module_id.'_toggler .pweb-icon:before{content:"\\'.$value.'"}';
			}
				
			// Toggler vertical text
			if ($params->get('toggler_vertical'))
			{
				$lang_code = JFactory::getLanguage()->getTag();
				$toggler_dir  = '/cache/mod_pwebcontact/';
				$toggler_file = 'toggler-'.$module_id.'-'.$lang_code.'-'
					.md5(
						 (int)$params->get('toggler_width', 30)
						.(int)$params->get('toggler_height', 120)
						.(int)$params->get('toggler_font_size', 12)
						.(int)$params->get('toggler_rotate', 1)
						.$params->get('toggler_font', 'NotoSans-Regular')
						.$params->get('toggler_color')
						.$params->get('toggler_name')
						.$params->get('style_toggler', 'blue')
					)
					.'.png';
				
				if (!file_exists(JPATH_ROOT.$toggler_dir.$toggler_file)) 
					self::createToggleImage(JPATH_ROOT.$toggler_dir, $toggler_file);
				
				//$css .= '#pwebcontact'.$module_id.'_toggler .pweb-text{background-image:url('.JUri::base(true).$toggler_dir.$toggler_file.')}';
				$css .= '#pwebcontact'.$module_id.'_toggler .pweb-text{background-image:url(data:image/png;base64,'
						.base64_encode(file_get_contents(JPATH_ROOT.$toggler_dir.$toggler_file))
						.')}';
			}
		}
		
		
		// Form container
		if ($value = $params->get('form_font_size'))
			$declarations[] = 'font-size:'.$value;
		if ($value = $params->get('form_font_family'))
			$declarations[] = 'font-family:'.$value;
		if (count($declarations)) {
			$css .=  '#pwebcontact'.$module_id.'_box,'
					.'#pwebcontact'.$module_id.'_form label,'
					.'#pwebcontact'.$module_id.'_form input,'
					.'#pwebcontact'.$module_id.'_form textarea,'
					.'#pwebcontact'.$module_id.'_form select,'
					.'#pwebcontact'.$module_id.'_form button,'
					.'#pwebcontact'.$module_id.'_form .btn'
					.'{'.implode(';', $declarations).'}';
			$declarations = array();
		}
		
		if ($value = $params->get('text_color')) {
			$css .=  '#pwebcontact'.$module_id.'_form label,'
					.'#pwebcontact'.$module_id.'_form .pweb-separator-text,'
					.'#pwebcontact'.$module_id.'_form .pweb-msg,'
					.'#pwebcontact'.$module_id.'_form .pweb-chars-counter,'
					.'#pwebcontact'.$module_id.'_form .pweb-uploader,'
					.'#pwebcontact'.$module_id.'_box .pweb-dropzone'
					.'{color:'.$value.'}';
		}
		
		if ($value = $params->get('bg_color')) {
			if (($opacity = (float)$params->get('bg_opacity')) < 1) {
				$bg_color = self::parseToRgbColor($value);
				$value .= ';background-color:rgba('.$bg_color['r'].','.$bg_color['g'].','.$bg_color['b'].','.$opacity.')';
			}
			$container_bg = 'background-color:'.$value;
			$css .= '#pwebcontact'.$module_id.'_container{'.$container_bg.'}';
		}
		
		
		// Form width
		if ($value = $params->get('form_width')) {
			if ($layout != 'slidebox' OR strpos($value, 'px') !== false)
				$css .= '#pwebcontact'.$module_id.'_box{max-width:'.$value.'}';
		}
		// Labels width
		if ($params->get('labels_position', 'inline') == 'inline' AND ($value = (int)$params->get('labels_width'))) 
		{
			if ($value > 90) $value = 30;
			$css .= '#pwebcontact'.$module_id.'_box .pweb-label{width:'.$value.'%}';
			$css .= '#pwebcontact'.$module_id.'_box .pweb-field{width:'.(99.9-floatval($value)).'%}';	
		}
		
		// Message success and error
		if ($value = $params->get('msg_success_color'))
			$css .= '#pwebcontact'.$module_id.'_form .pweb-msg .pweb-success{color:'.$value.'}';
		if ($value = $params->get('msg_error_color'))
			$css .= '#pwebcontact'.$module_id.'_form .pweb-msg .pweb-error{color:'.$value.'}';
		
		
		// Buttons, fields, links
		if ($value = $params->get('buttons_fields_color')) {
			$declarations[] = 'background-image:none';
			$declarations[] = 'background-color:'.$value;
			$declarations[] = 'border-color:'.$value;
			
			$css .=  '#pwebcontact'.$module_id.'_container a,'
					.'#pwebcontact'.$module_id.'_container a:hover,'
					.'#pwebcontact'.$module_id.'_container .pweb-button-close'
					.'{color:'.$value.' !important}';
			
			$css .=  '#pwebcontact'.$module_id.'_form input.pweb-input,'
			 		.'#pwebcontact'.$module_id.'_form select,'
			 		.'#pwebcontact'.$module_id.'_form textarea{border-color:'.$value.'}';
			$css .=  '#pwebcontact'.$module_id.'_form input.pweb-input:focus,'
			 		.'#pwebcontact'.$module_id.'_form select:focus,'
			 		.'#pwebcontact'.$module_id.'_form textarea:focus'
			 		.'{border-color:'.$value.' !important}';
		}
		if ($value = $params->get('buttons_text_color'))
			$declarations[] = 'color:'.$value.' !important';
		if (count($declarations)) {
			$css .=  '#pwebcontact'.$module_id.'_form button,'
					.'#pwebcontact'.$module_id.'_form .btn'
					.'{'.implode(';', $declarations).'}';
			$declarations = array();
		}
		
		if ($layout == 'modal') 
		{
			// Modal backdrop
			if (($value = (float)$params->get('modal_opacity')) > 0) 
				$declarations[] = 'opacity:'.$value;
			if ($value = $params->get('modal_bg')) 
				$declarations[] = 'background-color:'.$value;
			if (count($declarations)) {
				$css .= '.pwebcontact'.$module_id.'_modal-open .modal-backdrop.fade.in{'.implode(';', $declarations).'}';
				$declarations = array();
			}
			
			// Modal transfer effect
			if (($value = (float)$params->get('modal_duration', 400)) !== 400) {
				$declarations[0] = 'animation-duration:'.$value.'ms';
				$declarations[] = '-o-'.$declarations[0];
				$declarations[] = '-ms-'.$declarations[0];
				$declarations[] = '-moz-'.$declarations[0];
				$declarations[] = '-webkit-'.$declarations[0];
			}
			if (isset($container_bg))
				$declarations[] = $container_bg;
			if (count($declarations)) {
				if (($class = $params->get('style_bg', 'white')) != -1) 
					$css .= '.pweb-bg-'.$class;
				$css .= '.ui-effects-transfer.pweb-genie.pwebcontact'.$module_id.'-genie{'.implode(';', $declarations).'}';
				$declarations = array();
			}
		}
		
		// Background image
		$declarations_mobile = array();
		if ($value = $params->get('bg_image')) {
			$declarations[] = 'background-image:url('.JUri::base(true).'/'.$value.')';
		}
		if ($value = $params->get('bg_position')) {
			if ($params->get('rtl') == 2) {
				if (strpos($value, 'left') !== false)
					$value = str_replace('left', 'right', $value);
				elseif (strpos($value, 'right') !== false)
					$value = str_replace('right', 'left', $value);
			}
			$declarations[] = 'background-position:'.$value;
		}
		if (($padding_position = $params->get('bg_padding_position')) AND ($padding = $params->get('bg_padding'))) {
			if ($params->get('rtl') == 2) {
				if ($padding_position == 'left')
					$padding_position = 'right';
				elseif ($padding_position == 'right')
					$padding_position = 'left';
			}
			$declarations[] = 'padding-'.$padding_position.':'.$padding;
			
			if (($padding_position == 'left' OR $padding_position == 'right')) {
				$padding_mobile = 10;
				if ($layout == 'slidebox' 
					AND ($params->get('position') == 'left' OR $params->get('position') == 'right') 
					AND $params->get('toggler_vertical') AND !$params->get('toggler_slide')) {
						$padding_mobile = 50;
				}
				if ($params->get('bg_image')) {
					$declarations_mobile[] = 'background-image:none';
				}
				$declarations_mobile[] = 'padding-'.$padding_position.':'.$padding_mobile.'px';
			}
		}
		if (count($declarations)) {
			$css .= '#pwebcontact'.$module_id.'_container{'.implode(';', $declarations).'}';
			if (count($declarations_mobile)) {
				$css .= '@media(max-width:480px){#pwebcontact'.$module_id.'_container{'.implode(';', $declarations_mobile).'}}';
			}
			$declarations = array();
		}


		// Accordion boxed with arrow
		if ($layout == 'accordion' AND $params->get('accordion_boxed', 1) AND $params->get('bg_color')) {
			$border_color = isset($bg_color) ? $bg_color : self::parseToRgbColor($params->get('bg_color'));
			foreach ($border_color as &$color) {
				$color -= 25; // 10% from 255
				if ($color < 0) $color = 0;
			}
			
			$declarations[0] = 'box-shadow:'.($params->get('shadow', 1) ? '0 0 4px rgba(0,0,0,0.5),' : '')
				.'inset 0 0 8px rgb('.$border_color['r'].','.$border_color['g'].','.$border_color['b'].')';
			$declarations[] = '-moz-'.$declarations[0];
			$declarations[] = '-webkit-'.$declarations[0];
			$declarations[] = 'border-color:rgb('.$border_color['r'].','.$border_color['g'].','.$border_color['b'].')';
			$css .= '#pwebcontact'.$module_id.'_container{'.implode(';', $declarations).'}';
			$css .= '#pwebcontact'.$module_id.'_box .pweb-arrow{border-bottom-color:rgb('.$border_color['r'].','.$border_color['g'].','.$border_color['b'].')}';
			$declarations = array();
		}


		// Disable Boostrap glyphicons
		if (!$params->get('boostrap_glyphicons', 1))
			$css .= '[class^="icon-"],[class*=" icon-"]{background-image:none !important}';


		return $css;
	}


	public static function initHeader() 
	{
		$doc 		= JFactory::getDocument();
		$params 	= self::getParams();
		$media_url 	= $params->get('media_url');
		$layout 	= $params->get('layout_type', 'slidebox');
		$debug 		= $params->get('debug');

		// jQuery and Bootstrap JS
		if ($params->get('load_jquery', 1)) {
			JHtml::_('jquery.framework');
			if ($params->get('load_bootstrap', 1)) {
				JHtml::_('bootstrap.framework');
			}
		}
		elseif ($params->get('load_bootstrap', 1)) {
			JHtml::_('script', 'jui/bootstrap.min.js', false, true, false, false, $debug);
		}
		
		// Bootstrap CSS
		if ($params->get('load_bootstrap_css', 2) == 1) {
			JHtml::_('stylesheet', 'jui/bootstrap.min.css', array(), true);
			if ($params->get('rtl', 0)) 
				JHtml::_('stylesheet', 'jui/bootstrap-rtl.css', array(), true);
		}
		elseif ($params->get('load_bootstrap_css', 2) == 2) {
			$doc->addStyleSheet($media_url.'css/bootstrap.css');
			if ($params->get('rtl', 0)) 
				$doc->addStyleSheet($media_url.'css/bootstrap-rtl.css');
		}


		// Toggler IcoMoon
		if ($params->get('load_icomoon', 1) AND $params->get('toggler_icon') == 'icomoon' AND $params->get('toggler_icomoon') AND $layout != 'static')
			$doc->addStyleSheet($media_url.'css/icomoon.css');


		// CSS layout
		$doc->addStyleSheet($media_url.'css/layout.css');
		$doc->addStyleSheet($media_url.'css/animations.css');

		// CSS RTL layout
		if ($params->get('rtl', 0))
			$doc->addStyleSheet($media_url.'css/layout-rtl.css');


		// CSS IE
		if (!self::$loaded['ie_css']) 
		{
			self::$loaded['ie_css'] = true;
			
			jimport('joomla.environment.browser');
			$browser = JBrowser::getInstance();
			
			if ($browser->getBrowser() == 'msie' AND (float)$browser->getMinor() < 9) 
			{
				if ((int)$browser->getMinor() >= 8)
					$doc->addCustomTag(
						 '<!--[if IE 8]>'."\r\n"
						.'<style type="text/css">'
							.'.pwebcontact-form .pweb-input,'
							.'.pwebcontact-form select,'
							.'.pwebcontact-form textarea,'
							.'.pwebcontact-form .btn'
							.'{behavior:url('.$media_url.'css/PIE.htc)}'
						.'</style>'."\r\n"
						.'<![endif]-->'
					);
				
				$doc->addCustomTag(
					 '<!--[if lt IE 9]>'."\r\n"
					.'<link rel="stylesheet" href="'.$media_url.'css/ie8.css" />'."\r\n"
					.'<style type="text/css">'
						.'.pwebcontact_toggler,'
						.'.pwebcontact-container'
						.'{behavior:url('.$media_url.'css/PIE.htc)}'
					.'</style>'."\r\n"
					.'<script src="'.JUri::base(true).'/media/jui/js/html5.js"></script>'."\r\n"
					.'<![endif]-->'
				);
			}
		}


		if ($params->get('show_upload', 0)) 
		{
			if ($params->get('icons', 'icomoon') == 'icomoon' AND $params->get('load_icomoon', 1))
				$doc->addStyleSheet($media_url.'css/icomoon.css');
			
			$doc->addStyleSheet($media_url.'css/uploader.css');
			if ($params->get('rtl', 0)) 
				$doc->addStyleSheet($media_url.'css/uploader-rtl.css');
			
			if ($params->get('load_jquery_fileupload', 1)) {
				
				if ($params->get('load_jquery_ui', 1)) {
					if ($params->get('load_jquery', 1))
						JHtml::_('jquery.ui', array('core'));
					else 
						JHtml::_('script', 'jui/jquery.ui.core.min.js', false, true, false, false, $debug);
					
					// load jQuery UI Widget if older than 1.9.2
					if (version_compare(JVERSION, '3.2.0') == -1)
						$doc->addScript($media_url.'js/jquery.ui.widget'.($debug ? '' : '.min').'.js');
				}
				
				if ($debug) {
					$doc->addScript($media_url.'js/jquery.iframe-transport.js');
					$doc->addScript($media_url.'js/jquery.fileupload.js');
					$doc->addScript($media_url.'js/jquery.fileupload-process.js');
					$doc->addScript($media_url.'js/jquery.fileupload-validate.js');
					$doc->addScript($media_url.'js/jquery.fileupload-ui.js');
				} else {
					$doc->addScript($media_url.'js/jquery.fileupload.min.js');
				}
			}
			
			if (!self::$loaded['uploader_text']) 
			{
				self::$loaded['uploader_text'] = true;
				
				JText::script('MOD_PWEBCONTACT_UPLOADING');
				JText::script('MOD_PWEBCONTACT_UPLOAD_ERR');
				JText::script('MOD_PWEBCONTACT_UPLOAD_BYTES_ERR');
				JText::script('MOD_PWEBCONTACT_UPLOAD_LIMIT_ERR');
				JText::script('MOD_PWEBCONTACT_UPLOAD_TYPE_ERR');
				JText::script('MOD_PWEBCONTACT_UPLOAD_SIZE_ERR');
			}
		}

		if ($layout == 'accordion' OR ($layout == 'modal' AND $params->get('modal_effect','square') != 'default'))
		{
			if ($params->get('load_jquery_ui_effects', 1)) {
				$doc->addScript($media_url.'js/jquery.ui.effects'.($debug ? '' : '.min').'.js');
			}
			if ($layout == 'modal') {
				$doc->addStyleSheet($media_url.'css/animations.css');
			}
		}

		if ($params->get('load_jquery_validate', 1)) 
			$doc->addScript($media_url.'js/jquery.validate'.($debug ? '' : '.min').'.js');
		
		//TODO chosen
		//JHtml::_('script', 'jui/chosen.jquery.min.js', false, true, false, false, $debug);
		//JHtml::_('stylesheet', 'jui/chosen.css', false, true);

		$doc->addScript($media_url.'js/jquery.pwebcontact'.(file_exists($params->get('media_path').'js/jquery.pwebcontact.js') ? '' : '.min').'.js');


		// CSS styles
		if (($file = $params->get('style_bg', 'white')) != -1)
			$doc->addStyleSheet($media_url.'css/background/'.$file.'.css');
		if (($file = $params->get('style_form', 'blue')) != -1)
			$doc->addStyleSheet($media_url.'css/form/'.$file.'.css');
		if (($layout =='slidebox' OR $params->get('show_toggler', 1)) AND ($file = $params->get('style_toggler', 'blue')) != -1)
			$doc->addStyleSheet($media_url.'css/toggler/'.$file.'.css');
		
		// Set custom styles
		if ($css = self::getCssDeclaration()) 
		{
			$path = JPATH_CACHE.'/mod_pwebcontact/';
			$file = md5($css).'.css';
			if ($params->get('cache_css', 1) AND !file_exists($path.$file)) 
			{
				// set write permissions to cache folder
				jimport('joomla.filesystem.file');
				jimport('joomla.filesystem.folder');
				if (!JFolder::exists($path)) {
					JFolder::create($path, 0777);
				}
				elseif (!is_writable($path) AND JPath::canChmod($path)) {
					JPath::setPermissions($path, null, '0777');
				}
				
				// write cache file
				if (!JFile::write($path.$file, $css))
					$params->set('cache_css', 0);
			}
			
			if ($params->get('cache_css', 1))
				$doc->addStyleSheet(JUri::base(true).'/cache/mod_pwebcontact/'.$file);
			else 
				$doc->addStyleDeclaration($css);
		}


		if (!self::$loaded['text']) 
		{
			self::$loaded['text'] = true;
			
			JText::script('MOD_PWEBCONTACT_INIT');
			JText::script('MOD_PWEBCONTACT_SENDING');
			JText::script('MOD_PWEBCONTACT_SEND_ERR');
			JText::script('MOD_PWEBCONTACT_REQUEST_ERR');
			JText::script('MOD_PWEBCONTACT_COOKIES_ERR');
		}
	}


	public static function getScript() 
	{
		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();
		
		$params = self::getParams();
		
		$module_id 	= (int)$params->get('id');
		$media_url 	= $params->get('media_url');
		$layout 	= $params->get('layout_type', 'slidebox');
		$position 	= $params->get('position', 'left');
		
		$options = array();	
		$options[] = 'id:'.$module_id;
		$options[] = 'layout:"'.$layout.'"';
		$options[] = 'position:"'.$position.'"';
		$options[] = 'offsetPosition:"'.$params->get('offset_position').'"';
		$options[] = 'basePath:"'.JUri::base(true).'"';
		if (is_file(JPATH_ROOT.'/components/com_ajax/ajax.php'))
			$options[] = 'ajaxUrl:"index.php?option=com_ajax&module=pwebcontact&Itemid='.$app->input->getInt('Itemid').'&method="';
		else
			$options[] = 'ajaxUrl:"'.($params->get('root_path', 0) ? 'mod_pwebcontact_' : 'modules/mod_pwebcontact/').'ajax.php?method="';
		
		if (($value = $params->get('msg_position', 'after')) != 'after')
			$options[] = 'msgPosition:"'.$value.'"';
		if (($value = (int)$params->get('msg_close_delay', 10)) != 10)
			$options[] = 'msgCloseDelay:'.$value;
		
		if ($params->get('debug', 0))
			$options[] = 'debug:1';
		
		if (($app->getCfg('caching') AND $params->get('cache', 0)) OR $params->get('cache', 0) == 2)
			$options[] = 'reloadToken:1';
		
		if ($params->get('rules_target', 1) == 2)
			$options[] = 'rulesModal:1';
		
		if (($value = (int)$params->get('tooltips', 3)) != 3)
			$options[] = 'tooltips:'.$value;
		
		if ($value = $params->get('toggler_name_close') AND !$params->get('toggler_vertical', 0))
			$options[] = 'togglerNameClose:"'.$value.'"';
		
		
		if (($open = (int)$params->get('open_toggler')) > 0)
		{
			$max_count = (int)$params->get('open_count');
			if ($max_count == 0) {
				$options[] = 'openAuto:'.$open;
			} elseif ($max_count > 0) {
				if ($params->get('open_counter_storage', 1) == 2) {
					// session
					$session = JFactory::getSession();
					if (($count = (int)$session->get('openauto', 0, 'pwebcontact'.$module_id)) < $max_count) {
						$session->set('openauto', ++$count, 'pwebcontact'.$module_id);
						$options[] = 'openAuto:'.$open;
					}
				} else {
					// cookie
					if ($params->get('load_jquery_cookie', 1)) 
						$doc->addScript($media_url.'js/jquery.cookie'.($params->get('debug') ? '' : '.min').'.js');
					
					$options[] = 'openAuto:'.$open;
					$options[] = 'maxAutoOpen:'.$max_count;
					if (($value = (int)$params->get('cookie_lifetime', 30)) != 30)
						$options[] = 'cookieLifetime:'.($value*3600*24);
					if (($value = $app->getCfg('cookie_path', JUri::base(true).'/')) != '/')
						$options[] = 'cookiePath:"'.$value.'"';
					if ($value = $app->getCfg('cookie_domain'))
						$options[] = 'cookieDomain:"'.$value.'"';
				}
			}
			if (($value = (int)$params->get('open_delay')) > 0) {
				$options[] = 'openDelay:'.$value;
			}
		}
		
		if ($params->get('close_toggler', 0))
			$options[] = 'closeAuto:1';
		if (($value = (int)$params->get('close_delay')) > 0)
			$options[] = 'closeDelay:'.$value;
		
		if (!$params->get('close_other', 1))
			$options[] = 'closeOther:0';
		
		// reset form after email has been sent
		if (($value = (int)$params->get('reset_form', 1)) != 1)
			$options[] = 'reset:'.$value;
		
		// redirect after email has been sent
		$redirect = false;
		if (($value = (int)$params->get('redirect_itemid', 0)) > 0) {
			$redirect = JRoute::_('index.php?Itemid='.$value, false);
		} elseif ($value = $params->get('redirect_url')) {
			$redirect = JRoute::_($value, false);
		}
		if ($redirect) {
			$options[] = 'redirectURL:"'.$redirect.'"';
			if (($value = (int)$params->get('redirect_delay', 3)) != 3) {
				$options[] = 'redirectDelay:'.$value;
			}
		}
		
		
		// On complete event
		$options2 = array();
		// Google AdWords Conversion Tracking
		if ($value = $params->get('adwords_url')) {
			$options2[] = '$("<img/>",{"src":"'.$value.'","width":1,"height":1,"border":0}).appendTo(this.Msg);';
		}
		// Microsoft adCenter Conversion Tracking
		if ($value = $params->get('adcenter_url')) {
			$options2[] = '$("<iframe/>",{"src":"'.$value.'","width":1,"height":1,"frameborder":0,"scrolling":"no"}).css({"visibility":"hidden","display":"none"}).appendTo(this.Msg);';
		}
		// After email sent success
		if ($value = $params->get('oncomplete')) {
			$options2[] = 'try{'.strip_tags($value)."\r\n".'}catch(e){this.debug(e)}';
		}
		if (count($options2)) $options[] = 'onComplete:function(data){'.implode('', $options2).'}';
		
		// On error event
		if ($value = $params->get('onerror')) {
			$options[] = 'onError:function(data){try{'.strip_tags($value)."\r\n".'}catch(e){this.debug(e)}}';
		}
		
		// On load, open and close events
		if ($value = $params->get('onload')) {
			$options[] = 'onLoad:function(){try{'.strip_tags($value)."\r\n".'}catch(e){this.debug(e)}}';
		}
		if ($value = $params->get('onopen')) {
			$options[] = 'onOpen:function(){try{'.strip_tags($value)."\r\n".'}catch(e){this.debug(e)}}';
		}
		if ($value = $params->get('onclose')) {
			$options[] = 'onClose:function(){try{'.strip_tags($value)."\r\n".'}catch(e){this.debug(e)}}';
		}
		
		
		// Uploader
		if ($params->get('show_upload', 0)) 
		{
			if ($value = $params->get('upload_allowed_ext'))
				$options[] = 'uploadAcceptFileTypes:/(\.|\/)('.$value.')$/i';
				
			// max file size in bytes
			if (($value = (float)$params->get('upload_size_limit', 1)) != 1) {
				$value *= 1024*1024;
				$options[] = 'uploadMaxSize:'.$value;
			}
			// max files limit
			if (($value = (int)$params->get('upload_files_limit', 5)) != 5)
				$options[] = 'uploadFilesLimit:'.$value;
			
			// start upload after file has been chosen
			if (!$params->get('upload_autostart', 1))
				$options[] = 'uploadAutoStart:0';
		}
		
		// Slide Box
		if ($layout == 'slidebox') 
		{
			// Form width
			if (($value = $params->get('form_width')) AND strpos($value, 'px') !== false)
				$options[] = 'slideWidth:'.(int)$value;
			if (($value = (int)$params->get('slide_duration')) > 0) 
				$options[] = 'slideDuration:'.$value;
			if (($value = $params->get('slide_transition')) != -1 AND $value != -2 AND $value) {
				$options[] = 'slideTransition:"'.$value.'"';
				if (strpos($value, 'ease') !== false AND $params->get('load_jquery_ui', 1)) {
					if ($params->get('load_jquery', 1))
						JHtml::_('jquery.ui', array('core'));
					else 
						JHtml::_('script', 'jui/jquery.ui.core.min.js', false, true, false, false, $params->get('debug'));
				}
			}
		}
		// Lightbox window
		else if ($layout == 'modal') 
		{
			if ($params->get('modal_opacity', -1) == 0) 
				$options[] = 'modalBackdrop:0';
			if ($params->get('modal_disable_close'))
				$options[] = 'modalClose:0';
			if (($value = $params->get('style_bg', 'white')) != -1)
				$options[] = 'modalStyle:"'.$value.'"';
			if (($value = $params->get('modal_duration', 400)) != 400)
				$options[] = 'modalEffectDuration:'.(int)$value;
			if (($value = $params->get('modal_effect', 'square')) != 'square')
				$options[] = 'modalEffect:"'.$value.'"';
		}
		
		// Custom validation rules and calendar fields
		$fields = self::getFields();
		$rules = $calendars = array();
		foreach ($fields as $field)
		{
			if (in_array($field->type, array('text', 'name', 'phone', 'subject', 'password'))) 
			{
				if ($field->params) 
				{
					$options2 = array('name:"'.$field->alias.'"', 'regexp:'.$field->params);
					$rules[] = '{'.implode(',',$options2).'}';
				}
			}
			elseif ($field->type == 'date')
			{
				$calendars[] = '{id:"'.$field->alias.'"'.($field->params ? ',format:"'.$field->params.'"' : '').'}';
			}
		}
		if (count($rules)) {
			$options[] = 'validatorRules:['.implode(',',$rules).']';
		}
		if (count($calendars)) 
		{
			JHtml::_('behavior.framework');
			JHtml::_('behavior.calendar');
			$options[] = 'calendars:['.implode(',',$calendars).']';
			if (($value = JFactory::getLanguage()->getFirstDay()) != 0)
				$options[] = 'calendarFirstDay:'.$value;
			
			if ($params->get('icons', 'icomoon') == 'icomoon' AND $params->get('load_icomoon', 1))
				$doc->addStyleSheet($media_url.'css/icomoon.css');
		}
		
		
		// JavaScript initialization
		$script = 
		'jQuery(document).ready(function($){'.
			'pwebContact'.$module_id.'=new pwebContact({'.implode(',', $options).'})'. 
		'});';
		
		
		if ($params->get('debug') AND !self::$loaded['debug_js'])
		{
			self::$loaded['debug_js'] = true;
			
			$script = 
			'jQuery(document).ready(function($){'.
				'if(typeof pwebContact'.$module_id.'Count=="undefined"){'.
					// Check if document header has been loaded
					'if(typeof pwebContact=="undefined")alert("PWeb debug: Contact form module has been loaded incorrect.'.
					(JPluginHelper::isEnabled('system', 'modulesanywhere') ? ' Do not use Modules Anywhere to load contact form inside template.' : '').'");'.
					// Check if one module instance has been loaded only once
					'pwebContact'.$module_id.'Count=$(".pwebcontact'.$module_id.'_form").length;'.
					'if(pwebContact'.$module_id.'Count>1)'.
						'alert("PWeb debug: Contact form module ID '.$module_id.' has been loaded "+pwebContact'.$module_id.'Count+" times. You can have multiple contact forms, but one instance of module can be loaded only once!")'.
				'}'.
			'});'.
			$script
			;
		}

		return $script;
	}


	protected static function createToggleImage($path = null, $file = null)
	{
		$params 		= self::getParams();
		
		$font_path 		= $params->get('media_path') . 'images/fonts/'.$params->get('toggler_font', 'NotoSans-Regular').'.ttf';
		$font_size 		= (int)$params->get('toggler_font_size', 12);
		
		if ($params->get('rtl')) {
			$text_open 	= self::utf8_strrev($params->get('toggler_name_open'));
			$text_close = self::utf8_strrev($params->get('toggler_name_close'));
		} else {
			$text_open 	= $params->get('toggler_name_open');
			$text_close = $params->get('toggler_name_close');
		}
		
		$length 		= strlen($text_open);
		if ($text_close AND strlen($text_close) > $length) $length = strlen($text_close);
		
		$width 			= $params->get('toggler_width', 30);
	    $height 		= is_numeric($params->get('toggler_height')) ? $params->get('toggler_height') : $length * $font_size / 1.2;
		
		$rotate 		= (int)$params->get('toggler_rotate', 1);
		
		// Parse font color
		if ($color = $params->get('toggler_color'))
		{
			$color = self::parseToRgbColor($color);
		}
		if (!is_array($color)) {
			if (in_array($params->get('style_toggler', 'blue'), array('white', 'gray')))
				$color = array('r' => 51, 'g' => 51, 'b' => 51); // gray
			else
				$color = array('r' => 255, 'g' => 255, 'b' => 255); // white
		}
		
		// create image
		$im = imagecreatetruecolor($text_close ? $width * 2 : $width, $height);
		imagesavealpha($im, true);
		imagealphablending($im, false);
		
		// set transparent background color
		$bg = imagecolorallocatealpha($im, 255, 0, 255, 127);
		imagefill($im, 0, 0, $bg);
		
		// set font color
		$font_color = imagecolorallocate($im, $color['r'], $color['g'], $color['b']);
		
		// display text
		if ($rotate > 0) {
			imagettftext($im, 
				$font_size, -90, 
				$width * 0.25, 
				0, 
				$font_color, $font_path, $text_open
			);
			
			if ($text_close) 
				imagettftext($im, 
					$font_size, -90, 
					$width + $width * 0.25, 
					0, 
					$font_color, $font_path, $text_close
				);
		}
		else {
			imagettftext($im, 
				$font_size, 90, 
				$width * 0.75, 
				$height, 
				$font_color, $font_path, $text_open
			);
			
			if ($text_close) 
				imagettftext($im, 
					$font_size, 90, 
					$width + $width * 0.75, 
					$height, 
					$font_color, $font_path, $text_close
				);
		}
		
		// set write permissions to cache folder
		jimport('joomla.filesystem.folder');
		if (!JFolder::exists($path)) {
			JFolder::create($path, 0777);
		}
		elseif (!is_writable($path) AND JPath::canChmod($path)) {
			JPath::setPermissions($path, null, '0777');
		}
			
		// save image
		//TODO consider output image and catch it with ob_get_contents() and then write with JFile
		imagepng($im, $path . $file);
		imagedestroy($im);
	}


	protected static function utf8_strrev($str)
	{
		if (empty($str)) return null;
		
		preg_match_all('/./us', $str, $ar);
		return join('', array_reverse($ar[0]));
	}


	protected static function parseToRgbColor($color = null)
	{
		$color = trim($color);
		// parse hex color
		if (preg_match('/^\#([0-9abcdef]{1,2})([0-9abcdef]{1,2})([0-9abcdef]{1,2})$/i', $color, $match)) 
		{
			if (strlen($match[1]) == 2)
			{
				$color = array(
					'r' => hexdec($match[1]),
					'g' => hexdec($match[2]),
					'b' => hexdec($match[3])
				);
			}
			else 
			{
				$color = array(
					'r' => hexdec($match[1].$match[1]),
					'g' => hexdec($match[2].$match[2]),
					'b' => hexdec($match[3].$match[3])
				);
			}
		}
		// parse rgb color
		elseif (preg_match('/\((\d+),(\d+),(\d+)/i', $color, $match))
		{
			$color = array(
				'r' => $match[1],
				'g' => $match[2],
				'b' => $match[3]
			);
		}
		
		return $color;
	}


	public static function getHiddenFields() 
	{
		$params = self::getParams();
		$html = '';
		
		// Community Builder
		$html .= modPWebContactComprofilerHelper::getHiddenField();
		// JoomSocial
		$html .= modPWebContactJomSocialHelper::getHiddenField();
		// SobiPro
		$html .= modPWebContactSobiProHelper::getHiddenField();
		// Zoo
		$html .= modPWebContactZooHelper::getHiddenField();
		
		// CMS and extension version
		if ($params->get('debug') AND ($info = self::getSystemInfo())) {
			$html .= "\r\n<!-- ".implode(', ', $info)." -->\r\n";
		}
		
		return $html;
	}
	
	
	public static function initAjaxResponse() 
	{
		if (function_exists('exceptions_error_handler'))
			@set_error_handler('exceptions_error_handler');
		
		$app = JFactory::getApplication();
		
		$module_id = $app->input->getInt('mid', 0);
		$params = self::getParams($module_id);
		
		// Language
		$lang = JFactory::getLanguage();
		if ($params->get('rtl', 2) == 2) {
			if ($lang->isRTL())
				$params->set('rtl', 1);
			else
				$params->set('rtl', 0);
		}
		$lang->load('mod_pwebcontact');
		
		// Debug
		if ($app->input->getInt('debug')) $params->set('debug', 1);
		if (!defined('PWEBCONTACT_DEBUG')) define('PWEBCONTACT_DEBUG', $params->get('debug'));
		
		if (PWEBCONTACT_DEBUG) {
			self::$logs[] = 'Joomla! version '.JVERSION;
			
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);
			
			// get extension details
			$query->select('manifest_cache')
				->from('#__extensions')
				->where('type = '.$db->quote('module'))
				->where('element = '.$db->quote('mod_pwebcontact'));
			
			$db->setQuery($query);
			try {
				if ($manifest_cache = $db->loadResult()) {
					$manifest = new JRegistry($manifest_cache);
					if ($version = $manifest->get('version'))
						self::$logs[] = 'Contact Form version '.$version;
				}
			} catch (RuntimeException $e) {
				
			}
			
			self::$logs[] = 'Ajax response';
		}
		
		// Set media path
		$params->set('media_url', 	str_replace('modules/mod_pwebcontact/', '', JUri::base()).'media/mod_pwebcontact/');
		$params->set('media_path', 	JPATH_ROOT.'/media/mod_pwebcontact/');
		$params->set('upload_url',  $params->get('media_url').'upload/'.$module_id.'/');
		$params->set('upload_path', $params->get('media_path').'upload/'.$module_id.'/');
		
		// Internet Explorer < 10
		if (!isset($_SERVER['HTTP_ACCEPT']) OR strpos($_SERVER['HTTP_ACCEPT'], 'application/json') === false) {
			// Change response Content-type
			$doc = JFactory::getDocument();
			$doc->setMimeEncoding('text/plain', false);
			if (version_compare(JVERSION, '3.2.0') >= 0) {
				// Remove header Content-disposition
				$app->registerEvent('onAfterRender', array(__CLASS__, 'removeHeaderContentDisposition'));
			}
		}
	}


	public static function closeAjaxResponse() 
	{
		if (PWEBCONTACT_DEBUG) 
		{
			$app = JFactory::getApplication();
			
			// Catch all system messages
			$messages = $app->getMessageQueue();
			foreach ($messages as $message) {
				self::$logs[] = 'Joomla '. $message['type'] .': '. $message['message'];
			}
			
			self::$logs[] = 'Ajax response exit';
		}

		return count(self::$logs) ? self::$logs : null;
	}
	
	
	public static function removeHeaderContentDisposition()
	{
		$app = JFactory::getApplication();
		
		$headers = $app->getHeaders();
		$app->clearHeaders();
		
		foreach ($headers as $header) {
			if (strtolower($header['name']) != 'content-disposition') {
				$app->setHeader($header['name'], $header['value'], true);
			}
		}
	}


	public static function checkToken() 
	{
		$response = true;
		
		try {
			$app = JFactory::getApplication();
			$token = JSession::getFormToken();
			
			if (!($app->input->post->get($token, '', 'alnum') || $app->input->get->get($token, '', 'alnum')))
			{
				$session = JFactory::getSession();
				if ($session->isNew())
				{
					// session has expired or cookies are blocked
					$response = array('status' => 308, 'msg' => JText::_('MOD_PWEBCONTACT_COOKIES_ERR'));
				}
				else
				{
					$response = array('status' => 302, 'msg' => JText::_('MOD_PWEBCONTACT_TOKEN_ERR'));
				}
			}
		} catch (Exception $e) {
			$response = array('status' => 302, 'msg' => JText::_('MOD_PWEBCONTACT_JOOMLA_ERR'), 'debug' => array($e->getMessage().' in '.$e->getFile().' on line '.$e->getLine()));
		}
		
		return $response;
	}


	public static function getTokenAjax() 
	{
		return array('status' => 103, 'token' => JSession::getFormToken());
	}


	public static function checkCaptchaAjax() 
	{
		self::initAjaxResponse();
		if (($response = self::checkToken()) !== true) return $response;
		
		if (PWEBCONTACT_DEBUG) self::$logs[] = 'Checking captcha';
		
		$app 	= JFactory::getApplication();
		$params = self::getParams();
		
		$response = array('status' => 101, 'msg' => '');
		
		try {
			// Captcha
			$captcha_plugin = $params->get('captcha', $app->getCfg('captcha', 0));
			if ($captcha_plugin AND ($captcha = JCaptcha::getInstance($captcha_plugin)) != null) 
			{
				if (!$captcha->checkAnswer($app->input->get('captcha', null, 'string'))) {
					if (PWEBCONTACT_DEBUG) self::$logs[] = 'Invalid captcha code';
					$response = array('status' => 201, 'msg' => JText::_('MOD_PWEBCONTACT_INVALID_CAPTCHA_ERR'));
				}
			}
		} catch (Exception $e) {
			self::$logs[] = $e->getMessage().' in '.$e->getFile().' on line '.$e->getLine();
			$response = array('status' => 301, 'msg' => JText::_('MOD_PWEBCONTACT_JOOMLA_ERR'));
		}
		
		$response['debug'] = self::closeAjaxResponse();
		
		return $response;
	}
	
	
	public static function uploaderAjax() 
	{
		self::initAjaxResponse();
		if (($response = self::checkToken()) !== true) return $response;
		
		if (PWEBCONTACT_DEBUG) self::$logs[] = 'Uploader';
		
		try {			
			$response = modPWebContactUploader::uploader();
			$response = array_merge(array('status' => 104), $response);
		} catch (Exception $e) {
			self::$logs[] = $e->getMessage().' in '.$e->getFile().' on line '.$e->getLine();
			$response = array('status' => 400, 'msg' => JText::_('MOD_PWEBCONTACT_JOOMLA_ERR'));
		}
		
		$response['debug'] = self::closeAjaxResponse();
		
		return $response;
	}


	public static function sendEmailAjax() 
	{
		self::initAjaxResponse();
		if (($response = self::checkToken()) !== true) return $response;
		
		if (PWEBCONTACT_DEBUG) self::$logs[] = 'Sending emails';
		
		$params = self::getParams();
		
		try {			
			$response = self::sendEmail();
		} catch (Exception $e) {
			self::$logs[] = $e->getMessage().' in '.$e->getFile().' on line '.$e->getLine();
			$response = array('status' => 300, 'msg' => JText::_('MOD_PWEBCONTACT_JOOMLA_ERR'));
		}
		
		// delete atachments
		if ($params->get('show_upload', 0) AND $params->get('attachment_delete') AND $params->get('attachment_type', 1) == 1 AND ($response['status'] < 200 OR $response['status'] >= 300))
		{
			if (PWEBCONTACT_DEBUG) self::$logs[] = 'Deleting attachments';
			
			try {
				modPWebContactUploader::deleteAttachments();
				$response['deleted'] = true;
			} catch (Exception $e) {
				self::$logs[] = $e->getMessage().' in '.$e->getFile().' on line '.$e->getLine();
				$response = array('status' => 401, 'msg' => JText::_('MOD_PWEBCONTACT_JOOMLA_ERR'));
			}
		}
		
		$response['debug'] = self::closeAjaxResponse();
		
		return $response;
	}
	

	public static function sendEmail() 
	{		
		jimport('joomla.mail.helper');
		jimport('joomla.filesystem.file');

		$app 		= JFactory::getApplication();
		$user 		= JFactory::getUser();
		$lang 		= JFactory::getLanguage();
		$params 	= self::getParams();
		$module_id 	= (int)$params->get('id');
		
		// mail from
		$global_name  = $app->getCfg('fromname', $app->getCfg('sitename'));
		$global_email = $params->get('email_from', $app->getCfg('mailfrom'));
		if (!$global_email) {
			if (PWEBCONTACT_DEBUG) self::$logs[] = 'Invalid Global Configuration';
			return array('status' => 303, 'msg' => JText::_('MOD_PWEBCONTACT_GLOBAL_CONFIG_ERR'));
		}
		
		$data 		=& self::$data;
		$email_vars =& self::$email_vars;
		
		// Get inputs
		$data = $app->input->getArray(array(
			'fields'			=> 'array',
			'mailto'			=> 'int',
			'title' 			=> 'string',
			'url' 				=> 'string',
			'screen_resolution' => 'string',
			'attachments' 		=> 'array'
		));

		$data['ip_address'] 	= self::detectIP();
		$data['browser'] 		= self::detectBrowser();
		$data['os'] 			= self::detectOS();
		
		$data['user_id'] 		= $user->id;
		$data['user_subject'] 	= '';
		
		// init email variables
		$email_vars = array(
			'name'				=> '',
			'email'				=> '',
			'username' 			=> $user->username,
			'ip_address' 		=> $data['ip_address'],
			'browser' 			=> $data['browser'],
			'os' 				=> $data['os'],
			'screen_resolution' => $data['screen_resolution'],
			'title' 			=> $data['title'],
			'url' 				=> $data['url'],
			'site_name' 		=> $app->getCfg('sitename'),
			'mailto_name'		=> '',
			'ticket'			=> ''
		);
		
		$fields = self::getFields();
		
		$user_email = null;
		$user_name 	= null;
		$user_cc 	= array();
		$email_to 	= array();
		
		$invalid_fields = array();
		
		// init email variables for fields and validate them
		foreach ($fields as $field)
		{
			// skip all separators which does not have any data
			if (strpos($field->type, 'separator') !== false) continue;
			
			// get field from request
			if (isset($data['fields'][$field->alias])) {
				$value = $data['fields'][$field->alias];
			} else {
				$data['fields'][$field->alias] = $value = null;
			}
			
			// is required
			if ($field->required AND ($value === null OR $value === '')) {
				// required field is empty
				$invalid_fields[] = 'field-'.$field->alias;
				continue;
			}
			
			if ($field->type == 'email') 
			{
				// Validate email
				if ($value AND JMailHelper::isEmailAddress($value) === false) {
					$invalid_fields[] = 'field-'.$field->alias;
				} else {
					if (!$user_email) 
						$email_vars['email'] = $user_email = $value;
					else 
						$user_cc[] = $value;
				}
			}
			else 
			{
				if ($field->type == 'name') {
					if (!$user_name) 
						$email_vars['name'] = $user_name = $value;
				}
				elseif ($field->type == 'subject') {
					$data['user_subject'] .= ' '.$value;
				}
				
				// validate fields with regular expression
				if (in_array($field->type, array('text', 'name', 'phone', 'subject', 'password')) AND $field->params AND $value AND !preg_match($field->params, $value)) {
					$invalid_fields[] = 'field-'.$field->alias;
				}
			}
		}
		
		
		// mailto list
		if ($params->get('email_to_list')) 
		{
			if ($data['mailto'] > 0) {
				$rows = @explode(PHP_EOL, $params->get('email_to_list'));
				if (array_key_exists($data['mailto']-1, $rows)) {
					$row = @explode('|', $rows[$data['mailto']-1]);
					if ($row[0]) {
						$email_to[] = $row[0];
						$email_vars['mailto_name'] = $row[1];
					}
				}
			}
			else {
				// required field is empty
				$invalid_fields[] = 'mailto';
			}
		} 
		else {
			$data['mailto'] = null;
		}
		
		
		if ($params->get('show_upload') == 2 AND !count($data['attachments'])) {
			$invalid_fields[] = 'uploader';
		}
		
		// invalid fields
		if (count($invalid_fields)) {
			if (PWEBCONTACT_DEBUG) self::$logs[] = 'Invalid fields';
			return array('status' => 200, 'msg' => JText::_('MOD_PWEBCONTACT_INVALID_FIELDS_ERR'), 'invalid' => $invalid_fields);
		}
		
		
		// Community Builder
		if ( $params->get('comprofiler') AND ($profile_id = $app->input->getInt('comprofiler', 0)) > 0 ) {
			$email = modPWebContactComprofilerHelper::getEmail($profile_id);
			if ($email === false) {
				return array('status' => 500, 'msg' => JText::_('MOD_PWEBCONTACT_NO_RECIPIENT'));
			} else {
				$email_to[] = $email;
				if (PWEBCONTACT_DEBUG) self::$logs[] = 'Community Builder email: '.$email;
			}
		} 
		// JomSocial
		elseif ( $params->get('jomsocial') AND ($profile_id = $app->input->getInt('jomsocial', 0)) > 0 ) {
			$email = modPWebContactJomSocialHelper::getEmail($profile_id);
			if ($email === false) {
				return array('status' => 510, 'msg' => JText::_('MOD_PWEBCONTACT_NO_RECIPIENT'));
			} else {
				$email_to[] = $email;
				if (PWEBCONTACT_DEBUG) self::$logs[] = 'JomSocial email: '.$email;
			}
		} 
		// SobiPro
		elseif ( $params->get('sobipro') AND ($entry_id = $app->input->getInt('sobipro', 0)) > 0 ) {
			$email = modPWebContactSobiProHelper::getEmail($entry_id);
			if ($email === false) {
				return array('status' => 520, 'msg' => JText::_('MOD_PWEBCONTACT_NO_RECIPIENT'));
			} else {
				$email_to[] = $email;
				if (PWEBCONTACT_DEBUG) self::$logs[] = 'SobiPro email: '.$email;
			}
		}
		// Zoo
		elseif ( $params->get('zoo') AND ($item_id = $app->input->getInt('zoo', 0)) > 0 ) {
			$email = modPWebContactZooHelper::getEmail($item_id);
			if ($email === false) {
				return array('status' => 520, 'msg' => JText::_('MOD_PWEBCONTACT_NO_RECIPIENT'));
			} else {
				$email_to[] = $email;
				if (PWEBCONTACT_DEBUG) self::$logs[] = 'Zoo email: '.$email;
			}
		}
		else {
			// Joomla
			if ($params->get('email_to')) {
				$email_to = array_merge($email_to, @explode(',', $params->get('email_to')));
			}
			if ($params->get('email_user')) 
			{
				$db = JFactory::getDBO();
				$query = $db->getQuery(true);
				$query->select('email')
					  ->from('#__users')
					  ->where('id = '.(int)$params->get('email_user'))
					  ;
				$db->setQuery($query);
				
				try {
					if ($email = $db->loadResult()) {
						$email_to[] = $email;
					}
				} catch (RuntimeException $e) {
					
				}
			}
		}
		
		if (!count($email_to)) {
			$email_to[] = $global_email;
		}
		
		
		// ticket
		$data['ticket'] = '';
		if ($ticket_type = $params->get('ticket_enable', 0)) 
		{
			if ($ticket_type == 1)
			{
				$data['ticket'] 		= JFactory::getDate()->format('YmdHis');
				$email_vars['ticket'] 	= sprintf($params->get('ticket_format', '[#%s]'), $data['ticket']);
			}
			elseif ($ticket_type == 2)
			{
				$ticket_file = $params->get('media_path').'tickets/ticket_'.sprintf('%03d', $module_id).'.txt';
				$ticket_counter = JFile::exists($ticket_file) ? (int)file_get_contents($ticket_file) : 0;
				$ticket_counter++;
				JFile::write($ticket_file, $ticket_counter);
				
				$data['ticket'] 		= $ticket_counter;
				$email_vars['ticket'] 	= sprintf($params->get('ticket_format', '[#%06d]'), $ticket_counter);
			}
			
			if ($data['ticket']) 
			{
				// success message with ticket
				$success_msg = JText::sprintf($params->get('msg_success', 'MOD_PWEBCONTACT_MAIL_SUCCESS_TICKET'), $email_vars['ticket']);
				
				// email subject with ticket
				$data['subject'] = JText::sprintf($params->get('email_subject', 'MOD_PWEBCONTACT_EMAIL_SUBJECT_TICKET'), $email_vars['ticket']);
			}
		}
		
		// success message
		if (!isset($success_msg)) $success_msg = JText::_($params->get('msg_success', 'MOD_PWEBCONTACT_MAIL_SUCCESS'));
		// clean subject
		//$success_msg = str_replace(array('"','\\'), '', $success_msg);
		
		// email subject
		if (!isset($data['subject'])) $data['subject'] = JText::_($params->get('email_subject', 'MOD_PWEBCONTACT_EMAIL_SUBJECT'));
		
		// email subject suffix
		//TODO test RTL if suffix should be before subject
		switch ($params->get('email_subject_sfx', 2))
		{
			case 1:
				$data['subject'] .= ' '.$email_vars['site_name'];
				break;
			case 2:
				$data['subject'] .= ' '.$data['title'];
				break;
			case 3:
				$data['subject'] .= $data['user_subject'];
		}

		// HOOK PROCCESS DATA - here you can add custom code to proccess variables: $data, $email_vars

		// HTML emails path
		$tmpl_path = $params->get('media_path').'email_tmpl/';
		
		// User email copy and auto-reply
		$email_copy 		= ($params->get('email_copy', 0) AND $app->input->getInt('copy', 0));
		$email_autoreply 	= $params->get('email_autoreply', 0);
		
		if ($user_email AND ($email_copy OR $email_autoreply)) 
		{
			$mail = JFactory::getMailer();
			
			// add recipient
			$mail->addRecipient($user_email);
			if (PWEBCONTACT_DEBUG) self::$logs[] = 'User email: '.$user_email;
			
			// Add carbon copy recipients
			if (count($user_cc))
			{
				$mail->addCC($user_cc);
				if (PWEBCONTACT_DEBUG) self::$logs[] = 'User email: '.implode(', ', $user_cc);
			}
			
			// set subject
			$mail->setSubject($data['subject']);
			if (PWEBCONTACT_DEBUG) self::$logs[] = 'User email subject: '.$data['subject'];
			
			// set sender
			$mail->setSender(array($global_email, $global_name));
			if (PWEBCONTACT_DEBUG) self::$logs[] = 'User email sender: '.$global_email;
			
			// set reply to
			if ($params->get('email_replyto')) 
			{
				$mail->ClearReplyTos();
				$mail->addReplyTo($params->get('email_replyto'), $params->get( 'email_replyto_name', $global_name));
				if (PWEBCONTACT_DEBUG) self::$logs[] = 'User email reply to: '.$params->get('email_replyto');
			}
			
			// Auto-reply
			if ($email_autoreply)
			{
				// load email body template
				$tmpl = $params->get('email_tmpl_html_autoreply');
				$is_html = $tmpl ? true : false;
				$mail->IsHTML($is_html);
				if ($is_html) {
					$body = file_get_contents($tmpl_path . $tmpl .'.html');
				} else {
					$body = $params->get('email_tmpl_text_autoreply');
				}
	
				self::parseTmplVars($body, $is_html, $lang->getTag());
				
				// set body text direction
				$body = ($params->get('rtl', 0) ? "\xE2\x80\x8F" : "\xE2\x80\x8E") . $body;
				
				// set body text
				$mail->setBody($body);
				
				if (PWEBCONTACT_DEBUG) self::$logs[] = 'User auto-reply email ready';
		
				// send auto-reply email
				if ($mail->Send() !== true) 
				{
					return array('status' => 304, 'msg' => JText::_('MOD_PWEBCONTACT_MAIL_AUTOREPLY_ERR'));
				} 
				elseif (PWEBCONTACT_DEBUG) self::$logs[] = 'User auto-reply email sent successfully';
			}
			
			// User email copy
			if ($email_copy)
			{
				// set attachments as files
				if ($params->get('attachment_type', 1) == 1 AND count($data['attachments']))
				{
					$path = $params->get('upload_path');
					foreach ($data['attachments'] as $file)
						$mail->addAttachment($path . $file, $file);
					if (PWEBCONTACT_DEBUG) self::$logs[] = 'User email attachments: '.implode(', ', $data['attachments']);
				}
				
				// load email body template
				$tmpl = $params->get('email_tmpl_html_user');
				$is_html = $tmpl ? true : false;
				$mail->IsHTML($is_html);
				if ($is_html) {
					$body = file_get_contents($tmpl_path . $tmpl .'.html');
				} else {
					$body = $params->get('email_tmpl_text_user');
				}
	
				self::parseTmplVars($body, $is_html, $lang->getTag());
				
				// set body text direction
				$body = ($params->get('rtl', 0) ? "\xE2\x80\x8F" : "\xE2\x80\x8E") . $body;
				
				// set body text
				$mail->setBody($body);
				
				if (PWEBCONTACT_DEBUG) self::$logs[] = 'User email ready';
		
				// send User email
				if ($mail->Send() !== true) 
				{
					return array('status' => 305, 'msg' => JText::_('MOD_PWEBCONTACT_MAIL_USER_ERR'));
				} 
				elseif (PWEBCONTACT_DEBUG) self::$logs[] = 'User email sent successfully';
			}
		}


		// Demo: do not send email to Admin
		if ($params->get('demo', 0)) {
			if (PWEBCONTACT_DEBUG) self::$logs[] = 'DEMO mode is not sending email to Admin';
			return array('status' => 102, 'msg' => $success_msg, 'ticket' => $data['ticket']);
		}


		// Administrator email language
		$email_lang = $params->get('email_lang');
		if ($email_lang) 
		{
			if ($email_lang != $lang->getTag()) 
			{
				$lang->setLanguage($email_lang);
				$lang->load();
				$lang->load('mod_pwebcontact');
				$params->set('rtl', $lang->isRTL());
				
				if (PWEBCONTACT_DEBUG) self::$logs[] = 'Admin email language loaded: '.$email_lang;
			}
			else {
				$params->set('email_lang', false);
			}
		}


		// Administrator email
		$mail = JFactory::getMailer();

		// add recipient
		$mail->addRecipient($email_to);
		if (PWEBCONTACT_DEBUG) self::$logs[] = 'Admin emails: '.implode(', ', $email_to);
			
		// set subject
		$mail->setSubject($data['subject']);
		if (PWEBCONTACT_DEBUG) self::$logs[] = 'Admin email subject: '.$data['subject'];

		// set sender
		if ($user_email AND !$params->get('server_sender', 0)) {
			$mail->setSender(array($user_email, $user_name));
			if (PWEBCONTACT_DEBUG) self::$logs[] = 'Admin email Sender: '.$user_email;
		} else {
			$mail->setSender(array($global_email, $global_name));
			if (PWEBCONTACT_DEBUG) self::$logs[] = 'Admin email Sender: '.$global_email;
			
			// set reply to
			if ($user_email) {
				$mail->ClearReplyTos();
				$mail->addReplyTo(array($user_email, $user_name));
				if (PWEBCONTACT_DEBUG) self::$logs[] = 'Admin email reply to: '.$user_email;
			}
		}

		
		// Add blind carbon copy recipients
		if ($params->get('email_bcc')) 
		{
			$email_bcc = @explode(',', $params->get('email_bcc'));
			$mail->addBCC($email_bcc);
			if (PWEBCONTACT_DEBUG) self::$logs[] = 'Admin BCC recipients: '.$params->get('email_bcc');
		}
		
		// Add User email as blind carbon copy in debug mode
		if (PWEBCONTACT_DEBUG AND $user_email)
		{
			$mail->addBCC($user_email);
			self::$logs[] = 'Admin BCC debug recipient: '.$user_email;
		}

		// set attachments as files
		if ($params->get('attachment_type', 1) == 1 AND count($data['attachments']))
		{
			$path = $params->get('upload_path');
			foreach ($data['attachments'] as $file)
				$mail->addAttachment($path . $file, $file);
			if (PWEBCONTACT_DEBUG) self::$logs[] = 'Admin email attachments: '.implode(', ', $data['attachments']);
		}

		// load email body template
		$tmpl = $params->get('email_tmpl_html_admin');
		$is_html = $tmpl ? true : false;
		$mail->IsHTML($is_html);
		if ($is_html) {
			$body = file_get_contents($tmpl_path . $tmpl .'.html');
		} else {
			$body = $params->get('email_tmpl_text_admin');
		}

		self::parseTmplVars($body, $is_html, $lang->getTag());

		// set body text direction
		$body = ($params->get('rtl', 0) ? "\xE2\x80\x8F" : "\xE2\x80\x8E") . $body;

		// set body text
		$mail->setBody($body);

		if (PWEBCONTACT_DEBUG) self::$logs[] = 'Admin email ready';

		// send Admin email
		if ($mail->Send() !== true) 
		{
			return array('status' => 306, 'msg' => JText::_('MOD_PWEBCONTACT_MAIL_ADMIN_ERR'));
		}
		elseif (PWEBCONTACT_DEBUG) self::$logs[] = 'Admin email sent successfully';


		return array('status' => 100, 'msg' => $success_msg, 'ticket' => $data['ticket']);
	}
	
	
	protected static function getSystemInfo() 
	{
		if (!self::$sys_info) 
		{
			self::$sys_info = array( 'Joomla! version '.JVERSION );
			
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);
			
			// get extension details
			$query->select('manifest_cache')
				->from('#__extensions')
				->where('type = '.$db->quote('module'))
				->where('element = '.$db->quote('mod_pwebcontact'));
			
			$db->setQuery($query);
			try {
				if ($manifest_cache = $db->loadResult()) {
					$manifest = new JRegistry($manifest_cache);
					if ($version = $manifest->get('version'))
						self::$sys_info[] = 'Contact Form version '.$version;
				}
			} catch (RuntimeException $e) {
				
			}
		}
		
		return self::$sys_info;
	}


	protected static function parseTmplVars(&$content, $is_html = true, $lang_code = 'en-GB')
	{
		$cache_key = $lang_code .'_'. (int)$is_html .'_'. md5($content);
		
		if (!isset(self::$email_tmpls[$cache_key]))
		{
			$params = self::getParams();
			$app = JFactory::getApplication();
			
			$patterns = $replacements = $fields_replacements = array();
			
			// text direction
			if ($is_html) 
			{
				$patterns[] = '{dir}';
				$replacements[] = $params->get('rtl', 0) ? 'rtl' : 'ltr';
			}
			
			// Language variables
			if (preg_match_all('/{lang:([^}]+)}/i', $content, $lang_vars, PREG_SET_ORDER))
			{
				foreach ($lang_vars as $variable)
				{
					$patterns[] 	= $variable[0];
					$replacements[] = JText::_($variable[1]);
				}
			}
			
			// Zoo variables
			if ($params->get('zoo') AND ($item_id = $app->input->getInt('zoo', 0)) AND preg_match_all('/{zoo.([^}]+)}/i', $content, $zoo_vars, PREG_SET_ORDER) )
			{
				foreach ($zoo_vars as $variable)
				{
					$patterns[] 	= $variable[0];
					$replacements[] = modPWebContactZooHelper::getFieldValue($item_id, $variable[1]);
				}
			}
			
			// Varaibles with fields
			$cache_fields_key = $lang_code .'_'. (int)$is_html .'_fields';
			$search_fields = strpos($content, '{fields}') !== false;
			$fields = self::getFields();
			
			foreach ($fields as $field)
			{
				// skip all separators which does not have any data
				if (strpos($field->type, 'separator') !== false) continue;
				
				if (isset(self::$data['fields'][$field->alias])) {
					$value = self::$data['fields'][$field->alias];
				} else {
					$value = null;
				}
				
				switch ($field->type)
				{
					case 'textarea':
						if ($is_html AND $value) 
							$value = nl2br($value);
						break;
					case 'checkboxes':
					case 'multiple':
						if (is_array($value)) {
							foreach ($value as &$val)
								$val = JText::_($val);
							$value = implode(', ', $value);
						}
						break;
					case 'checkbox':
					case 'radio':
					case 'select':
						if ($value) 
							$value = JText::_($value);
						break;
				}
				
				$patterns[] 	= '{'.$field->alias.'.value}';
				$replacements[] = $value;
				
				$patterns[] 	= '{'.$field->alias.'.label}';
				$replacements[] = $name = JText::_($field->name);
				
				if ($search_fields AND !isset(self::$email_tmpls[$cache_fields_key])) {
					//TODO test RTL if need to change position of sprintf arguments
					$fields_replacements[] = JText::sprintf('MOD_PWEBCONTACT_EMAIL_FIELD_FORMAT_'.($is_html ? 'HTML' : 'TEXT'), $name, $value);
				}
			}
			
			
			// all fields
			if ($search_fields) 
			{
				if (!isset(self::$email_tmpls[$cache_fields_key])) {
					self::$email_tmpls[$cache_fields_key] = implode($is_html ? '<br>' : "\r\n", $fields_replacements);
				}
				$patterns[] 	= '{fields}';
				$replacements[] = self::$email_tmpls[$cache_fields_key];
			}


			// attachments
			if (strpos($content, '{files}') !== false)
			{
				$patterns[] = '{files}';
				
				// attachments as links
				if (count(self::$data['attachments']) AND $params->get('attachment_type', 1) == 2)
				{
					$cache_files_key = $lang_code .'_'. (int)$is_html .'_files';
					if (!isset(self::$email_tmpls[$cache_files_key]))
					{
						$urls = array();
						$url = $params->get('upload_url');
						foreach (self::$data['attachments'] as $file)
							$urls[] = JText::sprintf('MOD_PWEBCONTACT_EMAIL_FILE_FORMAT_'.($is_html ? 'HTML' : 'TEXT'), $url.rawurlencode($file), $file);

						self::$email_tmpls[$cache_files_key] = implode($is_html ? '<br>' : "\r\n", $urls);
					}
					
					$replacements[] = self::$email_tmpls[$cache_files_key];
				}
				else 
				{
					$replacements[] = '';
				}
			}
			
			
			// system
			foreach (self::$email_vars as $variable => $value)
			{
				$patterns[] 	= '{'.$variable.'}';
				$replacements[] = $value;
			}
			
			
			// replace email variables with values
			$content = str_replace($patterns, $replacements, $content);
			
			
			self::$email_tmpls[$cache_key] = $content;
		}
		else $content = self::$email_tmpls[$cache_key];
	}


	protected static function detectBrowser()
	{
		jimport('joomla.environment.browser');
		
		$browser = JBrowser::getInstance();
		
		$name = $browser->getBrowser();
		$version = $browser->getVersion();
		
		if ($name == 'mozilla' AND preg_match('|Firefox/([0-9.]+)|', $browser->getAgentString(), $match))
		{
			$name = 'firefox';
			$version = $match[1];
		}
		
		return ucfirst($name).' '.$version;
	}


	protected static function detectIP()
	{
		if (isset($_SERVER['REMOTE_ADDR'])) 
			$ip = $_SERVER['REMOTE_ADDR'];
		elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) 
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		elseif (isset($_SERVER['HTTP_CLIENT_IP'])) 
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		elseif (isset($_SERVER['HTTP_VIA'])) 
			$ip = $_SERVER['HTTP_VIA'];
		else 
			$ip = 'unknown';
		
		return $ip;
	}


	protected static function detectOS()
	{
		$os_name = null;
		$os = array(
			// Mircrosoft Windows Operating Systems
			'Windows 8.1' => 'Windows NT 6.3',
			'Windows 8' => 'Windows NT 6.2',
			'Windows 7' => 'Windows NT 6.1',
			'Windows Vista' => 'Windows NT 6.0',
			'Windows XP' => 'Windows NT 5.1',
			'Windows Server 2003' => 'Windows NT 5.2',
			'Windows 2000' => 'Windows NT 5.0|Windows 2000',
			'Windows NT 4.0' => 'Windows NT 4.0|WinNT4.0|WinNT|Windows NT',
			'Windows ME' => 'Windows ME|Windows 98; Win 9x 4.90',
			'Windows 98' => 'Windows 98|Win98',
			'Windows 95' => 'Windows 95|Win95|Windows_95',
			'Windows CE' => 'Windows CE',
			'Windows Phone %s' => 'Windows Phone OS (\d+(\.\d+)*)+',
			'Windows Phone %s' => 'Windows Phone (\d+(\.\d+)*)+',
			'Windows' => 'Windows',
			// Apple Mobile Devices
			'iPod iPhone OS %s' => 'iPod.+iPhone OS (\d+(_\d+)*)+',
			'iPhone OS %s' => 'iPhone OS (\d+(_\d+)*)+',
			'iPad OS %s' => 'iPad.+OS (\d+(_\d+)*)+',
			'iPhone' => 'iPhone',
			// Apple Mac Operating Systems
			'Mac OS X Cheetah' => 'Mac OS X 10.0',
			'Mac OS X Puma' => 'Mac OS X 10.1',
			'Mac OS X Jaguar' => 'Mac OS X 10.2',
			'Mac OS X Panther' => 'Mac OS X 10.3',
			'Mac OS X Tiger' => 'Mac OS X 10.4',
			'Mac OS X Leopard' => 'Mac OS X 10.5',
			'Mac OS X Snow Leopard' => 'Mac OS X 10.6',
			'Mac OS X Lion' => 'Mac OS X 10.7',
			'Mac OS X Mountain Lion' => 'Mac OS X 10.8',
			'Mac OS X Mavericks' => 'Mac OS X 10.9',
			'Mac OS X%s' => 'Mac OS X( \d+\.\d+)*',
			'Mac OS' => 'Mac_PowerPC|PowerPC|Macintosh',
			// Mobile Devices
			'Andriod %s' => 'Android (\d+(\.\d+)*)+',
			'SymbianOS' => 'Symbian|SymbOS',
			// Linux Operating Systems
			'Ubuntu %s' => 'Ubuntu[\/ ]+(\d+(\.\d+)*)+',
			'Ubuntu' => 'Ubuntu',
			'Fedora' => 'Fedora',
			'Red Hat' => 'Red Hat',
			'OpenSUSE' => 'SUSE',
			'Debian' => 'Debian',
			'Mandriva' => 'Mandriva',
			'Linux Mint' => 'Linux Mint',
			'PCLinuxOS' => 'PCLinuxOS',
			'CentOS' => 'CentOS',
			'Aurox' => 'Aurox',
			'Chromium OS' => 'ChromiumOS',
			'Google Chrome OS' => 'ChromeOS',
			// Kernel
			'Linux' => 'Linux|X11',
			// UNIX Like Operating Systems
			'Open BSD' => 'OpenBSD',
			'SunOS' => 'SunOS',
			'Solaris' => 'Solaris',
			'CentOS' => 'CentOS',
			'QNX' => 'QNX',
			// Kernels
			'UNIX' => 'UNIX',
			// BSD Operating Systems
			'OpenBSD' => 'OpenBSD',
			'FreeBSD' => 'FreeBSD',
			'NetBSD' => 'NetBSD',
			//DEC Operating Systems
			'OS/8' => 'OS\/8|OS8',
			'Older DEC OS' => 'DEC|RSTS|RSTS\/E',
			'WPS-8' => 'WPS-8|WPS8',
			// BeOS Like Operating Systems
			'BeOS' => 'BeOS|BeOS r5',
			'BeIA' => 'BeIA',
			// OS/2 Operating Systems
			'OS/2 2.0' => 'OS\/220|OS\/2 2.0',
			'OS/2' => 'OS\/2|OS2'
		);
		
		foreach ($os as $name => $regExp) {
			if (preg_match('/'.$regExp.'/i', $_SERVER['HTTP_USER_AGENT'], $match)) {
				$os_name = sprintf($name, array_key_exists(1, $match) ? $match[1] : '');
				break;
			}
		}
		
		return $os_name;
	}
}

if (!function_exists('exceptions_error_handler'))
{
	function exceptions_error_handler($errno, $errstr, $errfile, $errline)
	{
		if (!(error_reporting() & $errno)) {
			// This error code is not included in error_reporting
			return;
		}
		throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
	}
}
