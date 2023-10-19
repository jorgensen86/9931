<?php
class ModelExtensionEnhancementEaOcmodManager extends Model {
	
// ***************************************************************************************** //	
// ************************************  Install Stuff  ************************************ //
// ***************************************************************************************** //		
	public function installModBackup() {
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ea_modification_backup`");
		$this->db->query("CREATE TABLE `" . DB_PREFIX . "ea_modification_backup` (`mod_id` int(11) NOT NULL, `mod_xml` mediumtext COLLATE utf8_general_ci NOT NULL, PRIMARY KEY (`mod_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");		
		return true;
	}	
	
	public function installModEmail() {
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ea_modification_email`");
		$this->db->query("CREATE TABLE `" . DB_PREFIX . "ea_modification_email` (`mod_id` int(11) NOT NULL, `mod_email` varchar(96) COLLATE utf8_general_ci NOT NULL, PRIMARY KEY (`mod_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");		
		return true;
	}	
	
	public function installModMeta() {
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ea_modification_meta`");
		$this->db->query("CREATE TABLE `" . DB_PREFIX . "ea_modification_meta` (`mod_id` int(11) NOT NULL, `type` VARCHAR(64) NOT NULL DEFAULT 'Installed', `file` VARCHAR(255) DEFAULT NULL, PRIMARY KEY (`mod_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");		
		return true;
	}	
	
	public function installModModified() {
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ea_modification_modified`");
		$this->db->query("CREATE TABLE `" . DB_PREFIX . "ea_modification_modified` (`mod_id` int(11) NOT NULL, username varchar(255) NOT NULL, `date_modified` datetime NOT NULL, PRIMARY KEY (`mod_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");		
		return true;
	}		
	
	public function installModComment() {
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ea_modification_comment`");
		$this->db->query("CREATE TABLE `" . DB_PREFIX . "ea_modification_comment` (`comment_id` int(11) NOT NULL AUTO_INCREMENT,`mod_id` int(11) NOT NULL, `comment` text COLLATE utf8_general_ci NOT NULL DEFAULT '', `date_added` datetime NOT NULL, PRIMARY KEY (`comment_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");		
		return true;
	}

// ***************************************************************************************** //	
// ***************************************  Add Stuff  ************************************* //
// ***************************************************************************************** //		
	public function addModification($data) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "modification` SET `extension_install_id` = '" . (int)$data['extension_install_id'] . "', `name` = '" . $this->db->escape((string)$data['name']) . "', `code` = '" . $this->db->escape((string)$data['code']) . "', `author` = '" . $this->db->escape((string)$data['author']) . "', `version` = '" . $this->db->escape((string)$data['version']) . "', `link` = '" . $this->db->escape((string)$data['link']) . "', `xml` = '" . $this->db->escape((string)$data['xml']) . "', `status` = '" . (int)$data['status'] . "', `date_added` = NOW()");
	}	

	public function addExtensionInstall($filename, $extension_id = 0, $extension_download_id = 0) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "extension_install` SET `filename` = '" . $this->db->escape($filename) . "', `extension_id` = '" . (int)$extension_id . "', `extension_download_id` = '" . (int)$extension_download_id . "', `date_added` = NOW()");
	
		return $this->db->getLastId();
	}	
	
	public function addModificationEmail($modification_id,$email) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "ea_modification_email` SET `mod_id` = '" . (int)$modification_id . "', `mod_email` = '" . $this->db->escape((string)$email) . "'");
	}	
	
	public function addModificationDev($data) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "modification` SET `extension_install_id` = '" . (int)$data['extension_install_id'] . "', `name` = '" . $this->db->escape((string)$data['name']) . "', `code` = '" . $this->db->escape((string)$data['code']) . "', `author` = '" . $this->db->escape((string)$data['author']) . "', `version` = '" . $this->db->escape((string)$data['version']) . "', `link` = '" . $this->db->escape((string)$data['link']) . "', `xml` = '" . $this->db->escape($data['xml']) . "', `status` = '" . (int)$data['status'] . "', `date_added` = NOW()");
		
		return $this->db->getLastId();
	}
	
	public function addMeta($modification_id,$dev_file) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "ea_modification_meta SET mod_id = '" . (int) $modification_id 	. "', file = '" . $this->db->escape((string)$dev_file) . "', type='Development'");
	}	
	
	public function createBackup($modification_id, $xml) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "ea_modification_backup` SET `mod_id` = '" . (int)$modification_id . "', `mod_xml` = '" . $this->db->escape((string)$xml) . "'");
	}		
	
	public function addModComment($modification_id,$comment) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "ea_modification_comment` SET `mod_id` = '" . (int)$modification_id . "', `comment` = '" . $this->db->escape((string)$comment) . "', `date_added` = NOW()");
	}


