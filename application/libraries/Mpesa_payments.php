<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * SHULETECH: records a confirmed M-Pesa payment against the student's fees, exactly once.
 * Used both by the parent's browser (polling) and by Safaricom's callback.
 */
class Mpesa_payments
{
    private $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
    }

    public function get($id)
    {
        return $this->CI->db->get_where('mpesa_transactions', array('id' => (int) $id), 1)->row();
    }

    public function getByCheckout($checkoutRequestId)
    {
        return $this->CI->db->get_where('mpesa_transactions', array('checkout_request_id' => (string) $checkoutRequestId), 1)->row();
    }

    public function markFailed($id, $status, $code, $desc)
    {
        $this->CI->db->where('id', (int) $id)->where('status', 'pending')
            ->update('mpesa_transactions', array('status' => $status, 'result_code' => $code, 'result_desc' => substr((string) $desc, 0, 250)));
    }

    /**
     * Book the payment. Returns the fee_deposit_bulk response (array) on success, true if it was
     * already booked earlier, false on failure.
     */
    public function finalize($id, $receipt, $code = '0', $desc = 'Paid')
    {
        $db = $this->CI->db;
        // claim the row: only one caller can move it from pending -> processing
        $db->where('id', (int) $id)->where('status', 'pending')
            ->update('mpesa_transactions', array('status' => 'processing', 'mpesa_receipt' => $receipt, 'result_code' => $code, 'result_desc' => substr((string) $desc, 0, 250)));
        if ($db->affected_rows() !== 1) {
            $row = $this->get($id);
            return ($row && in_array($row->status, array('completed', 'processing'), true)) ? true : false;
        }

        $row    = $this->get($id);
        $params = @unserialize($row->params, array('allowed_classes' => array('stdClass')));
        if (!is_array($params) || empty($params['student_fees_master_array'])) {
            $db->where('id', (int) $id)->update('mpesa_transactions', array('status' => 'error', 'result_desc' => 'Missing payment details'));
            return false;
        }

        $label     = 'M-Pesa receipt ' . $receipt;
        $bulk_fees = array();
        foreach ($params['student_fees_master_array'] as $fee_value) {
            $applied_fee_discount = isset($fee_value['applied_fee_discount']) ? $fee_value['applied_fee_discount'] : 0;
            $bulk_fees[] = array(
                'fee_category'             => $fee_value['fee_category'],
                'student_transport_fee_id' => $fee_value['student_transport_fee_id'],
                'student_fees_master_id'   => $fee_value['student_fees_master_id'],
                'fee_groups_feetype_id'    => $fee_value['fee_groups_feetype_id'],
                'amount_detail'            => array(
                    'amount'                    => $fee_value['amount_balance'] - $applied_fee_discount,
                    'date'                      => date('Y-m-d'),
                    'amount_discount'           => $applied_fee_discount,
                    'processing_charge_type'    => isset($params['processing_charge_type']) ? $params['processing_charge_type'] : null,
                    'gateway_processing_charge' => isset($params['gateway_processing_charge']) ? $params['gateway_processing_charge'] : 0,
                    'amount_fine'               => $fee_value['fine_balance'],
                    'description'               => 'Online fees deposit through ' . $label,
                    'received_by'               => '',
                    'payment_mode'              => 'M-Pesa',
                ),
            );
        }
        $fee_discount_group = isset($params['fee_discount_group']) ? $params['fee_discount_group'] : null;

        $response = $this->CI->studentfeemaster_model->fee_deposit_bulk($bulk_fees, $fee_discount_group);
        if (!$response) {
            $db->where('id', (int) $id)->update('mpesa_transactions', array('status' => 'error', 'result_desc' => 'Paid on M-Pesa but the fee record could not be saved - record it manually'));
            return false;
        }
        $db->where('id', (int) $id)->update('mpesa_transactions', array('status' => 'completed', 'invoice_json' => json_encode($response)));

        try {
            $this->notify($params, $response);
        } catch (Throwable $e) {
            log_message('error', 'M-Pesa receipt notification failed: ' . $e->getMessage());
        }
        return $response;
    }

    /** Same "fee submitted" SMS/e-mail Smart School sends after a Paystack payment. */
    private function notify($params, $response)
    {
        $CI = $this->CI;
        $CI->load->library('mailsmsconf');
        $student_id         = $params['student_id'];
        $student_session_id = $params['student_session_id'];
        $student            = $CI->student_model->get($student_id);

        $fee_group_name = $type = $code = $amount = $fine_type = $due_date = $fine_percentage = $fine_amount = $invoice = $receipt_urls = array();
        $fee_category   = 'fees';
        foreach ($response as $k => $r) {
            $fee_category = $r['fee_category'];
            $invoice[]    = array('invoice_id' => $r['invoice_id'], 'sub_invoice_id' => $r['sub_invoice_id'], 'fee_category' => $fee_category);
            if ($r['student_transport_fee_id'] != 0 && $fee_category == 'transport') {
                $m                 = $CI->studenttransportfee_model->getTransportFeeMasterByStudentTransportID($r['student_transport_fee_id']);
                $fee_group_name[]  = $CI->lang->line('transport_fees');
                $type[]            = $m->month;
                $code[]            = '-';
                $amount[]          = $m->amount;
            } else {
                $m                = $CI->feegrouptype_model->getFeeGroupByIDAndStudentSessionID($r['fee_groups_feetype_id'], $student_session_id);
                $fee_group_name[] = $m->fee_group_name;
                $type[]           = $m->type;
                $code[]           = $m->code;
                $amount[]         = $m->is_system ? $m->balance_fee_master_amount : $m->amount;
            }
            $fine_type[]       = $m->fine_type;
            $due_date[]        = $m->due_date;
            $fine_percentage[] = $m->fine_percentage;
            $fine_amount[]     = $m->fine_amount;
            if (isset($params['student_fees_master_array'][$k])) {
                $f = $params['student_fees_master_array'][$k];
                $receipt_urls[] = base_url() . 'download-receipt/' . encode_receipt_url($r['invoice_id'], $f['fee_category'], $f['student_transport_fee_id'],
                    $f['fee_groups_feetype_id'], $f['student_fees_master_id'], $f['fee_session_group_id'], 'student', $student_id);
            }
        }
        $obj = array(
            'student_id' => $student_id, 'student_session_id' => $student_session_id, 'invoice' => $invoice,
            'contact_no' => $student['guardian_phone'], 'email' => $student['email'], 'parent_app_key' => $student['parent_app_key'],
            'amount' => '(' . implode(',', $amount) . ')', 'fine_type' => '(' . implode(',', $fine_type) . ')', 'due_date' => '(' . implode(',', $due_date) . ')',
            'fine_percentage' => '(' . implode(',', $fine_percentage) . ')', 'fine_amount' => '(' . implode(',', $fine_amount) . ')',
            'fee_group_name' => '(' . implode(',', $fee_group_name) . ')', 'type' => '(' . implode(',', $type) . ')', 'code' => '(' . implode(',', $code) . ')',
            'fee_category' => $fee_category, 'fee_receipt_url' => '(' . implode(', ', $receipt_urls) . ')', 'send_type' => 'group',
        );
        $CI->mailsmsconf->mailsms('fee_submission', $obj);
    }
}
