<?php
/** flatLand! : zewo
 * /classes/orm/structure/column.php
 */

namespace Zewo\ORM\Structure;

final class Column {

	public function __get( $sName ) {
		switch( $sName ) {
			case 'table':
				return $this->_sTable;
				break;
			case 'name':
				return $this->_sName;
				break;
			case 'type':
				return $this->_sType;
				break;
			case 'size':
				return $this->_iSize;
				break;
			case 'default':
				return $this->_sDefault;
				break;
			case 'foreignTable':
				return $this->_oForeignTable;
				break;
			case 'foreignColumn':
				return $this->_oForeignColumn;
				break;
			case 'possibleValues':
				return $this->_aPossibleValues;
				break;
		}
	} // __get

	public function __construct( $aSchemaInfos, \Zewo\ORM\Structure\Table $oTable ) {
		$this->_sTable = $oTable->table;
		$this->_sName = $aSchemaInfos['COLUMN_NAME'];
		preg_match("/([[:alpha:]]+)([[:alnum:][:punct:]]*)[[:space:]]*(.*)/", $aSchemaInfos['COLUMN_TYPE'], $aTypeInfos);
		$this->_sType = $aTypeInfos[1];
		if( in_array( $this->_sType, $this->_aNumericSizedTypes ) ) {
			$this->_iSize = intval( str_replace( array( '(', ')' ), '', $aTypeInfos[2] ) );
			if( $this->_sType == 'tinyint' && $this->_iSize == 1 ) {
				$this->_sType = 'boolean';
				$this->_iSize = null;
			} else
				$this->_bSigned = $aTypeInfos[3] !== 'unsigned';
		} else {
			if( $this->_sType == 'enum' || $this->_sType == 'set' ) {
				$this->_aPossibleValues = json_decode( '["' . substr( str_replace("','", '","', $aTypeInfos[2]) , 2, -2) . '"]', true );
			} else
				$this->_iSize = $aTypeInfos[2];
		}
		$this->_sDefault = $aSchemaInfos['COLUMN_DEFAULT'];
		$this->_bPrimary = $aSchemaInfos['CONSTRAINT_NAME'] === 'PRIMARY';
		$this->_bNullable = $aSchemaInfos['IS_NULLABLE'] === 'YES';
		if( $aSchemaInfos['REFERENCED_TABLE_NAME'] && $aSchemaInfos['REFERENCED_COLUMN_NAME'] ) {
			$this->_oForeignTable = $aSchemaInfos['REFERENCED_TABLE_NAME'] == $this->_sTable ? $oTable : new \Zewo\ORM\Structure\Table( $oTable->base, $aSchemaInfos['REFERENCED_TABLE_NAME'] );
			$this->_oForeignColumn = $this->_oForeignTable->getColumn( $aSchemaInfos['REFERENCED_COLUMN_NAME'] );
		}
	} // __construct

	public function isSigned() {
		return $this->_bSigned;
	} // isSigned

	public function isNullable() {
		return $this->_bNullable;
	} // isNullable

	public function isForeign() {
		return !is_null( $this->_oForeignTable );
	} // isNullable

	public function isPrimary() {
		return $this->_bPrimary;
	} // isPrimary

	private $_sTable;
	private $_sName;
	private $_sType;
	private $_iSize;
	private $_bSigned;
	private $_sDefault;
	private $_bPrimary;
	private $_bNullable;

	private $_aPossibleValues = array();

	private $_oForeignTable = null; // Table
	private $_oForeignColumn = null; // Column

	private $_aNumericSizedTypes = array( 'tinyint', 'smallint', 'mediumint', 'bigint', 'int', 'varchar' );

} // class::Column
