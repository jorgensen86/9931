<?php
class ControllerExtensionModuleProductDatatable extends Controller {
	const INPUTS = ['image', 'name', 'model', 'description', 'category', 'manufacturer', 'price' , 'weight', 'stock_status', 'quantity', 'status', 'sort_order', 'action'];

	private $error = array();

	public function index() {
		$this->load->language('extension/module/product_datatable');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('module_product_datatable', $this->request->post);
			
			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
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
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/product_datatable', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/module/product_datatable', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->post['module_product_datatable_status'])) {
			$data['module_product_datatable_status'] = $this->request->post['module_product_datatable_status'];
		} else {
			$data['module_product_datatable_status'] = $this->config->get('module_product_datatable_status');
		}

		if (isset($this->request->post['module_product_datatable_columns'])) {
			$data['module_product_datatable_columns'] = $this->request->post['module_product_datatable_columns'];
		} else {
			$data['module_product_datatable_columns'] = $this->config->get('module_product_datatable_columns');
		}

		$data['fields'] = self::INPUTS;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/product_datatable', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/product_datatable')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		return !$this->error;
	}
}