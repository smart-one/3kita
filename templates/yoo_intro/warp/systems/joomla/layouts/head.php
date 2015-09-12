<jdoc:include type="head" />
<?php

// load jQuery, if not loaded before
if (!$this->warp->system->application->get('jquery')) {
	$this->warp->system->application->set('jquery', true);
	$this->warp->system->document->addScript($this->warp->path->url('lib:jquery/jquery.js'));	
}

$style_urls  = array_keys($this->warp->stylesheets->get());
$script_urls = array_keys($this->warp->javascripts->get());

// get compressed styles and scripts
if ($compression = $this->warp->config->get('compression')) {
	$options = array();
	
	if ($compression >= 2) {
		$options['gzip'] = true;
	}

	if ($compression == 3) {
		$options['data_uri'] = true;
	}

	if ($urls = $this->warp->cache->processStylesheets($style_urls, $options)) {
		$style_urls = $urls;
	}

	if ($urls = $this->warp->cache->processJavascripts($script_urls, $options)) {
		$script_urls = $urls;
	}
	
	$head = $this->warp->system->document->getHeadData();

	if (count($head['styleSheets'])) {
		foreach ($head['styleSheets'] as $style => $meta) {
			if (preg_match('/\.css$/i', $style) && ($url = $this->warp->cache->processStylesheets(array($style), array_merge($options, array('data_uri' => false))))) {
				$style = array_shift($url);
			}

			$styles[$style] = $meta;
		}
		$head['styleSheets'] = $styles;
	}

	if (count($head['scripts'])) {
		foreach ($head['scripts'] as $script => $meta) {
			if (preg_match('/\.js$/i', $script) && ($url = $this->warp->cache->processJavascripts(array($script), $options))) {
				$script = array_shift($url);
			}

			$scripts[$script] = $meta;
		}
		$head['scripts'] = $scripts;
	}

	$this->warp->system->document->setHeadData($head);
}

// add styles
foreach ($style_urls as $style) {
	echo '<link rel="stylesheet" href="'.$style.'" type="text/css" />'."\n";
}

// add scripts
foreach ($script_urls as $script) {
	echo '<script type="text/javascript" src="'.$script.'"></script>'."\n";
}

// add style declarations
foreach ($this->warp->stylesheets->getDeclarations() as $type => $style) {
  echo '<style type="'.$type.'">'.$style.'</style>'."\n";
}

// add script declarations
foreach ($this->warp->javascripts->getDeclarations() as $type => $script) {
  echo '<script type="'.$type.'">'.$script.'</script>'."\n";
} 

$this->output('head');