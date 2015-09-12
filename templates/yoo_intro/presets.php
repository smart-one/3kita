<?php
/**
* @package   yoo_intro
* @author    YOOtheme http://www.yootheme.com
* @copyright Copyright (C) YOOtheme GmbH
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

/*
 * Presets
 */

$default_preset = array();

$warp->config->addPreset('default', 'Beige Yellow',array_merge($default_preset,array(
	'color' => 'default'
)));

$warp->config->addPreset('beigeblue', 'Beige Blue', array_merge($default_preset,array(
	'color' => 'beigeblue'
)));

$warp->config->addPreset('beigegreen', 'Beige Green', array_merge($default_preset,array(
	'color' => 'beigegreen'
)));

$warp->config->addPreset('beigered', 'Beige Red', array_merge($default_preset,array(
	'color' => 'beigered'
)));

$warp->config->addPreset('greyblue', 'Grey Blue', array_merge($default_preset,array(
	'color' => 'greyblue'
)));

$warp->config->addPreset('greyorange', 'Grey Orange', array_merge($default_preset,array(
	'color' => 'greyorange'
)));

$warp->config->addPreset('greyred', 'Grey Red', array_merge($default_preset,array(
	'color' => 'greyred'
)));

$warp->config->addPreset('khakiblue', 'Khaki Blue', array_merge($default_preset,array(
	'color' => 'khakiblue'
)));

$warp->config->addPreset('khakigreen', 'Khaki Green', array_merge($default_preset,array(
	'color' => 'khakigreen'
)));

$warp->config->addPreset('khakiorange', 'Khaki Orange', array_merge($default_preset,array(
	'color' => 'khakiorange'
)));

$warp->config->addPreset('blue', 'Blue Orange', array_merge($default_preset,array(
	'color' => 'blue'
)));

$warp->config->addPreset('brown', 'Brown Yellow', array_merge($default_preset,array(
	'color' => 'brown'
)));

$warp->config->applyPreset();