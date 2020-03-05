<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Warehouse extends Platform
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('products_model');
        $this->load->model('warehouse_model');

        $this->load->library('product');
    }

    public function get($action, $id = null)
    {
        switch ($action) {
            case 'documents':
                $this->getDocuments();
                break;
            case 'document':
            default:
                $this->getDocument($id);
        }
        $this->render(null, 'json');
    }

    public function create($type)
    {
        switch ($type) {
            case 'set':
                $this->createSet();
                break;
            case 'document':
            default:
                $this->addDocument();

        }
        $this->render(null, 'json');
    }

    private function getDocuments()
    {
        $docs = $this->warehouse_model->getDocuments();

        $c = count($docs);
        for ($i = 0; $i < $c; $i++) {
            $docs[$i] = $this->formatDocument($docs[$i]);
        }
        $this->data = $docs;
    }

    private function getDocument($doc_id)
    {
        $doc = $this->warehouse_model->getDocument($doc_id);

        $this->date = $this->formatDocument($doc);
    }

    private function formatDocument($doc)
    {
        $doc->products = $this->products_model->getProductsByList($doc->listId);
        $doc->date = formatDate($doc->date, 'j n Y');
        $doc->number = $doc->type . ' ' . $doc->number;

        return $doc;
    }

    private function addDocument()
    {
        $input = $this->getInput();
        $action = $input['action'];

        $list_id = $this->products_model->createProductsList();
        $items = $this->addItems($input['items'], $list_id, $action);

        $type = $this->buildDocType($action, $input['type']);

        $document = [];
        $document['list_id'] = $list_id;
        $document['type'] = $type;
        $document['issuing'] = $input['issuing'];
        $document['receiver'] = $input['receiver'];
        $document['doc_comment'] = $input['comments'];

        $docNo = $this->warehouse_model->addDocument($document);

        $this->data['status'] = 'ok';
        $this->data['docNo'] = $docNo;
    }

    private function buildDocType($action, $type)
    {
        if ($action === 'issuing' && $type === 'inside') {
            return 'RW';
        }
        $return = ($action === 'receive') ? 'P' : 'W';
        $return .= ($type === 'inside') ? 'W' : 'Z';

        return $return;
    }

    private function addItems($items = array(), $list_id, $action)
    {
        $c = count($items);
        for ($i = 0; $i < $c; $i++) {
            $p_id = $items[$i]['id'];
            $quantity = intval($items[$i]['quantity']);
            if ($p_id == 0) {
                $product = $this->product->prepareProduct($items[$i], true);
                $p_id = $this->products_model->addProduct($product);
                $this->products_model->assignToGroups($p_id, $items[$i]['groups']);
            } else {
                $this->updateQuantity($action, $items[$i]);
            }
            $this->products_model->assignToList($list_id, $p_id, $quantity);
        }
        return true;
    }

    private function createSet()
    {
        $input = $this->getInput();

        $list_id = $this->products_model->createProductsList();
        $list = [];

        $items = $input['items'];

        $c = count($items);
        for ($i = 0; $i < $c; $i++) {
            $id = $items[$i]['id'];
            $quantity = $items[$i]['quantity'];
            $this->products_model->assignToList($list_id, $id, $quantity);
        }

        $this->product->prepareProduct($input);
        $this->product->assignList($list_id);
        $set = $this->product->get();

        $set_id = $this->products_model->addProduct($set);

        $this->data = ['status' => 'ok'];
    }

    public function update($id)
    {

    }

    private function updateQuantity($action, $product = array())
    {
        $prev = $this->products_model->getProduct($product['id']);
        $upd = [];

        if ($action === 'issuing') {
            if ($prev->quantity < $product['quantity']) {
                $this->data['status'] = 'error';
                $this->data['message'] = 'Ilość wydawanych towarów jest większa niż stan magazynowy';
                $this->render(null, 'json');
                die();
            }
            $quantity = intval($prev->quantity) - intval($product['quantity']);
        } else {
            $quantity = intval($prev->quantity) + intval($product['quantity']);
        }

        $upd['quantity'] = $quantity;
        $this->products_model->updateProduct($product['id'], $upd);
    }

    public function getNextDocNumber($type = null)
    {
        $document = $this->warehouse_model->getNextDocNumber($type);

        $this->data = $document;
        $this->render(null, 'json');
    }

    private function getWarehouseDoc($document_id)
    {
        $document = $this->warehouse_model->getDocument($document_id);
        $document->issuing = '';
        $document->receiver = '';

        if ($document->issuingId != 0) {
            $document->issuing = $this->warehouse_model->getOperatorName($document->issuingId);
        }

        if ($document->receiverId != 0) {
            $document->receiver = $this->warehouse_model->getOperatorName($document->receiverId);
        }
        $document->products = $this->warehouse_model->getProductsFromList($document->listId);
        $document->author = $this->ion_auth->user($document->authorId)->row();
        return $document;
    }

    public function updateDocument()
    {
        $input = $this->input->post();

        $document = array();

        $doc_id = $input['id'];
        $this->products_model->clearProductsList($input['listId']);
        $this->assignProductsToList($input['listId'], $input['parts']);

        $document['doc_issuing_id'] = $this->getOperatorId($input['issuing']);
        $document['doc_receiver_id'] = $this->getOperatorId($input['receiver']);

        $this->warehouse_model->updateDocument($doc_id, $document);
    }

    private function assignProductsToList($list_id, $products = array())
    {
        for ($i = 0; $i < sizeof($products); $i++) {
            $product_id = $products[$i]['id'];
            $product = array();
            $product['product_tax_rate_id'] = 0;
            $product['product_quantity'] = $products[$i]['quantity'];
            $product['product_price'] = 0;
            $this->products_model->assignProductToList($list_id, $product_id, $product);
        }
    }

    private function setProductsQuantity($current_amount, $issued_amount, $product_id)
    {
        if (intval($current_amount) > intval($issued_amount)) {
            $quantity = intval($current_amount) - intval($issued_amount);
        } else {
            $quantity = 0;
        }
        $this->products_model->setProductQuantity($product_id, $quantity);
    }

    private function setPartsQuantity($list_id, $sets_amount)
    {
        $products = $this->warehouse_model->getProductsFromList($list_id);
        for ($i = 0; $i < sizeof($products); $i++) {
            $quantity = $products[$i]->quantity * $sets_amount;
            $this->setProductsQuantity($products[$i]->productQuantity, $quantity, $products[$i]->id);
        }
    }

    private function assignToCategories($product_id, $categories = array())
    {
        $this->products_model->clearProductCategories($product_id);
        for ($i = 0; $i < sizeof($categories); $i++) {
            $this->products_model->assignToCategory($product_id, $categories[$i]);
        }
    }

    public function updateProduct()
    {
        $input = $this->input->post('product', true);
        $product_id = $this->input->post('productId', true);
        $product = $this->parseProduct($input);
        $this->products_model->updateProduct($product_id, $product);
        $this->assignToCategories($product_id, $input['categories']);
    }

    public function createWarehouseDocument($type_id, $document_number, $list_id, $product = array())
    {
        $document = array();
        $document['doc_user_id'] = $this->ion_auth->get_user_id();
        $document['doc_issuing_id'] = $product['issuing'];
        $document['doc_receiver_id'] = $product['receiver'];
        $document['doc_products_list_id'] = $list_id;
        $document['doc_type_id'] = $type_id;
        $document['doc_number'] = $document_number;
        $document['doc_comment'] = $product['product_comment'];
        $this->warehouse_model->createWarehouseDocument($document);
    }

}
