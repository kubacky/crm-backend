<?php

class Warehouse_model extends Platform_Model
{
    private $fields = 'doc_id as id, type, doc_number as number, doc_comment as comment, issuing, receiver, '
    .'date_add as date, list_id as listId, document_types.name as docName';

    public function __construct()
    {
        parent::__construct();
        $def_pattern = 'X/YYYY';
    }

    public function addDocument($document = array())
    {
        $document['user_id'] = $this->u_id;
        $document['doc_number'] = $this->getNextDocNumber($document['type']);
        $document['date_add'] = $this->date;
        $document['date_upd'] = $this->date;

        $this->db->insert('warehouse_docs', $document);
        return $document['type'] . ' ' . $document['doc_number'];
    }

    public function getDocuments()
    {
        $this->db->select($this->fields . ', first_name as firstName, last_name as lastName');
        $this->db->from('warehouse_docs, document_types, users');
        $this->db->where('warehouse_docs.flag', 1);
        $this->db->where('users.id = warehouse_docs.user_id');
        $this->db->where('symbol = type');
        $this->db->order_by('date_add', 'DESC');
        $query = $this->db->get();
        return $query->result();
    }

    public function getDocument($document_id)
    {
        $this->db->select($this->fields);
        $this->db->from('warehouse_docs, document_types');
        $this->db->where('warehouse_docs.flag', 1);
        $this->db->where('crm_warehouse_docs_id', $document_id);
        $this->db->where('symbol = type');
        $query = $this->db->get();
        $result = $query->result();

        $result[0]->date = formatDate($result[0]->date, 'd n Y');
        return $result[0];
    }

    public function getProductsFromList($list_id)
    {
        $this->db->select('crm_products_id as id, product_name as name, listed_product_quantity as quantity, '
            . 'product_quantity as productQuantity, listed_product_name as listedName');
        $this->db->from('products, products_listed');
        $this->db->where('listed_list_id', $list_id);
        $this->db->where('products_listed.flag', 1);
        $this->db->where('crm_products_id = listed_product_id');
        $this->db->order_by('product_name', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }

    private function formatDocuments($documents)
    {
        for ($i = 0; $i < sizeof($documents); $i++) {
            $documents[$i]->number = $documents[$i]->symbol . ' ' . $documents[$i]->number;
            $documents[$i]->timestamp = strtotime($documents[$i]->date);
            $documents[$i]->date = formatDate($documents[$i]->date, 'd n Y');
        }
        return $documents;
    }

    public function getDocumentName($type_id)
    {
        $this->db->select('type_name as name');
        $this->db->from('document_types');
        $this->db->where('crm_document_types_id', $type_id);
        $this->db->limit(1);
        $query = $this->db->get();
        $result = $query->result();

        if (!empty($result)) {
            return $result[0]->name;
        }
        return name;
    }

    public function createWarehouseDocument($document)
    {
        $document['date_add'] = $this->date;
        $document['date_upd'] = $this->date;
        $this->db->insert('warehouse_docs', $document);
        return $this->db->insert_id('warehouse_docs');
    }

    public function updateDocument($document_id, $document)
    {
        $document['date_upd'] = $this->date;
        $this->db->set($document);
        $this->db->where('crm_warehouse_docs_id', $document_id);
        $this->db->update('warehouse_docs');
    }

    public function addOperator($operator = array())
    {
        $operator['date_add'] = $this->date;
        $operator['date_upd'] = $this->date;
        $this->db->insert('operators', $operator);
        return $this->db->insert_id('operators');
    }

    public function getNextDocNumber($type, $start = 0)
    {
        $year = date('Y');
        $this->db->select('doc_number');
        $this->db->from('warehouse_docs');
        $this->db->where('type', $type);
        $this->db->where('year(date_add)', $year);
        $this->db->where('flag', 1);
        $this->db->order_by('date_add', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get();
        $result = $query->result();

        if (!empty($result)) {
            $start = $this->cutDocNumber($result[0]->doc_number, $type);
        }
        return $this->parseDocNumber($start + 1, $type);
    }

    private function cutDocNumber($number, $type)
    {
        $format = $this->getFormatNumber($type);
        $format_parts = explode('/', $format);
        $number_index = 0;
        for ($i = 0; $i < sizeof($format_parts); $i++) {
            if (strpos('X', $format_parts[$i])) {
                $number_index = $i;
            }
        }
        $number_parts = explode('/', $number);
        return intval($number_parts[$number_index]);
    }

    private function parseDocNumber($number, $type)
    {
        $format = $this->getFormatNumber($type);
        $format_parts = explode('/', $format);
        $doc_number = '';
        for ($i = 0; $i < sizeof($format_parts); $i++) {
            $pos = strpos($format_parts[$i], 'X');
            if ($pos === false) {
                $doc_number .= '/';
                $doc_number .= $this->formatNumberParts($format_parts[$i]);
            } else {
                $doc_number .= $this->getRealNumber($format_parts[$i], $number);
            }
        }
        return $doc_number;
    }

    private function getFormatNumber($type)
    {
        $this->db->select('pattern');
        $this->db->from('document_types');
        $this->db->where('symbol', $type);
        $this->db->limit('1');
        $query = $this->db->get();
        $result = $query->result();
        
        if(!empty($result)) {
            return $result[0]->pattern;
        }
        return $this->$def_pattern;
    }

    private function getRealNumber($format, $number)
    {
        if (strlen($format) > strlen($number)) {
            $diff = strlen($format) - strlen($number);
            $return = '';
            for ($i = 0; $i < $diff; $i++) {
                $return .= '0';
            }
            $return .= $number;
            return $return;
        }
        return $number;
    }

    private function formatNumberParts($part)
    {
        switch ($part) {
            case 'YY':
                return date('y');
            case 'MM':
                return date('M');
            case 'YYYY':
                return date('Y');
            default:
                return $part;
        }
    }

}
