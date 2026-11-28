<?php
/*
 | SHULETECH: payment methods - Paystack + M-Pesa only.
 | The original Smart School page (27 gateways) is kept in backup/paymentsettingList.php.all-gateways
 */
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
$paystack = null;
$mpesa    = null;
foreach ($paymentlist as $row) {
    if ($row->payment_type === 'paystack') { $paystack = $row; }
    if ($row->payment_type === 'mpesa') { $mpesa = $row; }
}
$enabled  = array();
foreach ($paymentlist as $row) {
    if ($row->is_active === 'yes' && in_array($row->payment_type, array('paystack', 'mpesa'), true)) { $enabled[] = $row->payment_type; }
}
$otherActive = array();
foreach ($paymentlist as $row) {
    if ($row->is_active === 'yes' && !in_array($row->payment_type, array('paystack', 'mpesa'), true)) { $otherActive[] = $row->payment_type; }
}
$canEdit = $this->rbac->hasPrivilege('payment_methods', 'can_edit');
$v = function ($obj, $field) { return $obj && isset($obj->$field) ? html_escape($obj->$field) : ''; };
$school_currency = $this->db->select('c.short_name')->from('sch_settings s')->join('currencies c', 'c.id = s.currency', 'left')->get()->row();
$is_kes = $school_currency && strtoupper((string) $school_currency->short_name) === 'KES';
?>
<style>
    .st-pay-grid { display: grid; grid-template-columns: minmax(0, 1fr) 320px; gap: 20px; align-items: start; }
    @media (max-width: 1199px) { .st-pay-grid { grid-template-columns: 1fr; } }
    .st-pay-tabs { display: inline-flex; gap: 4px; padding: 4px; border-radius: 10px; background: var(--muted); margin: 0 0 18px; }
    .st-pay-tabs button { border: 0; background: transparent; color: var(--muted-foreground); font-weight: 500; font-size: 13.5px; padding: 7px 16px; border-radius: 7px; display: inline-flex; align-items: center; gap: 8px; }
    .st-pay-tabs button.active { background: var(--card); color: var(--foreground); box-shadow: var(--shadow-sm); }
    .st-pay-pane { display: none; }
    .st-pay-pane.active { display: block; }
    .st-field { margin-bottom: 16px; }
    .st-field label { display: block; margin-bottom: 6px; }
    .st-field .hint { color: var(--muted-foreground); font-size: 12.5px; margin-top: 5px; }
    .st-field .err { color: var(--destructive); font-size: 12.5px; margin-top: 4px; display: block; min-height: 0; }
    .st-row2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    @media (max-width: 767px) { .st-row2 { grid-template-columns: 1fr; } }
    .st-seg { display: inline-flex; border: 1px solid var(--input); border-radius: 9px; overflow: hidden; }
    .st-seg label { margin: 0 !important; padding: 7px 14px; font-size: 13px !important; cursor: pointer; color: var(--muted-foreground); border-right: 1px solid var(--input); font-weight: 500 !important; }
    .st-seg label:last-child { border-right: 0; }
    .st-seg input { display: none; }
    .st-seg input:checked + span { color: var(--foreground); }
    .st-seg label:has(input:checked) { background: var(--accent); color: var(--foreground); }
    .st-method { display: flex; align-items: center; gap: 12px; padding: 14px; border: 1px solid var(--border); border-radius: 12px; margin-bottom: 10px; cursor: pointer; }
    .st-method:hover { background: var(--accent); }
    .st-method .logo { width: 38px; height: 38px; border-radius: 9px; display: inline-flex; align-items: center; justify-content: center; font-size: 18px; flex: none; }
    .st-method .name { font-weight: 600; font-size: 14px; color: var(--foreground); }
    .st-method .sub { font-size: 12.5px; color: var(--muted-foreground); }
    .st-method input[type=checkbox] { margin-left: auto; width: 18px; height: 18px; }
    .st-status { font-size: 12px; padding: 2px 8px; border-radius: 6px; margin-left: 6px; }
    .st-status.on { background: var(--tint-green); color: var(--tint-green-fg); }
    .st-status.off { background: var(--muted); color: var(--muted-foreground); }
    .st-note { border: 1px solid var(--border); border-radius: 10px; padding: 12px 14px; font-size: 13px; color: var(--muted-foreground); background: var(--muted); margin-bottom: 16px; }
    .st-note.warn { border-color: color-mix(in srgb, var(--tint-amber-fg) 40%, transparent); background: var(--tint-amber); color: var(--foreground); }
    .st-note b { color: var(--foreground); }
    .st-code { font-family: ui-monospace, Consolas, monospace; font-size: 12px; background: var(--muted); border: 1px solid var(--border); border-radius: 6px; padding: 2px 6px; word-break: break-all; }
