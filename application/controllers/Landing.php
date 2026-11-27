<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Public marketing landing page for ShuleTech.
 * The school CMS front website is still available at /frontend.
 */
class Landing extends CI_Controller
{
    public function index()
    {
        $this->load->helper('url');

        $logo_file = 'backend/images/shuletech-logo.png';
        $data = array(
            'logo_url'  => file_exists(FCPATH . $logo_file) ? base_url($logo_file) : null,
            'login_url' => site_url('site/login'),
            'user_login_url' => site_url('site/userlogin'),
            'year'      => date('Y'),
        );

        $this->load->view('landing/index', $data);
    }
}
