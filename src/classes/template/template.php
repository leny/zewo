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
		$this->_genCacheName( $sCacheID );
		if( $this->_existsInCache( $sCacheID ) ) {
			return $this->_getFromCache( $sCacheID );
		} else {
			$this->_parseTemplate();
		}
		$this->_oZewo->utils->trace( 'render template' );
	} // render

	private function _existsInCache( $sCacheID ) {
		// TODO : exists in cache, and check in opcode for validity
		return false;
	} // _existsInCache

	private function _getFromCache( $sCacheID ) {
		// TODO
	} // _getFromCache

	private function _existsInOpcode() {
		return file_exists( $this->_oZewo->config->get( 'template.folders.cache' ) . $this->_sCacheName . '.toc' );
	} // _existsInOpcode

	private function _parseTemplate() {
		if( false /* $this->_existsInOpcode() */ ) {
			// TODO
		} else {
			$oOpcodeGenerator = new \Zewo\Templates\Opcode\OpcodeGenerator( file_get_contents( $this->_sTPLPath ) );
			$oOpcodeGenerator->generate( $this->_sCacheName );
			// file_put_contents( $this->_oZewo->config->get( 'template.folders.cache' ) . $this->_sCacheName . '.toc' , $oOpcodeGenerator->generate( $this->_sCacheName ) );
		}
	} // _parseTemplate

	private function _genCacheName( $sCacheID = '' ) {
		// cache name is md5 of md5_file of template + cacheID
		if( is_null( $this->_sCacheName ) )
			$this->_sCacheName = md5( md5_file( $this->_sTPLPath ) . $sCacheID );
	} // _genCacheName

	private $_sTPLPath;
	private $_sTPLContent;
	private $_sParsedTemplate;

	private $_sCacheName;

	private $_oZewo;

} // class::Template
