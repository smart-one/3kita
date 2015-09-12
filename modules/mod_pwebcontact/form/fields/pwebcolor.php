<?php
/**
* @version 3.1.0
* @package PWebContact
* @copyright © 2013 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
* @license GNU General Public License http://www.gnu.org/licenses/gpl-3.0.html
* @author Piotr Moæko
*/

defined('_JEXEC') or die( 'Restricted access' );

JFormHelper::loadFieldClass('color');

if (version_compare(JVERSION, '3.1.0') >= 0)
{
	class JFormFieldPwebColor extends JFormFieldColor {}
}
else
{
	// Joomla 2.5 and 3.0 color field
	class JFormFieldPwebColor extends JFormFieldColor
	{
		protected $type = 'PwebColor';

		protected function getInput()
		{
			// remove default value
			$clear = empty($this->value) ? true : false;
			
			$html = parent::getInput();
			
			return $clear ? str_replace('#000000', '', $html) : $html;
		}
	}
}