<?php
class ModelCatalogCodeType extends Model {
	public function addCodeType($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "code_type SET name = '" . $this->db->escape($data['name']) . "', bold = '" . (int)$data['bold'] . "', sort_order = '" . (int)$data['sort_order'] . "'");
        
		return $this->db->getLastId();
	}

	public function editCodeType($code_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "code_type SET name = '" . $this->db->escape($data['name']) . "', bold = '" . (int)$data['bold'] . "', sort_order = '" . (int)$data['sort_order'] . "' WHERE code_id = '" . (int)$code_id . "'");
	}

	public function deleteCodeType($code_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "code_type WHERE code_id = '" . (int)$code_id . "'");
	}

	public function getCodeType($code_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "code_type WHERE code_id = '" . (int)$code_id . "'");

		return $query->row;
	}

	public function getCodeTypes($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "code_type";

		$sort_data = array(
			'name',
			'sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY name";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}


	public function getTotalCodeTypes() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "code_type");

		return $query->row['total'];
	}

    public function getTotalManufacturerByCodeId($code_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "manufacturer_to_code WHERE code_id = '" . (int)$code_id . "'");

		return $query->row['total'];
	}

}