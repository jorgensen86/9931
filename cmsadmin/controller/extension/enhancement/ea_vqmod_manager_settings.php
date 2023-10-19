<?php
class ControllerExtensionEnhancementEaVqmodManagerSettings extends Controller {
	private $error = array();	

	public function index() {		
		$language_data = $this->load->language('extension/enhancement/ea_vqmod_manager');
		foreach($language_data as $key=>$value){
			$data[$key] = $value;
		}

		$this->document->setTitle($this->language->get('heading_settings'));
		
		$this->document->addStyle('view/template/extension/enhancement/vqmod_manager/js/jquery/jquery-confirm.css');
		$this->document->addScript('view/template/extension/enhancement/vqmod_manager/js/jquery/jquery-confirm.min.js');
		$this->document->addStyle('view/template/extension/enhancement/vqmod_manager/css/bootstrap-radio.css');
		$this->document->addStyle('view/template/extension/enhancement/vqmod_manager/css/ea_vqmod_manager.css');
		
		
  		$this->load->model('setting/setting');		
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('vq_editor', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success_settings');

			$this->response->redirect($this->url->link('extension/enhancement/ea_vqmod_manager', 'user_token=' . $this->session->data['user_token']));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_settings'),
			'href' => $this->url->link('extension/enhancement/ea_vqmod_manager_settings', 'user_token=' . $this->session->data['user_token'])
		);

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['update_settings'] = $this->url->link('extension/enhancement/ea_vqmod_manager_settings', 'user_token=' . $this->session->data['user_token']);
		
		$data['update_permissions'] = $this->url->link('extension/enhancement/ea_vqmod_manager_settings/userPermissions', 'user_token=' . $this->session->data['user_token']);

		$data['cancel'] = $this->url->link('extension/enhancement/ea_vqmod_manager', 'user_token=' . $this->session->data['user_token']);

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->post['vq_editor_edit'])) {
			$data['vq_editor_edit'] = $this->request->post['vq_editor_edit'];
		} else {
			$data['vq_editor_edit'] = $this->config->get('vq_editor_edit');
		}

		if (isset($this->request->post['vq_editor_delete'])) {
			$data['vq_editor_delete'] = $this->request->post['vq_editor_delete'];
		} else {
			$data['vq_editor_delete'] = $this->config->get('vq_editor_delete');
		}

		if (isset($this->request->post['vq_editor_upload'])) {
			$data['vq_editor_upload'] = $this->request->post['vq_editor_upload'];
		} else {
			$data['vq_editor_upload'] = $this->config->get('vq_editor_upload');
		}

		if (isset($this->request->post['vq_editor_download'])) {
			$data['vq_editor_download'] = $this->request->post['vq_editor_download'];
		} else {
			$data['vq_editor_download'] = $this->config->get('vq_editor_download');
		}
		
		$data['screen_modes'] = array(1,2);
		
		if (isset($this->request->post['vq_editor_screen_mode'])) {
			$data['vq_editor_screen_mode'] = $this->request->post['vq_editor_screen_mode'];
		} elseif ($this->config->get('vq_editor_screen_mode')) {
			$data['vq_editor_screen_mode'] = $this->config->get('vq_editor_screen_mode');
		} else {
			$data['vq_editor_screen_mode'] = 1;
		}
		
		$data['themes'] = array();
		$ignore_theme = array('theme-cobalt');
		$acethemes = glob(DIR_APPLICATION . 'view/template/extension/enhancement/vqmod_manager/js/ace/theme-*');
		foreach ($acethemes as $acetheme) {
			$theme = basename($acetheme);
			$theme = str_replace(".js","",$theme);
			if (!in_array($theme, $ignore_theme)) { $data['themes'][] = $theme ; }	
		}	
		
		if (isset($this->request->post['vq_editor_theme'])) {
			$data['vq_editor_theme'] = $this->request->post['vq_editor_theme'];
		} elseif ($this->config->get('vq_editor_theme')) {
			$data['vq_editor_theme'] = $this->config->get('vq_editor_theme');
		} else {
			$data['vq_editor_theme'] = 'theme-cobalt';
		}
		
		$this->load->model('extension/enhancement/ea_vqmod_manager');
		
		$data['users'] = array();
		$results = $this->model_extension_enhancement_ea_vqmod_manager->getUsers();
		foreach ($results as $result) {
			$data['users'][] = array(
				'user_id'    => $result['user_id'],
				'username'   => $result['username']
			);
		}		

		if (isset($this->request->post['vq_editor_perm_edit'])) {
			$data['vq_editor_perm_edit'] = $this->request->post['vq_editor_perm_edit'];
		} elseif ($this->config->get('vq_editor_perm_edit')) {
			$data['vq_editor_perm_edit'] = $this->config->get('vq_editor_perm_edit');
		} else {
			$data['vq_editor_perm_edit'] = array();
		}

		if (isset($this->request->post['vq_editor_perm_upload'])) {
			$data['vq_editor_perm_upload'] = $this->request->post['vq_editor_perm_upload'];
		} elseif ($this->config->get('vq_editor_perm_upload')) {
			$data['vq_editor_perm_upload'] = $this->config->get('vq_editor_perm_upload');
		} else {
			$data['vq_editor_perm_upload'] = array();
		}		

		if (isset($this->request->post['vq_editor_perm_download'])) {
			$data['vq_editor_perm_download'] = $this->request->post['vq_editor_perm_download'];
		} elseif ($this->config->get('vq_editor_perm_download')) {
			$data['vq_editor_perm_download'] = $this->config->get('vq_editor_perm_download');
		} else {
			$data['vq_editor_perm_download'] = array();
		}		

		if (isset($this->request->post['vq_editor_perm_delete'])) {
			$data['vq_editor_perm_delete'] = $this->request->post['vq_editor_perm_delete'];
		} elseif ($this->config->get('vq_editor_perm_delete')) {
			$data['vq_editor_perm_delete'] = $this->config->get('vq_editor_perm_delete');
		} else {
			$data['vq_editor_perm_delete'] = array();
		}

		if ($this->config->get('vq_editor_theme')) {
			$data['themejs'] = $this->config->get('vq_editor_theme');
			$data['themename'] = str_replace("theme-","",$data['themejs']);
		} else {
			$data['themejs'] = 'theme-cobalt';
			$data['themename'] = 'cobalt';
		}
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/enhancement/vqmod_manager/ea_vqmod_manager_settings', $data));
	}
	
	public function userPermissions() {		
		$language_data = $this->load->language('extension/enhancement/ea_vqmod_manager');
		foreach($language_data as $key=>$value){
			$data[$key] = $value;
		}
		
		$this->load->model('setting/setting');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('vq_editor_perm', $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success_permissions');
			$this->response->redirect($this->url->link('extension/enhancement/ea_vqmod_manager_settings', 'user_token=' . $this->session->data['user_token']));
		} else {
			$this->session->data['error'] = $this->language->get('error_permission_settings');
			$this->response->redirect($this->url->link('extension/enhancement/ea_vqmod_manager_settings', 'user_token=' . $this->session->data['user_token']));
		}	
	}
	
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/enhancement/ea_vqmod_manager_settings')) {
			$this->error['warning'] = $this->language->get('error_permission_settings');
		}

		return !$this->error;
	}
}
