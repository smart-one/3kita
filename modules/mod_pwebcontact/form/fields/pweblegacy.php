<?php
/**
* @version 3.1.0
* @package PWebContact
* @copyright © 2014 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
* @license GNU General Public License http://www.gnu.org/licenses/gpl-3.0.html
* @author Piotr Moćko
*/

defined('_JEXEC') or die( 'Restricted access' );

/**
 * Joomla 2.5 Legacy
 */
class JFormFieldPwebLegacy extends JFormField
{
	protected $type = 'PwebLegacy';
	
	
	protected function getInput()
	{
		if (version_compare(JVERSION, '3.0.0') == -1) 
		{
			$app = JFactory::getApplication();
			$doc = JFactory::getDocument();
			
			if (version_compare(PHP_VERSION, '5.2.4') == -1) 
			{
				$app->enqueueMessage(JText::sprintf('MOD_PWEBCONTACT_CONFIG_MSG_PHP_VERSION', '5.2.4'), 'error');
			} 
			
			// jQuery and Bootstrap in Joomla 2.5
			if (!class_exists('JHtmlJquery'))
			{
				$error = null;
				if (!is_file(JPATH_PLUGINS.'/system/pwebj3ui/pwebj3ui.php'))
				{
					$error = JText::sprintf('MOD_PWEBCONTACT_CONFIG_INSTALL_PWEBLEGACY', 
								'<a href="http://www.perfect-web.co/blog/joomla/62-jquery-bootstrap-in-joomla-25" target="_blank">', '</a>');
				}
				elseif (!JPluginHelper::isEnabled('system', 'pwebj3ui')) 
				{
					$error = JText::sprintf('MOD_PWEBCONTACT_CONFIG_ENABLE_PWEBLEGACY', 
								'<a href="index.php?option=com_plugins&amp;view=plugins&amp;filter_search='.urlencode('Perfect Joomla! 3 User Interface').'" target="_blank">', '</a>');
				}
				else 
				{
					JLoader::import('cms.html.jquery', JPATH_PLUGINS.DIRECTORY_SEPARATOR.'system'.DIRECTORY_SEPARATOR.'pwebj3ui'.DIRECTORY_SEPARATOR.'libraries');
				}
				
				if ($error) {
					$app->enqueueMessage($error, 'error');
					$doc->addScriptDeclaration(
						'window.addEvent("domready", function(){'
							.'new Element("div", {class: "pweb-fields-tip", html: \'<span class="badge badge-important">'.$error.'</span>\'}).inject(document.id("jform_params_fields"),"top");'
						.'});'
					);
				}
			}
			
			$doc->addStyleSheet(JUri::root(true).'/media/mod_pwebcontact/css/admin_j25.css');
		}
		
		return null;
	}
	
	
	protected function getLabel()
	{
		return null;
	}
}