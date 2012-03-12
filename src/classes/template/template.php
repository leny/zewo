<?php
/** flatLand! : zewo
 * /classes/template/template.php
 */

namespace Zewo\Templates;

class Template extends \Zewo\Tools\Singleton {

	public function __get( $sName ) {
		switch( $sName ) {
			case 'opcodePath':
				return $this->_sOpCodePath;
				break;
		}
	} // __get

	public function __construct( $sTPLPath ) {
		$this->_oZewo = \Zewo\Zewo::getInstance();
		$sCompleteTPLPath = $this->_oZewo->config->get( 'template.folders.templates' ) . $sTPLPath;
		if( !file_exists( $sCompleteTPLPath ) )
			throw new \InvalidArgumentException( "Template doesn't exists at \"" . $sCompleteTPLPath . "\" !" );
		$this->_sTPLPath = $sCompleteTPLPath;
	} // __construct

	public function render( $bDisplay=true, $sCacheID = null ) {
		$this->_genCacheName( $sCacheID );
		$this->_sOpCodePath = $this->_oZewo->config->get( 'template.folders.cache' ) . $this->_sCacheName . '.toc';
		if( !$this->_existsInOpcode() )
			$this->_generateOpcode();
		$sOpcode = $this->_getFromOpcode();
		if( $bDisplay ) {
			$this->_oZewo->utils->trace( 'render template' );
			// TODO : display
		} else
			return $sOpcode;
	} // render

	private function _existsInOpcode() {
		return $this->_bForceExists || file_exists( $this->_oZewo->config->get( 'template.folders.cache' ) . $this->_sCacheName . '.toc' );
	} // _existsInOpcode

	private function _getFromOpcode() {
		return file_get_contents( $this->_oZewo->config->get( 'template.folders.cache' ) . $this->_sCacheName . '.toc' );
	} // _getFromOpcode

	private function _generateOpcode() {
		$this->_sOpCodeContent  = '<?php /* ZEWO Opcode Template : ' . $this->_sCacheName . ' */ ?>' . "\n";
		$this->_sOpCodeContent .= file_get_contents( $this->_sTPLPath );

		// parse includes
		$this->_sOpCodeContent = $this->_replaceIncludes( $this->_sOpCodeContent );
		// comments
		$this->_replaceComments();
		// blocks elements
		$this->_replaceIfs();
		$this->_replaceForeachs();
		// inline elements
		$this->_replaceExpressions();

		$this->_oZewo->utils->trace( $this->_sOpCodeContent );

		file_put_contents( $this->_oZewo->config->get( 'template.folders.cache' ) . $this->_sCacheName . '.toc' , $this->_sOpCodeContent );
	} // _generateOpcode

	private function _initOpcode( $sOpcodeID ) {
		$this->_sOpCodeContent  = '<?php /* ZEWO Opcode Template : ' . $sOpcodeID . ' */ ?>' . "\n";
		$this->_sOpCodeContent .= $this->_sTemplateSource;
	} // _initOpcode

	private function _replaceIncludes( $sSource ) {
		return preg_replace_callback( $this->_sIncludeBlockRegex, array( $this, '_parseIncludeBlock' ), $sSource );
	} // _replaceIncludes

