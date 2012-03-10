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
		$this->_registerError( func_get_args() );
	} // error

	public function ajaxGet() {
		$this->_registerRoute( func_get_args(), array( self::METHOD_GET ), true );
	} // ajaxGet

	public function ajaxPost() {
		$this->_registerRoute( func_get_args(), array( self::METHOD_POST ), true );
	} // ajaxPost

	public function ajax() {
		$this->_registerRoute( func_get_args(), array( self::METHOD_GET, self::METHOD_POST ), true );
	} // ajax

	public function ajaxError() {
		$this->_registerError( func_get_args(), true );
	} // error

	public function redirect( $sPath ) {
		header( "Location: " . $sPath );
		exit;
	} // redirect

	public function run() {
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
		$bHasMatched = false;
		foreach( $this->_aRegisteredErrorRoutes as $oErrorRoute ) {
			if( $oErrorRoute->match( $iCode ) ) {
				$bHasMatched = true;
				$oErrorRoute->exec();
				break;
			}
		}
		if( !$bHasMatched )
			$this->_defaultErrorRouteHandler( 404 );
		die();
	} // callError

	private function _registerRoute( $aParams, $aMethods, $bIsAJAX = false ) {
		$aCallbacks = $aParams;
		$sPattern = array_shift( $aCallbacks );
		$this->_aRegisteredRoutes[] = new \Zewo\Routing\Route( $sPattern, $aMethods, $aCallbacks, $bIsAJAX );
	} // _registerRoute

	private function _registerError( $aParams, $bIsAJAX = false ) {
		$aCallbacks = $aParams;
		$iErrorCode = array_shift( $aCallbacks );
		$this->_aRegisteredErrorRoutes[] = new \Zewo\Routing\ErrorRoute( $iErrorCode, $aCallbacks, $bIsAJAX );
	} // _registerError

	private function _defaultErrorRouteHandler( $iCode ) {
		switch( $iCode ) {
			case 400: $sCodeDetails = 'Bad Request'; break;
			case 401: $sCodeDetails = 'Unauthorized'; break;
			case 403: $sCodeDetails = 'Forbidden'; break;
			case 404: $sCodeDetails = 'Not Found'; break;
			case 405: $sCodeDetails = 'Method Not Allowed'; break;
			case 406: $sCodeDetails = 'Not Acceptable'; break;
			case 408: $sCodeDetails = 'Request Timeout'; break;
			case 409: $sCodeDetails = 'Conflict'; break;
			case 410: $sCodeDetails = 'Gone'; break;
			case 418: $sCodeDetails = 'I\'m a teapot'; break;
			case 420: $sCodeDetails = 'Enhance Your Calm'; break;
			case 429: $sCodeDetails = 'Bad Request'; break;
		}
		return header( "HTTP/1.0 " . $iCode . ' ' . $sCodeDetails );
	} // _defaultErrorRouteHandler

	private $_sCurrentURI;
	private $_aRegisteredRoutes = array();
	private $_aRegisteredErrorRoutes = array();

	/* TODO : adapt these
		function lock($sLogin, $sPassword, $sWarning="Connexion &eacute;chou&eacute;e : mauvais login et/mot de passe") {
			if (!server('PHP_AUTH_USER')) {
			  header('WWW-Authenticate: Basic realm="Calcium"');
			  header('HTTP/1.0 401 Unauthorized');
			  die($sWarning);
			} else if(server('PHP_AUTH_USER') !== $sLogin || server('PHP_AUTH_PW') !== $sPassword) {
			  header('WWW-Authenticate: Basic realm="CORESystem"');
			  header('HTTP/1.0 401 Unauthorized');
			  die($sWarning);
			}
		} // lock
	*/

} // class::Router
