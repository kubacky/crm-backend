
<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Products extends Platform {

    public function __construct() {
        parent::__construct();
    }
    
    public function index() {
        $this->render('products/index');
    }
    
    public function product($product_id) {
        $this->render('products/product');
    }
}
