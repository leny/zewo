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
		// comments
		$this->_replaceComments();
		// inline elements
		$this->_replaceVars();
		$this->_replaceConstants();
		// blocks elements
		$this->_replaceIfs();
		$this->_oZewo->utils->trace( $this->_sOpcodeReturn );
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
		$aVarComponents = preg_split( '/(\.|\-\>|\[.+\])/', $aMatches[1], -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );
		$sVarName = '';
		for( $i = -1, $l = sizeof( $aVarComponents ); ++$i < $l; ) {
			if( $aVarComponents[ $i ] == '.' )
				$sVarName .= "[ '" . $aVarComponents[ ++$i ] . "' ]";
			elseif( $aVarComponents[ $i ] == '->' )
				$sVarName .= "->" . $aVarComponents[ ++$i ];
			else
				$sVarName .= $aVarComponents[ $i ];
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

	private function _replaceIfs() {
		$this->_sOpcodeReturn = preg_replace_callback( $this->_sIfBlocksOpenRegex, array( $this, '_parseIfBlockOpen' ), $this->_sOpcodeReturn );

		$this->_sOpcodeReturn = preg_replace( $this->_sIfBlocksElseRegex, '<?php else: ?>', $this->_sOpcodeReturn );
		$this->_sOpcodeReturn = preg_replace( $this->_sIfBlocksCloseRegex, '<?php endif; ?>', $this->_sOpcodeReturn );
	} // _replaceIfs

	private function _parseIfBlockOpen( $aMatches ) {
		return '<?php ' . $aMatches[ 1 ] . 'if( ' . $this->_parseExpression( $aMatches[ 2 ] ) . ' ): ?>';
	} // _parseIfBlockOpen

	private function _parseExpression( $sExpression ) {
		// TODO
		return $sExpression;
	} // _parseExpression

	private $_sTemplateSource;
	private $_sOpcodeReturn;

	private $_oZewo;

	// regexes
	private $_sSimpleCommentsRegex = '/(\{\*.+\*\})/';
	private $_sBlockCommentsRegex = '/(\{\*\}.+\{\*\})/sme';
	private $_sVarsRegex = '/\{(\$[^\|\}]+)[\|]*(.+)*\}/';
	private $_sConstantsRegex = '/\{#([^\|\}]+)[\|]*(.+)*\}/';
		// if blocks
	private $_sIfBlocksOpenRegex = '/\{([else|else ]*)if ([^\}]+)\}/';
	private $_sIfBlocksElseRegex = '/\{else\}/';
	private $_sIfBlocksCloseRegex = '/\{\/if\}/';

} // class::OpcodeGenerator
