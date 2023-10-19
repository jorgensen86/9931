<?php
class ControllerExtensionModuleQuickCategoryTree extends Controller {
    private $error = array();
    
	public function index() {
		$this->load->language('extension/module/quick_category_tree');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('module_quick_category_tree', $this->request->post);
			
			/* Register events if status is enabled */
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
			'href' => $this->url->link('extension/module/quick_category_tree', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/module/quick_category_tree', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->post['module_quick_category_tree_status'])) {
			$data['module_quick_category_tree_status'] = $this->request->post['module_quick_category_tree_status'];
		} else {
			$data['module_quick_category_tree_status'] = $this->config->get('module_quick_category_tree_status');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/quick_category_tree', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/quick_category_tree')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function install() {
		$this->load->model('setting/event');
		$this->model_setting_event->addEvent('admin_quick_category_tree','admin/view/common/column_left/before', 'extension/module/quick_category_tree/addAdminMenu');
	}

	public function uninstall() {
		$this->load->model('setting/event');
		$this->model_setting_event->deleteEventByCode('admin_quick_category_tree');
	}

	public function addAdminMenu(&$route, &$data) {
        if (!$this->config->get('module_quick_category_tree_status')) return;
        
        $this->language->load('extension/module/quick_category_tree');

		foreach ($data['menus'] as $key => $menu) {
			if ($menu['id'] === 'menu-catalog') {
                $catalog_menu = $data['menus'][$key]['children']; 
                
                $data['menus'][$key]['children'][0] = array(
					'name' 	=> $this->language->get('text_menu'),
					'href' 	=> $this->url->link('catalog/category_tree', 'user_token=' . $this->session->data['user_token']),
					'children' => array()
                );
                
                foreach($catalog_menu as $k => $child) {
                    $data['menus'][$key]['children'][$k+1] = array(
                        'name' 	=> $child['name'],
                        'href' 	=> $child['href'],
                        'children' => $child['children']
                    );
                }

                break;
			}
		}
	}
}