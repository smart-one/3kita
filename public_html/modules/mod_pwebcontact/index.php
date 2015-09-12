<?php
/**
* @version 3.1.4
* @package PWebContact
* @copyright © 2013 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
* @license GNU General Public License http://www.gnu.org/licenses/gpl-3.0.html
* @author Piotr Moæko
*/

define('_JEXEC', 1);
defined( '_JEXEC' ) or die( 'Restricted access' );

if (isset($_SERVER['HTTP_ACCEPT']) AND strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
	header('Content-type: application/json');
} else {
	header('Content-type: text/plain');
}
?>{"success":true,"message":null,"messages":null,"data":{"status":307,"msg":"Request 303 redirect from POST: modules\/mod_pwebcontact\/ajax.php to GET: modules\/mod_pwebcontact\/"}}