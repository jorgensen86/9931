<?php
class ModelCatalogAppliance extends Model {
	public function getAppliance($appliance_id) {
		return $this->db->query("SELECT DISTINCT a.*, m.name AS manufacturer FROM " . DB_PREFIX . "appliance a LEFT JOIN " . DB_PREFIX . "manufacturer m ON (a.manufacturer_id = m.manufacturer_id) WHERE a.appliance_id = '" . (int)$appliance_id . "' AND a.status = '1'")->row;
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
}
