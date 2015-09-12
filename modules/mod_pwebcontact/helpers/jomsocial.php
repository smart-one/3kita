<?php
/**
* @version 3.2.8
* @package PWebContact
* @subpackage JomSocial
* @copyright © 2014 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
* @license GNU General Public License http://www.gnu.org/licenses/gpl-3.0.html
* @author Piotr Moćko
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

class modPWebContactJomSocialHelper
{
	public static function isUserProfileView() 
	{
		$app = JFactory::getApplication();
		if ( $app->input->get('option') == 'com_community' AND $app->input->get('view') == 'profile' AND ($profile_id = $app->input->getInt('userid', 0)) ) {
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
		
		if ( $params->get('jomsocial') AND ($profile_id = $app->input->getInt('userid', 0)) ) {
			
			if ($params->get('jomsocial') == 2) {
				$html = '<input type="hidden" name="jomsocial" value="'.$profile_id.'">';
			}
			elseif ($app->input->get('option') == 'com_community' AND $app->input->get('view') == 'profile') {
				//check if current user has access rights
				if (self::hasAccessRights($profile_id)) {
					$html = '<input type="hidden" name="jomsocial" value="'.$profile_id.'">';
				}
			}
		}
		return $html;
	}
	
	
	public static function getEmail($profile_id = 0) 
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
				
		//get JomSocial user email
		$query->select('u.email')
			  ->from('#__users AS u')
			  ->where('u.id = ' . (int)$profile_id)
			  ;
		$db->setQuery($query);
		
		try {
			$email = $db->loadResult();
		} catch (RuntimeException $e) {
			if (PWEBCONTACT_DEBUG) modPwebcontactHelper::setLog('JomSocial query error: '.$e->getMessage());
			return false;
		}
		if (version_compare(JVERSION, '3.0.0') == -1 AND $error = $db->getErrorMsg()) {
			if (PWEBCONTACT_DEBUG) modPwebcontactHelper::setLog('JomSocial query error: '.$error);
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