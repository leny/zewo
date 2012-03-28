<?php
/** flatLand! : zewo
 * /classes/routing/error_route.php : main routing classe
 */

namespace Zewo\Routing;

class ErrorRoute {

	public function __construct( $iCode, $aCallbacks, $bIsAJAX = false ) {
		$this->_iCode = $iCode;
		foreach( $aCallbacks as $cCallback ) {
			if( !is_callable( $cCallback ) )
				throw new InvalidArgumentException( "A error middleware must be callable !" );
			$this->_aCallbacks[] = $cCallback;
		}
		$this->_bIsAJAX = $bIsAJAX;
	} // __construct

	public function match( $iCode ) {
		return ( $this->_iCode == $iCode );
	} // match

	public function exec() {
		foreach( $this->_aCallbacks as $cCallback )
			call_user_func_array( $cCallback, array_merge( array( $this->_iCode, $_SERVER[ 'REQUEST_URI' ] ), func_get_args() ) );
	} // exec

	private $_bIsAJAX = false;
	private $_iCode;
	private $_aCallbacks = array();

} // class:ErrorRoute
