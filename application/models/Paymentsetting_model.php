<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Paymentsetting_model extends MY_Model {

    public function __construct() {
        parent::__construct();
    }

    /**
     * This funtion takes id as a parameter and will fetch the record.
     * If id is not provided, then it will fetch all the records form the table.
     * @param int $id
     * @return mixed
     */
    public function get() {
        $this->db->select()->from('payment_settings');
        $query = $this->db->get();
        return $query->result();
    }

    public function getActiveMethod() {
        // SHULETECH: Paystack and M-Pesa can both be enabled; prefer a non-M-Pesa row here because
        // the rest of Smart School expects one "card" gateway. M-Pesa is routed separately.
        $this->db->select()->from('payment_settings');
        $this->db->where('is_active', 'yes');
        $this->db->order_by("payment_type = 'mpesa'", 'ASC', false);
        $this->db->limit(1);
        $query = $this->db->get();
        return $query->row();
    }

    /** SHULETECH: settings row for one gateway (or null). */
    public function getByType($type) {
        $query = $this->db->get_where('payment_settings', array('payment_type' => $type), 1);
        return $query->row();
    }

    /** SHULETECH: payment types that are switched on, e.g. ['paystack', 'mpesa']. */
    public function getEnabledTypes() {
        $types = array();
        foreach ($this->db->select('payment_type')->get_where('payment_settings', array('is_active' => 'yes'))->result() as $row) {
            $types[] = $row->payment_type;
        }
        return $types;
    }

    /** SHULETECH: switch on exactly these gateways (others off). */
    public function setEnabledTypes(array $types) {
        $this->db->update('payment_settings', array('is_active' => 'no'));
        if (!empty($types)) {
            $this->db->where_in('payment_type', $types);
            $this->db->update('payment_settings', array('is_active' => 'yes'));
        }
    }

    public function add($data) {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        $this->db->where('payment_type', $data['payment_type']);
        $q = $this->db->get('payment_settings');

        if ($q->num_rows() > 0) {

            $this->db->where('id', $q->row()->id);
            $this->db->update('payment_settings', $data);
            $message = UPDATE_RECORD_CONSTANT . " On payment settings id " . $q->row()->id;
            $action = "Update";
            $record_id = $q->row()->id;
            $this->log($message, $record_id, $action);
            //======================Code End==============================

            $this->db->trans_complete(); # Completing transaction
            /* Optional */

            if ($this->db->trans_status() === false) {
                # Something went wrong.
                $this->db->trans_rollback();
                return false;
            } else {
                //return $return_value;
            }
        } else {

            $this->db->insert('payment_settings', $data);
            $insert_id = $this->db->insert_id();
            $message = INSERT_RECORD_CONSTANT . " On payment settings id " . $insert_id;
            $action = "Insert";
            $record_id = $insert_id;
            $this->log($message, $record_id, $action);
            //======================Code End==============================

            $this->db->trans_complete(); # Completing transaction
            /* Optional */

            if ($this->db->trans_status() === false) {
                # Something went wrong.
                $this->db->trans_rollback();
                return false;
            } else {
                //return $return_value;
            }
        }
    }

    public function valid_paymentsetting() {

        $payment_setting = $this->input->post('payment_setting');
        if ($payment_setting == "none") {
            return true;
        }
        if (!$this->check_data_exists($payment_setting)) {
            $this->form_validation->set_message('paymentsetting', 'Please fill your %s detail');
            return FALSE;
        } else {
            return TRUE;
        }
    }
 
    function check_data_exists($payment_setting) {

        $this->db->where('payment_type', $payment_setting);
        $query = $this->db->get('payment_settings');

        if ($query->num_rows() > 0) {
            return TRUE;
        } else {
            return FALSE;
        }        
    }

    public function active($data, $other = false) {

        if (!$other) {
            $this->db->where('payment_type', $data['payment_type']);
            $this->db->update('payment_settings', $data);
            $data['is_active'] = "no";
            $payment_type = $data['payment_type'];
            unset($data['payment_type']);
            $this->db->where('payment_type !=', $payment_type);
            $this->db->update('payment_settings', $data);
        } else {

            $this->db->update('payment_settings', $data);
        }
    }

    public function payment_gateway_config($data, $other = false) {

        if (!$other) {
            $this->db->where('payment_type', $data['payment_type']);
            $this->db->update('payment_settings', $data);
            $data['charge_type'] = NULL;
            $data['charge_value'] = NULL;
                
            $payment_type = $data['payment_type'];
            unset($data['payment_type']);
            $this->db->where('payment_type !=', $payment_type);
            $this->db->update('payment_settings', $data);
        } else {

            $this->db->update('payment_settings', $data);
        }
    }

}
