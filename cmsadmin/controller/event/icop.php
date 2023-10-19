<?php
define('DIR_ICOP', DIR_STORAGE . 'icop/');

class ControllerEventIcop extends Controller {
	public function headerView(&$route, &$data) {
		if (!$this->user->isLogged()) return;
		$data['styles']['bootstrap_toggle'] = array(
			'href' 	=> 'view/javascript/bootstrap-toggle/css/bootstrap-toggle.min.css',
			'rel'  	=> 'stylesheet',
			'media' => 'screen',
		);

		array_push($data['scripts'], 'view/javascript/bootstrap-toggle/js/bootstrap-toggle.min.js');
		array_push($data['scripts'], 'view/javascript/icop/header.js');

		$data['catalogs'] = array(
			'product'  		=> $this->url->link('catalog/product/add', 'user_token=' . $this->session->data['user_token']),
			'category' 		=> $this->url->link('catalog/category/add', 'user_token=' . $this->session->data['user_token']),
			'information' 	=> $this->url->link('catalog/information/add', 'user_token=' . $this->session->data['user_token']),
			'manufacturer'	=> $this->url->link('catalog/manufacturer/add', 'user_token=' . $this->session->data['user_token'])
		);

		$data['extensions'] = array(
			'module' => array(
				'icon' => 'fa fa-puzzle-piece fw',
				'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module')
			),
			'payment' => array(
				'icon' => 'fa fa-credit-card fw',
				'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment')
			),
			'shipping' => array(
				'icon' => 'fa fa-truck fw',
				'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping')
			),
			'total' => array(
				'icon' => 'fa fa-credit-card fw',
				'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=total')
			),
			'feed' => array(
				'icon' => 'fa fa-rss fw',
				'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=feed')
			),
		);

		$data['caches'] = array(
			'ocmod'  	=> 'OcMod Cache',
			'vqmod' 	=> 'VqMod Cache',
			'system' 	=> 'System Cache',
			'image' 	=> 'Image Cache',
			'journal'	=> 'Theme Cache'
		);

		$data['maintenance'] = !$this->config->get('config_maintenance');
	}

	public function hiddenFields($controller = '') {
		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			$file = fopen(DIR_ICOP . $this->request->get['file'] . '_hidden_fields.json', 'w');
			fwrite($file, json_encode($this->request->post[$this->request->get['file']]));
			fclose($file);
		}

		$this->document->addStyle('view/stylesheet/icop/product.css');
		
		$data['hidden_tabs'] = [
		 	'recurring' => $this->user->hasPermission('access', 'catalog/recurring'),
		 	'reward'	=> $this->user->hasPermission('access', 'extension/total/reward'),
		 	'design'	=> $this->user->hasPermission('access', 'design/layout')
		 ];
		
		$controller = utf8_strtolower(str_replace('ControllerCatalog', '', $controller));

		if ($controller === 'product') {
			$data['fields'] = array(
				'sku' 			=> 'sku', 
				'upc' 			=> 'upc', 
				'mpn' 			=> 'mpn', 
				'jan'			=> 'jan', 
				'ean' 			=> 'ean', 
				'isbn' 			=> 'isbn', 
				'location' 		=> 'location',
				'date_available'=> 'date_available', 
				'length' 		=> 'length', 
				'shipping' 		=> 'shipping', 
				'length_class' 	=> 'length_class_id', 
				'weight' 		=> 'weight', 
				'weight_class' 	=> 'weight_class_id', 
				'download' 		=> 'download', 
				'store' 		=> 'product_store[]', 
				'filter' 		=> 'filter',
			);
		} elseif ($controller === 'category') {
			$data['fields'] = array(
		 		'store' 	=> 'category_store[]', 
		 		'filter' 	=> 'filter',
		 		'top' 		=> 'top',
		 		'column' 	=> 'column',
		 	);
		} elseif ($controller === 'information') {
			$data['fields'] = array(
				'store' 	=> 'information_store[]', 
		 		'bottom' 	=> 'bottom',
		 	);
		} elseif ($controller === 'manufacturer') {
			$data['fields'] = array(
				'store' 	=> 'manufacturer_store[]', 
		 	);
		}

		$data['key'] = $controller;
		$data['hidden_fields'] = json_decode(@file_get_contents(DIR_ICOP . $controller . '_hidden_fields.json'), true);
		$data['user_token'] = $this->session->data['user_token'];
		$data['user_group_id'] = $this->user->getGroupId();

		return $this->load->view('icop/hidden_fields_form', $data);
		
	}

	public function maintenance() {
		$this->load->model('setting/setting');

		$this->model_setting_setting->editSettingValue('config', 'config_maintenance', $this->request->get['maintenance']);
	}

	public function journal3ModulePermissions() {
		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			$file = fopen(DIR_ICOP . 'journal3_hidden_modules.json', 'w');
			fwrite($file, json_encode($this->request->post['fields']));
			fclose($file);
			return;
		}

		$data['user_token'] = $this->session->data['user_token'];

		$data['fields'] = ["Banners", "Blocks", "Slider", "Gallery", "Info Blocks", "Newsletter", "Contact Form", "Title / Text", "Button", "Grid", "Testimonials", "Layout Notice", "Countdown", "Products", "Mini Products", "Brands", "Categories", "Catalog", "Filter", "Links Menu", "Flyout Menu", "Accordion Menu", "Icons Menu", "Blog Posts", "Mini Posts", "Categories", "Comments", "Blog Search", "Blog Tags", "Header Notice", "Notification", "Popup", "Bottom Menu", "Side Menu" ];

		$data['hidden_fields'] = json_decode(@file_get_contents(DIR_ICOP . 'journal3_hidden_modules.json'), true);
		
		$this->response->setOutput($this->load->view('icop/hidden_modules_journal3', $data));
	}

	public function clearCache() {
		if (isset($this->request->get['cache'])) {
			if ($this->request->get['cache'] === 'image') {
				$this->deleteFiles(DIR_IMAGE . 'cache/');
				unset($this->session->data['success']);
			} elseif ($this->request->get['cache'] === 'system') {
				$this->deleteFiles(DIR_CACHE);
			} elseif ($this->request->get['cache'] === 'vqmod') {
				$this->deleteFiles(realpath(DIR_APPLICATION . '/..') . '/vqmod/vqcache/');
				@unlink(realpath(DIR_APPLICATION . '/..') . '/vqmod/mods.cache');
			}
		}
	}

	private function deleteFiles($directory) {
		foreach (glob($directory . '*') as $object) {
			if (is_dir($object)) {
				$this->deleteFiles($object .'/');
				rmdir($object);
			} elseif (is_file($object)) {
				if (!in_array(pathinfo($object, PATHINFO_EXTENSION), ['html', 'htaccess', 'txt'])) {
					unlink($object);
				}
			}
		}
	}
}
