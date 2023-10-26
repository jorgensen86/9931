<?php
class ModelCatalogAppliance extends Model
{
	public function addAppliance($data)
	{
		$this->db->query("INSERT INTO " . DB_PREFIX . "appliance SET `code` = '" . $this->db->escape($data['code']) . "', manufacturer_id = '" . (int)$data['manufacturer_id'] . "', category_id = '" . (int)$data['category_id'] . "', status = '" . (int)$data['status'] . "', sort_order = '" . (int)$data['sort_order'] . "', date_added = NOW(), date_modified = NOW()");

		$appliance_id = $this->db->getLastId();

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "appliance SET image = '" . $this->db->escape($data['image']) . "' WHERE appliance_id = '" . (int)$appliance_id . "'");
		}

		if(isset($data['appliance_codes'])) {
			foreach ($data['appliance_codes'] as $key => $value) {
				if(trim(($value))) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "appliance_codes SET appliance_id = '" . $appliance_id . "', code_id = '" . (int)$key . "', value = '" . $this->db->escape($value) . "'");
				}
			}
		}

		return $appliance_id;
	}

	public function editAppliance($appliance_id, $data)
	{
		$this->db->query("UPDATE " . DB_PREFIX . "appliance SET code = '" . $this->db->escape($data['code']) . "', manufacturer_id = '" . (int)$data['manufacturer_id'] . "', category_id = '" . (int)$data['category_id'] . "', status = '" . (int)$data['status'] . "', sort_order = '" . (int)$data['sort_order'] . "', date_modified = NOW() WHERE appliance_id = '" . (int)$appliance_id . "'");

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "appliance SET image = '" . $this->db->escape($data['image']) . "' WHERE appliance_id = '" . (int)$appliance_id . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "appliance_codes WHERE appliance_id = '" . (int)$appliance_id . "'");

		if(isset($data['appliance_codes'])) {
			foreach ($data['appliance_codes'] as $key => $value) {
				if(trim(($value))) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "appliance_codes SET appliance_id = '" . $appliance_id . "', code_id = '" . (int)$key . "', value = '" . $this->db->escape($value) . "'");
				}
			}
		}
	}

	public function copyAppliance($appliance_id)
	{
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "appliance p WHERE p.appliance_id = '" . (int)$appliance_id . "'");

		if ($query->num_rows) {
			$data = $query->row;

			$data['status'] = '0';

			$this->addAppliance($data);
		}
	}

	public function deleteAppliance($appliance_id)
	{
		$this->db->query("DELETE FROM " . DB_PREFIX . "appliance WHERE appliance_id = '" . (int)$appliance_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "appliance_codes WHERE appliance_id = '" . (int)$appliance_id . "'");
	}

	public function getAppliance($appliance_id)
	{
		$query = $this->db->query("SELECT DISTINCT *, (SELECT name FROM " . DB_PREFIX . "manufacturer m WHERE m.manufacturer_id = a.manufacturer_id ) as manufacturer FROM " . DB_PREFIX . "appliance a WHERE appliance_id = '" . (int)$appliance_id . "'");

		return $query->row;
	}

	public function getAppliances($data = array())
	{
		$sql = "SELECT a.*, m.name as manufacturer, (SELECT cd.name FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) WHERE cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c.category_id = a.category_id) as category FROM " . DB_PREFIX . "appliance a LEFT JOIN " . DB_PREFIX . "manufacturer m ON (a.manufacturer_id = m.manufacturer_id) WHERE 1";

		
		// $sql = "SELECT a.*, m.name as manufacturer, cd.name as category FROM " . DB_PREFIX . "appliance a LEFT JOIN " . DB_PREFIX . "category c ON (a.category_id = c.category_id) LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) LEFT JOIN " . DB_PREFIX . "manufacturer m ON (a.manufacturer_id = m.manufacturer_id) WHERE cd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_code'])) {
			$sql .= " AND a.code LIKE '" . $this->db->escape($data['filter_code']) . "%'";
		}

		if (!empty($data['filter_manufacturer'])) {
			$sql .= " AND m.name LIKE '" . $this->db->escape($data['filter_manufacturer']) . "'";
		}

		if (!empty($data['filter_category'])) {
			$sql .= " AND a.category_id = '" . (int)$data['filter_category'] . "'";
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$sql .= " AND a.status = '" . (int)$data['filter_status'] . "'";
		}

		$sql .= " GROUP BY a.appliance_id";

		$sort_data = array(
			'a.code',
			'manufacturer',
			'category',
			'a.status',
			'a.sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY a.code";
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

	public function getProductsByCategoryId($category_id)
	{
		$query = $this->db->query("SELECT *, FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p2c.category_id = '" . (int)$category_id . "' ORDER BY pd.name ASC");

		return $query->rows;
	}

	public function getTotalAppliances($data = array())
	{

		$sql = "SELECT COUNT(DISTINCT a.appliance_id) AS total FROM " . DB_PREFIX . "appliance a LEFT JOIN " . DB_PREFIX . "category c ON (a.category_id = c.category_id) LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) LEFT JOIN " . DB_PREFIX . "manufacturer m ON (a.manufacturer_id = m.manufacturer_id) WHERE cd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_code'])) {
			$sql .= " AND a.code LIKE '" . $this->db->escape($data['filter_code']) . "%'";
		}

		if (!empty($data['filter_category'])) {
			$sql .= " AND a.category_id = '" . (int)$data['filter_category'] . "'";
		}

		if (!empty($data['filter_manufacturer'])) {
			$sql .= " AND m.name LIKE '" . $this->db->escape($data['filter_manufacturer']) . "'";
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$sql .= " AND a.status = '" . (int)$data['filter_status'] . "'";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getApplianceCodes($appliance_id) {
		$query = $this->db->query("SELECT ac.*, ct.name FROM " . DB_PREFIX . "appliance_codes ac LEFT JOIN " . DB_PREFIX . "code_type ct ON (ac.code_id = ct.code_id) WHERE ac.appliance_id = '" . (int)$appliance_id  . "' ORDER BY ct.sort_order ASC");

		return $query->rows;
	}

	public function getTotalProductsByManufacturerId($manufacturer_id)
	{
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

		return $query->row['total'];
	}
}
