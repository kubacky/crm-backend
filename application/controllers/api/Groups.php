<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Groups extends Platform
{

    public function __construct()
    {
        parent::__construct();

        $this->load->model('groups_model');
    }

    public function get($type = 'customer')
    {
        $this->data = $this->groups_model->getGroups($type);
        $this->render(null, 'json');
    }

    public function update($type)
    {
        $input = json_decode(trim(file_get_contents('php://input')), true);

        $new = $input['newGroups'];
        $deleted = $input['deletedGroups'];

        $this->deleteGroups($deleted);

        if (array_key_exists('groups', $input)) {
            $this->updateGroups($input['groups']);
        }
        $this->addGroups($type, $new);

        $this->data['status'] = 'ok';
        $this->render(null, 'json');
    }

    private function deleteGroups($groups = array())
    {
        $c = count($groups);
        for ($i = 0; $i < $c; $i++) {
            $this->groups_model->deleteGroup($groups[$i]);
        }
    }

    private function updateGroups($groups)
    {
        if ($groups) {
            foreach ($groups as $group_id => $group_name) {
                $group = [];
                $group['name'] = $group_name;
                $group['flag'] = 1;
                $this->groups_model->updateGroup($group_id, $group);
            }
        }
    }

    private function addGroups($type, $groups = array())
    {
        $c = count($groups);
        for ($i = 0; $i < $c; $i++) {
            $group = [];
            $group['source_type'] = $type;
            $group['name'] = $groups[$i];
            $this->groups_model->addGroup($group);
        }
    }
}