	private function _parseIncludeBlock( $aMatches ) {
		preg_match_all( '/(\w+)="?([^"\s]+)"?\s?/', $aMatches[ 1 ], $aParameters );
		$sFileToLoad = null;
		$aAdditionalVars = array();
		foreach( $aParameters[ 0 ] as $i => $sValue ) {
			if( $aParameters[ 1 ][ $i ] == 'file' ) {
				$sFileToLoad = $aParameters[ 2 ][ $i ];
				if( !file_exists( $this->_oZewo->config->get( 'template.folders.templates' ) . $aParameters[ 2 ][ $i ] ) )
					throw new \InvalidArgumentException( "The {include} template \"" . $this->_oZewo->config->get( 'template.folders.templates' ) . $aParameters[ 2 ][ $i ] .  "\" doesn't exists !" );
			} else
				$aAdditionalVars[ '$' . $aParameters[ 1 ][ $i ] ] = '$' . $aParameters[ 1 ][ $i ] . ' = ' . $this->_parseExpression( $aParameters[ 2 ][ $i ] ) . ';';
		}
		if( is_null( $sFileToLoad ) )
			throw new \InvalidArgumentException( "There is no file attribute for {include} !" );
		$sCode  = '<?php ' . "\n";
		foreach( $aAdditionalVars as $sAdditionalVar )
			$sCode .= $sAdditionalVar . "\n";
		$sCode .= '?> ' . "\n";
		// TODO : avoid recursivity
		$oTemplate = new \Zewo\Templates\Template( $sFileToLoad );
		$oTemplate->render( false );
		$sCode .= '<?php include( "' . $oTemplate->opcodePath . '" ); ?>' . "\n";

		$sCode .= '<?php ' . "\n";
		foreach( $aAdditionalVars as $sVarName => $sAdditionalVar )
			$sCode .= 'unset( ' . $sVarName . ' );' . "\n";
		$sCode .= '?> ' . "\n";
		return $sCode;
	} // _parseIncludeBlock

	private function _replaceComments() {
		// simple comments
		$this->_sOpCodeContent = preg_replace( $this->_sSimpleCommentsRegex, '', $this->_sOpCodeContent );
		// blocks comments
		$this->_sOpCodeContent = preg_replace( $this->_sBlockCommentsRegex, '', $this->_sOpCodeContent );
	} // _replaceComments

	private function _replaceExpressions() {
		$this->_sOpCodeContent = preg_replace_callback( $this->_sExpressionBlockRegex, array( $this, '_parseExpressionBlock' ), $this->_sOpCodeContent );
	} // _replaceExpressions

	private function _parseExpressionBlock( $aMatches ) {
		return '<?=' . $this->_parseExpression( $aMatches[ 1 ] ) . '; ?>';
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
		$this->_sOpCodeContent = preg_replace_callback( $this->_sVarsRegex, array( $this, '_parseExpressionBlock' ), $this->_sOpCodeContent );
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
		$this->_sOpCodeContent = preg_replace_callback( $this->_sIfBlocksOpenRegex, array( $this, '_parseIfBlockOpen' ), $this->_sOpCodeContent );
		$this->_sOpCodeContent = preg_replace( $this->_sIfBlocksElseRegex, '<?php else: ?>', $this->_sOpCodeContent );
		$this->_sOpCodeContent = preg_replace( $this->_sIfBlocksCloseRegex, '<?php endif; ?>', $this->_sOpCodeContent );
	} // _replaceIfs

	private function _parseIfBlockOpen( $aMatches ) {
		return '<?php ' . $aMatches[ 1 ] . 'if( ' . $this->_parseExpression( $aMatches[ 2 ] ) . ' ): ?>';
	} // _parseIfBlockOpen

	private function _replaceForeachs() {
		$this->_sOpCodeContent = preg_replace_callback( $this->_sForeachBlocksRegex, array( $this, '_parseForeachBlock' ), $this->_sOpCodeContent );
	} // _replaceForeachs

	private function _parseForeachBlock( $aMatches ) {
		// $this->_oZewo->utils->trace( $aMatches );
		$sCode = '';
		// parse parameters
		$aParameters = array(
			'name' => 'loop',
			'key' => 'key',
			'item' => 'item',
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

	private function _genCacheName( $sCacheID = '' ) {
		// cache name is md5 of md5_file of template + cacheID
		if( is_null( $this->_sCacheName ) )
			$this->_sCacheName = md5( md5_file( $this->_sTPLPath ) . $sCacheID );
	} // _genCacheName

	private $_sTPLPath;
	private $_sTPLContent;
	private $_sParsedTemplate;

	private $_sOpCodePath;
	private $_sOpCodeContent;

	private $_sCacheName;

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
		// include blocks
	private $_sIncludeBlockRegex = '/\{include\s([^\}]+)\}/';

} // class::Template
