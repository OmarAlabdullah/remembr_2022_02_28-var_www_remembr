<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation as FORM;


/**
 * Condolence
 *
 * @ORM\Entity
 */
class Condolence extends Memory
{
	/**
	 *@return  string
	 */
	public function getType()
	{
		return 'condolence';
	}

    /**
	 * @var string
	 *
	 * @FORM\AllowEmpty
	 * @FORM\Filter({"name":"StringTrim"})
	 * @FORM\Filter({"name":"StripTags"})
	 * @ORM\Column(name="anonymousEmail", type="string", length=255, nullable=true)
	 */
	protected $anonymousEmail;

    public function getAnonymousEmail()
    {
        return $this->anonymousEmail;
    }

    public function setAnonymousEmail($anonymousEmail)
    {
        $this->anonymousEmail = $anonymousEmail;
		return $this;
    }
    
	/**
	 * @return array
	 */
	public function getArrayCopy($depth=0)
	{
		$arr = parent::getArrayCopy($depth);
		$arr['type'] = 'condolence';

		return $arr;
	}
}