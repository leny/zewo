<?php
/** flatLand! : zewo
 * /classes/zewo.php : main classe
 */

namespace Zewo;

class Zewo extends Tools\Singleton {

	public function __get( $sName ) {
		switch( $sName ) {
			case 'route':
				return $this->_oRouting;
				break;
			case 'utils':
				return $this->_oUtils;
				break;
			case 'global':
			case 'globals':
				return $this->utils->globals;
				break;
			case 'templates':
			case 'template':
			case 'tpl':
				return $this->_oTemplate;
				break;
			case 'db':
				return $this->_oDB;
				break;
			case 'config':
				return $this->_oConfig;
				break;
			case 'cache':
				return $this->_oCache;
				break;
			case 'orm':
				return $this->_oORM;
				break;
			default:
				if( !array_key_exists( $sName, $this->_aDynamicProperties ) )
					throw new \InvalidArgumentException( 'There is no dynamic property called "' . $sName . '" !' );
				call_user_func_array( $this->_aDynamicProperties[ $sName ], array() );
				break;
		}
	} // __get

	// shortcuts

	public function __call( $sName, $aArguments ) {
		switch( $sName ) {
			// TEMPLATE shortcuts
			case 'assign':
			case 'fetch':
			case 'fetchTo':
			case 'display':
			case 'close':
			case 'clearCache':
				call_user_func_array( array( $this->template, $sName ), $aArguments );
				break;
			// ROUTING shortcuts
			case 'run':
			case 'post':
			case 'get':
			case 'map':
			case 'error':
			case 'redirect':
			case 'callError':
			case 'callErrorOn':
				call_user_func_array( array( $this->route, $sName ), $aArguments );
				break;
			// UTILS shortcuts
			case 'load':
				call_user_func_array( array( $this->utils, $sName ), $aArguments );
				break;
			default:
				if( !array_key_exists( $sName, $this->_aDynamicMethods ) )
					throw new \InvalidArgumentException( 'There is no dynamic method called "' . $sName . '" !' );
				call_user_func_array( $this->_aDynamicMethods[ $sName ], $aArguments );
				break;
		}
	} // __call

	public function init( $mConfig=null, $sPathBase = null ) {
		if( is_array( $mConfig ) ) {
			$aConfig = $mConfig;
		} elseif( file_exists( $mConfig ) ) {
			$aConfig = json_decode( file_get_contents( $mConfig ), true ) ?: array();
		}
		$this->_applyConfig( $aConfig, $sPathBase );
	} // init

	public function reloadCache() {
		$this->_oCache = Tools\Cache\Cache::getInstanceOf( $this->config->get( 'cache.type' ) );
	} // reloadCache

	public function registerMethod( $sMethod, $cCallback ) {
		if( in_array( $sMethod, $this->_aReservedMethodsNames ) )
			throw new \InvalidArgumentException( 'Method "' . $sMethod . '" already exists in zewo !' );
		if( !is_callable( $cCallback ) )
			throw new \InvalidArgumentException( 'Given callback method for "' . $sMethod . '" is not callable !' );
		$this->_aDynamicMethods[ $sMethod ] = $cCallback;
	} // registerMethod

	public function registerProperty( $sProperty, $cCallback ) {
		if( in_array( $sProperty, $this->_aReservedPropertiesNames ) )
			throw new \InvalidArgumentException( 'Property "' . $sProperty . '" already exists in zewo !' );
		if( !is_callable( $cCallback ) )
			throw new \InvalidArgumentException( 'Given callback property for "' . $sPropery . '" is not callable !' );
		$this->_aDynamicProperties[ $sProperty ] = $cCallback;
	} // registerProperty

	private function _applyConfig( $aConfig, $sPathBase ) {
		$this->_oConfig = Config\Config::getInstance();
		$this->_oConfig->apply( $aConfig, $sPathBase );
		$this->_oRouting = Routing\Router::getInstance();
		$this->_oUtils = Utils\Utils::getInstance();
		// db
		$this->_oDB = DB\db::getInstance();
		if( $this->_oUtils->array_keys_exists( array( 'connexion', 'host', 'base', 'login', 'pass' ), $this->config->get( 'db' ) ) ) {
			$this->_oDB->addConnexion( $this->config->get( 'db.connexion' ), $this->config->get( 'db.host' ), $this->config->get( 'db.login' ), $this->config->get( 'db.pass' ), true );
			$this->_oDB->addDatabase( $this->config->get( 'db.connexion' ), $this->config->get( 'db.base' ), true );
		} elseif( is_array( $this->config->get( 'db' ) ) ) {
			foreach( $this->config->get( 'db' ) as $aConnexionInfos ) {
				if( $this->_oUtils->array_keys_exists( array( 'connexion', 'host', 'base', 'login', 'pass' ), $aConnexionInfos ) ) {
					$this->_oDB->addConnexion( $aConnexionInfos[ 'connexion' ], $aConnexionInfos[ 'host' ], $aConnexionInfos[ 'login' ], $aConnexionInfos[ 'pass' ], true );
					$this->_oDB->addDatabase( $aConnexionInfos[ 'connexion' ], $aConnexionInfos[ 'base' ], true );
				}
			}
		}
		// templates
		$this->_oTemplate = Templates\Templating::getInstance();
		// cache
		$this->_oCache = Tools\Cache\Cache::getInstanceOf( $this->config->get( 'cache.type' ) );
		$this->_oORM = ORM\ORM::getInstance();
	} // _applyConfig

	private $_oTemplate;
	private $_oRouting;
	private $_oUtils;
	private $_oDB;
	private $_oConfig;
	private $_oCache;
	private $_oORM;

	private $_aReservedMethodsNames = array( 'assign', 'fetch', 'fetchTo', 'display', 'close', 'clearCache', 'run', 'post', 'get', 'map', 'error', 'redirect', 'callError', 'callErrorOn', 'load' );
	private $_aDynamicMethods = array();

	private $_aReservedPropertiesNames = array( 'route', 'utils', 'globals', 'global', 'tpl', 'template', 'templates', 'db', 'config', 'cache', 'orm' );
	private $_aDynamicProperties = array();

} // class::Zewo
