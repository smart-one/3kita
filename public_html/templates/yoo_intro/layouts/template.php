<?php
/**
* @package   yoo_intro
* @author    YOOtheme http://www.yootheme.com
* @copyright Copyright (C) YOOtheme GmbH
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

// get template configuration
include(dirname(__FILE__).'/template.config.php');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->warp->config->get('language'); ?>" lang="<?php echo $this->warp->config->get('language'); ?>" dir="<?php echo $this->warp->config->get('direction'); ?>" >
<head>

<?php echo $this->warp->template->render('head'); ?>
<link rel="apple-touch-icon" href="<?php echo $this->warp->path->url('template:apple_touch_icon.png'); ?>" />
<head><link rel="shortcut icon" href="favicon.ico"></head>
</head>

<body id="page" class="yoopage <?php echo $this->warp->config->get('columns'); ?> <?php echo $this->warp->config->get('itemcolor'); ?> <?php echo $this->warp->config->get('frontpage'); ?>">

	<?php if($this->warp->modules->count('absolute')) : ?>
	<div id="absolute">
		<?php echo $this->warp->modules->render('absolute'); ?>
	</div>
	<?php endif; ?>

	<div id="page-body">
		<div class="wrapper">

			<div id="header">

				<div id="headerbar">

					<?php if($this->warp->modules->count('headerleft')) : ?>
					<div class="left">
						<?php echo $this->warp->modules->render('headerleft'); ?>
					</div>
					<?php endif; ?>
					
					<?php if($this->warp->modules->count('headerright')) : ?>
					<div class="right">
						<?php echo $this->warp->modules->render('headerright'); ?>
					</div>
					<?php endif; ?>

				</div>
                
				<?php if ($this->warp->modules->count('breadcrumbs')) : ?>
                <div id="breadcrumbs">
                    <?php echo $this->warp->modules->render('breadcrumbs'); ?>
                </div>
                <?php endif; ?>

				<?php if($this->warp->modules->count('logo')) : ?>		
				<div id="logo">
					<?php echo $this->warp->modules->render('logo'); ?>
				</div>
				<?php endif; ?>

				<?php if($this->warp->modules->count('menu')) : ?>
				<div id="menu">
					<?php echo $this->warp->modules->render('menu'); ?>
				</div>
				<?php endif; ?>

				<?php if ($this->warp->modules->count('banner')) : ?>
				<div id="banner">
					<?php echo $this->warp->modules->render('banner'); ?>
				</div>
				<?php endif;  ?>
				
				<?php if($this->warp->modules->count('search')) : ?>
				<div id="search">
					<?php echo $this->warp->modules->render('search'); ?>
				</div>
				<?php endif; ?>

			</div>
			<!-- header end -->

			<?php  if ($this->warp->modules->count('top + topblock')) : ?>
			<div id="top">
            
				<?php if($this->warp->modules->count('topblock')) : ?>
                <div class="topblock width100 float-left">
                    <?php echo $this->warp->modules->render('topblock'); ?>
                </div>
                <?php endif; ?>
    
                <?php if ($this->warp->modules->count('top')) : ?>
					<?php echo $this->warp->modules->render('top', array('wrapper'=>"topbox float-left", 'layout'=>$this->warp->config->get('top'))); ?>
				<?php endif; ?>
                
			</div>
			<!-- top end -->
			<?php endif; ?>

			<div id="middle">
				<div id="middle-expand">

					<div id="main">
						<div id="main-shift">

							<?php if ($this->warp->modules->count('maintop')) : ?>
							<div id="maintop">
								<?php echo $this->warp->modules->render('maintop', array('wrapper'=>"maintopbox float-left", 'layout'=>$this->warp->config->get('maintop'))); ?>									
							</div>
							<!-- maintop end -->
							<?php endif; ?>

							<div id="mainmiddle">
								<div id="mainmiddle-expand">
								
									<div id="content">
										<div id="content-shift">

											<?php if ($this->warp->modules->count('contenttop')) : ?>
											<div id="contenttop">
												<?php echo $this->warp->modules->render('contenttop', array('wrapper'=>"contenttopbox float-left", 'layout'=>$this->warp->config->get('contenttop'))); ?>
											</div>
											<!-- contenttop end -->
											<?php endif; ?>

											<div id="component">
												<div class="component-bg floatbox">
													<?php echo $this->warp->template->render('content'); ?>
												</div>
											</div>
				
											<?php if ($this->warp->modules->count('contentbottom')) : ?>
											<div id="contentbottom">
												<?php echo $this->warp->modules->render('contentbottom', array('wrapper'=>"contentbottombox float-left", 'layout'=>$this->warp->config->get('contentbottom'))); ?>
											</div>
											<!-- mainbottom end -->
											<?php endif; ?>
										
										</div>
									</div>
									<!-- content end -->
									
									<?php if($this->warp->modules->count('contentleft')) : ?>
									<div id="contentleft">
										<?php echo $this->warp->modules->render('contentleft'); ?>
									</div>
									<?php endif; ?>
									
									<?php if($this->warp->modules->count('contentright')) : ?>
									<div id="contentright">
										<?php echo $this->warp->modules->render('contentright'); ?>
									</div>
									<?php endif; ?>
									
								</div>
							</div>
							<!-- mainmiddle end -->

							<?php if ($this->warp->modules->count('mainbottom')) : ?>
							<div id="mainbottom">
								<?php echo $this->warp->modules->render('mainbottom', array('wrapper'=>"mainbottombox float-left", 'layout'=>$this->warp->config->get('mainbottom'))); ?>
							</div>
							<!-- mainbottom end -->
							<?php endif; ?>
						
						</div>
					</div>
					
					<?php if($this->warp->modules->count('left')) : ?>
					<div id="left">
						<?php echo $this->warp->modules->render('left'); ?>
					</div>
					<?php endif; ?>
					
					<?php if($this->warp->modules->count('right')) : ?>
					<div id="right">
						<?php echo $this->warp->modules->render('right'); ?>
					</div>
					<?php endif; ?>

				</div>
			</div>
					
			<?php if ($this->warp->modules->count('bottom + bottomblock')) : ?>
			<div id="bottom">
			
				<div class="bottom-line"></div>
            	
				<?php if ($this->warp->modules->count('bottom')) : ?>
                    <?php echo $this->warp->modules->render('bottom', array('wrapper'=>"bottombox float-left", 'layout'=>$this->warp->config->get('bottom'))); ?>
                <?php endif; ?>
                
                <?php if($this->warp->modules->count('bottomblock')) : ?>
                <div class="bottomblock width100 float-left">
                    <?php echo $this->warp->modules->render('bottomblock'); ?>
                </div>
                <?php endif; ?>
                
			</div>
			<!-- bottom end -->
			<?php endif; ?>

		</div>
	</div>
    
	<div id="page-footer">
		<div class="wrapper">
      
      		<?php if ($this->warp->modules->count('bottom2')) : ?>
			<div id="bottom2">
                
                <?php if ($this->warp->modules->count('bottom2')) : ?>
                    <?php echo $this->warp->modules->render('bottom2', array('wrapper'=>"bottombox float-left", 'layout'=>$this->warp->config->get('bottom2'))); ?>
                <?php endif; ?>
                
			</div>
			<!-- bottom end -->
			<?php endif; ?>
      
			<?php if ($this->warp->modules->count('footer + debug')) : ?>
			<div id="footer">

				<a class="anchor" href="#page"></a>
				<?php echo $this->warp->modules->render('footer'); ?>
				<?php echo $this->warp->modules->render('debug'); ?>
				
			</div>
			<!-- footer end -->
			<?php endif; ?>

		</div>
	</div>
        <?php echo $this->render('footer'); ?>
</body>
</html>