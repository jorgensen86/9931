<?php
class ModelExtensionEnhancementEaVqmodManager extends Model {
	
// ***************************************************************************************** //	
// ************************************  Install Stuff  ************************************ //
// ***************************************************************************************** //		
	public function installVqmodTable() {
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ea_vqmod_manager`");
		$this->db->query("CREATE TABLE `" . DB_PREFIX . "ea_vqmod_manager` (`vqmod_id` int(11) NOT NULL AUTO_INCREMENT, `name` varchar(255) COLLATE utf8_general_ci NOT NULL DEFAULT '', `xml_name` varchar(255) COLLATE utf8_general_ci NOT NULL DEFAULT '', `author` varchar(255) COLLATE utf8_general_ci NOT NULL, `version` varchar(5) COLLATE utf8_general_ci NOT NULL, `status` tinyint(1) NOT NULL, `date_added` datetime NOT NULL, `date_modified` datetime NOT NULL, `uid` int(11) NOT NULL, PRIMARY KEY (`vqmod_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
		return true;
	}	
	
	public function installVqmodBackup() {
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ea_vqmod_backup`");
		$this->db->query("CREATE TABLE `" . DB_PREFIX . "ea_vqmod_backup` (`vqmod_id` int(11) NOT NULL, `xml` mediumtext COLLATE utf8_general_ci NOT NULL, PRIMARY KEY (`vqmod_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");		
		return true;
	}	
	
	public function installVqmodEmail() {
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ea_vqmod_email`");
		$this->db->query("CREATE TABLE `" . DB_PREFIX . "ea_vqmod_email` (`vqmod_id` int(11) NOT NULL, `email` varchar(255) COLLATE utf8_general_ci NOT NULL, PRIMARY KEY (`vqmod_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");		
		return true;
	}		
	
	public function installVqmodComment() {
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ea_vqmod_comment`");
		$this->db->query("CREATE TABLE `" . DB_PREFIX . "ea_vqmod_comment` (`comment_id` int(11) NOT NULL AUTO_INCREMENT,`vqmod_id` int(11) NOT NULL, `comment` text COLLATE utf8_general_ci NOT NULL DEFAULT '', `date_added` datetime NOT NULL, PRIMARY KEY (`comment_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");		
		return true;
	}
	

// ***************************************************************************************** //	
// ***************************************  Add Stuff  ************************************* //
// ***************************************************************************************** //		
	public function addVqmod($data = array()) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "ea_vqmod_manager` SET `name` = '" . $this->db->escape((string)$data['name']) . "', `xml_name` = '" . $this->db->escape((string)$data['xml_name']) . "', `author` = '" . $this->db->escape((string)$data['author']) . "', `version` = '" . $this->db->escape((string)$data['version']) . "', `status` = '" . (int)$data['status'] . "', `date_added` = NOW(), `date_modified` = '1970-01-01 01:00:00', `uid` = '" . (int)$this->user->getId() . "'");		
	}	
	
	public function addVqmodEmail($vqmod_id,$email) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "ea_vqmod_email` SET `vqmod_id` = '" . (int)$vqmod_id . "', `email` = '" . $this->db->escape((string)$email) . "'");
	}	
	
	public function addVqmodBackup($vqmod_id,$xml) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "ea_vqmod_backup` SET `vqmod_id` = '" . (int)$vqmod_id . "', `xml` = '" . $this->db->escape((string)$xml) . "'");
	}		
	
	public function addVqmodComment($vqmod_id,$comment) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "ea_vqmod_comment` SET `vqmod_id` = '" . (int)$vqmod_id . "', `comment` = '" . $this->db->escape((string)$comment) . "', `date_added` = NOW()");
	}
	
