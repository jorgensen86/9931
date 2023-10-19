<?php
class ControllerExtensionEnhancementEaVqmodManager extends Controller {
	private $error = array();	

	public function __construct($registry) {
		parent::__construct($registry);

		$this->base_dir = substr_replace(DIR_SYSTEM, '/', -8);
		$this->vqmod_dir = substr_replace(DIR_SYSTEM, '/vqmod/', -8);
		$this->vqmod_script_dir = substr_replace(DIR_SYSTEM, '/vqmod/xml/', -8);
		$this->vqcache_dir = substr_replace(DIR_SYSTEM, '/vqmod/vqcache/', -8);
		$this->vqcache_files = substr_replace(DIR_SYSTEM, '/vqmod/vqcache/vq*', -8);
		$this->vqmod_logs_dir = substr_replace(DIR_SYSTEM, '/vqmod/logs/', -8);
		$this->vqmod_logs = substr_replace(DIR_SYSTEM, '/vqmod/logs/*.log', -8);
		$this->vqmod_modcache = substr_replace(DIR_SYSTEM, '/vqmod/mods.cache', -8);
		$this->vqmod_opencart_script = substr_replace(DIR_SYSTEM, '/vqmod/xml/vqmod_opencart.xml', -8);
		$this->vqmod_path_replaces = substr_replace(DIR_SYSTEM, '/vqmod/pathReplaces.php', -8);

		clearstatcache();
	}

