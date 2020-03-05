<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Products extends Platform
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('products_model');
    }

    public function get($type, $id = null)
    {
        switch ($type) {
            case 'list':
                $this->getProductsList($id);
                break;
            case 'product':
                $this->getProduct($id);
                break;
            case 'sets':
                $this->data = $this->products_model->getProducts('set');
                break;
            case 'group':
                $this->getGroup($id);
                break;
            case 'products':
                $this->data = $this->products_model->getProducts('product');
                break;
            case 'all':
            default:
                $this->data = $this->products_model->getProducts();
        }
        $this->render(null, 'json');
    }

    public function find()
    {

        $this->render(null, 'json');
    }

    public function create()
    {

    }

    public function update($id)
    {

    }

    public function delete()
    {
        $input = $this->getInput();

        $products = $input['products'];
        $c = count($products);
        for ($i = 0; $i < $c; $i++) {
            $this->products_model->deleteProduct($products[$i]['id']);
        }

        $this->data['status'] = 'ok';
        $this->render(null, 'json');
    }

    private function getAll()
    {

    }

    private function getProducts($type)
    {
        $this->data = $this->products_model->getProducts($type);
    }

    private function getProductsList($id)
    {
        $products = explode('_', $id);
        array_shift($products);

        $c = count($products);
        $return = [];
        for ($i = 0; $i < $c; $i++) {
            $return[] = $this->products_model->getProduct($products[$i]);
        }
        $this->data = $return;
    }

    private function getProduct($id)
    {
        $product = $this->products_model->getProduct($id);
        $product->history = $this->products_model->getHistory($id);

        $this->data = $product;
    }

    private function getGroup($id)
    {
        $this->data = $this->products_model->getProductsByGroup($id);
    }
}
