<?php
/**
* @package   yoo_expo
* @author    YOOtheme http://www.yootheme.com
* @copyright Copyright (C) YOOtheme GmbH
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');
$params = &$this->params;

?>

<div class="items">

	<?php foreach ($this->items as $i => $item) : ?>
	<div class="item">

		<?php if ($params->get('show_create_date')) : ?>
		<div class="date">
			<div class="day"><?php echo JHTML::_('date',$item->created, JText::_('d')); ?></div>
			<div class="month"><?php echo JHTML::_('date',$item->created, JText::_('F')); ?></div>
			<div class="year"><?php echo JHTML::_('date',$item->created, JText::_('Y')); ?></div>
		</div>
		<?php endif; ?>

		<h1 class="title">
	
			<?php if ($params->get('link_titles')): ?>
				<a href="<?php echo JRoute::_(ContentHelperRoute::getArticleRoute($item->slug,$item->catslug)); ?>" title="<?php echo $this->escape($item->title); ?>"><?php echo $this->escape($item->title); ?></a>
			<?php else : ?>
				<?php echo $this->escape($item->title); ?>
			<?php endif; ?>
	
		</h1>

		<?php if ($params->get('show_intro')) :?>
		<div class="content"><?php echo JHTML::_('string.truncate', $item->introtext, $params->get('introtext_limit')); ?></div>		
		<?php endif; ?>

	</div>
	<?php endforeach; ?>

</div>

<?php echo $this->pagination->getPagesLinks(); ?>