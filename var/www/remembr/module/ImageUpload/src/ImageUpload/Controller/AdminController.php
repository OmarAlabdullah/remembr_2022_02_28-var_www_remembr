<?php

namespace ImageUpload\Controller;

use Base\Controller\BaseController;

class AdminController extends \Acelaya\IndexController
{

	function __construct() {
		parent::__construct(
			new \Acelaya\Files\FilesService(
				new \Acelaya\Files\FilesOptions(array(
					'basePath' => __DIR__ . '/../../../../../data/uploads/cms/'
				))));
	}
	
	protected $view;

	public function indexAction()
	{
		return array(
			'files' => $this->filesService->getFiles()
		);
	}
	
	public function saveSlugsAction()
	{
		return array(
			'files' => $this->filesService->getFiles()
		);
	}
	
	public function listAction()
	{
		return array(
			'files' => $this->filesService->getFiles()
		);
	}

	// important
	public function setRequest($req)
	{
			$this->request = $req;
	}
}
