<?php
class ControllerExtensionEnhancementEaOcmodManager extends Controller {
	private $error = array();

	public function __construct($registry) {
		parent::__construct($registry);

		$this->base_dir = substr_replace(DIR_SYSTEM, '/', -8);
	}

	public function index() {		
		$language_data = $this->load->language('extension/enhancement/ea_ocmod_manager');
		foreach($language_data as $key=>$value){
			$data[$key] = $value;
		}

		$this->document->setTitle($this->language->get('heading_title'));

  		$this->load->model('extension/enhancement/ea_ocmod_manager');		
		
		$code = 'enhanced_mod_manager';
		$link = 'https://www.opencart.com/index.php?route=marketplace/extension&filter_member=magicmike';

		$getlink = $this->model_extension_enhancement_ea_ocmod_manager->getModificationByCode($code);
		if ($getlink['link'] !== $link) {
			$this->model_extension_enhancement_ea_ocmod_manager->updateMyMod($getlink['modification_id'], $link);
		}
	
		$query = $this->db->query("SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "ea_modification_backup'");
		if (!$query->rows) {
			$this->model_extension_enhancement_ea_ocmod_manager->installModBackup();
		}
		
		$query = $this->db->query("SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "ea_modification_email'");
		if (!$query->rows) {
			$this->model_extension_enhancement_ea_ocmod_manager->installModEmail();
		}
	
		$query = $this->db->query("SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "ea_modification_meta'");
		if (!$query->rows) {
			$this->model_extension_enhancement_ea_ocmod_manager->installModMeta();
		}
	
		$query = $this->db->query("SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "ea_modification_modified'");
		if (!$query->rows) {
			$this->model_extension_enhancement_ea_ocmod_manager->installModModified();
		}
	
		$query = $this->db->query("SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "ea_modification_comment'");
		if (!$query->rows) {
			$this->model_extension_enhancement_ea_ocmod_manager->installModComment();
		}
		
		$this->document->addStyle('view/template/extension/enhancement/ocmod_manager/js/jquery/jquery-confirm.css');
		$this->document->addStyle('view/template/extension/enhancement/ocmod_manager/js/bootstrap/bootstrap-multiselect.css');	
		$this->document->addStyle('view/template/extension/enhancement/ocmod_manager/css/ea_ocmod_manager.css');
		$this->document->addStyle('view/template/extension/enhancement/ocmod_manager/js/datatables/css/dataTables.bootstrap.css');
		
		$this->document->addScript('view/template/extension/enhancement/ocmod_manager/js/jquery/jquery-confirm.min.js');		
		$this->document->addScript('view/template/extension/enhancement/ocmod_manager/js/bootstrap/bootstrap-multiselect.min.js');		
		$this->document->addScript('view/template/extension/enhancement/vqmod_manager/js/datatables/js/jquery.dataTables.min.js');
		$this->document->addScript('view/template/extension/enhancement/vqmod_manager/js/datatables/js/dataTables.bootstrap.min.js');
		$this->document->addScript('view/template/extension/enhancement/vqmod_manager/js/datatables/js/dataTables.conditionalPaging.js');
		
		$dev_files = glob(DIR_SYSTEM . "*.ocmod.xml*", GLOB_BRACE);
		
		if(!empty($dev_files)) {		
			foreach($dev_files as $dev_file) {
				$readable = $this->checkXml($dev_file);
				$filename = pathinfo($dev_file, PATHINFO_FILENAME);
				$file_ext = pathinfo($dev_file, PATHINFO_EXTENSION);
				if ($readable != true && $file_ext != 'xml_') {
					rename (DIR_SYSTEM  . $filename . ".xml" , DIR_SYSTEM . $filename. ".xml_" );
					$getmod = $this->model_extension_enhancement_ea_ocmod_manager->getModificationByFilename($filename . '.xml');
					$this->model_extension_enhancement_ea_ocmod_manager->disableModification($getmod['modification_id']);
					$this->model_extension_enhancement_ea_ocmod_manager->updateMetaByFilename($filename . '.xml', $filename.'.xml_');
					$this->model_extension_enhancement_ea_ocmod_manager->updateExtensionInstallByFilename($filename . '.xml', $filename.'.xml_');	
				}
			}
		}

		$this->getList();
	}	
	
	public function updateConfig() {
		$modification_id = $this->request->get['modification_id'];
		$screen_mode = $this->request->get['screen_mode'];

		$this->load->model('extension/enhancement/ea_ocmod_manager');
		$this->model_extension_enhancement_ea_ocmod_manager->editSettingValue('oc_editor', 'oc_editor_screen', $screen_mode);		
		
		$url = $this->getListUrlParams(array('modification_id' => $modification_id));
		$this->response->redirect($this->url->link('extension/enhancement/ea_ocmod_manager/edit', 'user_token=' . $this->session->data['user_token'] . $url));
	}
	
	public function genBackup() {
		$this->load->model('extension/enhancement/ea_ocmod_manager');
		
		$modification_id = $this->request->get['modification_id'];

		$getmod = $this->model_extension_enhancement_ea_ocmod_manager->getModification($modification_id);
			
		if (file_exists(DIR_SYSTEM.$getmod['file'])) {
			$xml = html_entity_decode(file_get_contents(DIR_SYSTEM .  $getmod['file']), ENT_QUOTES, 'UTF-8');
		} else {
			$xml = $getmod['xml'];
		}

		$backup = $this->model_extension_enhancement_ea_ocmod_manager->getBackup($modification_id);

		if (!$backup) {
			$this->model_extension_enhancement_ea_ocmod_manager->createBackup($modification_id, $xml);
		} else {
			$this->model_extension_enhancement_ea_ocmod_manager->deleteBackup($modification_id);
			$this->model_extension_enhancement_ea_ocmod_manager->createBackup($modification_id, $xml);
		}
		
		$url = $this->getListUrlParams(array('modification_id' => $modification_id));
		$this->response->redirect($this->url->link('extension/enhancement/ea_ocmod_manager/edit', 'user_token=' . $this->session->data['user_token'] . $url));
	}	
	
	public function installPage() {
		$language_data = $this->load->language('extension/enhancement/ea_ocmod_manager');
		foreach($language_data as $key=>$value) {
			$data[$key] = $value;
		}

		$data['user_token'] = $this->session->data['user_token'];
		
		$this->response->setOutput($this->load->view('extension/enhancement/ocmod_manager/ea_ocmod_manager_installer', $data));
	}
	
	public function add() {
		$language_data = $this->load->language('extension/enhancement/ea_ocmod_manager');
		foreach($language_data as $key=>$value){
			$data[$key] = $value;
		}

		$this->load->model('extension/enhancement/ea_ocmod_manager');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateXmlForm()) {
			if (isset($this->request->get['savedev'])) {
				$savedev = true;
			} else {
				$savedev = false;
			}
			
			$xml = html_entity_decode(rawurldecode($this->request->post['xml']), ENT_QUOTES, 'UTF-8');		
			
			$dom = new DOMDocument('1.0', 'UTF-8');
			$dom->preserveWhiteSpace = false;
			$dom->loadXml($xml);

			$data = array();

			$data['name'] = $dom->getElementsByTagName('name')->item(0)->textContent;

			$code = $dom->getElementsByTagName('code')->item(0)->textContent;
			
			$data['code'] = $code;

			if ($dom->getElementsByTagName('version')->length) {
				$data['version'] = $dom->getElementsByTagName('version')->item(0)->textContent;
			} else {
				$data['version'] = '';
			}

			if ($dom->getElementsByTagName('author')->length) {
				$data['author'] = $dom->getElementsByTagName('author')->item(0)->textContent;
			} else {
				$data['author'] = '';
			}

			if ($dom->getElementsByTagName('link')->length) {
				$link = $dom->getElementsByTagName('link')->item(0)->textContent;
				$reg = '/(http|https):\/\/[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&amp;:\/~\+#]*[\w\-\@?^=%&amp;\/~\+#])/';
				preg_match_all($reg, $link, $matches, PREG_SET_ORDER, 0);				
				$data['link'] = $matches[0][0];	
			} else {
				$data['link'] = '';
			}
			
			$data['status'] = 1;
			
			$data['extension_install_id'] = $this->model_extension_enhancement_ea_ocmod_manager->addExtensionInstall($code.'.ocmod.xml');
			
			if ($savedev == true) {
				$data['xml'] = '';
				$dev_file = DIR_SYSTEM . $code.'.ocmod.xml';
				file_put_contents($dev_file, html_entity_decode($xml, ENT_QUOTES, 'UTF-8'));
				$modification_id = $this->model_extension_enhancement_ea_ocmod_manager->addModificationDev($data);
				$this->model_extension_enhancement_ea_ocmod_manager->addMeta($modification_id, $code.'.ocmod.xml');	
				$this->session->data['success'] = $this->language->get('text_success_added_dev');
			} else {			
				$data['xml'] = $xml;
				$this->model_extension_enhancement_ea_ocmod_manager->addModification($data);
				$modification_id = $this->db->getLastId();
				$this->session->data['success'] = $this->language->get('text_success_added');
			}

			$this->response->redirect($this->url->link('extension/enhancement/ea_ocmod_manager/genBackup', 'user_token=' . $this->session->data['user_token'] . $this->getListUrlParams(array('modification_id' => $modification_id))));
		}

		$this->getXmlForm();
	}

	public function edit() {
		$language_data = $this->load->language('extension/enhancement/ea_ocmod_manager');
		foreach($language_data as $key=>$value){
			$data[$key] = $value;
		}

		$this->load->model('extension/enhancement/ea_ocmod_manager');
			
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && !empty($this->request->get['modification_id']) && $this->validateXmlForm()) {
			$modification_id = $this->request->get['modification_id'];

			$mod_info = $this->model_extension_enhancement_ea_ocmod_manager->getModification($modification_id);
			
			$extension_install_id = $mod_info['extension_install_id'];
			
			$mod_file = $mod_info['file'];
				
			$filename = pathinfo($mod_info['file'], PATHINFO_FILENAME);

			if ($mod_info['file'] == $filename.'.xml_' && $this->request->post['xml_status'] == 1) {
				rename (DIR_SYSTEM  . $filename . ".xml_" , DIR_SYSTEM . $filename. ".xml" );
				$this->model_extension_enhancement_ea_ocmod_manager->updateMeta($modification_id, $filename.'.xml');
				$this->model_extension_enhancement_ea_ocmod_manager->updateExtensionInstall($extension_install_id, $filename.'.xml');
				$mod_file = $filename.'.xml';
			}

			if ($mod_info['file'] == $filename.'.xml' && $this->request->post['xml_status'] == 0) {
				rename (DIR_SYSTEM  . $filename . ".xml" , DIR_SYSTEM . $filename. ".xml_" );
				$this->model_extension_enhancement_ea_ocmod_manager->updateMeta($modification_id, $filename.'.xml_');
				$this->model_extension_enhancement_ea_ocmod_manager->updateExtensionInstall($extension_install_id, $filename.'.xml_');				
				$mod_file = $filename.'.xml_';
			}
			
			$xml = html_entity_decode(rawurldecode($this->request->post['xml']), ENT_QUOTES, 'UTF-8');
			
			$backup = $this->model_extension_enhancement_ea_ocmod_manager->getBackup($modification_id);

			if (!$backup) {
				$this->model_extension_enhancement_ea_ocmod_manager->createBackup($modification_id, $xml);
			} else {
				$this->model_extension_enhancement_ea_ocmod_manager->deleteBackup($modification_id);
				$this->model_extension_enhancement_ea_ocmod_manager->createBackup($modification_id, $xml);
			}
			
			$dom = new DOMDocument('1.0', 'UTF-8');
			$dom->preserveWhiteSpace = false;
			$dom->loadXml($xml);

			$data = array();

			$data['xml'] = $xml;

			$data['name'] = $dom->getElementsByTagName('name')->item(0)->textContent;

			$data['code'] = $dom->getElementsByTagName('code')->item(0)->textContent;

			if ($dom->getElementsByTagName('version')->length) {
				$data['version'] = $dom->getElementsByTagName('version')->item(0)->textContent;
			} else {
				$data['version'] = '';
			}

			if ($dom->getElementsByTagName('author')->length) {
				$data['author'] = $dom->getElementsByTagName('author')->item(0)->textContent;
			} else {
				$data['author'] = '';
			}

			if ($dom->getElementsByTagName('link')->length) {
				$link = $dom->getElementsByTagName('link')->item(0)->textContent;
				$reg = '/(http|https):\/\/[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&amp;:\/~\+#]*[\w\-\@?^=%&amp;\/~\+#])/';
				preg_match_all($reg, $link, $matches, PREG_SET_ORDER, 0);				
				$data['link'] = $matches[0][0];				
				//$this->log->debug($data['link']);
			} else {
				$data['link'] = '';
			}
			
			if ($data['code'] == 'enhanced_mod_manager') {
				$data['status'] = 1;
			} else {
				$data['status'] = (int)$this->request->post['xml_status'];
			}
			
			if ($mod_info['type'] == "Installed") {
				$this->model_extension_enhancement_ea_ocmod_manager->editModification($modification_id, $data);
			} else {
				file_put_contents(DIR_SYSTEM . $mod_file, html_entity_decode($xml, ENT_QUOTES, 'UTF-8'));				
				$data['xml'] = '';
				$this->model_extension_enhancement_ea_ocmod_manager->editModification($modification_id, $data);
			}	

			$url = $this->getListUrlParams(array('modification_id' => $modification_id));

			if (isset($this->request->get['refresh'])) {
				$this->response->redirect($this->url->link('extension/enhancement/ea_ocmod_manager/refresh', 'redirect=1&user_token=' . $this->session->data['user_token'] . $url));
			}

			$this->session->data['success'] = $this->language->get('text_success_saved');
			$this->response->redirect($this->url->link('extension/enhancement/ea_ocmod_manager/edit', 'user_token=' . $this->session->data['user_token'] . $url));
		}

		$this->getXmlForm();
	}

	public function delete() {		
		$language_data = $this->load->language('extension/enhancement/ea_ocmod_manager');
		foreach($language_data as $key=>$value){
			$data[$key] = $value;
		}

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/enhancement/ea_ocmod_manager');

		if (isset($this->request->post['selected']) && $this->validate()) {			
			$mod_code = $this->model_extension_enhancement_ea_ocmod_manager->getModificationByCode('enhanced_mod_manager');
			foreach ($this->request->post['selected'] as $modification_id) {
				$mod_info = $this->model_extension_enhancement_ea_ocmod_manager->getModification($modification_id);
				if (file_exists(DIR_SYSTEM.$mod_info['file']) && $mod_info['type'] == "Development") {
					unlink(DIR_SYSTEM.$mod_info['file']);
				}
				if ($mod_code['modification_id'] == $modification_id) {
					$this->model_extension_enhancement_ea_ocmod_manager->deleteModification($modification_id);
					$this->model_extension_enhancement_ea_ocmod_manager->deleteModificationEmail($modification_id);
					$this->model_extension_enhancement_ea_ocmod_manager->deleteBackup($modification_id);
					$this->model_extension_enhancement_ea_ocmod_manager->deleteModComments($modification_id);
					$this->model_extension_enhancement_ea_ocmod_manager->deleteMeta($modification_id);
					$this->model_extension_enhancement_ea_ocmod_manager->deleteExtensionInstall($mod_info['extension_install_id']);
					$this->response->redirect($this->url->link('marketplace/modification/refresh', '&user_token=' . $this->session->data['user_token'] . $url));
				} else {
					$this->model_extension_enhancement_ea_ocmod_manager->deleteModification($modification_id);
					$this->model_extension_enhancement_ea_ocmod_manager->deleteModificationEmail($modification_id);
					$this->model_extension_enhancement_ea_ocmod_manager->deleteBackup($modification_id);
					$this->model_extension_enhancement_ea_ocmod_manager->deleteModComments($modification_id);
					$this->model_extension_enhancement_ea_ocmod_manager->deleteMeta($modification_id);
					$this->model_extension_enhancement_ea_ocmod_manager->deleteExtensionInstall($mod_info['extension_install_id']);
				}				
			}

			$url = $this->getListUrlParams(array('modification_id' => $this->request->get['modification_id']));

			$this->response->redirect($this->url->link('extension/enhancement/ea_ocmod_manager/refresh', '&user_token=' . $this->session->data['user_token'] . $url));
		}

		$this->getList();
	}

	public function deleteFile() {
		$language_data = $this->load->language('extension/enhancement/ea_ocmod_manager');
		foreach($language_data as $key=>$value){
			$data[$key] = $value;
		}

		$this->load->model('extension/enhancement/ea_ocmod_manager');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && !empty($this->request->post['extension_path_id']) && $this->validate()) {
			$extension_path_id = $this->request->post['extension_path_id'];
			$file_info = $this->model_extension_enhancement_ea_ocmod_manager->getModificationFile($extension_path_id);
			$this->model_extension_enhancement_ea_ocmod_manager->deleteModificationFile($extension_path_id);			
						
			if (file_exists($this->base_dir . $file_info['path'])) {
				@unlink($this->base_dir . $file_info['path']);
			} else {
				$data['error_warning'] = $this->language->get('error_file_deleted');
			}

			$url = $this->getListUrlParams(array('modification_id' => $this->request->get['modification_id']));

			$this->session->data['success'] = $this->language->get('text_success_file_deleted');
			
			$this->response->redirect($this->url->link('extension/enhancement/ea_ocmod_manager/edit', 'user_token=' . $this->session->data['user_token'] . $url));
		}

		$this->getXmlForm();
	}

	public function enable() {		
		$language_data = $this->load->language('extension/enhancement/ea_ocmod_manager');
		foreach($language_data as $key=>$value){
			$data[$key] = $value;
		}

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/enhancement/ea_ocmod_manager');

		if (isset($this->request->get['modification_id']) && $this->validate()) {
			$modification_id = $this->request->get['modification_id'];
			$mod_info = $this->model_extension_enhancement_ea_ocmod_manager->getModification($modification_id);
			$extension_install_id = $mod_info['extension_install_id'];
			
			if ($mod_info['file']) {
				$filename = pathinfo($mod_info['file'], PATHINFO_FILENAME);
				rename (DIR_SYSTEM  . $filename . ".xml_" , DIR_SYSTEM . $filename. ".xml" );
				$this->model_extension_enhancement_ea_ocmod_manager->updateMeta($modification_id, $filename.'.xml');
				$this->model_extension_enhancement_ea_ocmod_manager->updateExtensionInstall($extension_install_id, $filename.'.xml');
			}
			
			$this->model_extension_enhancement_ea_ocmod_manager->enableModification($modification_id);

			$url = $this->getListUrlParams(array('modification_id' => $modification_id));

			$this->response->redirect($this->url->link('extension/enhancement/ea_ocmod_manager/refresh', 'modify=1&user_token=' . $this->session->data['user_token'] . $url));
		}

		$this->getList();
	}

	public function disable() {		
		$language_data = $this->load->language('extension/enhancement/ea_ocmod_manager');
		foreach($language_data as $key=>$value){
			$data[$key] = $value;
		}

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/enhancement/ea_ocmod_manager');

		if (isset($this->request->get['modification_id']) && $this->validate()) {
			$modification_id = $this->request->get['modification_id'];
			$mod_info = $this->model_extension_enhancement_ea_ocmod_manager->getModification($modification_id);
			$extension_install_id = $mod_info['extension_install_id'];
			
			if ($mod_info['file']) {
				$filename = pathinfo($mod_info['file'], PATHINFO_FILENAME);
				rename (DIR_SYSTEM  . $filename . ".xml" , DIR_SYSTEM . $filename. ".xml_" );				
				$this->model_extension_enhancement_ea_ocmod_manager->updateMeta($modification_id, $filename.'.xml_');
				$this->model_extension_enhancement_ea_ocmod_manager->updateExtensionInstall($extension_install_id, $filename.'.xml_');
			}
			
			$this->model_extension_enhancement_ea_ocmod_manager->disableModification($modification_id);

			$url = $this->getListUrlParams(array('modification_id' => $modification_id));

			if ($mod_info['code'] == 'enhanced_mod_manager') {
				$this->response->redirect($this->url->link('extension/enhancement/ea_ocmod_manager/refresh', '&remove=1&user_token=' . $this->session->data['user_token'] . $url));
			}
			
			$this->response->redirect($this->url->link('extension/enhancement/ea_ocmod_manager/refresh', 'modify=0&user_token=' . $this->session->data['user_token'] . $url));
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
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/enhancement/ea_ocmod_manager', 'user_token=' . $this->session->data['user_token'] . $url)
		);

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} elseif (!empty($this->error['system_xml'])) {
			$data['error_warning'] = $this->error['system_xml'];
		} elseif (!empty($this->error)) {
			$data['error_warning'] = $this->language->get('error_warning');
		} else {
			$data['error_warning'] = '';
		}	
		
		$errors = $this->parseErrorLogs();
		
		$data['add_xml'] = $this->url->link('extension/enhancement/ea_ocmod_manager/add', 'user_token=' . $this->session->data['user_token'] . $url);
		$data['filter_action'] = $this->url->link('extension/enhancement/ea_ocmod_manager', 'user_token=' . $this->session->data['user_token']);
		$data['reset_url'] = $this->url->link('extension/enhancement/ea_ocmod_manager', 'user_token=' . $this->session->data['user_token']);	

		$data['clear_log'] = $this->url->link('extension/enhancement/ea_ocmod_manager/clearlog', 'user_token=' . $this->session->data['user_token'] . $url);
		$data['refresh'] = $this->url->link('extension/enhancement/ea_ocmod_manager/refresh', 'user_token=' . $this->session->data['user_token'] . $url);
		$data['clear'] = $this->url->link('extension/enhancement/ea_ocmod_manager/clear', 'user_token=' . $this->session->data['user_token'] . $url);
		$data['delete'] = $this->url->link('extension/enhancement/ea_ocmod_manager/delete', 'user_token=' . $this->session->data['user_token'] . $url);
		
		$data['settings'] = $this->url->link('extension/enhancement/ea_ocmod_manager_settings', 'user_token=' . $this->session->data['user_token']);
		$data['filelist'] = $this->url->link('extension/enhancement/ea_ocmod_manager_files', 'user_token=' . $this->session->data['user_token']);
		$data['reload'] = $this->url->link('extension/enhancement/ea_ocmod_manager/reloadDev', 'user_token=' . $this->session->data['user_token'] . $url);
		$data['upload_dev'] = $this->url->link('extension/enhancement/ea_ocmod_manager/uploadDev', 'user_token=' . $this->session->data['user_token'] . $url);

		$data['modifications'] = array();

		$filter_data = array(
			'filter_name'	=> $filter_name,
			'filter_author'	=> $filter_author,
			'filter_status'	=> $filter_status,
			'sort'  		=> $sort,
			'order' 		=> $order,
			'start' 		=> ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' 		=> $this->config->get('config_limit_admin')
		);

		$modification_total = $this->model_extension_enhancement_ea_ocmod_manager->getTotalModifications($filter_data);

		$results = $this->model_extension_enhancement_ea_ocmod_manager->getModifications($filter_data);
		
		foreach ($results as $result) {
			if (!empty($result['date_modified']) && $result['date_modified'] != '0000-00-00 00:00:00') {
				$date_modified = date($this->language->get('datetime_format'), strtotime($result['date_modified']));
			} else {
				$date_modified = 'N/A';
			}
			
			if (!empty($result['username'])) {
				$username = $result['username'];
			} else {
				$username = '';
			}

			if (isset($errors[$result['name']])) {
				$result['error'] = true;
			} else {
				$result['error'] = false;			
			}

			if ($result['type'] === 'Installed') {
				$readable = true;
			} else {
				if (is_file(DIR_SYSTEM .  $result['file'])) {
					$readable = $this->checkXml(DIR_SYSTEM . $result['file']);
				}		
			}
			
			$data['modifications'][] = array(
				'modification_id' 	=> $result['modification_id'],
				'install_id' 	  	=> $result['extension_install_id'],
				'xml_name'        	=> $result['filename'],
				'name'            	=> $result['name'],
				'author'          	=> $result['author'],
				'code'            	=> $result['code'],
				'version'         	=> $result['version'],
				'status'          	=> $result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
				'type'         	  	=> $result['type'],
				'readable'         	=> $readable,
				'date_added'      	=> date($this->language->get('datetime_format'), strtotime($result['date_added'])),
				'date_modified'   	=> $date_modified,
				'username'   		=> $username,
				'link'            	=> $result['link'],
				'email'           	=> $this->model_extension_enhancement_ea_ocmod_manager->getModificationEmail($result['modification_id']),
				'edit'            	=> $this->url->link('extension/enhancement/ea_ocmod_manager/genBackup', 'user_token=' . $this->session->data['user_token'] . '&modification_id=' . $result['modification_id']),
				'download' 		  	=> $this->url->link('extension/enhancement/ea_ocmod_manager/downloadXml', 'user_token=' . $this->session->data['user_token'] . '&modification_id=' . $result['modification_id']),
				'upload' 		  	=> $this->url->link('extension/enhancement/ea_ocmod_manager/uploadXml', 'user_token=' . $this->session->data['user_token'] . '&modification_id=' . $result['modification_id']),
				'enable'          	=> $this->url->link('extension/enhancement/ea_ocmod_manager/enable', 'user_token=' . $this->session->data['user_token'] . '&modification_id=' . $result['modification_id']),
				'disable'         	=> $this->url->link('extension/enhancement/ea_ocmod_manager/disable', 'user_token=' . $this->session->data['user_token'] . '&modification_id=' . $result['modification_id']),
				'enabled'         	=> $result['status'],
				'error'         	=> $result['error'],
				'error_link'        => $this->url->link('extension/enhancement/ea_ocmod_manager/viewerror', 'user_token=' . $this->session->data['user_token'] . '&modification_id=' . $result['modification_id'])
			);
		}

		$data['user_token'] = $this->session->data['user_token'];

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
		
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_author'])) {
			$url .= '&filter_author=' . urlencode(html_entity_decode($this->request->get['filter_author'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . urlencode(html_entity_decode($this->request->get['filter_status'], ENT_QUOTES, 'UTF-8'));
		}

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_name'] = $this->url->link('extension/enhancement/ea_ocmod_manager', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url);
		$data['sort_author'] = $this->url->link('extension/enhancement/ea_ocmod_manager', 'user_token=' . $this->session->data['user_token'] . '&sort=author' . $url);
		$data['sort_code'] = $this->url->link('extension/enhancement/ea_ocmod_manager', 'user_token=' . $this->session->data['user_token'] . '&sort=code' . $url);
		$data['sort_version'] = $this->url->link('extension/enhancement/ea_ocmod_manager', 'user_token=' . $this->session->data['user_token'] . '&sort=version' . $url);
		$data['sort_status'] = $this->url->link('extension/enhancement/ea_ocmod_manager', 'user_token=' . $this->session->data['user_token'] . '&sort=status' . $url);
		$data['sort_type'] = $this->url->link('extension/enhancement/ea_ocmod_manager', 'user_token=' . $this->session->data['user_token'] . '&sort=type' . $url);
		$data['sort_date_added'] = $this->url->link('extension/enhancement/ea_ocmod_manager', 'user_token=' . $this->session->data['user_token'] . '&sort=date_added' . $url);
		$data['sort_date_modified'] = $this->url->link('extension/enhancement/ea_ocmod_manager', 'user_token=' . $this->session->data['user_token'] . '&sort=date_modified' . $url);

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
			$url .= '&filter_status=' . urlencode(html_entity_decode($this->request->get['filter_status'], ENT_QUOTES, 'UTF-8'));
		}

		$pagination = new Pagination();
		$pagination->total = $modification_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('extension/enhancement/ea_ocmod_manager', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}');

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($modification_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($modification_total - $this->config->get('config_limit_admin'))) ? $modification_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $modification_total, ceil($modification_total / $this->config->get('config_limit_admin')));

		$data['filter_name'] = $filter_name;
		$data['filter_author'] = $filter_author;
		$data['filter_status'] = $filter_status;
		$data['sort'] = $sort;
		$data['order'] = $order;
		
		$data['reload_url'] = $url;

		if ($this->config->get('oc_editor_edit') == 1) {
			$data['ocmod_edit'] = false;			
		} else {
			$data['ocmod_edit'] = true;
		}

		if ($this->config->get('oc_editor_upload') == 1) {
			$data['ocmod_upload'] = false;			
		} else {
			$data['ocmod_upload'] = true;
		}

		if ($this->config->get('oc_editor_download') == 1) {
			$data['ocmod_download'] = false;			
		} else {
			$data['ocmod_download'] = true;
		}

		if ($this->config->get('oc_editor_contact') == 1) {
			$data['ocmod_contact'] = false;			
		} else {
			$data['ocmod_contact'] = true;
		}

		if ($this->config->get('oc_editor_uninstall') == 1) {
			$data['ocmod_uninstall'] = false;			
		} else {
			$data['ocmod_uninstall'] = true;
		}
		
		if ($this->user->hasPermission('access', 'extension/enhancement/ea_ocmod_manager_settings')) {
			$data['has_perm_access'] = true;
		} else {
			$data['has_perm_access'] = false;
		}
		
		if ($this->validate()) {
			$data['has_perm_modify'] = true;
		} else {
			$data['has_perm_modify'] = false;
		}

		$file = DIR_LOGS . 'ocmod.log';

		if (file_exists($file)) {
			$data['log'] = htmlentities(file_get_contents($file, FILE_USE_INCLUDE_PATH, null));
		} else {
			$data['log'] = '';
		}

		$data['modified_files'] = array();

		$modified_files = self::modifiedFiles(DIR_MODIFICATION);

		$filter = array();
		$filter['sort'] = 'name';
		$filter['order'] = 'ASC';
		
		$modification_files = $this->getMainXmlFiles($filter);
		
		$ocfiles_count = 0;

		foreach($modified_files as $modified_file) {
			if(isset($modification_files[$modified_file])){
				$modifications = $modification_files[$modified_file];
			} else {
				$modifications = array();
			}

			$data['modified_files'][] = array(
				'file' => $modified_file,
				'modifications' => $modifications
			);
			$ocfiles_count++;
		}
		
		$data['ocfiles_count'] = (int)$ocfiles_count;

		$error_file = DIR_LOGS . 'ocmod_error.log';

		if (file_exists($error_file)) {
			$data['error_log'] = htmlentities(file_get_contents($error_file, FILE_USE_INCLUDE_PATH, null));
		} else {
			$data['error_log'] = '';
		}
		
		if ($this->config->get('oc_editor_perm_edit')  && in_array($this->user->getId(), $this->config->get('oc_editor_perm_edit'))) {
			$data['has_perm_edit'] = true;
		} else {
			$data['has_perm_edit'] = false;
		}
		
		if ($this->config->get('oc_editor_perm_upload') && in_array($this->user->getId(), $this->config->get('oc_editor_perm_upload'))) {
			$data['has_perm_upload'] = true;
		} else {
			$data['has_perm_upload'] = false;
		}
		
		if ($this->config->get('oc_editor_perm_download') && in_array($this->user->getId(), $this->config->get('oc_editor_perm_download'))) {
			$data['has_perm_download'] = true;
		} else {
			$data['has_perm_download'] = false;
		}
		
		if ($this->user->hasPermission('modify', 'marketplace/installer')) {
			$data['has_perm_install'] = true;
		} else {
			$data['has_perm_install'] = false;
		}
		
		if ($this->validate()) {
			$data['has_perm_delete'] = true;
		} else {
			$data['has_perm_delete'] = false;
		}
		
		if ($this->user->hasPermission('modify', 'common/developer')) {
			$data['has_perm_cache'] = true;
		} else {
			$data['has_perm_cache'] = false;
		}
		
		$mod_comments = $this->model_extension_enhancement_ea_ocmod_manager->getAllComments();
		
		$data['comments_count'] = count($mod_comments);
		
		$data['src_url'] = HTTP_SERVER;
		
		$data['storage'] = strstr(DIR_STORAGE, 'storage/');

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/enhancement/ocmod_manager/ea_ocmod_manager', $data));
	}	
	
	public function getXmlForm() {					
		$this->document->addStyle('view/template/extension/enhancement/ocmod_manager/js/jquery/jquery-confirm.css');
		$this->document->addStyle('view/template/extension/enhancement/ocmod_manager/css/ea_ocmod_manager.css');			
		$this->document->addStyle('view/template/extension/enhancement/ocmod_manager/js/ace/ace-diff.css');
		$this->document->addStyle('view/template/extension/enhancement/ocmod_manager/js/datatables/css/dataTables.bootstrap.css');
		
		$this->document->addScript('view/template/extension/enhancement/ocmod_manager/js/ace/diff_match_patch.js');		
		$this->document->addScript('view/template/extension/enhancement/ocmod_manager/js/jquery/jquery-confirm.min.js');		
		$this->document->addScript('view/template/extension/enhancement/ocmod_manager/js/slidereveal.js');
		
		$this->document->addScript('view/template/extension/enhancement/ocmod_manager/js/datatables/js/jquery.dataTables.min.js');
		$this->document->addScript('view/template/extension/enhancement/ocmod_manager/js/datatables/js/dataTables.bootstrap.min.js');
		$this->document->addScript('view/template/extension/enhancement/vqmod_manager/js/datatables/js/dataTables.conditionalPaging.js');
		
		$data['heading_title'] = $this->language->get('heading_title');

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
		
		if (isset($this->request->get['modification_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/enhancement/ea_ocmod_manager/edit', 'user_token=' . $this->session->data['user_token'] . '&modification_id=' . $this->request->get['modification_id'])
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => ''
			);
		}
		
		$urlParams = array();

		if (isset($this->request->get['modification_id'])) {
			$this->load->model('extension/enhancement/ea_ocmod_manager');

			$modification_info = $this->model_extension_enhancement_ea_ocmod_manager->getModification($this->request->get['modification_id']);
			if (!$modification_info) exit;
			
			$backup_info = $this->model_extension_enhancement_ea_ocmod_manager->getBackup($this->request->get['modification_id']);
			
			if ($backup_info) {
				$data['backup']['xml'] = $backup_info['mod_xml'];
			} else {
				$data['backup']['xml'] = '';
			}
			
			$data['additional_files'] = $this->model_extension_enhancement_ea_ocmod_manager->getAllModFiles($modification_info['extension_install_id']);
			
			$data['additional_count'] = $this->model_extension_enhancement_ea_ocmod_manager->getTotalModFiles($modification_info['extension_install_id']);
			
			$data['additional_file_name'] = sprintf($this->language->get('text_additional_files'), $modification_info['filename']);
			
			$data['text_form'] = sprintf($this->language->get('text_edit'), $modification_info['name']);
			
			$urlParams['modification_id'] = $this->request->get['modification_id'];
			$url = $this->getListUrlParams($urlParams);

			$data['action'] = $this->url->link('extension/enhancement/ea_ocmod_manager/edit', 'user_token=' . $this->session->data['user_token'] . $url);
			
			$data['adddevelop'] = $this->url->link('extension/enhancement/ea_ocmod_manager/saveDev', 'user_token=' . $this->session->data['user_token']);

			$data['refresh'] = $this->url->link('extension/enhancement/ea_ocmod_manager/edit', 'refresh=1&user_token=' . $this->session->data['user_token'] . $url);
			
			$urlParams['extension_install_id'] = $modification_info['extension_install_id'];
			$url = $this->getListUrlParams($urlParams);
			
			$data['del_file'] = $this->url->link('extension/enhancement/ea_ocmod_manager/deleteFile', 'user_token=' . $this->session->data['user_token'] . $url);
			
			$error_file = DIR_LOGS . 'ocmod_error.log';

			if (file_exists($error_file)) {
				$data['error_log'] = '';
				
				$logs = htmlentities(file_get_contents($error_file, FILE_USE_INCLUDE_PATH, null));

				$logs = explode('----------------------', $logs);

				foreach ($logs as $log) {
					$log = trim(trim(trim($log), '- '));

					if (!empty($modification_info['name']) && strpos(utf8_strtolower($log), utf8_strtolower($modification_info['name'])) !== false) {
						$data['error_log'] .= $log . "\n\n----------------------------------------------------------------\n\n";
					}
				}			
			} else {
				$data['error_log'] = '';
			}

			$this->document->setTitle($modification_info['name'] . ' » ' . $data['heading_title']);
		} else {
			$data['text_form'] = $this->language->get('text_add');

			$data['refresh'] = false;

			$data['action'] = $this->url->link('extension/enhancement/ea_ocmod_manager/add', 'user_token=' . $this->session->data['user_token']);

			$this->document->setTitle($data['heading_title']);
		}

		$data['cancel'] = $this->url->link('extension/enhancement/ea_ocmod_manager', 'user_token=' . $this->session->data['user_token'] . $this->getListUrlParams());

		$data['modification'] = array();

		if (!empty($modification_info)) {			
			$data['install_id'] = $modification_info['extension_install_id'];
			$data['modification']['status'] = $modification_info['status'];
			$data['modification']['code'] = $modification_info['code'];
		} else {
			$data['install_id'] = '';
			$data['modification']['status'] = 0;
			$data['modification']['code'] = '';
		}

		if (isset($this->request->post['xml'])) {
			$data['modification']['xml'] = html_entity_decode($this->request->post['xml'], ENT_QUOTES, 'UTF-8');
		} elseif (!empty($modification_info)) {
			if ($modification_info['type'] == "Installed") {
				$data['modification']['xml'] = html_entity_decode($modification_info['xml'], ENT_QUOTES, 'UTF-8');				
				$data['modification']['filename'] = $modification_info['name'];
			} else {
				$data['modification']['xml'] = html_entity_decode(file_get_contents(DIR_SYSTEM .  $modification_info['file']), ENT_QUOTES, 'UTF-8');
				$data['modification']['filename'] = $modification_info['file'];
			}
		} else {
			$data['modification']['xml'] = '';
			$xml = '';
		}
		
		// Get XML File Paths
		$data['ocfiles'] = array();
		$data['storage'] = DIR_STORAGE;
		$ocfiles_count = 0;
		
		if ($data['modification']['xml'] != '' && $this->validateXml($data['modification']['xml'])) {
			$xml = $data['modification']['xml'];

			$dom = new DOMDocument('1.0', 'UTF-8');
			$dom->preserveWhiteSpace = false;
			$dom->loadXml($xml);

			foreach ($dom->getElementsByTagName('file') as $node) {
				$data['ocfiles'][] = array(
					'path' => $node->getAttribute('path')
				);
				$ocfiles_count++;
			}
		}
		
		$data['ocfiles_count'] = (int)$ocfiles_count;
		
		if (isset($this->request->get['modification_id'])) {
			$data['mod_id'] = $this->request->get['modification_id'];
		} else {
			$data['mod_id'] = '';
		}
		
		if ($modification_info['type'] == "Installed") {
			$data['installed'] = true;
		} else {
			$data['installed'] = false;
		}
		
		if (isset($this->request->get['modification_id']) && !$this->validateXml($data['modification']['xml'])) {
			$data['mod_xml_error'] = $this->error['xml'];
			if (($pos = strpos($this->error['xml'], "Line:")) !== FALSE) { 
    			$data['error_line'] = substr($this->error['xml'], $pos+5); 
			}
		} else {
			$data['mod_xml_error'] = '';
			$data['error_line'] = '';
		}

		if ($this->config->get('oc_editor_theme')) {
			$data['themejs'] = $this->config->get('oc_editor_theme');
			$data['themename'] = str_replace("theme-","",$data['themejs']);
		} else {
			$data['themejs'] = 'theme-cobalt';
			$data['themename'] = 'cobalt';
		}
		
		if ($this->config->get('oc_editor_screen')) {
			$screen_mode = (int)$this->config->get('oc_editor_screen');
		} else if ($this->config->get('oc_editor_screen_mode')) {
			$screen_mode = (int)$this->config->get('oc_editor_screen_mode');
		} else {
			$screen_mode = 1;
		}

		if ($this->config->get('oc_editor_perm_delete') == 1) {
			$data['ocmod_delete'] = false;			
		} else {
			$data['ocmod_delete'] = true;
		}
		
		if ($this->config->get('oc_editor_perm_delete') && in_array($this->user->getId(), $this->config->get('oc_editor_perm_delete'))) {
			$data['has_perm_delete'] = true;
		} else {
			$data['has_perm_delete'] = false;
		}	
		
		if (isset($this->request->get['modification_id'])) {
			$mod_comments = $this->model_extension_enhancement_ea_ocmod_manager->getModComments($this->request->get['modification_id']);
			$data['comments_count'] = count($mod_comments);
		} else {
			$mod_comments = '';
			$data['comments_count'] = 0;
		}
		
		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		if ($screen_mode == 2) {		
			$this->response->setOutput($this->load->view('extension/enhancement/ocmod_manager/ea_ocmod_manager_diff_form', $data));
		} else {
			$this->response->setOutput($this->load->view('extension/enhancement/ocmod_manager/ea_ocmod_manager_form', $data));
		}
	}
		
	public function checkXml($xml) {
		libxml_use_internal_errors(true);
		$ocxml = simplexml_load_file($xml);

		if (libxml_get_errors()) {
			$readable = false;
			libxml_clear_errors();
		} else {
			$readable = true;
		}
		
		return $readable;
	}
	
	public function parseErrorLogs() {
		$logLines = file(DIR_LOGS . 'ocmod.log',FILE_IGNORE_NEW_LINES);
		
		$errors = array();
		
		foreach($logLines as $logLine) {				
			if (strpos($logLine, 'MOD: ') !== false) {						
				$modName = explode("MOD: ",$logLine);
				$modName = $modName[1];
				if (isset($errors[$modName]))
					unset($errors[$modName]); 
				if ($modName == "Modification Default")
					continue;
			}
			
			if ($this->beginsWith($logLine,"FILE: ")) {
				$errorFile = explode("FILE: ",$logLine);
				$errorFile = $errorFile[1];
			}
			
			if ($this->beginsWith($logLine,"CODE: ")) {
				$errorCode = explode("CODE: ",$logLine);
				$errorCode = $errorCode[1];
			}
			
			if ($this->beginsWith($logLine,"NOT FOUND - ")) {
				$errors[$modName][] = array("file" => $errorFile, "code"=>$errorCode, "error"=>$logLine);
			}

		}
		return $errors;
	}

	public function viewerror() {	
		$this->load->model('extension/enhancement/ea_ocmod_manager');
		
		$json = array();

		$errors = $this->parseErrorLogs();

		$result = $this->model_extension_enhancement_ea_ocmod_manager->getModification($this->request->get['modification_id']);

		$json['name'] = $result['name'];
		$json['filename'] = $result['filename'];

		$errSummary = '<p style="font-family:Courier New;"><font size="4">';
		
		foreach ($errors[$result['name']] as $errLine) {
			$errSummary .= "<strong>File&nbsp;&nbsp;: </strong>" . $errLine['file'] ."</br>";
			$errSummary .= "<strong>Code&nbsp;&nbsp;: </strong>" . htmlspecialchars($errLine['code'])."</br>";
			$errSummary .= "<strong>ERROR&nbsp;: </strong>" . $errLine['error']."</br>";
			$errSummary .= "<hr></br>";
		}
		
		$errSummary .= "</p>";
		
		$json['errSummary'] = $errSummary;

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
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

	private function validateXmlForm() {
		if (!$this->user->hasPermission('modify', 'extension/enhancement/ea_ocmod_manager')) {
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

		if (!$error && (!$dom->getElementsByTagName('name') || $dom->getElementsByTagName('name')->length == 0 || $dom->getElementsByTagName('name')->item(0)->textContent == '')) {
			$error = $this->language->get('error_name');
		}

		if (!$error && (!$dom->getElementsByTagName('code') || $dom->getElementsByTagName('code')->length == 0 || $dom->getElementsByTagName('code')->item(0)->textContent == '')) {
			$error = $this->language->get('error_code');
		}

		if (!$error) {
			$code = $dom->getElementsByTagName('code')->item(0)->textContent;

			$this->load->model('extension/enhancement/ea_ocmod_manager');
			
			$modification_info = $this->model_extension_enhancement_ea_ocmod_manager->getModificationByCode($code);

			if ($modification_info && (!isset($this->request->get['modification_id']) || $modification_info['modification_id'] != $this->request->get['modification_id'])) {
				$error = sprintf($this->language->get('error_exists'), $modification_info['name'], $modification_info['code']);
			}
		}

		if ($error) {
			$this->error['xml'] = $error;
		}

		return !$this->error;
	}

	public function getModFiles() {		
		$modification_id = $this->request->get['modification_id'];
		
		$this->load->model('extension/enhancement/ea_ocmod_manager');
		$mod_info = $this->model_extension_enhancement_ea_ocmod_manager->getModification($modification_id);
		
		$extension_install_id = $mod_info['extension_install_id'];	
		
		$requestData = $_REQUEST;

		$columns = array( 
			0 => 'extension_path_id',
			1 => 'path'
		);
		
		$sql = "SELECT * FROM `" . DB_PREFIX . "extension_path` WHERE `extension_install_id` = '" . (int)$extension_install_id . "'";
		
		$query = $this->db->query($sql);

		$totalData = count($query->rows);
		$totalFiltered = $totalData;
		
		if( !empty($requestData['search']['value']) ) {
			$sql .= " AND `path` LIKE '%".$requestData['search']['value']."%'";
		}
		
		$query = $this->db->query($sql);		
		$totalFiltered = count($query->rows);
		
		$sql .= " ORDER BY ". $columns[$requestData['order'][0]['column']]." ".$requestData['order'][0]['dir']." LIMIT ".(int)$requestData['start']." ,".(int)$requestData['length']." ";
		
		$query = $this->db->query($sql);

		$data = array();
		
		foreach ($query->rows as $row) {
			$nestedData = array(); 
			$nestedData[] = '<input type="checkbox" name="extension_path_id" value="'.$row["extension_path_id"].'" class="delinput" />';
			$nestedData[] = $row["path"];	
			$nestedData[] = '<button type="button" data-toggle="tooltip" title="'.$this->language->get('button_delete_file').'" class="btn btn-danger btn-xs btndel"><i class="fa fa-trash-o"></i></button>';
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

	public function autoCompleteName() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			if (isset($this->request->get['filter_name'])) {
				$filter_name = $this->request->get['filter_name'];
			} else {
				$filter_name = '';
			}
			
			$this->load->model('extension/enhancement/ea_ocmod_manager');

			$filter_data = array(
				'filter_name'      => $filter_name,
				'start'            => 0,
				'limit'            => 5
			);

			$results = $this->model_extension_enhancement_ea_ocmod_manager->getModifications($filter_data);

			foreach ($results as $result) {
				$json[] = array(
					'name'       		=> strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
					'modification_id' 	=> $result['modification_id']
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
			
			$this->load->model('extension/enhancement/ea_ocmod_manager');

			$filter_data = array(
				'start'            => 0,
				'limit'            => 5
			);

			$results = $this->model_extension_enhancement_ea_ocmod_manager->getModificationAuthors($filter_data);

			foreach ($results as $result) {
				$json[] = array(
					'author'       		=> strip_tags(html_entity_decode($result['author'], ENT_QUOTES, 'UTF-8')),
					'modification_id' 	=> $result['modification_id']
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
	
	public function reloadDev() {			
		$language_data = $this->load->language('extension/enhancement/ea_ocmod_manager');
		foreach($language_data as $key=>$value) {
			$data[$key] = $value;
		}		

		if (!$this->validate()) {
			$this->response->redirect($this->url->link('extension/enhancement/ea_ocmod_manager', 'user_token=' . $this->session->data['user_token']));
		}		
		
		$this->load->model('extension/enhancement/ea_ocmod_manager');
		
		$this->model_extension_enhancement_ea_ocmod_manager->deleteMetaMod();
		
		$this->model_extension_enhancement_ea_ocmod_manager->deleteMetaType();
		
		$dev_files = glob(DIR_SYSTEM . "*.ocmod.xml*", GLOB_BRACE);
		
		if(!empty($dev_files)) {		
			foreach($dev_files as $dev_file) {
				$xml = simplexml_load_file($dev_file) or die("Error: Cannot create object");
				
				$code = $xml->code;
	
				$link = $xml->link;
				$reg = '/(http|https):\/\/[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&amp;:\/~\+#]*[\w\-\@?^=%&amp;\/~\+#])/';
				preg_match_all($reg, $link, $matches, PREG_SET_ORDER, 0);				
				$cleanlink = $matches[0][0];
				
				$data= array(
					'code'            => $code,
					'name'            => $xml->name,
					'author'          => $xml->author,
					'version'         => $xml->version,
					'link'            => $cleanlink,
					'xml'             => '',
					'status'          => $this->endsWith($dev_file,".ocmod.xml") ? '1' : '0',
				);	
				
				$data['extension_install_id'] = $this->model_extension_enhancement_ea_ocmod_manager->addExtensionInstall($code.'.ocmod.xml');

				$modification_id = $this->model_extension_enhancement_ea_ocmod_manager->addModificationDev($data);

				$mod_file = basename($dev_file);

				$this->model_extension_enhancement_ea_ocmod_manager->addMeta($modification_id, $mod_file);
			}
			$this->session->data['success'] = $this->language->get('text_success_dev_reload');
			$this->response->redirect($this->url->link('extension/enhancement/ea_ocmod_manager', 'user_token=' . $this->session->data['user_token']));
		} else {
			$this->error['warning'] = $this->language->get('error_dev_files');
			$this->response->redirect($this->url->link('extension/enhancement/ea_ocmod_manager', 'user_token=' . $this->session->data['user_token']));
		}
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

	public function clear() {		
		$language_data = $this->load->language('extension/enhancement/ea_ocmod_manager');
		foreach($language_data as $key=>$value) {
			$data[$key] = $value;
		}

		$this->document->setTitle($this->language->get('heading_title'));

		if ($this->validate()) {
			$error_log = array();

			// Clear vqmod cache
			$vqmod_path = substr(DIR_SYSTEM, 0, -7) . 'vqmod/';

			if (file_exists($vqmod_path)) {
				$vqmod_cache = glob($vqmod_path.'vqcache/vq*');

				if ($vqmod_cache) {
					foreach ($vqmod_cache as $file) {
						if (file_exists($file)) {
							@unlink($file);
						}
					}
				}

				if (file_exists($vqmod_path.'mods.cache')) {
					@unlink($vqmod_path.'mods.cache');
				}

				if (file_exists($vqmod_path.'checked.cache')) {
					@unlink($vqmod_path.'checked.cache');
				}
			}			
			
			$files = array();

			// Make path into an array
			$path = array(DIR_MODIFICATION . '*');

			// While the path array is still populated keep looping through
			while (count($path) != 0) {
				$next = array_shift($path);

				foreach (glob($next) as $file) {
					// If directory add to path array
					if (is_dir($file)) {
						$path[] = $file . '/*';
					}

					// Add the file to the files to be deleted array
					$files[] = $file;
				}
			}

			// Reverse sort the file array
			rsort($files);

			// Clear all modification files
			foreach ($files as $file) {
				if ($file != DIR_MODIFICATION . 'index.html') {
					// If file just delete
					if (is_file($file)) {
						unlink($file);

					// If directory use the remove directory function
					} elseif (is_dir($file)) {
						rmdir($file);
					}
				}
			}

			$this->session->data['success'] = $this->language->get('text_success_cache');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('extension/enhancement/ea_ocmod_manager', 'user_token=' . $this->session->data['user_token'] . $url));
		}

		$this->getList();
	}

	public function clearlog() {		
		$language_data = $this->load->language('extension/enhancement/ea_ocmod_manager');
		foreach($language_data as $key=>$value){
			$data[$key] = $value;
		}
		
		$this->document->setTitle($this->language->get('heading_title'));
		
		if ($this->validate()) {
			$handle = fopen(DIR_LOGS . 'ocmod.log', 'w+');

			fclose($handle);

			$this->session->data['success'] = $this->language->get('text_success_logs');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('extension/enhancement/ea_ocmod_manager', 'user_token=' . $this->session->data['user_token'] . $url));
		}

		$this->getList();
	}

	public function uploadDev() {		
		$language_data = $this->load->language('extension/enhancement/ea_ocmod_manager');
		foreach($language_data as $key=>$value){
			$data[$key] = $value;
		}

		$json = array();

		if (!$this->user->hasPermission('modify', 'marketplace/install')) {
			$json['error'] = $this->language->get('error_permission_install');
		}
		
		if (isset($this->request->files['import_dev']['name'])) {
			if (substr($this->request->files['import_dev']['name'], -10) != '.ocmod.xml') {
				$json['error'] = $this->language->get('error_filetype');
			}

			if ($this->request->files['import_dev']['error'] != UPLOAD_ERR_OK) {
				$json['error'] = $this->language->get('error_upload_' . $this->request->files['import_dev']['error']);
			}
		} else {
			$json['error'] = $this->language->get('error_upload');
		}		

		if (!$json) {
			$file = html_entity_decode($this->request->files['import_dev']['name'], ENT_QUOTES, 'UTF-8');

			move_uploaded_file($this->request->files['import_dev']['tmp_name'], DIR_SYSTEM . $file);
			
			$filepath = DIR_SYSTEM . $file;
			
			if (is_file($filepath)) {
				$xml = html_entity_decode(file_get_contents($filepath), ENT_QUOTES, 'UTF-8');
				$filename = pathinfo($filepath, PATHINFO_FILENAME);
				
				if (!$this->validateXml($xml)) {
					rename (DIR_SYSTEM  . $filename . ".xml" , DIR_SYSTEM . $filename. ".xml_" );					
					$json['error'] = $this->language->get('error_xml_invalid');
				}				

				if (!$json) {
					$json['success'] = sprintf($this->language->get('text_success_dev_uploaded'), $filename . ".xml");
					$json['reload'] = $this->language->get('text_please_click_reload');
				}								
			} else {
				$json['error'] = $this->language->get('error_file');
			}

		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function uploadXml() {		
		$language_data = $this->load->language('extension/enhancement/ea_ocmod_manager');
		foreach($language_data as $key=>$value){
			$data[$key] = $value;
		}
		
		$modification_id = (int)$this->request->get['modification_id'];
		
		$this->load->model('extension/enhancement/ea_ocmod_manager');
		$mod_info = $this->model_extension_enhancement_ea_ocmod_manager->getModification($modification_id);

		$json = array();

		if (!$this->user->hasPermission('modify', 'marketplace/install')) {
			$json['error'] = $this->language->get('error_permission_install');
		}

		if (isset($this->request->files['import_xml']['tmp_name']) && is_uploaded_file($this->request->files['import_xml']['tmp_name'])) {
			$filename = tempnam(DIR_UPLOAD, 'bac');

			move_uploaded_file($this->request->files['import_xml']['tmp_name'], $filename);
		} elseif (isset($this->request->get['import_xml'])) {
			$filename = html_entity_decode($this->request->get['import_xml'], ENT_QUOTES, 'UTF-8');
		} else {
			$filename = '';
		}

		if (!is_file($filename)) {
			$json['error'] = $this->language->get('error_file');
		}

		if (!$json) {
			$xml = file_get_contents($filename);

			if ($xml) {
				try {
					$dom = new DOMDocument('1.0', 'UTF-8');
					$dom->preserveWhiteSpace = false;
					$dom->loadXml($xml);

					$code = $dom->getElementsByTagName('code')->item(0)->textContent;
					
					$name = $dom->getElementsByTagName('name')->item(0)->textContent;

					$author = $dom->getElementsByTagName('author')->item(0)->textContent;

					$version = $dom->getElementsByTagName('version')->item(0)->textContent;

					if ($dom->getElementsByTagName('link')->length) {
						$link = $dom->getElementsByTagName('link')->item(0)->textContent;
						$reg = '/(http|https):\/\/[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&amp;:\/~\+#]*[\w\-\@?^=%&amp;\/~\+#])/';
						preg_match_all($reg, $link, $matches, PREG_SET_ORDER, 0);				
						$cleanlink = $matches[0][0];	
					} else {
						$cleanlink = '';
					}						

					if (!$json) {
						$data = array(
							'code'              => $code,
							'name'              => $name,
							'author'            => $author,
							'version'           => $version,
							'link'              => $cleanlink,
							'xml'               => html_entity_decode($xml, ENT_QUOTES, 'UTF-8'),
							'status'            => (int)$mod_info['status']
						);

						$this->model_extension_enhancement_ea_ocmod_manager->editModification($modification_id, $data);
					}
				} catch(Exception $exception) {
					$json['error'] = sprintf($this->language->get('error_exception'), $exception->getCode(), $exception->getMessage(), $exception->getFile(), $exception->getLine());
				}
			}

			if (!$json) {
				$json['success'] = $this->language->get('text_success_file_uploaded');
				$json['location'] = 'index.php?route=extension/enhancement/ea_ocmod_manager/refresh&user_token=' . $this->session->data['user_token'];
			}
		}

		if (is_file($filename)) {
			unlink($filename);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
 
	public function downloadXml() {
		$this->load->model('extension/enhancement/ea_ocmod_manager');

		if (isset($this->request->get['modification_id']) && $this->user->hasPermission('modify', 'extension/enhancement/ea_ocmod_manager')) {
			$mod = $this->model_extension_enhancement_ea_ocmod_manager->getModification($this->request->get['modification_id']);

			$name = $mod['code'] . '.ocmod.xml';
			
			if (!headers_sent()) {
				if (!empty($mod['xml'])) {
					header('Content-Type: application/octet-stream');
					header('Content-Description: File Transfer');
					header('Content-Disposition: attachment; filename="' . $name . '"');
					header('Content-Transfer-Encoding: binary');
					header('Expires: 0');
					header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
					header('Pragma: public');
					header('Content-Length: ' . utf8_strlen($name));
					echo $mod['xml'];
					exit;
				} else {
					exit('Error: Could not find file ' . $name . '!');
				}
			} else {
				exit('Error: Headers already sent out!');
			}
		}

		$this->getList();
	}
 
	public function exportMod() {
		$this->load->model('extension/enhancement/ea_ocmod_manager');

		$json = array();
		
		if (isset($this->request->get['modification_id']) && $this->validate()) {
			$mod_info = $this->model_extension_enhancement_ea_ocmod_manager->getModification($this->request->get['modification_id']);
			$mod_files = $this->model_extension_enhancement_ea_ocmod_manager->getAllModFiles($mod_info['extension_install_id']);
			
			if (!extension_loaded('zip')) {
				$json['error'] = $this->language->get('error_zip_extension');				
			} 
			
			if (!$json) {
				$file_path = $this->base_dir;				
				
				$filename = pathinfo($mod_info['filename'], PATHINFO_FILENAME);
				
				$xml_file = 'install.xml';
				
				if(file_exists($file_path.$xml_file)) {
					unlink($file_path.$xml_file);
				}
				
				if ($mod_info['type'] == 'Installed') {
					file_put_contents($file_path.$xml_file, html_entity_decode($mod_info["xml"], ENT_QUOTES, 'UTF-8'));	
				} else {
					$xml = file_get_contents(DIR_SYSTEM .  $mod_info['filename']);
					file_put_contents($file_path.$xml_file, html_entity_decode($xml, ENT_QUOTES, 'UTF-8'));
				}
				
				if (!stripos($mod_info['filename'],'.ocmod.')) {
					$zip_file_name = $file_path.$filename.".ocmod.zip";	
				} else {
					$zip_file_name = $file_path.$filename.".zip";
				}
				
				if(file_exists($zip_file_name)) {
					unlink($zip_file_name);
				}
				
				$zip = new ZipArchive();
				
				if($zip->open($zip_file_name,ZipArchive::CREATE) === true) {  
					foreach ($mod_files as $mod_file) {						
						if ($mod_info['type'] == 'Installed') {
							$zip->addFile($file_path.stripslashes($xml_file),stripslashes($xml_file));
							if(file_exists($file_path.$mod_file["path"])) {    
								$zip->addFile($file_path.stripslashes($mod_file["path"]),'upload/'.stripslashes($mod_file["path"])); 
							}
						} else {
							if(file_exists(DIR_SYSTEM .  $mod_info['filename'])) {    
								$zip->addFile($file_path.stripslashes($xml_file),stripslashes($xml_file));  
							}	
							if(file_exists($file_path.$mod_file["path"])) {    
								$zip->addFile($file_path.stripslashes($mod_file["path"]),'upload/'.stripslashes($mod_file["path"])); 
							}							
						}						
					}					
				} else {
					$json['error'] = $this->language->get('error_zip_create');					
				}
				
				$zip->close();
				unlink($file_path.$xml_file);
				
				if (!$json) {
					if(file_exists($zip_file_name)) {
						$json['zip_name'] = $zip_file_name;
						$json['zip_file'] = HTTP_CATALOG.$filename.".zip";
					}
				}
			}

			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
		}
	}
 
	public function deleteZip() {
		$zip_file_name = html_entity_decode($this->request->get['zip_file_name']);
		if(file_exists($zip_file_name)) {
			unlink($zip_file_name);
		}
	}

	public function refresh($data = array()) {		
		$language_data = $this->load->language('extension/enhancement/ea_ocmod_manager');
		foreach($language_data as $key=>$value){
			$data[$key] = $value;
		}

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/enhancement/ea_ocmod_manager');

		if ($this->validate()) {
			// Just before files are deleted, if config settings say maintenance mode is off then turn it on
			$maintenance = $this->config->get('config_maintenance');

			// Clear logs on refresh
			$handle = fopen(DIR_LOGS . 'ocmod.log', 'w+');
			fclose($handle);

			$handle = fopen(DIR_LOGS . 'ocmod_error.log', 'w+');
			fclose($handle);
			
			// Clear twig cache on refresh
			$directories = glob(DIR_CACHE . '*', GLOB_ONLYDIR);

			if ($directories) {
				foreach ($directories as $directory) {
					$files = glob($directory . '/*');
					
					foreach ($files as $file) { 
						if (is_file($file)) {
							unlink($file);
						}
					}
					
					if (is_dir($directory)) {
						rmdir($directory);
					}
				}
			}
			
			$this->load->model('setting/setting');

			$this->model_setting_setting->editSettingValue('config', 'config_maintenance', true);

			//Error Log
			$error_log = array();
			
			//Log
			$log = array();

			// Clear all modification files
			$files = array();

			// Make path into an array
			$path = array(DIR_MODIFICATION . '*');

			// While the path array is still populated keep looping through
			while (count($path) != 0) {
				$next = array_shift($path);

				foreach (glob($next) as $file) {
					// If directory add to path array
					if (is_dir($file)) {
						$path[] = $file . '/*';
					}

					// Add the file to the files to be deleted array
					$files[] = $file;
				}
			}

			// Reverse sort the file array
			rsort($files);

			// Clear all modification files
			foreach ($files as $file) {
				if ($file != DIR_MODIFICATION . 'index.html') {
					// If file just delete
					if (is_file($file)) {
						unlink($file);

					// If directory use the remove directory function
					} elseif (is_dir($file)) {
						rmdir($file);
					}
				}
			}

			// Begin
			$xml = array();

			// Load the default modification XML
			$xml[] = file_get_contents(DIR_SYSTEM . 'modification.xml');

			// This is purly for developers so they can run mods directly and have them run without upload after each change.
			$files = glob(DIR_SYSTEM . '*.ocmod.xml');

			if ($files) {
				foreach ($files as $file) {
					$xml[] = file_get_contents($file);
				}
			}

			// Get the default modification file
			$results = $this->model_extension_enhancement_ea_ocmod_manager->getModifications();

			foreach ($results as $result) {
				if ($result['status'] && $result['xml']) {
					$xml[] = $result['xml'];
				}
			}

			$modification = array();

			foreach ($xml as $xml) {
				if (empty($xml)){
					continue;
				}
				
				$dom = new DOMDocument('1.0', 'UTF-8');
				$dom->preserveWhiteSpace = false;
				$dom->loadXml($xml);

				// Log
				$log[] = 'MOD: ' . $dom->getElementsByTagName('name')->item(0)->textContent;
				
				$error_log_mod = 'MOD: ' . $dom->getElementsByTagName('name')->item(0)->textContent;

				// Wipe the past modification store in the backup array
				$recovery = array();

				// Set the a recovery of the modification code in case we need to use it if an abort attribute is used.
				if (isset($modification)) {
					$recovery = $modification;
				}

				$files = $dom->getElementsByTagName('modification')->item(0)->getElementsByTagName('file');

				foreach ($files as $file) {
					$operations = $file->getElementsByTagName('operation');
					
					$file_error = $file->getAttribute('error');

					$files = explode('|', $file->getAttribute('path'));

					foreach ($files as $file) {
						$path = '';

						// Get the full path of the files that are going to be used for modification
						if ((substr($file, 0, 7) == 'catalog')) {
							$path = DIR_CATALOG . substr($file, 8);
						}

						if ((substr($file, 0, 5) == 'admin')) {
							$path = DIR_APPLICATION . substr($file, 6);
						}

						if ((substr($file, 0, 6) == 'system')) {
							$path = DIR_SYSTEM . substr($file, 7);
						}

						if ($path) {
							$files = glob($path, GLOB_BRACE);
							
							if (!$files) {
								if ($file_error != 'skip') {
									$error_log[] = '----------------------------------------------------------------';
									$error_log[] = $error_log_mod;
									$error_log[] = 'MISSING FILE!';
									$error_log[] = $path;									
								}
							}

							if ($files) {
								foreach ($files as $file) {
									// Get the key to be used for the modification cache filename.
									if (substr($file, 0, strlen(DIR_CATALOG)) == DIR_CATALOG) {
										$key = 'catalog/' . substr($file, strlen(DIR_CATALOG));
									}

									if (substr($file, 0, strlen(DIR_APPLICATION)) == DIR_APPLICATION) {
										$key = 'admin/' . substr($file, strlen(DIR_APPLICATION));
									}

									if (substr($file, 0, strlen(DIR_SYSTEM)) == DIR_SYSTEM) {
										$key = 'system/' . substr($file, strlen(DIR_SYSTEM));
									}

									// If file contents is not already in the modification array we need to load it.
									if (!isset($modification[$key])) {
										$content = file_get_contents($file);

										$modification[$key] = preg_replace('~\r?\n~', "\n", $content);
										$original[$key] = preg_replace('~\r?\n~', "\n", $content);

										// Log
										$log[] = PHP_EOL . 'FILE: ' . $key;
									}

									foreach ($operations as $operation) {
										$error = $operation->getAttribute('error');

										// Ignoreif
										$ignoreif = $operation->getElementsByTagName('ignoreif')->item(0);

										if ($ignoreif) {
											if ($ignoreif->getAttribute('regex') != 'true') {
												if (strpos($modification[$key], $ignoreif->textContent) !== false) {
													continue;
												}
											} else {
												if (preg_match($ignoreif->textContent, $modification[$key])) {
													continue;
												}
											}
										}

										$status = false;

										// Search and replace
										if ($operation->getElementsByTagName('search')->item(0)->getAttribute('regex') != 'true') {
											// Search
											$search = $operation->getElementsByTagName('search')->item(0)->textContent;
											$trim = $operation->getElementsByTagName('search')->item(0)->getAttribute('trim');
											$index = $operation->getElementsByTagName('search')->item(0)->getAttribute('index');

											// Trim line if no trim attribute is set or is set to true.
											if (!$trim || $trim == 'true') {
												$search = trim($search);
											}

											// Add
											$add = $operation->getElementsByTagName('add')->item(0)->textContent;
											$trim = $operation->getElementsByTagName('add')->item(0)->getAttribute('trim');
											$position = $operation->getElementsByTagName('add')->item(0)->getAttribute('position');
											$offset = $operation->getElementsByTagName('add')->item(0)->getAttribute('offset');

											if ($offset == '') {
												$offset = 0;
											}

											// Trim line if is set to true.
											if ($trim == 'true') {
												$add = trim($add);
											}

											// Log
											$log[] = 'CODE: ' . $search;

											// Check if using indexes
											if ($index !== '') {
												$indexes = explode(',', $index);
											} else {
												$indexes = array();
											}

											// Get all the matches
											$i = 0;

											$lines = explode("\n", $modification[$key]);

											for ($line_id = 0; $line_id < count($lines); $line_id++) {
												$line = $lines[$line_id];

												// Status
												$match = false;

												// Check to see if the line matches the search code.
												if (stripos($line, $search) !== false) {
													// If indexes are not used then just set the found status to true.
													if (!$indexes) {
														$match = true;
													} elseif (in_array($i, $indexes)) {
														$match = true;
													}

													$i++;
												}

												// Now for replacing or adding to the matched elements
												if ($match) {
													switch ($position) {
														default:
														case 'replace':
															$new_lines = explode("\n", $add);

															if ($offset < 0) {
																array_splice($lines, $line_id + $offset, abs($offset) + 1, array(str_replace($search, $add, $line)));

																$line_id -= $offset;
															} else {
																array_splice($lines, $line_id, $offset + 1, array(str_replace($search, $add, $line)));
															}
															break;
														case 'before':
															$new_lines = explode("\n", $add);

															array_splice($lines, $line_id - $offset, 0, $new_lines);

															$line_id += count($new_lines);
															break;
														case 'after':
															$new_lines = explode("\n", $add);

															array_splice($lines, ($line_id + 1) + $offset, 0, $new_lines);

															$line_id += count($new_lines);
															break;
													}

													// Log
													$log[] = 'LINE: ' . $line_id;
													$status = true;
												}
											}

											$modification[$key] = implode("\n", $lines);
										} else {
											$search = trim($operation->getElementsByTagName('search')->item(0)->textContent);
											$limit = $operation->getElementsByTagName('search')->item(0)->getAttribute('limit');
											$replace = trim($operation->getElementsByTagName('add')->item(0)->textContent);

											// Limit
											if (!$limit) {
												$limit = -1;
											}

											// Log
											$match = array();

											preg_match_all($search, $modification[$key], $match, PREG_OFFSET_CAPTURE);

											// Remove part of the the result if a limit is set.
											if ($limit > 0) {
												$match[0] = array_slice($match[0], 0, $limit);
											}

											if ($match[0]) {
												$log[] = 'REGEX: ' . $search;

												for ($i = 0; $i < count($match[0]); $i++) {
													$log[] = 'LINE: ' . (substr_count(substr($modification[$key], 0, $match[0][$i][1]), "\n") + 1);
												}

												$status = true;
											}

											// Make the modification
											$modification[$key] = preg_replace($search, $replace, $modification[$key], $limit);
										}

										if (!$status) {
											if ($error != 'skip') {
												$error_log[] = "\n";
												$error_log[] = $error_log_mod;
												$error_log[] = 'NOT FOUND!';
												$error_log[] = 'CODE: ' . $search;
												$error_log[] = 'FILE: ' . $key;
											}
											
											// Abort applying this modification completely.
											if ($error == 'abort') {
												$modification = $recovery;
												// Log
												$log[] = 'NOT FOUND - ABORTING!';
												break 5;
											}
											// Skip current operation or break
											elseif ($error == 'skip') {
												// Log
												$log[] = 'NOT FOUND - OPERATION SKIPPED!';
												continue;
											}
											// Break current operations
											else {
												// Log
												$log[] = 'NOT FOUND - OPERATIONS ABORTED!';
											 	break;
											}
										}
									}
								}
							}
						}
					}
				}

				// Log
				$log[] = '----------------------------------------------------------------';
			}

			// Log
			$ocmod = new Log('ocmod.log');
			$ocmod->write(implode("\n", $log));
			
			if ($error_log) {
				$ocmod = new Log('ocmod_error.log');
				$ocmod->write(implode("\n", $error_log));
			}

			// Write all modification files
			foreach ($modification as $key => $value) {
				// Only create a file if there are changes

				if ($original[$key] != $value) {
					$path = '';

					$directories = explode('/', dirname($key));

					foreach ($directories as $directory) {
						$path = $path . '/' . $directory;

						if (!is_dir(DIR_MODIFICATION . $path)) {
							@mkdir(DIR_MODIFICATION . $path, 0777);
						}
					}

					$handle = fopen(DIR_MODIFICATION . $key, 'w');

					fwrite($handle, $value);

					fclose($handle);
				}
			}

			// Maintance mode back to original settings
			$this->model_setting_setting->editSettingValue('config', 'config_maintenance', $maintenance);

			$url = '';
			
			if (isset($this->request->get['modification_id'])) {
				$url = $this->getListUrlParams(array('modification_id' => $this->request->get['modification_id']));
			}
			
			if (isset($this->request->get['remove'])) {
				$this->session->data['success'] = $this->language->get('text_success');
				$this->response->redirect($this->url->link('marketplace/modification', 'user_token=' . $this->session->data['user_token']));
			}
			
			if (isset($this->request->get['modify'])) {
				if ($this->request->get['modify'] == 1) {
					$this->session->data['success'] = $this->language->get('text_success_enabled');
				} else {
					$this->session->data['success'] = $this->language->get('text_success_disabled');
				}				
			}
			
			if (isset($this->request->get['redirect'])) {
				$this->session->data['success'] = $this->language->get('text_success_save_refresh');
				$this->response->redirect($this->url->link('extension/enhancement/ea_ocmod_manager/edit', 'user_token=' . $this->session->data['user_token'] . $url));
			}
			
			$this->session->data['success'] = $this->language->get('text_success_refreshed');
			
			$this->response->redirect($this->url->link(!empty($data['redirect']) ? $data['redirect'] : 'extension/enhancement/ea_ocmod_manager', 'user_token=' . $this->session->data['user_token'] . $url));
		}

		$this->getList();
	}

	static function modifiedFiles($dir, $dirLen = 0) {
		$tree = glob(rtrim($dir, '/') . '/*');
		if (!$dirLen) {
			$dirLen = strlen($dir);
		}
		
		$files = array();

	    if (is_array($tree)) {
	        foreach($tree as $file) {
	        	if ($file == $dir . 'index.html') {
					continue;
				} elseif (is_file($file)) {
	                $files[] = substr($file, $dirLen);
	            } elseif (is_dir($file)) {
	                $files = array_merge($files, self::modifiedFiles($file, $dirLen));
	            }
	        }
	    }

	    return $files;
	}

	protected function getMainXmlFiles($filter = array()) {
		$return = array();

		$baseLen = strlen(substr(DIR_SYSTEM, 0, -7));

		$xml = array();

		$xml[] = file_get_contents(DIR_SYSTEM . 'modification.xml');

		$files = glob(DIR_SYSTEM . '*.ocmod.xml');

		if ($files) {
			foreach ($files as $file) {
				$xml[] = file_get_contents($file);
			}
		}
		
		$results = $this->model_extension_enhancement_ea_ocmod_manager->getModifications($filter);

		foreach ($results as $result) {
			if ($result['status'] && $result['xml']) {
				$xml[] = $result['xml'];
			}
		}

		foreach ($xml as $xml) {
			if (empty($xml)){
				continue;
			}

			$dom = new DOMDocument('1.0', 'UTF-8');
			$dom->preserveWhiteSpace = false;
			$dom->loadXml($xml);

			$files = $dom->getElementsByTagName('modification')->item(0)->getElementsByTagName('file');

			foreach ($files as $file) {
				$operations = $file->getElementsByTagName('operation');

				$file_error = $file->getAttribute('error');

				$files = explode('|', $file->getAttribute('path'));

				foreach ($files as $file) {
					$path = '';

					// Get the full path of the files that are going to be used for modification
					if ((substr($file, 0, 7) == 'catalog')) {
						$path = DIR_CATALOG . substr($file, 8);
					}

					if ((substr($file, 0, 5) == 'admin')) {
						$path = DIR_APPLICATION . substr($file, 6);
					}

					if ((substr($file, 0, 6) == 'system')) {
						$path = DIR_SYSTEM . substr($file, 7);
					}

					if ($path) {
						$files = glob($path, GLOB_BRACE);

						if ($files) {
							foreach ($files as $file) {
								$file = substr($file, $baseLen);

								if (!isset($return[$file])) {
									$return[$file] = array();
								}

								if ($dom->getElementsByTagName('code')->length) {
									$code = $dom->getElementsByTagName('code')->item(0)->textContent;
								} else {
									continue;
								}

								if (!empty($return[$file])) {
									foreach ($return[$file] as $return_file) {
										if ($return_file['code'] == $code) {
											continue 2;
										}
									}
								}

								if ($dom->getElementsByTagName('name')->length) {
									$name = $dom->getElementsByTagName('name')->item(0)->textContent;
								} else {
									continue;
								}

								if ($dom->getElementsByTagName('author')->length) {
									$author = $dom->getElementsByTagName('author')->item(0)->textContent;
								} else {
									$author = '';
								}

								$return[$file][] = array(
									'code' => $code,
									'name' => $name,
									'author' => $author
								);
							}
						}
					}
				}
			}
		}

		return $return;
	}

	protected function getListUrlParams(array $params = array()) {
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
	
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/enhancement/ea_ocmod_manager')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}	


// ****************************************************************************************************** //	
// *********************************************  Comment Stuff  **************************************** //	
// ****************************************************************************************************** //		
	
	public function getAllComments() {		
		$language_data = $this->load->language('extension/enhancement/ea_ocmod_manager');
		foreach($language_data as $key=>$value){
			$data[$key] = $value;
		}
		
		$requestData = $_REQUEST;

		$columns = array( 
			0 => 'comment_id', 
			1 => 'name',
			2 => 'comment',
			3 => 'mc.date_added'
		);
		
		$sql = "SELECT *, mc.date_added AS date_added FROM `" . DB_PREFIX . "modification` mm LEFT JOIN `" . DB_PREFIX . "ea_modification_comment` mc ON (mm.modification_id = mc.mod_id) WHERE `comment` != ''";
		
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

	public function getModComments() {		
		$language_data = $this->load->language('extension/enhancement/ea_ocmod_manager');
		foreach($language_data as $key=>$value){
			$data[$key] = $value;
		}
		
		$requestData = $_REQUEST;

		$columns = array( 
			0 => 'comment_id',
			1 => 'comment',
			2 => 'date_added'
		);
		
		$sql = "SELECT * FROM `" . DB_PREFIX . "ea_modification_comment` WHERE `mod_id` = '" . (int)$this->request->get['modification_id'] . "'";
		
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
		$language_data = $this->load->language('extension/enhancement/ea_ocmod_manager');
		foreach($language_data as $key=>$value){
			$data[$key] = $value;
		}
		
		$comment_id = $this->request->get['comment_id'];
		
		$json = array();

		$this->load->model('extension/enhancement/ea_ocmod_manager');
		
		if (!$this->validate()) {
			$json['error'] = $this->language->get('error_permission');
		}
		
		if (!$json) {
			$this->model_extension_enhancement_ea_ocmod_manager->deleteModComment($comment_id);
			$json['success'] = $this->language->get('text_success_comment_deleted');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function getFormComment() {		
		$language_data = $this->load->language('extension/enhancement/ea_ocmod_manager');
		foreach($language_data as $key=>$value){
			$data[$key] = $value;
		}
		
		$data['modification_id'] = (int)$this->request->get['modification_id'];
		
		$data['user_token'] = $this->session->data['user_token'];

		if ($this->request->get['route'] == 'extension/enhancement/ea_ocmod_manager/edit') {
			$data['reload_main'] = '';
		} else {
			$data['reload_main'] = html_entity_decode($this->url->link('extension/enhancement/ea_ocmod_manager', 'user_token=' . $this->session->data['user_token']));
		}	
		
		$this->response->setOutput($this->load->view('extension/enhancement/ocmod_manager/ea_ocmod_manager_comment_form', $data));
	}

	public function saveComment() {		
		$language_data = $this->load->language('extension/enhancement/ea_ocmod_manager');
		foreach($language_data as $key=>$value){
			$data[$key] = $value;
		}
		
		$modification_id = (int)$this->request->get['modification_id'];

		$this->load->model('extension/enhancement/ea_ocmod_manager');	
		
		$json = array();

		if (isset($this->error['md_comment'])) {
			$json['error_comment'] = $this->error['md_comment'];
		}		

		if (!$json) {
			if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateCommentForm()) {
				$this->model_extension_enhancement_ea_ocmod_manager->addModComment($modification_id,$this->request->post['mod_comment']);			
				$json['success'] = $this->language->get('text_success_comment_saved');
			}
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	protected function validateCommentForm() {		
		$language_data = $this->load->language('extension/enhancement/ea_ocmod_manager');
		foreach($language_data as $key=>$value){
			$data[$key] = $value;
		}

		if ((utf8_strlen($this->request->post['mod_comment']) < 10) || (utf8_strlen($this->request->post['mod_comment']) > 3000)) {
			$this->error['md_comment'] = $this->language->get('error_comment');
		}

		return !$this->error;
	}	
	
// ****************************************************************************************************** //	
// *********************************************  Contact Stuff  **************************************** //	
// ****************************************************************************************************** //	
	
	public function contact_developer() {
		$modification_id = $this->request->get['mod_id'];		
		$this->getFormContact($modification_id);
	}

	protected function getFormContact($modification_id) {		
		$language_data = $this->load->language('extension/enhancement/ea_ocmod_manager');
		foreach($language_data as $key=>$value){
			$data[$key] = $value;
		}
		
		$this->document->setTitle($this->language->get('heading_title_contact'));
		
		$this->load->model('extension/enhancement/ea_ocmod_manager');

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

		$mod_info = $this->model_extension_enhancement_ea_ocmod_manager->getModification($modification_id);
		$email_result = $this->model_extension_enhancement_ea_ocmod_manager->getModificationEmail($modification_id);

		$data['from_name'] = $this->config->get('config_owner');
		$data['from_email'] = $this->config->get('config_email');
		
		if ($email_result) {
			$data['dev_email'] = $email_result['mod_email'];
		} else {
			$data['dev_email'] = '';
		}
		
		if ($mod_info) {
			$data['email_subject'] = sprintf($this->language->get('text_email_subject'), $mod_info['name']);
		} else {
			$data['email_subject'] = '';
		}
		
		$data['modification_id'] = $modification_id;

		$data['action'] = html_entity_decode($this->url->link('extension/enhancement/ea_ocmod_manager/contact_developer', 'user_token=' . $this->session->data['user_token'] . '&modification_id=' . $modification_id));

		$max_upload = (int)(ini_get('upload_max_filesize'));
		$max_post = (int)(ini_get('post_max_size'));
		$max_memory = (int)(ini_get('memory_limit'));
		$file_limit = min($max_upload, $max_post, $max_memory);
		
		$data['file_limit'] = 'Max: ' . $file_limit . 'MB';

		$data['userPermission'] = $this->user->hasPermission('modify', 'tool/upload');
		
		$data['user_token'] = $this->session->data['user_token'];
		
		$this->response->setOutput($this->load->view('extension/enhancement/ocmod_manager/ea_ocmod_manager_contact_form', $data));
	}
	
	protected function validateContactForm() {		
		$language_data = $this->load->language('extension/enhancement/ea_ocmod_manager');
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
	
	public function uploadAttachment() {		
		$language_data = $this->load->language('extension/enhancement/ea_ocmod_manager');
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
		$language_data = $this->load->language('extension/enhancement/ea_ocmod_manager');
		foreach($language_data as $key=>$value){
			$data[$key] = $value;
		}
		
		$modification_id = $this->request->get['mod_id'];

		$this->load->model('extension/enhancement/ea_ocmod_manager');

		$email_result = $this->model_extension_enhancement_ea_ocmod_manager->getModificationEmail($modification_id);		
		
		$json = array();

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateContactForm()) {
			if (!$email_result) {
				$this->model_extension_enhancement_ea_ocmod_manager->addModificationEmail($modification_id,$this->request->post['to_email']);
			} else {
				if ($email_result['mod_email'] != $this->request->post['to_email']) {
					$this->model_extension_enhancement_ea_ocmod_manager->editModificationEmail($modification_id,$this->request->post['to_email']);
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
	
// ****************************************************************************************************** //	
// *******************************************  Install/Uninstall  ************************************** //	
// ****************************************************************************************************** //	
	
	public function uploadMod() {		
		$language_data = $this->load->language('extension/enhancement/ea_ocmod_manager');
		foreach($language_data as $key=>$value) {
			$data[$key] = $value;
		}
		
		$this->load->model('extension/enhancement/ea_ocmod_manager');

		$json = array();

		// Check user has permission
		if (!$this->user->hasPermission('modify', 'marketplace/installer')) {
			$json['error'] = $this->language->get('error_permission_install');
		}

		// Check if there is a install zip already there
		$files = glob(DIR_STORAGE . 'marketplace/*.tmp');

		foreach ($files as $file) {
			if (is_file($file) && (filectime($file) < (time() - 5))) {
				unlink($file);
			}
			
			if (is_file($file)) {
				$json['error'] = $this->language->get('error_install');
				
				break;
			}
		}

		// Check for any install directories
		$directories = glob(DIR_STORAGE . 'marketplace/tmp-*');
		
		foreach ($directories as $directory) {
			if (is_dir($directory) && (filectime($directory) < (time() - 5))) {
				// Get a list of files ready to upload
				$files = array();
	
				$path = array($directory);
	
				while (count($path) != 0) {
					$next = array_shift($path);
	
					// We have to use scandir function because glob will not pick up dot files.
					foreach (array_diff(scandir($next), array('.', '..')) as $file) {
						$file = $next . '/' . $file;
	
						if (is_dir($file)) {
							$path[] = $file;
						}
	
						$files[] = $file;
					}
				}
	
				rsort($files);
	
				foreach ($files as $file) {
					if (is_file($file)) {
						unlink($file);
					} elseif (is_dir($file)) {
						rmdir($file);
					}
				}
	
				rmdir($directory);
			}
			
			if (is_dir($directory)) {
				$json['error'] = $this->language->get('error_install');
				
				break;
			}		
		}
		
		if (isset($this->request->files['file']['name'])) {
			if (substr($this->request->files['file']['name'], -10) != '.ocmod.zip') {
				$json['error'] = $this->language->get('error_filetype');
			}

			if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
				$json['error'] = $this->language->get('error_upload_' . $this->request->files['file']['error']);
			}
		} else {
			$json['error'] = $this->language->get('error_upload');
		}

		if (!$json) {
			$this->session->data['install'] = token(10);
			
			$file = DIR_STORAGE . 'marketplace/' . $this->session->data['install'] . '.tmp';
			
			move_uploaded_file($this->request->files['file']['tmp_name'], $file);

			if (is_file($file)) {
				$extension_install_id = $this->model_extension_enhancement_ea_ocmod_manager->addExtensionInstall($this->request->files['file']['name']);
				
				$json['text'] = $this->language->get('text_install');

				$json['next'] = str_replace('&amp;', '&', $this->url->link('extension/enhancement/ea_ocmod_manager/installMod', 'user_token=' . $this->session->data['user_token'] . '&extension_install_id=' . $extension_install_id));				
				
				$json['location'] = 'index.php?route=extension/enhancement/ea_ocmod_manager&user_token=' . $this->session->data['user_token'];
			} else {
				$json['error'] = $this->language->get('error_file');
			}
		}
		
		$this->model_extension_enhancement_ea_ocmod_manager->disableModificationByInstallId($extension_install_id);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function installMod() {		
		$language_data = $this->load->language('extension/enhancement/ea_ocmod_manager');
		foreach($language_data as $key=>$value) {
			$data[$key] = $value;
		}

		$json = array();
			
		if (isset($this->request->get['extension_install_id'])) {
			$extension_install_id = $this->request->get['extension_install_id'];
		} else {
			$extension_install_id = 0;
		}
			
		if (!$this->user->hasPermission('modify', 'marketplace/install')) {
			$json['error'] = $this->language->get('error_permission_install');
		}

		// Make sure the file name is stored in the session.
		if (!isset($this->session->data['install'])) {
			$json['error'] = $this->language->get('error_file');
		} elseif (!is_file(DIR_STORAGE . 'marketplace/' . $this->session->data['install'] . '.tmp')) {
			$json['error'] = $this->language->get('error_file');
		}

		if (!$json) {
			$json['text'] = $this->language->get('text_unzip');

			$json['next'] = str_replace('&amp;', '&', $this->url->link('extension/enhancement/ea_ocmod_manager/unzipMod', 'user_token=' . $this->session->data['user_token'] . '&extension_install_id=' . $extension_install_id));
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function unzipMod() {		
		$language_data = $this->load->language('extension/enhancement/ea_ocmod_manager');
		foreach($language_data as $key=>$value) {
			$data[$key] = $value;
		}

		$json = array();

		if (isset($this->request->get['extension_install_id'])) {
			$extension_install_id = $this->request->get['extension_install_id'];
		} else {
			$extension_install_id = 0;
		}
		
		if (!$this->user->hasPermission('modify', 'marketplace/install')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!isset($this->session->data['install'])) {
			$json['error'] = $this->language->get('error_file');
		} elseif (!is_file(DIR_STORAGE . 'marketplace/' . $this->session->data['install'] . '.tmp')) {
			$json['error'] = $this->language->get('error_file');
		}
		
		// Sanitize the filename
		if (!$json) {
			$file = DIR_STORAGE . 'marketplace/' . $this->session->data['install'] . '.tmp';
					
			// Unzip the files
			$zip = new ZipArchive();

			if ($zip->open($file)) {
				$zip->extractTo(DIR_STORAGE . 'marketplace/' . 'tmp-' . $this->session->data['install']);
				$zip->close();
			} else {
				$json['error'] = $this->language->get('error_unzip');
			}

			// Remove Zip
			unlink($file);

			$json['text'] = $this->language->get('text_move');
			$json['location'] = 'index.php?route=extension/enhancement/ea_ocmod_manager&user_token=' . $this->session->data['user_token'];

			$json['next'] = str_replace('&amp;', '&', $this->url->link('extension/enhancement/ea_ocmod_manager/moveMod', 'user_token=' . $this->session->data['user_token'] . '&extension_install_id=' . $extension_install_id));
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function moveMod() {		
		$language_data = $this->load->language('extension/enhancement/ea_ocmod_manager');
		foreach($language_data as $key=>$value) {
			$data[$key] = $value;
		}
		
		$json = array();

		if (isset($this->request->get['extension_install_id'])) {
			$extension_install_id = $this->request->get['extension_install_id'];
		} else {
			$extension_install_id = 0;
		}

		if (!$this->user->hasPermission('modify', 'marketplace/install')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!isset($this->session->data['install'])) {
			$json['error'] = $this->language->get('error_directory');
		} elseif (!is_dir(DIR_STORAGE . 'marketplace/' . 'tmp-' . $this->session->data['install'] . '/')) {
			$json['error'] = $this->language->get('error_directory');
		}

		if (!$json) {
			$directory = DIR_STORAGE . 'marketplace/tmp-' . $this->session->data['install'] . '/';
		
			if (is_dir($directory . 'upload/')) {
				$files = array();
	
				// Get a list of files ready to upload
				$path = array($directory . 'upload/*');
	
				while (count($path) != 0) {
					$next = array_shift($path);
	
					foreach ((array)glob($next) as $file) {
						if (is_dir($file)) {
							$path[] = $file . '/*';
						}
	
						$files[] = $file;
					}
				}
	
				// A list of allowed directories to be written to
				$allowed = array(
					'admin/controller/extension/',
					'admin/language/',
					'admin/model/extension/',
					'admin/view/image/',
					'admin/view/javascript/',
					'admin/view/stylesheet/',
					'admin/view/template/extension/',
					'catalog/controller/extension/',
					'catalog/language/',
					'catalog/model/extension/',
					'catalog/view/javascript/',
					'catalog/view/theme/',
					'system/config/',
					'system/library/',
					'image/catalog/',
					'image/payment/'
				);
	
				// First we need to do some checks
				foreach ($files as $file) {
					$destination = str_replace('\\', '/', substr($file, strlen($directory . 'upload/')));
	
					$safe = false;
	
					foreach ($allowed as $value) {
						if (strlen($destination) < strlen($value) && substr($value, 0, strlen($destination)) == $destination) {
							$safe = true;
	
							break;
						}
	
						if (strlen($destination) > strlen($value) && substr($destination, 0, strlen($value)) == $value) {
							$safe = true;
	
							break;
						}
					}
					
					if ($safe) {
						// Check if the copy location exists or not
						if (substr($destination, 0, 5) == 'admin') {
							$destination = DIR_APPLICATION . substr($destination, 6);
						}
	
						if (substr($destination, 0, 7) == 'catalog') {
							$destination = DIR_CATALOG . substr($destination, 8);
						}
	
						if (substr($destination, 0, 5) == 'image') {
							$destination = DIR_IMAGE . substr($destination, 6);
						}
	
						if (substr($destination, 0, 6) == 'system') {
							$destination = DIR_SYSTEM . substr($destination, 7);
						}
					} else {
						$json['error'] = sprintf($this->language->get('error_allowed'), $destination);
	
						break;
					}
				}
				
				if (!$json) {
					$this->load->model('extension/enhancement/ea_ocmod_manager');
	
					foreach ($files as $file) {
						$destination = str_replace('\\', '/', substr($file, strlen($directory . 'upload/')));
	
						$path = '';
	
						if (substr($destination, 0, 5) == 'admin') {
							$path = DIR_APPLICATION . substr($destination, 6);
						}
	
						if (substr($destination, 0, 7) == 'catalog') {
							$path = DIR_CATALOG . substr($destination, 8);
						}
	
						if (substr($destination, 0, 5) == 'image') {
							$path = DIR_IMAGE . substr($destination, 6);
						}
	
						if (substr($destination, 0, 6) == 'system') {
							$path = DIR_SYSTEM . substr($destination, 7);
						}
	
						if (is_dir($file) && !is_dir($path)) {
							if (mkdir($path, 0777)) {
								$this->model_extension_enhancement_ea_ocmod_manager->addExtensionPath($extension_install_id, $destination);
							}
						}
	
						if (is_file($file)) {
							if (rename($file, $path)) {
								$this->model_extension_enhancement_ea_ocmod_manager->addExtensionPath($extension_install_id, $destination);
							}
						}
					}
				}
			}
		}

		if (!$json) {
			$json['text'] = $this->language->get('text_xml');
			$json['location'] = 'index.php?route=extension/enhancement/ea_ocmod_manager&user_token=' . $this->session->data['user_token'];

			$json['next'] = str_replace('&amp;', '&', $this->url->link('extension/enhancement/ea_ocmod_manager/xmlMod', 'user_token=' . $this->session->data['user_token'] . '&extension_install_id=' . $extension_install_id));
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function xmlMod() {		
		$language_data = $this->load->language('extension/enhancement/ea_ocmod_manager');
		foreach($language_data as $key=>$value) {
			$data[$key] = $value;
		}

		$json = array();
		
		if (isset($this->request->get['extension_install_id'])) {
			$extension_install_id = $this->request->get['extension_install_id'];
		} else {
			$extension_install_id = 0;
		}

		if (!$this->user->hasPermission('modify', 'marketplace/install')) {
			$json['error'] = $this->language->get('error_permission_install');
		}

		if (!isset($this->session->data['install'])) {
			$json['error'] = $this->language->get('error_directory');
		} elseif (!is_dir(DIR_STORAGE . 'marketplace/' . 'tmp-' . $this->session->data['install'] . '/')) {
			$json['error'] = $this->language->get('error_directory');
		}

		if (!$json) {
			$file = DIR_STORAGE . 'marketplace/' . 'tmp-' . $this->session->data['install'] . '/install.xml';

			if (is_file($file)) {
				$this->load->model('extension/enhancement/ea_ocmod_manager');
				
				// If xml file just put it straight into the DB
				$xml = file_get_contents($file);
	
				if ($xml) {
					try {
						$dom = new DOMDocument('1.0', 'UTF-8');
						$dom->preserveWhiteSpace = false;
						$dom->loadXml($xml);
	
						$name = $dom->getElementsByTagName('name')->item(0);
	
						if ($name) {
							$name = $name->nodeValue;
						} else {
							$name = '';
						}
	
						$code = $dom->getElementsByTagName('code')->item(0);
	
						if ($code) {
							$code = $code->nodeValue;
	
							// Check to see if the modification is already installed or not.
							$modification_info = $this->model_extension_enhancement_ea_ocmod_manager->getModificationByCode($code);
	
							if ($modification_info) {
								$this->model_extension_enhancement_ea_ocmod_manager->deleteModification($modification_info['modification_id']);
								// Remove any emails
								$this->model_extension_enhancement_ea_ocmod_manager->deleteModificationEmail($modification_id);
								// Remove any backups
								$this->model_extension_enhancement_ea_ocmod_manager->deleteBackup($modification_id);
								// Remove any comments
								$this->model_extension_enhancement_ea_ocmod_manager->deleteModComments($modification_id);
								// Remove any meta
								$this->model_extension_enhancement_ea_ocmod_manager->deleteMeta($modification_id);
								// Remove any dev files
								if (file_exists(DIR_SYSTEM.$modification_info['file']) && $modification_info['type'] == "Development") {
									unlink(DIR_SYSTEM.$modification_info['file']);
								}
							}
						} else {
							$json['error'] = $this->language->get('error_code');
						}
	
						$author = $dom->getElementsByTagName('author')->item(0);
	
						if ($author) {
							$author = $author->nodeValue;
						} else {
							$author = '';
						}
	
						$version = $dom->getElementsByTagName('version')->item(0);
	
						if ($version) {
							$version = $version->nodeValue;
						} else {
							$version = '';
						}

						if ($dom->getElementsByTagName('link')->length) {
							$link = $dom->getElementsByTagName('link')->item(0)->textContent;
							$reg = '/(http|https):\/\/[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&amp;:\/~\+#]*[\w\-\@?^=%&amp;\/~\+#])/';
							preg_match_all($reg, $link, $matches, PREG_SET_ORDER, 0);				
							$cleanlink = $matches[0][0];
						} else {
							$cleanlink = '';
						}						
						
						if (!$json) {
							$modification_data = array(
								'extension_install_id' => $extension_install_id,
								'name'                 => $name,
								'code'                 => $code,
								'author'               => $author,
								'version'              => $version,
								'link'                 => $cleanlink,
								'xml'                  => $xml,
								'status'               => 0
							);
	
							$this->model_extension_enhancement_ea_ocmod_manager->addModification($modification_data);
						}
					} catch(Exception $exception) {
						$json['error'] = sprintf($this->language->get('error_exception'), $exception->getCode(), $exception->getMessage(), $exception->getFile(), $exception->getLine());
					}
				}
			}
		}

		if (!$json) {
			$json['text'] = $this->language->get('text_clear');
			$json['location'] = 'index.php?route=extension/enhancement/ea_ocmod_manager&user_token=' . $this->session->data['user_token'];

			$json['next'] = str_replace('&amp;', '&', $this->url->link('extension/enhancement/ea_ocmod_manager/clearMod', 'user_token=' . $this->session->data['user_token']));
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function clearMod() {		
		$language_data = $this->load->language('extension/enhancement/ea_ocmod_manager');
		foreach($language_data as $key=>$value) {
			$data[$key] = $value;
		}

		$json = array();

		if (!$this->user->hasPermission('modify', 'marketplace/install')) {
			$json['error'] = $this->language->get('error_permission_install');
		}

		if (!isset($this->session->data['install'])) {
			$json['error'] = $this->language->get('error_directory');
		}

		if (!$json) {
			$directory = DIR_STORAGE . 'marketplace/tmp-' . $this->session->data['install'] . '/';
			
			if (is_dir($directory)) {
				// Get a list of files ready to upload
				$files = array();
	
				$path = array($directory);
	
				while (count($path) != 0) {
					$next = array_shift($path);
	
					// We have to use scandir function because glob will not pick up dot files.
					foreach (array_diff(scandir($next), array('.', '..')) as $file) {
						$file = $next . '/' . $file;
	
						if (is_dir($file)) {
							$path[] = $file;
						}
	
						$files[] = $file;
					}
				}
	
				rsort($files);
	
				foreach ($files as $file) {
					if (is_file($file)) {
						unlink($file);
					} elseif (is_dir($file)) {
						rmdir($file);
					}
				}
	
				if (is_dir($directory)) {
					rmdir($directory);
				}
			}
			
			$file = DIR_STORAGE . 'marketplace/' . $this->session->data['install'] . '.tmp';
			
			if (is_file($file)) {
				unlink($file);
			}
							
			$json['success'] = $this->language->get('text_success_installed');
			$json['location'] = 'index.php?route=extension/enhancement/ea_ocmod_manager&user_token=' . $this->session->data['user_token'];
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function uninstallMod() {		
		$language_data = $this->load->language('extension/enhancement/ea_ocmod_manager');
		foreach($language_data as $key=>$value){
			$data[$key] = $value;
		}

		$json = array();

		$extension_install_id = $this->request->get['extension_install_id'];
		
		$modification_id = $this->request->get['modification_id'];
		
		$this->load->model('extension/enhancement/ea_ocmod_manager');

		$mod_info = $this->model_extension_enhancement_ea_ocmod_manager->getModification($modification_id);				
		
		if (!$this->user->hasPermission('modify', 'marketplace/install')) {
			$json['error'] = $this->language->get('error_permission_uninstall');
		}

		if (!$json) {		
			if ($mod_info['code'] == 'enhanced_mod_manager') {			
				$json['location'] = 'index.php?route=marketplace/modification/refresh&modification_id=' . (int)$modification_id . '&user_token=' . $this->session->data['user_token'];
			} else {
				$json['location'] = 'index.php?route=extension/enhancement/ea_ocmod_manager/refresh&modification_id=' . (int)$modification_id . '&user_token=' . $this->session->data['user_token'];
			}
			
			$results = $this->model_extension_enhancement_ea_ocmod_manager->getExtensionPathsByExtensionInstallId($extension_install_id);

			rsort($results);

			foreach ($results as $result) {
				$source = '';

				// Check if the copy location exists or not
				if (substr($result['path'], 0, 5) == 'admin') {
					$source = DIR_APPLICATION . substr($result['path'], 6);
				}

				if (substr($result['path'], 0, 7) == 'catalog') {
					$source = DIR_CATALOG . substr($result['path'], 8);
				}

				if (substr($result['path'], 0, 5) == 'image') {
					$source = DIR_IMAGE . substr($result['path'], 6);
				}
				
				if (substr($result['path'], 0, 14) == 'system/library') {
					$source = DIR_SYSTEM . 'library/' . substr($result['path'], 15);
				}

				if (is_file($source)) {
					unlink($source);
				} elseif (is_dir($source)) {
					$files = glob($source . '/*');

					if (!count($files)) {
						rmdir($source);
					}
				}

				$this->model_extension_enhancement_ea_ocmod_manager->deleteExtensionPath($result['extension_path_id']);
			}

			// Remove the install
			$this->model_extension_enhancement_ea_ocmod_manager->deleteExtensionInstall($extension_install_id);			
			// Remove any xml modifications
			$this->model_extension_enhancement_ea_ocmod_manager->deleteModificationsByExtensionInstallId($extension_install_id);			
			// Remove any emails
			$this->model_extension_enhancement_ea_ocmod_manager->deleteModificationEmail($modification_id);			
			// Remove any backups
			$this->model_extension_enhancement_ea_ocmod_manager->deleteBackup($modification_id);	
			// Remove any comments
			$this->model_extension_enhancement_ea_ocmod_manager->deleteModComments($modification_id);
			// Remove any meta
			$this->model_extension_enhancement_ea_ocmod_manager->deleteMeta($modification_id);
			// Remove any dev files
			if (file_exists(DIR_SYSTEM.$mod_info['file']) && $mod_info['type'] == "Development") {
				unlink(DIR_SYSTEM.$mod_info['file']);
			}
			$json['success'] = $this->language->get('text_success_uninstalled');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
