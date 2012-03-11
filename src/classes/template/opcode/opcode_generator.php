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
		$this->_replaceForeachs();
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
		$sExpressionAfter = preg_replace_callback( $this->_aExpressionSplitRegexes, array( $this, '_parseExpressionParts' ), $sExpression, 1 );
		if( $sExpression == $sExpressionAfter )
			$sExpressionAfter = $this->_parseExpressionPart( $sExpressionAfter );
		return $sExpressionAfter;
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

	private function _replaceForeachs() {
		$this->_sOpcodeReturn = preg_replace_callback( $this->_sForeachBlocksRegex, array( $this, '_parseForeachBlock' ), $this->_sOpcodeReturn );
	} // _replaceForeachs

	private function _parseForeachBlock( $aMatches ) {
		// $this->_oZewo->utils->trace( $aMatches );
		$sCode = '';
		// parse parameters
		$aParameters = array(
			'name' => 'loop',
			'key' => 'key'
		);
		$aRawParameters = explode( ' ', $aMatches[ 1 ] );
		foreach( $aRawParameters as $sRawParameter ) {
			preg_match( '/(from|item|key|name)=(.+)/', $sRawParameter, $aParamMatches );
			$aParameters[ $aParamMatches[ 1 ] ] = ( $aParamMatches[ 1 ] == 'from' ) ? $this->_parseExpressionPart( $aParamMatches[ 2 ] ) : str_replace( array( '"', "'" ), '', $aParamMatches[ 2 ] );
		}
		// has foreachelse ?
		if( isset( $aMatches[ 3 ] ) && strpos( $aMatches[ 3 ], '{foreachelse}' ) !== false )
			$sCode .= '<?php if( !isset( ' . $aParameters[ 'from' ] . ' ) || sizeof( ' . $aParameters[ 'from' ] . ' ) === 0 ): ?>' . "\n";
		$sCode .= '<?php $' . $aParameters[ 'name' ] . '_index = 0; foreach( ' . $aParameters[ 'from' ] . ' as $' . $aParameters[ 'key' ] . ' => &$' . $aParameters[ 'item' ] . ' ): ' . "\n";
			$sCode .= "\t" . '$' . $aParameters[ 'name' ] . ' = array(' . "\n";
			$sCode .= "\t\t" . '"index" => $' . $aParameters[ 'name' ] . '_index,' . "\n";
			$sCode .= "\t\t" . '"iteration" => $' . $aParameters[ 'name' ] . '_index + 1,' . "\n";
			$sCode .= "\t\t" . '"first" => $' . $aParameters[ 'name' ] . '_index === 0,' . "\n";
			$sCode .= "\t\t" . '"last" => $' . $aParameters[ 'name' ] . '_index - 1 === sizeof( ' . $aParameters[ 'from' ] . ' ),' . "\n";
			$sCode .= "\t" . '); ?>' . "\n";
			$sCode .= "\t" . trim( $aMatches[ 2 ] ) . "\n";
		$sCode .= '<?php $' . $aParameters[ 'name' ] . '_index++; endforeach; ?>' . "\n";
		if( isset( $aMatches[ 3 ] ) && strpos( $aMatches[ 3 ], '{foreachelse}' ) !== false ) {
			$sCode .= '<?php else: ?>' . "\n";
				$sCode .= "\t" . trim( $aMatches[ 4 ] ) . "\n";
			$sCode .= '<?php endif; ?>' . "\n";
		}
		return $sCode;
	} // _parseForeachBlock

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
		// foreach blocks
	private $_sForeachBlocksRegex = '/\{foreach\s([^\}]*)\}(.*?)(\{foreachelse\}(.*?))?\{\/foreach\}/sm';

} // class::OpcodeGenerator
