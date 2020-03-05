<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Notes extends Platform
{

    public function __construct()
    {
        parent::__construct();

        $this->load->model('notes_model');
        $this->load->library('note');

        $this->load->library('event');
    }

    public function get($action = 'notes', $type = 'customer', $id = null)
    {
        switch ($action) {
            case 'note':
                $this->data = $this->notes_model->getNote($id);
                break;
            case 'notes':
            default:
                $this->data = $this->notes_model->getNotes($id, $type);
        }
        $this->render(null, 'json');
    }

    public function save()
    {
        $input = $this->getInput();

        if (array_key_exists('id', $input)) {
            $this->update($input['id']);
        } else {
            $this->create($input['type'], $input['sourceId']);
        }

    }

    public function create($type, $source_id)
    {

        $input = $this->getInput();

        $note = $this->note->prepareNote($source_id, $type, $input, true);
        $id = $this->notes_model->addNote($note);

        if ($id) {
            $params = json_encode($note);
            $this->event->create($source_id, 'note', $type, 'created', NOTE_CREATED, $params);

            $this->data['id'] = $id;
            $this->data['status'] = 'OK';
            $this->render(null, 'json');
        }
    }

    public function update($note_id)
    {

        $input = $this->getInput();

        $previous = $this->notes_model->getNote($note_id);

        $note = $this->note->prepareNote($previous->source_id, $previous->source_type, $input, true);
        $this->notes_model->updateNote($note_id, $note);

        $current = $this->notes_model->getNote($note_id);
        $params = ['before' => $previous, 'after' => $current];

        $this->event->create($previous->source_id, 'note', $previous->source_type, 'updated', NOTE_UPDATED, json_encode($params));

        $this->data['id'] = $note_id;
        $this->data['status'] = 'OK';
        $this->render(null, 'json');

    }

    public function delete($note_id)
    {
        $note = $this->notes_model->getNote($note_id);
        $this->notes_model->deleteNote($note_id);

        $params = json_encode($note);
        $this->event->create($note->source_id, 'note', $note->source_type, 'deleted', NOTE_DELETED, $params);
    }

}
