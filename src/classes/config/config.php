<?php
/** flatLand! : zewo
 * /classes/config/config.php
 */

namespace Zewo\Config;

// TODO : refactor this

class Config extends \Zewo\Tools\Singleton {

	public function __get( $sName ) {
		switch( $sName ) {
			case 'db_connexion':
				return $this->_sDBConnexion;
				break;
			case 'db_host':
				return $this->_sDBHost;
				break;
			case 'db_login':
				return $this->_sDBLogin;
				break;
			case 'db_pass':
				return $this->_sDBPass;
				break;
			case 'db_base':
				return $this->_sDBBase;
				break;
			default:
				return parent::__get( $sName );
				break;
		}
	} // __get

	public function apply( $aConfig ) {
		$this->_apply( $aConfig );
	} // apply

	private function _apply( $aConfig ) {
		// db
		if( isset( $aConfig[ 'db' ] ) ) {
			if( isset( $aConfig[ 'db' ][ 'connexion' ] ) )
				$this->_sDBConnexion = $aConfig[ 'db' ][ 'connexion' ];
			if( isset( $aConfig[ 'db' ][ 'host' ] ) )
				$this->_sDBHost = $aConfig[ 'db' ][ 'host' ];
			if( isset( $aConfig[ 'db' ][ 'login' ] ) )
				$this->_sDBLogin = $aConfig[ 'db' ][ 'login' ];
			if( isset( $aConfig[ 'db' ][ 'pass' ] ) )
				$this->_sDBPass = $aConfig[ 'db' ][ 'pass' ];
			if( isset( $aConfig[ 'db' ][ 'base' ] ) )
				$this->_sDBBase = $aConfig[ 'db' ][ 'base' ];
		}
	} // _apply

	// DB
	private $_sDBConnexion = '';
	private $_sDBHost = 'localhost';
	private $_sDBLogin = '';
	private $_sDBPass = '';
	private $_sDBBase = '';

	// ORM
	private $_sCacheKey = 'noCacheKey';
	private $_sBaseClass = 'Zewo\ORM\Element';

} // class::Config
