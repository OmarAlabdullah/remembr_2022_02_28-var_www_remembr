<?php

namespace Base\Rights;

use Gedmo\Tool\Wrapper\EntityWrapper;

class ExtMaterializedPathRepository extends \Gedmo\Tree\Entity\Repository\MaterializedPathRepository
{
	public function getPathQueryBuilder($node) {
		$meta = $this->getClassMetadata();
		$config = $this->listener->getConfiguration($this->_em, $meta->name);
		$separator = addcslashes($config['path_separator'], '%');
		$path = $config['path'];
		$alias = 'materialized_path_entity';

		if (!is_object($node) || !($node instanceof $meta->name))
			return null;

		$node = new EntityWrapper($node, $this->_em);
		$ids = explode($separator, $node->getPropertyValue($path));
		$ids = array_slice($ids, 0, count($ids)-2);
			// The last element of $ids is always empty (due to the trailing separator), and the last id is always the
			// node we got as a parameter - no need to query that.

		$qb = $this->_em->createQueryBuilder($meta->name)
				->select($alias)
				->from($meta->name, $alias);
		$qb->where($qb->expr()->in($alias.'.'.$meta->getSingleIdentifierFieldName(), $ids));
		return $qb;
	}

	public function getPathQuery($node) {
		return $this->getPathQueryBuilder($node)->getQuery();
	}

	/**
	 * Queries and returns a list of all the parent nodes of a given node to the root. The given node is not returned.
	 * Generally the return value can be discarded, and getParent() (or whatever the parent property getter is) can
	 * be used to traverse the hierarchy.
	 *
	 * This method increases efficiency before doing a parent-to-parent traversal.
	 */
	public function getPath($node) {
		return $this->getPathQuery($node)->execute();
	}
}

?>
