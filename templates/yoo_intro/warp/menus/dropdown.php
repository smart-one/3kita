<?php
/**
* @package   Warp Theme Framework
* @author    YOOtheme http://www.yootheme.com
* @copyright Copyright (C) YOOtheme GmbH
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

/*
	Class: WarpMenuDropdown
		Menu base class
*/
class WarpMenuDropdown extends WarpMenu {

    const WRAPPER_HTML = '<div class="dropdown columns%s"%s><div class="dropdown-t1"><div class="dropdown-t2"><div class="dropdown-t3"></div></div></div><div class="dropdown-1"><div class="dropdown-2"><div class="dropdown-3"></div></div></div><div class="dropdown-b1"><div class="dropdown-b2"><div class="dropdown-b3"></div></div></div></div>';

    const GROUP_HTML = '<div class="group-box1"><div class="group-box2"><div class="group-box3"><div class="group-box4"><div class="group-box5"><div class="hover-box1"><div class="hover-box2"><div class="hover-box3"><div class="hover-box4"></div></div></div></div></div></div></div></div></div>';

	/*
		Function: process

		Returns:
			Xml Object
	*/	
	function process($module, $element) {		

        $element->first('ul:first')->addClass('menu-dropdown');

        foreach ($element->find('ul.level2') as $ul) {

            // get parent li
            $li = $ul->addClass('col1')->parent();

            // get columns
            $columns  = max((int) $li->attr('data-menu-columns'), 1);
            $children = $ul->children('li');
            $colrows  = ceil($children->length / $columns);
            $column   = 0;
            $i        = 0;

            // build dropdown columns
            foreach ($children as $child) {

                $col   = intval($i / $colrows);
                $anchr = $child->children('a,span')->item(0);

                if ($column != $col) {
                    $column = $col;
                }

                if ($li->children('ul')->length == $column) {
                    $j = 1;
                    $li->append('<ul class="level2 col'.($column + 1).'"></ul>');
                }

                if ($column > 0) {

                    // set item/first
                    $first = $j == 1 ? ' first' : null;
                    $child->removeClass('item'.($i + 1))->addClass('item'.$j.$first);
                    $anchr->removeClass('item'.($i + 1))->addClass('item'.$j.$first);

                    // set last on previous
                    if ($j == 1 && isset($prev)) {
                        $prev->addClass('last');
                        $prev->first('a.level2,span.level2')->addClass('last');
                    }

                    // add to column
                    $li->children('ul')->item($column)->append($child);

                    $j++;
                }

                // prepend group box
                $child->prepend(self::GROUP_HTML);

                $child->first('.hover-box4')->append($anchr);
                
                // move children to group box
                foreach ($child->children() as $c) {
                    if ($c->tag() == 'div') continue;
                    
                    $child->first('.group-box5')->append($c->tag() == 'ul' ? $c->wrap('<div class="sub"></div>')->parent() : $c);
                }

                $prev = $child;

                $i++;
            }

            // get width
            $width = (int) $li->attr('data-menu-columnwidth');
            $style = $width > 0 ? sprintf(' style="width:%spx;"', $columns * $width) : null;

            // prepend dropdown wrapper
            $li->prepend(sprintf(self::WRAPPER_HTML, $columns, $style));

            $wrapper = $li->first('.dropdown .dropdown-3');
            $cols    = $li->children('ul');
            $count   = $cols->length;

            foreach ($cols as $i => $col) {

                // set first/last
                if ($i == 0) $col->addClass('first');
                if ($i == $count - 1) $col->addClass('last');

                $wrapper->append($col);
            }
        }

		return $element;
	}

}