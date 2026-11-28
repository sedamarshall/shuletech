<!DOCTYPE html>
<html lang="en">
<head>
<?php $title = 'Choose how to pay'; include __DIR__ . '/_head.php'; ?>
</head>
<body>
<div class="wrap">
    <div class="top">
        <a class="back" href="<?php echo base_url('user/user/getfees'); ?>"><i class="icon-arrow-left"></i> Back to fees</a>
        <span class="school"><?php echo html_escape($school); ?></span>
    </div>
    <h1>How would you like to pay?</h1>
    <p class="sub">Amount due: <b><?php echo html_escape($symbol) . amountFormat($amount); ?></b> for <?php echo html_escape($params['name']); ?>.</p>

    <?php if (in_array('mpesa', $enabled, true)) { ?>
    <a class="opt" href="<?php echo base_url('user/gateway/mpesa'); ?>">
        <span class="ic" style="background:var(--mpesa-tint);color:var(--mpesa)"><i class="icon-smartphone"></i></span>
        <span><span class="t">M-Pesa</span><br><span class="d">Get a prompt on your phone and enter your PIN</span></span>
        <i class="icon-chevron-right go"></i>
    </a>
    <?php } ?>
    <?php if (in_array('paystack', $enabled, true)) { ?>
    <a class="opt" href="<?php echo base_url('user/gateway/mpesa/paystack'); ?>">
        <span class="ic" style="background:var(--blue-tint);color:var(--blue)"><i class="icon-credit-card"></i></span>
        <span><span class="t">Card or bank</span><br><span class="d">Visa, Mastercard and bank payments via Paystack</span></span>
        <i class="icon-chevron-right go"></i>
    </a>
    <?php } ?>
    <p class="secure"><i class="icon-lock"></i> Processing fees, if any, are shown on the next screen.</p>
</div>
</body>
</html>
