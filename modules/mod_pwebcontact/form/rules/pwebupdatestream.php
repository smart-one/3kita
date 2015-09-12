<?php
/**
* @version 3.2.4
* @package PWebContact
* @copyright © 2014 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
* @license GNU General Public License http://www.gnu.org/licenses/gpl-3.0.html
* @author Piotr Moćko
*/

defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.form.formrule');

/**
 * Form Rule class for the Joomla Framework.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormRulePwebUpdateStream extends JFormRule
{
	/**
	 * Method to enable/disable updates stream
	 *
	 * @param   object  $element	The JXMLElement object representing the <field /> tag for the
	 * 								form field object.
	 * @param   mixed   $value		The form field value to validate.
	 * @param   string  $group		The field name group control value. This acts as as an array
	 * 								container for the field. For example if the field has name="foo"
	 * 								and the group value is set to "bar" then the full field name
	 * 								would end up being "bar[foo]".
	 * @param   object  $input		An optional JRegistry object with the entire data set to validate
	 * 								against the entire form.
	 * @param   object  $form		The form object for which the field is being tested.
	 *
	 * @return  boolean  True if the value is valid, false otherwise.
	 *
	 * @since   11.1
	 * @throws  JException on invalid rule.
	 */
	public function test(& $element, $value, $group = null, & $input = null, & $form = null)
	{
		require_once JPATH_ROOT.'/modules/mod_pwebcontact/helpers/updateserver.php';
		$updateServer = new modPWebContactUpdateServer;
		
		if ($value == 1 OR $value == 2)
		{
			$updateServer->add(); // 2nd argument: null = commercial, false = free
		}
		else
		{
			$updateServer->delete();
		}
		
		// change option in other instances of module
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('id, params')
			->from('#__modules')
			->where('module = '.$db->quote('mod_pwebcontact'))
			->where('params NOT LIKE '.$db->quote('%'.$db->escape('"feed":"'.$value.'"').'%') )
			->where('id != '.(int)JFactory::getApplication()->input->getInt('id'));
		$db->setQuery($query);
		
		try {
			$modules = $db->loadObjectList();
		} catch (Exception $e) {
			$modules = false;
		}
		
		if ($modules)
		{
			foreach ($modules as $module)
			{
				$module->params = preg_replace('/"feed":"\d+"/i', '"feed":"'.$value.'"', $module->params);
				
				$query->clear()
					->update('#__modules')
					->set('params = '.$db->quote($db->escape($module->params)) )
					->where('id = '.(int)$module->id);
				$db->setQuery($query);
				
				try {
					$db->execute();
				} catch (Exception $e) {
				
				}
			}
		}

		return true;
	}
}