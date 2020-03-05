<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Thumb_creator {

    private $_source = array();
    private $_thumb = array();

    public function __construct() {
        defined('DS') || define('DS', DIRECTORY_SEPARATOR);
        defined('UPLOAD_PATH') || define('UPLOAD_PATH', FCPATH . '..' . DS . 'img'
                        . DS . 'upload' . DS);

        $this->_thumb['quantity'] = 90;
    }

    public function setSource($source) {
        $this->_source['width'] = $source['width'];
        $this->_source['height'] = $source['height'];
        $this->_source['ratio'] = $source['width'] / $source['height'];
        $this->_source['path'] = $source['file_path'];
        $this->_source['name'] = $source['filename'];
        $this->_source['type'] = $source['type'];
    }

    public function setThumb($data = array()) {
        //array(szerokość, wysokość, pozostaw proporcje, folder docelowy)
        $this->_thumb['width'] = $data[0];
        $this->_thumb['height'] = $data[1];
        $this->_thumb['ratio'] = $data[0] / $data[1];
        $this->_thumb['maintain_ratio'] = $data[2];
        $this->_thumb['path'] = UPLOAD_PATH . $data[3]
                . DS . $this->_source['name'];
    }

    public function resize() {
        switch ($this->_source['type']) {
            case 'image/png':
                $this->_thumb['blank'] = imagecreatefrompng($this->_source['path']);
                break;
            case 'image/gif':
                $this->_thumb['blank'] = imagecreatefromgif($this->_source['path']);
                break;
            case 'image/jpeg':
            default:
                $this->_thumb['blank'] = imagecreatefromjpeg($this->_source['path']);
        }

        if ($this->_thumb['width'] > $this->_source['width'] * 1.2 || $this->_thumb['height'] > $this->_source['height'] * 1.2) {
            return false;
        }

        //proporcja SZEROKOSCI oryginalnego obrazu do szerokosci miniaturki
        $this->_thumb['width_ratio'] = $this->_source['width'] / $this->_thumb['width'];

        //proporcja WYSOKOSCI oryginalnego obrazu do szerokosci miniaturki
        $this->_thumb['height_ratio'] = $this->_source['height'] / $this->_thumb['height'];

        /*
         * MINIATURA ZAWIERA JEDYNIE FRAGMENT ORYGINALNEGO OBRAZU
         * WIELKOSC TEGO FRAGMENTU WYLICZAMY Z PROPORCJI
         * 
         * JEZELI PROPORCJE ORYGINALNEGO OBRAZU I MINIATURKI SA TAKIE SAME
         * PO PROSTU USTALAMY WYMAGANE PARAMERTY PRZY TWORZENIU MINIATURY
         */

        //pozycje początkowe i wymiary maski
        $this->_source['mask_x'] = 0;
        $this->_source['mask_y'] = 0;
        $this->_source['mask_width'] = $this->_source['width'];
        $this->_source['mask_height'] = $this->_source['height'];

        //jezeli proporcje maja zostac oryginalne albo sa takie same, ustala gotowe dane do zmiany rozmiaru
        if ($this->_source['ratio'] == $this->_thumb['ratio'] || $this->_thumb['maintain_ratio'] == true) {
            //obraz poziomy
            if ($this->_source['ratio'] >= 1) {
                $this->_thumb['height'] = $this->_source['height'] / $this->_thumb['width_ratio'];
            }

            //obraz pionowy
            if ($this->_source['ratio'] < 1) {
                $this->_thumb['width'] = $this->_source['width'] / $this->_thumb['height_ratio'];
            }
        } else {
            //jezeli miniatura jest w pionie a oryginal w poziomie 
            //lub proprcja obrazu jest wieksza od miniatury 
            //(unikamy sytuacji gdy po przeskalowaniu wysokosc obrazu jest mniejsza od wysokosci miniatury)
            if ($this->_source['ratio'] > 1 && $this->_thumb['ratio'] < 1 || $this->_source['ratio'] > $this->_thumb['ratio']) {
                $this->_source['mask_width'] = $this->_thumb['width'] * $this->_thumb['height_ratio'];
                $this->_source['mask_x'] = ($this->_source['width'] - $this->_source['mask_width']) / 2;
            } else if ($this->_source['ratio'] > 1 && $this->_thumb['ratio'] > 1) {

                if ($this->_source['ratio'] < $this->_thumb['ratio']) {
                    $this->_source['mask_height'] = $this->_thumb['height'] * $this->_thumb['width_ratio'];
                    $this->_source['mask_y'] = ($this->_source['height'] - $this->_source['mask_height']) / 2;
                } else {
                    $this->_source['mask_width'] = $this->_thumb['width'] * $this->_thumb['height_ratio'];
                    $this->_source['mask_x'] = ($this->_source['width'] - $this->_source['mask_width']) / 2;
                }
            } else {
                $this->_source['mask_height'] = $this->_thumb['height'] * $this->_thumb['width_ratio'];
                $this->_source['mask_y'] = ($this->_source['height'] - $this->_source['mask_height']) / 2;
            }
        }

        //utworzenie pustego CZARNEGO obrazu o wymiarach miniaturki
        $this->_thumb['destination'] = imagecreatetruecolor($this->_thumb['width'], $this->_thumb['height']);

        //biale tlo dla plikow innych niz jpg
        if ($this->_source['type'] != 'jpg') {
            $white = imagecolorallocate($this->_thumb['destination'], 255, 255, 255);
            imagefilledrectangle($this->_thumb['destination'], 0, 0, $this->_thumb['width'], $this->_thumb['height'], $white);
        }

        $create = imagecopyresampled($this->_thumb['destination'], $this->_thumb['blank'], 0, 0, $this->_source['mask_x'], $this->_source['mask_y'], $this->_thumb['width'], $this->_thumb['height'], $this->_source['mask_width'], $this->_source['mask_height']);
        if ($create == true) {
            switch ($this->_source['type']) {
                case 'image/png':
                    imagepng($this->_thumb['destination'], $this->_thumb['path'], 8);
                    break;
                case 'image/gif':
                    imagegif($this->_thumb['destination'], $this->_thumb['path'], 100);
                    break;
                case 'image/jpeg':
                default:
                    imagejpeg($this->_thumb['destination'], $this->_thumb['path'], $this->_thumb['quantity']);
            }
        } else {
            return false;
        }
    }

}
