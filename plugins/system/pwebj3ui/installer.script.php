<?php
/**
* @version 1.7
* @package PWebJ3UI
* @copyright © 2014 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
* @license GNU General Public Licence http://www.gnu.org/licenses/gpl-3.0.html
* @author Piotr Moćko
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class plgsystempwebj3uiInstallerScript
{
	/**
	 * Constructor
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 */
	public function __construct(JAdapterInstance $adapter) 
	{
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
	}

	/**
	 * Called before any type of action
	 *
	 * @param   string  $route  Which action is happening (install|uninstall|discover_install|update)
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function preflight($route, JAdapterInstance $adapter) {}

	/**
	 * Called after any type of action
	 *
	 * @param   string  $route  Which action is happening (install|uninstall|discover_install|update)
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function postflight($route, JAdapterInstance $adapter) {}

	/**
	 * Called on installation
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function install(JAdapterInstance $adapter) 
	{
		if (version_compare(JVERSION, '2.5.5') >= 0) 
		{
			$db = JFactory::getDBO();
			
			$query = $db->getQuery(true);
			$query->update('#__extensions');
			$query->set('enabled = 1');
			$query->where('type = "plugin"');
			$query->where('folder = "system"');
			$query->where('element = "pwebj3ui"');
			$db->setQuery($query);
			
			try {
				$db->execute();
			} catch (Exception $e) {
			
			}
		}
		
		if (version_compare(JVERSION, '3.0.0') == -1) 
		{
			$this->copyMedia(true);
		}
		else
		{
			if (version_compare(JVERSION, '3.1.4') == -1) 
			{
				// Update Bootstrap to version 2.3.2
				$this->copyBootstrap();
			}
			
			// remove unused files from plugin
			$plugin_path = JPATH_ROOT.'/plugins/system/pwebj3ui/';
			
			JFolder::delete($plugin_path.'libraries/cms');
			JFolder::delete($plugin_path.'media');
			
			JFolder::create($plugin_path.'media');
			JFile::copy($plugin_path.'index.html', $plugin_path.'media/index.html');
		}
	}

	/**
	 * Called on update
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function update(JAdapterInstance $adapter) 
	{
		if (version_compare(JVERSION, '3.0.0') == -1) 
		{
			$this->copyMedia();
		}
		elseif (version_compare(JVERSION, '3.1.4') == -1) 
		{
			// Update Bootstrap to version 2.3.2
			$this->copyBootstrap();
		}
	}

	/**
	 * Called on uninstallation
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 */
	public function uninstall(JAdapterInstance $adapter) 
	{
		if (version_compare(JVERSION, '3.0.0') == -1) 
		{
			// remove JUI
			JFolder::delete(JPATH_ROOT.'/media/jui');
			
			// restore previous JUI if there is a copy
			if (JFolder::exists(JPATH_ROOT.'/media/jui-bak'))
			{
				JFolder::move(JPATH_ROOT.'/media/jui-bak', JPATH_ROOT.'/media/jui');
			}
		}
		elseif (version_compare(JVERSION, '3.1.4') == -1) 
		{
			// Restore Bootstrap version 2.1.0
			$this->restoreBootstrap();
		}
	}


	protected function copyMedia($backup = false)
	{
		$plugin_path = JPATH_ROOT.'/plugins/system/pwebj3ui/media/';
		
		if (JFolder::exists(JPATH_ROOT.'/media/jui'))
		{
			if ($backup)
			{
				JFolder::move(JPATH_ROOT.'/media/jui', JPATH_ROOT.'/media/jui-bak');
				JFolder::move($plugin_path.'jui', JPATH_ROOT.'/media/jui');
			}
			else 
			{
				JFolder::copy($plugin_path.'jui', JPATH_ROOT.'/media/jui', '', true);
				JFolder::delete($plugin_path.'jui');
			}
		}
		else 
		{
			JFolder::move($plugin_path.'jui', JPATH_ROOT.'/media/jui');
		}
		
		JFolder::copy($plugin_path.'system', JPATH_ROOT.'/media/system', '', true);
		JFolder::delete($plugin_path.'system');
	}


	protected function copyBootstrap()
	{
		$plugin_path = JPATH_ROOT.'/plugins/system/pwebj3ui/media/jui/js/';
		$media_path = JPATH_ROOT.'/media/jui/js/';
		
		if (!JFile::exists($media_path.'bootstrap.min.js.bak'))
		{
			JFile::copy($media_path.'bootstrap.js', $media_path.'bootstrap.js.bak');
			JFile::copy($media_path.'bootstrap.min.js', $media_path.'bootstrap.min.js.bak');
		}
		
		JFile::copy($plugin_path.'bootstrap.js', $media_path.'bootstrap.js');
		JFile::copy($plugin_path.'bootstrap.min.js', $media_path.'bootstrap.min.js');
	}


	protected function restoreBootstrap()
	{
		$media_path = JPATH_ROOT.'/media/jui/js/';
		
		if (JFile::exists($media_path.'bootstrap.min.js.bak'))
		{
			JFile::move($media_path.'bootstrap.js.bak', $media_path.'bootstrap.js');
			JFile::move($media_path.'bootstrap.min.js.bak', $media_path.'bootstrap.min.js');
		}
	}
}