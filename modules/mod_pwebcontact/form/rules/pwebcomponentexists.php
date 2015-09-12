<?php
/**
* @version 3.2.1
* @package PWebContact
* @copyright © 2013 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
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
class JFormRulePwebComponentExists extends JFormRule
{
	/**
	 * Method to test if required component is installed
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
		if ($value > 0) {
			
			if (isset($element['component'])) {
				$component = (string) $element['component'];
				if ($component) {
					$result = file_exists(JPATH_ROOT.'/components/com_'.$component.'/'.$component.'.php');
					if (!$result)
						$result = file_exists(JPATH_ADMINISTRATOR.'/components/com_'.$component.'/'.$component.'.php');
					
					if ($result)
					{
						$db = JFactory::getDBO();
						$query = $db->getQuery(true);
						$query->select($db->quoteName('enabled'))
							  ->from($db->quoteName('#__extensions'))
							  ->where($db->quoteName('element').' = '.$db->quote('com_'.$component))
							  ;
						$db->setQuery($query);
						$enabled = $db->loadResult();
						
						if ($enabled === null OR $enabled === 0) $result = false; 
					}
					return $result;
				}
			}
			elseif (isset($element['path'])) {
				$path = (string) $element['path'];
				if ($path) 
					return file_exists(JPATH_ROOT.'/'.$path);
			}
		}

		return true;
	}
}