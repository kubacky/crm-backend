<?php

class Subs_model extends Platform_Model {

    public function __construct() {
        parent::__construct();
    }

    public function getAll() {
        $this->db->select('crm_subscriptions_id as id, subscription_tax_rate_id as taxRateId, '
                . 'subscription_months as months, subscription_name as name, subscription_price as price');
        $this->db->from('subscriptions');
        $this->db->where('flag', 1);
        $this->db->order_by('subscription_name', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }

    public function getMonthly() {
        $this->db->select('crm_clients_id as clientId, client_name as clientName, client_shortcut as clientCode, '
                . 'subscription_name as subName, crm_client_subscriptions.subscription_price as price, '
                . 'subscription_payment_days as days, crm_client_subscriptions.subscription_quantity as quantity, '
                . 'crm_subscriptions_id as subId, subscription_tax_rate_id as taxRateId');
        $this->db->from('clients, subscriptions, client_subscriptions');
        $this->db->where('crm_client_subscriptions.flag', 1);
        $this->db->where('crm_clients_id = subscription_client_id');
        $this->db->where('subscription_months', 1);
        $this->db->where('crm_subscriptions_id = subscription_id');
        $this->db->order_by('client_name', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }

    public function getSubscription($subscription_id) {
        $this->db->select('crm_subscriptions_id as id, subscription_tax_rate_id as taxRateId, '
                . 'subscription_months as months, subscription_name as name, subscription_price as price');
        $this->db->from('subscriptions');
        $this->db->where('crm_subscriptions_id', $subscription_id);
        $this->db->limit(1);
        $query = $this->db->get();
        $result = $query->result();
        return $result[0];
    }

    public function getClientSubscriptions() {
        $this->db->select('crm_client_subscriptions_id as subId, client_name as clientName, client_shortcut as clientCode, '
                . 'subscription_name as subName, crm_client_subscriptions.subscription_price as price, '
                . 'subscription_payment_days as days, crm_client_subscriptions.subscription_quantity as quantity');
        $this->db->from('clients, subscriptions, client_subscriptions');
        $this->db->where('crm_client_subscriptions.flag', 1);
        $this->db->where('crm_clients_id = subscription_client_id');
        $this->db->where('crm_subscriptions_id = subscription_id');
        $this->db->order_by('client_name', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }

    public function getClientSubscription($sub_id) {
        $this->db->select('crm_client_subscriptions_id as subId, client_name as clientName, client_shortcut as clientCode, '
                . 'subscription_name as subName, crm_client_subscriptions.subscription_price as price, '
                . 'subscription_payment_days as days, crm_client_subscriptions.subscription_quantity as quantity');
        $this->db->from('clients, subscriptions, client_subscriptions');
        $this->db->where('crm_client_subscriptions_id', $sub_id);
        $this->db->where('crm_clients_id = subscription_client_id');
        $this->db->where('crm_subscriptions_id = subscription_id');
        $this->db->order_by('client_name', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }

    public function assignSubscription($subscription = array()) {
        $subscription['date_add'] = $this->date;
        $subscription['date_upd'] = $this->date;
        $this->db->insert('client_subscriptions', $subscription);
        return $this->db->insert_id('client_subscriptions');
    }

    public function unsubscribeClient($sub_id) {
        $this->set('flag', 0);
        $this->db->where('crm_client_subscriptions_id', $sub_id);
        $this->db->update('crm_client_subscriptions');
        $this->updateDate('crm_client_subscriptions', $sub_id);
    }

    public function addSubscription($subscription = array()) {
        $subscription['date_add'] = $this->date;
        $subscription['date_upd'] = $this->date;
        $this->db->insert('subscriptions', $subscription);
        return $this->db->insert_id('subscriptions');
    }

    public function updateSubscription($subscription_id, $subscription = array()) {
        $subscription['date_upd'] = $this->date;
        $this->db->set($subscription);
        $this->db->where('crm_subscriptions_id', $subscription_id);
        $this->db->update('subscriptions');
    }

    public function deleteSubscription($subscription_id) {
        $this->db->set('flag', 0);
        $this->db->where('crm_subscriptions_id', $subscription_id);
        $this->db->update('subscriptions');
    }

}
