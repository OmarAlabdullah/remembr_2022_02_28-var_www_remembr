<?php
namespace TH\ZfQrcode\View\Helper;

use Zend\View\Helper\AbstractHelper;

class QrCodeUrl extends AbstractHelper
{
	protected $path;
	protected $filetype;
	public function __construct($path, $filetype)
	{
		$this->path = $path;
		$this->filetype = $filetype;
	}

    /**
	* View helper to create QR-Code from text
	*
	* @param string $text A string representing the data for the QR-code.
	* @param int $size
	* @param int $padding
	* @return string a relative url to the qr image.
	*/
    public function __invoke($text, $size=300, $padding=10)
    {
        if (!is_string($text) || !$text) return;
        if (!is_int($size) || $size <= 0 ) return;
        if (!is_int($padding) || $padding < 0 ) return;

		$path = $this->path;
		$filetype = $this->filetype;
		$filename = sha1("{$text}_{$size}_{$padding}");
		$filename = "$filename.$filetype";

		if (!file_exists("$path/$filename"))
		{
			$qrCode = new \Endroid\QrCode\QrCode();
			$qrCode->setText($text);
			$qrCode->setSize($size);
			$qrCode->setPadding($padding);
			$qrCode->render("$path/$filename", $filetype);
		}

		$src = $this->view->url('ThQrCode', array('qrid' => $filename));

        return $src;
    }
}