<?php

namespace Banner\Twig;

use \Banner\Entity\Banner;

class TwigBanner extends \Twig_Extension
{
	private $em;

	public function getName() { return 'banner_twig'; }

	public function getFunctions() {
		return array(
			new \Twig_SimpleFunction('banner', array($this, 'showBanner'), array('is_safe' => array('html'))),
		);
	}

	public function __construct(array $config, $em) {
		$this->config = $config;
		$this->em = $em;
	}

	public function showBanner() {
		try {
			$query = $this->em->createQueryBuilder();
			$count = $query->select('count(b.id)')->from('Banner\Entity\Banner', 'b')
				->where('b.maxClicks=0 or b.clicks<b.maxClicks')
				->andWhere('b.maxViews=0 or b.views<b.maxViews')->getQuery()->getSingleScalarResult();
			if (!$count)
				return '';

			$query = $this->em->createQueryBuilder();
			$banner = $query->select('b')->from('Banner\Entity\Banner', 'b')
				->where('b.maxClicks=0 or b.clicks<b.maxClicks')
				->andWhere('b.maxViews=0 or b.views<b.maxViews')
				->setFirstResult(mt_rand(0, $count-1))->setMaxResults(1)->getQuery()->getSingleResult();

			$query = $this->em->createQueryBuilder();
			// Atomic update: views = views+1
			$update = $query->update('Banner\Entity\Banner', 'b')->set('b.views', $query->expr()->sum('b.views', 1))->where('b.id=:id');
			$update->getQuery()->execute(array('id' => $banner->getId()));
			$open = '<div class="banner '.$banner->getFormat()->getCssClass().'">';
			if ($banner->getType() == Banner::typeHtml) {
				return $open.$banner->getContent().'<a href="/banner/redirect/'.$banner->getId().'"><span class="link-spanner"></span></a></div>';
			} else {
				return $open.'<a href="/banner/redirect/'.$banner->getId().'"><img src="'.$this->config['bannerWebDir'].'/'.$banner->getContent().'" /></a></div>';
			}
		} catch (\Exception $e) {
			return 'Exception during banner process: '.$e->getMessage();
		}
	}
}

?>
