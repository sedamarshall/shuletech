<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * SHULETECH: Safaricom Daraja "Lipa na M-Pesa Online" (STK push) client.
 *
 * Settings come from payment_settings (payment_type = 'mpesa'):
 *   api_username        consumer key
 *   api_secret_key      consumer secret
 *   api_publishable_key business short code (paybill, or the store number for a till)
 *   api_password        Lipa na M-Pesa passkey
 *   salt                till number (Buy Goods only; empty for paybill)
 *   api_signature       transaction type: CustomerPayBillOnline | CustomerBuyGoodsOnline
 *   gateway_mode        sandbox | live
 */
class Mpesa_lib
{
    private $CI;
    private $cfg;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->model('paymentsetting_model');
        $this->cfg = $this->CI->paymentsetting_model->getByType('mpesa');
    }

    public function isConfigured()
    {
        return $this->cfg && $this->cfg->api_username !== '' && $this->cfg->api_secret_key !== ''
            && $this->cfg->api_publishable_key !== '' && $this->cfg->api_password !== '';
    }

    public function settings()
    {
        return $this->cfg;
    }

    private function baseUrl()
    {
        return ($this->cfg && $this->cfg->gateway_mode === 'live') ? 'https://api.safaricom.co.ke' : 'https://sandbox.safaricom.co.ke';
    }

    /** Normalise 07XXXXXXXX / 01XXXXXXXX / +2547... / 2547... to 2547XXXXXXXX (or 2541...). Returns '' if invalid. */
    public static function normalisePhone($phone)
    {
        $p = preg_replace('/\D+/', '', (string) $phone);
        if (strlen($p) === 10 && $p[0] === '0') {
            $p = '254' . substr($p, 1);
        } elseif (strlen($p) === 9 && ($p[0] === '7' || $p[0] === '1')) {
            $p = '254' . $p;
        }
        return preg_match('/^254[17]\d{8}$/', $p) ? $p : '';
    }

    private function request($method, $path, $body = null, $token = null)
    {
        $ch = curl_init($this->baseUrl() . $path);
        $headers = ['Accept: application/json'];
        if ($token) {
            $headers[] = 'Authorization: Bearer ' . $token;
        } else {
            $headers[] = 'Authorization: Basic ' . base64_encode($this->cfg->api_username . ':' . $this->cfg->api_secret_key);
        }
        if ($body !== null) {
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        $raw  = curl_exec($ch);
        $err  = curl_error($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($raw === false) {
            return ['ok' => false, 'http' => 0, 'error' => 'Could not reach Safaricom: ' . $err, 'data' => null];
        }
        $data = json_decode($raw, true);
        return ['ok' => $code >= 200 && $code < 300, 'http' => $code, 'error' => is_array($data) ? ($data['errorMessage'] ?? ($data['ResponseDescription'] ?? '')) : substr($raw, 0, 200), 'data' => $data];
    }

    /** OAuth access token (cached in the session for 50 minutes). */
    public function token()
    {
        $cacheKey = 'mpesa_token_' . md5($this->cfg->api_username . $this->cfg->gateway_mode);
        $cached   = $this->CI->session->userdata($cacheKey);
        if (is_array($cached) && $cached['exp'] > time()) {
            return $cached['token'];
        }
        $r = $this->request('GET', '/oauth/v1/generate?grant_type=client_credentials');
        if (!$r['ok'] || empty($r['data']['access_token'])) {
            return null;
        }
        $this->CI->session->set_userdata($cacheKey, ['token' => $r['data']['access_token'], 'exp' => time() + 3000]);
        return $r['data']['access_token'];
    }

    private function password($timestamp)
    {
        return base64_encode($this->cfg->api_publishable_key . $this->cfg->api_password . $timestamp);
    }

    /** Send the payment prompt to the payer's phone. */
    public function stkPush($phone, $amount, $accountRef, $description, $callbackUrl)
    {
        $token = $this->token();
        if (!$token) {
            return ['ok' => false, 'error' => 'M-Pesa authentication failed. Check the consumer key and secret.'];
        }
        $ts      = date('YmdHis');
        $buyGoods = $this->cfg->api_signature === 'CustomerBuyGoodsOnline';
        $partyB  = ($buyGoods && trim((string) $this->cfg->salt) !== '') ? trim($this->cfg->salt) : $this->cfg->api_publishable_key;
        $body = [
            'BusinessShortCode' => $this->cfg->api_publishable_key,
            'Password'          => $this->password($ts),
            'Timestamp'         => $ts,
            'TransactionType'   => $buyGoods ? 'CustomerBuyGoodsOnline' : 'CustomerPayBillOnline',
            'Amount'            => (int) $amount,
            'PartyA'            => $phone,
            'PartyB'            => $partyB,
            'PhoneNumber'       => $phone,
            'CallBackURL'       => $callbackUrl,
            'AccountReference'  => substr(preg_replace('/[^A-Za-z0-9]/', '', $accountRef), 0, 12),
            'TransactionDesc'   => substr($description, 0, 13),
        ];
        $r = $this->request('POST', '/mpesa/stkpush/v1/processrequest', $body, $token);
        if ($r['ok'] && isset($r['data']['ResponseCode']) && (string) $r['data']['ResponseCode'] === '0') {
            return ['ok' => true, 'merchant_request_id' => $r['data']['MerchantRequestID'], 'checkout_request_id' => $r['data']['CheckoutRequestID'],
                    'message' => $r['data']['CustomerMessage'] ?? ''];
        }
        return ['ok' => false, 'error' => $r['error'] ?: 'M-Pesa did not accept the request.'];
    }

    /**
     * Ask Safaricom for the outcome of a prompt.
     * Returns state: 'success' | 'failed' | 'cancelled' | 'pending', plus code/desc.
     */
    public function stkQuery($checkoutRequestId)
    {
        $token = $this->token();
        if (!$token) {
            return ['state' => 'pending', 'code' => null, 'desc' => 'auth'];
        }
        $ts = date('YmdHis');
        $r  = $this->request('POST', '/mpesa/stkpushquery/v1/query', [
            'BusinessShortCode' => $this->cfg->api_publishable_key,
            'Password'          => $this->password($ts),
            'Timestamp'         => $ts,
            'CheckoutRequestID' => $checkoutRequestId,
        ], $token);
        $d = $r['data'];
        if (is_array($d) && isset($d['ResultCode'])) {
            $code = (string) $d['ResultCode'];
            $desc = $d['ResultDesc'] ?? '';
            if ($code === '0') {
                return ['state' => 'success', 'code' => $code, 'desc' => $desc];
            }
            if ($code === '1032') {
                return ['state' => 'cancelled', 'code' => $code, 'desc' => $desc];
            }
            return ['state' => 'failed', 'code' => $code, 'desc' => $desc];
        }
        // "The transaction is being processed" comes back as an error until the payer responds
        return ['state' => 'pending', 'code' => null, 'desc' => is_array($d) ? ($d['errorMessage'] ?? '') : ''];
    }

    /** Signed callback URL so Safaricom callbacks can't be forged for other transactions. */
    public function callbackUrl($txnId)
    {
        return base_url('mpesa_callback/stk/' . (int) $txnId . '/' . self::sign($txnId));
    }

    public static function sign($txnId)
    {
        $CI = &get_instance();
        $CI->config->load('shuletech_security', true);
        $key = (string) $CI->config->item('link_signing_key', 'shuletech_security');
        return substr(hash_hmac('sha256', 'mpesa-callback|' . (int) $txnId, $key), 0, 32);
    }
}
