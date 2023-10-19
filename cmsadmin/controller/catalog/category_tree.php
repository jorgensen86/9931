<?php
class ControllerCatalogCategoryTree extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('catalog/category');
		$this->document->addStyle('view/javascript/jstree-3.3.10-0/themes/default/style.min.css');
		$this->document->addStyle('view/javascript/jstree-3.3.10-0/themes/proton/style.min.css');
		$this->document->addStyle('view/javascript/jstree-3.3.10-0/tree.css');
		$this->document->addScript('view/javascript/jstree-3.3.10-0/jstree.min.js');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('catalog/category_tree', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/category_tree', $data));
	}

	public function getData() {
		$json = array();
		$root = (isset($this->request->get['id']) && $this->request->get['id'] == '#');
		$operation = isset($this->request->get['operation']) ? $this->request->get['operation'] : '';
		$category_id = isset($this->request->get['id']) && ctype_digit($this->request->get['id']) ? $this->request->get['id'] : 0;

		if ($root) {
			$json[] = array('data' => array('status' => 1), 'text' => 'Κατηγορίες', 'children' => true,  'id' => "0", 'icon' => 'jstree-folder');
		} else {
			$categories = $this->db->query("SELECT *, (SELECT COUNT(parent_id) FROM " . DB_PREFIX . "category WHERE parent_id = c.category_id) AS children FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) WHERE c.parent_id = '" . (int)$category_id . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY c.sort_order, cd.name")->rows;

			foreach ($categories as $cat) {
				$json[] = array(
					'data' => array( 'status' => $cat['status']), 
					'text' => $cat['name'], 
					'children' => $cat['children'] > 0, 
					'id' => $cat['category_id'], 
					'icon' => 'jstree-folder', 
					'a_attr' => array('class' => (bool)$cat['status'] ? 'active' : 'deactive')
				);
			}
		}

		$this->response->addHeader('Content-Type: application/json; charset=utf-8');
		$this->response->setOutput(json_encode($json));
	}
}
