<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * SHULETECH: M-Pesa (STK push) fee payments for students/parents, plus the
 * "choose M-Pesa or Paystack" step when both are switched on.
 */
class Mpesa extends Studentgateway_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('mpesa_lib', 'mpesa_payments'));
        $this->setting = $this->setting_model->get();
    }

    private function params()
    {
        $params = $this->session->userdata('params');
        if (empty($params) || empty($params['student_fees_master_array'])) {
            redirect(base_url('user/user/getfees'));
        }
        return $params;
    }

    /** Re-price the session's payment with this gateway's processing charge. */
    private function applyCharge($type)
    {
        $params = $this->params();
        $cfg    = $this->paymentsetting_model->getByType($type);
        $base   = $params['fine_amount_balance'] + $params['total'] - $params['applied_fee_discount'];
        $charge = 0;
        if ($cfg && $cfg->charge_type === 'percentage') {
            $charge = ($base * (float) $cfg->charge_value) / 100;
        } elseif ($cfg && $cfg->charge_type === 'fix') {
            $charge = (float) $cfg->charge_value;
        }
        $params['processing_charge_type']    = $cfg ? $cfg->charge_type : null;
        $params['gateway_processing_charge'] = $charge;
        $params['key']                       = $cfg ? $cfg->api_secret_key : '';
        $this->session->set_userdata('params', $params);
        return $params;
    }

    private function amountFor($params)
    {
        return (float) convertBaseAmountCurrencyFormat($params['fine_amount_balance'] + $params['total'] - $params['applied_fee_discount'] + $params['gateway_processing_charge']);
    }

    private function viewData($params)
    {
        return array(
            'params'       => $params,
            'setting'      => $this->setting,
            'school'       => $this->setting_model->getCurrentSchoolName(),
            'symbol'       => $params['invoice']->symbol,
            'student_data' => $this->student_model->get($params['student_id']),
            'fees'         => $params['student_fees_master_array'],
        );
    }

    /** Both methods on: let the payer pick. */
    public function choose()
    {
        $params           = $this->params();
        $data             = $this->viewData($params);
        $base                                = $params;
        $base['gateway_processing_charge']   = 0;           // each method adds its own fee on the next screen
        $data['amount']   = $this->amountFor($base);
        $data['enabled']  = $this->paymentsetting_model->getEnabledTypes();
        $this->load->view('user/gateway/mpesa/choose', $data);
    }

    public function paystack()
    {
        $this->applyCharge('paystack');
        redirect(base_url('user/gateway/paystack'));
    }

    public function index()
    {
        $params = $this->applyCharge('mpesa');
        if (!$this->mpesa_lib->isConfigured()) {
            $this->session->set_flashdata('error', '<div class="alert alert-danger">M-Pesa is not set up yet. Please contact the school.</div>');
            redirect(base_url('user/user/getfees'));
        }
        $data               = $this->viewData($params);
        $data['amount']     = (int) ceil($this->amountFor($params));
        $data['phone']      = $data['student_data']['guardian_phone'];
        $data['both']       = count(array_intersect(array('mpesa', 'paystack'), $this->paymentsetting_model->getEnabledTypes())) === 2;
        $data['sandbox']    = $this->mpesa_lib->settings()->gateway_mode !== 'live';
        $this->load->view('user/gateway/mpesa/index', $data);
    }

    /** AJAX: send the prompt to the phone. */
    public function stk()
    {
        $this->output->set_content_type('application/json');
        $params = $this->session->userdata('params');
        if (empty($params) || empty($params['student_fees_master_array'])) {
            return $this->output->set_output(json_encode(array('ok' => false, 'error' => 'Your payment session expired. Go back to fees and try again.')));
        }
        $phone = Mpesa_lib::normalisePhone($this->input->post('phone'));
        if ($phone === '') {
            return $this->output->set_output(json_encode(array('ok' => false, 'error' => 'Enter a Safaricom number like 0712 345 678.')));
        }
        $amount = (int) ceil($this->amountFor($params));
        if ($amount < 1) {
            return $this->output->set_output(json_encode(array('ok' => false, 'error' => 'There is nothing to pay.')));
        }
        $student = $this->student_model->get($params['student_id']);
        $ref     = 'ADM' . preg_replace('/\D/', '', (string) $student['admission_no']);

        $this->db->insert('mpesa_transactions', array(
            'student_id'         => (int) $params['student_id'],
            'student_session_id' => (int) $params['student_session_id'],
            'phone'              => $phone,
            'amount'             => $amount,
            'account_ref'        => substr($ref ?: 'FEES', 0, 12),
            'status'             => 'pending',
            'params'             => serialize($params),
        ));
        $txnId = (int) $this->db->insert_id();

        $r = $this->mpesa_lib->stkPush($phone, $amount, $ref ?: 'FEES', 'School fees', $this->mpesa_lib->callbackUrl($txnId));
        if (!$r['ok']) {
            $this->db->where('id', $txnId)->update('mpesa_transactions', array('status' => 'failed', 'result_desc' => substr($r['error'], 0, 250)));
            return $this->output->set_output(json_encode(array('ok' => false, 'error' => $r['error'])));
        }
        $this->db->where('id', $txnId)->update('mpesa_transactions', array(
            'merchant_request_id' => $r['merchant_request_id'], 'checkout_request_id' => $r['checkout_request_id'],
        ));
        $this->session->set_userdata('mpesa_txn', $txnId);
        return $this->output->set_output(json_encode(array('ok' => true, 'txn' => $txnId, 'message' => 'Check your phone and enter your M-Pesa PIN.')));
    }

    /** AJAX: has the payer confirmed? */
    public function status($txnId = 0)
    {
        $this->output->set_content_type('application/json');
        $row = $this->mpesa_payments->get($txnId);
        $me  = $this->session->userdata('params');
        if (!$row || !$me || (int) $row->student_id !== (int) $me['student_id']) {
            return $this->output->set_output(json_encode(array('state' => 'failed', 'message' => 'Payment not found.')));
        }
        if ($row->status === 'pending' && $row->checkout_request_id) {
            $q = $this->mpesa_lib->stkQuery($row->checkout_request_id);
            if ($q['state'] === 'success') {
                // the callback carries the M-Pesa receipt number; until it arrives, keep the request id as reference
                $this->mpesa_payments->finalize($row->id, $row->mpesa_receipt ?: substr($row->checkout_request_id, -12), $q['code'], $q['desc']);
            } elseif (in_array($q['state'], array('failed', 'cancelled'), true)) {
                $this->mpesa_payments->markFailed($row->id, $q['state'], $q['code'], $q['desc']);
            }
            $row = $this->mpesa_payments->get($txnId);
        }
        switch ($row->status) {
            case 'completed':
            case 'processing':
                $out = array('state' => 'success', 'redirect' => base_url('user/gateway/payment/successinvoice'));
                break;
            case 'cancelled':
                $out = array('state' => 'failed', 'message' => 'You cancelled the M-Pesa prompt. You can send it again.');
                break;
            case 'failed':
                $out = array('state' => 'failed', 'message' => $this->friendly($row->result_code, $row->result_desc));
                break;
            case 'error':
                $out = array('state' => 'failed', 'message' => 'Your M-Pesa payment went through but we could not record it automatically. The school has been notified - please keep your M-Pesa message.');
                break;
            default:
                $out = array('state' => 'pending', 'age' => time() - strtotime($row->created_at));
        }
        return $this->output->set_output(json_encode($out));
    }

    private function friendly($code, $desc)
    {
        $map = array(
            '1'    => 'Your M-Pesa balance is too low for this payment.',
            '1032' => 'You cancelled the M-Pesa prompt.',
            '1037' => 'Your phone could not be reached. Make sure it is on and try again.',
            '2001' => 'Wrong M-Pesa PIN. Please try again.',
            '1019' => 'The prompt expired before it was confirmed. Please try again.',
        );
        return isset($map[(string) $code]) ? $map[(string) $code] : ($desc ?: 'The payment did not go through.');
    }
}
