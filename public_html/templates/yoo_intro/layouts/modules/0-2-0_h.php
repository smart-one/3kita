<?php
/**
* @package   yoo_intro
* @author    YOOtheme http://www.yootheme.com
* @copyright Copyright (C) YOOtheme GmbH
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

// example: angled module with border

?>
<div class="module <?php echo $style; ?> <?php echo $color; ?> <?php echo $yootools; ?> <?php echo $first; ?> <?php echo $last; ?>">

	<?php if ($showtitle) : ?>
	<h3 class="header"><span class="header-2"><span class="header-3"><?php echo $title; ?></span></span></h3>
	<?php endif; ?>

	<?php echo $badge; ?>
	
	<div class="box-1">
		<div class="box-2 deepest <?php if ($showtitle) echo 'with-header'; ?>">

			<?php echo $content; ?>
			
		</div>
	</div>
		
</div>