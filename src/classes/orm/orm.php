<?php
/** flatLand! : zewo
 * /classes/orm/orm.php
 */

namespace Zewo\ORM;

class ORM extends \Zewo\Tools\Singleton {

	public static function createClass( $sClassName ) {
		$oZewo = \Zewo\Zewo::getInstance();
		$oZewo->orm->generateClass( $sClassName, $oZewo->utils->convertor->fromClassNameToTableName( $sClassName ) );
	} // createClass

	public function generateClass( $sClassName, $sTableName ) {
		eval( 'namespace { class ' . $sClassName . ' extends ' . $this->_oZewo->config->get( 'orm.baseClass' ) . ' { public function __construct( $mID=null ) { return parent::__construct( "' . $sTableName . '", $mID ); } } }' );
		return $sClassName;
	} // generateClass

	public function generateClasses() {
		$aAllTables = $this->_oZewo->db->query( "SELECT TABLE_NAME FROM TABLES WHERE TABLE_SCHEMA = '" . $this->_oZewo->db->currentDatabase . "'", 'schema' );
		if( !is_array( $aAllTables ) || !sizeof( $aAllTables ) )
			return false;
		$aClasses = array();
		foreach( $aAllTables as $aTableRow )
			$aClasses[] = $this->generateClass( $this->_oZewo->utils->convertor->fromTableNameToClassName( $aTableRow['TABLE_NAME'] ), $aTableRow['TABLE_NAME'] );
		return $aClasses;
	} // generateClasses

	protected function __construct() {
		$this->_oZewo = \Zewo\Zewo::getInstance();
		spl_autoload_register( '\Zewo\ORM\ORM::createClass' );
	} // __construct

	private $_oZewo;

} // class::ORM
