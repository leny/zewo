<?php
/** flatLand! : zewo
 * /classes/routing/route.php : main routing classe
 */

namespace Zewo\Routing;

// TODO : matching params in route
// TODO : passing params to callbacks
// TODO : matching optional params in route
// TODO : matching * in route
// TODO : matching AJAX

class Route {

	public function __construct( $sPattern, $aMethods, $aCallbacks, $bIsAJAX = false ) {
		$this->_sPattern = $sPattern;
		$this->_aAllowedMethods = $aMethods;
		foreach( $aCallbacks as $cCallback ) {
			if( !is_callable( $cCallback ) )
				throw new InvalidArgumentException( "A route middleware must be callable !" );
			$this->_aCallbacks[] = $cCallback;
		}
		$this->_bIsAJAX = $bIsAJAX;
	} // __construct

	public function match( $sURI ) {
		if( $this->_isMatching( $sURI ) ) {
			$this->_sCurrentURI = $sURI;
			return true;
		} else return false;
	} // match

	public function exec() {
		foreach( $this->_aCallbacks as $cCallback )
			call_user_func_array( $cCallback, array() ); // TODO : add params
	} // exec

	private function _isMatching( $sURI ) {
		$bMatching = true;
		// matching method
		$bMatching = $bMatching && ( in_array( strtolower( $_SERVER[ 'REQUEST_METHOD' ] ), $this->_aAllowedMethods ) );
		// TODO : matching ajax if needed
		// TODO : matching params
		// matching pattern
		$bMatching = $bMatching && ( $sURI == $this->_sPattern );
		return $bMatching;
	} // _isMatching

	private $_sCurrentURI;

	private $_bIsAJAX = false;
	private $_sPattern;
	private $_aAllowedMethods;
	private $_aCallbacks = array();

} // class::Route
