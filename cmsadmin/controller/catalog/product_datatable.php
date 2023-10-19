<?php
class ControllerCatalogProductDatatable extends Controller {
	const FILTERS = array(
		'filter_name'     		=> 'text',
		'filter_model' 	  		=> 'text',
		'filter_category'	 	=> 'text',
		'filter_category_id'	=> 'hidden',
		'filter_manufacturer'	=> 'text',
		'filter_manufacturer_id'=> 'hidden',
		'filter_price'			=> 'text',
		'filter_quantity'		=> 'text',
		'filter_sort_order'		=> 'text',
		'filter_status'			=> 'select',
		'filter_stock_status'	=> 'select',
	);
	
	const SORTS =   ['', '', 'pd.name', 'p.model', '', '', '', 'p.price','p.weight','' ,'p.quantity','p.status','p.sort_order'];

	private $error = array();

	public function index() {
		$this->load->language('catalog/product');
		$this->load->language('extension/module/product_datatable');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->document->addStyle('view/javascript/datatables/DataTables-1.10.22/css/dataTables.bootstrap.min.css');
		$this->document->addStyle('view/javascript/datatables/common.css');
		$this->document->addScript('view/javascript/datatables/DataTables-1.10.22/js/jquery.dataTables.min.js');
		$this->document->addScript('view/javascript/datatables/DataTables-1.10.22/js/dataTables.bootstrap.min.js');

		$this->load->model('catalog/product');

		$data['hidden_columns'] = json_decode(file_get_contents(DIR_STORAGE . 'icop/' .  'datatable_hidden_columns.json'), true);
				
		$sort = isset($this->request->get['sort']) ? $this->request->get['sort'] : 'pd.name';
		$order = isset($this->request->get['order']) ? $this->request->get['order'] : 'asc';
		$page = isset($this->request->get['page']) ? $this->request->get['page'] : 1;
		
		$url = '';
		
		foreach (self::FILTERS as $key => $value) {
			if (isset($this->request->get[$key])) {
				$$key = $this->request->get[$key];
				if (!empty($this->request->get[$key])) {
					$url .= '&' . $key . '=' . urlencode(html_entity_decode($this->request->get[$key], ENT_QUOTES, 'UTF-8'));
				}
			} else {
				$$key = '';
			}
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['add'] = $this->url->link('catalog/product/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['copy'] = $this->url->link('catalog/product/copy', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('catalog/product/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$this->load->model('localisation/stock_status');
		$data['stock_statuses'] = array();
		foreach( $this->model_localisation_stock_status->getStockStatuses() as $stock_status ) {
			$data['stock_statuses'][] = ['id' => $stock_status['stock_status_id'], 'name' => $stock_status['name']];
		}

		$data['statuses'] = array(
			array('id' => 0, 'name' => $this->language->get('text_disabled')),
			array('id' => 1, 'name' => $this->language->get('text_enabled')),
		);
		

		$this->load->model('tool/image');
		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		foreach (self::FILTERS as $key => $value) {
			$data[$key] = $$key;
		}
		
		$data['filters'] = self::FILTERS;
 		
		$data['sort'] = array_search($sort, self::SORTS);
		$data['order'] = $order;

		$data['columns'] = $this->config->get('module_product_datatable_columns');
		
		$data['start'] = $page * 10 - 10;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/product_datatable_list', $data));
	}

	public function getData() {
		        
        $url = '&page=' . ($this->request->get['start'] / $this->request->get['length'] + 1);

		foreach (self::FILTERS as $key => $value) {
			if ( (isset($this->request->get[$key]) && ($key !== 'filter_status') && !empty($this->request->get[$key])) || (($key == 'filter_status') && ($this->request->get[$key] !== ''))) {
				$url .= '&' . $key . '=' . urlencode(html_entity_decode($this->request->get[$key], ENT_QUOTES, 'UTF-8'));
			}
		}

		if (isset($this->request->get['order'])) {
			$sort = self::SORTS[$this->request->get['order'][0]['column']];
			$order = $this->request->get['order'][0]['dir'];
			$url .= '&sort=' . $sort . '&order=' . $order;
		} 

		$filter_data = array(
			'sort'            => $sort,
			'search'		  => $this->request->get['search']['value'],
			'order'           => utf8_strtoupper($order),
			'start'           => $this->request->get['start'],
			'limit'           => $this->request->get['length']
		);
		
		foreach(self::FILTERS as $key => $filter) {
			if (isset($this->request->get[$key]) && !is_null($this->request->get[$key])) {
				$$key = $this->request->get[$key];
			} else {
				$$key = '';
			}

			$filter_data[$key] = $$key;
		}
		

		$this->load->model('catalog/product');
		$this->load->model('catalog/category');
		$this->load->model('catalog/manufacturer');
		$this->load->model('tool/image');

		$page = ($this->request->get['start'] /  $this->request->get['length'] + 1);

		$this->load->model('localisation/stock_status');
		$data['stock_statuses'] = $this->model_localisation_stock_status->getStockStatuses();
		
		$product_total = $this->model_catalog_product->getTotalProducts($filter_data);
		$results = $this->model_catalog_product->getProducts($filter_data);
		
		$data['products'] = array();
		
		foreach ($results as $result) {
			$categories = $this->model_catalog_product->getProductCategories($result['product_id']);
			$product_categories = array();

			foreach ($categories as $category_id) {
				$category_info = $this->model_catalog_category->getCategory($category_id);

				if ($category_info) {
					$product_categories[] = array(
						'category_id' => $category_info['category_id'],
						'name'		  => $category_info['name'],
						'path'        => ($category_info['path']) ? $category_info['path'] . ' &gt; ' . $category_info['name'] : $category_info['name']
					);
				}
			}

			$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($result['manufacturer_id']);

			if (is_file(DIR_IMAGE . $result['image'])) {
				$image = $this->model_tool_image->resize($result['image'], 40, 40);
			} else {
				$image = $this->model_tool_image->resize('no_image.png', 40, 40);
			}

			$special = false;

			$product_specials = $this->model_catalog_product->getProductSpecials($result['product_id']);

			foreach ($product_specials  as $product_special) {
				if (($product_special['date_start'] == '0000-00-00' || strtotime($product_special['date_start']) < time()) && ($product_special['date_end'] == '0000-00-00' || strtotime($product_special['date_end']) > time())) {
					$special = $this->currency->format($product_special['price'], $this->config->get('config_currency'));

					break;
				}
			}

			$data['products'][] = array(
				'DT_RowId'   	 => 'productId-' . $result['product_id'],
				'product_id'   	 => $result['product_id'],
				'image'      	 => $image,
				'name'       	 => $result['name'],
				'model'      	 => $result['model'],
				'description'    => $result['description'],
				'quantity'		 => $result['quantity'],
				'price'      	 => $this->currency->format($result['price'], $this->config->get('config_currency')),
				'special'    	 => $special,
				'stock_status'   => $this->model_localisation_stock_status->getStockStatus($result['stock_status_id']),
				'category' 	 => $product_categories,
				'manufacturer_id'=> $result['manufacturer_id'],
				'weight'		 => (float)$result['weight'],
				'manufacturer'	 => $manufacturer_info ? $manufacturer_info['name'] : $this->language->get('text_none'),
				'status'     	 => $result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
				'sort_order'	 => $result['sort_order'],
				'action'       	 => array(
					'view'	=> ($this->request->server['HTTPS'] ? HTTPS_CATALOG : HTTP_CATALOG) .'index.php?route=product/product&product_id=' . $result['product_id'],
					'edit'	=> $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $result['product_id'] . $url, true)
				)
			);
		}

		$response['draw'] = $this->request->get['draw'];
		$response['page'] = $this->request->get['start'];
		$response['recordsTotal'] = $this->model_catalog_product->getTotalProducts([]);
		$response['recordsFiltered'] = $product_total;
		$response['data'] = $data['products'];

		$response['copy'] =  str_replace('&amp;', '&', $this->url->link('catalog/product/copy', 'user_token=' . $this->session->data['user_token'] . $url, true));
		$response['delete'] =  str_replace('&amp;', '&', $this->url->link('catalog/product/delete', 'user_token=' . $this->session->data['user_token'] . $url, true));


		$this->response->addHeader('application/json');
		$this->response->setOutput(json_encode($response));
	}

	public function editData() {
		$json = array();
		
		if($this->request->server['REQUEST_METHOD'] == 'POST') {
			$this->load->model('catalog/product');
			$this->load->language('catalog/product');
			if (isset($this->request->post['key'])) {
				if($this->request->post['key'] === 'model' && ((utf8_strlen($this->request->post['value']) < 1) || (utf8_strlen($this->request->post['value']) > 64))) {
					$json['error'] = $this->language->get('error_model');
				}
		
				if($this->request->post['key'] === 'name' && ((utf8_strlen($this->request->post['value']) < 1) || (utf8_strlen($this->request->post['value']) > 255))) {
					$json['error'] = $this->language->get('error_name');
				}
			}

			if (!$json) {
				if ($this->request->get['action'] === 'delete_category') {
					$this->model_catalog_product->deleteCategory($this->request->post);
				} elseif ($this->request->get['action'] === 'add_category') {
					$this->model_catalog_product->editProductCategories($this->request->post);
				} else {
					$this->model_catalog_product->updateField($this->request->post);
				}
			}
		}

		$this->response->addHeader('application/json');
		$this->response->setOutput(json_encode($json));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'catalog/product')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}

	public function openModal() {
		$this->load->language('catalog/product');
		$this->load->model('catalog/product');

		$data['title'] = $this->language->get('entry_' . $this->request->get['field']);
		$data['content'] = $this->model_catalog_product->getProduct($this->request->get['product_id']);

		$this->response->setOutput($this->load->view('catalog/product_modal_form',$data));
	}

	public function hiddenColumnsDatatable() {
		if ($this->request->server['REQUEST_METHOD'] == 'POST') {

			$columns = json_decode(file_get_contents(DIR_STORAGE . 'icop/' .  'datatable_hidden_columns.json'), true);

			if (is_array($columns) && in_array($this->request->post['column'], $columns)) {
				$key = array_search($this->request->post['column'], $columns);
				unset($columns[$key]);
			} else {
				$columns[] = $this->request->post['column'];
			}

			$file = fopen(DIR_STORAGE . 'icop/' .  'datatable_hidden_columns.json', 'w');
			fwrite($file, json_encode($columns));
			fclose($file);			
		}		
	}
}
