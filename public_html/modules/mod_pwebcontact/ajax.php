<?php
/**
* @version 3.2.0
* @package PWebContact
* @copyright © 2013 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
* @license GNU General Public License http://www.gnu.org/licenses/gpl-3.0.html
* @author Piotr Moćko
*/

define('_JEXEC', 1);

// Only for JED validation
defined('_JEXEC') or die('Restricted access');

define('DS', DIRECTORY_SEPARATOR); //j2.5
$base_path = str_replace( DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'mod_pwebcontact', '', dirname(__FILE__) );

if (file_exists($base_path . '/defines.php'))
{
	include_once $base_path . '/defines.php';
}
if (!defined('_JDEFINES'))
{
	define('JPATH_BASE', $base_path);
	require_once JPATH_BASE . '/includes/defines.php';
}

require_once JPATH_BASE . '/includes/framework.php';

// Disable this file for Joomla 3.2+
version_compare(JVERSION, '3.2.0') == -1 or die('Deprecated');

// Instantiate the application.
$app = JFactory::getApplication('site');

// Initialise the application.
$app->initialise();

require_once (JPATH_ROOT.'/modules/mod_pwebcontact/helper.php');

$method = $app->input->get('method');
if (method_exists('modPwebcontactHelper', $method . 'Ajax'))
{
	$response = new stdClass;
	$response->success 	= true;
	$response->message 	= null;
	$response->messages = null;
	$response->data 	= call_user_func('modPwebcontactHelper' . '::' . $method . 'Ajax');
	
	JResponse::setBody( json_encode($response) );
	
	// Change response type for Internet Explorer < 10
	if (!isset($_SERVER['HTTP_ACCEPT']) OR strpos($_SERVER['HTTP_ACCEPT'], 'application/json') === false) 
	{
		JResponse::setHeader('Content-Type', 'text/plain', true);
	}
}

// Return the response.
echo JResponse::toString();

	
/* Response code:
 * 100 - email send successfully
 * 101 - valid captcha
 * 102 - demo mode, email sent successfully to user
 * 103 - token
 * 104 - upload
 * 
 * 200 - invalid fields
 * 201 - invalid captcha
 * 
 * 300 - error executing mailer
 * 301 - error checking captcha
 * 302 - invalid token
 * 303 - missing email in Global Configuration
 * 304 - error sending auto-reply
 * 305 - error sending email to user
 * 306 - error sending email to admin
 * 307 - request redirect
 * 308 - session has expired or cookies are blocked
 * 
 * 400 - error executing upload
 * 401 - error executing attachments delete
 * 402 - uploader is disabled
 * 403 - upload dir is not writable
 * 
 * 500 - Community Builder
 * 510 - JomSocial
 * 520 - SobiPro
 */