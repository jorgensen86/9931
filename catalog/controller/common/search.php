<?php
class ControllerCommonSearch extends Controller {
	public function index() {
		$this->load->language('common/search');

		$data['text_search'] = $this->language->get('text_search');

		if (isset($this->request->get['search'])) {
			$data['search'] = $this->request->get['search'];
		} else {
			$data['search'] = '';
		}

		if (isset($this->request->get['fs'])) {
			$data['search'] = $this->request->get['fs'];
		} else {
			$data['search'] = '';
		}
		

		/* Jorgensen - Replace Home Search Template */
		if(!$this->request->get['route'] || $this->request->get['route'] === 'common/home') {
			return $this->load->view('common/home_search', $data);
		}
		/* Jorgensen - Replace Home Search Template */

		return $this->load->view('common/search', $data);
	}
}