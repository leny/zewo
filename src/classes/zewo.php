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
		}
	} // __get

	// shortcuts

	public function __call( $sName, $aArguments ) {
		switch( $sName ) {
			// ROUTING shortcuts
			case 'run':
			case 'post':
			case 'get':
			case 'map':
			case 'error':
			case 'redirect':
				call_user_func_array( array( $this->route, $sName ), $aArguments );
				break;
			// UTILS shortcuts
			case 'load':
				call_user_func_array( array( $this->utils, $sName ), $aArguments );
				break;
		}
	} // __call

	public function init( $mConfig=null ) {
		if( is_array( $mConfig ) ) {
			$aConfig = $mConfig;
		} else {
			// TODO : check if JSON file exists
		}
		$this->_applyConfig( $aConfig );
	} // init

	private function _applyConfig( $aConfig ) {
		$this->_oConfig = Config\Config::getInstance();
		$this->_oConfig->apply( $aConfig );
		$this->_oRouting = Routing\Router::getInstance();
		$this->_oUtils = Utils\Utils::getInstance();
		// db
		$this->_oDB = DB\db::getInstance();
		$this->_oDB->addConnexion( $this->config->get( 'db.connexion' ), $this->config->get( 'db.host' ), $this->config->get( 'db.login' ), $this->config->get( 'db.pass' ), true );
		$this->_oDB->addDatabase( $this->config->get( 'db.connexion' ), $this->config->get( 'db.base' ), true );
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

} // class::Zewo
