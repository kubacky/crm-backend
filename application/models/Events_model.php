<?php

class Events_model extends Platform_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function createEvent($event = array())
    {
        if (!array_key_exists('ip', $event)) {
            $event['ip'] = get_user_ip();
        }

        if (!array_key_exists('user_id', $event)) {
            $event['user_id'] = $this->u_id;
        }

        if (!array_key_exists('date_add', $event)) {
            $event['date_add'] = $this->date;
        }

        $this->db->insert('events', $event);
    }

    public function getEvents($id, $concerns)
    {
        $this->db->select('event_id as id, title, params, event_type as type, '
            . 'user_id as uId, source_type as sourceType, date_add as date, '
            . 'first_name as firstName, last_name as lastName, concerns, ip');
        $this->db->from('events, users');
        $this->db->where('source_id', $id);
        $this->db->where('concerns', $concerns);
        $this->db->where('users.id = events.user_id');
        $this->db->order_by('date_add', 'DESC');

        $query = $this->db->get();
        $result = $query->result();

        return $this->formatEvents($result);
    }

    public function getEvent($id, $type, $action, $title = null)
    {
        $this->db->select('event_id as id, title, params, event_type as type, '
            . 'user_id as uId, source_type as sourceType, date_add as date, '
            . 'first_name as firstName, last_name as lastName, concerns, ip');
        $this->db->from('events, users');
        $this->db->where('source_id', $id);
        $this->db->where('source_type', $type);
        $this->db->where('event_type', $action);

        if ($title) {
            $this->db->where('title', $title);
        }
        $this->db->where('users.id = events.user_id');

        return $this->getOnce();
    }

    private function formatEvents($events = array())
    {
        $c = count($events);
        for ($i = 0; $i < $c; $i++) {
            $event = $events[$i];
            $date_format = (isCurrentYear($event->date)) ? 'j n' : 'j n Y';

            $event->timestamp = strtotime($event->date);
            $event->day = Date('j', $event->timestamp);
            $event->shortMonth = mb_substr(formatDate($event->date, 'n'), 0, 3);
            $event->fullMonth = formatDate($event->date, 'n');
            $event->hour = Date('H:i', $event->timestamp);
            $event->date = formatDate($event->date, $date_format);
            $event->fullDate = formatDate($event->date, $date_format . ' H:i');
            $event->changes = json_decode($event->params);
            $event->icon = $this->setEventIcon($event->type);
        }
        return $events;
    }

    private function setEventIcon($event_type)
    {
        switch ($event_type) {
            case 'created':
                return 'la-star-o';
                break;
            case 'updated':
                return 'la-pencil-square-o';
                break;
            case 'deleted':
                return 'la-trash-o';
                break;
        }
    }
}
