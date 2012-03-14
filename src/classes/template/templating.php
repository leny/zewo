<?php
/** flatLand! : zewo
 * /classes/template/templating.php
 */

namespace Zewo\Templates;

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

	public function display( $sTPLPath ) {
		// TODO : generate assigned vars 
		$this->_getTemplate( $sTPLPath )->render();
	} // display

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

	private $_aAssignedVariables = array();
	private $_aRegisteredTemplates = array();

} // class::Templating
