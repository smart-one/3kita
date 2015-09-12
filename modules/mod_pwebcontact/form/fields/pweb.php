<?php
/**
* @version 3.2.4
* @package PWebContact
* @copyright © 2014 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
* @license GNU General Public License http://www.gnu.org/licenses/gpl-3.0.html
* @author Piotr Moćko
*/

defined('_JEXEC') or die( 'Restricted access' );

JFormHelper::loadFieldClass('List');

/**
 * Perfect-Web field with UI and validation
 */
class JFormFieldPweb extends JFormFieldList
{
	protected $type = 'Pweb';
	protected $extension = 'mod_pwebcontact';
	protected $documentation = 'http://www.perfect-web.co/joomla/ajax-contact-form-popup-module/documentation';
	
	
	protected function getInput()
	{
		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();
		
		$html = $params = null;
		
		if (version_compare(JVERSION, '2.5.5') == -1) 
		{
			// Joomla minimal version
			$app->enqueueMessage(
				JText::sprintf('MOD_PWEBCONTACT_CONFIG_MSG_JOOMLA_VERSION', 
					'2.5.5', 
					'<a href="index.php?option=com_joomlaupdate" target="_blank">', '</a>'
				), 'error');
		} 
		else 
		{
			JHtml::_('stylesheet', 'jui/icomoon.css', array(), true);
			
			// check Joomla Global Mailer configuration
			$this->checkMailer();
			
			// Module ID
			$module_id = $app->input->getInt('id', 0);
			
			// check module configuration
			if ($module_id > 0) 
			{
				// get params
				require_once JPATH_ROOT.'/modules/mod_pwebcontact/helper.php';
				$params = modPwebcontactHelper::getParams($module_id);
				
				if ($params->get('rtl', 2) == 2) {
					// warn about auto RTL
					$langs = JLanguage::getKnownLanguages(JPATH_ROOT);
					foreach ($langs as $lang) {
						if ((bool)$lang['rtl']) {
							$app->enqueueMessage(JText::_('MOD_PWEBCONTACT_CONFIG_RTL_TIP'), 'notice');
							break;
						}
					}
				}
				
				// check if debug mode is enabled
				if ($params->get('debug', 0)) {
					$app->enqueueMessage(JText::_('MOD_PWEBCONTACT_CONFIG_MSG_DISABLE_DEBUG'), 'notice');
				}
				
				// check if demo mode is enabled
				if ($params->get('demo', 0)) {
					$app->enqueueMessage(JText::_('MOD_PWEBCONTACT_CONFIG_MSG_DEMO'), 'warning');
				}
				
				// check if upload directory is writable
				if ($params->get('show_upload', 0)) {
					$this->checkUploadPath('/media/mod_pwebcontact/upload/'.$module_id.'/');
				}
				
				// check Admin Tools Pro exception
				if (is_file(JPATH_ROOT.'/components/com_ajax/ajax.php')) {
					$this->checkAdminToolsPro();
				} elseif ($params->get('root_path', 0)) {
					$this->checkAdminToolsPro('mod_pwebcontact_ajax.php');
				} else {
					$this->checkAdminToolsPro('modules/mod_pwebcontact/ajax.php');
				}
				
				// check JoomlArt T3 Framework templates
				$this->checkJAT3v2CacheExclude('mod_pwebcontact');

				// check module details configuration
				$this->checkModuleDetails($module_id);
			}
			
			// check if Ajax Interface is installed and enabled
			if ($this->checkAjaxComponent() === true OR version_compare(JVERSION, '3.2.0') >= 0)
			{
				// Hide root path option
				$doc->addScriptDeclaration(
					'jQuery(document).ready(function($){'.
					'$("#'.$this->formControl.'_'.$this->group.'_root_path").closest("li,div.control-group").hide();'.
					'});'
				);
			}
			
			// check if Bootstrap is updated to version 2.3.1
			if (version_compare(JVERSION, '3.0.0') >= 0 AND version_compare(JVERSION, '3.1.4') == -1) 
			{
				$this->checkBootstrap();
			}
			
			// check functions for image creation
			$this->checkImageTextCreation();
			
			// check if cache directory is writable
			// check if direct access to files in cache directory is allowed
			$this->checkCacheDir();
		}
		
		// add documentation toolbar button
		if (version_compare(JVERSION, '3.0.0') == -1) {
			$button = '<a href="'.$this->documentation.'" style="font-weight:bold;border-color:#025A8D;background-color:#DBE4E9;" target="_blank"><span class="icon-32-help"> </span> '.JText::_('MOD_PWEBCONTACT_DOCUMENTATION').'</a>';
		} else {
			$button = '<a href="'.$this->documentation.'" class="btn btn-small btn-info" target="_blank"><i class="icon-support"></i> '.JText::_('MOD_PWEBCONTACT_DOCUMENTATION').'</a>';
		}
		$bar = JToolBar::getInstance();
		$bar->appendButton('Custom', $button, $this->extension.'-docs');
		
		JText::script('MOD_PWEBCONTACT_PREVIEW_BUTTON');
		JText::script('MOD_PWEBCONTACT_COPY_BUTTON');
		JText::script('MOD_PWEBCONTACT_INTEGRATION_COMPONENT_VIEW');
		
		// add admin styles and script
		$doc->addStyleSheet(JUri::root(true).'/media/mod_pwebcontact/css/admin.css');
		if (class_exists('JHtmlJquery')) 
		{
			JHtml::_('jquery.framework');
			$doc->addScript(JUri::root(true).'/media/mod_pwebcontact/js/jquery.admin.js');
		}
		
		// disable Joomla Form Validator which slows down saving big forms
		$doc->addScriptDeclaration(
			'window.addEvent("domready", function(){'.
				'document.forms.adminForm.className = document.forms.adminForm.className.replace("form-validate", "");'.
				'document.formvalidator = {'.
					'setHandler: function(name, fn, en){},'.
					'attachToForm: function(form){},'.
					'validate: function(el){return true},'.
					'isValid: function(form){return true},'.
					'handleResponse: function(state, el){}'.
				'};'.
			'});'
		);

		// check if user is authorized to manage extensions
		if (JFactory::getUser()->authorise('core.manage', 'com_installer'))
		{
			// add feeds script
			if ($this->value == 1 OR $this->value == 3)
			{
				require_once JPATH_ROOT.'/modules/mod_pwebcontact/helpers/updateserver.php';
				$updateServer = new modPWebContactUpdateServer;
				$doc->addScriptDeclaration( $updateServer->getFeedScript() );
			}
		}
		else
		{
			$doc->addScriptDeclaration(
				'if(typeof jQuery!=="undefined"){'.
				'jQuery(document).ready(function($){'.
				'$("#'.$this->formControl.'_'.$this->group.'_feed").closest("li,div.control-group").hide();'.
				'})}'
			);
		}
		
		return parent::getInput() . $html;
	}

