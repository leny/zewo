<?php
/** flatLand! - zewo
 * /tools/cached.php
 */

namespace Zewo\Tools;

abstract class Cached {

	final public function isFromCache() {
		return $this->_bIsFromCache;
	} // isFromCache

	protected function _setCacheKey( $sKey = null ) {
		$this->_sCacheKey = 'zewo_' . \Zewo\Zewo::getInstance()->config->get( 'orm.cacheKey' ) . '_' . strtolower( get_called_class() ) . '_' . ( $sKey ?: substr( md5( time() ), 0, 5 ) );
	} // _setCacheKey

	final protected function _getFromCache( $sKey = null ) {
		if( is_null( $sKey ) )
			$this->_setCacheKey();
		$oFromCache = \Zewo\Zewo::getInstance()->cache->get( $this->_sCacheKey );
		if( !$oFromCache )
			return false;
		foreach( get_object_vars( $oFromCache ) as $sName=>$mValue )
			$this->$sName = $mValue;
		$this->_bIsFromCache = true;
		return true;
	} // _getFromCache

	final protected function _storeInCache( $sKey = null ) {
		if( is_null( $sKey ) )
			$this->_setCacheKey();
		\Zewo\Zewo::getInstance()->cache->set( $this->_sCacheKey, $this );
	} // _storeInCache

	final protected function _removeFromCache( $sKey = null ) {
		if( is_null( $sKey ) )
			$this->_setCacheKey();
		\Zewo\Zewo::getInstance()->cache->delete( $this->_sCacheKey );
	} // _removeFromCache

	protected $_sCacheKey;

	private $_bIsFromCache = false;

} // class::Cached
