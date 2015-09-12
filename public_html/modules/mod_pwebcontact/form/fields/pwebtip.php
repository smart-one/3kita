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
 * Tpis
 */
class JFormFieldPwebTip extends JFormField
{
	protected $type = 'PwebTip';
	
	
	protected function getInput()
	{
		$html = '';
		
		$app = JFactory::getApplication();
		
		$module_id = $app->input->getInt('id', 0);
		if ($module_id > 0) 
		{
			switch ($this->element['tip'])
			{
				case 'javascript':
					$html .= 
						 '<div class="pweb-description">'
						 
						.JText::sprintf('MOD_PWEBCONTACT_JAVASCRIPT_DESC',
							'<code>pwebContact'.$module_id.'.toggleForm();</code>',
							'<code>pwebContact'.$module_id.'.toggleForm(1);</code>',
							'<code>pwebContact'.$module_id.'.toggleForm(0);</code>',
							'<code>pwebContact'.$module_id.'.toggleForm(1, 3);</code>'
						)
						
						.'</div>';
					break;
					
				case 'menu':
				
					$link = null;
					if (version_compare(JVERSION, '2.5.14') <= 0 OR (version_compare(JVERSION, '3.0.0') >= 0 AND version_compare(JVERSION, '3.1.5') <= 0)) 
					{
						$app->setUserState('com_menus.edit.item.link', 'javascript:pwebContact'.$module_id.'.toggleForm()');
						$link = JText::sprintf('MOD_PWEBCONTACT_ADD_TO_MENU_URL_DESC', '<code>javascript:pwebContact'.$module_id.'.toggleForm()</code>');
					}
					
					$html .= 
						 '<div class="pweb-description">'
						 
						.JText::sprintf('MOD_PWEBCONTACT_ADD_TO_MENU_DESC',
							'<a href="#" class="pweb-menuitems-all">', '</a>',
							'<code>pwebcontact'.$module_id.'_toggler</code>',
							$link,
							'<a href="index.php?option=com_menus&amp;view=item&amp;layout=edit&amp;type=url" target="_blank">', '</a>'
						)
						
						.'</div>';
					
					JText::script('MOD_PWEBCONTACT_ASSIGNED_TO_ALL_MENU_ITEMS');
					
					break;
					
				case 'open':
					$html .= 
						 '<div class="pweb-description">'
						 
						.JText::sprintf('MOD_PWEBCONTACT_OPEN_FROM_DESC', 
							'<code>&lt;a href="#" class="pwebcontact'.$module_id.'_toggler"&gt;Click here&lt;/a&gt;</code>',
							'<code>&lt;a href="#" class="pwebcontact'.$module_id.'_toggler"&gt;&lt;img src="..."&gt;&lt;/a&gt;</code>',
							'<code>#pwebcontact'.$module_id.':open</code>'
						)
						
						.'</div>';
					break;
				
				case 'preload':
					$html .= 
						 '<div class="pweb-description">'
						 
						.JText::sprintf('MOD_PWEBCONTACT_PRELOAD_FIELDS_DESC', 
							'<code>#pwebcontact'.$module_id.':name=Tester/message=Testing/checkboxes=Option A;Option B</code>',
							'<code>#pwebcontact'.$module_id.':open:name=Tester/message=Testing/checkboxes=Option A;Option B</code>',
							'<code>data-pwebcontact-fields</code>',
							'<code>&lt;a href="#" class="pwebcontact'.$module_id.'_toggler" data-pwebcontact-fields="name=Tester/message=Testing"&gt;Click here&lt;/a&gt;</code>',
							'<code>data-pwebcontact-fields-once</code>',
							'<code>&lt;a href="#" class="pwebcontact'.$module_id.'_toggler" data-pwebcontact-fields-once="name=Tester/message=Testing"&gt;Click here&lt;/a&gt;</code>',
							'<code>pwebContact'.$module_id.'.preloadFields("name=Tester/message=Testing");</code>',
							'<code>/</code>',
							'<code>=</code>',
							'<code>;</code>'
						)
						
						.'</div>';
					break;
				
				case 'article':
					$html .= 
						 '<div class="pweb-description">'
						 
						.JText::sprintf('MOD_PWEBCONTACT_ADD_TO_ARTICLE_DESC', 
							'<code>pwebcontact'.$module_id.'</code>', 
							'<a href="#" class="pweb-set-position" data-position="pwebcontact'.$module_id.'">', '</a>',
							'<a href="#" class="pweb-menuitems-all">', '</a>',
							'<code>{loadposition pwebcontact'.$module_id.'}</code>'
						)
						
						.(JPluginHelper::isEnabled('content', 'loadmodule') ? '' :
							 '<br><a href="index.php?option=com_plugins&amp;view=plugins&amp;filter_search=loadmodule" target="_blank">'
							.JText::_('MOD_PWEBCONTACT_ENABLE_PLUGIN_LOADMODULE')
							.'</a>'
						)
						
						.'</div>';
					
					JText::script('MOD_PWEBCONTACT_POSITION_SET');
					JText::script('MOD_PWEBCONTACT_ASSIGNED_TO_ALL_MENU_ITEMS');
					
					break;
				
				case 'upload_path':
					$html .= '<div class="pweb-description"><code>media/mod_pwebcontact/upload/'.$module_id.'/</code></div>';
					break;
			}
		}
		
		if (!$html) 
		{
			switch ($this->element['tip'])
			{
				case 'language':
					
					$html .= 
						 '<div class="pweb-description">'
						.JText::sprintf('MOD_PWEBCONTACT_LANGUAGE_OVERRIDE_DESC', 
							'<a href="index.php?option=com_languages&amp;view=overrides" target="_blank">', '</a>'
						);
					
					$langs = JLanguage::getKnownLanguages(JPATH_ROOT);
					foreach ($langs as $lang) {
						$path = '/language/'.$lang['tag'].'/'.$lang['tag'].'.mod_pwebcontact.ini';
						if (file_exists(JPATH_ROOT.$path)) {
							$html .= '<br><code>'.$path.'</code> '
									.'<a href="#" onclick="Joomla.popupWindow(\'..'.$path.'\', \'Language\', 900, 500, 1);return false;">'
									.'<i class="icon-eye"></i> '.JText::_('MOD_PWEBCONTACT_PREVIEW_BUTTON').'</a>';
						}
					}
					
					$html .= '</div>';
					
					break;
				
				case 'upload_size':
					$max_size = $this->convertSize(ini_get('post_max_size'));
					$html .= '<div class="pweb-description"><span class="badge badge-info">'.$max_size.' MB</span></div>'.
							'<script type="text/javascript">var pwebUploadMaxSize = '.$max_size.';</script>';
					break;
					
				case 'version':
					
					$db = JFactory::getDBO();
					$query = $db->getQuery(true);
					$query->select('manifest_cache')
						->from('#__extensions')
						->where(array(
							'type = '.$db->quote('module'),
							'element = '.$db->quote('mod_pwebcontact')
						));
					$db->setQuery($query);
					try {
						$manifest = $db->loadResult();
					} catch (RuntimeException $e) {
						$manifest = null;
					}
					
					$version = 'unknow';
					if ($manifest) {
						$manifest = new JRegistry($manifest);
						$version = $manifest->get('version');
					}
					
					$html .= '<div class="pweb-description"><span class="badge badge-inverse">'.$version.'</span></div>';
					break;
					
				default:
					if (!$module_id) {
						$html .= '<span class="badge badge-warning"><i class="icon-warning"></i>'.JText::_('MOD_PWEBCONTACT_CONFIG_MSG_SAVE_CONFIGURATION').'</span>';
					}
			}
		}

		return $html;
	}

	private function convertSize($str)
    {
        $val = trim($str);
        $last = strtolower($str[strlen($str)-1]);
        switch($last) 
		{
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;
        }
		$val = $val / 1024 / 1024;
		
        return $val > 10 ? intval($val) : round($val, 2);
    }
}