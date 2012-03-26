<?php
/** flatLand! : zewo
 * /classes/orm/elements/element.php
 */

namespace Zewo\ORM\Elements;

abstract class Element extends \Zewo\Tools\Cached implements \ArrayAccess {

	const FORCE_SAVE = 1;
	const RECURSIVE_SAVE = 2;

	public function __get( $sName ) {
		if( in_array( $sName, array_keys( $this->_oStructure->columns ) ) ) {
			if( $this->_oStructure->getColumn( $sName )->isForeign() )
				return $this->_getSubClass( $this->_oStructure->getColumn( $sName ) );
			else
				return isset( $this->_aColumnsData[ $sName ] ) ? $this->_aColumnsData[ $sName ] : null;
		} elseif( in_array( $sName, array_keys( $this->_aDynamicData ) ) )
			return $this->_aDynamicData[$sName];
		else
			throw new \InvalidArgumentException( 'The property "' . $sName . '" doesn\'t exists in ' . get_called_class() . '.' );
	} // __get

	public function __set( $sName, $mValue ) {
		if( in_array( $sName, array_keys( $this->_oStructure->columns ) ) ) {
			if( $this->_oStructure->getColumn( $sName )->isForeign() ) {
				if( is_null( $mValue ) ) {
					if( !$this->_oStructure->getColumn( $sName )->isNullable() )
						throw new \UnexpectedValueException( 'The property "' . $sName . '" of "' . get_called_class() . '" can\'t be nullable !' );
					$this->_aColumnsData[$sName] = null;
				} else {
					$sForeignClassName = \Zewo\Zewo::getInstance()->utils->convertor->fromTableNameToClassName( $this->_oStructure->getColumn( $sName )->foreignTable->table );
					if( !is_a( $mValue, $sForeignClassName ) ) {
						$sForeignColumn = $this->_oStructure->getColumn( $sName )->foreignColumn->name;
						$mValue = new $sForeignClassName( array( $sForeignColumn => $mValue ) );
						if( $mValue->isNew() )
							throw new \UnexpectedValueException( 'The property "' . $sName . '" of "' . get_called_class() . '" must be an instance of "' . $sForeignClassName . '". The convertor has failed to create an instance of "' . $sForeignClassName . '" with given value.' );
					}
					if( $mValue->getStructure()->table == $this->_oStructure->getColumn( $sName )->foreignTable->table )
						$this->_setSubClass($sName, $mValue);
				}
			} elseif( $this->_oStructure->isPrimary( $sName ) )
				throw new \UnexpectedValueException( 'You can\'t set a new value to "' . $sName . '" : column is a primary key of table "' . $this->_oStructure->table . '" !' );
			else
				$this->_aColumnsData[$sName] = $mValue;
		} else
			$this->_aDynamicData[ $sName ] = $mValue;
	} // __set

	public function __construct( $sTable, $mQuery ) {
		$this->_oStructure = new \Zewo\ORM\Structure\Table( \Zewo\Zewo::getInstance()->db->currentDatabase, $sTable );
		if( is_null( $mQuery ) )
			return $this->_reset();
		$this->_mOriginalQuery = $mQuery;
		if( !$this->_getFromCache( $this->_getCacheKey( $mQuery ) ) )
			$this->_load( $mQuery );
		return $this;
	} // __construct

	public function save() {
		$bForce = in_array( Element::FORCE_SAVE, func_get_args() );
		$bRecursive = in_array( Element::RECURSIVE_SAVE, func_get_args() );
		$bOperation = true;
		if( $bRecursive ) {
			foreach( $this->_oStructure->columns as $sColumnName => $oColumn ) {
				if( $oColumn->isForeign() && isset( $this->_aSubClassesData[ $sColumnName ] ) && is_subclass_of( $this->$sColumnName, \Zewo\Zewo::getInstance()->config->get( 'orm.baseClass' ) ) ) {
					if( !( $oColumn->isNullable() && $this->_aSubClassesData[ $sColumnName ]->isNull() ) )
						$bOperation = $bOperation && call_user_func_array( array( $this->_aSubClassesData[ $sColumnName ], 'save' ), func_get_args() );
				} elseif( $oColumn->isForeign() && $bForce ) {
					$oInit = $this->$sColumnName;
					if( !( $oColumn->isNullable() && ( is_null( $this->_aSubClassesData[ $sColumnName ] ) || $this->_aSubClassesData[ $sColumnName ]->isNull() ) ) )
						$bOperation = $bOperation && call_user_func_array( array( $this->_aSubClassesData[ $sColumnName ], 'save' ), func_get_args() );
				}
			}
		}
		return $bOperation && $this->_save( $bForce );
	} // save

