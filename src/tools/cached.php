<?php
/** flatLand! - zewo
 * /tools/cached.php
 */

namespace Zewo\Tools;

abstract class Cached {

	final public function isFromCache() {
		return $this->_bIsFromCache;
	} // isFromCache

	protected function _getCacheKey( $sKey = null ) {
		if( is_null( $this->_sCacheKey ) )
			$this->_sCacheKey = 'zewo_' . \Zewo\Zewo::getInstance()->config->get( 'orm.cacheKey' ) . '_' . strtolower( get_called_class() ) . '_' . ( $sKey ?: substr( md5( time() ), 0, 5 ) );
		return $this->_sCacheKey;
	} // _setCacheKey

	final protected function _getFromCache( $sKey = null ) {
		$oFromCache = \Zewo\Zewo::getInstance()->cache->get( $this->_getCacheKey( $sKey ) );
		if( !$oFromCache )
			return false;
		foreach( get_object_vars( $oFromCache ) as $sName=>$mValue )
			$this->$sName = $mValue;
		$this->_bIsFromCache = true;
		return true;
	} // _getFromCache

	final protected function _storeInCache( $sKey = null ) {
		\Zewo\Zewo::getInstance()->cache->set( $this->_getCacheKey( $sKey ), $this );
	} // _storeInCache

	final protected function _removeFromCache( $sKey = null ) {
		\Zewo\Zewo::getInstance()->cache->delete( $this->_getCacheKey( $sKey ) );
	} // _removeFromCache

	protected $_sCacheKey;

	private $_bIsFromCache = false;

} // class::Cached
