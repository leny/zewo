<?php
/** flatLand! : zewo
 * /classes/utils/fs/file.php
 */

namespace Zewo\Utils\FS;

class File extends FileSystem {

	// --- public properties

	public function __get( $sName ) {
		switch( $sName ) {
			case 'hu_size':
			case 'human_size':
				return $this->_HUSize();
				break;

			case 'size':
				return $this->_iSize;
				break;

			case 'filename':
				return $this->_sBaseName . '.' . $this->_sExtension;
				break;

			case 'type':
			case 'mime':
			case 'mimetype':
				return $this->_sMimeType;
				break;

			case 'path':
				return $this->_sPath;
				break;

			case 'url':
				return \Zewo\Config\Config::getInstance()->get( 'path.url' ) . '/' . \Zewo\Config\Config::getInstance()->get( 'path.files' ) . $this->filename;
				break;

			case 'tag':
				return '<img alt="" src="' . $this->url . '" />';
				break;

			case 'content':
				return $this->read();
				break;
		}
	} // __get

	public function __set( $sName, $sValue ) {
		switch( $sName ) {
			case 'basename':
				$this->_sBaseName = $sValue;
				break;

			default:
				fwarning('Error in setter');
				break;
		}
	} // __set

	// --- constructor

	public function __construct( $mData ) {
		if( array_has_keys( self::$_aGlobalFileArrayPattern, $mData ) ) {
			return !is_uploaded_file( $mData['tmp_name'] ) ? false : $this->_loadFromGlobals( $mData );
		} else {
			return $this->_loadFromPath( $mData );
		}
	} // __construct

	// --- public methods

	public function exists() {
		return file_exists( $this->_sPath ) && is_file( $this->_sPath );
	} // exists

	public function save() {
		return $this->_save( null );
	} // save

	public function saveTo( $sPath ) {
		if( $this->_bUploaded ) {
			if( is_dir( $sPath ) )
				$sPath = $sPath . $this->_sBaseName . '.' . $this->_sExtension;
			$bOperation = move_uploaded_file( $this->_sPath, $sPath );
			if( $bOperation ) {
				$this->_bUploaded = false;
				$this->_sPath = $sPath;
				$this->_updateInfos();
			}
			return $bOperation;
		} else {
			$this->_sPath = $sPath;
			$this->_updateInfos( null );
			return $this->_save( $sPath );
		}
	} // saveTo

	public function copyTo( $sPath=null, $sPrefix=null ) {
		if( is_null( $sPath ) && is_null( $sPrefix ) )
			throw new \InvalidArgumentException( "File::copyTo required at least a path or a prefix !" );
		if( is_null( $sPrefix ) ) {
			$bOperation = copy( $this->_sPath, $sPath );
			if( $bOperation ) {
				$sFSClassName = get_called_class();
				return new $sFSClassName( $sPath );
			} else
				return false;
		} else if( is_null( $sPath ) ) {
			$sPath = $this->_sBasePath . $sPrefix . $this->_sBaseName . '.' . strtolower( $this->_sExtension );
			$bOperation = copy( $this->_sPath, $sPath );
			if( $bOperation ) {
				$sFSClassName = get_called_class();
				return new $sFSClassName( $sPath );
			} else
				return false;
		} else {
			$aInfos = pathinfo( $sPath );
			$sPath = $aInfos['dirname'] . '/' . $sPrefix . $aInfos['filename'] . '.' . strtolower( $aInfos['extension'] );
			$bOperation = copy( $this->_sPath, $sPath );
			if( $bOperation ) {
				$sFSClassName = get_called_class();
				return new $sFSClassName( $sPath );
			} else
				return false;
		}
	} // copyTo

	public function delete() {
		return unlink( $this->_sPath );
	} // delete

	public function read() {
		return $this->_read();
	} // read

	public function reload() {
		return $this->_read( true );
	} // reloaf

	public function write( $sContent ) {
		$this->_sContent = $sContent;
	} // write

	public function append( $sContent ) {
		$this->_sContent .= $sContent;
	} // write

	public function isImage() {
		return get_called_class() === 'Image';
	} // isImage

	// --- protected methods

	protected function _read( $bForceFromFile=false ) {
		if( !isset($this->_sContent) || $bForceFromFile )
			$this->_sContent = file_get_contents($this->_sPath);
		return $this->_sContent;
	} // _read

	protected function _save( $sPath=null ) {
		if( is_dir( $sPath ) )
			$sPath = $sPath . $this->_sBaseName . '.' . $this->_sExtension;
		return file_put_contents( $sPath ?: $this->_sPath, $this->_read() ) !== false;
	} // _save

	protected function _updateInfos( ) {
		if( !$this->_bUploaded ) {
			$aInfos = pathinfo( $this->_sPath );
			$this->_iSize = filesize( $this->_sPath );
			$this->_sBasePath = $aInfos['dirname'] . '/';
			$this->_sBaseName = $aInfos['filename'];
			$this->_sMimeType = mime_content_type( $this->_sPath );
			$this->_sExtension = strtolower( $aInfos['extension'] );
		} else {
			$aInfos = pathinfo( $this->_sOriginalName );
			$this->_sExtension = strtolower( $aInfos['extension'] );
		}
	} // _updateInfos

	protected function _HUSize() {
		$iSizeInKo = $this->_iSize / 1024;
		if( round($iSizeInKo, 1) > 1024*1024 ) {
   			$iSizeInGo = $iSizeInKo / 1024 / 1024;
   			return round($iSizeInGo, 2)."Go";
		} elseif( round($iSizeInKo, 1) > 1024 ) {
			$iSizeInMo = $iSizeInKo / 1024;
			return round($iSizeInMo, 1)."Mo";
		} elseif( $iSizeInOctet > 1024 ) {
			return round($iSizeInKo)."ko";
		} else
			return round($iSizeInOctet)."o";
	} // HUSize

	// --- protected properties

	protected $_sOriginalName;
	protected $_sPath;
	protected $_sMimeType;
	protected $_iSize;

	protected $_sContent;

	protected $_sBasePath;
	protected $_sBaseName;
	protected $_sExtension;

	protected $_bUploaded = false;

	protected static $_aGlobalFileArrayPattern = array( 'name', 'type', 'tmp_name', 'error', 'size' );

	// --- private methods

	private function _loadFromGlobals( $aData ) {
		$this->_sOriginalName = $aData['name'];
		$this->_sMimeType = $aData['type'];
		$this->_sPath = $aData['tmp_name'];
		$this->_iSize = $aData['size'];
		$this->_bUploaded = true;
		$this->_updateInfos();
	} // _loadFromGlobals

	private function _loadFromPath( $sPath ) {
		if( file_exists( $sPath ) ) {
			$this->_sPath = $sPath;
			$this->_updateInfos();
		} else return null;
	} // _loadFromPath

	// --- private members

} // class:File
