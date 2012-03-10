<?php
/** flatLand! : zewo
 * /classes/template/template.php
 */

namespace Zewo\Templates;

class Template extends \Zewo\Tools\Singleton {

	public function __construct( $sTPLPath ) {
		global $zewo;
		$sCompleteTPLPath = $zewo->config->get( 'template.folders.templates' ) . $sTPLPath;
		if( !file_exists( $sCompleteTPLPath ) )
			throw new \InvalidArgumentException( "Template doesn't exists at \"" . $sCompleteTPLPath . "\" !" );
		$this->_sTPLPath = $sCompleteTPLPath;
	} // __construct

	public function render() {
		global $zewo;
		// TODO : get from cache
		// TODO : render and store in cache
		$zewo->utils->trace( 'render template', $this );
	} // render

	private $_sTPLPath;

} // class::Template
