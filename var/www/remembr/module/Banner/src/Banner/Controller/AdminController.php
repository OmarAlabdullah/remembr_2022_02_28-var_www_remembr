<?php

namespace Banner\Controller;

use Base\Controller\BaseController;
use Banner\Entity\Banner;

class AdminController extends BaseController
{
	protected $indexActionLoggedOut = true;
	protected $createActionLoggedOut = true;
	protected $editActionLoggedOut = true;

	public function indexAction() {
		$banners = $this->getEm()->getRepository('Banner\Entity\Banner')->findAll();

		return array(
			'banners' => $banners
		);
	}

	public function setRequest($req) {
		$this->request = $req;
	}

	public function createAction() {
		$em = $this->getEm();
		$formats = $em->getRepository('Banner\Entity\BannerFormat')->findBy(array(), array('id' => 'asc'));

		$form = new \Banner\Forms\BannerForm('create', $formats, $this->getServiceLocator()->get('Config'));
		$banner = new \Banner\Entity\Banner();
		$banner->setType(\Banner\Entity\Banner::typeHtml);
		$form->bind($banner);

		$req = $this->getRequest();
		if ($req->isPost()) {
			$form->setData(array_merge_recursive($req->getPost()->toArray(), $req->getFiles()->toArray()));
			if ($form->isValid()) {
				// Convert the 'format' value back to an entity reference
				$banner->setFormat($em->getReference('Banner\Entity\BannerFormat', $banner->getFormat()));
				$repo = $em->getRepository('Banner\Entity\Banner');
				do {
					$banner->generateExternalId();
				} while (count($repo->findBy(array('externalId' => $banner->getExternalId()))) > 0);

				$em->persist($banner);
				$em->flush($banner);

				return $this->redirect()->toRoute(null, array('action' => 'index'), array(), true);
 			}
		}

		$model = new \Zend\View\Model\ViewModel(array(
			'form' => $form,
		));
		$model->setTemplate('banner/admin/bannerform.twig');
		return $model;
	}

	public function editAction() {
		$id = $this->params()->fromRoute('id');
		if (!$id) return $this->redirect()->toRoute('banners', array('controller' => 'admin', 'action' => 'index'));

		$em = $this->getEm();

		$banner = $em->find('Banner\Entity\Banner', $id);
		$formats = $em->getRepository('Banner\Entity\BannerFormat')->findBy(array(), array('id' => 'asc'));

		$config = $this->getServiceLocator()->get('Config');
		$form = new \Banner\Forms\BannerForm('edit', $formats, $config, true);
		$form->setData($banner->getArrayCopy());

		$req = $this->getRequest();
		if ($req->isPost()) {
			$form->setData(array_merge_recursive($req->getPost()->toArray(), $req->getFiles()->toArray()));
			if ($form->isValid()) {
				// Convert the 'format' value back to an entity reference
				$data = $form->getData();
				$data['format'] = $em->getReference('Banner\Entity\BannerFormat', $data['format']);
				if ($data['type'] == Banner::typeImg) {
					unset($data['content']);
				}
				$this->updateBannerImage($banner, $data, $config['banner']['bannerDir']);

				$banner->exchangeArray($data);
				$em->flush();

				return $this->redirect()->toRoute(null, array('action' => 'index'), array(), true);
 			}
		}


		$model = new \Zend\View\Model\ViewModel(array(
			'form' => $form,
		));
		$model->setTemplate('banner/admin/bannerform.twig');
		return $model;
	}

	private function updateBannerImage(Banner $oldBanner, array &$newData, $bannerDir) {
		if ($oldBanner->getType() == Banner::typeImg && $newData['type'] == Banner::typeImg &&
			  $newData['image']['error'] == UPLOAD_ERR_NO_FILE) {
			return;
		}
		if ($oldBanner->getType() == Banner::typeImg) {
			unlink($bannerDir.'/'.$oldBanner->getContent());
		}
		if ($newData['type'] == Banner::typeImg) {
			$newName = $oldBanner->getExternalId().'.'.pathinfo($newData['image']['tmp_name'], PATHINFO_EXTENSION);
			rename($newData['image']['tmp_name'], $bannerDir.'/'.$newName);
			$newData['content'] = $newName;
		}
	}
}

?>
