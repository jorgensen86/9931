<?php
class ModelCatalogAppliance extends Model {
	public function getAppliance($appliance_id) {
		return $this->db->query("SELECT DISTINCT a.*, m.name AS manufacturer, (SELECT GROUP_CONCAT(ct.name, ':', ac.value SEPARATOR ' ') FROM " . DB_PREFIX . "appliance_codes ac LEFT JOIN " . DB_PREFIX . "code_type ct ON (ac.code_id = ct.code_id) WHERE ac.appliance_id = a.appliance_id) as extra_codes FROM " . DB_PREFIX . "appliance a LEFT JOIN " . DB_PREFIX . "manufacturer m ON (a.manufacturer_id = m.manufacturer_id) WHERE a.appliance_id = '" . (int)$appliance_id . "' AND a.status = '1'")->row;
	}

	public function getCategoryFullPath($category_id) {
		return $this->db->query("SELECT path_id FROM " . DB_PREFIX . "category_path WHERE category_id = '" . (int)$category_id . "' ORDER BY level ASC")->rows;
	}

	public function getAppliances() {
		$sql = "SELECT DISTINCT a.*, m.name AS manufacturer FROM " . DB_PREFIX . "appliance a LEFT JOIN " . DB_PREFIX . "manufacturer m ON (a.manufacturer_id = m.manufacturer_id) WHERE a.status = '1'";

		// if(!empty($data['filter_name'])) {
		// 	$sql .= " AND "
		// }

		return $this->db->query($sql)->rows;
	}

	public function getCategoryTree($category_id) {
		return $this->db->query("SELECT c.category_id,cd.name FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "category c on c.category_id = cp.path_id LEFT JOIN " . DB_PREFIX . "category_description cd ON c.category_id = cd.category_id WHERE c.status = '1' AND cd.language_id = '" . $this->config->get('config_language_id') . "' AND cp.category_id = '" . (int)$category_id . "' ORDER BY cp.level ASC")->rows;
	}

	public function getExtraCodes($appliance_id) {
		return $this->db->query("SELECT * FROM " . DB_PREFIX . "appliance_codes ac LEFT JOIN " . DB_PREFIX . "code_type ct ON (ac.code_id = ct.code_id) WHERE ac.appliance_id = '" . (int)$appliance_id . "' ORDER BY ct.sort_order, name")->rows;
	}
}
