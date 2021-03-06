<?php
/** flatLand! : zewo
 * /classes/template/templating.php
 */

namespace Zewo\Templates;

class Templating extends \Zewo\Tools\Singleton {

	public function clearCache() {
		$aCachedFiles = glob( $this->_oZewo->config->get( 'template.folders.cache' ) . '*.t*c' );
		foreach( $aCachedFiles as $sFile)
			unlink( $sFile );
	} // clearCache

	public function assign( $sName, $mValue ) {
		$this->_aAssignedVariables[ $sName ] = $mValue;
	} // assign

	public function assignByRef( $sName, &$mValue ) {
		$this->_aAssignedVariables[ $sName ] = $mValue;
	} // assignByRef

	public function getAssignedVariable( $sName ) {
		return isset( $this->_aAssignedVariables[ $sName ] ) ? $this->_aAssignedVariables[ $sName ] : null ;
	} // getAssignedVariable

	public function fetchTo( $sName, $sTPLPath, $sCacheID = null ) {
		$this->assign( $sName, $this->fetch( $sTPLPath, $sCacheID ) );
	} // fetchTo

	public function fetch( $sTPLPath, $sCacheID = null ) {
		$sTemplateFilePath = $this->_getTemplateFile( $sTPLPath, $sCacheID );
		ob_start();
		include( $sTemplateFilePath );
		return ob_get_clean();
	} // fetch

	public function display( $sTPLPath, $aFetches = array(), $sCacheID = null ) {
		$this->_assignFetches( $aFetches );
		$sTemplateFilePath = $this->_getTemplateFile( $sTPLPath, $sCacheID );
		include( $sTemplateFilePath );
	} // display

	public function close( $sTPLPath, $aFetches = array(), $sCacheID = null ) {
		$this->display( $sTPLPath, $aFetches, $sCacheID );
		die();
	} // close

	protected function __construct() {
		$this->_oZewo = \Zewo\Zewo::getInstance();
	} // __construct

	private function _assignFetches( $aFetches = array() ) {
		if( !is_array( $aFetches ) || !sizeof( $aFetches ) )
			return;
		foreach( $aFetches as $sName => $sTPLPath )
			$this->assign( $sName, $this->fetch( $sTPLPath ) );
	} // _assignFetches

	private function _getTemplate( $sTPLPath ) {
		if( !isset( $this->_aRegisteredTemplates[ $sTPLPath ] ) )
			$this->_aRegisteredTemplates[ $sTPLPath ] = new Template( $sTPLPath );
		return $this->_aRegisteredTemplates[ $sTPLPath ];
	} // _getTemplate

	private function _generateAssignedVars() {
		$sCode  = '<?php ' . "\n";
		$sCode .= '		$zewo = \Zewo\Zewo::getInstance();' . "\n";
		$sCode .= '		defined( "LDELIM" ) ?: define( "LDELIM", "{" );' . "\n";
		$sCode .= '		defined( "RDELIM" ) ?: define( "RDELIM", "}" );' . "\n";
		foreach( $this->_aAssignedVariables as $sName => $mValue )
			$sCode .= '		$' . $sName . ' = $zewo->tpl->getAssignedVariable( \'' . $sName . '\' );' . "\n";
		$sCode .= '?>' . "\n";
		return $sCode;
	} // _generateAssignedVars

	private function _getTemplateFile( $sTPLPath, $sCacheID = null ) {
		$sTemplateFilePath = $this->_oZewo->config->get( 'template.folders.cache' ) . md5( md5_file( $this->_oZewo->config->get( 'template.folders.templates' ) . $sTPLPath ) . $sCacheID ) . '.tpc';
		$sTPLFileContent  = $this->_generateAssignedVars();
		$sTPLFileContent .= $this->_getTemplate( $sTPLPath )->render( $sCacheID );
		if( !file_put_contents( $sTemplateFilePath, $sTPLFileContent ) )
			throw new RuntimeException( 'The template file "' . $sTemplateFilePath . '" cannot be written !' );
		return $sTemplateFilePath;
	} // _getTemplateFile

	private $_oZewo;

	private $_aAssignedVariables = array();
	private $_aRegisteredTemplates = array();

} // class::Templating
