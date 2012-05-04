<?php
/** flatLand! - zewo
 * /tools/cache/cache.php
 */

namespace Zewo\Tools\Cache;

abstract class Cache {

	const APC = 'APC';
	const SESSION = 'SESSION';

	public static function getInstanceOf( $sType ) {
		switch( $sType ) {
			case self::APC:
				return \Zewo\Tools\Cache\APC::getInstance();
				break;
			case self::SESSION:
				return \Zewo\Tools\Cache\SessionCache::getInstance();
				break;
			default:
				throw new \InvalidArgumentException( 'Unknown Cache Type "' . $sType . '" !' );
				break;
		}
	} // getInstanceOf

} // class::Cache

interface iCache {
	public function get( $sName, &$bSuccess );
	public function set( $sName, $mValue );
	public function delete( $sName );
	public function flush();
} // interface:iCache