// ***************************************************************************************** //	
// **************************************  Edit/Update  ************************************ //
// ***************************************************************************************** //	
	public function editModification($modification_id, $data) {
		$user = $this->db->query("SELECT *, CONCAT(username, '<br />(', firstname, ' ', lastname, ')') AS user FROM `" . DB_PREFIX . "user` WHERE username = '" . $this->db->escape($this->user->getUserName()) . "'");		
		$this->db->query("DELETE FROM `" . DB_PREFIX . "ea_modification_modified` WHERE `mod_id` = '" . (int)$modification_id . "'");
		$this->db->query("INSERT INTO `" . DB_PREFIX . "ea_modification_modified` SET `mod_id` = '" . (int)$modification_id . "', `username` = '" . $user->row['user'] . "', `date_modified` = NOW()");
		
		$this->db->query("UPDATE `" . DB_PREFIX . "modification` SET `code` = '" . $this->db->escape($data['code']) . "', `name` = '" . $this->db->escape($data['name']) . "', `author` = '" . $this->db->escape($data['author']) . "', `version` = '" . $this->db->escape($data['version']) . "', `link` = '" . $this->db->escape($data['link']) . "', `xml` = '" . $this->db->escape($data['xml']) . "', `status` = '" . (int)$data['status'] . "' WHERE `modification_id` = '" . (int)$modification_id . "'");
	}
	
	public function editModificationEmail($modification_id,$email) {
		$this->db->query("UPDATE `" . DB_PREFIX . "ea_modification_email` SET `mod_email` = '" . $this->db->escape((string)$email) . "' WHERE `mod_id` = '" . (int)$modification_id . "'");
	}
	
	public function editSettingValue($code = '', $key = '', $value = '', $store_id = 0) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE `store_id` = '" . (int)$store_id . "' AND `code` = '" . $this->db->escape($code) . "' AND `key` = '" . $this->db->escape($key) . "'");
		if($query->num_rows) {
			$this->db->query("UPDATE `" . DB_PREFIX . "setting` SET `value` = '" . $this->db->escape($value) . "', serialized = '0' WHERE store_id = '" . (int)$store_id . "' AND `code` = '" . $this->db->escape($code) . "' AND `key` = '" . $this->db->escape($key) . "'");
		} else {
			$this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$store_id . "', `code` = '" . $this->db->escape($code) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape($value) . "'");
		}
		
	}
	
	public function updateMeta($modification_id,$xml_file) {
		$this->db->query("UPDATE `" . DB_PREFIX . "ea_modification_meta` SET `file` = '" . $this->db->escape((string)$xml_file) . "' WHERE `mod_id` = '" . (int)$modification_id . "'");
	}

	public function updateExtensionInstall($extension_install_id, $filename) {
		$this->db->query("UPDATE `" . DB_PREFIX . "extension_install` SET `filename` = '" . $this->db->escape($filename) . "' WHERE extension_install_id = '" . (int)$extension_install_id . "'");
	
		return $this->db->getLastId();
	}	
	
	public function updateMetaByFilename($old_file,$new_file) {
		$this->db->query("UPDATE `" . DB_PREFIX . "ea_modification_meta` SET `file` = '" . $this->db->escape((string)$new_file) . "' WHERE `file` = '" . $this->db->escape((string)$old_file) . "'");
	}

	public function updateExtensionInstallByFilename($old_file,$new_file) {
		$this->db->query("UPDATE `" . DB_PREFIX . "extension_install` SET `filename` = '" . $this->db->escape($new_file) . "' WHERE filename = '" . $this->db->escape((string)$old_file) . "'");
	}	

	public function updateMyMod($modification_id, $link) {
		$this->db->query("UPDATE `" . DB_PREFIX . "modification` SET `link` = '" . (string)$this->db->escape($link) . "' WHERE modification_id = '" . (int)$modification_id . "'");
	}

	
