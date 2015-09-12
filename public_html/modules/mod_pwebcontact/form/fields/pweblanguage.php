<?php
/**
* @version 3.0
* @package PWebContact
* @copyright © 2013 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
* @license GNU General Public License http://www.gnu.org/licenses/gpl-3.0.html
* @author Piotr Moćko
*/

defined('_JEXEC') or die( 'Restricted access' );

/**
 * Administrator language
 */
class JFormFieldPwebLanguage extends JFormField
{
	protected $type = 'PwebLanguage';
	
	
	protected function getInput()
	{
		$lang = JFactory::getLanguage();
		$lang->load('mod_pwebcontact_admin', JPATH_ROOT);
		
		return null;
	}
	
	
	protected function getLabel()
	{
		return null;
	}
}