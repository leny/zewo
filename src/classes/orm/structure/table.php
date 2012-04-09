<?php
/** flatLand! : zewo
 * /classes/orm/structure/table.php
 */

namespace Zewo\ORM\Structure;

final class Table extends \Zewo\Tools\Cached {

	public function __get( $sName ) {
		switch( $sName ) {
			case 'name':
			case 'table':
				return $this->_sTable;
				break;
			case 'base':
			case 'database':
				return $this->_sBase;
				break;
			case 'columns':
				return $this->_aColumns;
				break;
			case 'primary':
				return $this->_mPrimary;
				break;
		}
	} // __get

	public function __construct( $sBase, $sTable ) {
		$this->_sBase = $sBase;
		$this->_sTable = $sTable;
		if( !$this->_getFromCache( $this->_sTable ) )
			if( !$this->_buildTableInfos() )
				throw new \InvalidArgumentException( 'Unknown table "' . $this->_sTable . '" in base "' . $this->_sBase . '" !' );
		return $this;
	} // __construct

	public function getColumn( $sName ) {
		if( !$this->hasColumn( $sName ) )
			return false && trigger_error('Unknown column "' . $sName . '" in table "' . $this->_sTable . '" !', E_USER_WARNING);
		return $this->_aColumns[ $sName ];
	} // getColumn

	public function hasColumn( $sName ) {
		return isset( $this->_aColumns[ $sName ] );
	} // hasColumn

	public function hasMultiplePrimary() {
		return is_array( $this->_mPrimary );
	} // hasMultiplePrimary

	public function isPrimary( $sName ) {
		if( is_array( $this->_mPrimary ) ) {
			foreach($this->_mPrimary as $oPrimaryColumn)
				if( $oPrimaryColumn->name === $sName )
					return true;
		} else
			return $this->_mPrimary->name === $sName;
		return false;
	} // isPrimary

	protected $_sBase;
	protected $_sTable;

	protected $_mPrimary = null;

	protected $_aColumns = array();

	private function _buildTableInfos() {
		$sTableInfosQuery = "SELECT DISTINCT COLUMNS.COLUMN_NAME, COLUMNS.COLUMN_DEFAULT, COLUMNS.IS_NULLABLE, COLUMNS.COLUMN_TYPE, KEY_COLUMN_USAGE.CONSTRAINT_NAME, KEY_COLUMN_USAGE.REFERENCED_TABLE_NAME, KEY_COLUMN_USAGE.REFERENCED_COLUMN_NAME FROM COLUMNS LEFT JOIN KEY_COLUMN_USAGE ON(COLUMNS.COLUMN_NAME = KEY_COLUMN_USAGE.COLUMN_NAME AND KEY_COLUMN_USAGE.TABLE_NAME = COLUMNS.TABLE_NAME AND KEY_COLUMN_USAGE.TABLE_SCHEMA = COLUMNS.TABLE_SCHEMA) WHERE COLUMNS.TABLE_SCHEMA = '" . $this->_sBase . "' AND COLUMNS.TABLE_NAME = '" . $this->_sTable . "' ORDER BY COLUMNS.ORDINAL_POSITION ASC";
		$aTableInfos = \Zewo\Zewo::getInstance()->db->query( $sTableInfosQuery, 'schema' );
		if( !is_array( $aTableInfos ) || !sizeof( $aTableInfos ) )
			return false;
		foreach( $aTableInfos as $aColumnInfo ) {
			$this->_aColumns[ $aColumnInfo[ 'COLUMN_NAME' ] ] = new \Zewo\ORM\Structure\Column( $aColumnInfo, $this );
			if( $aColumnInfo['CONSTRAINT_NAME'] == 'PRIMARY' ) {
				if( is_null( $this->_mPrimary ) ) {
					$this->_mPrimary = $this->_aColumns[ $aColumnInfo['COLUMN_NAME'] ];
				} else {
					if( !is_array( $this->_mPrimary ) )
						$this->_mPrimary = array( $this->_mPrimary );
					$this->_mPrimary[] = $this->_aColumns[ $aColumnInfo['COLUMN_NAME'] ];
				}
			}
		}
		$this->_storeInCache( $this->_sTable );
		return true;
	} // _buildTableInfos

} // class:Table
