<?php
namespace TH\ZfQrcode\Controller;

use \Zend\Mvc\Controller\AbstractActionController;
use \Munee\Dispatcher;
use \Munee\Request;

class QrCodeController extends AbstractActionController
{
	protected $mime = array(
		'png' => 'image/png',
		'jpg' => 'image/jpeg',
		'gif' => 'image/gif'
	);

    /**
	* output qr image
	* @return void
	*/
    public function QrCodeAction()
    {
		$config = $this->getServiceLocator()->get('Config');
		$path = $config['TH']['QrCode']['path'];
		$filetype = $config['TH']['QrCode']['filetype'];

		$filename = "$path/{$this->params('qrid')}";
		if (!file_exists($filename))
		{
            header('HTTP/1.0 404 Not Found');
            header('Status: 404 Not Found');
		}
		else
		{
			header("HTTP/1.1 200 Ok");
			header('Cache-Control: public');
			header('Expires: '.gmdate('D, d M Y H:i:s', time()+31536000));
			header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($filename)).' GMT', true, 200);
			header('Content-Length: '.filesize($filename));
			header('Content-Type: ' . $this->mime[$filetype]);

			if ($name = $this->params()->fromQuery('download'))
			{
				$name = str_replace(array('"',"\n","\r"), '', $name);
				header("Content-disposition: attachment; filename={$name}");
			}

			readfile($filename);
		}
        exit();
    }
}