	public function load( $mQuery ) {
		return $this->_load( $mQuery );
	} // load

	public function reload() {
		$this->_reset();
		return $this->_load( $this->_mOriginalQuery );
	} // reload

	public function delete() {
		return $this->_delete();
	} // delete

	public function clear() {
		return $this->_reset();
	} // clear

	public function reset() {
		return $this->_reset();
	} // clear

	public function getStructure() {
		return $this->_oStructure;
	} // getStructure

	public function __toString() {
		return $this->_jsonize();
	} // __toString

	public function toJSON() {
		return $this->_jsonize();
	} // toJSON

	public function isNew() {
		return $this->_bNew;
	} // isNew

	public function isNull() {
		return $this->_bNew && empty( $this->_aColumnsData );
	} // isNull

	public static function get( $sQuery, $bFromCache = true ) {
		return new \Zewo\ORM\Elements\Elements( get_called_class(), $sQuery, $bFromCache );
	} // get

	public static function getAll( $bFromCache = true ) {
		// build query
		$oTable = new \Zewo\ORM\Structure\Table( \Zewo\Zewo::getInstance()->db->currentDatabase, \Zewo\Zewo::getInstance()->utils->convertor->fromClassNameToTableName( get_called_class() ) );
		$aPrimary = array();
		if( $oTable->hasMultiplePrimary() )
			foreach( $oTable->primary as $oColumn )
				$aPrimary[] = $oColumn->name;
		else
			$aPrimary[] = $oTable->primary->name;
		return self::get( "SELECT " . implode( ',', $aPrimary ) . " FROM " . $oTable->name, $bFromCache );
	} // getAll

	// - implements:ArrayAccess
	public function offsetSet( $mOffset, $mValue ) { $this->$mOffset = $mValue; }
    public function offsetExists( $mOffset ) { return isset( $this->$mOffset ); }
    public function offsetUnset( $mOffset ) { $this->$mOffset = null; }
    public function offsetGet( $mOffset ) { return $this->$mOffset; }

