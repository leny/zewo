<?php
/** flatLand! : zewo
 * /classes/config/config.php
 */

namespace Zewo\Config;

class Config extends \Zewo\Tools\Singleton {

	public function get( $sConfigPath ) {
		$aCurrent = $this->_aData;
		foreach( explode( '.', $sConfigPath ) as $sConfigPathPart ) {
			if( is_array( $aCurrent ) )
				$aCurrent = isset( $aCurrent[ $sConfigPathPart ] ) ? $aCurrent[ $sConfigPathPart ] : null;
		}
		return $aCurrent;
	} // get

	public function apply( $aConfig, $sPathBase = null ) {
		$this->_aData = array_merge( $this->_aDefault, $aConfig );
		if( !is_null( $sPathBase ) ) {
			$this->_aData[ 'template' ][ 'folders' ][ 'cache' ] = $sPathBase . $this->_aData[ 'template' ][ 'folders' ][ 'cache' ];
			$this->_aData[ 'template' ][ 'folders' ][ 'templates' ] = $sPathBase . $this->_aData[ 'template' ][ 'folders' ][ 'templates' ];
		}
	} // apply

	private $_aData = array();
	private $_aDefault = array(
		// DB
		'db' => array(
			'connexion' => '',
			'host' => 'localhost',
			'login' => '',
			'pass' => '',
			'base' => '',
		),
		// CACHE
		'cache' => array(
			'type' => \Zewo\Tools\Cache\Cache::APC,
		),
		// ORM
		'orm' => array(
			'cacheKey' => 'noCacheKey',
			'baseClass' => '\Zewo\Extendeds\Elements\Element',
		),
		// TEMPLATES
		'template' => array(
			'cache' => false,
			'folders' => array(
				'cache' => 'cache/',
				'templates' => 'templates/',
			),
		),
	);

} // class::Config
