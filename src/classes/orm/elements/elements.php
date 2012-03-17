<?php
/** flatLand! : zewo
 * /classes/orm/elements/elements.php
 */

namespace Zewo\ORM\Elements;

class Elements extends \Zewo\Tools\Cached implements \Iterator, \Countable, \ArrayAccess {

	public function __get( $sName ) {
		switch( $sName ) {
			case 'first':
				return $this->_getAt( 0 );
				break;

			case 'last':
				return $this->_getAt( $this->size - 1 );
				break;

			case 'length':
			case 'size':
				return sizeof( $this->_aElements );
				break;

			case 'page':
				return $this->_iCurrentPage;
				break;

			case 'pages':
				return floor( $this->size / $this->pageSize );
				break;

			case 'pageSize':
				return $this->_iPageSize;
				break;

			default:
				break;
		}
	} // __get

	public function __set( $sName, $mValue ) {
		switch( $sName ) {
			case 'pageSize':
				$this->_iPageSize = intval( $mValue ) ?: 10;
				break;

			case 'page':
				$this->getPage( intval( $mValue ) ?: 1 );
				break;

			default:
				break;
		}
	} // __set

	public function __construct( $sTargetClass, $sQuery ) {
		$this->_sTargetClass = 'namespace\\' . $sTargetClass;
		if( !$this->_getFromCache( $this->_getCacheKey( $sQuery ) ) )
			$this->_load( $sQuery );
		return $this;
	} // __construct

	public function __toString() {
		return $this->_jsonize();
	} // __toString

	public function isEmpty() {
		return ( $this->size === 0 );
	} // isEmpty

	public function loadAll() {
		if( !$this->_bAllLoaded )
			$this->_loadAll();
		return $this;
	} // loadAll

	public function saveAll() {
		$bOperation = true;
		for( $i=0; $i < $this->size; $i++ )
			$bOperation = $bOperation && call_user_func_array( array( $this->_getAt( $i ), 'save' ), func_get_args() );
		return $bOperation;
	} // saveAll

	public function get( $iIndex ) {
		return $this->_getAt( $iIndex, true );
	} // get

	public function getPage( $iIndex ) {
		$this->_paginate();
		$this->_aElements = array_slice( $this->_aPagesElements , ( ( $iIndex - 1 ) * $this->pageSize ), $this->pageSize);
		return $this;
	} // getPage

	public function filter( $mFilter, $mFilterValue=null ) {
		if( is_null( $mFilterValue ) && !is_array( $mFilter ) )
			throw new \InvalidArgumentException( "If filter is alone, it must be an Array !" );
		else if( !is_null( $mFilterValue ) && !is_array( $mFilter ) )
			$mFilter = array( $mFilter => $mFilterValue );
		$this->_aSavedStateOfElements[] = $this->_aElements;
		$this->_bActiveFilter = true;
		$aKeepedElements = array();
		for( $i=0; $i < $this->size; $i++ ) {
			$bKeep = true;
			foreach( $mFilter as $sProperty => $mValue ) {
				if( $this->_getAt( $i )->getStructure()->getColumn( $sProperty )->isForeign() ) {
					$sExternalProperty = $this->_getAt( $i )->getStructure()->getColumn( $sProperty )->foreignColumn->name;
					$bKeep = $bKeep && ( $this->_getAt( $i )->$sProperty->$sExternalProperty === $mValue->$sExternalProperty );
				} else
					$bKeep = $bKeep && ( $this->_getAt( $i )->$sProperty === $mValue );
			}
			if( $bKeep )
				$aKeepedElements[] = $this->_getAt( $i );
		}
		$this->_aElements = $aKeepedElements;
		$this->_paginate( true );
		return $this;
	} // filter

	public function end() {
		if( $this->_bActiveFilter ) {
			$this->_aElements = array_pop( $this->_aSavedStateOfElements );
			$this->_paginate( true );
			if( !sizeof( $this->_aSavedStateOfElements ) )
				$this->_bActiveFilter = false;
		}
		return $this;
	} // end

	// -- implements:Iterator
	public function rewind() { $this->_iPosition = 0; }
    public function current() { return $this->_getAt( $this->_iPosition, true ); }
    public function key() { return $this->_iPosition; }
    public function next() { ++$this->_iPosition; }
    public function valid() { return isset( $this->_aElements[ $this->_iPosition ] ); }

    // -- implements:ArrayAccess
	public function offsetSet( $iOffset, $mValue ) { $this->_aElements[ $iOffset ] = $mValue; }
    public function offsetExists( $iOffset ) { return isset( $this->_aElements[ $iOffset ] ); }
    public function offsetUnset( $iOffset ) { unset( $this->_aElements[ $iOffset ] ); }
    public function offsetGet( $iOffset ) { return $this->_getAt( $iOffset, true ); }

    // -- implements:Countable
	public function count() { return $this->size; }

	protected function _load( $sQuery ) {
		$this->_sLoadQuery = $sQuery;
		$this->_aElements = \Zewo\Zewo::getInstance()->db->query( $this->_sLoadQuery );
		$this->_storeInCache( $this->_getCacheKey( $this->_sLoadQuery ) );
    	return $this;
	} // _load

	protected function _loadAll() {
		for( $i=0; $i < $this->size; $i++ )
			$this->_getAt( $i );
		$this->_storeInCache( $this->_getCacheKey( $this->_sLoadQuery ) );
		$this->_bAllLoaded = true;
	} // _loadAll

	protected function _getAt( $iIndex, $bThenStore=false ) {
		if( is_array( $this->_aElements[ $iIndex ] ) )
			$this->_aElements[ $iIndex ] = new $this->_sTargetClass( $this->_aElements[ $iIndex ] );
		if( $bThenStore )
			$this->_storeInCache( $this->_getCacheKey( $this->_sLoadQuery ) );
		return $this->_aElements[ $iIndex ];
	} // _getAt

	protected function _jsonize() {
		if( $this->isEmpty() ) return '[]';
		$this->loadAll();
		return '[' . implode( ',', array_map( 'strval', $this->_aElements ) ) . ']';
	} // _jsonize

	protected function _paginate( $bForce = false ) {
		if( $bForce || is_null( $this->_aPagesElements ) )
			$this->_aPagesElements = $this->_aElements;
	} // _paginate

	protected $_iPosition = 0;

	protected $_sLoadQuery;
	protected $_sTargetClass;

	protected $_aElements;
	protected $_aSavedStateOfElements = array();
	protected $_aPagesElements;
	protected $_bActiveFilter = false;

	protected $_bAllLoaded = false;

	protected $_sCacheKey;

	protected $_iPageSize = 10;
	protected $_iCurrentPage = 1;

	private function _getCacheKey( $sQuery ) {
		if( is_null( $this->_sCacheKey ) )
			$this->_setCacheKey( md5( $sQuery ) );
		return $this->_sCacheKey;
	} // _getCacheKey

} // class::Elements
