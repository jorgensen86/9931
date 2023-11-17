<?php

class ModelSupplierSupplier extends Model {

	public function addSupplier($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "supplier SET company_name = '" . $this->db->escape($data['company_name']) . "', vat_number = '" . $this->db->escape($data['vat_number']) . "', email = '" . $this->db->escape($data['email']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', fax = '" . $this->db->escape($data['fax']) . "', profession = '" . $this->db->escape($data['profession']) . "', tax_office = '" . $this->db->escape($data['tax_office']) . "', address = '" . $this->db->escape($data['address']) . "', postcode = '" . $this->db->escape($data['postcode']) . "', city = '" . $this->db->escape($data['city']) . "', country_id = '" . (int)$data['country_id'] . "', date_added = NOW()");

		return $this->db->getLastId();
	}

	public function editSupplier($supplier_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "supplier SET company_name = '" . $this->db->escape($data['company_name']) . "', vat_number = '" . $this->db->escape($data['vat_number']) . "', email = '" . $this->db->escape($data['email']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', fax = '" . $this->db->escape($data['fax']) . "', profession = '" . $this->db->escape($data['profession']) . "', tax_office = '" . $this->db->escape($data['tax_office']) . "', address = '" . $this->db->escape($data['address']) . "', postcode = '" . $this->db->escape($data['postcode']) . "', city = '" . $this->db->escape($data['city']) . "', country_id = '" . (int)$data['country_id'] . "' WHERE supplier_id = '" . (int)$supplier_id . "'");
	}

	public function deleteSupplier($supplier_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "supplier WHERE supplier_id = '" . (int)$supplier_id . "'");
	}

	public function getSupplier($supplier_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "supplier WHERE supplier_id = '" . (int)$supplier_id . "'");

		return $query->row;
	}

	public function getSuppliers($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "supplier";
		
		$implode = array();

		if (!empty($data['filter_company_name'])) {
			$implode[] = "company_name LIKE '" . $this->db->escape($data['filter_company_name']) . "%'";
		}

		if (!empty($data['filter_email'])) {
			$implode[] = "email LIKE '" . $this->db->escape($data['filter_email']) . "%'";
		}

		if (!empty($data['filter_telephone'])) {
			$implode[] = "telephone LIKE '" . $this->db->escape($data['filter_telephone']) . "%'";
		}
        
		if (!empty($data['filter_vat_number'])) {
			$implode[] = "vat_number LIKE '" . $this->db->escape($data['filter_vat_number']) . "%'";
		}

		if (!empty($data['filter_date_added'])) {
			$implode[] = "DATE(date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		$sort_data = array(
			'company_name',
			'email',
			'telephone',
			'vat_number',
			'date_added'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY company_name";
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

	public function getTotalSuppliers($data = array()) {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "supplier";

		$implode = array();

		if (!empty($data['filter_company_name'])) {
			$implode[] = "company_name LIKE '" . $this->db->escape($data['filter_company_name']) . "%'";
		}

		if (!empty($data['filter_email'])) {
			$implode[] = "email LIKE '" . $this->db->escape($data['filter_email']) . "%'";
		}

		if (!empty($data['filter_telephone'])) {
			$implode[] = "telephone LIKE '" . $this->db->escape($data['filter_telephone']) . "%'";
		}
        
		if (!empty($data['filter_vat_number'])) {
			$implode[] = "vat_number LIKE '" . $this->db->escape($data['filter_vat_number']) . "%'";
		}

		if (!empty($data['filter_date_added'])) {
			$implode[] = "DATE(date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
		}

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getSupplierProducts($supplier_id, $data = array()) {

		$sql = "SELECT *, (SELECT SUM(quantity) FROM " . DB_PREFIX . "order_product op LEFT JOIN " . DB_PREFIX . "order o ON (op.order_id = o.order_id ) WHERE o.order_status_id > 0 AND o.preorder != 1 AND op.product_id = p.product_id GROUP BY op.product_id ) as purchased FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.supplier_id = '" . (int)$supplier_id . "'";

		if(!empty($data['filter_alert'])) {
			$sql .= " AND p.quantity <= p.quantity_alert ";
		}

		$sql .= " ORDER BY pd.name ASC, p.sort_order ASC ";

		return $this->db->query($sql)->rows;
	}
}