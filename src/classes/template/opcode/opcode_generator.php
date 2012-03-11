<?php
/** flatLand! : zewo
 * /classes/template/opcode/opcode_generator.php
 */

namespace Zewo\Templates\Opcode;

class OpcodeGenerator {

	public function __construct( $sTemplateContent ) {
		$this->_sTemplateSource = $sTemplateContent;
		$this->_oZewo = \Zewo\Zewo::getInstance();
	} // __construct

	public function generate( $sOpcodeID ) {
		$this->_initOpcode( $sOpcodeID );
		$this->_replaceComments();
		$this->_replaceVars();
		$this->_replaceConstants();

		return $this->_sOpcodeReturn;
	} // generate

	private function _initOpcode( $sOpcodeID ) {
		$this->_sOpcodeReturn  = '<?php /* ZEWO Opcode Template : ' . $sOpcodeID . ' */ ?>' . "\n";
		$this->_sOpcodeReturn .= $this->_sTemplateSource;
	} // _initOpcode

	private function _replaceComments() {
		// simple comments
		$this->_sOpcodeReturn = preg_replace( $this->_sSimpleCommentsRegex, '', $this->_sOpcodeReturn );
		// blocks comments
		$this->_sOpcodeReturn = preg_replace( $this->_sBlockCommentsRegex, '', $this->_sOpcodeReturn );
	} // _replaceComments

	private function _replaceVars() {
		$this->_sOpcodeReturn = preg_replace_callback( $this->_sVarsRegex, array( $this, '_parseVar' ), $this->_sOpcodeReturn );
	} // _replaceVars

	private function _parseVar( $aMatches ) {
		$aVarComponenents = preg_split( '/(\.|\-\>|\[.+\])/', $aMatches[1], -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );
		$sVarName = '';
		for( $i = -1, $l = sizeof( $aVarComponenents ); ++$i < $l; ) {
			if( $aVarComponenents[ $i ] == '.' )
				$sVarName .= "[ '" . $aVarComponenents[ ++$i ] . "' ]";
			elseif( $aVarComponenents[ $i ] == '->' )
				$sVarName .= "->" . $aVarComponenents[ ++$i ];
			else
				$sVarName .= $aVarComponenents[ $i ];
		}
		if( isset( $aMatches[ 2 ] ) )
			$sVarName = $this->_applyFunctionToVarExpression( $sVarName, $aMatches[ 2 ] );
		return '<?=' . $sVarName . '; ?>';
	} // _parseVar

	private function _replaceConstants() {
		$this->_sOpcodeReturn = preg_replace_callback( $this->_sConstantsRegex, array( $this, '_parseConstants' ), $this->_sOpcodeReturn );
	} // _replaceConstants

	private function _parseConstants( $aMatches ) {
		$sVarName = $aMatches[ 1 ];
		if( isset( $aMatches[ 2 ] ) )
			$sVarName = $this->_applyFunctionToVarExpression( $sVarName, $aMatches[ 2 ] );
		return '<?=' . $sVarName . '; ?>';
	} // _parseConstants

	private function _applyFunctionToVarExpression( $sVarName, $sFunctionsDefinition ) {
		$aFunctions = explode( '|', $sFunctionsDefinition );
		foreach( $aFunctions as $sFunction ) {
			$aFunctionComponents = explode( ':', $sFunction );
			$sFunctionName = array_shift( $aFunctionComponents );
			$sVarName = $sFunctionName . '( ' . $sVarName .  '' . ( sizeof( $aFunctionComponents ) ? ', ' . implode( ', ', $aFunctionComponents ) : '' ) . ' )';
		}
		return $sVarName;
	} // _applyFunctionToVarExpression

	private $_sTemplateSource;
	private $_sOpcodeReturn;

	private $_oZewo;

	// regexes
	private $_sSimpleCommentsRegex = '/(\{\*.+\*\})/';
	private $_sBlockCommentsRegex = '/(\{\*\}.+\{\*\})/sme';
	private $_sVarsRegex = '/\{(\$[^\|\}]+)[\|]*(.+)*\}/';
	private $_sConstantsRegex = '/\{#([^\|\}]+)[\|]*(.+)*\}/';

} // class::OpcodeGenerator
