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

	public function display( $sTPLPath ) {
		$this->_getTemplate( $sTPLPath )->render();
	} // display

	private function _getTemplate( $sTPLPath ) {
		if( !isset( $this->_aRegisteredTemplates[ $sTPLPath ] ) )
			$this->_aRegisteredTemplates[ $sTPLPath ] = new Template( $sTPLPath );
		return $this->_aRegisteredTemplates[ $sTPLPath ];
	} // _getTemplate

	private $_aAssignedVariables = array();
	private $_aRegisteredTemplates = array();

} // class::Templating
