<?php
/**
 * NoNumber Framework Helper File: Assignments: RedShop
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

/**
 * Assignments: RedShop
 */
class nnFrameworkAssignmentsRedShop
{
	function init(&$parent)
	{
		$parent->params->item_id = JFactory::getApplication()->input->getInt('pid', 0);
		$parent->params->category_id = JFactory::getApplication()->input->getInt('cid', 0);
		$parent->params->id = ($parent->params->item_id) ? $parent->params->item_id : $parent->params->category_id;
	}

	function passPageTypes(&$parent, &$params, $selection = array(), $assignment = 'all')
	{
		return $parent->passPageTypes('com_redshop', $selection, $assignment, 1);
	}

	function passCategories(&$parent, &$params, $selection = array(), $assignment = 'all', $article = 0)
	{
		if ($parent->params->option != 'com_redshop')
		{
			return $parent->pass(0, $assignment);
		}

		$pass = (
			($params->inc_categories
				&& ($parent->params->view == 'category')
			)
			|| ($params->inc_items && $parent->params->view == 'product')
		);

		if (!$pass)
		{
			return $parent->pass(0, $assignment);
		}

		$cats = array();
		if ($parent->params->category_id)
		{
			$cats = $parent->params->category_id;
		}
		else if ($parent->params->item_id)
		{
			$parent->q->clear()
				->select('x.category_id')
				->from('#__redshop_product_category_xref AS x')
				->where('x.product_id = ' . (int) $parent->params->item_id);
			$parent->db->setQuery($parent->q);
			$cats = $parent->db->loadColumn();
		}

		$cats = $parent->makeArray($cats);

		$pass = $parent->passSimple($cats, $selection, 'include');

		if ($pass && $params->inc_children == 2)
		{
			return $parent->pass(0, $assignment);
		}
		else if (!$pass && $params->inc_children)
		{
			foreach ($cats as $cat)
			{
				$cats = array_merge($cats, self::getCatParentIds($parent, $cat));
			}
		}

		return $parent->passSimple($cats, $selection, $assignment);
	}

	function passProducts(&$parent, &$params, $selection = array(), $assignment = 'all')
	{
		if (!$parent->params->id || $parent->params->option != 'com_redshop' || $parent->params->view != 'product')
		{
			return $parent->pass(0, $assignment);
		}

		return $parent->passSimple($parent->params->id, $selection, $assignment);
	}

	function getCatParentIds(&$parent, $id = 0)
	{
		return $parent->getParentIds($id, 'redshop_category_xref', 'category_parent_id', 'category_child_id');
	}
}