	private function checkMailer()
	{
		$app = JFactory::getApplication();
		$uri = JUri::getInstance();
		
		$host = strtolower($uri->getHost());
		$isLocalhsot = ($host == 'localhost' OR $host == '127.0.0.1');
		$domain = str_replace('www.', '', $host);
		if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
			$domain = $regs['domain'];
		}
		
		$global_conf_mailer = '<a href="index.php?option=com_config#jform_mailer" onclick="Cookie.write(\'configuration\',\'server\')" target="_blank">';
		
		// check if mail sending is enabled since J! 3.2
		if (version_compare(JVERSION, '3.2.0') >= 0 AND !$app->getCfg('mailonline', 1)) 
		{
			$app->enqueueMessage(
				JText::sprintf('MOD_PWEBCONTACT_CONFIG_MSG_GLOBAL_MAIL_ONLINE', 
					$global_conf_mailer, '</a>'
				), 'error');
		}
		
		// check if mail from is a valid email
		jimport('joomla.mail.helper');
		if (JMailHelper::isEmailAddress($app->getCfg('mailfrom')) === false) 
		{
			$app->enqueueMessage(
				JText::sprintf('MOD_PWEBCONTACT_CONFIG_MSG_GLOBAL_EMAIL_FROM', 
					$global_conf_mailer, '</a>'
				), 'error');
		}
		// check if mail from is with the same domain as site
		elseif (!$isLocalhsot AND strpos(strtolower($app->getCfg('mailfrom')), $domain) === false) 
		{
			$app->enqueueMessage(
				JText::sprintf('MOD_PWEBCONTACT_CONFIG_MSG_GLOBAL_EMAIL_FROM_DOMAIN', 
					$global_conf_mailer, '</a>',
					$domain
				), 'warning');
		}
		
