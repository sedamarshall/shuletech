<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * SHULETECH: Safaricom STK push result callback (public - Safaricom has no session).
 * URL: /mpesa_callback/stk/{txn_id}/{hmac}  (built by Mpesa_lib::callbackUrl)
 * The payload is never trusted on its own: success is re-checked with an STK query.
 */
class Mpesa_callback extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('mpesa_lib', 'mpesa_payments', 'mailsmsconf'));
    }

    private function ack()
    {
        $this->output->set_content_type('application/json')
            ->set_output(json_encode(array('ResultCode' => 0, 'ResultDesc' => 'Accepted')));
    }

    public function stk($txnId = 0, $sig = '')
    {
        if ($this->input->method() !== 'post' || !hash_equals(Mpesa_lib::sign($txnId), (string) $sig)) {
            show_404();
            return;
        }
        $payload = json_decode(file_get_contents('php://input'), true);
        $cb      = isset($payload['Body']['stkCallback']) ? $payload['Body']['stkCallback'] : null;
        $row     = $this->mpesa_payments->get($txnId);
        if (!$cb || !$row || (string) $row->checkout_request_id !== (string) ($cb['CheckoutRequestID'] ?? '')) {
            return $this->ack();
        }

        $code = (string) ($cb['ResultCode'] ?? '');
        $desc = (string) ($cb['ResultDesc'] ?? '');
        $meta = array();
        foreach (($cb['CallbackMetadata']['Item'] ?? array()) as $item) {
            if (isset($item['Name'])) {
                $meta[$item['Name']] = $item['Value'] ?? null;
            }
        }
        $receipt = isset($meta['MpesaReceiptNumber']) ? preg_replace('/[^A-Z0-9]/i', '', (string) $meta['MpesaReceiptNumber']) : '';

        if ($code === '0') {
            // an amount that doesn't match what we asked for is never booked
            if (isset($meta['Amount']) && (int) round((float) $meta['Amount']) !== (int) $row->amount) {
                $this->db->where('id', $row->id)->update('mpesa_transactions', array('result_desc' => 'Amount mismatch in callback: ' . (float) $meta['Amount']));
                return $this->ack();
            }
            if ($row->status === 'pending') {
                $q = $this->mpesa_lib->stkQuery($row->checkout_request_id);
                if ($q['state'] === 'success') {
                    $this->mpesa_payments->finalize($row->id, $receipt ?: substr($row->checkout_request_id, -12), $code, $desc);
                }
            } elseif ($receipt !== '' && in_array($row->status, array('completed', 'processing'), true)) {
                // booked earlier by the browser poll: store the real M-Pesa receipt number
                $this->db->where('id', $row->id)->update('mpesa_transactions', array('mpesa_receipt' => $receipt));
            }
        } else {
            $this->mpesa_payments->markFailed($row->id, $code === '1032' ? 'cancelled' : 'failed', $code, $desc);
        }
        return $this->ack();
    }
}
