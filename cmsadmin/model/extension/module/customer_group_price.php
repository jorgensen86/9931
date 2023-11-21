<?php
class ModelExtensionModuleCustomerGroupPrice extends Model {
    public function install() {
       $sql =   "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "customer_group_price` (
                `product_id` int(11) NOT NULL,
                `customer_group_id` int(11) NOT NULL,
                `price` decimal(15,4) NOT NULL DEFAULT '0.0000',
                 PRIMARY KEY (`product_id`,`customer_group_id`),
                 KEY `product_id` (`product_id`),
                 KEY `customer_group_id` (`customer_group_id`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
        
        $this->db->query($sql);
    }

    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "customer_group_price`");
    }

    public function deleteData() {
         $this->db->query("DELETE FROM `" . DB_PREFIX . "customer_group_price`");
     }

    public function editCustomerGroupPrices($product_id, $data) {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "customer_group_price` WHERE product_id = '" . (int)$product_id . "'");
        
        foreach ($data as $cgid => $value) {
            if($value['price'] || $value['discount'] ) {
                $this->db->query("INSERT INTO `" . DB_PREFIX . "customer_group_price` SET product_id = '" . (int)$product_id . "', customer_group_id ='" . (int)$cgid . "', price ='" . (float)$value['price'] . "', discount ='" . (float)$value['discount'] . "'");
            }
        }
    }

    public function getProductPrices($product_id) {
        $cgp_data = array();
        
        $query =  $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_group_price` WHERE product_id = '" . (int)$product_id . "'");

        foreach ($query->rows as $value) {
            $cgp_data[$value['customer_group_id']] = [
                'price' => $value['price'],
                'discount' => $value['discount']
            ];
        }

        return $cgp_data;
    }
}