		// check mailer configuration
		if ($app->getCfg('mailer') == 'mail')
		{
			// check if PHP mail functon is enabled
			if (!function_exists('mail') OR !is_callable('mail')) {
				$app->enqueueMessage(
					JText::sprintf('MOD_PWEBCONTACT_CONFIG_MSG_GLOBAL_PHP_MAILER_DISABLED', 
						$global_conf_mailer, '</a>'
					), 'error');
			}
			// warn that PHP mail might not work on localhost
			elseif ($isLocalhsot) {
				$app->enqueueMessage(
					JText::sprintf('MOD_PWEBCONTACT_CONFIG_MSG_GLOBAL_PHP_MAILER_LOCALHOST', 
						$global_conf_mailer, '</a>'
					), 'warning');
			}
		}
		elseif ($app->getCfg('mailer') == 'smtp')
		{
			// missing configuration for SMTP
			if (!$app->getCfg('smtpauth') OR !$app->getCfg('smtpuser') OR !$app->getCfg('smtppass') OR !$app->getCfg('smtphost')) {
				$app->enqueueMessage(
					JText::sprintf('MOD_PWEBCONTACT_CONFIG_MSG_GLOBAL_SMTP', 
						$global_conf_mailer, '</a>'
					), 'warning');
			}
			elseif (!$isLocalhsot)
			{
				// SMTP user from other domain than site
				if (strpos($app->getCfg('smtpuser'), '@') !== false AND strpos(strtolower($app->getCfg('smtpuser')), $domain) === false) {
					$app->enqueueMessage(
						JText::sprintf('MOD_PWEBCONTACT_CONFIG_MSG_GLOBAL_SMTP_USER_DOMAIN', 
							$global_conf_mailer, '</a>', 
							$domain
						), 'warning');
				}
				// SMTP host from other domain than site
				elseif (strtolower($app->getCfg('smtphost')) != 'localhost' AND strpos(strtolower($app->getCfg('smtphost')), $domain) === false) {
					$app->enqueueMessage(
						JText::sprintf('MOD_PWEBCONTACT_CONFIG_MSG_GLOBAL_SMTP_HOST_DOMAIN', 
							$global_conf_mailer, '</a>', 
							$domain
						), 'warning');
				}
			}
		}
	}

	private function checkModuleDetails($module_id = 0)
	{
		$app = JFactory::getApplication();
		
		// check if module has been assigned to menu items
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('menuid')
			  ->from('#__modules_menu')
			  ->where('moduleid = '.(int)$module_id)
			  ;
		$db->setQuery($query, 0, 1);
		$result = $db->loadResult();
		if ($result === null) 
		{
			$app->enqueueMessage(JText::_('MOD_PWEBCONTACT_CONFIG_MSG_ASSIGN_MENUITEMS'), 'notice');
		}
		
		// check module settings
		$query->clear();
		$query->select('position, published, showtitle')
			  ->from('#__modules')
			  ->where('id = '.(int)$module_id)
			  ;
		$db->setQuery($query);
		$module = $db->loadObject();
		
		// check module position
		if (!$module->position) {
			$app->enqueueMessage(JText::_('MOD_PWEBCONTACT_CONFIG_MSG_POSITION'), 'notice');
		}
		// check if module is published
		if ($module->published != 1) {
			$app->enqueueMessage(JText::_('MOD_PWEBCONTACT_CONFIG_MSG_PUBLISH'), 'notice');
		}
		// check if title is hidden
		if ($module->showtitle AND $module->position != 'debug') {
			$app->enqueueMessage(JText::_('MOD_PWEBCONTACT_CONFIG_MSG_HIDE_TITLE'), 'notice');
		}
	}

	private function checkUploadPath($path) 
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		
		// create wirtable upload path
		if (!JFolder::exists(JPATH_ROOT.$path)) {
			JFolder::create(JPATH_ROOT.$path, 0777);
		}
		if (!is_writable(JPATH_ROOT.$path) AND JPath::canChmod(JPATH_ROOT.$path)) {
			JPath::setPermissions(JPATH_ROOT.$path, null, '0777');
		}
		
		// check upload path
		if (!is_writable(JPATH_ROOT.$path)) {
			$app = JFactory::getApplication();	
			$app->enqueueMessage(JText::sprintf('MOD_PWEBCONTACT_CONFIG_MSG_UPLOAD_DIR', $path), 'warning');
		}
		// copy index.html file to upload path for security
		elseif (!JFile::exists(JPATH_ROOT.$path.'index.html')) {
			JFile::copy(JPATH_ROOT.'/media/mod_pwebcontact/upload/index.html', JPATH_ROOT.$path.'index.html');
		}
	}

	private function checkAjaxComponent()
	{
		if (version_compare(JVERSION, '3.2.0') == -1 AND !is_file(JPATH_ROOT.'/components/com_ajax/ajax.php')) 
			return null;
		
		$app = JFactory::getApplication();
		$db  = JFactory::getDBO();
		
		$query = $db->getQuery(true);
		$query->select($db->quoteName('enabled'))
			  ->from($db->quoteName('#__extensions'))
			  ->where($db->quoteName('element').' = '.$db->quote('com_ajax'))
			  ;
		$db->setQuery($query);
		$enabled = $db->loadResult();
		
		if ($enabled === null) 
		{
			if (is_file(JPATH_ROOT.'/components/com_ajax/ajax.php'))
			{
				$app->enqueueMessage(JText::sprintf('MOD_PWEBCONTACT_CONFIG_MSG_J32_AJAX_INTERFACE_DISCOVER',
					'<a href="index.php?option=com_installer&amp;view=discover&amp;task=discover.refresh" target="_blank">', '</a>',
					version_compare(JVERSION, '3.2.0') == -1 ? JText::sprintf('MOD_PWEBCONTACT_CONFIG_MSG_J32_AJAX_INTERFACE_DELETE', '/components/com_ajax') : ''
				), 'warning');
				return false;
			} 
			else 
			{
				$app->enqueueMessage(JText::_('MOD_PWEBCONTACT_CONFIG_MSG_J32_AJAX_INTERFACE_ERROR'), 'error');
				return false;
			}
		}
		elseif ($enabled === '0' OR $enabled === 0)  
		{
			$app->enqueueMessage(JText::sprintf('MOD_PWEBCONTACT_CONFIG_MSG_J32_AJAX_INTERFACE_ENABLE',
				'<a href="index.php?option=com_installer&amp;view=manage&amp;filter_search=ajax" target="_blank">', '</a>'
			), 'warning');
			return false;
		}
		
		return true;
	}

	private function checkBootstrap()
	{
		$path = JPATH_ROOT.'/media/jui/js/bootstrap.js';
		
		if (is_file($path)) 
		{
			$contents = file_get_contents($path);
			if ($contents AND preg_match('/bootstrap-\w+\.js v2\.1\.0/i', $contents))
			{
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::sprintf('MOD_PWEBCONTACT_CONFIG_MSG_J3_BOOTSTRAP_210_UPDATE',
					'<a href="http://www.perfect-web.co/blog/joomla/62-jquery-bootstrap-in-joomla-25" target="_blank">', '</a>'
				), 'warning');
			}
		}
	}

	private function checkImageTextCreation()
	{
		$functions = array(
			'imagecreatetruecolor',
			'imagecolorallocate',
			'imagecolorallocatealpha',
			'imagesavealpha',
			'imagealphablending',
			'imagefill',
			'imagettftext',
			'imagepng',
			'imagedestroy'
		);
		$disabled_functions = array();
		foreach ($functions as $function)
		{
			if (!(function_exists($function) && is_callable($function))) $disabled_functions[] = $function;
		}
		if (count($disabled_functions)) 
		{
			$app = JFactory::getApplication();
			$doc = JFactory::getDocument();
			
			$app->enqueueMessage(
				JText::sprintf('MOD_PWEBCONTACT_CONFIG_MSG_FUNCTIONS_DSIABLED', 
					implode(', ', $disabled_functions)
				), 'warning');
			
			// disable toggler tab options
			if (version_compare(JVERSION, '3.0.0') == -1) {
				$doc->addScriptDeclaration(
					'window.addEvent("domready",function(){'.
						'$$("#'.$this->formControl.'_'.$this->group.'_toggler_vertical1").each(function(el){'.
							'$$("label[for="+el.get("id")+"]").addClass("disabled").removeEvents("click");'.
						'}).set("disabled", "disabled");'.
					'});'
				);
				$doc->addStyleDeclaration(
					'label.disabled{color:#aaa}'
				);
			} else {
				$doc->addScriptDeclaration(
					'jQuery(document).ready(function($){'.
						'$("#'.$this->formControl.'_'.$this->group.'_toggler_vertical1").each(function(){'.
							'$("label[for="+$(this).attr("id")+"]").addClass("disabled").unbind("click");'.
						'}).prop("disabled", "disabled");'.
					'});'
				);
			}
			
			return false;
		}

		return true;
	}

	private function checkCacheDir()
	{
		$app = JFactory::getApplication();
		
		// check if cache directory is writable
		if (!is_writable(JPATH_CACHE)) {
			$app->enqueueMessage(JText::_('MOD_PWEBCONTACT_CONFIG_MSG_CACHE_DIR'), 'warning');
			return false;
		}
		// check if direct access to files in cache directory is allowed
		else {
			if (file_exists(JPATH_ADMINISTRATOR.'/components/com_admintools/models/htmaker.php') OR 
				file_exists(JPATH_ROOT.'/modules/mod_pwebcontact/skip_http_test.txt')) {
				
				return null;
			}
			
			$session = JFactory::getSession();
			if ($session->get('cache_access', null, 'pwebcontact_config') === true) 
				return true;
			
			$http = false;
			if (version_compare(JVERSION, '3.0.0') == -1) 
			{
				$transport = false;
				$options = new JRegistry;
				$availableAdapters = array('curl', 'socket', 'stream');
				foreach ($availableAdapters as $adapter)
				{
					if (!is_file(JPATH_LIBRARIES . '/joomla/http/transport/'.$adapter.'.php')) continue;
					
					jimport('joomla.http.transport.'.$adapter);
					$class = 'JHttpTransport' . ucfirst($adapter);
					
					try {
						$transport = new $class($options);
					} catch (RuntimeException $e) {
						$transport = false;
					}
					if ($transport !== false) break;
				}
				
				if ($transport !== false) 
					$http = new JHttp($options, $transport);
			}
			else 
			{
				jimport('joomla.http.factory');
				try {
					$http = JHttpFactory::getHttp();
				} catch (RuntimeException $e) {
					$http = false;
				}
			}
			
			if ($http !== false) 
			{
				$file_types = array(
					'css' => true
					//'htc' => true
				);
				foreach ($file_types as $type => $null) {
					try {
						$response = $http->get(JUri::root().'cache/mod_pwebcontact/test.'.$type);
						if ($response->code != 403)
							unset($file_types[$type]);
					} catch (RuntimeException $e) {
						unset($file_types[$type]);
					}
				}
				if (count($file_types)) {
					$app->enqueueMessage(
						JText::sprintf('MOD_PWEBCONTACT_CONFIG_MSG_CACHE_DIR_ALLOW_DIRECT_ACCESSCSS', 
							implode(', ', array_keys($file_types))
						), 'warning');
					return false;
				}
				else $session->set('cache_access', true, 'pwebcontact_config');
			}
		}
		
		return true;
	}

	private function checkAdminToolsPro($path = null)
	{
		if (!file_exists(JPATH_ADMINISTRATOR.'/components/com_admintools/models/htmaker.php')) return null;
		
		$result = true;
		
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		
		$query = $db->getQuery(true);
		$query->select($db->quoteName('value'))
			  ->from($db->quoteName('#__admintools_storage'))
			  ->where($db->quoteName('key').' = '.$db->quote('cparams'))
			  ;
		$db->setQuery($query);
		$res = $db->loadResult();
		
		$config = new JRegistry($res);
		$htconfig = $config->get('htconfig');
		
		if(!empty($htconfig) AND function_exists('base64_decode'))
		{
			$htconfig = json_decode(base64_decode($htconfig), true);
			
			if ($path) {
				$result_exceptionfiles = false;
				if (isset($htconfig['exceptionfiles']) AND is_array($htconfig['exceptionfiles'])) {
					$exceptionfiles = $htconfig['exceptionfiles'];
					foreach ($exceptionfiles as $exceptionfile) {
						if ($exceptionfile == $path) {
							$result_exceptionfiles = true;
							break;
						}
					}
				}
				if (!$result_exceptionfiles) {
					$result = false;
					$app->enqueueMessage(
						JText::sprintf('MOD_PWEBCONTACT_CONFIG_MSG_ADMINTOOLS_EXCEPTIONFILES', 
							$path,
							'<a href="index.php?option=com_admintools&amp;view=htmaker" target="_blank">', '</a>'
						), 'warning');
				}
			}
			
			
			$result_fepexdirs = false;
			if (isset($htconfig['fepexdirs']) AND is_array($htconfig['fepexdirs'])) {
				$fepexdirs = $htconfig['fepexdirs'];
				foreach ($fepexdirs as $dir) {
					if ($dir == 'cache') {
						$result_fepexdirs = true;
						break;
					}
				}
			}
			if (!$result_fepexdirs) {
				$result = false;
				$app->enqueueMessage(
					JText::sprintf('MOD_PWEBCONTACT_CONFIG_MSG_ADMINTOOLS_FEPEXDIRS', 
						'cache',
						'<a href="index.php?option=com_admintools&amp;view=htmaker" target="_blank">', '</a>'
					), 'warning');
			}
			
			
			$result_fepextypes = array(
				'css' => true
				//'htc' => true
			);
			if (isset($htconfig['fepextypes']) AND is_array($htconfig['fepextypes'])) {
				$fepextypes = $htconfig['fepextypes'];
				foreach ($fepextypes as $type) {
					if (array_key_exists($type, $result_fepextypes))
						unset($result_fepextypes[$type]);
					if (!count($result_fepextypes)) 
						break;
				}
			}
			if (count($result_fepextypes)) {
				$result = false;
				$app->enqueueMessage(
					JText::sprintf('MOD_PWEBCONTACT_CONFIG_MSG_ADMINTOOLS_FEPEXTYPES', 
						implode(', ', array_keys($result_fepextypes)),
						'<a href="index.php?option=com_admintools&amp;view=htmaker" target="_blank">', '</a>'
					), 'warning');
			}
		}
		
		return $result;
	}

	private function checkJAT3v2CacheExclude($extension = 'mod_pwebcontact', $type = 'module')
	{
		if (!file_exists(JPATH_ROOT.'/plugins/system/jat3/jat3.php')) return null;
		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('element'))
			  ->from($db->quoteName('#__extensions'))
			  ->where($db->quoteName('type').' = '.$db->quote('template'))
			  ->where($db->quoteName('element').' LIKE '.$db->quote('ja_%'))
			  ->where($db->quoteName('client_id').' = 0')
			  ;
		$db->setQuery($query);
		$templates = $db->loadColumn();
		
		if (!$templates OR !count($templates)) return null;
		
		$app = JFactory::getApplication();
		
		foreach ($templates as $template)
		{
			$params = new JRegistry();
			$params->loadFile(JPATH_ROOT.'/templates/'.$template.'/params.ini', 'ini');
			
			if ($params->get('cache', false) === 1 AND !preg_match('/'.$type.'=[^=]*'.$extension.'/i', $params->get('cache_exclude'))) { 
				$app->enqueueMessage(
					JText::sprintf('MOD_PWEBCONTACT_CONFIG_MSG_JAT3V2_CACHE', 
						$type.'='.$extension,
						'<a href="index.php?option=com_templates&amp;filter_search='.$template.'" target="_blank">', 
						$template, 
						'</a>'
					), 'warning');
			}
		}
		
		return true;
	}
}