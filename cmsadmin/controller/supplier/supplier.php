<?php

class ControllerSupplierSupplier extends Controller {
    private $error = array();

    public function index() {
        $this->load->language('supplier/supplier');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('supplier/supplier');

		$this->getList();
    }

	public function add() {
		$this->load->language('supplier/supplier');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('supplier/supplier');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_supplier_supplier->addSupplier($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_company_name'])) {
				$url .= '&filter_company_name=' . urlencode(html_entity_decode($this->request->get['filter_company_name'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_email'])) {
				$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_vat_number'])) {
				$url .= '&filter_vat_number=' . urlencode(html_entity_decode($this->request->get['filter_vat_number'], ENT_QUOTES, 'UTF-8'));
			}

            if (isset($this->request->get['filter_telephone'])) {
				$url .= '&filter_telephone=' . urlencode(html_entity_decode($this->request->get['filter_telephone'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_date_added'])) {
				$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
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

			$this->response->redirect($this->url->link('supplier/supplier', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('supplier/supplier');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('supplier/supplier');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_supplier_supplier->editSupplier($this->request->get['supplier_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_company_name'])) {
				$url .= '&filter_company_name=' . urlencode(html_entity_decode($this->request->get['filter_company_name'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_email'])) {
				$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_vat_number'])) {
				$url .= '&filter_vat_number=' . urlencode(html_entity_decode($this->request->get['filter_vat_number'], ENT_QUOTES, 'UTF-8'));
			}

            if (isset($this->request->get['filter_telephone'])) {
				$url .= '&filter_telephone=' . urlencode(html_entity_decode($this->request->get['filter_telephone'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_date_added'])) {
				$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
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

			$this->response->redirect($this->url->link('supplier/supplier', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('supplier/supplier');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('supplier/supplier');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $supplier_id) {
				$this->model_supplier_supplier->deleteSupplier($supplier_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_company_name'])) {
				$url .= '&filter_company_name=' . urlencode(html_entity_decode($this->request->get['filter_company_name'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_email'])) {
				$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_vat_number'])) {
				$url .= '&filter_vat_number=' . urlencode(html_entity_decode($this->request->get['filter_vat_number'], ENT_QUOTES, 'UTF-8'));
			}

            if (isset($this->request->get['filter_telephone'])) {
				$url .= '&filter_telephone=' . urlencode(html_entity_decode($this->request->get['filter_telephone'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_date_added'])) {
				$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
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

			$this->response->redirect($this->url->link('supplier/supplier', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

    protected function getList() {
        if (isset($this->request->get['filter_company_name'])) {
			$filter_company_name = $this->request->get['filter_company_name'];
		} else {
			$filter_company_name = '';
		}

		if (isset($this->request->get['filter_email'])) {
			$filter_email = $this->request->get['filter_email'];
		} else {
			$filter_email = '';
		}

		if (isset($this->request->get['filter_telephone'])) {
			$filter_telephone = $this->request->get['filter_telephone'];
		} else {
			$filter_telephone = '';
		}

		if (isset($this->request->get['filter_vat_number'])) {
			$filter_vat_number = $this->request->get['filter_vat_number'];
		} else {
			$filter_vat_number = '';
		}

        if (isset($this->request->get['filter_date_added'])) {
			$filter_date_added = $this->request->get['filter_date_added'];
		} else {
			$filter_date_added = '';
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'company_name';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['filter_company_name'])) {
			$url .= '&filter_company_name=' . urlencode(html_entity_decode($this->request->get['filter_company_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_email'])) {
			$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_telephone'])) {
			$url .= '&filter_telephone=' . $this->request->get['filter_telephone'];
		}
		if (isset($this->request->get['filter_vat_number'])) {
			$url .= '&filter_vat_number=' . urlencode(html_entity_decode($this->request->get['filter_vat_number'], ENT_QUOTES, 'UTF-8'));
		}

        if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
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
			'href' => $this->url->link('supplier/supplier', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

        $data['add'] = $this->url->link('supplier/supplier/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
        $data['delete'] = $this->url->link('supplier/supplier/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

        $data['suppliers'] = array();

		$filter_data = array(
			'filter_company_name'      => $filter_company_name,
			'filter_email'             => $filter_email,
			'filter_telephone'         => $filter_telephone,
			'filter_vat_number'        => $filter_vat_number,
			'filter_date_added'        => $filter_date_added,
			'sort'                     => $sort,
			'order'                    => $order,
			'start'                    => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'                    => $this->config->get('config_limit_admin')
		);

		$supplier_total = $this->model_supplier_supplier->getTotalSuppliers($filter_data);

		$results = $this->model_supplier_supplier->getSuppliers($filter_data);
        
		foreach ($results as $result) {	
			$data['suppliers'][] = array(
				'supplier_id'    => $result['supplier_id'],
				'company_name'   => $result['company_name'],
				'email'          => $result['email'],
				'vat_number'     => $result['vat_number'],
				'telephone'      => $result['telephone'],
				'date_added'     => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'edit'           => $this->url->link('supplier/supplier/edit', 'user_token=' . $this->session->data['user_token'] . '&supplier_id=' . $result['supplier_id'] . $url, true),
				'order'          => $this->url->link('supplier/supplier/order', 'user_token=' . $this->session->data['user_token'] . '&supplier_id=' . $result['supplier_id'] . $url, true)
			);
		}

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

        $url = '';

		if (isset($this->request->get['filter_company_name'])) {
			$url .= '&filter_company_name=' . urlencode(html_entity_decode($this->request->get['filter_company_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_email'])) {
			$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_telephone'])) {
			$url .= '&filter_telephone=' . $this->request->get['filter_telephone'];
		}
		if (isset($this->request->get['filter_vat_number'])) {
			$url .= '&filter_vat_number=' . urlencode(html_entity_decode($this->request->get['filter_vat_number'], ENT_QUOTES, 'UTF-8'));
		}

        if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

        if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_company_name'] = $this->url->link('supplier/supplier', 'user_token=' . $this->session->data['user_token'] . '&sort=company_name' . $url, true);
		$data['sort_email'] = $this->url->link('supplier/supplier', 'user_token=' . $this->session->data['user_token'] . '&sort=email' . $url, true);
		$data['sort_vat_number'] = $this->url->link('supplier/supplier', 'user_token=' . $this->session->data['user_token'] . '&sort=vat_number' . $url, true);
		$data['sort_telephone'] = $this->url->link('supplier/supplier', 'user_token=' . $this->session->data['user_token'] . '&sort=telephone' . $url, true);
		$data['sort_date_added'] = $this->url->link('supplier/supplier', 'user_token=' . $this->session->data['user_token'] . '&sort=date_added' . $url, true);

        $url = '';

		if (isset($this->request->get['filter_company_name'])) {
			$url .= '&filter_company_name=' . urlencode(html_entity_decode($this->request->get['filter_company_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_email'])) {
			$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_telephone'])) {
			$url .= '&filter_telephone=' . $this->request->get['filter_telephone'];
		}
		if (isset($this->request->get['filter_vat_number'])) {
			$url .= '&filter_vat_number=' . urlencode(html_entity_decode($this->request->get['filter_vat_number'], ENT_QUOTES, 'UTF-8'));
		}

        if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

        $pagination = new Pagination();
		$pagination->total = $supplier_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('supplier/supplier', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($supplier_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($supplier_total - $this->config->get('config_limit_admin'))) ? $supplier_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $supplier_total, ceil($supplier_total / $this->config->get('config_limit_admin')));

		$data['filter_company_name'] = $filter_company_name;
		$data['filter_email'] = $filter_email;
		$data['filter_vat_number'] = $filter_vat_number;
		$data['filter_telephone'] = $filter_telephone;
		$data['filter_date_added'] = $filter_date_added;

        $data['sort'] = $sort;
		$data['order'] = $order;

        $data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('supplier/supplier_list', $data));
    }

    protected function getForm() {
        $data['text_form'] = !isset($this->request->get['supplier_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->get['supplier_id'])) {
			$data['supplier_id'] = $this->request->get['supplier_id'];
		} else {
			$data['supplier_id'] = 0;
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['company_name'])) {
			$data['error_company_name'] = $this->error['company_name'];
		} else {
			$data['error_company_name'] = '';
		}

		if (isset($this->error['vat_number'])) {
			$data['error_vat_number'] = $this->error['vat_number'];
		} else {
			$data['error_vat_number'] = '';
		}

		if (isset($this->error['telephone'])) {
			$data['error_telephone'] = $this->error['telephone'];
		} else {
			$data['error_telephone'] = '';
		}

		$url = '';

		if (isset($this->request->get['filter_company_name'])) {
			$url .= '&filter_company_name=' . urlencode(html_entity_decode($this->request->get['filter_company_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_email'])) {
			$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_telephone'])) {
			$url .= '&filter_telephone=' . $this->request->get['filter_telephone'];
		}
		if (isset($this->request->get['filter_vat_number'])) {
			$url .= '&filter_vat_number=' . urlencode(html_entity_decode($this->request->get['filter_vat_number'], ENT_QUOTES, 'UTF-8'));
		}

        if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
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
			'href' => $this->url->link('supplier/supplier', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		if (!isset($this->request->get['supplier_id'])) {
			$data['action'] = $this->url->link('supplier/supplier/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('supplier/supplier/edit', 'user_token=' . $this->session->data['user_token'] . '&supplier_id=' . $this->request->get['supplier_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link('supplier/supplier', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['supplier_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$supplier_info = $this->model_supplier_supplier->getSupplier($this->request->get['supplier_id']);
		}

		if (isset($this->request->post['company_name'])) {
			$data['company_name'] = $this->request->post['company_name'];
		} elseif (!empty($supplier_info)) {
			$data['company_name'] = $supplier_info['company_name'];
		} else {
			$data['company_name'] = '';
		}

		if (isset($this->request->post['vat_number'])) {
			$data['vat_number'] = $this->request->post['vat_number'];
		} elseif (!empty($supplier_info)) {
			$data['vat_number'] = $supplier_info['vat_number'];
		} else {
			$data['vat_number'] = '';
		}

		if (isset($this->request->post['email'])) {
			$data['email'] = $this->request->post['email'];
		} elseif (!empty($supplier_info)) {
			$data['email'] = $supplier_info['email'];
		} else {
			$data['email'] = '';
		}

		if (isset($this->request->post['telephone'])) {
			$data['telephone'] = $this->request->post['telephone'];
		} elseif (!empty($supplier_info)) {
			$data['telephone'] = $supplier_info['telephone'];
		} else {
			$data['telephone'] = '';
		}
		

		if (isset($this->request->post['profession'])) {
			$data['profession'] = $this->request->post['profession'];
		} elseif (!empty($supplier_info)) {
			$data['profession'] = $supplier_info['profession'];
		} else {
			$data['profession'] = '';
		}

		if (isset($this->request->post['tax_office'])) {
			$data['tax_office'] = $this->request->post['tax_office'];
		} elseif (!empty($supplier_info)) {
			$data['tax_office'] = $supplier_info['tax_office'];
		} else {
			$data['tax_office'] = '';
		}

		if (isset($this->request->post['fax'])) {
			$data['fax'] = $this->request->post['fax'];
		} elseif (!empty($supplier_info)) {
			$data['fax'] = $supplier_info['fax'];
		} else {
			$data['fax'] = '';
		}

        if (isset($this->request->post['address'])) {
			$data['address'] = $this->request->post['address'];
		} elseif (!empty($supplier_info)) {
			$data['address'] = $supplier_info['address'];
		} else {
			$data['address'] = '';
		}

        if (isset($this->request->post['postcode'])) {
			$data['postcode'] = $this->request->post['postcode'];
		} elseif (!empty($supplier_info)) {
			$data['postcode'] = $supplier_info['postcode'];
		} else {
			$data['postcode'] = '';
		}

        if (isset($this->request->post['city'])) {
			$data['city'] = $this->request->post['city'];
		} elseif (!empty($supplier_info)) {
			$data['city'] = $supplier_info['city'];
		} else {
			$data['city'] = '';
		}

        if (isset($this->request->post['country_id'])) {
			$data['country_id'] = $this->request->post['country_id'];
		} elseif (!empty($supplier_info)) {
			$data['country_id'] = $supplier_info['country_id'];
		} else {
			$data['country_id'] = $this->config->get('config_country_id');
		}

		$this->load->model('localisation/country');

		$data['countries'] = $this->model_localisation_country->getCountries();
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('supplier/supplier_form', $data));
    }

	public function order() {

		$this->load->language('catalog/product');
		$this->load->language('supplier/supplier');
		
		$data['user_token'] = $this->session->data['user_token'];

		$this->document->setTitle($this->language->get('heading_order_title'));

		$this->load->model('supplier/supplier');

		if(isset($this->request->get['supplier_id'])) {
			$supplier_id = $this->request->get['supplier_id'];
		} else {
			$supplier_id = 0;
		}

		$supplier_info = $this->model_supplier_supplier->getSupplier($supplier_id);

		if($supplier_info) {

			$data['breadcrumbs'] = array();

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
			);

			$url = '';

			if (isset($this->request->get['filter_company_name'])) {
				$url .= '&filter_company_name=' . urlencode(html_entity_decode($this->request->get['filter_company_name'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_email'])) {
				$url .= '&filter_email=' . urlencode(html_entity_decode($this->request->get['filter_email'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_telephone'])) {
				$url .= '&filter_telephone=' . $this->request->get['filter_telephone'];
			}
			if (isset($this->request->get['filter_vat_number'])) {
				$url .= '&filter_vat_number=' . urlencode(html_entity_decode($this->request->get['filter_vat_number'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_date_added'])) {
				$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
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

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('supplier/supplier', 'user_token=' . $this->session->data['user_token'] . $url, true)
			);

			$data['breadcrumbs'][] = array(
				'text' => $supplier_info['company_name'],
				'href' => $this->url->link('supplier/supplier/edit', 'user_token=' . $this->session->data['user_token'] . '&supplier_id=' . $supplier_info['supplier_id'] . $url, true)
			);

			$data['cancel'] = $this->url->link('supplier/supplier', 'user_token=' . $this->session->data['user_token'] . $url, true);
			$data['print'] = $this->url->link('supplier/supplier/print', 'user_token=' . $this->session->data['user_token'] . '&supplier_id=' . $supplier_info['supplier_id'], true);
			
			$data['products'] = array();

			$filter_data = array(
				'filter_alert' => true
			);

			$results = $this->model_supplier_supplier->getSupplierProducts($supplier_id, $filter_data);

			$this->load->model('tool/image');

			foreach ($results as $result) {

				if (is_file(DIR_IMAGE . $result['image'])) {
					$image = $this->model_tool_image->resize($result['image'], 40, 40);
				} else {
					$image = $this->model_tool_image->resize('no_image.png', 40, 40);
				}

				$data['products'][] = array(
					'product_id' => $result['product_id'],
					'model'	=> $result['model'],
					'upc'	=> $result['upc'],
					'purchased'	=> $result['purchased'] ? : 0,
					'image' => $image,
					'name'	=> html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'),
					'quantity' => $result['quantity'],
					'quantity_alert' => $result['quantity_alert'],
					'price'	=>	$this->tax->calculate($result['price'], $result['tax-class_id'],  $this->config->get('config_tax'))
				);
			}

			$data['header'] = $this->load->controller('common/header');
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['footer'] = $this->load->controller('common/footer');

			$this->response->setOutput($this->load->view('supplier/supplier_order', $data));

		} else {
			return new Action('error/not_found');
		}
	}

	public function print() {
		$this->load->language('catalog/product');
		$this->load->language('supplier/supplier');

		$this->load->model('supplier/supplier');

		$data['title'] = $this->language->get('heading_order_title');

		if(isset($this->request->get['supplier_id'])) {
			$supplier_id = $this->request->get['supplier_id'];
		} else {
			$supplier_id = 0;
		}

		$supplier_info = $this->model_supplier_supplier->getSupplier($supplier_id);
		

		$data['products'] = array();

		if($supplier_info) {
			
			$data['company_name'] = $supplier_info['company_name'];

			if($supplier_info['address']) {
				$data['address'] = $supplier_info['address'];
			} else {
				$data['address'] = '';
			}

			if($supplier_info['postcode']) {
				$data['postcode'] = $supplier_info['postcode'];
			} else {
				$data['postcode'] = '';
			}

			if($supplier_info['city']) {
				$data['city'] = $supplier_info['city'];
			} else {
				$data['city'] = '';
			}

			if($supplier_info['telephone']) {
				$data['telephone'] = $supplier_info['telephone'];
			} else {
				$data['telephone'] = '';
			}

			if(($this->request->server['REQUEST_METHOD'] === 'POST') && isset($this->request->post['products']) ) {

				$this->load->model('catalog/product');
	
				foreach ($this->request->post['products'] as $product_id) {
					$product_info = $this->model_catalog_product->getProduct($product_id);
	
					if ($product_info && $this->request->post['quantities'][$product_id]) {
						$data['products'][] = array(
							'model' => $product_info['model'],
							'upc'	=> $product_info['upc'],
							'name'	=> $product_info['name'],
							'quantity' => $this->request->post['quantities'][$product_id]
						);
					}
				}
			}
		}

		$this->response->setOutput($this->load->view('supplier/supplier_print', $data));
	}

    protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'supplier/supplier')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['company_name']) < 3) || (utf8_strlen(trim($this->request->post['company_name'])) > 64)) {
			$this->error['company_name'] = $this->language->get('error_company_name');
		}

		if ((utf8_strlen($this->request->post['vat_number']) < 9) || (utf8_strlen(trim($this->request->post['vat_number'])) > 16)) {
			$this->error['vat_number'] = $this->language->get('error_vat_number');
		}

		if ((utf8_strlen($this->request->post['telephone']) < 3) || (utf8_strlen($this->request->post['telephone']) > 16)) {
			$this->error['telephone'] = $this->language->get('error_telephone');
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'supplier/supplier')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function autocomplete() {
		$json = array();

		if (isset($this->request->get['filter_company_name']) || isset($this->request->get['filter_email'])) {
			if (isset($this->request->get['filter_company_name'])) {
				$filter_company_name = $this->request->get['filter_company_name'];
			} else {
				$filter_company_name = '';
			}

			if (isset($this->request->get['filter_email'])) {
				$filter_email = $this->request->get['filter_email'];
			} else {
				$filter_email = '';
			}
			
			$this->load->model('supplier/supplier');

			$filter_data = array(
				'filter_company_name' => $filter_company_name,
				'filter_email'     => $filter_email,
				'start'            => 0,
				'limit'            => 5
			);

			$results = $this->model_supplier_supplier->getSuppliers($filter_data);

			foreach ($results as $result) {
				$json[] = array(
					'supplier_id'       => $result['supplier_id'],
					'company_name'      => strip_tags(html_entity_decode($result['company_name'], ENT_QUOTES, 'UTF-8')),
					'email'             => $result['email'],
				);
			}
		}

		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}