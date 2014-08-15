<?php

App::import('Vendor', 'GoogleApiClientLibrary/Google/Client');
App::import('Vendor', 'GoogleApiClientLibrary/Google/Auth/AssertionCredentials');
App::import('Vendor', 'GoogleApiClientLibrary/Google/Service/Analytics');

class AnalyticsController extends AppController {

	public $name = 'Analytics';
	public $uses = [];
	public $components = [];


	public function index() {

		$client = $this->login();
		$result = $this->get($client, 'ga:pageviews');
		$data['PV'] = $result['rows'][0];
		$result = $this->get($client, 'ga:users');
		$data['Uni'] = $result['rows'][0];
		$result = $this->get($client, 'ga:totalEvents', 'ga:eventLabel');
		$data['Imp'][0] = $result['totalsForAllResults']['ga:totalEvents'];
		$data['call'][0] = 22;

		pr($data);

	}

	private function get($client, $metrics, $dimensions=null, $sort=null) {

		$service = new Google_Service_Analytics($client);
		return $service->data_ga->get(
			'ga:' . Configure::read('ProfileIDs')['edsnv.com'], // XXXXX の部分は Analytics のビュー ID
			'2014-08-01', // 開始日
			'2014-08-01', // 終了日
			$metrics, // 主要指標 (metrics)
			array(
				'dimensions' => $dimensions, // 副指標
				'filters' => "ga:eventLabel=~相談電話_imp",
				// 'sort' => '-ga:totalEvents', // - を付けると降順ソート
				'max-results' => 10, // 取得件数
			)
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

		return $client;
	}
}
