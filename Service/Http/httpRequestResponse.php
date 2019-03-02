<?php

namespace Service\Http;
require_once __ROOT__ . '/vendor/autoload.php';
use Nette\Http\RequestFactory;
use Service\Service;

// https://github.com/nette/http/blob/master/tests/Http/Request.request.phpt#L22
// Nette\Http\Request
// Nette\Http\UrlScript
// Nette\Http\Url
class httpRequestResponse extends Service {

	const HEADERS = [
		'json' => 'application/json',
		'octet' => 'application/octet-stream',
		'form_urlencoded' => 'application/x-www-form-urlencoded',
		'form_data' => 'multipart/form-data',
	];
	private $factory;
	private $request;
	private $header_content_type;

	public function __construct() {
		parent::__construct();
		$this->factory = new RequestFactory();
		$this->request = $this->factory->createHttpRequest();
	}

	public function getUrlScript() {
		return $this->request->getUrl();
	}

	public function getAbsoluteUrl() {
		return $this->getUrlScript()->getAbsoluteUrl();
	}

	public function getBaseUrl() {
		return $this->getUrlScript()->getBaseUrl();
	}

	public function getHostUrl() {
		return $this->getUrlScript()->getHostUrl();
	}

	public function getRelativeUrl() {
		return $this->getUrlScript()->getRelativeUrl();
	}

	public function getPath() {
		return $this->getUrlScript()->getPath();
	}

	public function getQuery() {
		return $this->getUrlScript()->getQuery();
	}

	public function getHeaders() {
		return $this->request->getHeaders();
	}

	public function getMethod() {
		return strtoupper($this->request->getMethod());
	}

	public function getCookies() {
		return $this->request->getCookies();
	}

	public function getHeader($header, $default = NULL) {
		if (isset($default)) {
			return $this->request->getHeader($header, $default);
		} else {
			return $this->request->getHeader($header);
		}
	}

	public function getReferer() {
		return $this->request->getReferer();
	}

	public function getPost($key = NULL, $default = NULL) {
		if (func_num_args() === 0) {
			return $this->request->getPost();
		} else if (isset($key)) {
			if (isset($default)) {
				return $this->request->getPost($key, $default);
			} else {
				return $this->request->getPost($key);
			}
		}
	}

	public function formatHeaderContentType($content_type = '') {
		$exploder = explode(';', $content_type);
		$this->header_content_type = $exploder[0];
		return $this->header_content_type;
	}

	public function allowHeaderContentType() {
		$allow = false;
		in_array($this->header_content_type, array_values(self::HEADERS)) && $allow = true;
		return $allow;
	}

}