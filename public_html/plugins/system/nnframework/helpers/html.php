<?php
/**
 * nnHtml
 * extra JHTML functions
 *
 * @package         NoNumber Framework
 * @version         15.4.5
 *
 * @author          Peter van Westen <peter@nonumber.nl>
 * @link            http://www.nonumber.nl
 * @copyright       Copyright © 2015 NoNumber All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

$scripts = array();
$scripts[] = "if ( typeof( window['nn_texts'] ) == \"undefined\" ) { nn_texts = []; }";
$scripts[] = "nn_texts['selectall'] = '" . addslashes(JText::_('NN_SELECT_ALL')) . "';";
$scripts[] = "nn_texts['unselectall'] = '" . addslashes(JText::_('NN_UNSELECT_ALL')) . "';";
$scripts[] = "nn_texts['total'] = '" . addslashes(JText::_('NN_TOTAL')) . "';";
$scripts[] = "nn_texts['selected'] = '" . addslashes(JText::_('NN_SELECTED')) . "';";
$scripts[] = "nn_texts['unselected'] = '" . addslashes(JText::_('NN_UNSELECTED')) . "';";
$scripts[] = "nn_texts['maximize'] = '" . addslashes(JText::_('NN_MAXIMIZE')) . "';";
$scripts[] = "nn_texts['minimize'] = '" . addslashes(JText::_('NN_MINIMIZE')) . "';";
JFactory::getDocument()->addScriptDeclaration(implode('', $scripts));

/**
 * nnHtml
 */
class nnHtml
{
	static function selectlist(&$options, $name, $value, $id, $size = 0, $multiple = 0, $attribs = '')
	{
		require_once JPATH_PLUGINS . '/system/nnframework/helpers/parameters.php';
		$parameters = nnParameters::getInstance();
		$params = $parameters->getPluginParams('nnframework');

		if ($options == -1 || count($options) > $params->max_list_count)
		{
			if (is_array($value))
			{
				$value = implode(',', $value);
			}
			if (!$value)
			{
				$input = '<textarea name="' . $name . '" id="' . $id . '" cols="40" rows="5" />' . $value . '</textarea>';
			}
			else
			{
				$input = '<input type="text" name="' . $name . '" id="' . $id . '" value="' . $value . '" size="60" />';
			}

			return '<fieldset class="radio"><label class="nn_label nn_label_error"><label for="' . $id . '">' . JText::_('NN_ITEM_IDS') . ':</label>' . $input . '</label></fieldset>';
		}

		if (empty($options))
		{
			return '<fieldset class="radio"><label class="nn_label nn_label_error">' . JText::_('NN_NO_ITEMS_FOUND') . '</label></fieldset>';
		}

		if (!$size)
		{
			$size = ((count($options) > 10) ? 10 : count($options));
		}
		$attribs .= ' size="' . $size . '"';
		if ($multiple)
		{
			if (!is_array($value))
			{
				$value = explode(',', $value);
			}
			$attribs .= ' multiple="multiple"';
			if (substr($name, -2) != '[]')
			{
				$name .= '[]';
			}
		}

		foreach ($options as $i => $option)
		{
			$option = (object) $option;
			if (isset($option->text))
			{
				$option->text = str_replace(array('&nbsp;', '&#160;'), '___', $option->text);
				$options[$i] = $option;
			}
		}

		$class = 'inputbox';
		if ($multiple)
		{
			$class .= ' nn_multiselect';
		}

		$html = JHtml::_('select.genericlist', $options, $name, 'class="' . trim($class) . '" ' . trim($attribs), 'value', 'text', $value, $id);
		$html = str_replace('___', '&nbsp;', $html);

		$links = array();
		if ($multiple)
		{
			JHtml::stylesheet('nnframework/multiselect.min.css', false, true);
			JHtml::script('nnframework/multiselect.min.js', false, true);
		}
		else if ($size && count($options) > $size)
		{
			$links[] = '<a href="javascript://" onclick="nnScripts.toggleSelectListSize(\'' . $id . '\');" id="toggle_' . $id . '">'
				. '<span class="show">' . JText::_('NN_MAXIMIZE') . '</span>'
				. '<span class="hide" style="display:none;">' . JText::_('NN_MINIMIZE') . '</span>'
				. '</a>';
		}
		if (!empty($links))
		{
			JHtml::_('behavior.mootools');
			JHtml::script('nnframework/script.min.js', false, true);
			$html = implode(' - ', $links) . '<br />' . $html;
		}

		$html = '<fieldset class="radio" id="' . $id . '_fieldset">' . $html . '</fieldset>';

		return preg_replace('#>\[\[\:(.*?)\:\]\]#si', ' style="\1">', $html);
	}
}
