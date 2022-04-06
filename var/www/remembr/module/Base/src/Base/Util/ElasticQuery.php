<?php

namespace Base\Util;

/**
 * Assistant class to simplify building queries. Still very simple at this point.
 */
class ElasticQuery {
	private $parts = array();

	/**
	 * Short-named function to create a new instance, so you can immediately chain methods.
	 *
	 * @return \ElasticQuery
	 */
	public static function q() {
		return new ElasticQuery();
	}

	public function query(array $data) {
		$this->parts['query'] = $data;
		return $this;
	}

	public function sort(array $data) {
		$this->parts['sort'] = $data;
		return $this;
	}

	public function filter(array $data) {
		$this->parts['filter'] = $data;
		return $this;
	}

	public function get() { return $this->parts; }
}


?>
