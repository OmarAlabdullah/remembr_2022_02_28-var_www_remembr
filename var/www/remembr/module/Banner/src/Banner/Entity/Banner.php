<?php

namespace Banner\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="Banner")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Banner
{
	const typeHtml = 0;
	const typeImg  = 1;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id", type="integer", nullable=false)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $id;
	public function getId() { return $this->id; }

	/**
	 * This id can be used for user-facing purposes to obscure the (easily-guessed) ids of other banners.
	 * @var string
	 *
	 * @ORM\Column(type="string", length=40, unique=true)
	 */
	protected $externalId;
	public function getExternalId() { return $this->externalId; }

	/**
	 * @var boolean
	 *
	 * @ORM\Column(type="boolean")
	 */
	protected $enabled = false;
	public function getEnabled()       { return $this->enabled; }
	public function setEnabled($value) { $this->enabled = $value; }

	/**
   * @ORM\ManyToOne(targetEntity="BannerFormat")
	 */
	protected $format;
	public function getFormat()                    { return $this->format; }
	public function setFormat(BannerFormat $value) { $this->format = $value; }

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer")
	 */
	protected $type;
	public function getType()       { return $this->type; }
	public function setType($value) { $this->type = $value; }

	/**
	 * @var string
	 *
	 * @ORM\Column(type="text")
	 */
	protected $content = '';
	public function getContent()       { return $this->content; }
	public function setContent($value) { $this->content = $value; }

	/**
	 * @var string
	 *
	 * @ORM\Column(type="text",length=200)
	 */
	protected $url = '';
	public function getUrl()       { return $this->url; }
	public function setUrl($value) { $this->url = $value; }

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer")
	 */
	protected $views = 0;
	public function getViews()       { return $this->views; }
	public function setViews($value) { $this->views = $value; }

	/**
	 * @var integer
	 *
	 * If this is 0, allows infinite views.
	 *
	 * @ORM\Column(type="integer")
	 */
	protected $maxViews;
	public function getMaxViews()       { return $this->maxViews; }
	public function setMaxViews($value) { $this->maxViews = $value; }

	/**
	 * @var integer
	 *
	 * @ORM\Column(type="integer")
	 */
	protected $clicks = 0;
	public function getClicks()       { return $this->clicks; }
	public function setClicks($value) { $this->clicks = $value; }

	/**
	 * @var integer
	 *
	 * If this is 0, allows infinite clicks.
	 *
	 * @ORM\Column(type="integer")
	 */
	protected $maxClicks;
	public function getMaxClicks()       { return $this->maxClicks; }
	public function setMaxClicks($value) { $this->maxClicks = $value; }

	public function __construct() {
		$this->externalId = '';
		//$this->generateExternalId();
	}

	public function generateExternalId() {
		$chars = implode('', range('a', 'z')).implode('', range('A', 'Z')).implode('', range('0', '9'));
		$s = '';
		for ($i = 0; $i < 32; $i++)
			$s.= $chars[mt_rand(0, strlen($chars)-1)];
		$this->externalId = $s;
	}

	public function getArrayCopy() {
		$values = get_object_vars($this);
		$values['format'] = $this->format ? $this->format->getId() : null;
		return $values;
	}

	public function exchangeArray(array $values) {
		foreach (array('format', 'enabled', 'type', 'url', 'content', 'maxClicks', 'maxViews') as $name) {
			if (isset($values[$name])) {
				$this->$name = $values[$name];
			}
		}
	}
}

?>