</style>

<div class="content-wrapper">
    <section class="content">
        <div class="st-pay-grid">

            <!-- ============ gateway details ============ -->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><?php echo $this->lang->line('payment_methods'); ?></h3>
                </div>
                <div class="box-body">
                    <div class="st-pay-tabs" role="tablist">
                        <button type="button" class="active" data-pane="pane-mpesa"><i class="icon-smartphone"></i> M-Pesa</button>
                        <button type="button" data-pane="pane-paystack"><i class="icon-credit-card"></i> Paystack</button>
                    </div>

                    <!-- M-Pesa -->
                    <div class="st-pay-pane active" id="pane-mpesa">
                        <?php if (!$is_kes) { ?>
                            <div class="st-note warn"><b>Your school currency is <?php echo html_escape($school_currency ? $school_currency->short_name : $currency_symbol); ?>, not KES.</b>
                                M-Pesa charges in Kenyan shillings, so set the currency to KES under System Settings &rarr; Currency before switching M-Pesa on. Fee amounts are sent to M-Pesa as they are, rounded up to whole shillings.</div>
                        <?php } ?>
                        <div class="st-note">Parents get a payment prompt on their phone (Lipa na M-Pesa / STK push) and confirm with their M-Pesa PIN. Credentials come from your app on
                            <a href="https://developer.safaricom.co.ke/" target="_blank" rel="noopener">developer.safaricom.co.ke</a>.
                            Safaricom sends payment confirmations to <span class="st-code"><?php echo html_escape(base_url('mpesa_callback/stk/…')); ?></span>, so the live site must be reachable over HTTPS. Payments are also confirmed by checking with Safaricom directly, so they still go through if a confirmation is delayed.</div>

                        <form id="form-mpesa" action="<?php echo site_url('admin/paymentsettings/mpesa'); ?>" method="post" autocomplete="off">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="st-row2">
                                <div class="st-field">
                                    <label>Environment</label>
                                    <div class="st-seg">
                                        <label><input type="radio" name="mpesa_mode" value="sandbox" <?php echo ($v($mpesa, 'gateway_mode') !== 'live') ? 'checked' : ''; ?>><span>Sandbox (testing)</span></label>
                                        <label><input type="radio" name="mpesa_mode" value="live" <?php echo ($v($mpesa, 'gateway_mode') === 'live') ? 'checked' : ''; ?>><span>Live</span></label>
                                    </div>
                                    <span class="err mpesa_mode_error"></span>
                                </div>
                                <div class="st-field">
                                    <label>Account type</label>
                                    <div class="st-seg">
                                        <label><input type="radio" name="mpesa_type" value="CustomerPayBillOnline" <?php echo ($v($mpesa, 'api_signature') !== 'CustomerBuyGoodsOnline') ? 'checked' : ''; ?>><span>Paybill</span></label>
                                        <label><input type="radio" name="mpesa_type" value="CustomerBuyGoodsOnline" <?php echo ($v($mpesa, 'api_signature') === 'CustomerBuyGoodsOnline') ? 'checked' : ''; ?>><span>Till (Buy Goods)</span></label>
                                    </div>
                                    <span class="err mpesa_type_error"></span>
                                </div>
                            </div>
                            <div class="st-row2">
                                <div class="st-field">
                                    <label>Consumer key <small class="req">*</small></label>
                                    <input type="text" name="mpesa_consumer_key" class="form-control" value="<?php echo $v($mpesa, 'api_username'); ?>">
                                    <span class="err mpesa_consumer_key_error"></span>
                                </div>
                                <div class="st-field">
                                    <label>Consumer secret <small class="req">*</small></label>
                                    <input type="password" name="mpesa_consumer_secret" class="form-control" value="<?php echo $v($mpesa, 'api_secret_key'); ?>">
                                    <span class="err mpesa_consumer_secret_error"></span>
                                </div>
                            </div>
                            <div class="st-row2">
                                <div class="st-field">
                                    <label><span class="lbl-shortcode">Paybill number</span> <small class="req">*</small></label>
                                    <input type="text" name="mpesa_shortcode" class="form-control" inputmode="numeric" value="<?php echo $v($mpesa, 'api_publishable_key'); ?>">
                                    <div class="hint lbl-shortcode-hint">Sandbox test paybill: 174379</div>
                                    <span class="err mpesa_shortcode_error"></span>
                                </div>
                                <div class="st-field">
                                    <label>Lipa na M-Pesa passkey <small class="req">*</small></label>
                                    <input type="password" name="mpesa_passkey" class="form-control" value="<?php echo $v($mpesa, 'api_password'); ?>">
                                    <div class="hint">Sandbox passkey is shown on the Daraja "Lipa na M-Pesa" simulator page.</div>
                                    <span class="err mpesa_passkey_error"></span>
                                </div>
                            </div>
                            <div class="st-row2 row-till" style="display:none">
                                <div class="st-field">
                                    <label>Till number <small class="req">*</small></label>
                                    <input type="text" name="mpesa_till" class="form-control" inputmode="numeric" value="<?php echo $v($mpesa, 'salt'); ?>">
                                    <div class="hint">The till parents pay into. The short code above is your store number.</div>
                                    <span class="err mpesa_till_error"></span>
                                </div>
                            </div>
                            <div class="st-row2">
                                <div class="st-field">
                                    <label><?php echo $this->lang->line('processing_fees_type'); ?></label>
                                    <div class="st-seg">
                                        <?php $ct = $mpesa && $mpesa->charge_type ? $mpesa->charge_type : 'none'; ?>
                                        <label><input type="radio" name="charge_type" value="none" <?php echo $ct === 'none' ? 'checked' : ''; ?>><span><?php echo $this->lang->line('none'); ?></span></label>
                                        <label><input type="radio" name="charge_type" value="percentage" <?php echo $ct === 'percentage' ? 'checked' : ''; ?>><span>%</span></label>
                                        <label><input type="radio" name="charge_type" value="fix" <?php echo $ct === 'fix' ? 'checked' : ''; ?>><span><?php echo $this->lang->line('fix_amount'); ?></span></label>
                                    </div>
                                </div>
                                <div class="st-field">
                                    <label><?php echo $this->lang->line('percentage_fix_amount'); ?></label>
                                    <input type="text" name="mpesa_charge_value" class="form-control" value="<?php echo $v($mpesa, 'charge_value'); ?>">
                                    <span class="err mpesa_charge_value_error"></span>
                                </div>
                            </div>
                            <?php if ($canEdit) { ?>
                                <button type="submit" class="btn btn-primary" data-loading-text="<?php echo $this->lang->line('save'); ?>…"><?php echo $this->lang->line('save'); ?></button>
                            <?php } ?>
                        </form>
                    </div>

                    <!-- Paystack -->
                    <div class="st-pay-pane" id="pane-paystack">
                        <div class="st-note">Card, bank and mobile-money checkout hosted by Paystack. Find your secret key in the Paystack dashboard under Settings &rarr; API Keys &amp; Webhooks (use the <b>sk_test_…</b> key while testing).</div>
                        <form id="form-paystack" action="<?php echo site_url('admin/paymentsettings/paystack'); ?>" method="post" autocomplete="off">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="st-row2">
                                <div class="st-field">
                                    <label><?php echo $this->lang->line('paystack_secret_key'); ?> <small class="req">*</small></label>
                                    <input type="password" name="paystack_secretkey" class="form-control" value="<?php echo $v($paystack, 'api_secret_key'); ?>">
                                    <span class="err paystack_secretkey_error"></span>
                                </div>
                            </div>
                            <div class="st-row2">
                                <div class="st-field">
                                    <label><?php echo $this->lang->line('processing_fees_type'); ?></label>
                                    <div class="st-seg">
                                        <?php $pt = $paystack && $paystack->charge_type ? $paystack->charge_type : 'none'; ?>
                                        <label><input type="radio" name="charge_type" value="none" <?php echo $pt === 'none' ? 'checked' : ''; ?>><span><?php echo $this->lang->line('none'); ?></span></label>
                                        <label><input type="radio" name="charge_type" value="percentage" <?php echo $pt === 'percentage' ? 'checked' : ''; ?>><span>%</span></label>
                                        <label><input type="radio" name="charge_type" value="fix" <?php echo $pt === 'fix' ? 'checked' : ''; ?>><span><?php echo $this->lang->line('fix_amount'); ?></span></label>
                                    </div>
                                </div>
                                <div class="st-field">
                                    <label><?php echo $this->lang->line('percentage_fix_amount'); ?></label>
                                    <input type="text" name="paystack_charge_value" class="form-control" value="<?php echo $v($paystack, 'charge_value'); ?>">
                                    <span class="err paystack_charge_value_error"></span>
                                </div>
                            </div>
                            <?php if ($canEdit) { ?>
                                <button type="submit" class="btn btn-primary" data-loading-text="<?php echo $this->lang->line('save'); ?>…"><?php echo $this->lang->line('save'); ?></button>
                            <?php } ?>
                        </form>
                    </div>
                </div>
            </div>

            <!-- ============ what parents can use ============ -->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Methods parents can use</h3>
                </div>
                <div class="box-body">
                    <form id="form-enabled" action="<?php echo site_url('admin/paymentsettings/enabled'); ?>" method="post">
                        <?php echo $this->customlib->getCSRF(); ?>
                        <label class="st-method">
                            <span class="logo st-tint-green"><i class="icon-smartphone"></i></span>
                            <span><span class="name">M-Pesa</span>
                                <span class="st-status <?php echo $mpesa ? 'on' : 'off'; ?>"><?php echo $mpesa ? 'Set up' : 'Not set up'; ?></span>
                                <br><span class="sub">Prompt on the parent's phone</span></span>
                            <input type="checkbox" name="enabled[]" value="mpesa" <?php echo in_array('mpesa', $enabled, true) ? 'checked' : ''; ?>>
                        </label>
                        <label class="st-method">
                            <span class="logo st-tint-blue"><i class="icon-credit-card"></i></span>
                            <span><span class="name">Paystack</span>
                                <span class="st-status <?php echo $paystack ? 'on' : 'off'; ?>"><?php echo $paystack ? 'Set up' : 'Not set up'; ?></span>
                                <br><span class="sub">Card and bank checkout</span></span>
                            <input type="checkbox" name="enabled[]" value="paystack" <?php echo in_array('paystack', $enabled, true) ? 'checked' : ''; ?>>
                        </label>
                        <span class="err enabled_error" style="color:var(--destructive);font-size:12.5px"></span>
                        <p class="text-muted" style="font-size:12.5px;margin:8px 0 14px">With both ticked, parents choose at checkout. Untick both to turn off online payments.</p>
                        <?php if ($otherActive) { ?>
                            <div class="st-note warn">Currently active from the old list: <b><?php echo html_escape(implode(', ', $otherActive)); ?></b>. Saving here turns it off.</div>
                        <?php } ?>
                        <?php if ($canEdit) { ?>
                            <button type="submit" class="btn btn-primary" style="width:100%"><?php echo $this->lang->line('save'); ?></button>
                        <?php } ?>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
