<?php
/**
* @version 3.1.0
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
class JFormRulePwebCopyAjax extends JFormRule
{
	/**
	 * The regular expression to use in testing a form field value.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $regex = '[0-9]';

	/**
	 * Method to copy file to Joomla root directory for Ajax calls
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
		jimport('joomla.filesystem.file');
		
		if ($value == 1) 
		{
			if (!JFile::exists(JPATH_ROOT.'/mod_pwebcontact_ajax.php')) 
			{
				$result = JFile::copy(JPATH_ROOT.'/modules/mod_pwebcontact/ajax.php', JPATH_ROOT.'/mod_pwebcontact_ajax.php');
				if (!$result) 
				{
					$element['message'] = JText::sprintf('MOD_PWEBCONTACT_USE_ROOT_PATH_MESSAGE',
												JPATH_ROOT.'/modules/mod_pwebcontact/ajax.php',
												JPATH_ROOT.'/mod_pwebcontact_ajax.php'
					);
					return false;
				}
			}
		}
		else {
			if (JFile::exists(JPATH_ROOT.'/mod_pwebcontact_ajax.php'))
				JFile::delete(JPATH_ROOT.'/mod_pwebcontact_ajax.php');
		}

		return true;
	}
}