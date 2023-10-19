<?php
class ControllerExtensionEnhancementEaOcmodManagerFiles extends Controller {
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

		$this->document->setTitle($this->language->get('heading_title_files'));

  		$this->load->model('extension/enhancement/ea_ocmod_manager');
		
		$this->document->addStyle('view/template/extension/enhancement/ocmod_manager/js/jquery/jquery-confirm.css');
		$this->document->addScript('view/template/extension/enhancement/ocmod_manager/js/jquery/jquery-confirm.min.js');
		$this->document->addStyle('view/template/extension/enhancement/ocmod_manager/js/bootstrap/bootstrap-multiselect.css');		
		$this->document->addScript('view/template/extension/enhancement/ocmod_manager/js/bootstrap/bootstrap-multiselect.min.js');
		$this->document->addStyle('view/template/extension/enhancement/ocmod_manager/js/jquery/jquery.datetimepicker.css');		
		$this->document->addScript('view/template/extension/enhancement/ocmod_manager/js/jquery/jquery.datetimepicker.min.js');
		$this->document->addStyle('view/template/extension/enhancement/ocmod_manager/css/ea_ocmod_manager.css');
		
		$this->getList();		
	}	
	
	public function uploadPage() {
		$language_data = $this->load->language('extension/enhancement/ea_ocmod_manager');
		foreach($language_data as $key=>$value) {
			$data[$key] = $value;
		}

		$data['user_token'] = $this->session->data['user_token'];
		
		$this->response->setOutput($this->load->view('extension/enhancement/ocmod_manager/ea_ocmod_manager_uploader', $data));
	}

	public function delete() {
		$language_data = $this->load->language('extension/enhancement/ea_ocmod_manager');
		foreach($language_data as $key=>$value){
			$data[$key] = $value;
		}

		$this->load->model('extension/enhancement/ea_ocmod_manager');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && !empty($this->request->post['selected']) && $this->validate()) {
			foreach ($this->request->post['selected'] as $extension_path_id) {	
				$path_info = $this->model_extension_enhancement_ea_ocmod_manager->getExtensionPath($extension_path_id);
				if (file_exists($this->base_dir . $path_info['path'])) {
					@unlink($this->base_dir . $path_info['path']);
				}
				
				$this->model_extension_enhancement_ea_ocmod_manager->deleteExtensionPath($extension_path_id);
			}

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

			if (isset($this->request->get['filter_path'])) {
				$url .= '&filter_path=' . urlencode(html_entity_decode($this->request->get['filter_path'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_date'])) {
				$url .= '&filter_date=' . urlencode(html_entity_decode($this->request->get['filter_date'], ENT_QUOTES, 'UTF-8'));
			}

			if (isset($this->request->get['filter_shared'])) {
				$url .= '&filter_shared=true';
			}

			if (isset($this->request->get['page_limit'])) {
				$url .= '&page_limit=' . $this->request->get['page_limit'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->session->data['success'] = $this->language->get('text_success_file_deleted');
			
			$this->response->redirect($this->url->link('extension/enhancement/ea_ocmod_manager_files', 'user_token=' . $this->session->data['user_token'] . $url));
		}

		$this->getList();
	}

	protected function getList() {
		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = null;
		}
		
		if (isset($this->request->get['filter_path'])) {
			$filter_path = $this->request->get['filter_path'];
		} else {
			$filter_path = null;
		}
		
		if (isset($this->request->get['filter_date'])) {
			$filter_date = $this->request->get['filter_date'];
		} else {
			$filter_date = null;
		}
		
		if (isset($this->request->get['filter_shared'])) {
			$filter_shared = true;
		} else {
			$filter_shared = null;
		}
		
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'name';
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
		
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_path'])) {
			$url .= '&filter_path=' . urlencode(html_entity_decode($this->request->get['filter_path'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_date'])) {
			$url .= '&filter_date=' . urlencode(html_entity_decode($this->request->get['filter_date'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_shared'])) {
			$url .= '&filter_shared=true';
		}

		if (isset($this->request->get['page_limit'])) {
			$url .= '&page_limit=' . (int)$this->request->get['page_limit'];
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

		if (isset($this->request->get['page_limit']) && $this->request->get['page_limit'] >0) {
			$this->model_extension_enhancement_ea_ocmod_manager->editSettingValue('oc_editor', 'oc_editor_page_limit', $this->request->get['page_limit']);
			$page_limit = (int)$this->request->get['page_limit'];
		} else if ($this->config->get('oc_editor_page_limit')) {
			$page_limit = (int)$this->config->get('oc_editor_page_limit');
		} else {
			$page_limit = 20;
		}
		
		$data['per_page'] = $page_limit;
		
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title_files'),
			'href' => $this->url->link('extension/enhancement/ea_ocmod_manager_files', 'user_token=' . $this->session->data['user_token'])
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

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$filter_data = array(
			'filter_name'	=> $filter_name,
			'filter_path'	=> $filter_path,
			'filter_date'	=> $filter_date,
			'filter_shared'	=> $filter_shared,
			'sort'  		=> $sort,
			'order' 		=> $order,
			'start' 		=> ($page - 1) * $page_limit,
			'limit' 		=> $page_limit
		);

		$files_total = $this->model_extension_enhancement_ea_ocmod_manager->getTotalModificationFiles($filter_data);

		$results = $this->model_extension_enhancement_ea_ocmod_manager->getModificationFiles($filter_data);
		
		$shared_count = 0;
		
		$query = $this->model_extension_enhancement_ea_ocmod_manager->getModificationFilesShared();
		
		if($this->db->countAffected($query) > 1) {
			$shared_count = count($query);
		}
		
		$data['shared_count'] = (int)$shared_count;
		
		$data['files'] = array();
		
		foreach ($results as $result) {	
			$is_shared = $this->db->query("SELECT `path` FROM `" . DB_PREFIX . "extension_path` WHERE `path` = '" . $this->db->escape($result['path']) . "'");
	
			if($this->db->countAffected($is_shared) > 1) {
				$shared = true;
			} else {
				$shared = false;
			}
			
			$data['files'][] = array(
				'type' 			=> $result['type'],
				'path_id' 		=> $result['extension_path_id'],
				'install_id' 	=> $result['install_id'],
				'file_name'     => $result['name'],
				'file_file'     => $result['filename'],
				'file_code'     => $result['code'],
				'file_path'     => $result['path'],
				'date_added'    => date($this->language->get('datetime_format'), strtotime($result['date_added'])),
				'file_shared'   => $shared
			);
		}

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$url = '';

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}
		
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_path'])) {
			$url .= '&filter_path=' . urlencode(html_entity_decode($this->request->get['filter_path'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_date'])) {
			$url .= '&filter_date=' . urlencode(html_entity_decode($this->request->get['filter_date'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_shared'])) {
			$url .= '&filter_shared=true';
		}

		if (isset($this->request->get['page_limit'])) {
			$url .= '&page_limit=' . (int)$this->request->get['page_limit'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_id'] = $this->url->link('extension/enhancement/ea_ocmod_manager_files', 'user_token=' . $this->session->data['user_token'] . '&sort=install_id' . $url);
		$data['sort_name'] = $this->url->link('extension/enhancement/ea_ocmod_manager_files', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url);
		$data['sort_path'] = $this->url->link('extension/enhancement/ea_ocmod_manager_files', 'user_token=' . $this->session->data['user_token'] . '&sort=ep.path' . $url);
		$data['sort_date'] = $this->url->link('extension/enhancement/ea_ocmod_manager_files', 'user_token=' . $this->session->data['user_token'] . '&sort=ep.date_added' . $url);

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

		if (isset($this->request->get['filter_path'])) {
			$url .= '&filter_path=' . urlencode(html_entity_decode($this->request->get['filter_path'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_date'])) {
			$url .= '&filter_date=' . urlencode(html_entity_decode($this->request->get['filter_date'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_shared'])) {
			$url .= '&filter_shared=true';
		}

		if (isset($this->request->get['page_limit'])) {
			$url .= '&page_limit=' . $this->request->get['page_limit'];
		}

		$pagination = new Pagination();
		$pagination->total = (int)$files_total;
		$pagination->page = $page;
		$pagination->limit = $page_limit;
		$pagination->url = $this->url->link('extension/enhancement/ea_ocmod_manager_files', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}');

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($files_total) ? (($page - 1) * $page_limit) + 1 : 0, ((($page - 1) * $page_limit) > ($files_total - $page_limit)) ? $files_total : ((($page - 1) * $page_limit) + $page_limit), $files_total, ceil($files_total / $page_limit));

		$data['filter_name'] = $filter_name;
		$data['filter_path'] = $filter_path;
		$data['filter_date'] = $filter_date;
		$data['filter_shared'] = $filter_shared;
		$data['sort'] = $sort;
		$data['order'] = $order;
		
		if ($this->user->hasPermission('modify', 'marketplace/installer')) {
			$data['has_perm_install'] = true;
		} else {
			$data['has_perm_install'] = false;
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

		$data['paths'] = array(
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
		
		$data['action'] = $this->url->link('extension/enhancement/ea_ocmod_manager_files', 'user_token=' . $this->session->data['user_token'] . $url);
		$data['cancel'] = $this->url->link('extension/enhancement/ea_ocmod_manager', 'user_token=' . $this->session->data['user_token']);
		$data['reset_url'] = $this->url->link('extension/enhancement/ea_ocmod_manager_files', 'user_token=' . $this->session->data['user_token']);
		$data['delete'] = $this->url->link('extension/enhancement/ea_ocmod_manager_files/delete', 'user_token=' . $this->session->data['user_token'] . $url);
		
		$data['storage'] = strstr(DIR_STORAGE, 'storage/');

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/enhancement/ocmod_manager/ea_ocmod_manager_files', $data));
	}

	public function autocomplete() {
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
				//if($result['type'] == 'Installed') {
					$json[] = array(
						'name'       			=> strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
						'extension_install_id' 	=> $result['extension_install_id']
					);
				//}
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
	
	public function uploadModFiles() {		
		$language_data = $this->load->language('extension/enhancement/ea_ocmod_manager');
		foreach($language_data as $key=>$value) {
			$data[$key] = $value;
		}
		
		$this->load->model('extension/enhancement/ea_ocmod_manager');

		$json = array();
		
		$extension_install_id = '';
		
		if (!isset($this->request->get['extension_install_id']) || empty($this->request->get['extension_install_id'])) {
			$json['error'] = $this->language->get('error_modification_id');
		} else {
			$extension_install_id = $this->request->get['extension_install_id'];
			$result = $this->model_extension_enhancement_ea_ocmod_manager->getModificationByExtensionInstallId($extension_install_id);
			if (!$result) {
				$json['error'] = $this->language->get('error_modification');
			}			
		}

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
			if ($this->request->files['file']['name'] != 'upload.zip') {
				$json['error'] = $this->language->get('error_filename');
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
				$json['text'] = $this->language->get('text_install');
				$json['next'] = str_replace('&amp;', '&', $this->url->link('extension/enhancement/ea_ocmod_manager_files/installMod', 'user_token=' . $this->session->data['user_token'] . '&extension_install_id=' . $extension_install_id));
				$json['location'] = 'index.php?route=extension/enhancement/ea_ocmod_manager_files&user_token=' . $this->session->data['user_token'];
			} else {
				$json['error'] = $this->language->get('error_file');
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function installMod() {		
		$language_data = $this->load->language('extension/enhancement/ea_ocmod_manager');
		foreach($language_data as $key=>$value) {
			$data[$key] = $value;
		}

		$json = array();
			
		$extension_install_id = $this->request->get['extension_install_id'];
			
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
			$json['next'] = str_replace('&amp;', '&', $this->url->link('extension/enhancement/ea_ocmod_manager_files/unzipMod', 'user_token=' . $this->session->data['user_token'] . '&extension_install_id=' . $extension_install_id));
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

		$extension_install_id = $this->request->get['extension_install_id'];
		
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
			$json['next'] = str_replace('&amp;', '&', $this->url->link('extension/enhancement/ea_ocmod_manager_files/moveMod', 'user_token=' . $this->session->data['user_token'] . '&extension_install_id=' . $extension_install_id));
			$json['location'] = 'index.php?route=extension/enhancement/ea_ocmod_manager_files&user_token=' . $this->session->data['user_token'];
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

		$extension_install_id = $this->request->get['extension_install_id'];

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
					$results = $this->model_extension_enhancement_ea_ocmod_manager->getExtensionPathsByExtensionInstallId($extension_install_id);
	
					foreach ($files as $file) {
						$destination = str_replace('\\', '/', substr($file, strlen($directory . 'upload/')));
						
						foreach ($results as $result) {
							if ($result['path'] !== $destination) {
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
			}
		}

		if (!$json) {
			$json['text'] = $this->language->get('text_clear');
			$json['next'] = str_replace('&amp;', '&', $this->url->link('extension/enhancement/ea_ocmod_manager_files/clearMod', 'user_token=' . $this->session->data['user_token']));
			$json['location'] = 'index.php?route=extension/enhancement/ea_ocmod_manager_files&user_token=' . $this->session->data['user_token'];
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
							
			$json['success'] = $this->language->get('text_success_uploaded');
			$json['location'] = 'index.php?route=extension/enhancement/ea_ocmod_manager_files&user_token=' . $this->session->data['user_token'];
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/enhancement/ea_ocmod_manager_files')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

// ****************************************************************************************************** //	
// *******************************************   Debug to Logfile  ************************************** //	
// ****************************************************************************************************** //		
	private function debug($data) {
    	$this->log->debug($data);
	}
}
