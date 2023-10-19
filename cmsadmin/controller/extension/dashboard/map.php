<?php
class ControllerExtensionDashboardMap extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/dashboard/map');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('dashboard_map', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=dashboard', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=dashboard', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/dashboard/map', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/dashboard/map', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=dashboard', true);

		if (isset($this->request->post['dashboard_map_width'])) {
			$data['dashboard_map_width'] = $this->request->post['dashboard_map_width'];
		} else {
			$data['dashboard_map_width'] = $this->config->get('dashboard_map_width');
		}

		$data['columns'] = array();
		
		for ($i = 3; $i <= 12; $i++) {
			$data['columns'][] = $i;
		}
				
		if (isset($this->request->post['dashboard_map_status'])) {
			$data['dashboard_map_status'] = $this->request->post['dashboard_map_status'];
		} else {
			$data['dashboard_map_status'] = $this->config->get('dashboard_map_status');
		}

		if (isset($this->request->post['dashboard_map_sort_order'])) {
			$data['dashboard_map_sort_order'] = $this->request->post['dashboard_map_sort_order'];
		} else {
			$data['dashboard_map_sort_order'] = $this->config->get('dashboard_map_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/dashboard/map_form', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/dashboard/map')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
		
	public function dashboard() {
		$this->load->language('extension/dashboard/map');
		$this->load->language('common/column_left');

		// Stats
		$this->load->model('sale/order');
		
		$order_total = $this->model_sale_order->getTotalOrders();
		
		$this->load->model('report/statistics');

		$this->model_report_statistics->editValue('order_processing', $this->model_sale_order->getTotalOrders(array('filter_order_status' => implode(',', $this->config->get('config_processing_status')))));

		$this->model_report_statistics->editValue('order_complete', $this->model_sale_order->getTotalOrders(array('filter_order_status' => implode(',', $this->config->get('config_complete_status')))));

		$this->load->model('localisation/order_status');
				
		$order_status_data = array();

		$results = $this->model_localisation_order_status->getOrderStatuses();
	
		foreach ($results as $result) {
			if (!in_array($result['order_status_id'], array_merge($this->config->get('config_complete_status'), $this->config->get('config_processing_status')))) {
				$order_status_data[] = $result['order_status_id'];
			}
		}		
			
		$this->model_report_statistics->editValue('order_other', $this->model_sale_order->getTotalOrders(array('filter_order_status' => implode(',', $order_status_data))));
		
		$complete_total = $this->model_report_statistics->getValue('order_complete');
		
		if ((float)$complete_total && $order_total) {
			$statuses[] = round(($complete_total / $order_total) * 100);
		} else {
			$statuses[] = 0;
		}

		$processing_total = $this->model_report_statistics->getValue('order_processing');
		
		if ((float)$processing_total && $order_total) {
			$statuses[] = round(($processing_total / $order_total) * 100);
		} else {
			$statuses[] = 0;
		}
		
		$other_total = $this->model_report_statistics->getValue('order_other');
		
		if ((float)$other_total && $order_total) {
			$statuses[] = round(($other_total / $order_total) * 100);
		} else {
			$statuses[] = 0;
		}

		$data['order_statuses'] = json_encode($statuses);
		$data['labels'] = json_encode([$this->language->get('text_complete_status'), $this->language->get('text_processing_status'), $this->language->get('text_other_status')]);

		$data['user_token'] = $this->session->data['user_token'];
		
		return $this->load->view('extension/dashboard/map_info', $data);
	}
}
