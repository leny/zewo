<?php
/** flatLand! : zewo
 * /classes/utils/globals.php
 */

namespace Zewo\Utils;

class Globals extends \Zewo\Tools\Singleton {

	public function has( $sKey, $sTable = 'get' ) {
		switch( $sTable ) {
			case 'get':
				$aTable = $_GET;
				break;
			case 'post':
				$aTable = $_POST;
				break;
			case 'session':
				$aTable = $_SESSION;
				break;
			case 'cookie':
				$aTable = $_COOKIE;
				break;
			case 'files':
				$aTable = $_FILES;
				break;
			case 'data':
				$aTable = $this->_aData;
				break;
			default:
				return false;
				break;
		}
		return isset( $aTable[ $sKey ] );
	} // hasGet

	public function data( $sKey = null, $mDefault = null ) {
		return $this->_globals( $sKey, $this->_aData, $mDefault, true );
	} // data

	public function get( $sKey = null, $mDefault = null ) {
		return $this->_globals( $sKey, $_GET, $mDefault );
	} // get

	public function post( $sKey = null, $mDefault = null ) {
		return $this->_globals( $sKey, $_POST, $mDefault );
	} // post

	public function session( $sKey = null, $mDefault = null ) {
		return $this->_globals( $sKey, $_SESSION, $mDefault, true );
	} // session

	public function cookie( $sKey = null, $mDefault = null ) {
		if( !is_null( $mDefault ) )
			setcookie( $sKey, strval( $mDefault ), ( time() + 31536000 ), '/', str_replace( 'http://', '', $this->server( 'http_host' ) ) );
		return $this->_globals( $sKey, $_COOKIE, $mDefault );
	} // cookie

	public function server( $sKey = null, $mDefault = null ) {
		return $this->_globals( $sKey ? strtoupper( $sKey ) : null, $_SERVER, $mDefault );
	} // cookie

	public function files( $sKey = null, $mDefault = null ) {
		return $this->_globals( $sKey, $_FILES, $mDefault );
	} // cookie

	public function request( $sKey = null, $mDefault = null ) {
		$sMethod = strtolower( server( 'request_method' ) ) ?: 'get';
		return $sMethod == 'post' ? $this->_globals( $sKey, $_POST, $mDefault ) : globals( $sKey, $_GET, $mDefault );
	} // request

	private function _globals( $sKey = null, &$aTable, $mDefault = null, $bAssign = false) {
		if( is_null( $sKey ) )
			return $aTable;
		if( $bAssign && !is_null( $mDefault ) ) {
			$aTable[ $sKey ] = $mDefault;
			return $aTable[ $sKey ];
		}
		if( !empty( $aTable[$sKey] ) ) {
			if( is_string( $aTable[ $sKey ] ) )
				return stripslashes( $aTable[ $sKey ] );
			else
				return $aTable[ $sKey ];
		} else
			return $mDefault;
	} // globals

	private $_aData = array();

} // class::Globals
