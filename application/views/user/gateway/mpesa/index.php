<!DOCTYPE html>
<html lang="en">
<head>
<?php $title = 'Pay with M-Pesa'; include __DIR__ . '/_head.php'; ?>
</head>
<body>
<div class="wrap">
    <div class="top">
        <a class="back" href="<?php echo base_url($both ? 'user/gateway/mpesa/choose' : 'user/user/getfees'); ?>"><i class="icon-arrow-left"></i> <?php echo $both ? 'Other payment options' : 'Back to fees'; ?></a>
        <span class="school"><?php echo html_escape($school); ?></span>
    </div>

    <h1>Pay with M-Pesa <?php if ($sandbox) { ?><span class="badge">TEST MODE</span><?php } ?></h1>
    <p class="sub">For <?php echo html_escape($params['name']); ?></p>

    <div class="card">
        <ul class="lines">
            <?php foreach ($fees as $f) {
                $line = (float) $f['amount_balance'] - (isset($f['applied_fee_discount']) ? (float) $f['applied_fee_discount'] : 0); ?>
                <li><span><?php echo html_escape(trim(($f['fee_group_name'] ?? '') . ' · ' . ($f['fee_type_code'] ?? ''), ' ·')); ?></span><span><?php echo html_escape($symbol) . amountFormat($line); ?></span></li>
                <?php if ((float) $f['fine_balance'] > 0) { ?>
                    <li><span>Late fine</span><span><?php echo html_escape($symbol) . amountFormat($f['fine_balance']); ?></span></li>
                <?php } ?>
            <?php } ?>
            <?php if ((float) $params['gateway_processing_charge'] > 0) { ?>
                <li><span>M-Pesa processing fee</span><span><?php echo html_escape($symbol) . amountFormat($params['gateway_processing_charge']); ?></span></li>
            <?php } ?>
            <li class="total"><span>Total</span><span>KES <?php echo number_format($amount); ?></span></li>
        </ul>
    </div>

    <div class="card" id="step-phone">
        <label class="fl" for="phone">M-Pesa phone number</label>
        <div class="phone">
            <i class="icon-smartphone"></i>
            <input class="in" id="phone" type="tel" inputmode="tel" autocomplete="tel" placeholder="0712 345 678" value="<?php echo html_escape($phone); ?>">
        </div>
        <div class="hint">We'll send a payment prompt to this phone. Enter your M-Pesa PIN there to pay.</div>
        <button class="btn mpesa" id="send"><i class="icon-send"></i> Send payment prompt</button>
        <div class="alert" id="msg"></div>
    </div>

    <div class="card wait" id="step-wait">
        <div class="ring"></div>
        <h2>Check your phone</h2>
        <p id="wait-text">Enter your M-Pesa PIN on the prompt to pay KES <?php echo number_format($amount); ?>.</p>
        <button class="linkbtn" id="again" style="display:none">Didn't get it? Send again</button>
    </div>

    <p class="secure"><i class="icon-shield-check"></i> Paid directly to the school through Safaricom M-Pesa.</p>
</div>

<script>
(function () {
    var base = <?php echo json_encode(base_url('user/gateway/mpesa/')); ?>;
    var $ = function (id) { return document.getElementById(id); };
    var poll = null, started = 0;

    function show(type, text) { var m = $('msg'); m.className = 'alert ' + type; m.textContent = text; }
    function phoneStep(errText) {
        clearInterval(poll); poll = null;
        $('step-wait').style.display = 'none'; $('step-phone').style.display = 'block';
        $('send').disabled = false;
        if (errText) { show('err', errText); }
    }
    function waitStep() {
        $('step-phone').style.display = 'none'; $('step-wait').style.display = 'block'; $('again').style.display = 'none';
        $('wait-text').textContent = 'Enter your M-Pesa PIN on the prompt to pay KES <?php echo number_format($amount); ?>.';
    }

    function check(txn) {
        fetch(base + 'status/' + txn, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (d.state === 'success') {
                    clearInterval(poll);
                    $('wait-text').textContent = 'Payment received. Opening your receipt…';
                    window.location.href = d.redirect;
                } else if (d.state === 'failed') {
                    phoneStep(d.message);
                } else if ((Date.now() - started) > 90000) {
                    $('wait-text').textContent = 'Still waiting for your confirmation. If you already paid, keep this page open a little longer.';
                    $('again').style.display = 'inline';
                }
            })
            .catch(function () { /* network blip: keep polling */ });
    }

    function send() {
        var phone = $('phone').value.trim();
        if (!phone) { show('err', 'Enter your M-Pesa phone number.'); return; }
        $('send').disabled = true; show('info', 'Sending the prompt to your phone…');
        var body = new URLSearchParams(); body.append('phone', phone);
        fetch(base + 'stk', { method: 'POST', body: body, credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (!d.ok) { phoneStep(d.error || 'Could not send the prompt.'); return; }
                waitStep(); started = Date.now();
                poll = setInterval(function () { check(d.txn); }, 4000);
            })
            .catch(function () { phoneStep('Could not reach the server. Check your connection and try again.'); });
    }

    $('send').addEventListener('click', send);
    $('phone').addEventListener('keydown', function (e) { if (e.key === 'Enter') { send(); } });
    $('again').addEventListener('click', function () { phoneStep(''); $('msg').className = 'alert'; });
})();
</script>
</body>
</html>
