<?php
/**
* @package   Warp Theme Framework
* @author    YOOtheme http://www.yootheme.com
* @copyright Copyright (C) YOOtheme GmbH
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

/*
	Class: WarpHelperSystem
		Joomla! system helper class, provides Joomla! 1.7 CMS integration (http://www.joomla.org)
*/
class WarpHelperSystem extends WarpHelper {

	/* application */
	var $application;

	/* document */
	var $document;

	/* language */
	var $language;

	/* system path */
	var $path;

	/* system url */
	var $url;

	/* cache path */
	var $cache_path;

	/* cache time */
	var $cache_time;

	/*
		Function: Constructor
			Class Constructor.
	*/
	function __construct() {
		parent::__construct();		

		// init helpers
        $path  = $this->getHelper('path');
        $event = $this->getHelper('event');

		// init vars
		$this->application = JFactory::getApplication();
        $this->document    = JFactory::getDocument();
		$this->language    = JFactory::getLanguage();
        $this->path        = JPATH_ROOT;
        $this->url         = JURI::root(true);
        $this->cache_path  = $this->path.'/cache/template';
        $this->cache_time  = max(JFactory::getConfig()->getValue('config.cachetime') * 60, 86400);

		// set cache directory
		if (!file_exists($this->cache_path)) {
			JFolder::create($this->cache_path);
		}

		// set paths
        $path->register($this->path.'/administrator', 'admin');
        $path->register($this->path, 'site');
        $path->register($this->path.'/cache/template', 'cache');
		$path->register($path->path('warp:systems/joomla/menus'), 'menu');

		// set translations
		$this->language->load('tpl_warp', $path->path('warp:systems/joomla'), null, true);
		
		// set event
		$event->bind('render.template', array($this, 'init'));

		// is admin ?
		if ($this->application->isAdmin()) {
			
			// get helper
			$dom  = $this->getHelper('dom');
			$http = $this->getHelper('http');
			
			// get xml's
			$tmpl_xml = $dom->create($path->path('template:templateDetails.xml'), 'xml');
			$warp_xml = $dom->create($path->path('warp:warp.xml'), 'xml');

			// update check
			if ($url = $warp_xml->first('updateUrl')) {

				// get template info
				$template = $tmpl_xml->first('name');
				$version  = $tmpl_xml->first('version');
				$url      = sprintf('%s?application=%s&version=%s&format=raw', $url->text(), $template->text().'_j25', $version->text());

				// check cache
				$file  = $this->cache_path.sprintf('/%s.php', $template->text());
				$cache = file_exists($file) ? @unserialize(file_get_contents($file)) : null;

				if (!is_array($cache) || !isset($cache['check']) || !isset($cache['data'])) {
					$cache = array('check' => null, 'data' => null);
				}

				// only check once a day 
				if ($cache['check'] != date('Y-m-d').' '.$version->text()) {
					if ($request = $http->get($url)) {
						$cache = array('check' => date('Y-m-d').' '.$version->text(), 'data' => $request['body']);
						@file_put_contents($file, serialize($cache));
					}
				}

				// decode response and set message
				if (($response = json_decode($cache['data'])) && isset($response->status, $response->message) && $response->status == 'update-available') {
					$this->application->enqueueMessage($response->message, 'notice');
				}
			}
		}

	}

	/*
		Function: init
			Initialize system configuration

		Returns:
			Void
	*/
	function init() {

		if ($this->application->isSite()) {

			// init helpers
			$config = $this->getHelper('config');

			// set config
			$config->set('language', $this->document->language); 
			$config->set('direction', $this->document->direction); 
			$config->set('actual_date', JHTML::_('date', 'now', JText::_('DATE_FORMAT_LC')));

            // get template params
            $config->loadArray($this->document->params->toArray());
            
            // get page class suffix params
            $params = $this->application->getParams();
            $config->parseString($params->get('pageclass_sfx'));
            
			// dynamic presets ?
            if ($config->get('allow_dynamic_preset')) {
                if ($var = JRequest::getVar($this->warp->preset_var, null, 'default', 'alnum')) {
                    $this->application->setUserState('_current_preset', $var);
                }
                $config->set('_current_preset', $this->application->getUserState('_current_preset'));
            }

            $config->applyPreset();
        }
		
	}

	/*
		Function: isBlog

		Returns:
			Boolean
	*/
	function isBlog() {
		if (JRequest::getCmd('option') == 'com_content') {
			if (in_array(JRequest::getCmd('view'), array('frontpage', 'article', 'archive', 'featured')) || (JRequest::getCmd('view') == 'category' && JRequest::getCmd('layout') == 'blog')) {
				return true;
			}
		}
		if ($this->warp->config->get('custom') == 'nobox') {
			return true;
		}
		return false;
	}

}