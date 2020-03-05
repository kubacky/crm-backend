<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Categories extends Platform {

    public function __construct() {
        parent::__construct();
        $this->load->helper('string');
        $this->load->model('categories_model');
    }

    public function getCategories($select = 'all') {
        switch ($select) {
            case 'products':
                $categories = $this->categories_model->getCategories('products');
                break;
            case 'warehouse':
                $categories = $this->categories_model->getCategories('warehouse');
                break;
            case 'all':
            default:
                $categories = $this->categories_model->getAll();
                break;
        }

        $this->data = $categories;
        $this->render(null, 'json');
    }

    public function getCategory($category_id) {
        $category = $this->categories_model->getCategory($category_id);
        $this->data = $category[0];
        $this->render(null, 'json');
    }

    public function add() {
        $post = $this->input->post('category', true);
        $category = $this->parseCategory($post);
        for ($i = 0; $i < sizeof($post['assigned']); $i++) {
            $category['category_type'] = $post['assigned'][$i];
            $this->categories_model->addCategory($category);
        }
    }

    public function update() {
        $post = $this->input->post('category', true);
        $category = $this->parseCategory($post);
        $category_id = $this->input->post('categoryId', true);
        $this->categories_model->updateCategory($category_id, $category);
        $this->categories_model->setDateUpd($category_id);
    }

    public function delete() {
        $category_id = $this->input->post('category_id', true);
        $this->categories_model->deleteCategory($category_id);
        $this->categories_model->setDateUpd($category_id);
    }

    private function parseCategory($post) {
        $category = array();
        $category['category_user_id'] = $this->ion_auth->get_user_id();
        $category['category_parent_id'] = $post['parentId'];
        $category['category_name'] = $post['name'];
        $category['category_alias'] = getPlainText($category['category_name']);
        $category['category_description'] = $post['description'];
        return $category;
    }

}
