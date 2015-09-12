<?php
/**
* @version 1.6.2
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
class JFormRulePwebEmails extends JFormRule
{
	/**
	 * The regular expression to use in testing a form field value.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $regex = '^[\w.-]+(\+[\w.-]+)*@\w+[\w.-]*?\.\w{2,4}(,[ ]*[\w.-]+(\+[\w.-]+)*@\w+[\w.-]*?\.\w{2,4})*$';

	/**
	 * Method to test the email address or list of emails separated with coma
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
		// If the field is empty and not required, the field is valid.
		$required = ((string) $element['required'] == 'true' || (string) $element['required'] == 'required');
		if (!$required && empty($value)) {
			return true;
		}

		// Test the value against the regular expression.
		if (!parent::test($element, $value, $group, $input, $form)) {
			return false;
		}

		return true;
	}
}