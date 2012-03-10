<?php
/** flatLand! : zewo
 * /classes/zewo.php : main classe
 */

namespace Zewo;

class Zewo extends Tools\Singleton {

	public function __get( $sName ) {
		switch( $sName ) {
			case 'route':
				return $this->_oRouting;
				break;
			case 'utils':
				return $this->_oUtils;
				break;
			case 'global':
			case 'globals':
				return $this->utils->globals;
				break;
			// TODO : case 'template': break;
			// TODO : case 'db': break;
		}
	} // __get

	// shortcuts

	public function __call( $sName, $aArguments ) {
		switch( $sName ) {
			// ROUTING shortcuts
			case 'run':
			case 'post':
			case 'get':
			case 'map':
			case 'error':
			case 'redirect':
				call_user_func_array( array( $this->route, $sName ), $aArguments );
				break;
		}
	} // __call

	public function init( $mConfig=null ) {
		// TODO : check config type
		$this->_applyConfig();
	} // init

	private function _applyConfig() {
		$this->_oRouting = Routing\Router::getInstance();
		$this->_oUtils = Utils\Utils::getInstance();
	} // _applyConfig

	private $_oTemplate;
	private $_oRouting;
	private $_oUtils;
	private $_oDB;

} // class::Zewo
