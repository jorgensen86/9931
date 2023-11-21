<?php

class ModelExtensionModuleCustomerGroupPrice extends Model {
    public function getProductPrice($data) {
        
        $price_query = $this->db->query("SELECT MIN(p.price) as price FROM ((SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND pd2.product_id = '" . (int)$data['product_id'] . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC)  UNION SELECT price FROM " . DB_PREFIX . "customer_group_price WHERE product_id = '" . (int)$data['product_id'] . "' AND customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "') as p");
        
        return $price_query->row['price'];
    }

    public function getProductDiscount($data) {
        
        $discount_query = $this->db->query("SELECT discount FROM " . DB_PREFIX . "customer_group_price WHERE product_id = '" . (int)$data['product_id'] . "' AND customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "'");
        
        if ($discount_query->num_rows) {
            return $discount_query->row['discount'];
        }
    }
}
