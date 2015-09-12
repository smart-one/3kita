<?php
/**
* @version 3.2.7.4
* @package PWebContact
* @copyright © 2014 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
* @license GNU General Public License http://www.gnu.org/licenses/gpl-3.0.html
* @author Piotr Moćko
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

class modPWebContactUpdateServer
{
	protected $location = 'https://www.perfect-web.co/index.php?option=com_pwebshop&view=updates&format=%s&extension=mod_pwebcontact';
	protected $md5_path = '/modules/mod_pwebcontact/form/';
	protected $type		= array(
							'type' 		=> 'module',
							'element' 	=> 'mod_pwebcontact',
							'folder' 	=> '',
							'client_id' => 0
						);
	
	public function getMd5($path = null)
	{
		jimport('joomla.filesystem.folder');
		
		$files = JFolder::files($path ? $path : JPATH_ROOT . $this->md5_path, '^[a-f0-9]{25,32}$');
		if (count($files)) {
			return $files[0];
		}
		return null;
	}

	public function deleteMd5($path = null)
	{
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		
		$files = JFolder::files($path ? $path : JPATH_ROOT . $this->md5_path, '^[a-f0-9]{25,32}$', false, true);
		if (count($files)) {
			return JFile::delete($files);
		}
		return true;
	}
	
	public function getFeedScript($url_only = false)
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		
		// get extension details
		$query->select('manifest_cache')
			->from('#__extensions');
		foreach ($this->type as $column => $value)
			$query->where($column.' = '.$db->quote($value));
		
		$db->setQuery($query);
		try {
			$extension = $db->loadObject();
		} catch (RuntimeException $e) {
			$extension = null;
		}
		
		$location = sprintf($this->location, 'raw');
		
		// extension version
		if ($extension) {
			$manifest = new JRegistry($extension->manifest_cache);
			if ($version = $manifest->get('version'))
				$location .= '&version='.$version;
		}
			
		// Joomla version
		$location .= '&jversion='.JVERSION;
		
		// download ID
		if ($download_id = $this->getMd5()) {
			$location .= '&download_id='.$download_id;
		}
		
		// host name
		$location .= '&host='.urlencode(JUri::root());
		
		return $url_only ? $location :
			'setTimeout(function(){'.
			'var pw=document.createElement("script");pw.type="text/javascript";pw.async=true;'.
			'pw.src="'.$location.'";'.
			'var s=document.getElementsByTagName("script")[0];s.parentNode.insertBefore(pw,s);'.
			'},3000);'
			;
	}
	
	/**
	 * Add new update server or update it if already exists
	 *
	 * @param	string	$version		Version of module, if empty will be loaded from manifest cache
	 * @param	string	$download_id	Download ID, if NULL will be looked for, if FALSE will be skiped
	 *
	 * @return 	bool
	 */
	public function add($version = null, $download_id = null)
	{
		// get download ID
		if ($download_id === null AND $this->md5_path) 
		{
			if ($tmp = $this->getMd5()) {
				$download_id = $tmp;
			}
			else $this->delete();
		}
		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		
		// get extension details
		$query->select('extension_id AS id, name')
			->from('#__extensions');
		if (empty($version)) 
			$query->select('manifest_cache');
		foreach ($this->type as $column => $value)
			$query->where($column.' = '.$db->quote($value));
		
		$db->setQuery($query);
		try {
			$extension = $db->loadObject();
		} catch (RuntimeException $e) {
			$extension = null;
		}
		
		$location = sprintf($this->location, 'xml');
		
		// extension version
		if (empty($version) AND $extension) {
			$manifest = new JRegistry($extension->manifest_cache);
			$version = $manifest->get('version');
		}
		if ($version)
			$location .= '&version='.$version;
			
		// Joomla version
		$location .= '&jversion='.JVERSION;
		
		// download ID
		if ($download_id)
			$location .= '&download_id='.$download_id;
		
		// host name
		$location .= '&host='.urlencode(JUri::root());
		
		// get update site ID
		$query->clear()
			->select('update_site_id AS id, location')
			->from('#__update_sites')
			->where('location LIKE '.$db->quote($db->escape(sprintf($this->location, 'xml')).'%'));
		$db->setQuery($query);
		
		try {
			$update_site = $db->loadObject();
		} catch (RuntimeException $e) {
			$update_site = false;
		}
		
		
		if ($update_site)
		{
			// update existing site if location has changed
			if ($update_site->location != $location)
			{
				$query->clear()
					->update('#__update_sites')
					->set('location = '.$db->quote($db->escape($location)))
					->set('enabled = 1')
					->where('update_site_id = '.(int)$update_site->id);
				
				$db->setQuery($query);
				try {
					return $db->execute();
				} catch (RuntimeException $e) {
					return false;
				}
			}
		}
		elseif ($extension AND $extension->id)
		{
			// create new update site
			$query->clear()
				->insert('#__update_sites')
				->columns(array($db->quoteName('name'), $db->quoteName('type'), $db->quoteName('location'), $db->quoteName('enabled')))
				->values($db->quote($extension->name) . ', ' . $db->quote('extension') . ', ' . $db->quote($location) . ', 1');
			$db->setQuery($query);
			
			try {
				$db->execute();
				$update_site_id = (int)$db->insertid();
			} catch (RuntimeException $e) {
				$update_site_id = false;
			}
			
			if (!$update_site_id) return false;
			
			
			$query->clear()
				->insert('#__update_sites_extensions')
				->columns(array($db->quoteName('update_site_id'), $db->quoteName('extension_id')))
				->values($update_site_id.','.(int)$extension->id);
			$db->setQuery($query);
			
			try {
				return $db->execute();
			} catch (RuntimeException $e) {
				return false;
			}
		}

		return true;
	}
	
	
	/**
	 * Delete update servers
	 *
	 * @return 	bool
	 */
	public function delete()
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		
		// get update site id
		$query->select('update_site_id')
			->from('#__update_sites')
			->where('location LIKE '.$db->quote($db->escape(sprintf($this->location, 'xml')).'%'));
		$db->setQuery($query);
		
		try {
			$update_site_ids = $db->loadColumn();
		} catch (RuntimeException $e) {
			return false;
		}
		
		// delete update site from Joomla Update Manger
		if ($update_site_ids) 
		{
			$query->clear()
				->delete('#__update_sites_extensions')
				->where('update_site_id IN ('.implode(',', $update_site_ids).')');
			$db->setQuery($query);
			
			try {
				$result = $db->execute();
			} catch (RuntimeException $e) {
				$result = false;
			}
			
			
			$query->clear()
				->delete('#__update_sites')
				->where('update_site_id IN ('.implode(',', $update_site_ids).')');
			$db->setQuery($query);
			
			try {
				$result = $db->execute();
			} catch (RuntimeException $e) {
				$result = false;
			}
			
			return $result;
		}

		return true;
	}
}
