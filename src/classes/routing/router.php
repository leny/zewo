<?php
/** flatLand! : zewo
 * /classes/routing/router.php : main routing classe
 */

namespace Zewo\Routing;

class Router extends \Zewo\Tools\Singleton {

	const METHOD_GET = 'get';
	const METHOD_POST = 'post';

	public function get() {
		$this->_registerRoute( func_get_args(), array( self::METHOD_GET ) );
	} // get

	public function post() {
		$this->_registerRoute( func_get_args(), array( self::METHOD_POST ) );
	} // post

	public function map() {
		$this->_registerRoute( func_get_args(), array( self::METHOD_GET, self::METHOD_POST ) );
	} // map

	public function error() {
		// TODO : first param is http code, following is like the rest
	} // error

	public function redirect( $sPath ) {
		// TODO : redirect to path
	} // redirect

	public function ajaxGet() {
		$this->_registerRoute( func_get_args(), array( self::METHOD_GET ), true );
	} // ajaxGet

	public function ajaxPost() {
		$this->_registerRoute( func_get_args(), array( self::METHOD_POST ), true );
	} // ajaxPost

	public function ajax() {
		$this->_registerRoute( func_get_args(), array( self::METHOD_GET, self::METHOD_POST ), true );
	} // ajax

	public function run() {
		// TODO : find the road that match the current url and launch it
		$this->_sCurrentURI = $_SERVER[ 'REQUEST_URI' ];
		$bHasMatched = false;
		foreach( $this->_aRegisteredRoutes as $oRoute ) {
			if( $oRoute->match( $this->_sCurrentURI ) ) {
				$bHasMatched = true;
				$oRoute->exec();
				break;
			}
		}
		if( !$bHasMatched )
			$this->callError( 404 );
	} // run

	public function callError( $iCode ) {
		// TODO : look for Error Matching, else, display classical error
		die( 'Error ' . $iCode );
	} // callError

	private function _registerRoute( $aParams, $aMethods, $bIsAJAX = false ) {
		$aCallbacks = $aParams;
		$sPattern = array_shift( $aCallbacks );
		$this->_aRegisteredRoutes[] = new \Zewo\Routing\Route( $sPattern, $aMethods, $aCallbacks, $bIsAJAX );
	} // _registerRoute

	private $_sCurrentURI;
	private $_aRegisteredRoutes = array();

} // class::Router
