<?php
/**
* @package   Warp Theme Framework
* @author    YOOtheme http://www.yootheme.com
* @copyright Copyright (C) YOOtheme GmbH
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/


/*
	Class: WarpMenuAccordion
		Menu base class
*/
class WarpMenuAccordion extends WarpMenu {
	
	/*
		Function: process

		Returns:
			Xml Object
	*/	
	function process($module, $element) {

        $element->first('ul:first')->addClass('menu-accordion');

        $remove = array();

		// remove all but active or seperators (spans)
		foreach ($element->find('li.level1 ul') as $ul) {
			
			if ($ul->parent()->hasClass('active') || $ul->prev()->tag() == 'span') {
				
				if ($ul->prev()->tag() == 'span') {
					$ul->parent()->addClass('toggler');
					$ul->addClass('accordion');
				}

				$ul->wrap('<div></div>');

				continue;
			}

			$remove[] = $ul;
		}

		foreach ($remove as $ul) {
			$ul->parent()->removeChild($ul);
		}
		
		return $element;
	}

}