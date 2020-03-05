<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Products_model extends Platform_Model
{

    protected $fields = 'products.product_id as id, user_id as userId, products.list_id as listId, '
        . 'unit, products.quantity, name, plain_name as alias, code, date_add as date';

    public function __construct()
    {
        parent::__construct();
    }

    public function addProduct($product = array())
    {
        $product['user_id'] = $this->u_id;
        $product['date_add'] = $this->date;
        $product['date_upd'] = $this->date;

        $this->db->insert('products', $product);
        return $this->db->insert_id('products');
    }

    public function getProducts($type = null)
    {
        $this->db->select($this->fields);
        $this->db->from('products');
        $this->db->where('flag', 1);

        if ($type) {
            $this->db->where('type', $type);
        }

        $query = $this->db->get();
        $result = $query->result();

        return $this->formatProducts($result);
    }

    public function getProduct($product_id)
    {
        $this->db->select($this->fields);
        $this->db->from('products');
        $this->db->where('product_id', $product_id);
        return $this->getOnce();
    }

    public function getProductsByGroup($group_ids)
    {
        $parts = explode('-', $group_ids);

        $this->db->select($this->fields);
        $this->db->from('products, grouped');
        $this->db->where_in('group_id', $parts);
        $this->db->where('products.product_id = grouped.link_id');
        $this->db->where('group_type = ', 'warehouse');
        $this->db->where('flag', 1);
        $this->db->order_by('name', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }

    public function getProductsByList($list_id)
    {
        $this->db->select($this->fields . ', products_listed.quantity as listedQuantity');
        $this->db->from('products, products_listed');
        $this->db->where('products_listed.list_id', $list_id);
        $this->db->where('products.product_id = products_listed.product_id');
        $this->db->order_by('name', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }

    public function clearList($list_id)
    {
        $this->db->where('list_id', $list_id);
        $this->db->delete('products_listed');
    }

    public function updateProduct($product_id, $product = array())
    {
        $product['date_upd'] = $this->date;

        $this->db->set($product);
        $this->db->where('product_id', $product_id);
        $this->db->update('products');
    }

    public function createProductsList($previous_id = 0)
    {
        $product_list = array();
        $product_list['list_user_id'] = $this->u_id;
        $product_list['list_previous_id'] = $previous_id;
        $product_list['date_add'] = $this->date;
        $product_list['date_upd'] = $this->date;
        $this->db->insert('product_lists', $product_list);
        return $this->db->insert_id('product_lists');
    }

    public function assignToList($list_id, $product_id, $quantity)
    {
        $pr = $this->getProduct($product_id);

        $product = [];
        $product['list_id'] = $list_id;
        $product['product_id'] = $product_id;
        $product['product_name'] = $pr->name;
        $product['quantity'] = $quantity;

        $this->db->insert('products_listed', $product);
    }

    public function assignToGroups($product_id, $groups = array())
    {
        if (is_array($groups)) {
            $c = count($groups);
            for ($i = 0; $i < $c; $i++) {
                $group = [];
                $group['group_id'] = $groups[$i];
                $group['link_id'] = $product_id;
                $group['group_type'] = 'product';
                $this->db->insert('grouped', $group);
            }
        }
    }

    public function deleteProduct($product_id)
    {
        $upd = [];
        $upd['flag'] = 0;

        $this->updateProduct($product_id, $upd);
    }

    private function formatProducts($products = array())
    {
        $c = count($products);

        for ($i = 0; $i < $c; $i++) {
            $products[$i]->selected = false;
        }

        return $products;
    }

    public function getHistory($product_id)
    {
        $this->db->select('warehouse_docs.date_add as date, type, quantity, '
            . 'doc_number as number, name as doc_name');
        $this->db->from('warehouse_docs, products_listed, document_types');
        $this->db->where('product_id', $product_id);
        $this->db->where('warehouse_docs.list_id = products_listed.list_id');
        $this->db->where('warehouse_docs.type = document_types.symbol');
        $this->db->where('warehouse_docs.flag', 1);
        $this->db->order_by('warehouse_docs.date_add', 'ASC');

        $query = $this->db->get();
        $result = $query->result();

        $quantity = 0;

        $c = count($result);
        for ($i = 0; $i < $c; $i++) {
            if ($result[$i]->type == 'RW' || $result[$i]->type == 'WZ') {
                $result[$i]->quantity *= -1;
            }

            $quantity += $result[$i]->quantity;
            $result[$i]->index = $i + 1;
            $result[$i]->document_no = $result[$i]->type . ' ' . $result[$i]->number;
            $result[$i]->stock = $quantity;
            $result[$i]->date = formatDate($result[$i]->date, 'd n Y');
        }
        return $result;
    }
}

/**
public function getRegistry() {
$this->db->select('crm_client_products_id as id, product_serial_no as serial, '
. 'client_name as clientName, client_shortcut as client, product_purchase_date as date, '
. 'product_last_service_date as service');
$this->db->from('client_products, clients');
$this->db->where('client_products.flag', 1);
$this->db->where('clients.flag', 1);
$this->db->where('crm_clients_id = product_client_id');
$this->db->order_by('client_products.date_add', 'ASC');
$query = $this->db->get();
$result = $query->result();

return $this->formatRegistry($result);
}

private function formatRegistry($registry) {
for ($i = 0; $i < sizeof($registry); $i++) {
$registry[$i]->timestamp = strtotime($registry[$i]->date);
}
return $registry;
}

public function addProduct($product = array()) {
$product['date_add'] = $this->date;
$product['date_upd'] = $this->date;
$this->db->insert('products', $product);
return $this->db->insert_id('products');
}

public function addService($service = array()) {
$service['date_add'] = $this->date;
$service['date_upd'] = $this->date;
$this->db->insert('services', $service);
return $this->db->insert_id('services');
}

public function addSet($set = array()) {
$set['date_add'] = $this->date;
$set['date_upd'] = $this->date;
$this->db->insert('product_sets', $set);
return $this->db->insert_id('product_sets');
}

public function getSet($set_id) {
$this->db->select('crm_product_sets_id as id, set_list_id as listId, '
. 'set_tax_id as taxRateId, set_price as price, set_name as name, '
. 'set_code as code');
$this->db->from('product_sets');
$this->db->where('crm_product_sets_id', $set_id);
$this->db->limit(1);
$query = $this->db->get();
$result = $query->result();
return $result[0];
}

public function getInvoiceProducts($list_id) {
$this->db->select('listed_product_name as name, listed_product_quantity as quantity, '
. 'listed_product_price as price, rate_value as taxRate');
$this->db->from('products_listed, tax_rates');
$this->db->where('listed_list_id', $list_id);
$this->db->where('crm_tax_rates_id = listed_product_tax_id');
$this->db->order_by('listed_product_name', 'ASC');
$query = $this->db->get();
return $query->result();
}

public function getSetProducts($list_id) {
$this->db->select('crm_products_id as id, product_code as code, product_name as name, '
. 'listed_product_quantity as quantity');
$this->db->from('products, products_listed');
$this->db->where('listed_list_id', $list_id);
$this->db->where('crm_products_id = listed_product_id');
$this->db->where('crm_products.flag', 1);
$query = $this->db->get();
return $query->result();
}

public function clearProductsList($list_id) {
$this->db->set('flag', 0);
$this->db->where('listed_list_id', $list_id);
$this->db->update('products_listed');

$this->db->reset_query();
$this->db->set('flag', 0);
$this->db->where('crm_product_lists_id', $list_id);
$this->db->update('product_lists');
$this->updateDate('crm_product_lists', $list_id);
}

public function getProductSets() {
$this->db->select('crm_product_sets_id as id, set_list_id as listId, '
. 'set_price as price, set_name as name, set_code as code');
$this->db->from('product_sets');
$this->db->where('flag', 1);
$this->db->order_by('set_name', 'ASC');
$query = $this->db->get();
return $query->result();
}

public function getClientProduct($product_id) {
$this->db->select('crm_client_products_id, product_type_id as typeId, product_address_id as addressId, '
. 'product_client_id as clientId, product_class as class, product_serial_no as serial, '
. 'product_purchase_date as purchaseDate, product_last_service_date as serviceDate, '
. 'product_note as note');
$this->db->from('client_products');
$this->db->where('crm_client_products_id', $product_id);
$this->db->limit(1);
$query = $this->db->get();
$result = $query->result();

if (!empty($result)) {
return $result[0];
}
return null;
}

public function getCategorySets($category_id) {
$this->db->select('crm_product_sets_id as id, set_list_id as listId, '
. 'set_price as price, set_name as name, set_code as code');
$this->db->from('product_sets, products_sorted');
$this->db->where('product_sets.flag', 1);
$this->db->where('set_id = crm_product_sets_id');
$this->db->where('category_id', $category_id);
$this->db->order_by('set_name', 'ASC');
$query = $this->db->get();
return $query->result();
}

public function getProduct($product_id) {
$this->db->select('crm_products_id as id, product_code as code, product_quantity as quantity, '
. 'product_min_quantity as minQuantity, product_manufacturer_id as manufacturerId, '
. 'product_supplier_id as supplierId, product_tax_rate_id as taxRateId, '
. 'product_unit_of_measure_id as unitsId, product_price as price, product_parts_list_id as listId, '
. 'product_purchase_price as purchasePrice, product_name as name, product_alias as alias, '
. 'product_description as description, product_comment as comment');
$this->db->from('products');
$this->db->where('crm_products_id', $product_id);
$this->db->limit(1);
$query = $this->db->get();
$result = $query->result();
return $result[0];
}

public function getSetByAlias($alias) {
$alias = getPlainText($alias);
$this->db->select('crm_product_sets_id');
$this->db->from('product_sets');
$this->db->where('set_alias', $alias);
$this->db->where('flag', 1);
$this->db->limit(1);
$query = $this->db->get();
$result = $query->result();

if (!empty($result)) {
return $result[0]->crm_product_sets_id;
}
return null;
}

public function updateProduct($product_id, $product = array()) {
$this->db->set($product);
$this->db->where('crm_products_id', $product_id);
$this->db->update('products');
$this->updateDateUpd($product_id);
}

public function updateSet($set_id, $set = array()) {
$this->db->set($set);
$this->db->where('crm_product_sets_id', $set_id);
$this->db->update('product_sets');
$this->updateDate('crm_product_sets', $set_id);
}

public function createProductsList($previous_id = 0) {
$product_list = array();
$product_list['list_user_id'] = $this->u_id;
$product_list['list_previous_id'] = $previous_id;
$product_list['date_add'] = $this->date;
$product_list['date_upd'] = $this->date;
$this->db->insert('product_lists', $product_list);
return $this->db->insert_id('product_lists');
}

public function assignProductToClient($product) {
$product['date_add'] = $this->date;
$product['date_upd'] = $this->date;
$this->db->insert('client_products', $product);
return $this->db->insert_id('client_products');
}

public function assignProductToList($list_id, $product_id, $product = array()) {
$product_listed = array();
$product_listed['listed_user_id'] = $this->u_id;
$product_listed['listed_product_id'] = $product_id;
$product_listed['listed_list_id'] = $list_id;
$product_listed['listed_product_tax_id'] = $product['product_tax_rate_id'];
$product_listed['listed_product_quantity'] = $product['product_quantity'];
$product_listed['listed_product_price'] = $product['product_price'];
$product_listed['date_add'] = $this->date;
$product_listed['date_upd'] = $this->date;
$this->db->insert('products_listed', $product_listed);
}

public function assignToInvoiceList($list_id, $product = array()) {
$product_listed = array();
$product_listed['listed_user_id'] = $this->u_id;
$product_listed['listed_list_id'] = $list_id;
if (array_key_exists('tax', $product)) {
$product_listed['listed_product_tax_id'] = $product['tax']['id'];
} else {
$product_listed['listed_product_tax_id'] = $product['taxRateId'];
}
$product_listed['listed_product_quantity'] = $product['quantity'];
$product_listed['listed_product_price'] = $product['price'];
$product_listed['listed_product_name'] = $product['name'];
$product_listed['date_add'] = $this->date;
$product_listed['date_upd'] = $this->date;
$this->db->insert('products_listed', $product_listed);
}

public function assignSubToList($list_id, $sub = array()) {
$product_listed = array();
$product_listed['listed_user_id'] = $this->u_id;
$product_listed['listed_list_id'] = $list_id;
$product_listed['listed_product_tax_id'] = $sub['taxRateId'];
$product_listed['listed_product_quantity'] = $sub['quantity'];
$product_listed['listed_product_price'] = $sub['price'];
$product_listed['listed_product_name'] = $sub['subName'];
$product_listed['date_add'] = $this->date;
$product_listed['date_upd'] = $this->date;
$this->db->insert('products_listed', $product_listed);
}

public function getTaxRate($rate_id) {
$this->db->select('rate_value');
$this->db->from('tax_rates');
$this->db->where('crm_tax_rates_id', $rate_id);
$query = $this->db->get();
$result = $query->result();

return $result[0]->rate_value;
}

public function setProductQuantity($product_id, $quantity) {
$this->db->set('product_quantity', $quantity);
$this->db->where('crm_products_id', $product_id);
$this->db->update('products');
}

public function assignProductToInvoice($product = array()) {
$this->db->insert('products_listed', $product);
}

public function getWarehouseProducts($list_id = false) {
$this->db->select('crm_products_id as id, product_code as code, product_quantity as quantity, '
. 'product_name as name, date_add as date, product_comment as comment');
$this->db->from('products');
$this->db->where('flag', 1);
if ($list_id) {
$this->db->where('product_parts_list_id !=', NULL);
} else {
$this->db->where('product_parts_list_id', NULL);
}
$result = $this->db->get();
return $this->formatProducts($result->result());
}

public function getWarehouseCategoryProducts($category_id) {
$this->db->select('crm_products_id as id, product_code as code, product_quantity as quantity, '
. 'product_name as name, date_add as date, product_comment as comment, category_id');
$this->db->from('products, products_sorted');
$this->db->where('flag', 1);
$this->db->where('product_id = crm_products_id');
$this->db->where('category_id', $category_id);
$result = $this->db->get();
return $this->formatProducts($result->result());
}

public function deleteWarehouseProduct($product_id) {
$this->db->set('flag', 0);
$this->db->where('crm_products_id', $product_id);
$this->db->update('products');
$this->updateDateUpd($product_id);
}

public function deleteSet($set_id) {
$this->db->set('flag', 0);
$this->db->where('crm_product_sets_id', $set_id);
$this->db->update('product_sets');
$this->updateDate('crm_product_sets', $set_id);
}

public function updateDateUpd($product_id) {
$this->db->set('date_upd', $this->date);
$this->db->where('crm_products_id', $product_id);
$this->db->update('products');
}

private function formatProducts($result) {
for ($i = 0; $i < sizeof($result); $i++) {
$result[$i]->quantity = intval($result[$i]->quantity);
$result[$i]->timestamp = strtotime($result[$i]->date);
$result[$i]->date = formatDate($result[$i]->date, 'd n');
}
return $result;
}

public function assignToCategory($product_id, $category_id, $set = false) {
$sorted = array();
if ($set == true) {
$sorted['set_id'] = $product_id;
} else {
$sorted['product_id'] = $product_id;
}
$sorted['category_id'] = $category_id;
$this->db->insert('products_sorted', $sorted);
}

public function clearProductCategories($product_id) {
$this->db->where('product_id', $product_id);
$this->db->delete('products_sorted');
}

public function clearSetCategories($set_id) {
$this->db->where('set_id', $set_id);
$this->db->delete('products_sorted');
}

public function getProductCategories($product_id, $set = false) {
$this->db->select('category_id');
$this->db->from('products_sorted');
if ($set == true) {
$this->db->where('set_id', $product_id);
} else {
$this->db->where('product_id', $product_id);
}
$query = $this->db->get();
$result = $query->result();

$return = array();
for ($i = 0; $i < sizeof($result); $i++) {
$return[$i] = $result[$i]->category_id;
}
return $return;
}

public function getHistory($product_id) {
$this->db->select('warehouse_docs.date_add as date, type_symbol as symbol, listed_product_quantity as quantity, '
. 'doc_number as number, type_name as type');
$this->db->from('warehouse_docs, document_types, products_listed');
$this->db->where('listed_product_id', $product_id);
$this->db->where('crm_document_types_id = doc_type_id');
$this->db->where('doc_products_list_id = listed_list_id');
$this->db->where('warehouse_docs.flag', 1);
$this->db->order_by('warehouse_docs.date_add', 'ASC');

$query = $this->db->get();
$result = $query->result();

$quantity = 0;
for ($i = 0; $i < sizeof($result); $i++) {
if ($result[$i]->symbol == 'RW' || $result[$i]->symbol == 'WZ') {
$result[$i]->quantity *= -1;
}
$quantity += $result[$i]->quantity;
$result[$i]->stock = $quantity;
$result[$i]->date = formatDate($result[$i]->date, 'd n Y');
}
return $result;
}

public function getFullProductCategories($product_id) {
$this->db->select('category_id, category_name');
$this->db->from('products_sorted, product_categories');
$this->db->where('product_id', $product_id);
$this->db->where('crm_product_categories_id = category_id');
$query = $this->db->get();
return $query->result();
}

}
 */
