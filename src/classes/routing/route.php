<?php
/** flatLand! : zewo
 * /classes/routing/route.php : main routing classe
 */

namespace Zewo\Routing;

class Route {

	public function __construct( $sPattern, $aMethods, $aCallbacks, $bIsAJAX = false ) {
		$this->_sPattern = str_replace( ')', ')?', $sPattern );
		$this->_aAllowedMethods = $aMethods;
		foreach( $aCallbacks as $cCallback ) {
			if( !is_callable( $cCallback ) )
				throw new InvalidArgumentException( "A route middleware must be callable !" );
			$this->_aCallbacks[] = $cCallback;
		}
		$this->_bIsAJAX = $bIsAJAX;
		$this->_generateRegex();
	} // __construct

	public function match( $sURI ) {
		if( $this->_match( $sURI ) ) {
			$this->_sCurrentURI = $sURI;
			return true;
		} else return false;
	} // match

	public function exec() {
		foreach( $this->_aCallbacks as $cCallback )
			call_user_func_array( $cCallback, array_values( $this->_aParams ) );
	} // exec

	private function _match( $sURI ) {
		$bMatching = true;
		$bMatching = $bMatching && $this->_matchMethod();
		$bMatching = $bMatching && $this->_matchAJAX();
		$bMatching = $bMatching && $this->_matchURL( $sURI );
		return $bMatching;
	} // _match

	private function _matchMethod() {
		return in_array( strtolower( $_SERVER[ 'REQUEST_METHOD' ] ), $this->_aAllowedMethods );
	} // _matchMethod

	private function _matchAJAX() {
		return $this->_bIsAJAX ? ( strtolower( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) == 'xmlhttprequest' ) : true;
	} // _matchAJAX

	private function _matchURL( $sURI ) {
		if ( preg_match( $this->_sPatternAsRegex, $sURI, $aParamValues ) ) {
			array_shift( $aParamValues );
			foreach ( $this->_aParamNames as $i => $sValue ) {
				$sVal = substr( $sValue, 1 );
				if ( isset( $aParamValues[ $sVal ] ) ) {
					$this->_aParams[ $sVal ] = urldecode( $aParamValues[ $sVal ] );
				}
			}
			return true;
		} else
			return false;
	} // _matchURL

	private function _generateRegex() {
		// extract url params
		preg_match_all( '@:([\w]+)@', $this->_sPattern, $aParamNames, PREG_PATTERN_ORDER );
		$this->_aParamNames = $aParamNames[0];

		// convert url params to regex
		$sPatternAsRegex = preg_replace_callback( '@:[\w]+@', array( $this, '_convertPatternToRegex' ), $this->_sPattern );
		if ( substr($this->_sPattern, -1) === '/' ) {
			$sPatternAsRegex = $sPatternAsRegex . '?';
		}
		$this->_sPatternAsRegex = '@^' . $sPatternAsRegex . '$@';
	} // _generateRegex

	private function _convertPatternToRegex( $aMatches ) {
		$sKey = str_replace(':', '', $aMatches[0]);
		if ( array_key_exists( $sKey, $this->_aConditions ) )
			return '(?P<' . $sKey . '>' . $this->_aConditions[$sKey] . ')';
		else
			return '(?P<' . $sKey . '>[a-zA-Z0-9_\-\.\!\~\*\\\'\(\)\:\@\&\=\$\+,%]+)';
	} // _convertPatternToRegex

	private $_sCurrentURI;

	private $_bIsAJAX = false;
	private $_sPattern;
	private $_aAllowedMethods;
	private $_aConditions = array();
	private $_aCallbacks = array();

	private $_sPatternAsRegex;

	private $_aParams = array();
	private $_aParamNames = array();

} // class::Route
