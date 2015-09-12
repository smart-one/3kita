<?php
/**
* @version 3.2.4
* @package PWebContact
* @copyright © 2014 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
* @license GNU General Public License http://www.gnu.org/licenses/gpl-3.0.html
* @author Piotr Moćko
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

class mod_pwebcontactInstallerScript
{
	protected $old_manifest 		= null;
	protected $extension_where		= array(
										'type' 		=> 'module',
										'element' 	=> 'mod_pwebcontact',
										'folder' 	=> '',
										'client_id' => 0 // 0 = front-end, 1 = back-end
									);
	
	/**
	 * Constructor
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 */
	public function __construct(JAdapterInstance $adapter) 
	{
		jimport('joomla.registry.registry');
		
		$db = JFactory::getDBO();
		
		$query = $db->getQuery(true);
		$query->select('manifest_cache')
			->from('#__extensions');
		foreach ($this->extension_where as $column => $value)
			$query->where($column.' = '.$db->quote($value));
		$db->setQuery($query);
		
		try {
			$data = $db->loadResult();
		} catch (Exception $e) {
			$data = null;
		}
		
		$this->old_manifest = new JRegistry( $data );
	}

	/**
	 * Called before any type of action
	 *
	 * @param   string  $route  Which action is happening (install|uninstall|discover_install|update)
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function preflight($route, JAdapterInstance $adapter) {}

	/**
	 * Called after any type of action
	 *
	 * @param   string  $route  Which action is happening (install|uninstall|discover_install|update)
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function postflight($route, JAdapterInstance $adapter) 
	{
		$parent = $adapter->getParent();
		
		// install update server for new installation or when updating module older than 3.2.4
		require_once $parent->getPath('source').'/helpers/updateserver.php';
		
		$version = $parent->getManifest()->version;
		
		$updateServer = new modPWebContactUpdateServer;
		
		// delete old md5 files
		$updateServer->deleteMd5($parent->getPath('extension_root').'/form');
		// add new md5 file
		if ($download_id = $updateServer->getMd5($parent->getPath('source'))) {
			$buffer = '';
			JFile::write($parent->getPath('extension_root').'/form/'.$download_id, $buffer);
		}
		
		$updateServer->add( $version, $download_id );
	}

	/**
	 * Called on installation
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function install(JAdapterInstance $adapter) 
	{
		if (version_compare(JVERSION, '3.0.0') == -1) 
		{
			$this->installPwebJ3UI();
		}
		elseif (version_compare(JVERSION, '3.1.4') == -1) 
		{
			$this->installPwebJ3UI('bootstrap');
		}
	}

	/**
	 * Called on update
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function update(JAdapterInstance $adapter)
	{
		$db = JFactory::getDBO();
		
		if (version_compare(JVERSION, '3.0.0') == -1) 
		{
			$this->installPwebJ3UI();
		}
		elseif (version_compare(JVERSION, '3.1.4') == -1) 
		{
			$this->installPwebJ3UI('bootstrap');
		}
		
		if (version_compare($this->old_manifest->get('version', '3.0.0'), '3.0.0') == -1)
		{
			// upgrade options
			
			$query = $db->getQuery(true);
			$query->select('id, params')
				->from('#__modules')
				->where('module = '.$db->quote('mod_pwebcontact'));
			$db->setQuery($query);
			
			try {
				$modules = $db->loadObjectList();
			} catch (Exception $e) {
				$modules = false;
			}
			
			if (is_array($modules))
			{
				foreach ($modules as $module)
				{
					$params = new JRegistry($module->params);
					
					// skip new configuration
					if ($params->get('layout_type', false) !== false) continue;
					
					// toggler tab
					$params->def('toggler_name', 			$params->get('toggle_button'));
					$params->def('toggler_rotate', 			$params->get('toggle_rotate'));
					$params->def('toggler_width', 			$params->get('style_btn_width'));
					$params->def('toggler_height', 			$params->get('style_btn_height'));
					$params->def('toggler_font_size', 		$params->get('style_font'));
					$params->def('toggler_icon_gallery', 	$params->get('style_btn_icon', -1));
					$params->def('toggler_icon', 			$params->get('style_btn_icon') ? 'gallery' : null);
					$params->def('toggler_vertical', 		$params->get('toggle_vertical') ? 1 : 0);
					
					// upload
					if ($params->get('show_upload') AND $params->get('require_upload'))
						$params->set('show_upload', 2);
					$params->set('upload_size_limit', 		round($params->get('upload_size_limit', 1024) / 1024, 2));
					$params->def('upload_files_limit', 		$params->get('upload_file_limit'));
					$params->def('upload_show_limits', 		$params->get('upload_show_size_limit'));
					$params->set('upload_allowed_ext', 		str_replace(',', '|', $params->get('upload_allowed_ext')));
					
					// styles
					$params->def('text_color', 				$params->get('style_color'));
					$params->def('form_font_size', 			$params->get('style_font'));
					$params->def('bg_color', 				$params->get('style_bg'));
					$params->def('bg_opacity', 				$params->get('style_opacity'));
					
					// labels
					if ($params->get('layout') == 'static.static')
						$params->def('labels_position', 	'above');
					
					// modal styles
					$params->set('modal_opacity', 			$params->get('modal_opacity', -1));
					if (strpos($params->get('layout'), 'modal') !== false) {
						if ($params->get('modal_width') > 0)
							$params->def('form_width', 			$params->get('modal_width').'px');
						if ($params->get('modal_image')) {
							$params->def('bg_image', 			$params->get('modal_image'));
							$params->def('bg_padding_position', 'left');
							$params->def('bg_padding', 			'200px');
						}
					}
					
					// auto-popup on page load
					if ($params->get('open_form')) {
						$params->def('open_toggler', 1);
						$params->def('open_count', 0);
					}
					
					// redirect disabled
					if (!$params->get('redirect_enable')) {
						$params->set('redirect_itemid', '');
						$params->set('redirect_url', '');
					}
					
					// redirect delay
					if (($delay = (int)$params->get('redirect_delay'))) {
						$params->set('redirect_delay', round($delay/1000));
					}
					
					// tracking scripts
					$oncomplete = array();
					if ($params->get('analytics_tracker_enabled')) {
						if ($params->get('analytics_tracker_type', 1)) {
							if ($params->get('analytics_tracker_page'))
								$oncomplete[] = '_gaq.push([\'_trackPageview\', \''.$params->get('analytics_tracker_page').'\']);';
							if ($params->get('analytics_tracker_event'))
								$oncomplete[] = '_gaq.push([\'_trackEvent\', '.$params->get('analytics_tracker_event').']);';
						}
						else {
							if ($params->get('analytics_tracker_page'))
								$oncomplete[] = 'pageTracker._trackPageview(\''.$params->get('analytics_tracker_page').'\');';
							if ($params->get('analytics_tracker_event'))
								$oncomplete[] = 'pageTracker._trackEvent('.$params->get('analytics_tracker_event').');';
						}
					}
					$oncomplete[] = $params->get('custom_script');
					$params->def('oncomplete', implode("\r\n", $oncomplete));
					
					// integrations
					if ($params->get('comprofiler') AND $params->get('comprofiler_userprofile'))
						$params->set('comprofiler', 2);
					if ($params->get('jomsocial') AND $params->get('jomsocial_userprofile'))
						$params->set('jomsocial', 2);
					if ($params->get('sobipro') AND $params->get('sobipro_entry'))
						$params->set('sobipro', 2);
					
					// layout
					switch (substr($params->get('layout'), 0, strpos($params->get('layout'), '.'))) {
						case 'modal':
							$params->def('layout_type', 'modal');
							break;
						case 'static':
							$params->def('layout_type', 'static');
							$params->def('position', 'static:');
							break;
						default:
							$params->def('layout_type', 'slidebox');
					}
					
					if ($params->get('layout_type') == 'slidebox' OR $params->get('layout_type') == 'modal') {
						switch (substr($params->get('layout'), strpos($params->get('layout'), '-'))) {
							case 'right':
								$params->def('position', 'right:top');
								$params->def('offset', $params->get('style_top'));
								break;
							case 'top':
								$params->def('position', 'top:left');
								$params->def('offset', $params->get('style_left'));
								break;
							case 'bottom':
								$params->def('position', 'bottom:left');
								$params->def('offset', $params->get('style_left'));
								break;
							case 'static':
								$params->def('position', 'static:');
								break;
							default:
								$params->def('layout_type', 'left:top');
								$params->def('offset', $params->get('style_top'));
						}
					}
					
					$params->set('layout', 'default');
					
					// fields
					$fields = array();
					if ($params->get('pretext')) {
						$field = new stdClass;
						$field->type = 'separator_text';
						$field->name = $params->get('pretext');
						$fields[] = $field;
					}
					$field = new stdClass;
					$field->type = 'separator_system_top';
					$fields[] = $field;
					
					for ($i = 1; $i <= $params->get('fields_before', 0); $i++) {
						switch ($params->get('field_'.$i.'_type')) {
							case 'select':
								$type = 'select';
								$field_params = 'MOD_PWEBCONTACT_SELECT';
								break;
							case 'multiple':
								$type = 'multiple';
								$field_params = 4;
								break;
							case 'radio':
								$type = 'radio';
								$field_params = 1;
								break;
							case 'checkbox':
								$type = 'checkboxes';
								$field_params = 1;
								break;
							case 'textarea':
								$type = 'textarea';
								$field_params = 5;
								break;
							case 'calendar':
								$type = 'date';
								$field_params = '%d-%m-%Y';
								break;
							case 'text':
								$type = 'text';
								$field_params = '';
								break;
							default: 
								$type = false;
						}
						if ($type) {
							$field = new stdClass;
							$field->type = $type;
							$field->name = $params->get('field_'.$i.'_label');
							$field->alias = 'field_'.$i;
							$field->values = $params->get('field_'.$i.'_values');
							$field->tooltip = '';
							$field->params = $field_params;
							$field->required = $params->get('field_'.$i.'_require') == 1;
							$fields[] = $field;
						}
					}
					
					if ($params->get('show_name')) {
						$field = new stdClass;
						$field->type = 'name';
						$field->name = 'MOD_PWEBCONTACT_NAME';
						$field->alias = 'name';
						$field->values = '';
						$field->tooltip = '';
						$field->params = '';
						$field->required = $params->get('require_name') == 1;
						$fields[] = $field;
					}
					if ($params->get('show_email')) {
						$field = new stdClass;
						$field->type = 'email';
						$field->name = 'MOD_PWEBCONTACT_EMAIL';
						$field->alias = 'email';
						$field->values = '';
						$field->tooltip = '';
						$field->params = '';
						$field->required = $params->get('require_email') == 1;
						$fields[] = $field;
					}
					if ($params->get('show_phone')) {
						$field = new stdClass;
						$field->type = 'phone';
						$field->name = 'MOD_PWEBCONTACT_PHONE';
						$field->alias = 'phone';
						$field->values = '';
						$field->tooltip = '';
						$field->params = '/[\\d\\-\\+() ]+/';
						$field->required = $params->get('require_phone') == 1;
						$fields[] = $field;
					}
					
					while ($params->get('field_'.$i.'_require', false) !== false) {
						
						switch ($params->get('field_'.$i.'_type')) {
							case 'select':
								$type = 'select';
								$field_params = 'MOD_PWEBCONTACT_SELECT';
								break;
							case 'multiple':
								$type = 'multiple';
								$field_params = 4;
								break;
							case 'radio':
								$type = 'radio';
								$field_params = 1;
								break;
							case 'checkbox':
								$type = 'checkboxes';
								$field_params = 1;
								break;
							case 'textarea':
								$type = 'textarea';
								$field_params = 5;
								break;
							case 'calendar':
								$type = 'date';
								$field_params = '%d-%m-%Y';
								break;
							case 'text':
								$type = 'text';
								$field_params = '';
								break;
							default: 
								$type = false;
						}
						if ($type) {
							$field = new stdClass;
							$field->type = $type;
							$field->name = $params->get('field_'.$i.'_label');
							$field->alias = 'field_'.$i;
							$field->values = $params->get('field_'.$i.'_values');
							$field->tooltip = '';
							$field->params = $field_params;
							$field->required = $params->get('field_'.$i.'_require') == 1;
							$fields[] = $field;
						}
						
						$i++;
					}
					
					if ($params->get('show_message')) {
						$field = new stdClass;
						$field->type = 'textarea';
						$field->name = 'MOD_PWEBCONTACT_MESSAGE';
						$field->alias = 'message';
						$field->values = '';
						$field->tooltip = '';
						$field->params = '3|'.$params->get('chars_limit', 0);
						$field->required = $params->get('require_message') == 1;
						$fields[] = $field;
					}
					
					$field = new stdClass;
					$field->type = 'separator_upload';
					$fields[] = $field;
					
					if ($params->get('show_agree')) {
						$field = new stdClass;
						$field->type = 'checkbox';
						$field->name = $params->get('agree_label', 'Agree to terms');
						$field->alias = 'agree';
						$field->values = 'JYes';
						$field->tooltip = '';
						$field->params = $params->get('agree_url');
						$field->required = true;
						$fields[] = $field;
					}
					
					$field = new stdClass;
					$field->type = 'separator_system_bottom';
					$fields[] = $field;
					
					$params->def('fields', json_encode($fields));
					
					
					// update params in database
					$module->params = $params->toString();
					
					$query->clear()
						->update('#__modules')
						->set('params = '.$db->quote($db->escape($module->params)))
						->where('id = '.$module->id);
					$db->setQuery($query);
					
					try {
						$db->execute();
					} catch (Exception $e) {
					
					}
				}
			}
			
			
			// remove old update server from module version 1.5 - 2.1.3
			$this->removeUpdateServer('http://www.perfect-web.pl/updates/mod_pwebcontact-update.xml');
			
			
			// remove old files
			$media_files = array(
				'css/debug.css',
				'css/tooltip.css',
				'email_tmpl/admin_html.php',
				'email_tmpl/admin_text.php',
				'email_tmpl/user_html.php',
				'email_tmpl/user_text.php',
				'images/contact.jpg',
				'images/fail.png',
				'images/show-hide.png',
				'images/success.png',
				'js/mootools.pwebcontact.js',
				'js/uploader.js'
			);
			$media_folders = array(
				'css/default',
				'css/general',
				'css/modal',
				'css/static',
				'images/toggler'
			);
			$module_files = array(
				'email_template.php',
				'email_template_html.php',
				'form/fields/logo.png',
				'form/fields/pwebsnippets.php',
				'form/fields/pwebtoolbar.php',
				'tmpl/modal.php',
				'tmpl/static.php'
			);
			$module_folders = array(
				'css',
				'email_tmpl',
				'fields',
				'form/elements',
				'helpers15',
				'images',
				'js'
			);
			
			foreach ($media_files as $file)
				if (JFile::exists(JPATH_ROOT.'/media/mod_pwebcontact/'.$file))
					JFile::delete(JPATH_ROOT.'/media/mod_pwebcontact/'.$file);
			foreach ($media_folders as $folder)
				if (JFolder::exists(JPATH_ROOT.'/media/mod_pwebcontact/'.$folder))
					JFolder::delete(JPATH_ROOT.'/media/mod_pwebcontact/'.$folder);
			foreach ($module_files as $file)
				if (JFile::exists(JPATH_ROOT.'/modules/mod_pwebcontact/'.$file))
					JFile::delete(JPATH_ROOT.'/modules/mod_pwebcontact/'.$file);
			foreach ($module_folders as $folder)
				if (JFolder::exists(JPATH_ROOT.'/modules/mod_pwebcontact/'.$folder))
					JFolder::delete(JPATH_ROOT.'/modules/mod_pwebcontact/'.$folder);
		}
		
		if (version_compare($this->old_manifest->get('version', '3.0.0'), '3.1.0') == -1)
		{
			if (JFile::exists(JPATH_ROOT.'/modules/mod_pwebcontact/helpers/helper.php'))
				JFile::delete(JPATH_ROOT.'/modules/mod_pwebcontact/helpers/helper.php');
		}
		
		if (version_compare($this->old_manifest->get('version', '3.0.0'), '3.2.0') == -1)
		{
			if (JFile::exists(JPATH_ROOT.'/media/mod_pwebcontact/js/admin.js'))
				JFile::delete(JPATH_ROOT.'/media/mod_pwebcontact/js/admin.js');
		}
		
		if (version_compare($this->old_manifest->get('version', '3.0.0'), '3.2.4') == -1)
		{
			// remove old update server from module version 2.2.0 - 3.2.3
			$this->removeUpdateServer('http://www.perfect-web.co/updates/mod_pwebcontact-update.xml');
		}
		
		if (JFile::exists(JPATH_ROOT.'/mod_pwebcontact_ajax.php')) {
			JFile::delete(JPATH_ROOT.'/mod_pwebcontact_ajax.php');
			if (!is_file(JPATH_ROOT.'/components/com_ajax/ajax.php'))
				JFile::copy(JPATH_ROOT.'/modules/mod_pwebcontact/ajax.php', JPATH_ROOT.'/mod_pwebcontact_ajax.php');
		}
	}

	/**
	 * Called on uninstallation
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 */
	public function uninstall(JAdapterInstance $adapter) {}
	
	
	protected function installPwebJ3UI($msg = 'jquery')
	{
		if (!is_file(JPATH_PLUGINS.'/system/pwebj3ui/pwebj3ui.php'))
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage(
				 'Install also required plugin'
				.' <a href="http://www.perfect-web.co/blog/joomla/62-jquery-bootstrap-in-joomla-25" target="_blank">Perfect Joomla! 3 User Interface</a>'
				.($msg == 'jquery' 
					? ' to extend your Joomla! with native support of jQuery and Bootstrap from Joomla! 3'
					: ' to update Bootstrap to version 2.3.1 from Joomla! 3.1.4+ to fix Lightbox bug'
				).'. <a href="javascript:void()" onclick="document.getElementById(\'install_url\').value=\'http://www.perfect-web.co/downloads/plg_system_pwebj3ui.zip\';Joomla.submitbutton4()">'
				.'Click here</a> to install.'
			, 'warning');
		}
	}
	
	
	protected function removeUpdateServer($location = null)
	{
		$db = JFactory::getDBO();
		
		$query = $db->getQuery(true);
		$query->select('update_site_id')
			->from('#__update_sites')
			->where('location LIKE '.$db->quote($db->escape($location)));
		$db->setQuery($query);
		
		try {
			$update_site_id = (int)$db->loadResult();
		} catch (Exception $e) {
			$update_site_id = false;
		}
		
		if ($update_site_id) 
		{
			$query->clear()
				->delete('#__update_sites_extensions')
				->where('update_site_id = '.$update_site_id);
			$db->setQuery($query);
			try {
				$db->execute();
			} catch (Exception $e) {
			
			}
			
			$query->clear()
				->delete('#__update_sites')
				->where('update_site_id = '.$update_site_id);
			$db->setQuery($query);
			try {
				$db->execute();
			} catch (Exception $e) {
			
			}
		}
	}
}