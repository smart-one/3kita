<?php
/**
* @version 3.1.0
* @package PWebContact
* @copyright © 2013 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
* @license GNU General Public License http://www.gnu.org/licenses/gpl-3.0.html
* @author Piotr Moćko
*/

defined('_JEXEC') or die( 'Restricted access' );

JFormHelper::loadFieldClass('text');

/**
 * Acymailing lists
 */
class JFormFieldPwebAcymailingLists extends JFormFieldText
{
	public $type = 'PwebAcymailingLists';
	
	
	protected function getInput()
	{
		if (file_exists(JPATH_ROOT.'/components/com_acymailing/acymailing.php'))
		{
			JHtml::_('behavior.modal');
			
			$this->element['class'] .= ($this->element['class'] ? ' ' : '').'input-medium';
			
			$link = 'index.php?option=com_acymailing&amp;tmpl=component&amp;ctrl=chooselist&amp;task='.$this->id.'&amp;values='.$this->value.'&amp;control=';
			
			if (version_compare(JVERSION, '3.0.0') == -1)
			{
				$html  = '<div class="fltlft">';
				$html .= parent::getInput();
				$html .= '</div><div class="button2-left"><div class="blank">';
				$html .= '<a class="modal hasTip" id="link'.$this->id.'" title="::'.JText::_('MOD_PWEBCONTACT_ACYMAILING_SELECT_LISTS').'"';
				$html .= ' href="'.$link.'" rel="{handler:\'iframe\',size:{x:650,y:375}}">';
				$html .= JText::_('MOD_PWEBCONTACT_ACYMAILING_SELECT');
				$html .= '</a>';
				$html .= '</div></div>';
			}
			else 
			{
				$html  = '<div class="input-append">';
				$html .= parent::getInput();
				$html .= '<a class="btn modal hasTip" id="link'.$this->id.'" title="::'.JText::_('MOD_PWEBCONTACT_ACYMAILING_SELECT_LISTS').'"';
				$html .= ' href="'.$link.'" rel="{handler:\'iframe\',size:{x:650,y:375}}">';
				$html .= '<i class="icon-list-view"></i>';
				$html .= '</a>';
				$html .= '</div>';
			}
		}
		else	
		{
			$html = '<span class="badge badge-warning">'.JText::_('MOD_PWEBCONTACT_ACYMAILING_NOT_INSTALLED').'</span>';
			if (version_compare(JVERSION, '3.0.0') == -1)
			{
				$html = '<div class="fltlft">'.$htm.'</div>';
			}
		}
		return $html;
	}
}