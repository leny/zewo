<?php
/** flatLand! : zewo
 * /classes/utils/fs/image.php
 */

namespace Zewo\Utils\FS;

class Image extends File {

	const RESIZE_AUTO = 'auto';
	const RESIZE_PORTRAIT = 'portrait';
	const RESIZE_LANDSCAPE = 'landscape';
	const RESIZE_CROP = 'crop';
	const RESIZE_EXACT = 'exact';
	const RESIZE_FIT = 'fit';

	// --- public properties

	public function __get( $sName ) {
		switch( $sName ) {
			case 'width':
				return $this->_iWidth;
				break;

			case 'height':
				return $this->_iHeight;
				break;

			default:
				return parent::__get( $sName );
				break;
		}
	} // __get

	// --- constructor

	public function __construct( $mData ) {
		parent::__construct( $mData );
		return $this;
	} // __construct

	// --- public methods

	public function asBase64() {
		return "data:" . $this->mimetype . ";base64," . base64_encode( $this->_save( null ) );
	} // asBase64

	public function asRaw() {
		return $this->_save(null);
	} // asRaw

	public function resize($iNewWidth, $iNewHeight, $sResizeType=self::RESIZE_AUTO) {
		$this->_resize($iNewWidth, $iNewHeight, $sResizeType);
		return $this;
	} // resize

	public function save() {
		return $this->_save( $this->_sPath );
	} // save

	// --- protected methods

	protected function _load() {
		switch( $this->_sExtension ) {
			case 'jpg':
			case 'jpeg':
				$this->_rLoadedImg = @imagecreatefromjpeg($this->_sPath);
				break;
			case 'png':
				$this->_rLoadedImg = @imagecreatefrompng($this->_sPath);
				break;
			case 'gif':
				$this->_rLoadedImg = @imagecreatefromgif($this->_sPath);
				break;
			default:
				throw new \LogicException( "Le fichier n'est pas un format d'image valide." );
				break;
		}
	} // _load

	protected function _updateInfos() {
		parent::_updateInfos();
		if( is_null( $this->_rLoadedImg ) )
			$this->_load();
		$this->_iWidth = imagesx( $this->_rResizedImg ?: $this->_rLoadedImg );
		$this->_iHeight = imagesy( $this->_rResizedImg ?: $this->_rLoadedImg );
		gc_collect_cycles();
	} // _updateInfos

	protected function _save( $sUrl=null, $iImageQuality=100 ) {
		if( is_null( $sUrl ) )
			ob_start();
		switch($this->_sExtension) {
			case 'jpg':
			case 'jpeg':
				$bOperation = imagejpeg($this->_rResizedImg ?: $this->_rLoadedImg, $sUrl, $iImageQuality);
				break;
			case 'png':
				$iScaleQuality = round(($iImageQuality/100) * 9);
				$bOperation = imagepng($this->_rResizedImg ?: $this->_rLoadedImg, $sUrl, 9 - $iScaleQuality);
				break;
			case 'gif':
				$bOperation = imagegif($this->_rResizedImg ?: $this->_rLoadedImg, $sUrl);
				break;
		}
		if( is_null( $sUrl ) )
			$sResult = ob_get_clean();
		imagedestroy( $this->_rResizedImg ?: $this->_rLoadedImg );
		if( is_null( $sUrl ) )
			return $sResult;
		else
			return $bOperation;
	} // _save

	// --- protected properties

	protected $_rLoadedImg;
	protected $_rResizedImg;

	protected $_iWidth;
	protected $_iHeight;

	// --- private methods

	private function _resize( $iNewWidth, $iNewHeight, $sResizeType ) {
		$aOptionArray = $this->_getDimensions( $iNewWidth, $iNewHeight, $sResizeType );

		$iOptimalWidth = $aOptionArray['optimalWidth'];
		$iOptimalHeight = $aOptionArray['optimalHeight'];

		$this->_rResizedImg = imagecreatetruecolor($iOptimalWidth, $iOptimalHeight);
		imagecopyresampled($this->_rResizedImg, $this->_rLoadedImg, 0, 0, 0, 0, $iOptimalWidth, $iOptimalHeight, $this->_iWidth, $this->_iHeight);

		if( $sResizeType == self::RESIZE_CROP )
			$this->_crop($iOptimalWidth, $iOptimalHeight, $iNewWidth, $iNewHeight);
	} // _resize

