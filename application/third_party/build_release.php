<?php
/*
 | ShuleTech release builder - produces dist/shuletech-<version>.zip with the web installer.
 |
 |   C:\xampp\php\php.exe application\third_party\build_release.php [version]
 |
 | The zip contains the code (tracked + new, non-ignored files from the working tree), the default
 | uploads, and install/sql/shuletech.sql.gz: a clean database built from a TEMPORARY COPY of the
 | local database with all test data, logs, keys and personal details removed. The local database
 | is never modified. Secrets (database.php, shuletech_security.php, api_keys.php, backups) are
 | git-ignored and therefore never included.
 */
if (PHP_SAPI !== 'cli') { exit('CLI only'); }
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$root = realpath(__DIR__ . '/../../');
chdir($root);
$version = $argv[1] ?? date('Y.m.d-Hi');
$out     = "$root/dist/shuletech-$version.zip";
@mkdir("$root/dist", 0775, true);

function say($s) { echo $s . PHP_EOL; }
function fail($s) { fwrite(STDERR, "BUILD FAILED: $s\n"); exit(1); }

/* ---------- 1. files ---------- */
exec('git ls-files --cached --others --exclude-standard -z 2>&1', $lines, $rc);
if ($rc !== 0) { fail('git is required: ' . implode(' ', $lines)); }
$files = array_filter(explode("\0", implode("\n", $lines)));
$exclude = [
    '#^application/third_party/(seed_test_data|build_release|gen_font_css|gen_icon_css)\.php$#', // developer tools
    '#^DEPLOY\.md$#', '#^dist/#', '#^brand/.*\.zip$#', '#^install/sql/.*\.gz$#',
];
$code = [];
foreach ($files as $f) {
    $f = str_replace('\\', '/', $f);
    if ($f === '' || !is_file("$root/$f")) { continue; }
    foreach ($exclude as $re) { if (preg_match($re, $f)) { continue 2; } }
    $code[] = $f;
}
say('code files: ' . count($code));

