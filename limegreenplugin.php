<?php
/*
Plugin Name: Limegreen
Plugin URI: http://code.google.com/p/limegreen/wiki/WordpressPlugin
Version: 0.43.0
Description: Make information from a limegreen auto update atom file avaliable in wordpress. For more information see: http://code.google.com/p/limegreen/wiki/WordpressPlugin
Author: James Low
Author URI: http://jameslow.com
*/

include 'limegreen.php';

function limegreen_software_history($autoupdate, $timeformat = null) {
	$limegreen = new Limegreen($autoupdate);
	$builds = array_reverse($limegreen->getBuilds(false));
	foreach ($builds as $build) {
		echo '<br /><b>'.$build->version.'</b> - '.date(($timeformat == null ? "M j / y" : $timeformat),strtotime(strval($build->updated))).'<br />';
		echo preg_replace('/(\r|\n|\r\n)<br *\/?>/',"\r",$build->content);
	}
}

?>