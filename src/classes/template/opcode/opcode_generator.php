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
		// blocks elements
		$this->_replaceIfs();
		// inline elements
		$this->_replaceExpressions();

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

	private function _replaceExpressions() {
		$this->_sOpcodeReturn = preg_replace_callback( $this->_sExpressionBlockRegex, array( $this, '_parseExpressionBlock' ), $this->_sOpcodeReturn );
	} // _replaceExpressions

	private function _parseExpressionBlock( $aMatches ) {
		return '<?=' . $this->_parseExpression( $aMatches[ 1 ] ) . ' ?>';
	} // _parseExpressionBlock

	private function _parseExpression( $sExpression ) {
			// splitting conditions

			// splitting maths
		$sExpression = preg_replace_callback( $this->_aExpressionSplitRegexes, array( $this, '_parseExpressionParts' ), $sExpression, 1 );
		return $sExpression;
	} // _parseExpression

	private function _parseExpressionParts( $aMatches ) {
		$sExpression  = preg_replace_callback( $this->_aExpressionSplitRegexes, array( $this, '_parseExpressionParts' ), $this->_parseExpressionPart( trim( $aMatches[ 1 ] ) ) );
		$sExpression .= ' ' . trim( $aMatches[ 2 ] ) . ' ';
		$sExpression .= preg_replace_callback( $this->_aExpressionSplitRegexes, array( $this, '_parseExpressionParts' ), $this->_parseExpressionPart( trim( $aMatches[ 3 ] ) ) );
		return $sExpression;
	} // _parseExpressionParts

	private function _parseExpressionPart( $sPart ) {
		// constants
		$sPart = str_replace( '#', '', $sPart );
		// var
		$aVarParts = explode( '|', $sPart );
		$sVarName = $this->_parseVar( array_shift( $aVarParts ) );
		if( sizeof( $aVarParts ) )
			$sVarName = $this->_applyFunctionToVarExpression( $sVarName, $aVarParts );
		return $sVarName;
	} // _parseExpressionPart

	private function _replaceVars() {
		$this->_sOpcodeReturn = preg_replace_callback( $this->_sVarsRegex, array( $this, '_parseExpressionBlock' ), $this->_sOpcodeReturn );
	} // _replaceVars

	private function _parseVar( $sVarName ) {
		$aVarComponents = preg_split( '/(\.|\-\>|\[.+\])/', $sVarName, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );
		$sVarName = '';
		for( $i = -1, $l = sizeof( $aVarComponents ); ++$i < $l; ) {
			if( $aVarComponents[ $i ] == '.' )
				$sVarName .= "[ '" . $aVarComponents[ ++$i ] . "' ]";
			elseif( $aVarComponents[ $i ] == '->' )
				$sVarName .= "->" . $aVarComponents[ ++$i ];
			else
				$sVarName .= $aVarComponents[ $i ];
		}
		return $sVarName;
	} // _parseVar

	private function _applyFunctionToVarExpression( $sVarName, $aFunctions ) {
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

	private $_sTemplateSource;
	private $_sOpcodeReturn;

	private $_oZewo;

	// regexes
		// comments
	private $_sSimpleCommentsRegex = '/(\{\*.+\*\})/';
	private $_sBlockCommentsRegex = '/(\{\*\}.+\{\*\})/sme';
		// expression
	private $_sExpressionBlockRegex = '/\{([^\}]+)\}/';
	private $_aExpressionSplitRegexes = array(
		'/(.+)\s(<>|!=+|==+)(.+)/',
		'/(.+)([^-][<>]=?)(.+)/',
		'/(.+)(\+|-[^\>]|\*|\/|%)(.+)/',
	);
		// if blocks
	private $_sIfBlocksOpenRegex = '/\{([else|else ]*)if ([^\}]+)\}/';
	private $_sIfBlocksElseRegex = '/\{else\}/';
	private $_sIfBlocksCloseRegex = '/\{\/if\}/';

} // class::OpcodeGenerator