(function ($) {
    // tabs
    $('.st-pay-tabs button').on('click', function () {
        $('.st-pay-tabs button').removeClass('active');
        $(this).addClass('active');
        $('.st-pay-pane').removeClass('active');
        $('#' + $(this).data('pane')).addClass('active');
    });
    // paybill vs till labels
    function syncType() {
        var till = $('input[name=mpesa_type]:checked').val() === 'CustomerBuyGoodsOnline';
        $('.row-till').toggle(till);
        $('.lbl-shortcode').text(till ? 'Store number (head office)' : 'Paybill number');
        $('.lbl-shortcode-hint').text(till ? 'The store number from your till registration, not the till itself.' : 'Sandbox test paybill: 174379');
    }
    $('input[name=mpesa_type]').on('change', syncType);
    syncType();

    // ajax save for the three forms
    $('#form-mpesa, #form-paystack, #form-enabled').on('submit', function (e) {
        e.preventDefault();
        var $f = $(this), $btn = $f.find('button[type=submit]');
        $f.find('.err').text('');
        $btn.prop('disabled', true);
        $.ajax({ url: $f.attr('action'), type: 'POST', data: $f.serialize(), dataType: 'json' })
            .done(function (r) {
                if (r.st === 0) {
                    successMsg(r.msg);
                    if ($f.attr('id') !== 'form-enabled') { setTimeout(function () { location.reload(); }, 700); }
                } else {
                    var first = '';
                    $.each(r.msg, function (k, v) { if (v) { $f.find('.' + k + '_error').text(v); first = first || v; } });
                    errorMsg(first || 'Please check the form.');
                }
            })
            .fail(function () { errorMsg('Could not save. Please try again.'); })
            .always(function () { $btn.prop('disabled', false); });
    });
})(jQuery);
</script>
