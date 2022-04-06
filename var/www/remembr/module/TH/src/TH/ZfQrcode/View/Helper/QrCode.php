<?php
namespace TH\ZfQrcode\View\Helper;

use Zend\View\Helper\AbstractHelper;

class QrCode extends QrCodeUrl
{
   /**
	* View helper to create QR-Code url from text
	*
	* @param string $text A string representing the data for the QR-code.
	* @param int $size
	* @param int $padding
	* @param array $attributes [optional] An array of key value pairs to be added as attributes, the default value is null.
	* @return string a relative url to the qr image.
	*/
    public function __invoke($text, $size=300, $padding=10, $attributes = null)
    {
		$src = parent::__invoke($text, $size, $padding);

		if (empty($src)) return;

        $attributeString = '';
        if (is_array($attributes)) {
            foreach ($attributes as $key => $value) {
                if (! is_string($key) && ! is_string($value)) continue;
                $attributeString .= $key . '="' . $value . '" ';
            }
        }

        return '<img src="'.$src.'" ' . $attributeString . ' />';
    }
}