<?php
/**
* @package   Warp Theme Framework
* @author    YOOtheme http://www.yootheme.com
* @copyright Copyright (C) YOOtheme GmbH
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');

// Create shortcuts to some parameters.
$params		= $this->item->params;
$images		= json_decode($this->item->images);
$urls		= json_decode($this->item->urls);
$canEdit	= $this->item->params->get('access-edit');
$user		= JFactory::getUser();

?>

<div id="system" class="<?php $this->pageclass_sfx; ?>">

	<?php if ($this->params->get('show_page_heading', 1)) : ?>
	<h1 class="title"><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
	<?php endif; ?>

	<div class="item">

		<?php if (!$this->print) : ?>
			<?php if ($params->get('show_email_icon')) : ?>
			<div class="icon email"><?php echo JHtml::_('icon.email',  $this->item, $params); ?></div>
			<?php endif; ?>
		
			<?php if ($params->get('show_print_icon')) : ?>
			<div class="icon print"><?php echo JHtml::_('icon.print_popup',  $this->item, $params); ?></div>
			<?php endif; ?>
		<?php else : ?>
			<div class="icon printscreen"><?php echo JHtml::_('icon.print_screen',  $this->item, $params); ?></div>
		<?php endif; ?>

		<h1 class="title"><?php echo $this->escape($this->item->title); ?></h1>
		
		<?php if ($params->get('show_create_date') || ($params->get('show_author') && !empty($this->item->author)) || $params->get('show_category')) : ?>
		<p class="meta">
	
			<?php
				
				if ($params->get('show_author') && !empty($this->item->author )) {
					
					$author =  $this->item->author;
					$author = ($this->item->created_by_alias ? $this->item->created_by_alias : $author);
					
					if (!empty($this->item->contactid ) &&  $params->get('link_author') == true) {
						echo JText::sprintf('COM_CONTENT_WRITTEN_BY' , JHtml::_('link', JRoute::_('index.php?option=com_contact&view=contact&id='.$this->item->contactid),$author));
					} else {
						echo JText::sprintf('COM_CONTENT_WRITTEN_BY', $author);
					}

				}

				if ($params->get('show_create_date')) {
					echo ' '.JText::_('TPL_WARP_ON').' '.JHtml::_('date', $this->item->created, JText::_('DATE_FORMAT_LC3'));
				}

				echo '. ';
			
				if ($params->get('show_category')) {
					echo JText::_('TPL_WARP_POSTED_IN').' ';
					$title = $this->escape($this->item->category_title);
					$url = '<a href="'.JRoute::_(ContentHelperRoute::getCategoryRoute($this->item->catslug)).'">'.$title.'</a>';
					if ($params->get('link_category') AND $this->item->catslug) {
						echo $url;
					} else {
						echo $title;
					}
				}
			
			?>	
		
		</p>
		<?php endif; ?>

		<?php
		
			if (!$params->get('show_intro')) {
				echo $this->item->event->afterDisplayTitle;
			}
		
			echo $this->item->event->beforeDisplayContent;

			if (isset ($this->item->toc)) {
				echo $this->item->toc;
			}
			
		?>

		<div class="content">
		<?php
		
			if ($params->get('access-view')) {
				
				if (isset($urls) AND ((!empty($urls->urls_position) AND ($urls->urls_position=='0')) OR ($params->get('urls_position')=='0' AND empty($urls->urls_position) ))
					OR (empty($urls->urls_position) AND (!$params->get('urls_position')))) {
						echo $this->loadTemplate('links');
				}

				if (isset($images->image_fulltext) and !empty($images->image_fulltext)) {
					$imgfloat = (empty($images->float_fulltext)) ? $params->get('float_fulltext') : $images->float_fulltext;
					$class = (htmlspecialchars($imgfloat) != 'none') ? ' class="size-auto align-'.htmlspecialchars($imgfloat).'"' : ' class="size-auto"';
					$title = ($images->image_fulltext_caption) ? ' title="'.htmlspecialchars($images->image_fulltext_caption).'"' : '';
					echo '<img'.$class.$title.' src="'.htmlspecialchars($images->image_fulltext).'" alt="'.htmlspecialchars($images->image_fulltext_alt).'" />';
				}
				
				echo $this->item->text;
				
				if (isset($urls) AND ((!empty($urls->urls_position)  AND ($urls->urls_position=='1')) OR ( $params->get('urls_position')=='1') )) {
					echo $this->loadTemplate('links');
				}
			
			// optional teaser intro text for guests
			} elseif ($params->get('show_noauth') == true AND $user->get('guest')) {
				
				echo $this->item->introtext;
				
				// optional link to let them register to see the whole article.
				if ($params->get('show_readmore') && $this->item->fulltext != null) {
					$link1 = JRoute::_('index.php?option=com_users&view=login');
					$link = new JURI($link1);
					echo '<p class="links">';
					echo '<a href="'.$link.'">';
					$attribs = json_decode($this->item->attribs);
		
					if ($attribs->alternative_readmore == null) {
						echo JText::_('COM_CONTENT_REGISTER_TO_READ_MORE');
					} elseif ($readmore = $this->item->alternative_readmore) {
						echo $readmore;
						if ($params->get('show_readmore_title', 0) != 0) {
							echo JHtml::_('string.truncate', ($this->item->title), $params->get('readmore_limit'));
						}
					} elseif ($params->get('show_readmore_title', 0) == 0) {
						echo JText::sprintf('COM_CONTENT_READ_MORE_TITLE');	
					} else {
						echo JText::_('COM_CONTENT_READ_MORE');
						echo JHtml::_('string.truncate', ($this->item->title), $params->get('readmore_limit'));
					}
					
					echo '</a></p>';
				}
			}
			
		?>
		</div>

		<?php if ($canEdit) : ?>
		<p class="edit"><?php echo JHtml::_('icon.edit', $this->item, $params); ?> <?php echo JText::_('TPL_WARP_EDIT_ARTICLE'); ?></p>
		<?php endif; ?>

		<?php echo $this->item->event->afterDisplayContent; ?>
	
	</div>

</div>