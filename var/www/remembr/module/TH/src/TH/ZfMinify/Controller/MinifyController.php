<?php
namespace TH\ZfMinify\Controller;

use \Munee\Dispatcher;
use \Munee\Request;

class MinifyController extends \Base\Controller\BaseController
{
    private function http403() {
        return (new \Zend\Http\Response())->setStatusCode(401)->setContent("Forbidden");
    }
    
    /**
	* Directly outputs the return value of the munee Dispatcher object
	* run() method to emulate the approach originally used by Cody Lundquist.
	*
	* @return void
	*/
    public function minifyAction()
    {
		$config = $this->getServiceLocator()->get('Config');

//		runkit_constant_redefine('MUNEE_CACHE', '/var/tmp');

// Unfortunately Munee defines all these things when it's bootstrapped by the autoloader
// 
//		if (isset($config['TH']['ZfMinify']['cache']) && !defined('MUNEE_CACHE'))
//		{
//			define('MUNEE_CACHE', $config['TH']['ZfMinify']['cache']);
//		}
//		if (isset($config['TH']['ZfMinify']['webroot']) && !defined('WEBROOT'))
//		{
//			define('WEBROOT', $config['TH']['ZfMinify']['webroot']);
//		}
//		if (isset($config['TH']['ZfMinify']['encoding']) && !defined('MUNEE_CHARACTER_ENCODING'))
//		{
//			define('MUNEE_CHARACTER_ENCODING', $config['TH']['ZfMinify']['encoding']);
//		}

		$options = null;
		if (isset($config['TH']['ZfMinify']['requestoptions']))
		{
			$options = $config['TH']['ZfMinify']['requestoptions'];
		}
        
        $user = $this->getUser();
        
        foreach(explode(',', $_GET['files']) as $filename)
        {
            if (preg_match("/\/uploads\/.*$/", $filename)) {
                $pages = array_merge(
                    $this->getEm()->createQuery('SELECT page FROM Application\Entity\Page page JOIN page.photo photo WHERE photo.location = :location')
                        ->setParameter('location', $filename)->getResult(),
                    array_map(
                        function($x) { return $x->getPage(); },
                        $this->getEm()->createQuery('SELECT photo FROM Application\Entity\Photo photo JOIN photo.page page WHERE photo.photoid = :location')
                            ->setParameter('location', $filename)->getResult()
                    )
                );
                
                foreach($pages as $page)
                {
                    if($page->getPrivate() &&
                        !\Auth\Rights\RightList::hasAny($this->getRights(), $page, \Application\Rights::$friend) &&
                        !\Auth\Rights\RightList::hasAny($this->getRights(), $page, \Application\Rights::$admin))
                    {
                        //return $this->http403();
                        $_GET['files']='/images/user-icon-large.png';
                        //$_GET['resize']='w[150]h[150]f[true]';
                        unset($_GET['crop']);
                    }
                }
                
            }
        }
        

        echo Dispatcher::run(new Request($options));
        exit();
    }
}