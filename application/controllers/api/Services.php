<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Services extends Platform
{

    public function __construct()
    {
        parent::__construct();

        $this->load->model('services_model');
        $this->load->model('devices_model');
        $this->load->library('service');
        $this->load->library('event');
    }

    public function find($type = 'services')
    {
        switch ($type) {
            case 'technician':
                $this->data = $this->findTechnicians();
                break;
            case 'services':
            default:
                //$this->data = $this->services_model->getProductSets();
        }

        $this->render(null, 'json');
    }

    public function getServicesCount($year = '')
    {
        if ($year == '') {
            $year = date('Y');
        }
        $this->data = $this->services_model->getServicesCountByYear($year);
        $this->render(null, 'json');
    }

    public function get($action = 'services', $id = null)
    {
        switch ($action) {
            case 'service':
                $this->getService($id);
                break;
            case 'technicians':
                $this->getTechnicians();
                break;
            case 'services':
            default:
                $this->getServices($id);
        }
        $this->render(null, 'json');
    }

    public function save()
    {
        $input = $this->getInput();

        if (array_key_exists('id', $input)) {
            $this->update($input['id']);
        } else {
            $this->create();
        }
        $this->render(null, 'json');
    }

    private function create()
    {
        $input = $this->getInput();

        $devices = explode('|', $input['devices']);
        $this->service->prepareService($input);

        $c = count($devices);
        for ($i = 0; $i < $c; $i++) {
            $ids = explode(':', $devices[$i]);
            $this->service->assignDevice($ids[0]);
            $this->service->assignCustomer($ids[1]);

            $this->saveService(true);
        }
    }

    private function update($service_id)
    {
        $input = $this->getInput();
        $this->service->prepareService($input);

        $this->saveService(false, $service_id);

    }

    public function delete($service_id)
    {
        $this->services_model->deleteService($service_id);

        $service = $this->services_model->getService($service_id);

        $this->event->create($service->deviceId, 'service', 'device', 'deleted', SERVICE_DELETED, json_encode($service));

        $d_id = $service->deviceId;
        $this->setDeviceServices($d_id);

        $this->data['status'] = 'OK';
        $this->render(null, 'json');
    }

    private function saveService($new = true, $service_id = null)
    {
        $input = $this->getInput();

        $technician_id = $input['technicianId'];

        $this->service->assignTechnician($technician_id);
        $service = $this->service->get();
        
        if ($new) {
            $service_id = $this->services_model->addService($service);

            $srv = $this->services_model->getService($service_id);
            $this->event->create($srv->deviceId, 'service', 'device', 'created', SERVICE_CREATED, json_encode($srv));
        } else {
            $prev = $this->services_model->getService($service_id);

            $this->services_model->updateService($service_id, $service);

            $curr = $this->services_model->getService($service_id);
            $params = $this->compare($prev, $curr);

            $this->service->assignDevice($curr->deviceId);

            $this->event->create($curr->deviceId, 'service', 'device', 'updated', SERVICE_UPDATED, json_encode($params));
        }

        $service = $this->service->get();
        $this->setDeviceServices($service['device_id']);

        $this->data['id'] = $service_id;
        $this->data['status'] = 'OK';
    }

    private function getService($service_id)
    {
        $this->data = $this->services_model->getService($service_id);
    }

    private function setDeviceServices($device_id)
    {
        $upd = [];
        $upd['last_service_date'] = null;
        $upd['next_service_date'] = null;

        $last_service = $this->services_model->getLastService($device_id);
        $device = $this->devices_model->getDevice($device_id);

        if ($last_service) {
            $service = $this->services_model->getService($last_service);

            $days = countNextDays($device->servicePeriod);

            $next = strtotime($service->service_date . $days);

            $upd['last_service_date'] = $service->service_date;
            $upd['next_service_date'] = Date('Y-m-d', $next);
        }

        $this->devices_model->updateDevice($device_id, $upd);
    }

    private function getTechnicians()
    {
        $this->data = $this->services_model->getTechnicians();
    }

    private function findTechnicians()
    {

        $input = $this->getInput();
        $query = $input['query'];

        return $this->services_model->findTechnicians($query);
    }

    private function compare($previous, $current)
    {
        $previous = (array) $previous;
        $current = (array) $current;
        $differents = [];

        $this->lang->load('compare_service', 'polish');
        foreach ($previous as $key => $value) {
            if ($value !== $current[$key] && $this->lang->line($key)) {
                $differents[] = ['name' => $this->lang->line($key), 'before' => $value, 'after' => $current[$key]];
            }
        }

        return $differents;
    }
}