	private function _getDimensions( $iNewWidth, $iNewHeight, $sResizeType ) {
		switch( $sResizeType ) {
			case self::RESIZE_EXACT:
				$iOptimalWidth = $iNewWidth;
				$iOptimalHeight = $iNewHeight;
				break;
			case self::RESIZE_FIT:
				if( $this->_iWidth > $this->_iHeight ) {
					$iOptimalWidth = $iNewWidth;
					$iOptimalHeight = $iNewHeight / ( $this->_iWidth / $this->_iHeight );
				} else {
					$iOptimalWidth = $iNewHeight / ( $this->_iHeight / $this->_iWidth );
					$iOptimalHeight = $iNewHeight;
				}
				break;
			case self::RESIZE_PORTRAIT:
				$iOptimalWidth = $iNewHeight * ( $this->_iWidth / $this->_iHeight );
				$iOptimalHeight = $iNewHeight;
				break;
			case self::RESIZE_LANDSCAPE:
				$iOptimalWidth = $iNewWidth;
				$iOptimalHeight = $iNewWidth * ( $this->_iHeight / $this->_iWidth );
				break;
			case self::RESIZE_CROP:
				$aOptionArray = $this->_getOptimalCrop( $iNewWidth, $iNewHeight );
				$iOptimalWidth = $aOptionArray[ 'optimalWidth' ];
				$iOptimalHeight = $aOptionArray[ 'optimalHeight' ];
				break;
			case self::RESIZE_AUTO:
			default:
				$aOptionArray = $this->_getSizeByAuto( $iNewWidth, $iNewHeight );
				$iOptimalWidth = $aOptionArray[ 'optimalWidth' ];
				$iOptimalHeight = $aOptionArray[ 'optimalHeight' ];
				break;
		}
		return array(
			'optimalWidth' => $iOptimalWidth,
			'optimalHeight' => $iOptimalHeight,
		);
	} // _getDimensions

	private function _getSizeByAuto($iNewWidth, $iNewHeight) {
		if ($this->_iHeight < $this->_iWidth) {
			$iOptimalWidth = $iNewWidth;
			$iOptimalHeight = $iNewWidth * ($this->_iHeight / $this->_iWidth);
		} elseif ($this->_iHeight > $this->_iWidth) {
			$iOptimalWidth = $iNewHeight * ($this->_iWidth / $this->_iHeight);
			$iOptimalHeight = $iNewHeight;
		} else {
			if ($iNewHeight < $iNewWidth) {
				$iOptimalWidth = $iNewWidth;
				$iOptimalHeight = $iNewWidth * ($this->_iHeight / $this->_iWidth);
			} else if ($iNewHeight > $iNewWidth) {
				$iOptimalWidth = $iNewHeight * ($this->_iWidth / $this->_iHeight);
				$iOptimalHeight = $iNewHeight;
			} else {
				$iOptimalWidth = $iNewWidth;
				$iOptimalHeight = $iNewHeight;
			}
		}

		return array(
			'optimalWidth' => $iOptimalWidth,
			'optimalHeight' => $iOptimalHeight
		);
	} // _getSizeByAuto

	private function _getOptimalCrop($iNewWidth, $iNewHeight) {
		$fHeightRatio = $this->_iHeight / $iNewHeight;
		$fWidthRatio  = $this->_iWidth /  $iNewWidth;

		$fOptimalRatio = ($fHeightRatio < $fWidthRatio) ? $fHeightRatio : $fWidthRatio;

		$iOptimalHeight = $this->_iHeight / $fOptimalRatio;
		$iOptimalWidth  = $this->_iWidth  / $fOptimalRatio;

		return array(
			'optimalWidth' => $iOptimalWidth,
			'optimalHeight' => $iOptimalHeight
		);
	} // _getOptimalCrop

	private function _crop($iOptimalWidth, $iOptimalHeight, $iNewWidth, $iNewHeight) {
		$iCropStartX = ( $iOptimalWidth / 2) - ( $iNewWidth /2 );
		$iCropStartY = ( $iOptimalHeight/ 2) - ( $iNewHeight/2 );

		$rCrop = $this->_rResizedImg;

		$this->_rResizedImg = imagecreatetruecolor($iNewWidth , $iNewHeight);
		imagecopyresampled($this->_rResizedImg, $rCrop , 0, 0, $iCropStartX, $iCropStartY, $iNewWidth, $iNewHeight, $iNewWidth, $iNewHeight);
	} // _crop

	// --- private members

} // class:Image
