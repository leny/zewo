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
		$this->_oZewo->utils->trace( $this->_sOpcodeReturn );
	} // _replaceVars

	private function _parseVar( $aMatches ) {
		$this->_oZewo->utils->trace( $aMatches );
		$sVarName = $aMatches[1];
		// TODO dots in var names
		// functions applied
		if( isset( $aMatches[2] ) ) {
			$aFunctions = explode( '|', $aMatches[2] );
			$this->_oZewo->utils->trace( $aFunctions );
		}
		return '<?=' . $sVarName . '; ?>';
	} // _parseVar

	private $_sTemplateSource;
	private $_sOpcodeReturn;

	private $_oZewo;

	// regexes
	private $_sSimpleCommentsRegex = '/(\{\*.+\*\})/';
	private $_sBlockCommentsRegex = '/(\{\*\}.+\{\*\})/sme';
	private $_sVarsRegex = '/\{(\$[^\|\}]+)[\|]*(.+)*\}/';

} // class::OpcodeGenerator
