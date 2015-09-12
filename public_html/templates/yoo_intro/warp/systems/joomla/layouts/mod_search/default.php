<?php
/**
* @package   Warp Theme Framework
* @author    YOOtheme http://www.yootheme.com
* @copyright Copyright (C) YOOtheme GmbH
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

// get warp
$warp = Warp::getInstance();

// get item id
$itemid = intval($params->get('set_itemid', 0));

?>

<div id="searchbox">
	<form action="<?php echo JRoute::_('index.php'); ?>" method="post" role="search">
		<button class="magnifier" type="submit" value="Search"></button>
		<input type="text" value="" name="searchword" placeholder="<?php echo JText::_('TPL_WARP_SEARCH'); ?>" />
		<button class="reset" type="reset" value="Reset"></button>
		<input type="hidden" name="task"   value="search" />
		<input type="hidden" name="option" value="com_search" />
		<input type="hidden" name="Itemid" value="<?php echo $itemid > 0 ? $itemid : JRequest::getInt('Itemid'); ?>" />		
	</form>
</div>

<script type="text/javascript" src="<?php echo $warp->path->url('js:search.js'); ?>"></script>
<script type="text/javascript">
jQuery(function($) {
	$('#searchbox input[name=searchword]').search({'url': '<?php echo JRoute::_("index.php?option=com_search&tmpl=raw&type=json&ordering=&searchphrase=all");?>', 'param': 'searchword', 'msgResultsHeader': '<?php echo JText::_("TPL_WARP_SEARCH_RESULTS"); ?>', 'msgMoreResults': '<?php echo JText::_("TPL_WARP_SEARCH_MORE"); ?>', 'msgNoResults': '<?php echo JText::_("TPL_WARP_SEARCH_NO_RESULTS"); ?>'}).placeholder();
});
</script>