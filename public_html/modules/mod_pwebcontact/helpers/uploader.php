<?php
/**
* @version 3.2.2
* @package PWebContact
* @copyright © 2013 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
* @license GNU General Public License http://www.gnu.org/licenses/gpl-3.0.html
* @author Piotr Moćko
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once (dirname(__FILE__).'/UploadHandler.php');

/**
 * Class that encapsulates the file-upload internals
 */
class modPWebContactUploader extends UploadHandler
{
	public static function uploader()
	{
		$params = modPwebcontactHelper::getParams();
		
		// check if upload is enabled
		if (!$params->get('show_upload', 0)) 
		{
			if (PWEBCONTACT_DEBUG) modPwebcontactHelper::setLog('Uploader disabled');
			return array('status' => 402, 'files' => array());
		}
		
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		
		$path = $params->get('upload_path');
		if (!JFolder::exists($path)) {
			JFolder::create($path, 0777);
		}
		if (!is_writable($path) AND JPath::canChmod($path)) {
			JPath::setPermissions($path, null, '0777');
		}
		if (!is_writable($path)) {
			if (PWEBCONTACT_DEBUG) modPwebcontactHelper::setLog('Upload dir is not writable');
			return array('status' => 403, 'files' => array());
		}

		// load uploader
		$uploader = new modPWebContactUploader(array(
				'upload_dir' => $params->get('upload_path'),
	            'upload_url' => $params->get('upload_url'),
	            'accept_file_types' => '/(\.|\/)('.$params->get('upload_allowed_ext', '.+').')$/i',
	            'max_file_size' => ((float)$params->get('upload_size_limit', 1) * 1024 * 1024),
	            'image_versions' => array(),
	            // Set the following option to 'POST', if your server does not support
	            // DELETE requests. This is a parameter sent to the client:
	            'delete_type' => 'POST'
			), false, array(
				// translate messages
				1 => JText::_('MOD_PWEBCONTACT_UPLOAD_ERR_1'),
		        3 => JText::_('MOD_PWEBCONTACT_UPLOAD_ERR_3'),
		        4 => JText::_('MOD_PWEBCONTACT_UPLOAD_ERR_4'),
		        6 => JText::_('MOD_PWEBCONTACT_UPLOAD_ERR_6'),
		        7 => JText::_('MOD_PWEBCONTACT_UPLOAD_ERR_7'),
		        8 => JText::_('MOD_PWEBCONTACT_UPLOAD_ERR_8'),
		        'post_max_size' => JText::_('MOD_PWEBCONTACT_UPLOAD_ERR_1'),
		        'max_file_size' => JText::_('MOD_PWEBCONTACT_UPLOAD_SIZE_ERR'),
		        'accept_file_types' => JText::_('MOD_PWEBCONTACT_UPLOAD_TYPE_ERR')
			));
		
		$response = $uploader->handleRequest();
		
		if (PWEBCONTACT_DEBUG) modPwebcontactHelper::setLog('Uploader exit');
		
		return $response;
	}


	public static function deleteAttachments()
	{
		$app = JFactory::getApplication();
		$attachments = $app->input->get('attachments', array(), 'array');
		if (count($attachments)) 
		{
			jimport('joomla.filesystem.file');
			$params = modPwebcontactHelper::getParams();
			$path = $params->get('upload_path');
			foreach ($attachments as $file)
				JFile::delete($path . $file);
			
			if (PWEBCONTACT_DEBUG) modPwebcontactHelper::setLog('Deleted '.count($attachments).' files');
		}
		elseif (PWEBCONTACT_DEBUG) modPwebcontactHelper::setLog('No files to delete');
	}


	/* extend base methods */
	
	public function handleRequest()
	{
        $response = array();
        switch ($this->get_server_var('REQUEST_METHOD')) 
        {
            case 'OPTIONS':
            case 'HEAD':
                $response = $this->head();
                break;
            case 'GET':
                $response = $this->get();
                break;
            case 'PATCH':
            case 'PUT':
            case 'POST':
                $response = $this->post();
                break;
            case 'DELETE':
                $response = $this->delete();
                break;
            default:
                $this->header('HTTP/1.1 405 Method Not Allowed');
        }
		return $response;
    }

	public function post($print_response = true) 
	{
        if (isset($_REQUEST['_method']) && $_REQUEST['_method'] === 'DELETE') {
            return $this->delete($print_response);
        }
		
		if (PWEBCONTACT_DEBUG) modPwebcontactHelper::setLog('Uploading file');
		return parent::post($print_response);
	}

	public function delete($print_response = true) 
	{
		if (PWEBCONTACT_DEBUG) modPwebcontactHelper::setLog('Deleting file');
		return parent::delete($print_response);
	}

	protected function body($str)
	{
		// Do not print, will be printed later
    }

	protected function header($str) 
	{
        $header = explode(': ', $str);
		if (array_key_exists(1, $header))
			JResponse::setHeader($header[0], $header[1], true);
		elseif (!headers_sent()) 
			header($str);
    }

	protected function get_download_url($file_name, $version = null, $direct = false)
	{
		// Disable download
		return null;
	}

	protected function set_additional_file_properties($file) 
	{
		parent::set_additional_file_properties($file);
		// Do not return delete URL
		$file->deleteUrl = null;
    }
}
