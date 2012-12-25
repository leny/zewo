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

	public function set( $sConfigPath, $mValue ) {
		$aCurrent = &$this->_aData;
		foreach( explode( '.', $sConfigPath ) as $sConfigPathPart )
			$aCurrent = &$aCurrent[ $sConfigPathPart ];
		$aCurrent = $mValue;
	} // set

	public function apply( $aConfig, $sPathBase = null ) {
		$this->_aDefault[ 'path' ][ 'url' ] = 'http://' . $_SERVER[ 'HTTP_HOST' ] . '/';
		foreach( $this->_aDefault as $sSection => $aParameters ) {
			if( $sSection === 'db' && is_array( $aConfig[ $sSection ] ) ) {
				$this->_aData[ $sSection ] = $aConfig[ $sSection ];
			} else
				$this->_aData[ $sSection ] = isset( $aConfig[ $sSection ] ) ? array_merge( $this->_aDefault[ $sSection ], $aConfig[ $sSection ] ) : $this->_aDefault[ $sSection ];
		}
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
		// PATH
		'path' => array(
			'url' => null,
			'files' => 'files/'
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
