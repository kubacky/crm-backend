<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Properties extends Platform
{

    public function __construct()
    {
        parent::__construct();

        $this->load->model('properties_model');
    }

    public function get($action = 'properties', $property_id = null)
    {
        switch ($action) {
            case 'values':
                $this->data = $this->properties_model->getPropertyValues($id);
                break;
            case 'value':
                $this->data = $this->properties_model->getValue($id);
                break;
            case 'required':
                $this->data = $this->getRequiredProperties();
                break;
            case 'property':
                $this->data = $this->properties_model->getProperty($property_id);
                break;
            case 'properties':
            default:
                $this->data = $this->properties_model->getProperties();
        }
        $this->render(null, 'json');
    }

    public function create($action = 'property')
    {
        switch ($action) {
            case 'value':
                $this->addValue();
                break;
            case 'property':
            default:
                $this->addProperty();
        }
    }

    public function update($action = 'property', $id)
    {
        switch ($action) {
            case 'value':
                $this->updateValue($id);
                break;
            case 'property':
            default:
                $this->updateProperty($id);
        }
    }

    public function delete($action = 'property', $id)
    {
        switch ($action) {
            case 'value':
                $this->properties_model->deleteValue($id);
                break;
            case 'property':
            default:
                $this->properties_model->deleteProperty($id);
        }
    }

    private function getRequiredProperties() {
        $properties = $this->properties_model->getRequiredProperties();

        $c = count($properties);
        for($i = 0; $i < $c; $i++) {
            $properties[$i]->values = $this->properties_model->getPropertyValues($properties[$i]->id);
        }

        return $properties;
    }

    private function addProperty()
    {

        $input = json_decode(trim(file_get_contents('php://input')), true);

        $property = [];
        $property['required'] = $input['required'];
        $property['type'] = $input['type'];
        $property['name'] = $input['name'];

        if (!$this->properties_model->getPropertyByName($property['name'])) {
            $this->properties_model->addProperty($property);
        }
    }

    private function addValue()
    {

        $input = json_decode(trim(file_get_contents('php://input')), true);

        $value = [];
        $value['property_id'] = $input['propertyId'];
        $value['value'] = $input['value'];

        $this->properties_model->addValue($value);

    }

    private function updateValue()
    {

        $input = json_decode(trim(file_get_contents('php://input')), true);

        $value_id = $input['valueId'];

        $value = [];
        $value['value'] = $input['value'];

        $this->properties_model->updateValue($value_id, $value);
    }

}
