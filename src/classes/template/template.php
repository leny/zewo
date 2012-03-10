<?php
/** flatLand! : zewo
 * /classes/template/template.php
 */

namespace Zewo\Templates;

class Template extends \Zewo\Tools\Singleton {

	public function __construct( $sTPLPath ) {
		$this->_oZewo = \Zewo\Zewo::getInstance();
		$sCompleteTPLPath = $this->_oZewo->config->get( 'template.folders.templates' ) . $sTPLPath;
		if( !file_exists( $sCompleteTPLPath ) )
			throw new \InvalidArgumentException( "Template doesn't exists at \"" . $sCompleteTPLPath . "\" !" );
		$this->_sTPLPath = $sCompleteTPLPath;
	} // __construct

	public function render( $sCacheID = null ) {
		if( $this->_existsInCache( $sCacheID ) ) {
			return $this->_getFromCache( $sCacheID );
		} else {
			$this->_parseTemplate();
		}
		$this->_oZewo->utils->trace( 'render template', $this );
	} // render

	private function _existsInCache( $sCacheID ) {
		// TODO
		return false;
	} // _existsInCache

	private function _getFromCache( $sCacheID ) {
		// TODO
	} // _getFromCache

	private function _parseTemplate() {
		$this->_sTPLContent = file_get_contents( $this->_sTPLPath );
		// TODO
	} // _parseTemplate

	private $_sTPLPath;
	private $_sTPLContent;
	private $_sParsedTemplate;

	private $_oZewo;

} // class::Template
