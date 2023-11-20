<?php
class ControllerExtensionModuleCustomerGroupPrice extends Controller {
	const INPUTS = ['tax', 'option', 'delete', 'status'];

	private $error = array();

	public function index() {
		$this->load->language('extension/module/customer_group_price');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('module_customer_group_price', $this->request->post);

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
			'href' => $this->url->link('extension/module/customer_group_price', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/module/customer_group_price', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		$data['delete'] = $this->url->link('extension/module/customer_group_price/delete', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		$this->load->model('customer/customer_group');

		$data['customer_groups'] = $this->model_customer_customer_group->getCustomerGroups();

		foreach (self::INPUTS as $key => $value) {
			if (isset($this->request->post['module_customer_group_price_' . $value])) {
				$data['module_customer_group_price_' . $value] = $this->request->post['module_customer_group_price_' . $value];
			} else {
				$data['module_customer_group_price_' . $value] = $this->config->get('module_customer_group_price_' . $value);
			}
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/customer_group_price', $data));
	}

	public function delete() {
		if ($this->validate()) {
			$this->load->language('extension/module/customer_group_price');

			$this->load->model('extension/module/customer_group_price');
			$this->model_extension_module_customer_group_price->deleteData();

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		} else {
			$this->response->redirect($this->url->link('error/not_found', 'user_token=' . $this->session->data['user_token'], true));
		}
	}

	public function getCustomerGroupPrices(&$route, &$data) {
		if(!$this->config->get('module_customer_group_price_status')) return;

		$this->load->language('extension/module/customer_group_price');
		$data['entry_customer_group_price'] = $this->language->get('heading_title');
				
		$this->load->model('extension/module/customer_group_price');

		if(isset($this->request->post['cgp'])) {
			$data['cgp'] = $this->request->post['cgp'];
		} elseif (isset($this->request->get['product_id'])) {
			$data['cgp'] = $this->model_extension_module_customer_group_price->getProductPrices($this->request->get['product_id']);
		} else {
			$data['cgp'] = array();
		}
	}
	
	public function editCustomerGroupPrices(&$route, &$data, $args) {
		if(!$this->config->get('module_customer_group_price_status')) return;
		
		$this->load->model('extension/module/customer_group_price');
		$params = array();
		$product_id = '';
		
		if($route === 'catalog/product/addProduct') {
			$product_id = $args;
			$params = $data[0]['cgp'];
		} elseif ($route === 'catalog/product/editProduct') {
			$product_id = $data[0];
			$params = $data[1]['cgp'];
		} elseif ($route === 'catalog/product/deleteProduct') {
			$product_id = $data[0];
		}

		$this->model_extension_module_customer_group_price->editCustomerGroupPrices($product_id, $params);
	}

	public function editProductForm(&$route, &$data, &$output) {
		if(!$this->config->get('module_customer_group_price_status')) return;
		$html = $this->load->view('extension/module/cusomter_group_price/product_form', $data);
		$option = $this->load->view('extension/module/cusomter_group_price/option_form', $data);
		
		$output = preg_replace('/<div class="form-group">\s*<label class="col-sm-2 control-label" for="input-tax-class">/', $html . '<div class="form-group"><label class="col-sm-2 control-label" for="input-tax-class">', $output);
		
		// preg_match_all('/\[price\]" value="" placeholder="\S+" class="form-control" \/>/', $output, $matches);
		// $output = str_replace($matches[0]['2'], $matches[0]['2'] . $option, $output);
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/customer_group_price')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		return !$this->error;
	}

	public function install() {
		$this->load->model('extension/module/customer_group_price');
		$this->model_extension_module_customer_group_price->install();
		
		$this->load->model('setting/event');
		$this->model_setting_event->addEvent('admin_customer_group_price', 'admin/view/catalog/product_form/before', 'extension/module/customer_group_price/getCustomerGroupPrices');
		$this->model_setting_event->addEvent('admin_customer_group_price', 'admin/view/catalog/product_form/after', 'extension/module/customer_group_price/editProductForm');
		$this->model_setting_event->addEvent('admin_customer_group_price', 'admin/model/catalog/product/addProduct/after', 'extension/module/customer_group_price/editCustomerGroupPrices');
		$this->model_setting_event->addEvent('admin_customer_group_price', 'admin/model/catalog/product/editProduct/after', 'extension/module/customer_group_price/editCustomerGroupPrices');
		$this->model_setting_event->addEvent('admin_customer_group_price', 'admin/model/catalog/product/deleteProduct/after', 'extension/module/customer_group_price/editCustomerGroupPrices');
		$this->model_setting_event->addEvent('catalog_customer_group_price', 'catalog/model/catalog/product/getProduct/after', 'extension/module/customer_group_price/getProductPrice');
	}

	public function uninstall() {
		if (!$this->config->get('module_customer_group_price_delete')) {
			$this->load->model('extension/module/customer_group_price');
			$this->model_extension_module_customer_group_price->uninstall();
		}
		$this->load->model('setting/event');
		$this->model_setting_event->deleteEventByCode('admin_customer_group_price');
		$this->model_setting_event->deleteEventByCode('catalog_customer_group_price');
	}
}