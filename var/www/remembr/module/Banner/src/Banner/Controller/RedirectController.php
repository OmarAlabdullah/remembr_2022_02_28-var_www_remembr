<?php

namespace Banner\Controller;

class RedirectController extends \Base\Controller\BaseController
{
	protected $redirectActionLoggedOut = true;

	public function redirectAction() {
		$id = $this->params()->fromRoute('id');
		$banner = $this->getEm()->getRepository('Banner\Entity\Banner')->find($id);
		if (!$banner) die;

		$query = $this->getEm()->createQueryBuilder();
		$query->update('Banner\Entity\Banner', 'b')->set('b.clicks', $query->expr()->sum('b.clicks', 1))->where('b.id=:id')
			->getQuery()->execute(array('id' => $banner->getId()));

		$this->redirect()->toUrl($banner->getUrl());
	}
}

?>
