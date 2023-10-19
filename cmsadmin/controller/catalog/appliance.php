<?php
class ControllerCatalogAppliance extends Controller
{
	private $error = array();

	public function index()
	{
		$this->load->language('catalog/appliance');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/appliance');

		$this->getList();
	}

	public function add()
	{
		$this->load->language('catalog/appliance');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/appliance');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_catalog_appliance->addAppliance($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_code'])) {
				$url .= '&filter_code=' . urlencode(html_entity_decode($this->request->get['filter_code'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_manufacturer'])) {
				$url .= '&filter_manufacturer=' . urlencode(html_entity_decode($this->request->get['filter_manufacturer'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_category'])) {
				$url .= '&filter_category=' . urlencode(html_entity_decode($this->request->get['filter_category'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
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

			$this->response->redirect($this->url->link('catalog/appliance', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function edit()
	{
		$this->load->language('catalog/appliance');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/appliance');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {

			$this->model_catalog_appliance->editAppliance($this->request->get['appliance_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_code'])) {
				$url .= '&filter_code=' . urlencode(html_entity_decode($this->request->get['filter_code'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_manufacturer'])) {
				$url .= '&filter_manufacturer=' . urlencode(html_entity_decode($this->request->get['filter_manufacturer'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_category'])) {
				$url .= '&filter_category=' . urlencode(html_entity_decode($this->request->get['filter_category'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
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

			$this->response->redirect($this->url->link('catalog/appliance', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function delete()
	{
		$this->load->language('catalog/appliance');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/appliance');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $appliance_id) {
				$this->model_catalog_appliance->deleteAppliance($appliance_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_code'])) {
				$url .= '&filter_code=' . urlencode(html_entity_decode($this->request->get['filter_code'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_manufacturer'])) {
				$url .= '&filter_manufacturer=' . urlencode(html_entity_decode($this->request->get['filter_manufacturer'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_category'])) {
				$url .= '&filter_category=' . urlencode(html_entity_decode($this->request->get['filter_category'], ENT_QUOTES, 'UTF-8'));
			}


			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
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

			$this->response->redirect($this->url->link('catalog/appliance', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	public function copy()
	{
		$this->load->language('catalog/appliance');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/appliance');

		if (isset($this->request->post['selected']) && $this->validateCopy()) {
			foreach ($this->request->post['selected'] as $appliance_id) {
				$this->model_catalog_appliance->copyAppliance($appliance_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['filter_code'])) {
				$url .= '&filter_code=' . urlencode(html_entity_decode($this->request->get['filter_code'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_manufacturer'])) {
				$url .= '&filter_manufacturer=' . urlencode(html_entity_decode($this->request->get['filter_manufacturer'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_category'])) {
				$url .= '&filter_category=' . urlencode(html_entity_decode($this->request->get['filter_category'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_status'])) {
				$url .= '&filter_status=' . $this->request->get['filter_status'];
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

			$this->response->redirect($this->url->link('catalog/appliance', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	protected function getList()
	{

		if (isset($this->request->get['filter_code'])) {
			$filter_code = $this->request->get['filter_code'];
		} else {
			$filter_code = '';
		}

		if (isset($this->request->get['filter_manufacturer'])) {
			$filter_manufacturer = $this->request->get['filter_manufacturer'];
		} else {
			$filter_manufacturer = '';
		}

		if (isset($this->request->get['filter_category'])) {
			$filter_category = $this->request->get['filter_category'];
		} else {
			$filter_category = '';
		}

		if (isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		} else {
			$filter_status = '';
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'a.code';
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


		if (isset($this->request->get['filter_code'])) {
			$url .= '&filter_code=' . urlencode(html_entity_decode($this->request->get['filter_code'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_manufacturer'])) {
			$url .= '&filter_manufacturer=' . urlencode(html_entity_decode($this->request->get['filter_manufacturer'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_category'])) {
			$url .= '&filter_category=' . urlencode(html_entity_decode($this->request->get['filter_category'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
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
			'href' => $this->url->link('catalog/appliance', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['add'] = $this->url->link('catalog/appliance/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['copy'] = $this->url->link('catalog/appliance/copy', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('catalog/appliance/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['appliances'] = array();

		$filter_data = array(
			'filter_code'	  => $filter_code,
			'filter_manufacturer' => $filter_manufacturer,
			'filter_category' => $filter_category,
			'filter_status'   => $filter_status,
			'sort'            => $sort,
			'order'           => $order,
			'start'           => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'           => $this->config->get('config_limit_admin')
		);

		$this->load->model('tool/image');
		$this->load->model('catalog/category');

		$appliance_total = $this->model_catalog_appliance->getTotalAppliances($filter_data);

		$results = $this->model_catalog_appliance->getAppliances($filter_data);

		foreach ($results as $result) {
			if (is_file(DIR_IMAGE . $result['image'])) {
				$image = $this->model_tool_image->resize($result['image'], 40, 40);
			} else {
				$image = $this->model_tool_image->resize('no_image.png', 40, 40);
			}

			$category_info = $this->model_catalog_category->getCategory($result['category_id']);

			if ($category_info) {
				$path = ($category_info['path']) ? $category_info['path'] . ' &gt; ' . $category_info['name'] : $category_info['name'];
			} else {
				$path = '';
			}

			$data['appliances'][] = array(
				'appliance_id' => $result['appliance_id'],
				'image'      => $image,
				'code'       => $result['code'],
				'manufacturer' => $result['manufacturer'],
				'category' 	 => $result['category'],
				'path' 	 	 => $path,
				'status'     => $result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
				'sort_order' => $result['sort_order'],
				'edit'       => $this->url->link('catalog/appliance/edit', 'user_token=' . $this->session->data['user_token'] . '&appliance_id=' . $result['appliance_id'] . $url, true)
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

		if (isset($this->request->get['filter_code'])) {
			$url .= '&filter_code=' . urlencode(html_entity_decode($this->request->get['filter_code'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_manufacturer'])) {
			$url .= '&filter_manufacturer=' . urlencode(html_entity_decode($this->request->get['filter_manufacturer'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_category'])) {
			$url .= '&filter_category=' . urlencode(html_entity_decode($this->request->get['filter_category'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_code'] = $this->url->link('catalog/appliance', 'user_token=' . $this->session->data['user_token'] . '&sort=a.code' . $url, true);
		$data['sort_manufacturer'] = $this->url->link('catalog/appliance', 'user_token=' . $this->session->data['user_token'] . '&sort=manufacturer' . $url, true);
		$data['sort_category'] = $this->url->link('catalog/appliance', 'user_token=' . $this->session->data['user_token'] . '&sort=category' . $url, true);
		$data['sort_status'] = $this->url->link('catalog/appliance', 'user_token=' . $this->session->data['user_token'] . '&sort=a.status' . $url, true);
		$data['sort_order'] = $this->url->link('catalog/appliance', 'user_token=' . $this->session->data['user_token'] . '&sort=a.sort_order' . $url, true);

		$url = '';

		if (isset($this->request->get['filter_code'])) {
			$url .= '&filter_code=' . urlencode(html_entity_decode($this->request->get['filter_code'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_manufacturer'])) {
			$url .= '&filter_manufacturer=' . urlencode(html_entity_decode($this->request->get['filter_manufacturer'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_category'])) {
			$url .= '&filter_category=' . urlencode(html_entity_decode($this->request->get['filter_category'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $appliance_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('catalog/appliance', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($appliance_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($appliance_total - $this->config->get('config_limit_admin'))) ? $appliance_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $appliance_total, ceil($appliance_total / $this->config->get('config_limit_admin')));

		$data['filter_code'] = $filter_code;
		$data['filter_manufacturer'] = $filter_manufacturer;
		$data['filter_category_id'] = $filter_category;
		
		$category_info = $this->model_catalog_category->getCategory($filter_category);

		if ($category_info) {
			$data['filter_category'] = ($category_info['path']) ? $category_info['path'] . ' &gt; ' . $category_info['name'] : $category_info['name'];
		} else {
			$data['filter_category'] = '';
		}

		$data['filter_status'] = $filter_status;

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/appliance_list', $data));
	}

	protected function getForm()
	{
		$data['text_form'] = !isset($this->request->get['product_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['manufacturer'])) {
			$data['error_manufacturer'] = $this->error['manufacturer'];
		} else {
			$data['error_manufacturer'] = '';
		}

		if (isset($this->error['category'])) {
			$data['error_category'] = $this->error['category'];
		} else {
			$data['error_category'] = '';
		}

		if (isset($this->error['code'])) {
			$data['error_code'] = $this->error['code'];
		} else {
			$data['error_code'] = '';
		}

		if (isset($this->error['keyword'])) {
			$data['error_keyword'] = $this->error['keyword'];
		} else {
			$data['error_keyword'] = '';
		}

		$url = '';

		if (isset($this->request->get['filter_code'])) {
			$url .= '&filter_code=' . urlencode(html_entity_decode($this->request->get['filter_code'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_manufacturer'])) {
			$url .= '&filter_manufacturer=' . urlencode(html_entity_decode($this->request->get['filter_manufacturer'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_category'])) {
			$url .= '&filter_category=' . urlencode(html_entity_decode($this->request->get['filter_category'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
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
			'href' => $this->url->link('catalog/appliance', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		if (!isset($this->request->get['appliance_id'])) {
			$data['action'] = $this->url->link('catalog/appliance/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('catalog/appliance/edit', 'user_token=' . $this->session->data['user_token'] . '&appliance_id=' . $this->request->get['appliance_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link('catalog/appliance', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['appliance_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$appliance_info = $this->model_catalog_appliance->getAppliance($this->request->get['appliance_id']);
		}

		$data['user_token'] = $this->session->data['user_token'];

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['code'])) {
			$data['code'] = $this->request->post['code'];
		} elseif (!empty($appliance_info)) {
			$data['code'] = $appliance_info['code'];
		} else {
			$data['code'] = '';
		}

		// Image
		if (isset($this->request->post['image'])) {
			$data['image'] = $this->request->post['image'];
		} elseif (!empty($appliance_info)) {
			$data['image'] = $appliance_info['image'];
		} else {
			$data['image'] = '';
		}

		$this->load->model('tool/image');

		if (isset($this->request->post['image']) && is_file(DIR_IMAGE . $this->request->post['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($this->request->post['image'], 100, 100);
		} elseif (!empty($appliance_info) && is_file(DIR_IMAGE . $appliance_info['image'])) {
			$data['thumb'] = $this->model_tool_image->resize($appliance_info['image'], 100, 100);
		} else {
			$data['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
		}

		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		// Manufacturer
		$this->load->model('catalog/manufacturer');

		if (isset($this->request->post['manufacturer_id'])) {
			$data['manufacturer_id'] = $this->request->post['manufacturer_id'];
		} elseif (!empty($appliance_info)) {
			$data['manufacturer_id'] = $appliance_info['manufacturer_id'];
		} else {
			$data['manufacturer_id'] = 0;
		}

		if (isset($this->request->post['manufacturer'])) {
			$data['manufacturer'] = $this->request->post['manufacturer'];
		} elseif (!empty($appliance_info)) {
			$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($appliance_info['manufacturer_id']);

			if ($manufacturer_info) {
				$data['manufacturer'] = $manufacturer_info['name'];
			} else {
				$data['manufacturer'] = '';
			}
		} else {
			$data['manufacturer'] = '';
		}

		// Categories
		$this->load->model('catalog/category');

		if (isset($this->request->post['category_id'])) {
			$data['category_id'] = $this->request->post['category_id'];
		} elseif (!empty($appliance_info)) {
			$data['category_id'] = $appliance_info['category_id'];
		} else {
			$data['category_id'] = 0;
		}

		if (isset($this->request->post['category'])) {
			$data['category'] = $this->request->post['category'];
		} elseif (!empty($appliance_info)) {
			$category_info = $this->model_catalog_category->getCategory($appliance_info['category_id']);

			if ($category_info) {
				$data['category'] = ($category_info['path']) ? $category_info['path'] . ' &gt; ' . $category_info['name'] : $category_info['name'];
			} else {
				$data['category'] = '';
			}
		} else {
			$data['category'] = '';
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($appliance_info)) {
			$data['status'] = $appliance_info['status'];
		} else {
			$data['status'] = true;
		}

		if (isset($this->request->post['sort_order'])) {
			$data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($appliance_info)) {
			$data['sort_order'] = $appliance_info['sort_order'];
		} else {
			$data['sort_order'] = 1;
		}

		if (isset($this->request->post['appliance_seo_url'])) {
			$data['appliance_seo_url'] = $this->request->post['appliance_seo_url'];
		} elseif (isset($this->request->get['appliance_id'])) {
			$data['appliance_seo_url'] = $this->model_catalog_appliance->getApplianceSeoUrls($this->request->get['appliance_id']);
		} else {
			$data['appliance_seo_url'] = array();
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/appliance_form', $data));
	}

	protected function validateForm()
	{
		if (!$this->user->hasPermission('modify', 'catalog/appliance')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['code']) < 3) || (utf8_strlen($this->request->post['code']) > 64)) {
			$this->error['code'] = $this->language->get('error_model');
		}

		if (!$this->request->post['category_id']) {
			$this->error['category'] = $this->language->get('error_category');
		}

		if (!$this->request->post['manufacturer_id']) {
			$this->error['manufacturer'] = $this->language->get('error_manufacturer');
		}

		if ($this->request->post['appliance_seo_url']) {
			$this->load->model('design/seo_url');
			foreach ($this->request->post['appliance_seo_url'] as $language_id => $keyword) {
				if (!empty($keyword)) {
					if (count(array_keys($this->request->post['appliance_seo_url'], $keyword)) > 1) {
						$this->error['keyword'][$language_id] = $this->language->get('error_unique');
					}

					$seo_urls = $this->model_design_seo_url->getSeoUrlsByKeyword($keyword);

					foreach ($seo_urls as $seo_url) {
						if (($seo_url['store_id'] == 0) && (!isset($this->request->get['appliance_id']) || (($seo_url['query'] != 'appliance_id=' . $this->request->get['appliance_id'])))) {
							$this->error['keyword'][$language_id] = $this->language->get('error_keyword');
							break;
						}
					}
				}
			}
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}

	protected function validateDelete()
	{
		if (!$this->user->hasPermission('modify', 'catalog/appliance')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	protected function validateCopy()
	{
		if (!$this->user->hasPermission('modify', 'catalog/appliance')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function autocomplete()
	{
		$json = array();

		if (isset($this->request->get['filter_code'])) {
			$this->load->model('catalog/appliance');

			if (isset($this->request->get['filter_code'])) {
				$filter_code = $this->request->get['filter_code'];
			} else {
				$filter_code = '';
			}

			if (isset($this->request->get['limit'])) {
				$limit = $this->request->get['limit'];
			} else {
				$limit = 5;
			}

			$filter_data = array(
				'filter_code'  => $filter_code,
				'start'        => 0,
				'limit'        => $limit
			);

			$results = $this->model_catalog_appliance->getAppliances($filter_data);

			foreach ($results as $result) {

				$json[] = array(
					'appliance_id' => $result['appliance_id'],
					'code'      => $result['code'],
					'manufacturer' => $result['manufacturer'],
				);
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
