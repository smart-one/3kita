<?php
/**
* @version 3.2.8
* @package PWebContact
* @subpackage Zoo
* @copyright © 2014 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
* @license GNU General Public License http://www.gnu.org/licenses/gpl-3.0.html
* @author Piotr Moćko
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

class modPWebContactZooHelper
{
	public static function isItemView() 
	{
		$app = JFactory::getApplication();
		if ( $app->input->get('option') == 'com_zoo' AND $app->input->get('task') == 'item' ) {
			
			return true;
		}
		
		return false;
	}


	public static function getHiddenField() 
	{
		$app 	= JFactory::getApplication();
		$params = modPwebcontactHelper::getParams();
		$html 	= '';
		
		if ( $params->get('zoo') && ($item_id = $app->input->getInt('item_id', 0)) ) {
			
			if ($params->get('zoo_item') == 2) {
				$html = '<input type="hidden" name="zoo" value="'.$item_id.'">';
			}
			elseif ($app->input->get('option') == 'com_zoo' AND $app->input->get('task') == 'item') {
				$html = '<input type="hidden" name="zoo" value="'.$item_id.'">';
			}
		}
		return $html;
	}


	public static function getEmail($item_id = 0) 
	{
		// load config
		require_once(JPATH_ADMINISTRATOR.'/components/com_zoo/config.php');

		// get app
		$zoo = App::getInstance('zoo');

		// get current Zoo item
		$item = $zoo->table->item->get($item_id);
		if ($item) {
		
			if (!in_array($item->type, array('author', 'employee'))) {
			
				$elements = $item->getElements();
				$email = $item = null;
				foreach ($elements as $element) {
					// Find email for current Zoo item
					if ($element->getElementType() == 'email') {
						$email = $element->get('value');
						if ($item) break;
					}
					// Find author or employee ID for current Zoo item
					if (in_array(strtolower($element->config->get('name')), array('author', 'employee'))) {
						if ($element->getElementType() == 'relateditems') {
							$relateditems = $element->get('item', array());
							if (isset($relateditems[0])) {
								// get author or employee Zoo item
								$item = $zoo->table->item->get( $relateditems[0] );
								if ($email) break;
							}
						}
					}
				}
				if ($email) return $email;
			}
			
			if ($item) {
				// get email for Zoo author or employee
				$elements = $item->getElements();
				foreach ($elements as $element) {
					if ($element->getElementType() == 'email') {
						return $element->get('value');
					}
				}
			}
		}
		
		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		
		//get Zoo created by user email
		$query->select('u.email')
			  ->from('#__zoo_item AS i')
			  ->join('LEFT', '#__users AS u ON u.id = i.created_by')
			  ->where('i.id = ' . (int)$item_id)
			  ;
		$db->setQuery($query);
		try {
			$email = $db->loadResult();
		} catch (RuntimeException $e) {
			if (PWEBCONTACT_DEBUG) modPwebcontactHelper::setLog('Zoo query error: '.$e->getMessage());
			return false;
		}
		if (version_compare(JVERSION, '3.0.0') == -1 AND $error = $db->getErrorMsg()) {
			if (PWEBCONTACT_DEBUG) modPwebcontactHelper::setLog('Zoo query error: '.$error);
			return false;
		}
		if (!empty($email)) {
			return $email;
		}
		
		return false;
	}


	public static function getFieldValue($item_id = 0, $field_name = null) 
	{
		// load config
		require_once(JPATH_ADMINISTRATOR.'/components/com_zoo/config.php');

		// get app
		$zoo = App::getInstance('zoo');

		// get current Zoo item
		$item = $zoo->table->item->get($item_id);
		if ($item) {
			$elements = $item->getElements();
			foreach ($elements as $element) {
				if ($element->config->get('name') === $field_name) {
					try {
						// get text element value
						return $element->get('value');
					} catch (Exception $e) {
						
					}
				}
			}
		}
		
		return null;
	}
}