// ***************************************************************************************** //	
// **************************************  Edit/Update  ************************************ //
// ***************************************************************************************** //	
	public function editVqmod($data = array()) {
		$this->db->query("UPDATE `" . DB_PREFIX . "ea_vqmod_manager` SET `name` = '" . $this->db->escape((string)$data['name']) . "', `xml_name` = '" . $this->db->escape((string)$data['xml_name']) . "', `author` = '" . $this->db->escape((string)$data['author']) . "', `version` = '" . $this->db->escape((string)$data['version']) . "', `status` = '" . (int)$data['status'] . "', `date_modified` = NOW(), `uid` = '" . (int)$this->user->getId() . "' WHERE `vqmod_id` = '" . (int)$data['vqmod_id'] . "'");	
	}
	
	public function editVqmodEmail($vqmod_id,$email) {
		$this->db->query("UPDATE `" . DB_PREFIX . "ea_vqmod_email` SET `email` = '" . $this->db->escape((string)$email) . "' WHERE `vqmod_id` = '" . (int)$vqmod_id . "'");
	}
	
	public function updateModified($vqmod_id) {
		$this->db->query("UPDATE `" . DB_PREFIX . "ea_vqmod_manager` SET `date_modified` = NOW(), `uid` = '" . (int)$this->user->getId() . "' WHERE `vqmod_id` = '" . (int)$vqmod_id . "'");
	}
	
	public function editSettingValue($code = '', $key = '', $value = '', $store_id = 0) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE `store_id` = '" . (int)$store_id . "' AND `code` = '" . $this->db->escape($code) . "' AND `key` = '" . $this->db->escape($key) . "'");
		if($query->num_rows) {
			$this->db->query("UPDATE `" . DB_PREFIX . "setting` SET `value` = '" . $this->db->escape($value) . "', serialized = '0' WHERE store_id = '" . (int)$store_id . "' AND `code` = '" . $this->db->escape($code) . "' AND `key` = '" . $this->db->escape($key) . "'");
		} else {
			$this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$store_id . "', `code` = '" . $this->db->escape($code) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape($value) . "'");
		}
		
	}	
	
// ***************************************************************************************** //	
// *************************************  Delete Stuff  ************************************ //
// ***************************************************************************************** //		
	public function deleteVqmod($vqmod_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "ea_vqmod_manager` WHERE `vqmod_id` = '" . (int)$vqmod_id . "'");
	}	
	
	public function deleteVqmodByName($xml_name) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "ea_vqmod_manager` WHERE `xml_name` = '" . $this->db->escape((string)$xml_name) . "'");
	}
	
	public function deleteVqmodBackup($vqmod_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "ea_vqmod_backup` WHERE `vqmod_id` = '" . (int)$vqmod_id . "'");
	}
	
	public function deleteVqmodEmail($vqmod_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "ea_vqmod_email` WHERE `vqmod_id` = '" . (int)$vqmod_id . "'");
	}
	
	public function deleteVqmodComment($comment_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "ea_vqmod_comment` WHERE `comment_id` = '" . (int)$comment_id . "'");
	}
	
	public function deleteVqmodComments($vqmod_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "ea_vqmod_comment` WHERE `vqmod_id` = '" . (int)$vqmod_id . "'");
	}
	
	
// ***************************************************************************************** //	
// ************************************  Enable/Disable  *********************************** //
// ***************************************************************************************** //		
	public function enableVqmod($vqmod_id,$xml_name) {
		$this->db->query("UPDATE `" . DB_PREFIX . "ea_vqmod_manager` SET `status` = '1', `xml_name` = '" . $this->db->escape((string)$xml_name) . "' WHERE `vqmod_id` = '" . (int)$vqmod_id . "'");
	}

	public function disableVqmod($vqmod_id,$xml_name) {
		$this->db->query("UPDATE `" . DB_PREFIX . "ea_vqmod_manager` SET `status` = '0', `xml_name` = '" . $this->db->escape((string)$xml_name) . "' WHERE `vqmod_id` = '" . (int)$vqmod_id . "'");
	}	