// ***************************************************************************************** //	
// *************************************  Delete Stuff  ************************************ //
// ***************************************************************************************** //		
	public function deleteModification($modification_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "modification` WHERE `modification_id` = '" . (int)$modification_id . "'");
	}

	public function deleteModificationsByExtensionInstallId($extension_install_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "modification` WHERE `extension_install_id` = '" . (int)$extension_install_id . "'");
	}

	public function deleteModificationFile($extension_path_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "extension_path` WHERE `extension_path_id` = '" . (int)$extension_path_id . "'");
	}
		
	public function deleteExtensionPath($extension_path_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "extension_path` WHERE `extension_path_id` = '" . (int)$extension_path_id . "'");
	}
	
	public function deleteExtensionInstall($extension_install_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "extension_install` WHERE `extension_install_id` = '" . (int)$extension_install_id . "'");
	}

	public function deleteModificationEmail($modification_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "ea_modification_email` WHERE `mod_id` = '" . (int)$modification_id . "'");
	}
	
	public function deleteBackup($modification_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "ea_modification_backup` WHERE `mod_id` = '" . (int)$modification_id . "'");
	}
	
	public function deleteMetaMod() {
		$this->db->query("DELETE FROM " . DB_PREFIX . "modification WHERE modification_id IN (SELECT mod_id FROM " . DB_PREFIX . "ea_modification_meta)");
	}
	
	public function deleteMetaType() {
		$this->db->query("DELETE FROM " . DB_PREFIX . "ea_modification_meta WHERE type='Development'");
	}
	
	public function deleteMeta($modification_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "ea_modification_meta` WHERE `mod_id` = '" . (int)$modification_id . "'");
	}
	
	public function deleteModComment($comment_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "ea_modification_comment` WHERE `comment_id` = '" . (int)$comment_id . "'");
	}
	
	public function deleteModComments($modification_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "ea_modification_comment` WHERE `mod_id` = '" . (int)$modification_id . "'");
	}


// ***************************************************************************************** //	
// ************************************  Enable/Disable  *********************************** //
// ***************************************************************************************** //		
	public function enableModification($modification_id) {
		$this->db->query("UPDATE `" . DB_PREFIX . "modification` SET `status` = '1' WHERE `modification_id` = '" . (int)$modification_id . "'");
	}

	public function disableModification($modification_id) {
		$this->db->query("UPDATE `" . DB_PREFIX . "modification` SET `status` = '0' WHERE `modification_id` = '" . (int)$modification_id . "'");
	}

	public function disableModificationByInstallId($extension_install_id) {
		$this->db->query("UPDATE `" . DB_PREFIX . "modification` SET `status` = '0' WHERE `extension_install_id` = '" . (int)$extension_install_id . "'");
	}

	
