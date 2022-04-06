<?php
namespace Base\Router\Http;

use Traversable;
use Zend\Mvc\Router\Exception;
use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\RequestInterface as Request;
use Zend\Mvc\Router\Http\RouteInterface;
use Zend\Mvc\Router\Http\RouteMatch;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * DatabaseLiteral route.
 */
class DatabaseLiteral implements RouteInterface, ServiceLocatorAwareInterface
{
    protected $services;

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->services = $serviceLocator;
    }

	/**
	 * @return ServiceLocatorInterface
	 */
    public function getServiceLocator()
    {
        return $this->services;
    }

    /**
     * Default values.
     *
     * @var array
     */
    protected $defaults;


    /**
     * Entity to match.
     *
     * @var string
     */
    protected $entity;


    /**
     * Entity column to match
     *
     * @var string
     */
    protected $column;

    /**
     * Entity parname to match
     *
     * @var string
     */
    protected $parname;


    /**
     * Create a new database literal route.
     *
     * @param  string $entity
     * @param  string $column
     * @param  string $parname
     * @param  array  $defaults
     */
    public function __construct($entity, $column, $parname, array $defaults = array())
    {
        $this->entity   = $entity;
        $this->column   = $column;
        $this->parname   = $parname;
        $this->defaults = $defaults;
    }

    /**
     * factory(): defined by RouteInterface interface.
     *
     * @see    \Zend\Mvc\Router\RouteInterface::factory()
     * @param  array|Traversable $options
     * @return Literal
     * @throws Exception\InvalidArgumentException
     */
    public static function factory($options = array())
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        } elseif (!is_array($options)) {
            throw new Exception\InvalidArgumentException(__METHOD__ . ' expects an array or Traversable set of options');
        }

        if (!isset($options['entity'])) {
            throw new Exception\InvalidArgumentException('Missing "entity" in options array');
        }

        if (!isset($options['column'])) {
            throw new Exception\InvalidArgumentException('Missing "column" in options array');
        }

        if (!isset($options['parname'])) {
            throw new Exception\InvalidArgumentException('Missing "parname" in options array');
        }

        if (!isset($options['defaults'])) {
            $options['defaults'] = array();
        }

        return new static($options['entity'], $options['column'], $options['parname'], $options['defaults']);
    }

    /**
     * match(): defined by RouteInterface interface.
     *
     * @see    \Zend\Mvc\Router\RouteInterface::match()
     * @param  Request      $request
     * @param  integer|null $pathOffset
     * @return RouteMatch|null
     */
    public function match(Request $request, $pathOffset = null)
    {
        if (!method_exists($request, 'getUri')) {
            return null;
        }

        $uri  = $request->getUri();
        $path = $uri->getPath();
        if ($pathOffset !== null)
		{
            if ($pathOffset < 0 || strlen($path) < $pathOffset)
			{
				return null;
			}
			$path = substr($path, $pathOffset);
        }

		list($path) = explode('/', $path);


		$em = $this->getServiceLocator()->getServiceLocator()->get('th_entitymanager');
		$em->getFilters()->disable('soft-deleteable');
		$repo = $em->getRepository($this->entity);
		$obj = $repo->findOneBy(array($this->column => $path));
		$em->getFilters()->enable('soft-deleteable');

		if ($obj) 
		{
			$this->defaults[$this->parname] = $obj;
            return new RouteMatch($this->defaults, \strlen(\call_user_func(array($obj, 'get'.$this->column))) );
        }

        return null;
    }

    /**
     * assemble(): Defined by RouteInterface interface.
     *
     * @see    \Zend\Mvc\Router\RouteInterface::assemble()
     * @param  array $params
     * @param  array $options
     * @return mixed
     */
    public function assemble(array $params = array(), array $options = array())
    {
		if (! isset($params[$this->parname]) || ! ($params[$this->parname] instanceof $this->entity) || !method_exists($params[$this->parname], 'get'.$this->column))
		{
			return null;
		}
		
		return \call_user_func(array($params[$this->parname], 'get'.$this->column));
    }

    /**
     * getAssembledParams(): defined by RouteInterface interface.
     *
     * @see    RouteInterface::getAssembledParams
     * @return array
     */
    public function getAssembledParams()
    {
        return array($this->parname);//
    }
}