// ***************************************************************************************** //	
// **************************************  Get Stuff  ************************************** //
// ***************************************************************************************** //		
	public function getUsers() {
		$sql = "SELECT * FROM `" . DB_PREFIX . "user` WHERE status=1 ORDER BY username ASC";
		$query = $this->db->query($sql);
		return $query->rows;
	}
	
	public function getUserDetails($vqmod_id) {
		$uid = $this->db->query("SELECT `uid` FROM `" . DB_PREFIX . "ea_vqmod_manager` WHERE `vqmod_id` = '" . (int)$vqmod_id . "'");
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "user` WHERE user_id = '" . $uid->row['uid'] . "'");
		return $query->row;
	}
	
	public function getDateAdded($xml_name) {
		$query = $this->db->query("SELECT `date_added` FROM `" . DB_PREFIX . "ea_vqmod_manager` WHERE `xml_name` = '" . $this->db->escape((string)$xml_name) . "'");
		return $query->row;
	}
	
	public function getBackup($vqmod_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "ea_vqmod_backup` WHERE `vqmod_id` = '" . (int)$vqmod_id . "'");
		return $query->row;
	}
	
	public function getVqmodEmail($vqmod_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "ea_vqmod_email` WHERE `vqmod_id` = '" . (int)$vqmod_id . "'");
		return $query->row;
	}
	
	public function getVqmodByXmlName($xml_name) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "ea_vqmod_manager` WHERE `xml_name` = '" . $this->db->escape((string)$xml_name) . "'");
		return $query->row;
	}
	
	public function getXmlNames() {
		$query = $this->db->query("SELECT `xml_name` FROM `" . DB_PREFIX . "ea_vqmod_manager` WHERE 1");
		return $query->rows;
	}
	
	public function getVqmod($vqmod_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "ea_vqmod_manager` WHERE `vqmod_id` = '" . (int)$vqmod_id . "'");
		return $query->row;
	}
	
	public function getVqmods($data = array()) {
		$sql = "SELECT * FROM `" . DB_PREFIX . "ea_vqmod_manager`";

		$cond = array();

		if (!empty($data['filter_name'])) {
			$cond[] = " `name` LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_author'])) {
			$cond[] = " `author` LIKE '%" . $this->db->escape($data['filter_author']) . "%'";
		}

		if (isset($data['filter_status']) && strlen($data['filter_status'])) {
			$cond[] = " `status` = '" . (int)$data['filter_status'] . "'";
		}

		if ($cond) {
			$sql .= " WHERE " . implode(' AND ', $cond);
		}		
		
		$sort_data = array(
			'name',
			'author',
			'version',
			'status',
			'date_added',
			'date_modified'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY status";
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

	public function getTotalVqmods($data = array()) {
		$cond = array();

		if (!empty($data['filter_name'])) {
			$cond[] = " `name` LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_author'])) {
			$cond[] = " `author` LIKE '%" . $this->db->escape($data['filter_author']) . "%'";
		}

		if (isset($data['filter_status']) && strlen($data['filter_status'])) {
			$cond[] = " `status` = '" . (int)$data['filter_status'] . "'";
		}

		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "ea_vqmod_manager";

		if ($cond) {
			$sql .= " WHERE " . implode(' AND ', $cond);
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}	
	
	public function getVqmodAuthors($data = array()) {
		$sql = "SELECT * FROM `" . DB_PREFIX . "ea_vqmod_manager` GROUP BY author LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];		
		$query = $this->db->query($sql);		
		return $query->rows;
	}		
	
	public function getAllComments() {
		$sql = "SELECT * FROM `" . DB_PREFIX . "ea_vqmod_comment` WHERE 1";		
		$query = $this->db->query($sql);		
		return $query->rows;
	}
	
	public function getVqmodComments($vqmod_id) {
		$sql = "SELECT * FROM `" . DB_PREFIX . "ea_vqmod_comment` WHERE `vqmod_id` = '" . (int)$vqmod_id . "'";	
		$query = $this->db->query($sql);		
		return $query->rows;
	}
}