	public function index() {		
		$language_data = $this->load->language('extension/enhancement/ea_vqmod_manager');
		foreach($language_data as $key=>$value){
			$data[$key] = $value;
		}

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->document->addStyle('view/template/extension/enhancement/vqmod_manager/js/jquery/jquery-confirm.css');
		$this->document->addStyle('view/template/extension/enhancement/vqmod_manager/js/bootstrap/bootstrap-multiselect.css');		
		$this->document->addStyle('view/template/extension/enhancement/vqmod_manager/css/ea_vqmod_manager.css');
		$this->document->addStyle('view/template/extension/enhancement/vqmod_manager/js/datatables/css/dataTables.bootstrap.css');
		
		$this->document->addScript('view/template/extension/enhancement/vqmod_manager/js/jquery/jquery-confirm.min.js');
		$this->document->addScript('view/template/extension/enhancement/vqmod_manager/js/bootstrap/bootstrap-multiselect.min.js');		
		$this->document->addScript('view/template/extension/enhancement/vqmod_manager/js/datatables/js/jquery.dataTables.min.js');
		$this->document->addScript('view/template/extension/enhancement/vqmod_manager/js/datatables/js/dataTables.bootstrap.min.js');
		$this->document->addScript('view/template/extension/enhancement/vqmod_manager/js/datatables/js/dataTables.conditionalPaging.js');

		$this->load->model('extension/enhancement/ea_vqmod_manager');
		
		$query = $this->db->query("SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "ea_vqmod_manager'");
		if (!$query->rows) {
			$this->model_extension_enhancement_ea_vqmod_manager->installVqmodTable();
		}
		
		$query = $this->db->query("SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "ea_vqmod_manager_backup'");
		if (!$query->rows) {
			$this->model_extension_enhancement_ea_vqmod_manager->installVqmodBackup();
		}
		
		$query = $this->db->query("SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "ea_vqmod_manager_email'");
		if (!$query->rows) {
			$this->model_extension_enhancement_ea_vqmod_manager->installVqmodEmail();
		}
		
		$query = $this->db->query("SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "ea_vqmod_comment'");
		if (!$query->rows) {
			$this->model_extension_enhancement_ea_vqmod_manager->installVqmodComment();
		}

		if ($this->request->server['REQUEST_METHOD'] == 'POST' && isset($this->request->post['upload_vqmod'])) {
			$this->vqmod_upload();
		}
		
		$this->checkXmlDates();
		
		$vqmod_db = array();
		$vqmod_file = array();
		
		$vqmod_names = $this->model_extension_enhancement_ea_vqmod_manager->getXmlNames();
		$vqmod_scripts = $this->getXmls();
		
		foreach ($vqmod_names as $vqmod_name) {	
			$vqmod_db[] = $vqmod_name['xml_name'];
		}
		
		foreach ($vqmod_scripts as $vqmod_script) {	
			$vqmod_file[] = basename($vqmod_script);
		}
		
		$xml_not_exists = array_diff($vqmod_db, $vqmod_file);
		
		foreach ($xml_not_exists as $xml_name) {	
			$this->model_extension_enhancement_ea_vqmod_manager->deleteVqmodByName($xml_name);
		}

		$this->getList();
	}		
	
	public function updateConfig() {
		$vqmod_id = $this->request->get['vqmod_id'];
		$screen_mode = $this->request->get['screen_mode'];

		$this->load->model('extension/enhancement/ea_vqmod_manager');
		$this->model_extension_enhancement_ea_vqmod_manager->editSettingValue('vq_editor', 'vq_editor_screen', $screen_mode);		
		
		$this->response->redirect($this->url->link('extension/enhancement/ea_vqmod_manager/edit', 'user_token=' . $this->session->data['user_token'] . '&vqmod_id='.(int)$vqmod_id));
	}
	
	public function genBackup() {
		$this->load->model('extension/enhancement/ea_vqmod_manager');
		
		$vqmod_id = $this->request->get['vqmod_id'];

		$vqmod_info = $this->model_extension_enhancement_ea_vqmod_manager->getVqmod($vqmod_id);
			
		if (file_exists($this->vqmod_script_dir.$vqmod_info['xml_name'])) {
			$xml = html_entity_decode(file_get_contents($this->vqmod_script_dir .  $vqmod_info['xml_name']), ENT_QUOTES, 'UTF-8');

			$backup = $this->model_extension_enhancement_ea_vqmod_manager->getBackup($vqmod_id);

			if (!$backup) {
				$this->model_extension_enhancement_ea_vqmod_manager->addVqmodBackup($vqmod_id, $xml);
			} else {
				$this->model_extension_enhancement_ea_vqmod_manager->deleteVqmodBackup($vqmod_id);
				$this->model_extension_enhancement_ea_vqmod_manager->addVqmodBackup($vqmod_id, $xml);
			}
			
			$url = $this->getUrlParams(array('vqmod_id' => $vqmod_id));
		
			$this->response->redirect($this->url->link('extension/enhancement/ea_vqmod_manager/edit', 'user_token=' . $this->session->data['user_token'] . $url));
		} else {
			$this->session->data['error'] = $this->language->get('error_warning_backup_no_file');			
			$this->response->redirect($this->url->link('extension/enhancement/ea_vqmod_manager', 'user_token=' . $this->session->data['user_token']));
		}
	}
	
	public function add() {
		$language_data = $this->load->language('extension/enhancement/ea_vqmod_manager');
		foreach($language_data as $key=>$value){
			$data[$key] = $value;
		}

		$this->load->model('extension/enhancement/ea_vqmod_manager');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateXmlForm()) {			
			$xml_post = html_entity_decode(rawurldecode($this->request->post['xml']), ENT_QUOTES, 'UTF-8');
			
			$xml = simplexml_load_string($xml_post);	
			
			if (isset($this->request->post['xml_name'])) {
				$xmlnew = $this->cleanFilename($this->request->post['xml_name']);
			} else {
				$xmlnew = $this->cleanFilename($xml->id);
			}	
			
			$result = $this->model_extension_enhancement_ea_vqmod_manager->getVqmodByXmlName($xmlnew.'.xml');
			
			$rand_num = mt_rand(100000, 999999);
			
			if ($result) {
				$xml_name = $xmlnew . '_'.$rand_num.'.xml';
			} else {
				$xml_name = $xmlnew . '.xml';
			}
			
			$add_data = array(
				'name'		=> $xml->id,
				'xml_name'	=> $xml_name,
				'author'	=> $xml->author,
				'version' 	=> $xml->version,
				'status'    => (int)$this->request->post['status']
			);
			
			$this->model_extension_enhancement_ea_vqmod_manager->addVqmod($add_data);
			$vqmod_id = $this->db->getLastId();
			
			file_put_contents($this->vqmod_script_dir . $xml_name, html_entity_decode($xml_post, ENT_QUOTES, 'UTF-8'));

			$this->session->data['success'] = $this->language->get('text_success_added');

			$this->response->redirect($this->url->link('extension/enhancement/ea_vqmod_manager/genBackup', 'user_token=' . $this->session->data['user_token'] . '&vqmod_id='.(int)$vqmod_id));
		}

		$this->getXmlForm();
	}

	public function edit() {
		$language_data = $this->load->language('extension/enhancement/ea_vqmod_manager');
		foreach($language_data as $key=>$value){
			$data[$key] = $value;
		}

		$this->load->model('extension/enhancement/ea_vqmod_manager');
			
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && !empty($this->request->get['vqmod_id']) && $this->validateXmlForm()) {
			$vqmod_id = $this->request->get['vqmod_id'];

			$vqmod_info = $this->model_extension_enhancement_ea_vqmod_manager->getVqmod($vqmod_id);			
		
			$vqmod_file = $vqmod_info['xml_name'];
			
			$xml_post = html_entity_decode(rawurldecode($this->request->post['xml']), ENT_QUOTES, 'UTF-8');
			
			libxml_use_internal_errors(true);
			
			$xml = simplexml_load_string($xml_post);

			if (!libxml_get_errors()) {				

				$backup = $this->model_extension_enhancement_ea_vqmod_manager->getBackup($vqmod_id);

				if (!$backup) {
					$this->model_extension_enhancement_ea_vqmod_manager->addVqmodBackup($vqmod_id, $xml_post);
				} else {
					$this->model_extension_enhancement_ea_vqmod_manager->deleteVqmodBackup($vqmod_id);
					$this->model_extension_enhancement_ea_vqmod_manager->addVqmodBackup($vqmod_id, $xml_post);
				}
				
				libxml_clear_errors;
				file_put_contents($this->vqmod_script_dir . $vqmod_file, html_entity_decode($xml_post, ENT_QUOTES, 'UTF-8'));
				
				$filename = pathinfo($vqmod_file, PATHINFO_FILENAME);

				if ($vqmod_file == $filename.'.xml_' && $this->request->post['status'] == 1) {
					rename ($this->vqmod_script_dir  . $filename . ".xml_" , $this->vqmod_script_dir . $filename. ".xml" );
					$vqmod_file = $filename.'.xml';
					$this->model_extension_enhancement_ea_vqmod_manager->enableVqmod($vqmod_id,$vqmod_file);
				}

				if ($vqmod_file == $filename.'.xml' && $this->request->post['status'] == 0) {
					rename ($this->vqmod_script_dir  . $filename . ".xml" , $this->vqmod_script_dir . $filename. ".xml_" );				
					$vqmod_file = $filename.'.xml_';
					$this->model_extension_enhancement_ea_vqmod_manager->disableVqmod($vqmod_id,$vqmod_file);
				}			
			
				$edit_data = array(
					'vqmod_id'	=> $vqmod_id,
					'name'		=> $xml->id,
					'xml_name'	=> $vqmod_file,
					'author'	=> $xml->author,
					'version' 	=> $xml->version,
					'status'    => (int)$this->request->post['status'],
				);			

				$this->model_extension_enhancement_ea_vqmod_manager->editVqmod($edit_data);

				$url = $this->getUrlParams(array('vqmod_id' => $vqmod_id));

				$this->session->data['success'] = sprintf($this->language->get('text_success_saved'), $vqmod_file);

				$this->response->redirect($this->url->link('extension/enhancement/ea_vqmod_manager/edit', 'user_token=' . $this->session->data['user_token'] . $url));
			}			
		}
		
		$this->getXmlForm();
	}	

	public function delete() {		
		$language_data = $this->load->language('extension/enhancement/ea_vqmod_manager');
		foreach($language_data as $key=>$value){
			$data[$key] = $value;
		}

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/enhancement/ea_vqmod_manager');

		if (isset($this->request->post['selected']) && $this->validate()) {			
			foreach ($this->request->post['selected'] as $vqmod_id) {
				$vqmod_info = $this->model_extension_enhancement_ea_vqmod_manager->getVqmod($vqmod_id);
				
				if ($vqmod_info['xml_name'] != 'vqmod_opencart.xml') {
					if (file_exists($this->vqmod_script_dir.$vqmod_info['xml_name'])) {
						unlink($this->vqmod_script_dir.$vqmod_info['xml_name']);
					}

					$this->model_extension_enhancement_ea_vqmod_manager->deleteVqmod($vqmod_id);
					$this->model_extension_enhancement_ea_vqmod_manager->deleteVqmodBackup($vqmod_id);
					$this->model_extension_enhancement_ea_vqmod_manager->deleteVqmodEmail($vqmod_id);
					$this->model_extension_enhancement_ea_vqmod_manager->deleteVqmodComments($vqmod_id);
				}
			}
			
			$this->clear_vqcache($return = true);

			$url = $this->getUrlParams();
			
			$this->session->data['success'] = $this->language->get('text_success_delete');

			$this->response->redirect($this->url->link('extension/enhancement/ea_vqmod_manager', '&user_token=' . $this->session->data['user_token'] . $url));
		}

		$this->getList();
	}

	public function vqmod_delete() {			
		$language_data = $this->load->language('extension/enhancement/ea_vqmod_manager');
		foreach($language_data as $key=>$value){
			$data[$key] = $value;
		}	
		
		$url = $this->getUrlParams();
		
		$this->load->model('extension/enhancement/ea_vqmod_manager');
		
		if ($this->validate()) {
			$vqmod_script = $this->request->get['vqmod'];
			$vqmod_id = $this->request->get['vqmod_id'];			

			if (is_file($this->vqmod_script_dir . $vqmod_script)) {
				if (unlink($this->vqmod_script_dir . $vqmod_script)) {
					$this->clear_vqcache(true);
					
					$this->model_extension_enhancement_ea_vqmod_manager->deleteVqmod($vqmod_id);
					$this->model_extension_enhancement_ea_vqmod_manager->deleteVqmodBackup($vqmod_id);
					$this->model_extension_enhancement_ea_vqmod_manager->deleteVqmodEmail($vqmod_id);
					$this->model_extension_enhancement_ea_vqmod_manager->deleteVqmodComments($vqmod_id);
					
					$this->session->data['success'] = $this->language->get('text_success_delete');
					$this->response->redirect($this->url->link('extension/enhancement/ea_vqmod_manager', 'user_token=' . $this->session->data['user_token'] . $url));
				} else {
					$this->session->data['error'] = $this->language->get('error_delete');
				}
			}
		}

		$this->getList();
	}

	public function enable() {		
		$language_data = $this->load->language('extension/enhancement/ea_vqmod_manager');
		foreach($language_data as $key=>$value){
			$data[$key] = $value;
		}

		$this->load->model('extension/enhancement/ea_vqmod_manager');

		if (isset($this->request->get['vqmod_id']) && $this->validate()) {
			$vqmod_id = $this->request->get['vqmod_id'];
			$vqmod_info = $this->model_extension_enhancement_ea_vqmod_manager->getVqmod($vqmod_id);
			
			$vqmod_file = $vqmod_info['xml_name'];
			
			if (is_file($this->vqmod_script_dir . $vqmod_info['xml_name'])) {
				$filename = pathinfo($vqmod_info['xml_name'], PATHINFO_FILENAME);
				rename ($this->vqmod_script_dir  . $filename . ".xml_", $this->vqmod_script_dir . $filename. ".xml" );
				$vqmod_file = $filename.'.xml';
			}
			
			$this->model_extension_enhancement_ea_vqmod_manager->enableVqmod($vqmod_id,$vqmod_file);
			
			$this->clear_vqcache($return = true);
			
			$url = $this->getUrlParams();
			
			$this->session->data['success'] = $this->language->get('text_success_enabled');

			$this->response->redirect($this->url->link('extension/enhancement/ea_vqmod_manager', '&user_token=' . $this->session->data['user_token'] . $url));
		}

		$this->getList();
	}

	public function disable() {		
		$language_data = $this->load->language('extension/enhancement/ea_vqmod_manager');
		foreach($language_data as $key=>$value){
			$data[$key] = $value;
		}

		$this->load->model('extension/enhancement/ea_vqmod_manager');

		if (isset($this->request->get['vqmod_id']) && $this->validate()) {
			$vqmod_id = $this->request->get['vqmod_id'];
			$vqmod_info = $this->model_extension_enhancement_ea_vqmod_manager->getVqmod($vqmod_id);
			
			$vqmod_file = $vqmod_info['xml_name'];
			
			if (is_file($this->vqmod_script_dir . $vqmod_info['xml_name'])) {
				$filename = pathinfo($vqmod_info['xml_name'], PATHINFO_FILENAME);
				rename ($this->vqmod_script_dir . $filename . ".xml", $this->vqmod_script_dir . $filename. ".xml_" );
				$vqmod_file = $filename.'.xml_';
			}
			
			$this->model_extension_enhancement_ea_vqmod_manager->disableVqmod($vqmod_id,$vqmod_file);
			
			$this->clear_vqcache($return = true);

			$url = $this->getUrlParams();
			
			$this->session->data['success'] = $this->language->get('text_success_disabled');

			$this->response->redirect($this->url->link('extension/enhancement/ea_vqmod_manager', '&user_token=' . $this->session->data['user_token'] . $url));
		}

		$this->getList();
	}
		
	protected function getList() {
		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = null;
		}

		if (isset($this->request->get['filter_author'])) {
			$filter_author = $this->request->get['filter_author'];
		} else {
			$filter_author = null;
		}

      	if (isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		} else {
			$filter_status = null;
		}
		
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'status';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'DESC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';
		
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_author'])) {
			$url .= '&filter_author=' . urlencode(html_entity_decode($this->request->get['filter_author'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' .$this->request->get['filter_status'];
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
		
		require_once('../vqmod/vqmod.php');
		
		if (class_exists('VQMod')) {
			$vq_version = VQMod::$_vqversion;
		} else {
			$vq_version = '';
		}
		
		$data['vq_version'] = $this->language->get('text_vqversion').$vq_version;

		if ($this->vqmod_installation_check()) {
			$data['vqmod_is_installed'] = true;
		} else {
			$data['vqmod_is_installed'] = false;
		}

		if (isset($this->session->data['vqmod_installation_error'])) {
			$data['vqmod_installation_error'] = $this->session->data['vqmod_installation_error'];

			unset($this->session->data['vqmod_installation_error']);
		} else {
			$data['vqmod_installation_error'] = '';
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} elseif (!empty($this->error)) {
			$data['error_warning'] = $this->language->get('error_warning');
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}	
		
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/enhancement/ea_vqmod_manager', 'user_token=' . $this->session->data['user_token'] . $url)
		);
		
		$data['settings'] = $this->url->link('extension/enhancement/ea_vqmod_manager_settings', 'user_token=' . $this->session->data['user_token']);
		
		$data['reset_url'] = $this->url->link('extension/enhancement/ea_vqmod_manager', 'user_token=' . $this->session->data['user_token']);

		$data['add_vqmod'] = $this->url->link('extension/enhancement/ea_vqmod_manager/add', 'user_token=' . $this->session->data['user_token'] . $url);
		
		$data['delete'] = $this->url->link('extension/enhancement/ea_vqmod_manager/delete', 'user_token=' . $this->session->data['user_token'] . $url);	
		
		$data['clear_logs'] = $this->url->link('extension/enhancement/ea_vqmod_manager/clear_log', 'user_token=' . $this->session->data['user_token'] . $url);
		
		$data['clear_vqcache'] = $this->url->link('extension/enhancement/ea_vqmod_manager/clear_vqcache', 'user_token=' . $this->session->data['user_token'] . $url);
		
		$data['download_scripts'] = $this->url->link('extension/enhancement/ea_vqmod_manager/download_vqmod_scripts', 'user_token=' . $this->session->data['user_token'] . $url);

		if (class_exists('ZipArchive')) {
			$data['ziparchive'] = true;
		} else {
			$data['ziparchive'] = false;
		}
		
		//$this->checkXmlDates();
		
		$data['vqmodified'] = $this->getXmlPathNames();
		
		$data['vqmodified_count'] = count($data['vqmodified']);

		$filter_data = array(
			'filter_name'	=> $filter_name,
			'filter_author'	=> $filter_author,
			'filter_status'	=> $filter_status,
			'sort'  		=> $sort,
			'order' 		=> $order,
			'start' 		=> ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' 		=> $this->config->get('config_limit_admin')
		);

		$data['vqmods'] = array();

		$vqmods_total = $this->model_extension_enhancement_ea_vqmod_manager->getTotalVqmods($filter_data);

		$vqmods = $this->model_extension_enhancement_ea_vqmod_manager->getVqmods($filter_data);

		foreach ($vqmods as $vqmod) {
			$date_added = date($this->language->get('datetime_format'), strtotime($vqmod['date_added']));

			if (!$vqmod['date_modified'] || $vqmod['date_modified'] == '1970-01-01 01:00:00') {
				$date_modified = 'N/A';
			} else {
				$date_modified = date($this->language->get('datetime_format'), strtotime($vqmod['date_modified']));
			}		
			
			$invalid_xml = '';
			
			if (file_exists($this->vqmod_script_dir.$vqmod['xml_name'])) {
				libxml_use_internal_errors(true);
				$xml_file = simplexml_load_file($this->vqmod_script_dir.$vqmod['xml_name']);

				if (libxml_get_errors()) {
					$invalid_xml = sprintf($this->language->get('highlight'), $this->language->get('error_invalid_xml'));
					if (is_file($this->vqmod_script_dir . $vqmod['xml_name'])) {
						$filename = pathinfo($vqmod['xml_name'], PATHINFO_FILENAME);
						rename ($this->vqmod_script_dir . $filename . ".xml", $this->vqmod_script_dir . $filename. ".xml_" );
					}
					$this->model_extension_enhancement_ea_vqmod_manager->disableVqmod($vqmod['vqmod_id'],$filename. ".xml_");
					libxml_clear_errors();
				}				

				libxml_use_internal_errors(false);
			}
			
			$admin_details = $this->model_extension_enhancement_ea_vqmod_manager->getUserDetails($vqmod['vqmod_id']);
			$admin_name = html_entity_decode($admin_details['username'] . '<br />(' . $admin_details['firstname'] . ' ' . $admin_details['lastname'] . ')', ENT_QUOTES, 'UTF-8');

			$data['vqmods'][] = array(
				'vqmod_id'   	=> $vqmod['vqmod_id'],
				'name'          => $vqmod['name'],
				'file_name'   	=> $vqmod['xml_name'],
				'author'      	=> $vqmod['author'],
				'version'     	=> $vqmod['version'],
				'status'      	=> $vqmod['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
				'date_added'  	=> $date_added,
				'date_modified' => $date_modified,
				'edit'    	  	=> $this->url->link('extension/enhancement/ea_vqmod_manager/genBackup', 'user_token=' . $this->session->data['user_token'] . '&vqmod_id=' . $vqmod['vqmod_id'] . $url),					
				'download'    	=> $this->url->link('extension/enhancement/ea_vqmod_manager/download_vqmod_script', 'user_token=' . $this->session->data['user_token'] . '&vqmod_id=' . $vqmod['vqmod_id'] . $url),
				'delete'      	=> $this->url->link('extension/enhancement/ea_vqmod_manager/vqmod_delete', 'user_token=' . $this->session->data['user_token'] . '&vqmod=' . $vqmod['xml_name'] . '&vqmod_id=' . $vqmod['vqmod_id'] . $url),
				'enable'        => $this->url->link('extension/enhancement/ea_vqmod_manager/enable', 'user_token=' . $this->session->data['user_token'] . '&vqmod_id=' . $vqmod['vqmod_id']),
				'disable'       => $this->url->link('extension/enhancement/ea_vqmod_manager/disable', 'user_token=' . $this->session->data['user_token'] . '&vqmod_id=' . $vqmod['vqmod_id']),
				'enabled'       => $vqmod['status'],
				'contact'       => '',
				'user'      	=> $admin_name,
				'invalid_xml' 	=> $invalid_xml
			);
		}

		$url = '';
		
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_author'])) {
			$url .= '&filter_author=' . urlencode(html_entity_decode($this->request->get['filter_author'], ENT_QUOTES, 'UTF-8'));
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
		
		$data['sort_name'] = $this->url->link('extension/enhancement/ea_vqmod_manager', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url);
		$data['sort_author'] = $this->url->link('extension/enhancement/ea_vqmod_manager', 'user_token=' . $this->session->data['user_token'] . '&sort=author' . $url);
		$data['sort_version'] = $this->url->link('extension/enhancement/ea_vqmod_manager', 'user_token=' . $this->session->data['user_token'] . '&sort=version' . $url);
		$data['sort_status'] = $this->url->link('extension/enhancement/ea_vqmod_manager', 'user_token=' . $this->session->data['user_token'] . '&sort=status' . $url);
		$data['sort_date_added'] = $this->url->link('extension/enhancement/ea_vqmod_manager', 'user_token=' . $this->session->data['user_token'] . '&sort=date_added' . $url);
		$data['sort_date_modified'] = $this->url->link('extension/enhancement/ea_vqmod_manager', 'user_token=' . $this->session->data['user_token'] . '&sort=date_modified' . $url);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
		
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_author'])) {
			$url .= '&filter_author=' . urlencode(html_entity_decode($this->request->get['filter_author'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		$pagination = new Pagination();
		$pagination->total = $vqmods_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('extension/enhancement/ea_vqmod_manager', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}');

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($vqmods_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($vqmods_total - $this->config->get('config_limit_admin'))) ? $vqmods_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $vqmods_total, ceil($vqmods_total / $this->config->get('config_limit_admin')));

		$data['filter_name'] = $filter_name;
		$data['filter_author'] = $filter_author;
		$data['filter_status'] = $filter_status;
		$data['sort'] = $sort;
		$data['order'] = $order;
		
		$data['reload_url'] = $url;

		$data['vqcache'] = array();

		if (is_dir($this->vqcache_dir)) {
			$cacheDir = $this->vqcache_dir;
			$allowext = array("php","twig");
			$data['vqcache'] = $this->scanCache($cacheDir,$allowext);
		}
		
		$data['vqlog'] = '';

		if (is_dir($this->vqmod_logs_dir) && is_readable($this->vqmod_logs_dir)) {
			$vqmod_logs = glob($this->vqmod_logs);
			$vqmod_logs_size = 0;

			if (!empty($vqmod_logs)) {
				foreach ($vqmod_logs as $vqmod_log) {
					$vqmod_logs_size += filesize($vqmod_log);
				}

				if ($vqmod_logs_size > 6291456) {
					$data['error_warning'] = sprintf($this->language->get('error_log_size'), round(($vqmod_logs_size / 1048576), 2));
					$data['vqlog'] = sprintf($this->language->get('error_log_size'), round(($vqmod_logs_size / 1048576), 2));
				} else {
					foreach ($vqmod_logs as $vqmod_log) {
						$data['vqlog'] .= str_pad(basename($vqmod_log), 70, '*', STR_PAD_BOTH) . "\n";
						$data['vqlog'] .= htmlentities(file_get_contents($vqmod_log, FILE_USE_INCLUDE_PATH, null));
					}
				}
			}
		} 

		if ($data['vqlog']) {
			$data['tab_error_log'] = sprintf($this->language->get('highlight'), $this->language->get('tab_error_log'));
		}

		if (is_dir($this->vqmod_dir)) {
			$data['vqmod_path'] = $this->vqmod_dir;
		} else {
			$data['vqmod_path'] = '';
		}

		$vqmod_vars = get_class_vars('VQMod');

		$data['vqmod_vars'] = array();
		
		if (is_file($this->vqmod_path_replaces)) {
			if (!in_array('pathReplaces.php', get_included_files())) {
				include_once($this->vqmod_path_replaces);
			}

			if (!empty($replaces)) {
				$replacement_values = array();

				foreach ($replaces as $key => $value) {
					$replacement_values[] = $value[0] . $this->language->get('text_separator') . $value[1];
				}

				$data['vqmod_vars'][] = array(
					'setting' => $this->language->get('setting_path_replaces'),
					'value'   => implode('<br />', $replacement_values)
				);
			}
		}

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		if ($this->config->get('vq_editor_edit') == 1) {
			$data['vqmod_edit'] = false;			
		} else {
			$data['vqmod_edit'] = true;
		}

		if ($this->config->get('vq_editor_delete') == 1) {
			$data['vqmod_delete'] = false;			
		} else {
			$data['vqmod_delete'] = true;
		}

		if ($this->config->get('vq_editor_upload') == 1) {
			$data['vqmod_upload'] = false;			
		} else {
			$data['vqmod_upload'] = true;
		}

		if ($this->config->get('vq_editor_download') == 1) {
			$data['vqmod_download'] = false;			
		} else {
			$data['vqmod_download'] = true;
		}
		
		if ($this->config->get('vq_editor_perm_edit')  && in_array($this->user->getId(), $this->config->get('vq_editor_perm_edit'))) {
			$data['has_perm_edit'] = true;
		} else {
			$data['has_perm_edit'] = false;
		}
		
		if ($this->config->get('vq_editor_perm_delete') && in_array($this->user->getId(), $this->config->get('vq_editor_perm_delete'))) {
			$data['has_perm_delete'] = true;
		} else {
			$data['has_perm_delete'] = false;
		}
		
		if ($this->config->get('vq_editor_perm_upload') && in_array($this->user->getId(), $this->config->get('vq_editor_perm_upload'))) {
			$data['has_perm_upload'] = true;
		} else {
			$data['has_perm_upload'] = false;
		}
		
		if ($this->config->get('vq_editor_perm_download') && in_array($this->user->getId(), $this->config->get('vq_editor_perm_download'))) {
			$data['has_perm_download'] = true;
		} else {
			$data['has_perm_download'] = false;
		}
		
		if ($this->user->hasPermission('modify', 'common/developer')) {
			$data['has_perm_cache'] = true;
		} else {
			$data['has_perm_cache'] = false;
		}
		
		if ($this->user->hasPermission('access', 'extension/enhancement/ea_vqmod_manager_settings')) {
			$data['has_perm_access'] = true;
		} else {
			$data['has_perm_access'] = false;
		}
		
		if ($this->validate()) {
			$data['has_perm_modify'] = true;
		} else {
			$data['has_perm_modify'] = false;
		}

		$vqmod_comments = $this->model_extension_enhancement_ea_vqmod_manager->getAllComments();

		$data['comments_count'] = count($vqmod_comments);
		
		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/enhancement/vqmod_manager/ea_vqmod_manager', $data));		
	}	

	public function getXmlForm() {		
		$language_data = $this->load->language('extension/enhancement/ea_vqmod_manager');
		foreach($language_data as $key=>$value){
			$data[$key] = $value;
		}
			
		$this->document->addStyle('view/template/extension/enhancement/vqmod_manager/js/jquery/jquery-confirm.css');
		$this->document->addStyle('view/template/extension/enhancement/vqmod_manager/css/ea_vqmod_manager.css');			
		$this->document->addStyle('view/template/extension/enhancement/vqmod_manager/js/ace/ace-diff.css');
		$this->document->addStyle('view/template/extension/enhancement/vqmod_manager/js/datatables/css/dataTables.bootstrap.css');
		
		$this->document->addScript('view/template/extension/enhancement/vqmod_manager/js/ace/diff_match_patch.js');		
		$this->document->addScript('view/template/extension/enhancement/vqmod_manager/js/jquery/jquery-confirm.min.js');		
		$this->document->addScript('view/template/extension/enhancement/vqmod_manager/js/slidereveal.js');
						
		$this->document->addScript('view/template/extension/enhancement/vqmod_manager/js/datatables/js/jquery.dataTables.min.js');
		$this->document->addScript('view/template/extension/enhancement/vqmod_manager/js/datatables/js/dataTables.bootstrap.min.js');
		$this->document->addScript('view/template/extension/enhancement/vqmod_manager/js/datatables/js/dataTables.conditionalPaging.js');
		
		$data['heading_title'] = $this->language->get('heading_edit');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} elseif (!empty($this->error)) {
			$data['error_warning'] = $this->language->get('error_warning');
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = false;
		}

		if (isset($this->error['xml'])) {
			$data['error_xml'] = $this->error['xml'];
		} else {
			$data['error_xml'] = '';
		}
		
		$data['breadcrumbs'] = array();
		
		if (isset($this->request->get['vqmod_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_edit'),
				'href' => $this->url->link('extension/enhancement/ea_vqmod_manager/edit', 'user_token=' . $this->session->data['user_token'] . '&vqmod_id=' . $this->request->get['vqmod_id'])
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_edit'),
				'href' => ''
			);
		}
		
		if (isset($this->request->get['vqmod_id'])) {
			$vqmod_id = $this->request->get['vqmod_id'];
			
			$this->load->model('extension/enhancement/ea_vqmod_manager');
			
			$vqmod_info = $this->model_extension_enhancement_ea_vqmod_manager->getVqmod($vqmod_id);
			
			if (!$vqmod_info) exit;
			
			$backup_info = $this->model_extension_enhancement_ea_vqmod_manager->getBackup($vqmod_id);
			
			if ($backup_info) {
				$data['backup']['xml'] = $backup_info['xml'];
			} else {
				$data['backup']['xml'] = '';
			}			
			
			$data['text_form'] = sprintf($this->language->get('text_edit_vqmod'), $vqmod_info['xml_name']);
			
			$url = $this->getUrlParams(array('vqmod_id' => $vqmod_id));

			$data['action'] = $this->url->link('extension/enhancement/ea_vqmod_manager/edit', 'user_token=' . $this->session->data['user_token'] . $url);	
			
			$this->document->setTitle($vqmod_info['xml_name'] . ' » ' . $data['heading_edit']);
		} else {
			$data['text_form'] = $this->language->get('text_add_vqmod');

			//$url = $this->getUrlParams();
			
			$data['action'] = $this->url->link('extension/enhancement/ea_vqmod_manager/add', 'user_token=' . $this->session->data['user_token']);

			$this->document->setTitle($data['heading_edit']);
		}
		
		$data['cancel'] = $this->url->link('extension/enhancement/ea_vqmod_manager', 'user_token=' . $this->session->data['user_token']);

		$data['vqmod'] = array();		

		if (!empty($vqmod_info)) {
			$data['vqmod']['status'] = $vqmod_info['status'];
		} else {
			$data['vqmod']['status'] = 0;
		}

		if (isset($this->request->post['xml'])) {
			$data['vqmod']['xml'] = html_entity_decode($this->request->post['xml'], ENT_QUOTES, 'UTF-8');
		} elseif (!empty($vqmod_info)) {
			$data['vqmod']['xml'] = html_entity_decode(file_get_contents($this->vqmod_script_dir .  $vqmod_info['xml_name']), ENT_QUOTES, 'UTF-8');
		} else {
			$data['vqmod']['xml'] = '';
		}
		
		if (isset($this->request->get['vqmod_id'])) {
			$data['vqmod_id'] = $this->request->get['vqmod_id'];
			$data['download_vqmod'] = $this->url->link('extension/enhancement/ea_vqmod_manager/download_vqmod_script', 'user_token=' . $this->session->data['user_token'] . '&vqmod_id=' . $this->request->get['vqmod_id']);
		} else {
			$data['vqmod_id'] = '';
			$data['download_vqmod'] = '';
		}
		
		if (isset($this->request->get['vqmod_id']) && !$this->validateXml($data['vqmod']['xml'])) {
			$data['vqmod_xml_error'] = $this->error['xml'];
			if (($pos = strpos($this->error['xml'], "Line:")) !== FALSE) { 
    			$data['error_line'] = substr($this->error['xml'], $pos+5); 
			}
		} else {
			$data['vqmod_xml_error'] = '';
			$data['error_line'] = '';
		}
		
		// Get XML File Paths
		$data['vqmodified'] = array();
		$vqmodified_count = 0;
		
		if ($data['vqmod']['xml'] != '' && $this->validateXml($data['vqmod']['xml'])) {
			$xml = $data['vqmod']['xml'];

			$dom = new DOMDocument('1.0', 'UTF-8');
			$dom->preserveWhiteSpace = false;
			$dom->loadXml($xml);

			foreach ($dom->getElementsByTagName('file') as $node) {
				$names = explode(',', $node->getAttribute('name'));	
				foreach ($names as $name) {	
					$data['vqmodified'][] = array(
						'name' => $name,
						'auth' => $dom->getElementsByTagName('author'),
						'file' => basename($xml)
					);
					$vqmodified_count++;
				}
			}
		}
		
		$data['vqmodified_count'] = (int)$vqmodified_count;		
		
		if (isset($this->request->get['vqmod_id'])) {
			$vqmod_comments = $this->model_extension_enhancement_ea_vqmod_manager->getVqmodComments($this->request->get['vqmod_id']);
			$data['comments_count'] = count($vqmod_comments);
		} else {
			$vqmod_comments = '';
			$data['comments_count'] = 0;
		}

		if ($this->config->get('vq_editor_theme')) {
			$data['themejs'] = $this->config->get('vq_editor_theme');
			$data['themename'] = str_replace("theme-","",$data['themejs']);
		} else {
			$data['themejs'] = 'theme-cobalt';
			$data['themename'] = 'cobalt';
		}
		
		if ($this->config->get('vq_editor_screen')) {
			$screen_mode = (int)$this->config->get('vq_editor_screen');
		} else if ($this->config->get('vq_editor_screen_mode')) {
			$screen_mode = (int)$this->config->get('vq_editor_screen_mode');
		} else {
			$screen_mode = 1;
		}

		$data['vqlog'] = '';

		if (is_dir($this->vqmod_logs_dir) && is_readable($this->vqmod_logs_dir)) {
			$vqmod_logs = glob($this->vqmod_logs);

			if (!empty($vqmod_logs)) {
				foreach ($vqmod_logs as $vqmod_log) {
					$data['vqlog'] .= str_pad(basename($vqmod_log), 70, '*', STR_PAD_BOTH) . "\n";
					$data['vqlog'] .= htmlentities(file_get_contents($vqmod_log, FILE_USE_INCLUDE_PATH, null));
				}
			}
		} 

		if ($data['vqlog']) {
			$data['tab_error_log'] = sprintf($this->language->get('highlight'), $this->language->get('tab_error_log'));
		}

		if ($this->config->get('vq_editor_download') == 1) {
			$data['vqmod_download'] = false;			
		} else {
			$data['vqmod_download'] = true;
		}
		
		if ($this->config->get('vq_editor_perm_download')  && in_array($this->user->getId(), $this->config->get('vq_editor_perm_download'))) {
			$data['has_perm_download'] = true;
		} else {
			$data['has_perm_download'] = false;
		}
		
		$data['user_token'] = $this->session->data['user_token'];
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		if ($screen_mode == 2) {		
			$this->response->setOutput($this->load->view('extension/enhancement/vqmod_manager/ea_vqmod_manager_diff_form', $data));
		} else {
			$this->response->setOutput($this->load->view('extension/enhancement/vqmod_manager/ea_vqmod_manager_form', $data));
		}
	}		
	
	private function checkXmlDates() {
		$vqmod_scripts = $this->getXmls();
		
		$this->load->model('extension/enhancement/ea_vqmod_manager');
		
		foreach ($vqmod_scripts as $vqmod_script) {
			libxml_use_internal_errors(true);
			$xml = simplexml_load_file($vqmod_script);
			
			if (!libxml_get_errors()) {
				$xmlfile = basename($vqmod_script);
				$extension = pathinfo($vqmod_script, PATHINFO_EXTENSION);

				if ($extension == 'xml_') {
					$status = false;
				} else {
					$status = true;
				}				

				$add_data = array(
					'name'		=> isset($xml->id) ? $xml->id : '',
					'xml_name'	=> $xmlfile,
					'author'	=> isset($xml->author) ? $xml->author : '',
					'version' 	=> isset($xml->version) ? $xml->version : '',
					'status'    => $status ? '1' : '0',
				);

				$date_added_result = $this->model_extension_enhancement_ea_vqmod_manager->getDateAdded($xmlfile);
				if (empty($date_added_result)) {
					$this->model_extension_enhancement_ea_vqmod_manager->addVqmod($add_data);
				}
			}
			libxml_clear_errors();			
			libxml_use_internal_errors(false);				
		}
	}
	
	private function getXmlPathNames() {			
		$vqmod_scripts = $this->getXmls();
		
		$vqmodified = array();
		
		foreach ($vqmod_scripts as $vqmod_script) {				
			libxml_use_internal_errors(true);
			$xml = simplexml_load_file($vqmod_script);
			
			$extension = pathinfo($vqmod_script, PATHINFO_EXTENSION);
			
			if ($extension == 'xml') {				
				if (!libxml_get_errors()) {
					$files = $xml->file;
					foreach ($files as $file) {					
						$names = explode(',', $file->attributes()->name);
						foreach ($names as $name) {	
							$vqmodified[] = array(
								'name' => $name,
								'auth' => $xml->author,
								'file' => basename($vqmod_script)
							);
						}
					}
				}
			}
			
			libxml_clear_errors();			
			libxml_use_internal_errors(false);			
		}
		
		return $vqmodified;
	}
	
	public function getXmls() {
		$vqmod_scripts = array();
		$enabled_vqmod_scripts = glob($this->vqmod_script_dir . '*.xml');
		$disabled_vqmod_scripts = glob($this->vqmod_script_dir . '*.xml_');

		if (!empty($enabled_vqmod_scripts)) {			
			$vqmod_scripts = array_merge($vqmod_scripts, $enabled_vqmod_scripts);
		}

		if (!empty($disabled_vqmod_scripts)) {
			$vqmod_scripts = array_merge($vqmod_scripts, $disabled_vqmod_scripts);
		}
		return $vqmod_scripts;
	}
	
	public function vqmod_upload() {
		$language_data = $this->load->language('extension/enhancement/ea_vqmod_manager');
		foreach($language_data as $key=>$value){
			$data[$key] = $value;
		}
		
		$json = array();

		if (!$this->validate()) {
			$json['error'] = $this->language->get('error_permission_upload');
		}
		
		if (isset($this->request->files['vqmod_file']['name'])) {
			if ($this->request->files['vqmod_file']['type'] != 'text/xml') {
				$json['error'] = $this->language->get('error_filetype');
			}

			if ($this->request->files['vqmod_file']['error'] != UPLOAD_ERR_OK) {
				$json['error'] = $this->language->get('error_upload_' . $this->request->files['vqmod_file']['error']);
			}
		} else {
			$json['error'] = $this->language->get('error_upload');
		}		

		if (!$json) {
			$file = html_entity_decode($this->request->files['vqmod_file']['name'], ENT_QUOTES, 'UTF-8');
			$filepath = $this->vqmod_script_dir . $file;
			
			if (move_uploaded_file($this->request->files['vqmod_file']['tmp_name'], $filepath) === false) {
				$json['error'] = $this->language->get('error_move');
			} 
			
			if (!$json) {
				if (is_file($filepath)) {
					$xml = html_entity_decode(file_get_contents($filepath), ENT_QUOTES, 'UTF-8');
					$filename = pathinfo($filepath, PATHINFO_FILENAME);
					$extension = pathinfo($filepath, PATHINFO_EXTENSION);
					
					libxml_use_internal_errors(true);
					simplexml_load_file($filepath);

					if (libxml_get_errors()) {
						rename ($this->vqmod_script_dir  . $filename . ".xml" , $this->vqmod_script_dir . $filename. ".xml_" );					
						$json['error'] = $this->language->get('error_xml_invalid');
						libxml_clear_errors();
					}	
					
					if (!$json) {
						$this->clear_vqcache(true);
						
						$json['success'] = sprintf($this->language->get('text_success_upload'), $filename . ".xml");
						//$json['location'] = '';
					}								
				} else {
					$json['error'] = $this->language->get('error_file');
				}
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function download_vqmod_script() {
		$this->load->model('extension/enhancement/ea_vqmod_manager');
			
		$vqmod_info = $this->model_extension_enhancement_ea_vqmod_manager->getVqmod($this->request->get['vqmod_id']);
			
		if (!$vqmod_info) exit;	
		
		$filename = $vqmod_info['xml_name'];		

		$target = $this->vqmod_script_dir.$filename;
		
		$this->script_zip_send($target, $filename);
	}

	public function download_vqmod_scripts() {
		$language_data = $this->load->language('extension/enhancement/ea_vqmod_manager');
		foreach($language_data as $key=>$value){
			$data[$key] = $value;
		}
		
		if ($this->validate()) {
			$vqmod_scripts = array();

			$enabled_vqmod_scripts = glob($this->vqmod_script_dir . '*.xml');
			$disabled_vqmod_scripts = glob($this->vqmod_script_dir . '*.xml_');

			if (!empty($enabled_vqmod_scripts)) {			
				$vqmod_scripts = array_merge($vqmod_scripts, $enabled_vqmod_scripts);
			}

			if (!empty($disabled_vqmod_scripts)) {
				$vqmod_scripts = array_merge($vqmod_scripts, $disabled_vqmod_scripts);
			}			
			
			$targets = $vqmod_scripts;

			$this->zip_send($targets, 'vqmod_scripts_backup');
		} else {
			$this->response->redirect($this->url->link('extension/enhancement/ea_vqmod_manager', 'user_token=' . $this->session->data['user_token'], true));
		}
	}

	private function script_zip_send($target) {
		$temp = tempnam('tmp', 'zip');

		$zip = new ZipArchive();
		$zip->open($temp, ZipArchive::OVERWRITE);

		if (is_file($target)) {
			$zip->addFile($target, basename($target));
		}

		$zip->close();

		header('Pragma: public');
		header('Expires: 0');
		header('Content-Description: File Transfer');
		header('Content-Type: application/zip');
		header('Content-Disposition: attachment; filename=' . basename($target) . '.zip');
		header("Content-Length: " . filesize($temp));
		header('Content-Transfer-Encoding: binary');
		readfile($temp);
		unlink($temp);
	}

	private function zip_send($targets, $filename = 'vqmod_backup') {
		$temp = tempnam('tmp', 'zip');

		$zip = new ZipArchive();
		$zip->open($temp, ZipArchive::OVERWRITE);

		foreach ($targets as $target) {
			if (is_file($target)) {
				$zip->addFile($target, basename($target));
			}
		}

		$zip->close();

		header('Pragma: public');
		header('Expires: 0');
		header('Content-Description: File Transfer');
		header('Content-Type: application/zip');
		header('Content-Disposition: attachment; filename=' . $filename . '_' . date('Y-m-d_H-i') . '.zip');
		header('Content-Transfer-Encoding: binary');
		readfile($temp);
		unlink($temp);
	}
	
	public function scanCache($cacheDir, $allowext, $cacheData=array()) {
		$dirContent = scandir($cacheDir);
		foreach($dirContent as $key => $content) {
			$cachefile = $cacheDir.'/'.$content;
			$ext = substr($content, strrpos($content, '.') + 1);

			if(in_array($ext, $allowext)) {
				if(is_file($cachefile) && is_readable($cachefile)) {
					$cacheData[] = basename($cachefile);
				}
			}
		}
		return $cacheData;
	}

	public function clear_vqcache($return = false) {
		$language_data = $this->load->language('extension/enhancement/ea_vqmod_manager');
		foreach($language_data as $key=>$value){
			$data[$key] = $value;
		}
		
		if ($this->validate()) {
			$files = glob($this->vqcache_files);

			if ($files) {
				foreach ($files as $file) {
					if (is_file($file)) {
						unlink($file);
					}
				}
			}

			if (is_file($this->vqmod_modcache)) {
				unlink($this->vqmod_modcache);
			}

			if ($return) {
				return;
			}

			$this->session->data['success'] = $this->language->get('text_success_clear_vqcache');
		}

		$this->response->redirect($this->url->link('extension/enhancement/ea_vqmod_manager', 'user_token=' . $this->session->data['user_token'], true));
	}

	public function clear_log($return = false) {
		$language_data = $this->load->language('extension/enhancement/ea_vqmod_manager');
		foreach($language_data as $key=>$value){
			$data[$key] = $value;
		}
		
		if ($this->validate()) {
			if (is_dir($this->vqmod_logs_dir)) {
				$files = glob($this->vqmod_logs);

				if ($files) {
					foreach ($files as $file) {
						unlink($file);
					}
				}

				if ($return) {
					return;
				}
				
				$this->session->data['success'] = $this->language->get('text_success_clear_log');
			}
		}

		$this->response->redirect($this->url->link('extension/enhancement/ea_vqmod_manager', 'user_token=' . $this->session->data['user_token'], true));
	}

	private function vqmod_installation_check() {
		$language_data = $this->load->language('extension/enhancement/ea_vqmod_manager');
		foreach($language_data as $key=>$value){
			$data[$key] = $value;
		}
		
		if (!function_exists('simplexml_load_file')) {
			$this->session->data['vqmod_installation_error'] = $this->language->get('error_simplexml');
			return false;
		}

		if (!is_dir($this->vqmod_dir)) {
			$this->session->data['vqmod_installation_error'] = $this->language->get('error_vqmod_dir');
			return false;
		}

		if (!is_file($this->vqmod_dir . 'vqmod.php')) {
			$this->session->data['vqmod_installation_error'] = $this->language->get('error_vqmod_core');
			return false;
		}

		if (!is_dir($this->vqmod_script_dir)) {
			$this->session->data['vqmod_installation_error'] = $this->language->get('error_vqmod_script_dir');
			return false;
		}

		if (!is_dir($this->vqcache_dir)) {
			$this->session->data['vqmod_installation_error'] = $this->language->get('error_vqcache_dir');
			return false;
		}

		if (!is_file($this->vqmod_opencart_script)) {
			if (is_file($this->vqmod_opencart_script . '_')) {
				$this->session->data['error'] = $this->language->get('error_opencart_xml_disabled');
			} else {
				$this->session->data['vqmod_installation_error'] = $this->language->get('error_opencart_xml');
				return false;
			}
		}

		if (version_compare(VERSION, '3.0.0', '>=')) {
			libxml_use_internal_errors(true);
			$xml = simplexml_load_file($this->vqmod_opencart_script);
			libxml_clear_errors();

			if ((isset($xml->vqmver)) && (version_compare($xml->vqmver, '2.6.1', '<'))) {
				$this->session->data['vqmod_installation_error'] = $this->language->get('error_opencart_xml_version');
				return false;
			}
		}

		if (!class_exists('VQMod')) {
			if (is_file($this->vqmod_dir . 'install/index.php') && is_file($this->vqmod_dir . 'install/ugrsr.class.php')) {
				$this->session->data['vqmod_installation_error'] = sprintf($this->language->get('error_vqmod_install_link'), HTTP_CATALOG . 'vqmod/install');
			} else {
				$this->session->data['vqmod_installation_error'] = $this->language->get('error_vqmod_opencart_integration');
			}
			return false;
		}

		if ((is_dir($this->vqmod_logs_dir) && !is_writable($this->vqmod_logs_dir)) || (!is_writable($this->vqmod_dir))) {
			$this->session->data['vqmod_installation_error'] = $this->language->get('error_error_log_write');
			return false;
		}

		if (!is_writable($this->vqmod_script_dir)) {
			$this->session->data['vqmod_installation_error'] = $this->language->get('error_vqmod_script_write');
			return false;
		}

		if (!is_writable($this->vqcache_dir)) {
			$this->session->data['vqmod_installation_error'] = $this->language->get('error_vqcache_write');
			return false;
		}
         
		$vqcache_files = array(
			'vq2-system_startup.php'
		);
               
		foreach ($vqcache_files as $vqcache_file) {
			if (!is_file($this->vqcache_dir . $vqcache_file) && !is_file($this->vqmod_opencart_script . '_')) {
				$this->session->data['vqmod_installation_error'] = $this->language->get('error_vqcache_files_missing');
				return false;
			}
		}

		return true;
	}

	public function autoCompleteName() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			if (isset($this->request->get['filter_name'])) {
				$filter_name = $this->request->get['filter_name'];
			} else {
				$filter_name = '';
			}
			
			$this->load->model('extension/enhancement/ea_vqmod_manager');

			$filter_data = array(
				'filter_name'      => $filter_name,
				'start'            => 0,
				'limit'            => 5
			);

			$results = $this->model_extension_enhancement_ea_vqmod_manager->getVqmods($filter_data);

			foreach ($results as $result) {
				$json[] = array(
					'name'      => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
					'vqmod_id' 	=> $result['vqmod_id']
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

	public function autoCompleteAuthor() {
		$json = array();

		if (isset($this->request->get['filter_author'])) {
			if (isset($this->request->get['filter_author'])) {
				$filter_author = $this->request->get['filter_author'];
			} else {
				$filter_author = '';
			}
			
			$this->load->model('extension/enhancement/ea_vqmod_manager');

			$filter_data = array(
				'start'            => 0,
				'limit'            => 5
			);

			$results = $this->model_extension_enhancement_ea_vqmod_manager->getVqmodAuthors($filter_data);

			foreach ($results as $result) {
				$json[] = array(
					'author'   	=> strip_tags(html_entity_decode($result['author'], ENT_QUOTES, 'UTF-8')),
					'vqmod_id' 	=> $result['vqmod_id']
				);
			}
		}

		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['author'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	private function contains($needle, $haystack) {
    	return strpos($haystack, $needle) !== false;
	}
			
	private function beginsWith($needle, $haystack) {
		 $length = strlen($haystack);
		 return (substr($needle, 0, $length) === $haystack);
	}
								  
	private function endsWith($needle, $haystack) {
		$length = strlen($haystack);
		if ($length == 0) {
			return true;
		}

		return (substr($needle, -$length) === $haystack);
	}

	private function validateXml($modxml) {
		$error = false;

		if (!$error) {
			$xml = html_entity_decode(rawurldecode($modxml), ENT_QUOTES, 'UTF-8');

			libxml_use_internal_errors(true);

			$dom = new DOMDocument('1.0', 'UTF-8');

			if(!$dom->loadXml(html_entity_decode($xml, ENT_QUOTES, 'UTF-8'))) {

			    foreach (libxml_get_errors() as $error) {
			        $msg = '';

			        switch ($error->level) {
			            case LIBXML_ERR_WARNING :
			                $msg .= "Warning $error->code: ";
			                break;
			            case LIBXML_ERR_ERROR :
			                $msg .= "Error $error->code: ";
			                break;
			            case LIBXML_ERR_FATAL :
			                $msg .= "Fatal Error $error->code: ";
			                break;
			        }

			        $msg .= trim ( $error->message ) . "\nLine: $error->line";

			        $error = $msg;
			    }

			    libxml_clear_errors();
			}

			libxml_use_internal_errors(false);
		}

		if ($error) {
			$this->error['xml'] = $error;
		}

		return !$this->error;
	}

	protected function validateXmlForm() {
		if (!$this->user->hasPermission('modify', 'extension/enhancement/ea_vqmod_manager')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		$error = false;

		if (empty($this->request->post['xml'])) {
			$error = $this->language->get('error_required');
		}

		if (!$error) {
			$xml = html_entity_decode(rawurldecode($this->request->post['xml']), ENT_QUOTES, 'UTF-8');

			libxml_use_internal_errors(true);

			$dom = new DOMDocument('1.0', 'UTF-8');

			if(!$dom->loadXml(html_entity_decode($xml, ENT_QUOTES, 'UTF-8'))) {

			    foreach (libxml_get_errors() as $error) {
			        $msg = '';

			        switch ($error->level) {
			            case LIBXML_ERR_WARNING :
			                $msg .= "Warning $error->code: ";
			                break;
			            case LIBXML_ERR_ERROR :
			                $msg .= "Error $error->code: ";
			                break;
			            case LIBXML_ERR_FATAL :
			                $msg .= "Fatal Error $error->code: ";
			                break;
			        }

			        $msg .= trim ( $error->message ) . "\nLine: $error->line";

			        $error = $msg;
			    }

			    libxml_clear_errors();
			}

			libxml_use_internal_errors(false);
		}

		if (!$error && (!$dom->getElementsByTagName('id') || $dom->getElementsByTagName('id')->length == 0 || $dom->getElementsByTagName('id')->item(0)->textContent == '')) {
			$error = $this->language->get('error_name');
		}

		if (!$error && (!$dom->getElementsByTagName('version') || $dom->getElementsByTagName('version')->length == 0 || $dom->getElementsByTagName('version')->item(0)->textContent == '')) {
			$error = $this->language->get('error_version');
		}

		if (!$error && (!$dom->getElementsByTagName('vqmver') || $dom->getElementsByTagName('vqmver')->length == 0 || $dom->getElementsByTagName('vqmver')->item(0)->textContent == '')) {
			$error = $this->language->get('error_vqmver');
		}

		if (!$error && (!$dom->getElementsByTagName('author') || $dom->getElementsByTagName('author')->length == 0 || $dom->getElementsByTagName('author')->item(0)->textContent == '')) {
			$error = $this->language->get('error_author');
		}

		if ($error) {
			$this->error['xml'] = $error;
		}

		return !$this->error;
	}
	
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/enhancement/ea_vqmod_manager')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}	
	
	protected function cleanFilename($string) {
   		$string = utf8_strtolower($string); // Convert to lower case.
		$string = str_replace(' ', '_', $string); // Replaces all spaces with underscore.
		$string = str_replace('.xml', '', $string); // Remove xml extension.
   		$string = preg_replace('/[^A-Za-z0-9\_]/', '', $string); // Removes special chars.
   		return preg_replace('/_ */', '_', $string); // Replaces multiple underscore with single one.
	}


// ****************************************************************************************************** //	
// *********************************************  Comment Stuff  **************************************** //	
// ****************************************************************************************************** //	
	public function getAllComments() {		
		$language_data = $this->load->language('extension/enhancement/ea_vqmod_manager');
		foreach($language_data as $key=>$value){
			$data[$key] = $value;
		}
		
		$requestData = $_REQUEST;

		$columns = array( 
			0 => 'comment_id', 
			1 => 'name',
			2 => 'comment',
			3 => 'vc.date_added'
		);
		
		$sql = "SELECT *, vc.date_added AS date_added FROM `" . DB_PREFIX . "ea_vqmod_manager` vm LEFT JOIN `" . DB_PREFIX . "ea_vqmod_comment` vc ON (vm.vqmod_id = vc.vqmod_id) WHERE `comment` != ''";
		
		$query = $this->db->query($sql);

		$totalData = count($query->rows);
		$totalFiltered = $totalData;
		
		if(!empty($requestData['search']['value'])) {
			$sql .= " AND `name` LIKE '%".$requestData['search']['value']."%'";	
		}
		
		$query = $this->db->query($sql);		
		$totalFiltered = count($query->rows);
	
		$sql .= " ORDER BY ". $columns[$requestData['order'][0]['column']]." ".$requestData['order'][0]['dir']." LIMIT ".(int)$requestData['start']." ,".(int)$requestData['length']." ";
		
		$query = $this->db->query($sql);		

		$data = array();
		
		foreach ($query->rows as $row) {
			$nestedData = array();  
			$nestedData[] = '<input type="checkbox" name="comment_id" value="'.$row["comment_id"].'" class="delinput" />';
			$nestedData[] = html_entity_decode($row["name"], ENT_QUOTES, 'UTF-8');
			$nestedData[] = html_entity_decode($row["comment"], ENT_QUOTES, 'UTF-8');
			$nestedData[] = $row["date_added"];
			$nestedData[] = '<button type="button" data-toggle="tooltip" title="'.$this->language->get('button_delete_comment').'" class="btn btn-danger btn-xs delcomment"><i class="fa fa-trash-o"></i></button>';
			$data[] = $nestedData;
		}

		$json_data = array(
			"draw"            => intval($requestData['draw']),
			"recordsTotal"    => intval($totalData),
			"recordsFiltered" => intval($totalFiltered),
			"data"            => $data
		);	

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json_data));
	}	

	public function getVqmodComments() {		
		$language_data = $this->load->language('extension/enhancement/ea_vqmod_manager');
		foreach($language_data as $key=>$value){
			$data[$key] = $value;
		}
		
		$requestData = $_REQUEST;

		$columns = array( 
			0 => 'comment_id',
			1 => 'comment',
			2 => 'date_added'
		);
		
		$sql = "SELECT * FROM `" . DB_PREFIX . "ea_vqmod_comment` WHERE `vqmod_id` = '" . (int)$this->request->get['vqmod_id'] . "'";
		
		$query = $this->db->query($sql);

		$totalData = count($query->rows);
		$totalFiltered = $totalData;
	
		$sql .= " ORDER BY ". $columns[$requestData['order'][0]['column']]." ".$requestData['order'][0]['dir']." LIMIT ".(int)$requestData['start']." ,".(int)$requestData['length']." ";
		
		$query = $this->db->query($sql);		

		$data = array();
		
		foreach ($query->rows as $row) {
			$nestedData = array();  
			$nestedData[] = '<input type="checkbox" name="comment_id" value="'.$row["comment_id"].'" class="delinput" />';
			$nestedData[] = html_entity_decode($row["comment"], ENT_QUOTES, 'UTF-8');
			$nestedData[] = $row["date_added"];
			$nestedData[] = '<button type="button" data-toggle="tooltip" title="'.$this->language->get('button_delete_comment').'" class="btn btn-danger btn-xs delcomment"><i class="fa fa-trash-o"></i></button>';
			$data[] = $nestedData;
		}

		$json_data = array(
			"draw"            => intval($requestData['draw']),
			"recordsTotal"    => intval($totalData),
			"recordsFiltered" => intval($totalFiltered),
			"data"            => $data
		);	

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json_data));
	}
	
	public function deleteComment() {
		$language_data = $this->load->language('extension/enhancement/ea_vqmod_manager');
		foreach($language_data as $key=>$value){
			$data[$key] = $value;
		}
		
		$comment_id = $this->request->get['comment_id'];
		
		$json = array();

		$this->load->model('extension/enhancement/ea_vqmod_manager');
		
		if (!$this->validate()) {
			$json['error'] = $this->language->get('error_permission');
		}
		
		if (!$json) {
			$this->model_extension_enhancement_ea_vqmod_manager->deleteVqmodComment($comment_id);
			$json['success'] = $this->language->get('text_success_comment_deleted');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}	
	
	public function getFormComment() {		
		$language_data = $this->load->language('extension/enhancement/ea_vqmod_manager');
		foreach($language_data as $key=>$value){
			$data[$key] = $value;
		}
		
		$data['vqmod_id'] = (int)$this->request->get['vqmod_id'];
		
		$data['user_token'] = $this->session->data['user_token'];

		if ($this->request->get['route'] == 'extension/enhancement/ea_vqmod_manager/edit') {
			$data['reload_main'] = '';
		} else {
			$data['reload_main'] = html_entity_decode($this->url->link('extension/enhancement/ea_vqmod_manager', 'user_token=' . $this->session->data['user_token']));
		}	
		
		$this->response->setOutput($this->load->view('extension/enhancement/vqmod_manager/ea_vqmod_manager_comment_form', $data));
	}

	public function saveComment() {		
		$language_data = $this->load->language('extension/enhancement/ea_vqmod_manager');
		foreach($language_data as $key=>$value){
			$data[$key] = $value;
		}
		
		$vqmod_id = (int)$this->request->get['vqmod_id'];

		$this->load->model('extension/enhancement/ea_vqmod_manager');	
		
		$json = array();

		if (isset($this->error['vq_comment'])) {
			$json['error_comment'] = $this->error['vq_comment'];
		}		

		if (!$json) {
			if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateCommentForm()) {
				$this->model_extension_enhancement_ea_vqmod_manager->addVqmodComment($vqmod_id,$this->request->post['vqmod_comment']);			
				$json['success'] = $this->language->get('text_success_comment_saved');
			}
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	protected function validateCommentForm() {		
		$language_data = $this->load->language('extension/enhancement/ea_vqmod_manager');
		foreach($language_data as $key=>$value){
			$data[$key] = $value;
		}

		if ((utf8_strlen($this->request->post['vqmod_comment']) < 10) || (utf8_strlen($this->request->post['vqmod_comment']) > 3000)) {
			$this->error['vq_comment'] = $this->language->get('error_comment');
		}

		return !$this->error;
	}
	
	
// ****************************************************************************************************** //	
// *********************************************  Contact Stuff  **************************************** //	
// ****************************************************************************************************** //	
	
	public function getFormContact() {		
		$language_data = $this->load->language('extension/enhancement/ea_vqmod_manager');
		foreach($language_data as $key=>$value){
			$data[$key] = $value;
		}
		
		$this->document->setTitle($this->language->get('heading_contact'));
		
		$this->load->model('extension/enhancement/ea_vqmod_manager');

		$vqmod_id = $this->request->get['vqmod_id'];

		$vqmod_info = $this->model_extension_enhancement_ea_vqmod_manager->getVqmod($vqmod_id);
		$email_result = $this->model_extension_enhancement_ea_vqmod_manager->getVqmodEmail($vqmod_id);

		$data['from_name'] = $this->config->get('config_owner');
		$data['from_email'] = $this->config->get('config_email');
		
		if ($email_result) {
			$data['dev_email'] = $email_result['email'];
		} else {
			$data['dev_email'] = '';
		}
		
		if ($vqmod_info) {
			$data['email_subject'] = sprintf($this->language->get('text_email_subject'), $vqmod_info['name']);
		} else {
			$data['email_subject'] = '';
		}
		
		$data['vqmod_id'] = (int)$vqmod_id;

		$max_upload = (int)(ini_get('upload_max_filesize'));
		$max_post = (int)(ini_get('post_max_size'));
		$max_memory = (int)(ini_get('memory_limit'));
		$file_limit = min($max_upload, $max_post, $max_memory);
		
		$data['file_limit'] = 'Max: ' . $file_limit . 'MB';

		$data['userPermission'] = $this->user->hasPermission('modify', 'tool/upload');
		
		$data['user_token'] = $this->session->data['user_token'];
		
		$this->response->setOutput($this->load->view('extension/enhancement/vqmod_manager/ea_vqmod_manager_contact_form', $data));
	}
	
	public function uploadAttachment() {		
		$language_data = $this->load->language('extension/enhancement/ea_vqmod_manager');
		foreach($language_data as $key=>$value){
			$data[$key] = $value;
		}

		$json = array();

		if (!$this->user->hasPermission('modify', 'tool/upload')) {
			$json['error'] = $this->language->get('error_permission_upload');
		}

		if (empty($this->request->files['file']['name']) || !is_file($this->request->files['file']['tmp_name'])) {
			$json['error'] = $this->language->get('error_upload');
		}

		if (!$json) {
			$filename = html_entity_decode($this->request->files['file']['name'], ENT_QUOTES, 'UTF-8');

			if ((utf8_strlen($filename) < 3) || (utf8_strlen($filename) > 128)) {
				$json['error'] = $this->language->get('error_filename');
			}

			$allowed = array();

			$extension_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_ext_allowed'));

			$filetypes = explode("\n", $extension_allowed);

			foreach ($filetypes as $filetype) {
				$allowed[] = trim($filetype);
			}

			if (!in_array(strtolower(substr(strrchr($filename, '.'), 1)), $allowed)) {
				$json['error'] = $this->language->get('error_upload_filetype');
			}

			$allowed = array();

			$mime_allowed = preg_replace('~\r?\n~', "\n", $this->config->get('config_file_mime_allowed'));

			$filetypes = explode("\n", $mime_allowed);

			foreach ($filetypes as $filetype) {
				$allowed[] = trim($filetype);
			}

			if (!in_array($this->request->files['file']['type'], $allowed)) {
				$json['error'] = $this->language->get('error_upload_filetype');
			}

			if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
				$json['error'] = $this->language->get('error_upload_' . $this->request->files['file']['error']);
			}
		}

		if (!$json) {
			$file = $filename . '.' . token(32);

			move_uploaded_file($this->request->files['file']['tmp_name'], DIR_UPLOAD . $file);

			$this->load->model('tool/upload');

			$json['code'] = $this->model_tool_upload->addUpload($filename, $file);
			
			$json['filename'] = $filename;

			$json['success'] = $this->language->get('text_success_file_upload');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function sendMessage() {		
		$language_data = $this->load->language('extension/enhancement/ea_vqmod_manager');
		foreach($language_data as $key=>$value){
			$data[$key] = $value;
		}
		
		$vqmod_id = $this->request->get['vqmod_id'];

		$this->load->model('extension/enhancement/ea_vqmod_manager');

		$email_result = $this->model_extension_enhancement_ea_vqmod_manager->getVqmodEmail($vqmod_id);		
		
		$json = array();

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateContactForm()) {
			if (!$email_result) {
				$this->model_extension_enhancement_ea_vqmod_manager->addVqmodEmail($vqmod_id,$this->request->post['to_email']);
			} else {
				if ($email_result['email'] != $this->request->post['to_email']) {
					$this->model_extension_enhancement_ea_vqmod_manager->editVqmodEmail($vqmod_id,$this->request->post['to_email']);
				}
			}
			
			$mail = new Mail($this->config->get('config_mail_engine'));
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

			$temp_name = '';					
			if($this->request->post['file']) {
			  $this->load->model('tool/upload');
			  $upload_info = $this->model_tool_upload->getUploadByCode($this->request->post['file']);
			  $file_name = DIR_UPLOAD.$upload_info['filename'];
			  $temp_name = DIR_UPLOAD.$upload_info['name'];
			  copy($file_name,$temp_name);
			}

			$mail->setTo($this->request->post['to_email']);

			if($temp_name != ''){
				$mail->AddAttachment($temp_name);
			}

			$mail->setFrom($this->request->post['from_email']);
			$mail->setReplyTo($this->request->post['from_email']);
			$mail->setSender(html_entity_decode($this->request->post['from_name'], ENT_QUOTES, 'UTF-8'));
			$mail->setSubject(html_entity_decode($this->request->post['subject'], ENT_QUOTES, 'UTF-8'));
			$mail->setText($this->request->post['dev_message']);
			$mail->send();
			
			if(isset($temp_name) && $temp_name != ''){
				unlink($temp_name);
			}
			
			if(isset($this->request->post['send_copy'])) {
				if($this->request->post['send_copy'] == 'on') {
					$mail = new Mail($this->config->get('config_mail_engine'));
					$mail->parameter = $this->config->get('config_mail_parameter');
					$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
					$mail->smtp_username = $this->config->get('config_mail_smtp_username');
					$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
					$mail->smtp_port = $this->config->get('config_mail_smtp_port');
					$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

					$mail->setTo($this->request->post['from_email']);
					$mail->setFrom($this->config->get('config_email'));
					$mail->setSender($this->language->get('text_email_copy'));
					$mail->setSubject(html_entity_decode($this->request->post['subject'], ENT_QUOTES, 'UTF-8'));
					$mail->setText($this->request->post['dev_message']);
					$mail->send();
				}
			}
			
			$json['success'] = $this->language->get('text_success_message_sent');
		}

		if (isset($this->error['from_name'])) {
			$json['error_name'] = $this->error['from_name'];
		} else {
			$json['error_name'] = '';
		}

		if (isset($this->error['from_email'])) {
			$json['error_from'] = $this->error['from_email'];
		} else {
			$json['error_from'] = '';
		}

		if (isset($this->error['to_email'])) {
			$json['error_to'] = $this->error['to_email'];
		} else {
			$json['error_to'] = '';
		}

		if (isset($this->error['subject'])) {
			$json['error_subject'] = $this->error['subject'];
		} else {
			$json['error_subject'] = '';
		}

		if (isset($this->error['message'])) {
			$json['error_message'] = $this->error['message'];
		} else {
			$json['error_message'] = '';
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	protected function validateContactForm() {		
		$language_data = $this->load->language('extension/enhancement/ea_vqmod_manager');
		foreach($language_data as $key=>$value){
			$data[$key] = $value;
		}

		if ((utf8_strlen($this->request->post['from_name']) < 3) || (utf8_strlen($this->request->post['from_name']) > 32)) {
			$this->error['from_name'] = $this->language->get('error_name');
		}

		if (!filter_var($this->request->post['from_email'], FILTER_VALIDATE_EMAIL)) {
			$this->error['from_email'] = $this->language->get('error_email');
		}

		if (!filter_var($this->request->post['to_email'], FILTER_VALIDATE_EMAIL)) {
			$this->error['to_email'] = $this->language->get('error_email');
		}
		
		if (empty($this->request->post['subject'])) {
			$this->error['subject'] = $this->language->get('error_subject');
		}

		if ((utf8_strlen($this->request->post['dev_message']) < 10) || (utf8_strlen($this->request->post['dev_message']) > 3000)) {
			$this->error['message'] = $this->language->get('error_message');
		}

		return !$this->error;
	}	
	
// ****************************************************************************************************** //	
// *********************************************  Filter Stuff  **************************************** //	
// ****************************************************************************************************** //	

	protected function getUrlParams(array $params = array()) {
		if (isset($params['filter_name'])) {
			$params['filter_name'] = urlencode(html_entity_decode($params['filter_name'], ENT_QUOTES, 'UTF-8'));
		} elseif (isset($this->request->get['filter_name'])) {
			$params['filter_name'] = urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($params['filter_author'])) {
			$params['filter_author'] = urlencode(html_entity_decode($params['filter_author'], ENT_QUOTES, 'UTF-8'));
		} elseif (isset($this->request->get['filter_author'])) {
			$params['filter_author'] = urlencode(html_entity_decode($this->request->get['filter_author'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($params['filter_status'])) {
			$params['filter_status'] = $params['filter_status'];
		} elseif (isset($this->request->get['filter_status'])) {
			$params['filter_status'] = $this->request->get['filter_status'];
		}
		
		if (isset($params['sort'])) {
			$params['sort'] = $params['sort'];
		} elseif (isset($this->request->get['sort'])) {
			$params['sort'] = $this->request->get['sort'];
		}

		if (isset($params['order'])) {
			$params['order'] = $params['order'];
		} elseif (isset($this->request->get['order'])) {
			$params['order'] = $this->request->get['order'];
		}

		if (isset($params['page'])) {
			$params['page'] = $params['page'];
		} elseif (isset($this->request->get['page'])) {
			$params['page'] = $this->request->get['page'];
		}

		$paramsJoined = array();

		foreach($params as $param => $value) {
			$paramsJoined[] = "$param=$value";
		}

		return '&' . implode('&', $paramsJoined);
	}
}
