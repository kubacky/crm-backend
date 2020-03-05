<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class File extends Application {

    private $_uploaded = array();
    private $_thumb_sizes = array();

    public function __construct() {
        parent::__construct();
        $this->load->model('products_model');
        $this->load->model('image_model');
        $this->load->helper('string');
    }

    public function upload($destination = null) {
        $ds = DIRECTORY_SEPARATOR;

        $files = $_FILES['files'];
        $count = sizeof($files['tmp_name']);

        $config['upload_path'] = FCPATH . '..' . $ds
                . 'img' . $ds . 'upload' . $ds . 'temp';
        $config['allowed_types'] = 'gif|jpg|png';

        $this->load->library('upload', $config);
        if (is_array($files['error'])) {
            for ($i = 0; $i < $count; $i++) {
                $_FILES['file']['name'] = $files['name'][$i];
                $_FILES['file']['type'] = $files['type'][$i];
                $_FILES['file']['tmp_name'] = $files['tmp_name'][$i];
                $_FILES['file']['error'] = $files['error'][$i];
                $_FILES['file']['size'] = $files['size'][$i];
                if ($this->upload->do_upload('file')) {
                    $data = $this->upload->data();
                    $image = array();
                    $image['filename'] = $data['file_name'];
                    $image['file_path'] = $data['full_path'];
                    $image['dir_path'] = $data['file_path'];
                    $image['type'] = $data['file_type'];
                    $image['width'] = $data['image_width'];
                    $image['height'] = $data['image_height'];
                    $this->_uploaded[$i] = $image;
                } else {
                    $this->upload->display_errors();
                }
            }
        } else {
            if ($this->upload->do_upload('files')) {
                $data = $this->upload->data();
                $image = array();
                $image['filename'] = $data['file_name'];
                $image['file_path'] = $data['full_path'];
                $image['dir_path'] = $data['file_path'];
                $image['type'] = $data['file_type'];
                $image['width'] = $data['image_width'];
                $image['height'] = $data['image_height'];
                $this->_uploaded[] = $image;
            } else {
                $this->upload->display_errors();
            }
        }

        $this->data = $this->_uploaded;
        if ($destination != null) {
            $this->dispatchUploadedFiles($destination);
        }

        $this->render(null, 'json');
    }

    protected function dispatchUploadedFiles($destination) {
        switch ($destination) {
            case 'slider':
            default:
                $this->createSliderThumb();
        }
    }

    private function createSliderThumb() {
        $this->load->library('thumb_creator');
        $this->_thumb_sizes = array(array(268, 242, false, 'medium'));

        $images = $this->data;
        for ($i = 0; $i < sizeof($images); $i++) {
            $thumb = $this->createThumbs($images[$i]);
            if ($thumb != false) {
                return true;
            } else {
                $this->_messages[] = array('Nie udało się dodać zdjęcia: ' . $images[$i]['filename'], 'warning');
            }
        }
    }

    private function createThumbs($image = array()) {
        $return = true;
        $this->thumb_creator->setSource($image);
        for ($i = 0; $i < sizeof($this->_thumb_sizes); $i++) {
            $this->thumb_creator->setThumb($this->_thumb_sizes[$i]);
            if ($this->thumb_creator->resize()) {
                $return = false;
            }
        }
        return $return;
    }

}