// ***************************************************************************************** //	
// **************************************  Get Stuff  ************************************** //
// ***************************************************************************************** //	
	public function getModification($modification_id) {
		$query = $this->db->query("SELECT m.*, ei.filename AS filename, IFNULL(type,'Installed') as type, IFNULL(file,'-NA-') as file FROM `" . DB_PREFIX . "modification` m LEFT JOIN `" . DB_PREFIX . "extension_install` ei ON (m.extension_install_id = ei.extension_install_id) LEFT JOIN `" . DB_PREFIX . "ea_modification_meta` md ON (m.modification_id = md.mod_id) WHERE modification_id = '" . (int)$modification_id . "'");		
		return $query->row;
	}

	public function getModifications($data = array()) {
		$sql = "SELECT m.*, ei.filename AS filename, IFNULL(type,'Installed') as type, IFNULL(file,'-NA-') as file, mm.username as username, mm.date_modified as date_modified FROM `" . DB_PREFIX . "modification` m LEFT JOIN `" . DB_PREFIX . "extension_install` ei ON (m.extension_install_id = ei.extension_install_id) LEFT JOIN `" . DB_PREFIX . "ea_modification_meta` md ON (m.modification_id = md.mod_id) LEFT JOIN `" . DB_PREFIX . "ea_modification_modified` mm ON (m.modification_id = mm.mod_id)";

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
			'code',
			'version',
			'status',
			'type',
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

	public function getTotalModifications($data = array()) {
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

		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "modification";

		if ($cond) {
			$sql .= " WHERE " . implode(' AND ', $cond);
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}		
	
	public function getModificationFile($extension_path_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "extension_path` WHERE `extension_path_id` = '" . (int)$extension_path_id . "'");
		return $query->row;
	}		
	
	public function getModificationFilesShared() {
		$query = $this->db->query("SELECT ep.path FROM `" . DB_PREFIX . "extension_path` ep INNER JOIN (SELECT path FROM `" . DB_PREFIX . "extension_path` GROUP BY path HAVING COUNT(path) > 1) dupes ON ep.path = dupes.path");
		return $query->rows;
	}
	
	public function getModificationAuthors($data = array()) {
		$sql = "SELECT * FROM `" . DB_PREFIX . "modification` GROUP BY author LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		
		$query = $this->db->query($sql);
		
		return $query->rows;
	}

	public function getModificationFiles($data = array()) {
		$sql = "SELECT ep.extension_path_id, ep.extension_install_id AS install_id, ep.path, ep.date_added, ei.filename AS filename, m.name AS name, m.code AS code, m.modification_id AS modification_id, m.extension_install_id, IFNULL(type,'Installed') as type FROM `" . DB_PREFIX . "extension_path` ep LEFT JOIN `" . DB_PREFIX . "extension_install` ei ON (ep.extension_install_id = ei.extension_install_id) LEFT JOIN `" . DB_PREFIX . "modification` m ON (ep.extension_install_id = m.extension_install_id) LEFT JOIN `" . DB_PREFIX . "ea_modification_meta` mm ON (m.modification_id = mm.mod_id)";

		$cond = array();

		if (!empty($data['filter_name'])) {
			$cond[] = " `name` LIKE '%" . $this->db->escape((string)$data['filter_name']) . "%'";
		}

		if (!empty($data['filter_path'])) {
			$cond[] = " `path` LIKE '" . $this->db->escape((string)$data['filter_path']) . "%'";
		}
		
		if (!empty($data['filter_date'])) {
			$cond[] .= " DATE(ep.date_added) = DATE('" . $this->db->escape((string)$data['filter_date']) . "')";
		}	
		
		if (!empty($data['filter_shared'])) {
			$cond[] .= " `path` IN (SELECT `path` FROM `" . DB_PREFIX . "extension_path` GROUP BY ep.extension_install_id HAVING COUNT(path) > 1)";
		}

		if ($cond) {
			$sql .= " WHERE " . implode(' AND ', $cond);
		}
		
		$sort_data = array(
			'install_id',
			'name',
			'ep.path',
			'ep.date_added'
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

	public function getTotalModificationFiles($data = array()) {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "extension_path ep LEFT JOIN `" . DB_PREFIX . "extension_install` ei ON (ei.extension_install_id = ep.extension_install_id) LEFT JOIN `" . DB_PREFIX . "modification` m ON (m.extension_install_id = ep.extension_install_id)";
		
		$cond = array();

		if (!empty($data['filter_name'])) {
			$cond[] = " `name` LIKE '%" . $this->db->escape((string)$data['filter_name']) . "%'";
		}

		if (!empty($data['filter_path'])) {
			$cond[] = " `path` LIKE '" . $this->db->escape((string)$data['filter_path']) . "%'";
		}
		
		if (!empty($data['filter_date'])) {
			$cond[] .= " DATE(ep.date_added) = DATE('" . $this->db->escape((string)$data['filter_date']) . "')";
		}	
		
		if (!empty($data['filter_shared'])) {
			$cond[] .= " `path` IN (SELECT `path` FROM `" . DB_PREFIX . "extension_path` GROUP BY ep.extension_install_id HAVING COUNT(path) > 1)";
		}

		if ($cond) {
			$sql .= " WHERE " . implode(' AND ', $cond);
		}	
		
		$query = $this->db->query($sql);
		return $query->row['total'];
	}	
	
	public function getAllModFiles($extension_install_id) {
		$query = $this->db->query("SELECT `path` FROM `" . DB_PREFIX . "extension_path` WHERE `extension_install_id` = '" . (int)$extension_install_id . "'");
		return $query->rows;
	}	
	
	public function getTotalModFiles($extension_install_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "extension_path` WHERE `extension_install_id` = '" . (int)$extension_install_id . "'");
		return $query->row['total'];
	}
	
	public function getModificationByCode($code) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "modification` WHERE `code` = '" . $this->db->escape($code) . "'");
		return $query->row;
	}
	
	public function getModificationByFilename($filename) {
		$query = $this->db->query("SELECT ei.*,m.modification_id AS modification_id FROM `" . DB_PREFIX . "extension_install` ei LEFT JOIN `" . DB_PREFIX . "modification` m ON (m.extension_install_id = ei.extension_install_id) WHERE `filename` = '" . $this->db->escape($filename) . "'");
		return $query->row;
	}

	public function getModificationByExtensionInstallId($extension_install_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "modification` WHERE `extension_install_id` = '" . (int)$extension_install_id . "'");	
		return $query->row;
	}

	public function getExtensionPathsByExtensionInstallId($extension_install_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "extension_path` WHERE `extension_install_id` = '" . (int)$extension_install_id . "' ORDER BY `date_added` ASC");
		return $query->rows;
	}
	
	public function getExtensionPath($extension_path_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "extension_path` WHERE `extension_path_id` = '" . (int)$extension_path_id . "'");
		return $query->row;
	}

	public function getModificationEmail($modification_id) {
		$query = $this->db->query("SELECT `mod_email` FROM `" . DB_PREFIX . "ea_modification_email` WHERE `mod_id` = '" . (int)$modification_id . "'");
		return $query->row;
	}
	
	public function getBackup($modification_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "ea_modification_backup` WHERE `mod_id` = '" . (int)$modification_id . "'");		
		return $query->row;
	}
	
	public function getUsers() {
		$sql = "SELECT * FROM `" . DB_PREFIX . "user` WHERE status=1 ORDER BY username ASC";
		$query = $this->db->query($sql);
		return $query->rows;
	}		
	
	public function getAllComments() {
		$sql = "SELECT * FROM `" . DB_PREFIX . "ea_modification_comment` WHERE 1";		
		$query = $this->db->query($sql);		
		return $query->rows;
	}
	
	public function getModComments($modification_id) {
		$sql = "SELECT * FROM `" . DB_PREFIX . "ea_modification_comment` WHERE `mod_id` = '" . (int)$modification_id . "'";	
		$query = $this->db->query($sql);		
		return $query->rows;
	}
}