/* ---------- 2. clean database ---------- */
define('BASEPATH', "$root/system/");
define('ENVIRONMENT', 'production');
$db = []; $active_group = 'default'; $query_builder = true;
include "$root/application/config/database.php";
$c = $db[$active_group];
$bin = is_file('C:/xampp/mysql/bin/mysqldump.exe') ? 'C:\\xampp\\mysql\\bin\\' : '';
$exe = function ($tool) use ($bin) { return $bin === '' ? $tool : '"' . $bin . $tool . '.exe"'; };
$auth = '-h ' . escapeshellarg($c['hostname']) . ' -u ' . escapeshellarg($c['username']) . ($c['password'] !== '' ? ' -p' . escapeshellarg($c['password']) : '');
$tmpdb = 'shuletech_build_' . substr(bin2hex(random_bytes(3)), 0, 6);
$m = new mysqli($c['hostname'], $c['username'], $c['password']);
$m->query("CREATE DATABASE `$tmpdb` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
register_shutdown_function(function () use ($m, $tmpdb) { try { $m->query("DROP DATABASE IF EXISTS `$tmpdb`"); } catch (Throwable $e) {} });

$copy = $exe('mysqldump') . " $auth --single-transaction " . escapeshellarg($c['database']) . " | " . $exe('mysql') . " $auth $tmpdb";
exec($copy . ' 2>&1', $o, $rc);
if ($rc !== 0) { fail('could not copy the local database: ' . implode(' ', $o)); }
$m->select_db($tmpdb);

// seeded test rows (by manifest)
if (is_file("$root/backup/seed-manifest.json")) {
    $man = json_decode(file_get_contents("$root/backup/seed-manifest.json"), true);
    foreach (array_reverse($man['order']) as $t) {
        foreach (array_chunk(array_map('intval', $man['ids'][$t]), 500) as $chunk) { $m->query("DELETE FROM `$t` WHERE id IN (" . implode(',', $chunk) . ')'); }
    }
}
// transient data
foreach (['userlog', 'logs', 'captcha', 'mpesa_transactions', 'users_authentication', 'payment_settings'] as $t) { $m->query("TRUNCATE TABLE `$t`"); }
// personal details and keys -> neutral defaults (the installer fills these in)
$m->query("UPDATE sch_settings SET name = 'My School', email = '', phone = '', address = '', base_url = '', folder_path = '', cron_secret_key = ''");
$adminId = (int) $m->query("SELECT staff_id FROM staff_roles WHERE role_id = (SELECT id FROM roles WHERE name = 'Super Admin' LIMIT 1) ORDER BY staff_id LIMIT 1")->fetch_row()[0];
$q = $m->prepare("UPDATE staff SET name = 'Super', surname = 'Admin', email = 'admin@example.com', contact_no = '', emergency_contact_no = '', local_address = '', permanent_address = '', password = ? WHERE id = ?");
$placeholder = password_hash(bin2hex(random_bytes(24)), PASSWORD_DEFAULT);
$q->bind_param('si', $placeholder, $adminId);
$q->execute();

// safety net: refuse to ship real or test records
$must_be_empty = ['students', 'student_session', 'users', 'classes', 'sections', 'student_fees_deposite', 'student_fees_master', 'income', 'expenses',
                  'enquiry', 'student_attendences', 'staff_attendance', 'visitors_book', 'general_calls', 'complaint', 'messages', 'notification', 'book_issues'];
$problems = [];
foreach ($must_be_empty as $t) {
    if (!$m->query("SHOW TABLES LIKE '$t'")->num_rows) { continue; }
    $n = (int) $m->query("SELECT COUNT(*) FROM `$t`")->fetch_row()[0];
    if ($n > 0) { $problems[] = "$t ($n rows)"; }
}
$staff = (int) $m->query('SELECT COUNT(*) FROM staff')->fetch_row()[0];
if ($staff !== 1) { $problems[] = "staff ($staff rows, expected only the admin)"; }
if ($problems) { fail("the local database has data that would be shipped: " . implode(', ', $problems) . ". Remove it locally first (test data: seed_test_data.php --remove)."); }

$sqlgz = "$root/install/sql/shuletech.sql.gz";   // git-ignored; removed again after the build
$dump = $exe('mysqldump') . " $auth --single-transaction --skip-comments --skip-dump-date --default-character-set=utf8mb4 $tmpdb";
$pipe = popen($dump . ' 2>&1', 'r');
$gz = gzopen($sqlgz, 'wb9');
$bytes = 0;
while (!feof($pipe)) { $chunk = fread($pipe, 1 << 20); $bytes += strlen($chunk); gzwrite($gz, $chunk); }
gzclose($gz);
if (pclose($pipe) !== 0 || $bytes < 100000) { fail('database export failed'); }
say('clean database: ' . round($bytes / 1024) . ' KB uncompressed');

/* ---------- 3. zip (Windows tar / bsdtar: far faster than ZipArchive for ~40k files) ---------- */
$list = $code;
$list[] = 'install/sql/shuletech.sql.gz';
// default uploads (logos, login images, default photos) - but no generated student barcodes/QR codes
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator("$root/uploads", FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
$up = 0;
foreach ($it as $file) {
    $rel = 'uploads/' . str_replace(chr(92), '/', substr($file->getPathname(), strlen($root . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR)));
    if ($file->isDir()) { $list[] = $rel; continue; }
    if (preg_match('#/(barcodes|qrcode)/#', $rel) && basename($rel) !== 'index.html') { continue; }
    $list[] = $rel;
    $up++;
}
// writable folders the app expects (added empty - no logs/cache contents)
foreach (['uploads', 'temp', 'application/cache', 'application/logs', 'backend/captcha_images'] as $d) { if (is_dir("$root/$d")) { $list[] = $d; } }
$listFile = tempnam(sys_get_temp_dir(), 'stlist');
file_put_contents($listFile, implode("\n", array_unique($list)) . "\n");
@unlink($out);
$tar = is_file('C:/Windows/System32/tar.exe') ? '"C:' . chr(92) . 'Windows' . chr(92) . 'System32' . chr(92) . 'tar.exe"' : 'bsdtar';
exec("$tar -a -c -n -f " . escapeshellarg($out) . ' -T ' . escapeshellarg($listFile) . ' 2>&1', $o, $rc);
@unlink($listFile);
@unlink($sqlgz);
if ($rc !== 0 || !is_file($out)) { fail('zip failed: ' . implode(' ', array_slice($o, 0, 5))); }
say("uploads: $up files");
say('OK  ' . str_replace(chr(92), '/', $out) . '  (' . round(filesize($out) / 1048576, 1) . ' MB)');