    protected function _load( $mQuery ) {
    	// building SQL request
    	$this->_sLoadQuery  = "SELECT SQL_SMALL_RESULT * FROM `" . $this->_oStructure->table . "`";
    	if( is_array( $mQuery ) && sizeof( $mQuery ) ) {
    		$aSearchClause = array();
    		foreach( $mQuery as $sKey => $mValue ) {
    			$oColumn = $this->_oStructure->getColumn( $sKey );
    			if( $oColumn->isForeign() ) {
    				if( !is_subclass_of( $mValue, 'Element' ) )
    					throw new \UnexpectedValueException( $sKey . " must be a subclass of Element !" );
    				$sForeignProperty = $oColumn->foreignColumn->name;
    				$aSearchClause[] = '`' . $this->_oStructure->table . '`.`' . $sKey."` = " . \Zewo\Zewo::getInstance()->utils->convertor->toDB( $mValue->$sForeignProperty, $oColumn ) . "";
    			} else
    				$aSearchClause[] = '`' . $this->_oStructure->table . '`.`' . $sKey."` = " . \Zewo\Zewo::getInstance()->utils->convertor->toDB( $mValue, $oColumn ) . "";
    		}
    		$this->_sLoadQuery .= " WHERE " . implode( ' AND ', $aSearchClause );
    	} else {
    		if( $this->_oStructure->hasMultiplePrimary() ) {
    			trigger_error( 'Table "' . $this->_oStructure->table . '" has a multiple primary key. You must pass array of values to load "' . get_called_class() . '".', E_USER_NOTICE );
    			return $this->_reset();
    		}
    		$this->_sLoadQuery .= " WHERE `" . $this->_oStructure->table . "`.`" . $this->_oStructure->primary->name . "` = " . \Zewo\Zewo::getInstance()->utils->convertor->toDB( $mQuery, $this->_oStructure->primary );
    	}
    	$this->_sLoadQuery .= " LIMIT 1";
    	// parsing results
    	$aData = \Zewo\Zewo::getInstance()->db->queryOne( $this->_sLoadQuery );
    	$this->_aConvertedSQLData = array();
		$this->_aColumnsData = array();
    	if( !is_null( $aData ) ) {
    		$this->_aSQLData = $aData;
    		foreach( $this->_aSQLData as $sKey => $mValue ) {
    			$mConvertedValue = \Zewo\Zewo::getInstance()->utils->convertor->fromDB( $mValue, $this->_oStructure->getColumn( $sKey ) );
    			$this->_aConvertedSQLData[ $sKey ] = $mConvertedValue;
				$this->_aColumnsData[ $sKey ] = $mConvertedValue;
    		}
    		$this->_bNew = false;
    	} else {
    		$this->_reset();
    		if( is_array( $mQuery ) && sizeof( $mQuery ) ) {
    			foreach( $mQuery as $sKey => $mValue ) {
    				if( !is_null( $mValue ) ) {
    					if( $this->_oStructure->getColumn( $sKey )->isForeign() ) {
    						if( $this->_oStructure->getColumn( $sKey )->foreignTable->hasMultiplePrimary() ) {
    							foreach( $this->_oStructure->getColumn( $sKey )->foreignTable->primary as $oColumn ) {
    								if( $sKey == $oColumn->name ) {
    									$sForeignColumn = $oColumn->name;
    									break;
    								}
    							}
    						} else
    							$sForeignColumn = $this->_oStructure->getColumn( $sKey )->foreignColumn->name;
    						$this->_aColumnsData[ $sKey ] = $mValue->$sForeignColumn;
    					} else
    						$this->_aColumnsData[ $sKey ] = $mValue;
    				}
    			}
    		} else {
				if( $this->_oStructure->primary->isForeign() ) {
					$sForeignColumn = $this->_oStructure->primary->name;
					$this->_aColumnsData[ $this->_oStructure->primary->name ] = $mQuery->$sForeignColumn;
				} else
					$this->_aColumnsData[ $this->_oStructure->primary->name ] = $mQuery;
    		}
    	}
    	$this->_storeInCache( $this->_getCacheKey() );
    	return $this;
    } // _load

    protected function _save( $bForce = false ) {
		if( !$bForce && !$this->_hasChanges() )
			return true;
		$aKeys = array();
		foreach($this->_oStructure->columns as $oColumn)
			$aKeys[$oColumn->name] = "`" . $oColumn->name . "` = " . \Zewo\Zewo::getInstance()->utils->convertor->toDB( isset( $this->_aColumnsData[$oColumn->name] ) ? $this->_aColumnsData[$oColumn->name] : null , $oColumn );
		if( $this->_bNew ) {
			$this->_sSaveQuery = "INSERT INTO " . $this->_oStructure->table . " SET " . implode( ', ', $aKeys );
		} else {
			$this->_sSaveQuery = "UPDATE " . $this->_oStructure->table . " SET " . implode( ', ', $aKeys ) . " WHERE " . $this->_getWhereClause();
		}
		$bOperation = \Zewo\Zewo::getInstance()->db->query( $this->_sSaveQuery );
		if( $bOperation ) {
			if( $this->_bNew && !$this->_oStructure->hasMultiplePrimary() )
				$this->_aColumnsData[ $this->_oStructure->primary->name ] = \Zewo\Zewo::getInstance()->db->lastInsertedID;
			if( $this->_bNew )
				$this->_bNew = false;
			$this->_storeInCache( $this->_getCacheKey() );
		}
    	return $bOperation;
    } // _save

    protected function _delete() {
		$this->_sDeleteQuery = "DELETE FROM `" . $this->_sTable . "` WHERE " . $this->_getWhereClause();
		$bOperation = \Zewo\Zewo::getInstance()->db->query( $this->_sDeleteQuery );
		if( $bOperation ) {
			$this->_removeFromCache( $this->_getCacheKey() );
			$this->_reset();
		}
		return $bOperation;
    } // _delete

	protected function _reset() {
		$this->_bNew = true;
		foreach( $this->_oStructure->columns as $oColumn )
			$this->_aColumnsData[ $oColumn->name ] = null;
		return $this;
	} // _reset

