<?php

App::import('Vendor', 'GoogleApiClientLibrary/Google/Client');
App::import('Vendor', 'GoogleApiClientLibrary/Google/Auth/AssertionCredentials');
App::import('Vendor', 'GoogleApiClientLibrary/Google/Service/Analytics');

class AnalyticsController extends AppController {

	public $name = 'Analytics';
	public $uses = [];
	public $components = [];
	public $client;


	public function index() {

		$service = $this->login();
		$values = [
			'ga' => Configure::read('ProfileIDs')['PC個別ID'],
			'between' => ['start' => '2014-08-27', 'end' => '2014-08-27'],
		];
		$result = $this->getPv($service, $values);
		$data['PC']['PV'] = $result['rows'][0];
		$result = $this->getSession($service, $values);
		$data['PC']['Session'] = $result['rows'][0];

		$values = [
			'ga' => Configure::read('ProfileIDs')['Mobile個別ID'],
			'between' => ['start' => '2014-08-27', 'end' => '2014-08-27'],
		];
		$result = $this->getPv($service, $values);
		$data['Mobile']['PV'] = $result['rows'][0];
		$result = $this->getSession($service, $values);
		$data['Mobile']['Session'] = $result['rows'][0];

		pr($data);

	}

	private function getPv($service, $values) {

		return $service->data_ga->get(
			'ga:' . $values['ga'], // XXXXX の部分は Analytics のビュー ID
			$values['between']['start'], // 開始日
			$values['between']['end'], // 終了日
			'ga:pageviews', // 主要指標 (metrics)
			[
				'dimensions' => null, // 副指標
				'max-results' => 10, // 取得件数
			]
		);
	}

	private function getSession($service, $values) {

		return $service->data_ga->get(
			'ga:' . $values['ga'], // XXXXX の部分は Analytics のビュー ID
			$values['between']['start'], // 開始日
			$values['between']['end'], // 終了日
			'ga:visits', // 主要指標 (metrics)
			[
				// 'dimensions' => 'ga:visitCount', // 副指標
				'max-results' => 10, // 取得件数
			]
		);
	}

	private function login() {

		$client = new Google_Client();
		$client->setApplicationName("ModelnismoMedia");
		$client->setClientId(Configure::read('ClientID'));
		$client->setAssertionCredentials(new Google_Auth_AssertionCredentials(
			Configure::read('ServiceAccountName'),
			array('https://www.googleapis.com/auth/analytics.readonly'),
			file_get_contents(WWW_ROOT . '/files/' . Configure::read('KeyFile'))
		));

		return new Google_Service_Analytics($client);
	}
}
