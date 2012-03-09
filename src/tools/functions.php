<?php
/** flatLand! - zewo
 * /tools/functions.php
 */

function trace() {
	global $config;
	$aAttributes = func_get_args();
	if(!is_array($aAttributes))
		die('nothing to debug');
	$aBacktrace = debug_backtrace();
	$aBacktrace = reset( $aBacktrace );
	echo '<div style="border: 1px solid #ffc266; background: #ffffcc; padding: 2px 5px; margin: 5px; font-size: 11px; font-family: Verdana;">';
		echo '<strong style="color: #ff944c;"><small>trace: ' . str_replace($config['path']['system'], '/', $aBacktrace['file']) . ' ln. ' . $aBacktrace['line'] . '</small></strong>';
	for($i=0; $i<count($aAttributes); $i++) {
		if($i > 0)
			echo '<hr style="height: 1px; border: 0; background: #ffd699" />';
		echo '<pre>';
			var_dump($aAttributes[$i]);
		echo '</pre>';
	}
	echo '</div>';
} // trace
