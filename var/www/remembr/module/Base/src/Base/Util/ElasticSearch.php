<?php

namespace Base\Util;

class ElasticSearch
{
	private $url;
	public function __construct($host, $port, $index) {
		$this->url = 'http://'.$host.':'.$port.'/'.$index.'/';
	}

	private function request($endpoint, $method, array $data) {
		$ch = curl_init($endpoint);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		if ($data != null) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
		}

		$response = curl_exec($ch);
		if ($response === false) {
			$error = curl_error($ch);
			throw new Exception('Could not retrieve ElasticSearch response, error is '.$error);
		}
		return json_decode($response, true);
	}

	public function get($type, $id, $routing = null) {
		return $this->request($this->url.$type.'/'.$id.($routing != null ? '?routing='.$routing : ''), 'GET', null);
	}

	public function put($doc, $type, $id, $routing = null, $refresh = null)
	{
		$qs = http_build_query(array('routing'=>$routing, 'refresh'=>$refresh));
		return $this->request($this->url.$type.'/'.$id.($qs ? '?'.$qs: ''), 'PUT', $doc);
	}

	public function update($update, $type, $id, $routing = null, $refresh = null)
	{
		$qs = http_build_query(array('routing'=>$routing, 'refresh'=>$refresh));
		return $this->request($this->url.$type.'/'.$id.'/_update'.($qs ? '?'.$qs : ''), 'POST', $update);
	}

	public function search($query, $type=null) {
		return $this->request($this->url.($type!=null ? $type.'/' : '' ).'_search', 'GET', $query);
	}

	private static $settingKeys = array('_id', '_routing');

	public function bulkIndex($type, array $items, $idKey = null) {
		$data = '';
		foreach ($items as $item) {
			$settings = array();
			foreach (self::$settingKeys as $key) {
				if (isset($item[$key])) {
					$settings[$key] = $item[$key];
					unset($item[$key]);
				}
			}
			if ($idKey != null && isset($item[$idKey])) {
				$settings['_id'] = $item[$idKey];
			}
			$data.= '{"index":'.json_encode($settings)."}\n".json_encode($item)."\n";
		}
		$this->request($this->url.$type.'/_bulk', 'POST', $data);
	}

	public function changeSettings(array $settings) {
		$this->request($this->url.'_settings', 'PUT', $settings);
	}
}

?>
