<?php
/** flatLand! : zewo
 * /classes/db/db.php
 */

namespace Zewo\DB;

class db extends \Zewo\Tools\Singleton {

	public function __get( $sName ) {
		switch( $sName ) {
			case 'currentConnexion':
				return $this->_aConnexions[ $this->_sCurrentConnexionName ];
				break;
			case 'currentConnexionName':
				return $this->_sCurrentConnexionName;
				break;
			case 'connexions':
				return $this->_aConnexions;
				break;
			case 'currentDatabase':
				return $this->_aDatabases[ $this->_sCurrentDatabaseName ];
				break;
			case 'currentDatabaseName':
				return $this->_sCurrentDatabaseName;
				break;
			case 'databases':
				return $this->_aDatabases;
				break;
			case 'loggingStatus':
				return $this->_bEnabledLog;
				break;
			case 'queries':
				return $this->_aQueries;
				break;
			case 'lastQuery':
				return end( $this->_aQueries );
				break;
			case 'lastInsertedID':
				return mysqli_insert_id( $this->currentConnexion );
				break;
		}
	} // __get

	public function __set( $sName, $mValue ) {
		switch( $sName ) {
			case 'loggingStatus':
				$this->_bEnabledLog = $mValue;
				break;
		}
	} // __set

	public function addConnexion( $sName, $sHost, $sLogin, $sPass, $bSelect=false ) {
		$rConnexion = mysqli_connect($sHost, $sLogin, $sPass);
		if( !$rConnexion )
			return false && trigger_error( 'MySQL Connexion Error (' . mysqli_connect_errno() . ' : ' . mysqli_connect_error() . ')', E_USER_ERROR );
		if( isset( $this->_aConnexions[ $sName ] ) )
			trigger_error( 'MySQL Connexion link named "' . $sName . '" has been overwrited.', E_USER_NOTICE );
		$this->_aConnexions[ $sName ] = $rConnexion;
		return $bSelect ? $this->selectConnexion( $sName ) : true;
	} // addConnexion

	public function selectConnexion( $sName ) {
		if( !isset( $this->_aConnexions[ $sName ] ) )
			return false && trigger_error( 'MySQL Connexion link named "' . $sName . '" doesn\'t exists !', E_USER_ERROR );
		$this->_sCurrentConnexionName = $sName;
		return $this->currentConnexion;
	} // selectConnexion

	public function addDatabase( $sName, $sBase, $bSelect=false ) {
		if( isset( $this->_aDatabases[ $sName ] ) )
			trigger_error( 'MySQL Database link named "' . $sName . '" has been overwrited.', E_USER_NOTICE );
		$this->_aDatabases[ $sName ] = $sBase;
		return $bSelect ? $this->selectDatabase( $sName ) : true;
	} // addDatabase

	public function selectDatabase( $sName ) {
		if( !isset( $this->_aDatabases[ $sName ] ) )
			return false && trigger_error( 'MySQL Database link named "' . $sName . '" doesn\'t exists !', E_USER_ERROR );
		$bOperation = mysqli_select_db( $this->currentConnexion, $this->_aDatabases[ $sName ] );
		if( !$bOperation )
			return false && trigger_error( 'MySQL Database "' . $sBase . '" Selection Error', E_USER_ERROR );
		$this->_sCurrentDatabaseName = $sName;
		return $this->currentDatabase;
	} // selectConnexion

	public function closeCurrentConnexion() { /* TODO */ } // closeCurrentConnexion
	public function close( $sName ) { /* TODO */ } // close

	public function query( $sQuery, $sDatabaseName=null, $sConnexionName=null ) {
		$bTmpChange = false;
		if( !is_null( $sDatabaseName ) ) {
			$sOldDatabaseName = $this->currentDatabaseName;
			$this->selectDatabase( $sDatabaseName );
			$bTmpChange = true;
		}
		if( !is_null( $sConnexionName ) ) {
			$sOldConnexionName = $this->currentConnexionName;
			$this->selectConnexion( $sConnexionName );
			$bTmpChange = true;
		}
		$mOperationResults = $this->_query( $sQuery );
		if( $bTmpChange ) {
			if( isset( $sOldConnexionName ) )
				$this->selectConnexion( $sOldConnexionName );
			if( isset( $sOldDatabaseName ) )
				$this->selectDatabase( $sOldDatabaseName );
		}
		return $mOperationResults;
	} // query

	public function queryOne( $sQuery, $sDatabaseName=null, $sConnexionName=null ) {
		$aResult = $this->query( $sQuery, $sDatabaseName, $sConnexionName );
		if( !is_array( $aResult ) || sizeof( $aResult ) == 0 )
			return null;
		return $aResult[ 0 ];
	} // queryOne

	public function queryField( $sField, $sQuery, $sDatabaseName=null, $sConnexionName=null ) {
		$aResult = $this->query( $sQuery, $sDatabaseName, $sConnexionName );
		if( !is_array( $aResult ) || sizeof( $aResult ) == 0 )
			return null;
		return isset( $aResult[ 0 ][ $sField ] ) ? $aResult[ 0 ][ $sField ] : null;
	} // queryField

	// --- protected methods

	protected function _query( $sQuery ) {
		if( $this->loggingStatus )
			$fStartTime = microtime( true );
		$mData = mysqli_query( $this->currentConnexion, $sQuery );
		if( $this->loggingStatus ) {
			$fEndTime = microtime( true );
			$this->_logQuery( $sQuery, $mData, $fStartTime, $fEndTime );
		}
		if( gettype( $mData ) == 'boolean' ) {
			if( mysqli_errno( $this->currentConnexion ) )
				return false && trigger_error( $this->_getErrorMessage(), E_USER_ERROR );
			return true;
		}
		$aArray = array();
		while( $aCurrent = mysqli_fetch_assoc( $mData ) )
			$aArray[] = array_map( 'stripslashes', $aCurrent );
		mysqli_free_result( $mData );
		return ( count( $aArray ) ) ? $aArray : false;
	} // _query

	protected function _logQuery( $sQuery, $mData, $fStartTime, $fEndTime ) {
		$aStats = array(
			'connexion' => $this->currentConnexionName,
			'database' => $this->currentDatabaseName,
			'query' => $sQuery,
			'type' => gettype( $mData ) == 'boolean' ? 'operation' : 'query',
			'time' => round( ( $fEndTime - $fStartTime ) * 1000, 2 ),
			'rows' => null,
			'error' => null
		);
		if( mysqli_errno( $this->currentConnexion ) ) {
			$aStats[ 'error' ] = array(
				mysqli_errno( $this->currentConnexion ) => mysqli_error( $this->currentConnexion )
			);
		} else {
			$aStats[ 'rows' ] = gettype( $mData ) == 'boolean' ? mysqli_affected_rows( $this->currentConnexion ) : mysqli_num_rows( $mData );
		}
		$this->_aQueries[] = $aStats;
	} // _logQuery

	protected function _getErrorMessage() {
		return 'MySQL Error (' . mysqli_errno( $this->currentConnexion ) . ' : ' . mysqli_error( $this->currentConnexion ) . ')';
	} // _getErrorMessage

	// --- private members

	private $_sCurrentConnexionName;
	private $_sCurrentDatabaseName;

	private $_aConnexions = array();
	private $_aDatabases = array(
		'schema' => 'information_schema'
	);

	private $_bEnabledLog = true;

	private $_aQueries = array();

} // class::db