	protected function _getWhereClause() {
		if( $this->_oStructure->hasMultiplePrimary() ) {
			$aWhereClause = array();
			foreach( $this->_oStructure->primary as $oColumn ) {
				$sProperty = $oColumn->name;
				if( $oColumn->isForeign() ) {
					$sForeignColumn = $oColumn->foreignColumn;
					$aWhereClause[] = '`' . $this->_oStructure->table . '`.`' . $sColumn."` = " . \Zewo\Zewo::getInstance()->utils->convertor->toDB( $this->$sProperty->$sForeignColumn, $oColumn ) . "";
				} else {
					$aWhereClause[] = '`' . $this->_oStructure->table . '`.`' . $sColumn."` = " . \Zewo\Zewo::getInstance()->utils->convertor->toDB( $this->$sProperty, $oColumn ) . "";
				}
			}
			return implode( ' AND ', $aWhereClause ) . " ";
		} else
			return $this->_oStructure->primary->name . "` = " . \Zewo\Zewo::getInstance()->utils->convertor->toDB( $this->_aColumnsData[ $this->_oStructure->primary->name ], $this->_oStructure->primary );
	} // _getWhereClause

	protected function _getSubClass( \Zewo\ORM\Structure\Column $oColumn ) {
		if( !$oColumn->isForeign() )
			throw new \LogicException( 'This should never append : calling internal _getSubClass method for a property not foreigned. Post issue on github, please. Thanks.' );
		if( $oColumn->isNullable() && is_null( $this->_aColumnsData[ $oColumn->name ] ) )
			return null;
		if( !isset( $this->_aSubClassesData[ $oColumn->name ] ) ) {
			$sClassName = \Zewo\Zewo::getInstance()->utils->convertor->fromTableNameToClassName( $oColumn->foreignTable->table );
			$this->_aSubClassesData[ $oColumn->name ] = new $sClassName( array( $oColumn->foreignColumn->name => $this->_aColumnsData[ $oColumn->name ] ) );
		}
		return $this->_aSubClassesData[ $oColumn->name ];
	} // _getSubClass

	protected function _hasChanges() {
		$aValues = $aVerify = array();
		foreach( $this->_oStructure->columns as $oColumn ) {
			$aVerify[$oColumn->name] = \Zewo\Zewo::getInstance()->utils->convertor->toDB( isset( $this->_aConvertedSQLData[$oColumn->name] ) ? $this->_aConvertedSQLData[$oColumn->name] : null, $oColumn, true );
			$aValues[$oColumn->name] = \Zewo\Zewo::getInstance()->utils->convertor->toDB( isset( $this->_aColumnsData[$oColumn->name] ) ? $this->_aColumnsData[$oColumn->name] : null, $oColumn );
		}
		return $aValues !== $aVerify;
	} // _hasChanges

	protected function _jsonize() {
		$oExport = new stdClass();
		foreach( array_keys( $this->_aColumnsData ) as $sProperty )
			$oExport->$sProperty = ( gettype( $this->$sProperty ) == 'object' && is_a( $this->$sProperty, 'Element' ) ) ? json_decode( $this->$sProperty->toJSON() ) : $this->$sProperty;
		return json_encode( $oExport );
	} // _jsonize

	protected $_bNew = true;

	protected $_aColumnsData = array();
	protected $_aDynamicData = array();
	protected $_aSubClassesData = array();

	protected $_sCacheKey;

	protected $_mOriginalQuery;

	protected $_oStructure;

	private function _getCacheKey( $mParams = null ) {
		if( !is_null( $this->_sCacheKey ) ) {
			if( is_null( $mParams ) ) {
				if( $this->_oStructure->hasMultiplePrimary() ) {
					$mParams = array();
					foreach ( $this->_oStructure->primary as $oColumn)
						$mParams[] = $this->_aColumnsData[ $oColumn->name ];
				} else
					$mParams = $this->_aColumnsData[ $this->_oStructure->primary->name ];
			}
			$sCacheKey = is_array( $mParams ) ? http_build_query( $mParams ) : strval( $mParams );
			$this->_setCacheKey( $sCacheKey );
		}
		return $this->_sCacheKey;
	} // _getCacheKey

	private $_sLoadQuery;
	private $_sSaveQuery;
	private $_sDeleteQuery;

	private $_aSQLData;
	private $_aConvertedSQLData;

} // class::Element
