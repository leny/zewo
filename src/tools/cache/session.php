<?php
/** flatLand! - zewo
 * /tools/cache/apc.php
 */

namespace Zewo\Tools\Cache;

class SessionCache extends \Zewo\Tools\Singleton implements \Zewo\Tools\Cache\iCache {

	public function get( $sName, &$bSuccess=null ) {
		return isset( $_SESSION[ $sName ] ) ? $_SESSION[ $sName ] : null;
	} // get

	public function set( $sName, $mValue ) {
		$_SESSION[ $sName ] = $mValue;
	} // set

	public function delete( $sName ) {
		unset( $_SESSION[ $sName ] );
	} // delete

	public function flush() {
		return true;
	} // flush

} // class:SessionCache
