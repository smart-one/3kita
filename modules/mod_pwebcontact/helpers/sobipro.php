<?php
/**
* @version 3.2.4.1
* @package PWebContact
* @subpackage SobiPro
* @copyright © 2014 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
* @license GNU General Public License http://www.gnu.org/licenses/gpl-3.0.html
* @author Piotr Moćko
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

class modPWebContactSobiProHelper
{
	public static function isEntryView() 
	{
		$app = JFactory::getApplication();
		if ( $app->input->get('option') == 'com_sobipro' && ($entry_id = $app->input->getInt('sid', 0)) ) {
			
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);
		
			//get SobiPro object type
			$query->select('oType')
				  ->from('#__sobipro_object')
				  ->where('id = ' . (int)$entry_id)
				  ;
			$db->setQuery($query);
			
			try {
				$type = $db->loadResult();
			} catch (RuntimeException $e) {
				return false;
			}
			
			if ($type == 'entry') return true;
		}
		
		return false;
	}
	
	
	public static function getHiddenField() 
	{
		$app 	= JFactory::getApplication();
		$params = modPwebcontactHelper::getParams();
		$html 	= '';
		
		if ( $params->get('sobipro') && ($entry_id = $app->input->getInt('sid', 0)) ) {
			
			if ($params->get('sobipro_entry')) {
				$html = '<input type="hidden" name="sobipro" value="'.$entry_id.'">';
			}
			elseif ($app->input->get('option') == 'com_sobipro') {
			
				$db = JFactory::getDBO();
				$query = $db->getQuery(true);
			
				//get SobiPro object type
				$query->select('oType')
					  ->from('#__sobipro_object')
					  ->where('id = ' . (int)$entry_id)
					  ;
				$db->setQuery($query);
				
				try {
					$type = $db->loadResult();
				} catch (RuntimeException $e) {
					return null;
				}
				
				if ($type == 'entry') 
					$html = '<input type="hidden" name="sobipro" value="'.$entry_id.'">';
			}
		}
		return $html;
	}
	
	
	public static function getEmail($entry_id = 0) 
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		
		//get SobiPro entry email
		$query->select('d.baseData')
			  ->from('#__sobipro_field AS f')
			  ->join('LEFT', '#__sobipro_field_data AS d ON d.fid = f.fid')
			  ->where('d.sid = ' . (int)$entry_id)
			  ->where('f.enabled = 1')
			  ->where('(f.fieldType = '.$db->quote('email').' OR f.filter = '.$db->quote('email').')')
			  ;
		$db->setQuery($query);
		
		try {
			$email = $db->loadResult();
		} catch (RuntimeException $e) {
			if (PWEBCONTACT_DEBUG) modPwebcontactHelper::setLog('SobiPro query error: '.$e->getMessage());
			return false;
		}
		if (version_compare(JVERSION, '3.0.0') == -1 AND $error = $db->getErrorMsg()) {
			if (PWEBCONTACT_DEBUG) modPwebcontactHelper::setLog('SobiPro query error: '.$error);
			return false;
		}
		if (!empty($email)) {
			if (JMailHelper::isEmailAddress($email)) return $email;
			else {
				$result = self::unserialize($email);
				if ($result !== false && is_array($result)) {
					foreach ($result as $a)
						if (JMailHelper::isEmailAddress($a)) return $a;
				}
			}
		}
		
		//get SobiPro entry owner email
		$query = $db->getQuery(true);
		$query->select('u.email')
			  ->from('#__sobipro_object AS o')
			  ->join('LEFT', '#__users AS u ON u.id = o.owner')
			  ->where('o.id = ' . (int)$entry_id)
			  ;
		$db->setQuery($query);
		
		try {
			$email = $db->loadResult();
		} catch (RuntimeException $e) {
			if (PWEBCONTACT_DEBUG) modPwebcontactHelper::setLog('SobiPro query error: '.$e->getMessage());
			return false;
		}
		if (version_compare(JVERSION, '3.0.0') == -1 AND $error = $db->getErrorMsg()) {
			if (PWEBCONTACT_DEBUG) modPwebcontactHelper::setLog('SobiPro query error: '.$error);
			return false;
		}
		if (!empty($email)) {
			return $email;
		}
		
		return false;
	}
	
	
	private static function unserialize($var)
	{
		$r = null;
		if( is_string( $var ) && strlen( $var ) > 2 ) {
			if( ( $var2 = base64_decode( $var, true ) ) ) {
				if( function_exists( 'gzinflate' ) ) {
					if( ( $r = @gzinflate( $var2 ) ) ) {
						if( !$r = unserialize( $r ) ) {
							return false;
						}
					}
					else {
						if( !( $r = @unserialize( $var2 ) ) ) {
							return false;
						}
					}
				}
				else {
					if( !( $r = @unserialize( $var2 ) ) ) {
						return false;
					}
				}
			}
			else {
				if( !( $r = @unserialize( $var ) ) ) {
					return false;
				}
			}
		}
		return $r;
	}
}