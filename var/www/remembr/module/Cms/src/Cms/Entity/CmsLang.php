<?php

namespace Cms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="cmsLang")
 * @ORM\HasLifecycleCallbacks
 */
class CmsLang
{

    /**
     * @var integer
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    public function getId()
    {
        return $this->id;
    }

    /**
     * @var string
     * @ORM\Column(name="lang", type="string", length=5, nullable=false)
     */
    protected $lang;

    public function getLang()
    {
        return $this->lang;
    }

    public function setLang($value)
    {
        $this->lang = $value;
    }


}
