<?php
/** flatLand! - zewo
 * /tools/cache/apc.php
 */

namespace Zewo\Tools\Cache;

class APC extends \Zewo\Tools\Singleton implements \Zewo\Tools\Cache\iCache {

	public function get( $sName, &$bSuccess=null ) {
		return apc_fetch( $sName, $bSuccess );
	} // get

	public function set( $sName, $mValue ) {
		return apc_store( $sName, $mValue );
	} // set

	public function delete( $sName ) {
		return apc_delete( $sName );
	} // delete

	public function flush() {
		return apc_clear_cache( "user" );
	} // flush

} // class:APC
