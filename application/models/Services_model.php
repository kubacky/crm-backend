<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Services_model extends Platform_Model
{

    private $type = [];
    private $status = [];

    public function __construct()
    {
        parent::__construct();

        $this->type = [
            'Mobilny - u klienta', 'Stacjonarny',
        ];
        $this->status = [
            'Rozpoczęty', 'Zakończony',
        ];
        //$this->u_id = $this->ion_auth->get_user_id();
    }

    public function addService($service = array())
    {
        $service['date_add'] = $this->date;
        $service['date_upd'] = $this->date;
        $this->db->insert('services', $service);
        return $this->db->insert_id('services');
    }

    public function getServices($id, $type = 'customer')
    {
        $column = 'customer_id';
        if ($type == 'device') {
            $column = 'device_id';
        }

        $this->db->select('service_id as id, technician_id as technicianId, '
            . 'type_id as typeId, status_id as statusId, device_id as deviceId, price, '
            . 'service_date as date, comment, date_end as dateEnd');
        $this->db->from('services');
        $this->db->where($column, $id);
        $this->db->where('flag', 1);
        $this->db->order_by('service_date', 'DESC');

        $query = $this->db->get();
        $result = $query->result();

        $c = count($result);
        for ($i = 0; $i < $c; $i++) {
            $result[$i] = $this->formatService($result[$i]);
        }

        return $result;
    }

    public function getService($service_id)
    {
        $this->db->select('service_id as id, services.technician_id as technicianId, '
            . 'type_id as typeId, status_id as statusId, device_id as deviceId, price, '
            . 'service_date as date, comment, service_date, date_end as dateEnd, '
            . 'technicians.name as tech');
        $this->db->from('services, technicians');
        $this->db->where('service_id', $service_id);
        $this->db->where('technicians.technician_id = services.technician_id');
        $this->db->limit(1);

        $service = $this->getOnce();

        if ($service) {
            return $this->formatService($service);
        }
        return false;
    }

    public function getLastService($device_id)
    {
        $this->db->select('service_id as id');
        $this->db->from('services');
        $this->db->where('device_id', $device_id);
        $this->db->where('flag', 1);
        $this->db->order_by('service_date', 'DESC');

        $id = $this->getOnce();

        if ($id) {
            return $id->id;
        }
        return false;
    }

    public function updateService($service_id, $service = array())
    {
        $service['date_upd'] = $this->date;
        $this->db->set($service);
        $this->db->where('service_id', $service_id);
        $this->db->update('services');
    }

    public function deleteService($service_id)
    {
        $upd = [];
        $upd['flag'] = 0;
        $this->updateService($service_id, $upd);
    }

    public function addTechnician($technician = array())
    {
        $tech = $this->getTechnicianByName($technician['name']);
        if (!$tech) {
            $this->db->insert('technicians', $technician);
            return $this->db->insert_id('technicians');
        } else {
            return $tech->id;
        }

    }

    public function findTechnicians($query)
    {
        $this->db->select('technician_id as id, name');
        $this->db->from('technicians');
        $this->db->like('name', $query);
        $this->db->order_by('name', 'ASC');

        $query = $this->db->get();
        return $query->result();
    }

    public function getTechnicians()
    {
        $this->db->select('technician_id as id, name');
        $this->db->from('technicians');
        $this->db->order_by('name', 'ASC');
        $this->db->where('flag', 1);

        $query = $this->db->get();
        return $query->result();
    }

    public function getTechnician($technician_id)
    {
        $this->db->select('technician_id as id, name');
        $this->db->from('technicians');
        $this->db->where('technician_id', $technician_id);
        $this->db->limit(1);

        return $this->getOnce();
    }

    public function getServicesCountByYear($year) {
        $this->db->select('month(service_date) as month, count(service_id) as count');
        $this->db->from('services');
        $this->db->where('year(service_date)', $year);
        $this->db->group_by('month(service_date)');
        $this->db->order_by('service_date', 'ASC');

        $query = $this->db->get();
        $result = $query->result();

        $counts = $this->formatServicesCount($result);
        return $this->getJustServiceCounts($counts);
    }

    public function getTechnicianByName($name)
    {
        $this->db->select('technician_id as id, name');
        $this->db->from('technicians');
        $this->db->where('name', $name);
        $this->db->limit(1);

        return $this->getOnce();
    }

    public function deleteTechnician($technician_id)
    {
        $this->db->where('technician_id', $technician_id);
        $this->db->delete('technicians');
    }

    //zwraca tablice w formie [miesiac: ilosc]
    private function formatServicesCount($result) {
        $counts = [];

        $c = count($result);
        for($i = 0; $i < $c; $i++) {
            $counts[intval($result[$i]->month)] = intval($result[$i]->count);
        }
        for($j = 1; $j <=12; $j++) {
            if(!array_key_exists($j, $counts)) {
                $counts[$j] = 0;
            }
        }
        ksort($counts);

        return $counts;
    }

    private function getJustServiceCounts($counts) {
        $return = [];
        for($i = 1; $i <= 12; $i++) {
            $return[] = $counts[$i];
        }
        return $return;
    }

    private function formatService($service)
    {
        $s_id = $service->statusId;
        $t_id = $service->typeId;
        $tech_id = $service->technicianId;

        $service->status = $this->status[$s_id - 1];
        $service->type = $this->type[$t_id - 1];
        $service->technician = $this->getTechnician($tech_id);

        $service->isoDate = Date('c', strtotime($service->date));

        $date = $service->date;
        if ($service->dateEnd != null) {
            $service->isoDateEnd = Date('c', strtotime($service->dateEnd));
            $date = $service->dateEnd;
        }

        $service->date = formatDate($date, 'j n Y');

        return $service;
    }
}
