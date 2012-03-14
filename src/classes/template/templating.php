<?php
/** flatLand! : zewo
 * /classes/template/templating.php
 */

namespace Zewo\Templates;

// TODO : temporary assigns (for fetches and others)

class Templating extends \Zewo\Tools\Singleton {

	public function assign( $sName, $mValue ) {
		$this->_aAssignedVariables[ $sName ] = $mValue;
	} // assign

	public function assignByRef( $sName, &$mValue ) {
		$this->_aAssignedVariables[ $sName ] = $mValue;
	} // assignByRef
	
	public function getAssignedVariable( $sName ) {
		return isset( $this->_aAssignedVariables[ $sName ] ) ? $this->_aAssignedVariables[ $sName ] : null ;
	} // getAssignedVariable

	public function fetch( $sTPLPath, $sCacheID = null ) {
		$sTemplateFilePath = $this->_getTemplateFile( $sTPLPath, $sCacheID );
		ob_start();
		include( $sTemplateFilePath );
		return ob_get_clean();
	} // fetch

	public function display( $sTPLPath, $aFetches = array(), $sCacheID = null ) {
		// TODO : assign fetches
		$sTemplateFilePath = $this->_getTemplateFile( $sTPLPath, $sCacheID );
		include( $sTemplateFilePath );
	} // display

	public function close( $sTPLPath, $aFetches = array(), $sCacheID = null ) {
		// TODO : assign fetches
		$sTemplateFilePath = $this->_getTemplateFile( $sTPLPath, $sCacheID );
		include( $sTemplateFilePath );
		die();
	} // close

	protected function __construct() {
		$this->_oZewo = \Zewo\Zewo::getInstance();
	} // __construct

	private function _getTemplate( $sTPLPath ) {
		if( !isset( $this->_aRegisteredTemplates[ $sTPLPath ] ) )
			$this->_aRegisteredTemplates[ $sTPLPath ] = new Template( $sTPLPath );
		return $this->_aRegisteredTemplates[ $sTPLPath ];
	} // _getTemplate
	
	private function _generateAssignedVars() {
		$sCode  = '<?php ' . "\n";
		foreach( $this->_aAssignedVariables as $sName => $mValue )
			$sCode .= '		$' . $sName . ' = \Zewo\Templates\Templating::getInstance()->getAssignedVariable( ' . $sName . ' );' . "\n";
		$sCode .= '?>' . "\n";
		return $sCode;
	} // _generateAssignedVars

	private function _getTemplateFile( $sTPLPath, $sCacheID = null ) {
		$sTemplateFilePath = $this->_oZewo->config->get( 'template.folders.cache' ) . md5( md5_file( $sTPLPath ) . $sCacheID ) . '.tpc';
		$sTPLFileContent  = $this->generateAssignedVars();
		$sTPLFileContent .= $this->_getTemplate( $sTPLPath )->render( $sCacheID );
		if( !file_put_contents( $sTemplateFilePath, $sTPLFileContent ) )
			throw new RuntimeException( 'The template file "' . $sTemplateFilePath . '" cannot be written !' );
		return $sTemplateFilePath;
	} // _getTemplateFile

	private $_oZewo;

	private $_aAssignedVariables = array();
	private $_aRegisteredTemplates = array();

} // class::Templating
