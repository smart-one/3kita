<?php
/**
* @package   Warp Theme Framework
* @author    YOOtheme http://www.yootheme.com
* @copyright Copyright (C) YOOtheme GmbH
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/


/*
	Class: WarpMenuDefault
		Menu base class
*/
class WarpMenuDefault extends WarpMenu {

	
	/*
		Function: process

		Returns:
			Object
	*/	
	function process($module, $element) {
		self::_process($module, $element->first('ul:first'));
		return $element;
	}

    /*
        Function: _process

        Returns:
            Void
    */	
	static function _process($module, $element, $level = 0) {
	
        if ($level == 0) {
            $element->attr('class', 'menu');
        } else {
            $element->addClass('level'.($level + 1));
        }

        $i = 1;

        foreach ($element->children('li') as $li) {

            // is active ?
            if ($active = $li->attr('data-menu-active')) {
                $active = $active == 2 ? ' active current' : ' active';
            }else{
                $active = '';
            }

            // is parent ?
            $ul = $li->children('ul');
            $parent = $ul->length ? ' parent' : null;

            // set class in li
            $li->attr('class', sprintf('level%d item%s'.$parent.$active, $level + 1, $i));

            // set first in li
            if ($i == 1) $li->addClass('first');         

            // set class in a/span
            foreach ($li->children('a,span') as $child) {

                // get title
                $title = $child->first('span:first');
                $title->addClass('bg');

                // set subtitle
                $subtitle = $title ? explode('||', $title->text()) : array();
                
                if (count($subtitle) == 2) {
                    $title->html(sprintf('<span class="title">%s</span><span class="subtitle">%s</span>', trim($subtitle[0]), trim($subtitle[1])));
                }

                // set image
                if ($image = $li->attr('data-menu-image')) {
                    $title->addClass('icon');
                    $title->attr('style', sprintf('background-image: url(\'%s\');', $image));
                }

                // set class
                $child->addClass(sprintf('level%d item%s'.$parent.$active, $level + 1, $i));

                // set first
                if ($i == 1) $child->addClass('first');

                // set separator in li
                if ($child->hasClass('separator')) $li->addClass('separator');
            }

            $i++;

            // process submenu
            if ($ul->length) {
                self::_process($module, $ul->item(0), $level + 1);
            }
        }

        // set last
        if (isset($li)) {
            $li->addClass('last');
            if ($li->hasChildren()) $child->addClass('last');
        }
	}

}