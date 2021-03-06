<?php

/**
 * Class ControllerExtensionPaymentPayop
 *
 * @property Loader                             $load
 * @property Document                           $document
 * @property ModelSettingSetting                $model_setting_setting
 * @property Request                            $request
 * @property Response                           $response
 * @property Session                            $session
 * @property Language                           $language
 * @property Url                                $url
 * @property Config                             $config
 * @property ModelLocalisationGeoZone           $model_localisation_geo_zone
 * @property ModelLocalisationOrderStatus       $model_localisation_order_status
 * @property ModelExtensionPaymentPayop         $model_extension_payment_payop
 * @property Cart\User                          $user
 */
class ControllerExtensionPaymentPayop extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/payment/payop');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('payment_payop', $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link('marketplace/extension',
				'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data = array(
			'error_public_id'  => '',
			'error_secret_key' => '',
		);
		foreach ($this->error as $f => $v) {
			$data['error_' . $f] = $v;
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension',
				'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/payop',
				'user_token=' . $this->session->data['user_token'],
				true)
		);

		$data['action'] = $this->url->link('extension/payment/payop',
			'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension',
			'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

		$fields = array(
			'payment_payop_public_id',
			'payment_payop_secret_key',
			'payment_payop_order_status_wait',
			'payment_payop_order_status_success',
			'payment_payop_order_status_error',
			'payment_payop_status'
		);

		foreach ($fields as $f) {
			if (isset($this->request->post[$f])) {
				$data[$f] = $this->request->post[$f];
			} else {
				$data[$f] = $this->config->get($f);
			}
		}

        $data['ipn_url'] = HTTP_CATALOG . 'index.php?route=extension/payment/payop/callback';

		$this->load->model('localisation/order_status');
		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['payment_payop_geo_zone_id'])) {
			$data['payment_payop_geo_zone_id'] = $this->request->post['payment_payop_geo_zone_id'];
		} else {
			$data['payment_payop_geo_zone_id'] = $this->config->get('payment_payop_geo_zone_id');
		}
		$this->load->model('localisation/geo_zone');
		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['payment_payop_sort_order'])) {
			$data['payment_payop_sort_order'] = $this->request->post['payment_payop_sort_order'];
		} else {
			$data['payment_payop_sort_order'] = $this->config->get('payment_payop_sort_order');
		}

		$data['header']      = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer']      = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/payop', $data));
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/payop')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		$required_fields = array(
			'public_id',
			'secret_key'
		);

		foreach ($required_fields as $f) {
			if (!$this->request->post['payment_payop_' . $f]) {
				$this->error[$f] = $this->language->get('error_' . $f);
			}
		}

		return !$this->error;
	}

	public function install() {
		$this->load->model('extension/payment/payop');
		$this->model_extension_payment_payop->install();
	}

	public function uninstall() {
		$this->load->model('extension/payment/payop');
		$this->model_extension_payment_payop->uninstall();
	}
}