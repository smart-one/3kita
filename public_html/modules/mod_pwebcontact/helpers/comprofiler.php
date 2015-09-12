<?php
/**
* @version 3.2.8
* @package PWebContact
* @subpackage Community Builder
* @copyright © 2014 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
* @license GNU General Public License http://www.gnu.org/licenses/gpl-3.0.html
* @author Piotr Moćko
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

class modPWebContactComprofilerHelper
{
	public static function isUserProfileView() 
	{
		$app = JFactory::getApplication();
		if ( $app->input->get('option') == 'com_comprofiler' && $app->input->get('task') == 'userprofile' && ($profile_id = $app->input->getInt('user', 0)) ) {
			//check if current user has access rights
			if (self::hasAccessRights($profile_id)) return true;
		}
		
		return false;
	}
	
	
	public static function getHiddenField() 
	{
		$app 	= JFactory::getApplication();
		$params = modPwebcontactHelper::getParams();
		$html 	= '';
		
		if ( $params->get('comprofiler') && ($profile_id = $app->input->getInt('user', 0)) ) {
			
			if ($params->get('comprofiler') == 2) {
				$html = '<input type="hidden" name="comprofiler" value="'.$profile_id.'">';
			}
			elseif ($app->input->get('option') == 'com_comprofiler' && $app->input->get('task') == 'userprofile') {
				//check if current user has access rights
				if (self::hasAccessRights($profile_id)) {
					$html = '<input type="hidden" name="comprofiler" value="'.$profile_id.'">';
				}
			}
		}
		return $html;
	}
	
	
	public static function getEmail($profile_id = 0) 
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		
		//get Community Builder user email
		$query->select('u.email')
			  ->from('#__comprofiler AS c')
			  ->join('LEFT', '#__users AS u ON u.id = c.user_id')
			  ->where('c.id = ' . (int)$profile_id)
			  ;
		$db->setQuery($query);
		
		try {
			$email = $db->loadResult();
		} catch (RuntimeException $e) {
			if (PWEBCONTACT_DEBUG) modPwebcontactHelper::setLog('Community Builder query error: '.$e->getMessage());
			return false;
		}
		if (version_compare(JVERSION, '3.0.0') == -1 AND $error = $db->getErrorMsg()) {
			if (PWEBCONTACT_DEBUG) modPwebcontactHelper::setLog('Community Builder query error: '.$error);
			return false;
		}
		if (!empty($email)) {
			return $email;
		}
		
		return false;
	}
	
	
	private static function hasAccessRights($profile_id = 0) 
	{
		return true;
	}
}