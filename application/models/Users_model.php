<?php

class Users_model extends Platform_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getUserMenu($user_id = null)
    {
        if ($user_id === null) {
            $user_id = $this->u_id;
        }

        $modules = $this->getUserModules($user_id, 0);

        $c = count($modules);
        for ($i = 0; $i < $c; $i++) {
            $modules[$i]->active = false;
            $modules[$i]->state = 'inactive';
            $modules[$i]->group = $this->getUserSubmodules($modules[$i]->id);
        }
        return $modules;
    }

    public function getUserModules($user_id, $parent_id = 0)
    {
        $this->db->select('crm_modules_id as id, module_name as name, module_path as path, module_icon as icon');
        $this->db->from('modules, user_access');
        $this->db->where('crm_user_access.user_id', $user_id);
        $this->db->where('crm_modules.flag', 1);
        $this->db->where('crm_user_access.flag', 1);
        $this->db->where('crm_modules_id = module_id');
        $this->db->where('module_parent_id', $parent_id);
        $this->db->order_by('module_order', 'ASC');

        $query = $this->db->get();
        return $query->result();
    }

    public function getUserSubmodules($parent_id = 0)
    {
        $this->db->select('crm_modules_id as id, module_name as name, module_path as path, module_icon as icon');
        $this->db->from('modules');
        $this->db->where('crm_modules.flag', 1);
        $this->db->where('module_parent_id', $parent_id);
        $this->db->order_by('module_order', 'ASC');

        $query = $this->db->get();
        return $query->result();
    }

    public function getModules()
    {
        $this->db->select('crm_modules_id as id, module_name as name');
        $this->db->from('modules');
        $this->db->where('module_parent_id', 0);
        $this->db->where('flag', 1);
        $this->db->order_by('module_order', 'ASC');

        $query = $this->db->get();
        return $query->result();
    }

    public function assignModules($user_id, $modules = array())
    {

        $this->clearModules($user_id);
        
        $gr = [];
        $gr['user_id'] = $user_id;
        $gr['moderator_id'] = $this->u_id;

        $c = count($modules);
        for ($i = 0; $i < $c; $i++) {
            $gr['module_id'] = $modules[$i];
            $this->db->insert('user_access', $gr);
        }
    }

    public function clearModules($user_id) {

        $this->db->where('user_id', $user_id);
        $this->db->delete('user_access');
    }

}
