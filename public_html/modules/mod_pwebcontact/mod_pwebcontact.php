<?php
/**
* @version 3.2.4
* @package PWebContact
* @copyright © 2013 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
* @license GNU General Public License http://www.gnu.org/licenses/gpl-3.0.html
* @author Piotr Moćko
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

$app = JFactory::getApplication();

// Enable debug
$params->def('id', $module->id);
if ($app->input->getInt('debug')) $params->set('debug', 1);

// Show or hide module on Mobile browser
if ($filter_browsers = $params->get('filter_browsers'))
{
	jimport('joomla.environment.browser');
	$browser = JBrowser::getInstance();
	$isMobile = $browser->isMobile();
	// Show only on mobile OR only on desktop
	if (($filter_browsers == 1 AND !$isMobile) OR ($filter_browsers == 2 AND $isMobile)) {
		if ($params->get('debug')) {
			$app->enqueueMessage(JText::_('MOD_PWEBCONTACT_FILTER_BROWSERS_WARNING'), 'warning');
		}
		return;
	}
}

// Get layout name
$layout = $params->get('layout_type', 'slidebox');
// Position and offset
$position = explode(':', $params->get('position', 'left:top'));
$params->set('position', $position[0]);
$params->def('offset_position', array_key_exists(1, $position) ? $position[1] : 'top');

// Set static position for static and accordion layouts
if (in_array($layout, array('static', 'accordion'))) {
	$params->set('position', 'static');
}
// Set left position for slidebox layout which was set to static position
elseif ($layout == 'slidebox' AND $params->get('position') == 'static') {
	$params->set('position', 'left');
	$params->set('offset_position', 'top');
}

// Disable floating tab in component view
if ($app->input->get('tmpl') == 'component' AND $params->get('position') != 'static') return;


require_once (dirname(__FILE__).'/helper.php');

// Community Builder
if ($params->get('comprofiler') == 2) {
	if (!modPWebContactComprofilerHelper::isUserProfileView()) {
		if ($params->get('debug')) {
			$app->enqueueMessage(JText::_('MOD_PWEBCONTACT_INTEGRATION_COMPONENT_VIEW_WARNING'), 'warning');
		}
		return;
	}
}
// JomSocial
elseif ($params->get('jomsocial') == 2) {
	if (!modPWebContactJomSocialHelper::isUserProfileView()) {
		if ($params->get('debug')) {
			$app->enqueueMessage(JText::_('MOD_PWEBCONTACT_INTEGRATION_COMPONENT_VIEW_WARNING'), 'warning');
		}
		return;
	}
}
// SobiPro
elseif ($params->get('sobipro') == 2) {
	if (!modPWebContactSobiProHelper::isEntryView()) {
		if ($params->get('debug')) {
			$app->enqueueMessage(JText::_('MOD_PWEBCONTACT_INTEGRATION_COMPONENT_VIEW_WARNING'), 'warning');
		}
		return;
	}
}
// Zoo
elseif ($params->get('zoo') == 2) {
	if (!modPWebContactZooHelper::isItemView()) {
		if ($params->get('debug')) {
			$app->enqueueMessage(JText::_('MOD_PWEBCONTACT_INTEGRATION_COMPONENT_VIEW_WARNING'), 'warning');
		}
		return;
	}
}


// Display error if jQuery not installed
if (!class_exists('JHtmlJquery')) {
	$app->enqueueMessage(JText::_('MOD_PWEBCONTACT_INSTALL_PWEBLEGACY_ERR'), 'error');
	return;
}


// Auto RTL
if ($params->get('rtl', 2) == 2) {
	if (!JFactory::getLanguage()->isRTL())
		$params->set('rtl', 0);
	else {
		switch ($params->get('position')) {
			case 'left':
				$params->set('position', 'right');
				break;
			case 'right':
				$params->set('position', 'left');
				break;
			case 'top':
			case 'bottom':
				switch ($params->get('offset_position')) {
					case 'left':
						$params->set('offset_position', 'right');
						break;
					case 'right':
						$params->set('offset_position', 'left');
				}
		}
		$params->set('toggler_rotate', 0 - $params->get('toggler_rotate', 1));
	}
}


// Disable vertical toggler if position is not left or right
if (!in_array($params->get('position'), array('left', 'right'))) {
	$params->set('toggler_vertical', 0);
}
// Disable sliding of toggler if it is not vertical and position is left or right
elseif (!$params->get('toggler_vertical', 0)) {
	$params->set('toggler_slide', 0);
}

// Toggler position
if ($layout == 'slidebox') {
	if (!$params->get('show_toggler', 1)) {
		$params->set('toggler_vertical', 0);
		$params->set('toggler_slide', 0);
	}
	if ($params->get('toggler_slide', 0)) {
		$params->def('toggler_position', 'slide');
	} else {
		$params->def('toggler_position', 'fixed');
	}
}
elseif ($layout == 'modal') {
	if ($params->get('show_toggler', 1)) {
		$params->def('toggler_position', $params->get('position') == 'static' ? 'static' : 'fixed');
	}
}
elseif ($layout == 'accordion') {
	if ($params->get('show_toggler', 1)) {
		$params->def('toggler_position', 'fixed');
	}
}

// Disable auto-open for static layout
if ($layout == 'static') {
	$params->set('open_toggler', 0);
}

// Toggler tab name
$toggler_name = explode('|', $params->get('toggler_name', 'MOD_PWEBCONTACT_TOGGLER'));
$params->def('toggler_name_open', str_replace('"', '', JText::_($toggler_name[0])));
$params->def('toggler_name_close', array_key_exists(1, $toggler_name) ? str_replace('"', '', JText::_($toggler_name[1])) : null);


// Set media path
$media_path = JPATH_ROOT.'/media/mod_pwebcontact/';
$params->set('media_path', $media_path);
$media_url = JUri::base(true).'/media/mod_pwebcontact/';
$params->set('media_url', $media_url);


// Captcha
$captcha_plugin = $params->get('captcha', 0);
if ($captcha_plugin == -1) {
	$captcha_plugin = $app->getCfg('captcha', 0);
	$params->set('captcha', $captcha_plugin);
}
if ($captcha_plugin AND ($captcha = JCaptcha::getInstance($captcha_plugin)) == null) {
	$params->set('captcha', 0);
}


// Set params
modPwebcontactHelper::setParams($params);

// Get JavaScript init code
$script = modPwebcontactHelper::getScript();

// Load CSS and JS files and JS translations
modPwebcontactHelper::initHeader();

// Module CSS classes
modPwebcontactHelper::initCssClassess();
$positionClass 	= $params->get('positionClass');
$moduleClass 	= $params->get('moduleClass');

// Load fields
$fields = modPwebcontactHelper::getFields();

require(JModuleHelper::getLayoutPath('mod_pwebcontact', $params->get('layout', 'default')));
