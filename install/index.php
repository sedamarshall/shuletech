<?php
/*
 | ShuleTech web installer.
 | Runs before the app is configured (no CodeIgniter here). Locks itself after a successful install
 | via application/config/installed.lock - delete the install/ folder afterwards.
 */
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '0');
@set_time_limit(600);
@ini_set('memory_limit', '512M');
mysqli_report(MYSQLI_REPORT_OFF);
session_name('shuletech_installer');
session_start();

$ROOT    = dirname(__DIR__);
$CFG     = $ROOT . '/application/config';
$LOCK    = $CFG . '/installed.lock';
$SQLFILE = __DIR__ . '/sql/shuletech.sql.gz';
$APP     = 'ShuleTech';

function h($s): string { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
function st(): array { return $_SESSION['st'] ?? []; }
function put(array $a): void { $_SESSION['st'] = array_merge(st(), $a); }
function csrf(): string { if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(16)); } return $_SESSION['csrf']; }
function check_csrf(): void { if (!hash_equals($_SESSION['csrf'] ?? '', (string) ($_POST['csrf'] ?? ''))) { http_response_code(400); exit('Session expired. Reload the page.'); } }
function detect_url(): string {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') || (strpos($_SERVER['HTTP_CF_VISITOR'] ?? '', 'https') !== false);
    $dir = rtrim(str_replace(chr(92), '/', dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/install/index.php'))), '/');
    return ($https ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $dir . '/';
}
function db_connect(array $d, ?string &$err = null): ?mysqli {
    $m = @mysqli_init();
    @$m->options(MYSQLI_OPT_CONNECT_TIMEOUT, 8);
    if (!@$m->real_connect($d['host'], $d['user'], $d['pass'], $d['name'], (int) ($d['port'] ?: 3306))) {
        $err = mysqli_connect_error() ?: 'Could not connect.';
        return null;
    }
    $m->set_charset('utf8mb4');
    return $m;
}

/* ---------------- already installed ---------------- */
if (is_file($LOCK) || is_file($CFG . '/database.php')) { // a configured site never re-runs the installer
    page('Already installed', function () {
        echo '<div class="ico ok"><i>✓</i></div><h1>ShuleTech is already installed</h1>
              <p class="sub">The installer is locked. For safety, delete the <code>install</code> folder from the server.</p>
              <a class="btn" href="../site/login">Go to staff login</a>';
    });
    exit;
}

$step = (int) ($_GET['step'] ?? 1);
$err  = '';

/* ---------------- requirements ---------------- */
function requirements(string $ROOT, string $SQLFILE): array {
    $r = [];
    $r[] = ['PHP 8.1 or newer', version_compare(PHP_VERSION, '8.1.0', '>='), 'You have PHP ' . PHP_VERSION . '. Select PHP 8.2 or 8.3 for this site in your hosting panel.'];
    foreach (['mysqli', 'curl', 'gd', 'mbstring', 'zip', 'fileinfo', 'bcmath', 'openssl', 'json', 'zlib'] as $ext) {
        $r[] = ["PHP extension: $ext", extension_loaded($ext), "Enable the $ext extension for your PHP version (aaPanel: App Store → PHP → Install extensions)."];
    }
    foreach (['application/config', 'application/cache', 'application/logs', 'uploads', 'temp', 'backend/captcha_images'] as $dir) {
        $p = $ROOT . '/' . $dir;
        $r[] = ["Writable: $dir/", is_dir($p) && is_writable($p), "Make it writable by the web server, e.g. chown -R www:www and chmod 775 on $dir."];
    }
    $r[] = ['Installer database file', is_file($SQLFILE), 'install/sql/shuletech.sql.gz is missing. Re-upload the full ShuleTech zip.'];
    return $r;
}

/* ---------------- POST handlers ---------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'db') {
        $d = ['host' => trim($_POST['host'] ?? 'localhost'), 'port' => trim($_POST['port'] ?? '3306'), 'name' => trim($_POST['name'] ?? ''),
              'user' => trim($_POST['user'] ?? ''), 'pass' => (string) ($_POST['pass'] ?? '')];
        put(['db' => $d]);
        if ($d['name'] === '' || $d['user'] === '') {
            $err = 'Enter the database name and user.';
            $step = 2;
        } elseif (!($m = db_connect($d, $e))) {
            $err = 'Could not connect: ' . $e . ' Check the details, and that the user has access to this database.';
            $step = 2;
        } else {
            $n = (int) $m->query('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()')->fetch_row()[0];
            if ($n > 0 && empty($_POST['replace'])) {
                put(['db_tables' => $n]);
                $err = "This database already has $n tables. Use an empty database, or tick “Replace existing tables” to wipe it.";
                $step = 2;
            } else {
                put(['db_ok' => true, 'replace' => !empty($_POST['replace'])]);
                header('Location: ?step=3');
                exit;
            }
        }
    }

    if ($action === 'install') {
        $s = st();
        $f = array_map(fn($v) => is_string($v) ? trim($v) : $v, $_POST);
        put(['form' => array_diff_key($f, ['pass1' => 1, 'pass2' => 1, 'csrf' => 1])]);
        $url = rtrim((string) ($f['url'] ?? ''), '/') . '/';
        if (empty($s['db_ok'])) { header('Location: ?step=2'); exit; }
        if (($f['school'] ?? '') === '') { $err = 'Enter the school name.'; }
        elseif (!preg_match('#^https?://[^/\s]+(/.*)?$#i', $url)) { $err = 'Enter the site address, e.g. https://school.co.ke/'; }
        elseif (!filter_var($f['email'] ?? '', FILTER_VALIDATE_EMAIL)) { $err = 'Enter a valid admin e-mail address.'; }
        elseif (($f['first'] ?? '') === '') { $err = 'Enter the admin\'s first name.'; }
        elseif (strlen((string) ($_POST['pass1'] ?? '')) < 8) { $err = 'The admin password must be at least 8 characters.'; }
        elseif (($_POST['pass1'] ?? '') !== ($_POST['pass2'] ?? '')) { $err = 'The two passwords don\'t match.'; }
        elseif (!in_array($f['tz'] ?? '', timezone_identifiers_list(), true)) { $err = 'Choose a time zone.'; }
        if ($err === '') {
            $err = run_install($s, $f, $url, (string) $_POST['pass1'], $ROOT, $CFG, $LOCK, $SQLFILE);
            if ($err === '') { header('Location: ?step=4'); exit; }
        }
        $step = 3;
    }
}

/* ---------------- the install itself ---------------- */
function run_install(array $s, array $f, string $url, string $pass, string $ROOT, string $CFG, string $LOCK, string $SQLFILE): string {
    $m = db_connect($s['db'], $e);
    if (!$m) { return 'Database connection lost: ' . $e; }
    $m->query('SET FOREIGN_KEY_CHECKS = 0');
    if (!empty($s['replace'])) {
        $res = $m->query('SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE()');
        while ($t = $res->fetch_row()) { $m->query('DROP TABLE IF EXISTS `' . str_replace('`', '', $t[0]) . '`'); }
    }
    // 1) import
    $gz = @gzopen($SQLFILE, 'rb');
    if (!$gz) { return 'Could not read install/sql/shuletech.sql.gz.'; }
    $buf = ''; $n = 0;
    while (!gzeof($gz)) {
        $line = gzgets($gz, 1 << 20);
        if ($line === false) { break; }
        $t = ltrim($line);
        if ($buf === '' && ($t === '' || strpos($t, '-- ') === 0 || $t === "--\n" || $t === "--\r\n")) { continue; }
        $buf .= $line;
        if (preg_match('/;\s*$/', $line)) {
            if (!$m->query($buf)) { gzclose($gz); return 'Database import failed at statement ' . ($n + 1) . ': ' . $m->error; }
            $buf = ''; $n++;
        }
    }
    gzclose($gz);
    if ($n < 100) { return 'The database file looks incomplete (' . $n . ' statements).'; }

    // 2) school settings
    $cron = bin2hex(random_bytes(20));
    $folder = rtrim(str_replace(chr(92), '/', $ROOT), '/') . '/';
    $q = $m->prepare('UPDATE sch_settings SET name = ?, email = ?, phone = ?, address = ?, timezone = ?, base_url = ?, folder_path = ?, cron_secret_key = ?');
    $q->bind_param('ssssssss', $f['school'], $f['school_email'], $f['phone'], $f['address'], $f['tz'], $url, $folder, $cron);
    if (!$q->execute()) { return 'Could not save school settings: ' . $m->error; }

    // 3) admin account (the Super Admin row shipped in the database)
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    $admin = (int) ($m->query("SELECT staff_id FROM staff_roles WHERE role_id = (SELECT id FROM roles WHERE name = 'Super Admin' LIMIT 1) ORDER BY staff_id LIMIT 1")->fetch_row()[0] ?? 0);
    if ($admin < 1) { return 'The Super Admin account is missing from the database file.'; }
    $last = (string) ($f['last'] ?? '');
    $q = $m->prepare('UPDATE staff SET name = ?, surname = ?, email = ?, password = ?, is_active = 1 WHERE id = ?');
    $q->bind_param('ssssi', $f['first'], $last, $f['email'], $hash, $admin);
    if (!$q->execute()) { return 'Could not create the admin account: ' . $m->error; }
    $m->query('SET FOREIGN_KEY_CHECKS = 1');

    // 4) config files
    $db = $s['db'];
    $tpl = @file_get_contents($CFG . '/database.php.example');
    if (!$tpl) { return 'application/config/database.php.example is missing.'; }
    $rep = ["'hostname' => 'localhost'" => "'hostname' => " . var_export($db['host'], true),
            "'username' => 'your_db_user'" => "'username' => " . var_export($db['user'], true),
            "'password' => 'your_db_password'" => "'password' => " . var_export($db['pass'], true),
            "'database' => 'your_db_name'" => "'database' => " . var_export($db['name'], true)];
    $out = str_replace(array_keys($rep), array_values($rep), $tpl, $cnt);
    if ($cnt !== 4) { return 'database.php.example has an unexpected format.'; }
    if ((string) $db['port'] !== '' && (string) $db['port'] !== '3306') {
        $out = preg_replace("/('dbdriver'\s*=>\s*'mysqli',)/", "$1\n    'port'         => " . (int) $db['port'] . ',', $out, 1);
    }
    $files = [$CFG . '/database.php' => $out];

    $sec = @file_get_contents($CFG . '/shuletech_security.php.example');
    if (!$sec) { return 'application/config/shuletech_security.php.example is missing.'; }
    $files[$CFG . '/shuletech_security.php'] = str_replace('CHANGE_ME_run_php_-r_echo_bin2hex_random_bytes_32', bin2hex(random_bytes(32)), $sec);
    if (!is_file($CFG . '/api_keys.php') && is_file($CFG . '/api_keys.php.example')) {
        $files[$CFG . '/api_keys.php'] = file_get_contents($CFG . '/api_keys.php.example');
    }
    $hostname = strtolower((string) parse_url($url, PHP_URL_HOST));
    $hosts = array_values(array_unique(array_filter([$hostname, strpos($hostname, 'www.') === 0 ? substr($hostname, 4) : 'www.' . $hostname, 'localhost', '127.0.0.1'])));
    $files[$CFG . '/shuletech_hosts.php'] = "<?php\n// Written by the ShuleTech installer. Hosts this site answers to, and its main address.\nreturn " . var_export(['hosts' => $hosts, 'default_url' => $url], true) . ";\n";
    foreach ($files as $path => $content) {
        if (@file_put_contents($path, $content) === false) { return 'Could not write ' . str_replace($ROOT . '/', '', $path) . '. Make application/config writable.'; }
    }
    @file_put_contents($LOCK, 'Installed ' . date('c') . "\n");
    $_SESSION['done'] = ['url' => $url, 'cron' => $cron, 'email' => $f['email']];
    unset($_SESSION['st']);
    return '';
}

/* ---------------- views ---------------- */
function page(string $title, callable $body, int $step = 0): void { ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($title) ?> · ShuleTech installer</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400..800&display=swap">
<style>
  :root{--bg:#fafafa;--fg:#0a0a0a;--card:#fff;--muted:#f4f4f5;--mfg:#71717a;--border:#e4e4e7;--input:#e4e4e7;--primary:#092B63;--pfg:#fff;--accent:#FF7900;--red:#dc2626;--green:#16a34a;--ring:#a1a1aa}
  @media (prefers-color-scheme:dark){:root{--bg:#0f0f10;--fg:#fafafa;--card:#18181a;--muted:#232325;--mfg:#a1a1aa;--border:rgba(255,255,255,.09);--input:rgba(255,255,255,.14);--primary:#e5e7eb;--pfg:#0b1b38;--red:#f87171;--green:#4ade80;--ring:#71717a}}
  *{box-sizing:border-box} body{margin:0;background:var(--bg);color:var(--fg);font:14.5px/1.55 'Bricolage Grotesque','Segoe UI',system-ui,sans-serif;-webkit-font-smoothing:antialiased}
  .wrap{max-width:640px;margin:0 auto;padding:40px 16px 60px}
  .brand{display:flex;align-items:center;gap:10px;font-weight:800;font-size:22px;letter-spacing:-.02em;margin-bottom:28px}
  .brand svg{width:40px;height:36px}.brand .s{color:#092B63}.brand .t{color:#FF7900}
  @media (prefers-color-scheme:dark){.brand .s{color:#fff}.brand svg .cap{fill:#fff}}
  .steps{display:flex;gap:6px;margin:0 0 22px;padding:0;list-style:none;flex-wrap:wrap}
  .steps li{font-size:12.5px;padding:5px 11px;border-radius:99px;background:var(--muted);color:var(--mfg);font-weight:500}
  .steps li.on{background:var(--primary);color:var(--pfg)} .steps li.done{color:var(--green)}
  .card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:26px 26px 24px}
  h1{font-size:24px;font-weight:700;letter-spacing:-.02em;margin:0 0 6px}.sub{color:var(--mfg);margin:0 0 22px}
  h2{font-size:15px;font-weight:600;margin:22px 0 12px}
  .req{list-style:none;margin:0;padding:0;border:1px solid var(--border);border-radius:12px;overflow:hidden}
  .req li{display:flex;gap:10px;padding:10px 14px;border-bottom:1px solid var(--border);align-items:flex-start}.req li:last-child{border-bottom:0}
  .req b{font-weight:500}.req .fix{display:block;color:var(--mfg);font-size:12.5px}
  .dot{width:20px;height:20px;border-radius:50%;flex:none;display:inline-flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;margin-top:1px}
  .dot.y{background:color-mix(in srgb,var(--green) 15%,transparent);color:var(--green)}.dot.n{background:color-mix(in srgb,var(--red) 15%,transparent);color:var(--red)}
  label{display:block;font-weight:500;font-size:13.5px;margin:0 0 6px}
  .f{margin-bottom:14px}.row{display:grid;grid-template-columns:1fr 1fr;gap:14px}@media(max-width:560px){.row{grid-template-columns:1fr}}
  input[type=text],input[type=email],input[type=password],input[type=url],input[type=number],select,textarea{width:100%;height:40px;border:1px solid var(--input);border-radius:9px;background:transparent;color:var(--fg);font:inherit;padding:0 12px}
  textarea{height:64px;padding:8px 12px;resize:vertical} select option{background:var(--card)}
  input:focus,select:focus,textarea:focus{outline:0;border-color:var(--ring);box-shadow:0 0 0 3px color-mix(in srgb,var(--ring) 30%,transparent)}
  .hint{color:var(--mfg);font-size:12.5px;margin-top:5px}.check{display:flex;gap:8px;align-items:flex-start;font-weight:400}
  .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;height:42px;padding:0 20px;border:0;border-radius:10px;background:var(--primary);color:var(--pfg);font:inherit;font-weight:600;cursor:pointer;text-decoration:none}
  .btn[disabled]{opacity:.5;cursor:not-allowed}.btn.ghost{background:transparent;color:var(--fg);border:1px solid var(--input)}
  .actions{display:flex;justify-content:space-between;gap:10px;margin-top:22px;align-items:center}
  .alert{border-radius:10px;padding:11px 14px;margin:0 0 18px;font-size:13.5px;border:1px solid color-mix(in srgb,var(--red) 35%,transparent);background:color-mix(in srgb,var(--red) 8%,transparent);color:var(--red)}
  .note{border-radius:10px;padding:11px 14px;margin:16px 0 0;font-size:13px;background:var(--muted);color:var(--mfg)}
  code{font-family:ui-monospace,Consolas,monospace;font-size:12.5px;background:var(--muted);border:1px solid var(--border);border-radius:6px;padding:1px 6px;word-break:break-all}
  .ico{width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:26px;margin-bottom:14px}.ico.ok{background:color-mix(in srgb,var(--green) 15%,transparent);color:var(--green)}.ico i{font-style:normal}
  .kv{display:grid;grid-template-columns:150px 1fr;gap:8px 12px;font-size:13.5px;margin:14px 0}.kv span{color:var(--mfg)}
  .busy{display:none}.busy.on{display:inline}
</style>
</head>
<body><div class="wrap">
  <div class="brand"><svg viewBox="0 0 44 40" aria-hidden="true"><path class="cap" d="M22 3 42 11 22 19 2 11z" fill="#092B63"/><path class="cap" d="M11 15v7c0 3 5 6 11 6s11-3 11-6v-7l-11 4.5z" fill="#092B63"/><path d="M6 12.5v9" stroke="#FF7900" stroke-width="1.8" stroke-linecap="round"/><path d="M4.6 21.5h2.8l.6 4h-4z" fill="#FF7900"/><path d="M3 33c10 6 27 5 38-6" stroke="#FF7900" stroke-width="3.2" fill="none" stroke-linecap="round"/></svg><span><span class="s">Shule</span><span class="t">Tech</span></span></div>
  <?php if ($step) { $names = ['Requirements', 'Database', 'School & admin', 'Finish']; echo '<ol class="steps">';
        foreach ($names as $i => $n) { $k = $i + 1; echo '<li class="' . ($k === $step ? 'on' : ($k < $step ? 'done' : '')) . '">' . ($k < $step ? '✓ ' : $k . '. ') . h($n) . '</li>'; } echo '</ol>'; } ?>
  <div class="card"><?php $body(); ?></div>
</div></body></html>
<?php }

$s = st();
switch ($step) {
case 2:
    $d = $s['db'] ?? ['host' => 'localhost', 'port' => '3306', 'name' => '', 'user' => '', 'pass' => ''];
    page('Database', function () use ($d, $err, $s) { ?>
        <h1>Database</h1><p class="sub">Create an empty MySQL/MariaDB database and user in your hosting panel, then enter the details here.</p>
        <?php if ($err) { echo '<div class="alert">' . h($err) . '</div>'; } ?>
        <form method="post"><input type="hidden" name="csrf" value="<?= h(csrf()) ?>"><input type="hidden" name="action" value="db">
          <div class="row"><div class="f"><label>Database host</label><input type="text" name="host" value="<?= h($d['host']) ?>" required></div>
            <div class="f"><label>Port</label><input type="number" name="port" value="<?= h($d['port']) ?>"></div></div>
          <div class="f"><label>Database name</label><input type="text" name="name" value="<?= h($d['name']) ?>" required autocomplete="off"></div>
          <div class="row"><div class="f"><label>Database user</label><input type="text" name="user" value="<?= h($d['user']) ?>" required autocomplete="off"></div>
            <div class="f"><label>Database password</label><input type="password" name="pass" value="" autocomplete="new-password"></div></div>
          <?php if (!empty($s['db_tables'])) { ?><label class="check"><input type="checkbox" name="replace" value="1"> Replace existing tables (deletes everything in this database)</label><?php } ?>
          <div class="actions"><a class="btn ghost" href="?step=1">Back</a><button class="btn">Test connection &amp; continue</button></div>
        </form>
    <?php }, 2);
    break;

case 3:
    if (empty($s['db_ok'])) { header('Location: ?step=2'); exit; }
    $f = $s['form'] ?? [];
    $v = fn($k, $def = '') => h($f[$k] ?? $def);
    page('School & admin', function () use ($f, $v, $err) { ?>
        <h1>School &amp; admin account</h1><p class="sub">You can change all of this later under Settings.</p>
        <?php if ($err) { echo '<div class="alert">' . h($err) . '</div>'; } ?>
        <form method="post" id="inst"><input type="hidden" name="csrf" value="<?= h(csrf()) ?>"><input type="hidden" name="action" value="install">
          <h2>School</h2>
          <div class="f"><label>School name</label><input type="text" name="school" value="<?= $v('school') ?>" required></div>
          <div class="row"><div class="f"><label>School e-mail</label><input type="email" name="school_email" value="<?= $v('school_email') ?>"></div>
            <div class="f"><label>Phone</label><input type="text" name="phone" value="<?= $v('phone') ?>"></div></div>
          <div class="f"><label>Address</label><textarea name="address"><?= $v('address') ?></textarea></div>
          <div class="row"><div class="f"><label>Site address</label><input type="url" name="url" value="<?= $v('url', detect_url()) ?>" required><div class="hint">Use https:// once SSL is set up.</div></div>
            <div class="f"><label>Time zone</label><select name="tz"><?php $tz = $f['tz'] ?? 'Africa/Nairobi';
              foreach (timezone_identifiers_list() as $z) { echo '<option' . ($z === $tz ? ' selected' : '') . '>' . h($z) . '</option>'; } ?></select></div></div>
          <h2>Super admin</h2>
          <div class="row"><div class="f"><label>First name</label><input type="text" name="first" value="<?= $v('first') ?>" required></div>
            <div class="f"><label>Last name</label><input type="text" name="last" value="<?= $v('last') ?>"></div></div>
          <div class="f"><label>Admin e-mail (used to sign in)</label><input type="email" name="email" value="<?= $v('email') ?>" required></div>
          <div class="row"><div class="f"><label>Password</label><input type="password" name="pass1" minlength="8" required autocomplete="new-password"><div class="hint">At least 8 characters.</div></div>
            <div class="f"><label>Confirm password</label><input type="password" name="pass2" minlength="8" required autocomplete="new-password"></div></div>
          <div class="note">The school is set up with Kenyan shillings (KSh), a January–December school year and the ShuleTech look. Installing takes up to a minute.</div>
          <div class="actions"><a class="btn ghost" href="?step=2">Back</a><button class="btn" id="go"><span class="busy" id="busy">Installing… </span><span id="lbl">Install ShuleTech</span></button></div>
        </form>
        <script>document.getElementById('inst').addEventListener('submit',function(){var b=document.getElementById('go');b.disabled=true;document.getElementById('busy').classList.add('on');document.getElementById('lbl').style.display='none';});</script>
    <?php }, 3);
    break;

case 4:
    $done = $_SESSION['done'] ?? null;
    if (!$done) { header('Location: ?step=1'); exit; }
    page('Finished', function () use ($done) { $u = $done['url']; ?>
        <div class="ico ok"><i>✓</i></div>
        <h1>ShuleTech is installed</h1><p class="sub">Sign in with <b><?= h($done['email']) ?></b> and the password you chose.</p>
        <div class="kv"><span>Staff login</span><code><?= h($u) ?>site/login</code>
          <span>Students &amp; parents</span><code><?= h($u) ?>site/userlogin</code></div>
        <h2>Two more things</h2>
        <ol style="margin:0;padding-left:18px">
          <li><b>Delete the <code>install</code> folder</b> from the server. It is locked, but it shouldn't stay online.</li>
          <li style="margin-top:8px"><b>Add these cron jobs</b> in your hosting panel (save them now; they are not shown again):
            <div class="kv"><span>Daily, 06:00</span><code><?= h($u) ?>cron/<?= h($done['cron']) ?></code>
              <span>Every 5 minutes</span><code><?= h($u) ?>cron/schedulesmsemails/<?= h($done['cron']) ?></code></div></li>
        </ol>
        <div class="actions"><span></span><a class="btn" href="<?= h($u) ?>site/login">Go to staff login</a></div>
    <?php }, 4);
    break;

default:
    $req = requirements($ROOT, $SQLFILE);
    $ok = !in_array(false, array_column($req, 1), true);
    page('Requirements', function () use ($req, $ok) { ?>
        <h1>Install ShuleTech</h1><p class="sub">School management for Kenyan schools. First, a quick check that this server is ready.</p>
        <ul class="req"><?php foreach ($req as [$name, $pass, $fix]) { ?>
          <li><span class="dot <?= $pass ? 'y' : 'n' ?>"><?= $pass ? '✓' : '!' ?></span><span><b><?= h($name) ?></b><?php if (!$pass) { echo '<span class="fix">' . h($fix) . '</span>'; } ?></span></li>
        <?php } ?></ul>
        <div class="actions"><a class="btn ghost" href="?step=1">Check again</a>
          <?php if ($ok) { ?><a class="btn" href="?step=2">Continue</a><?php } else { ?><button class="btn" disabled>Fix the items above first</button><?php } ?></div>
    <?php }, 1);
}
