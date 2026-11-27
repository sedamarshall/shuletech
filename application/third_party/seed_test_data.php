<?php
/*
 | SHULETECH - test data seeder (LOCAL TESTING ONLY - never run on the live server)
 |
 |   Seed:    C:\xampp\php\php.exe application\third_party\seed_test_data.php
 |   Remove:  C:\xampp\php\php.exe application\third_party\seed_test_data.php --remove
 |
 | Every inserted row id is written to backup/seed-manifest.json, so --remove deletes exactly the
 | seeded rows and nothing entered by hand. Test logins go to backup/test-accounts.txt
 | (backup/ is blocked from the web by its .htaccess).
 */
if (PHP_SAPI !== 'cli') { exit('CLI only'); }

$root = realpath(__DIR__ . '/../../') . '/';
define('BASEPATH', $root . 'system/');
define('ENVIRONMENT', 'production');
$db = [];
$active_group = 'default';
$query_builder = true;
include $root . 'application/config/database.php';
$c = $db[$active_group];
$m = new mysqli($c['hostname'], $c['username'], $c['password'], $c['database']);
if ($m->connect_errno) { exit("DB connect failed: {$m->connect_error}\n"); }
$m->set_charset('utf8mb4');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$manifestFile = $root . 'backup/seed-manifest.json';
$accountsFile = $root . 'backup/test-accounts.txt';

/* ---------------- remove ---------------- */
if (in_array('--remove', $argv, true)) {
    if (!is_file($manifestFile)) { exit("No manifest found - nothing to remove.\n"); }
    $man = json_decode(file_get_contents($manifestFile), true);
    $m->begin_transaction();
    // delete children first (reverse of insert order)
    foreach (array_reverse($man['order']) as $table) {
        $ids = array_map('intval', $man['ids'][$table]);
        foreach (array_chunk($ids, 500) as $chunk) {
            $m->query("DELETE FROM `$table` WHERE id IN (" . implode(',', $chunk) . ")");
        }
        echo str_pad($table, 26) . count($ids) . " removed\n";
    }
    $m->commit();
    unlink($manifestFile);
    @unlink($accountsFile);
    exit("Seed data removed.\n");
}
if (is_file($manifestFile)) { exit("Seed data already present (backup/seed-manifest.json). Run with --remove first.\n"); }

/* ---------------- helpers ---------------- */
$man = ['created' => date('c'), 'order' => [], 'ids' => []];
function ins($table, array $row)
{
    global $m, $man;
    $cols = '`' . implode('`,`', array_keys($row)) . '`';
    $vals = implode(',', array_map(function ($v) use ($m) {
        return $v === null ? 'NULL' : "'" . $m->real_escape_string((string) $v) . "'";
    }, array_values($row)));
    $m->query("INSERT INTO `$table` ($cols) VALUES ($vals)");
    $id = $m->insert_id;
    if (!isset($man['ids'][$table])) { $man['ids'][$table] = []; $man['order'][] = $table; }
    $man['ids'][$table][] = $id;
    return $id;
}
// if anything fails half-way, still record what was inserted so --remove can clean it up
register_shutdown_function(function () use ($manifestFile) {
    global $man;
    if (!empty($man['ids']) && !is_file($manifestFile)) { file_put_contents($manifestFile, json_encode($man, JSON_PRETTY_PRINT)); }
});
function pick(array $a) { return $a[array_rand($a)]; }
function phone() { return '07' . mt_rand(10, 99) . mt_rand(100000, 999999); }
mt_srand(20261120);

$set       = $m->query("SELECT session_id, lang_id, start_month FROM sch_settings LIMIT 1")->fetch_assoc();
$startMonth = max(1, (int) $set['start_month']);
$sessionId = (int) $set['session_id'];
$langId    = (int) $set['lang_id'];
$session   = $m->query("SELECT session FROM sessions WHERE id = $sessionId")->fetch_assoc()['session'];
$startYear = (int) substr($session, 0, 4);              // e.g. 2025 for "2025-26"
$today     = date('Y-m-d');
$adminId   = 1;

// one shared test password for every seeded login
$testPassword = 'Test-' . bin2hex(random_bytes(4));
$staffHash    = password_hash($testPassword, PASSWORD_DEFAULT);

$first_m = ['Brian', 'Kevin', 'Dennis', 'Collins', 'Ian', 'Victor', 'Felix', 'Samuel', 'Peter', 'Joseph', 'Daniel', 'Elvis', 'Allan', 'Moses', 'Tony', 'Mark'];
$first_f = ['Faith', 'Mercy', 'Grace', 'Joy', 'Sharon', 'Esther', 'Cynthia', 'Ann', 'Wanjiru', 'Akinyi', 'Njeri', 'Chebet', 'Achieng', 'Mumbua', 'Halima', 'Neema'];
$last    = ['Otieno', 'Mwangi', 'Kamau', 'Wanjiku', 'Ochieng', 'Kiprono', 'Mutua', 'Njoroge', 'Odhiambo', 'Kariuki', 'Chepkoech', 'Omondi', 'Wafula', 'Barasa', 'Maina', 'Kiplagat', 'Nyambura', 'Onyango', 'Muthoni', 'Ruto'];
$towns   = ['Nairobi', 'Kisumu', 'Nakuru', 'Eldoret', 'Thika', 'Machakos', 'Kiambu'];

/* ---------------- staff ---------------- */
$deptIds = [];
foreach (['Academics', 'Finance', 'Administration', 'Library'] as $d) { $deptIds[$d] = ins('department', ['department_name' => $d, 'is_active' => 'yes']); }
$desigIds = [];
foreach (['Class Teacher', 'Subject Teacher', 'Bursar', 'Librarian', 'Front Desk'] as $d) { $desigIds[$d] = ins('staff_designation', ['designation' => $d, 'is_active' => 'yes']); }

$staffPlan = [
    ['Teacher', 'Class Teacher', 'Academics'], ['Teacher', 'Class Teacher', 'Academics'], ['Teacher', 'Class Teacher', 'Academics'],
    ['Teacher', 'Class Teacher', 'Academics'], ['Teacher', 'Class Teacher', 'Academics'], ['Teacher', 'Class Teacher', 'Academics'],
    ['Teacher', 'Subject Teacher', 'Academics'], ['Teacher', 'Subject Teacher', 'Academics'],
    ['Accountant', 'Bursar', 'Finance'], ['Librarian', 'Librarian', 'Library'], ['Receptionist', 'Front Desk', 'Administration'],
];
$roleIds = [];
$r = $m->query("SELECT id, name FROM roles");
while ($row = $r->fetch_assoc()) { $roleIds[$row['name']] = (int) $row['id']; }
$staffIds = [];
$accounts = [];
foreach ($staffPlan as $i => $p) {
    $female = $i % 2 === 0;
    $fn = $female ? pick($first_f) : pick($first_m);
    $ln = pick($last);
    $email = strtolower($fn . '.' . $ln . ($i + 1)) . '@test.shuletech.local';
    $sid = ins('staff', [
        'employee_id' => 'TST' . (1001 + $i), 'lang_id' => $langId, 'department' => $deptIds[$p[2]], 'designation' => $desigIds[$p[1]],
        'qualification' => $p[0] === 'Teacher' ? 'B.Ed' : 'Diploma', 'work_exp' => mt_rand(1, 15) . ' years',
        'name' => $fn, 'surname' => $ln, 'contact_no' => phone(), 'email' => $email,
        'dob' => date('Y-m-d', strtotime('-' . mt_rand(25, 55) . ' years -' . mt_rand(0, 300) . ' days')), 'marital_status' => pick(['Single', 'Married']),
        'date_of_joining' => date('Y-m-d', strtotime('-' . mt_rand(1, 8) . ' years')), 'local_address' => pick($towns), 'permanent_address' => pick($towns),
        'password' => $staffHash, 'gender' => $female ? 'Female' : 'Male', 'basic_salary' => mt_rand(35, 90) * 1000,
        'contract_type' => 'permanent', 'is_active' => 1, 'note' => 'SEEDED TEST DATA',
    ]);
    ins('staff_roles', ['role_id' => $roleIds[$p[0]], 'staff_id' => $sid, 'is_active' => 0]);
    $staffIds[] = $sid;
    $accounts[] = "Staff  ({$p[0]})  " . str_pad($email, 46) . "  site/login";
}

/* ---------------- classes & sections ---------------- */
$sectionIds = [];
foreach (['East', 'West'] as $s) { $sectionIds[$s] = ins('sections', ['section' => $s, 'is_active' => 'no']); }
$classes = [];
foreach (['Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6'] as $n => $cn) {
    $cid = ins('classes', ['class' => $cn, 'is_active' => 'no']);
    foreach ($sectionIds as $sec => $secId) {
        ins('class_sections', ['class_id' => $cid, 'section_id' => $secId, 'is_active' => 'no']);
        $classes[] = ['class_id' => $cid, 'section_id' => $secId, 'grade' => $n + 1];
    }
}

/* ---------------- fees: one Term 3 group for everyone ---------------- */
$feeTypes = [['Tuition', 'TUI', 8000], ['Activity', 'ACT', 1500], ['Lunch', 'LUN', 3500]];
$fgId  = ins('fee_groups', ['name' => 'Term 3 ' . date('Y'), 'description' => 'SEEDED TEST DATA', 'is_active' => 'no']);
$fsgId = ins('fee_session_groups', ['fee_groups_id' => $fgId, 'session_id' => $sessionId, 'is_active' => 'no']);
$fgft  = [];
foreach ($feeTypes as $ft) {
    $tid = ins('feetype', ['type' => $ft[0], 'code' => $ft[1], 'is_active' => 'no', 'description' => 'SEEDED TEST DATA']);
    $fgft[] = ['id' => ins('fee_groups_feetype', ['fee_session_group_id' => $fsgId, 'fee_groups_id' => $fgId, 'feetype_id' => $tid, 'session_id' => $sessionId,
        'amount' => $ft[2], 'due_date' => date('Y-m-d', strtotime(date('Y-m-01') . ' +14 days')), 'is_active' => 'no']), 'amount' => $ft[2]];
}

/* ---------------- students, parents, fees paid, attendance ---------------- */
$attDays = [];
for ($d = 0, $n = 0; $n < 10; $d++) {                       // last 10 school days incl. today
    $day = date('Y-m-d', strtotime("-$d days"));
    if (date('N', strtotime($day)) <= 5) { $attDays[] = $day; $n++; }
}
$adm = 2026001;
$studentCount = 0; $firstStudentLogin = null;
$monthStart = date('Y-m-01');
foreach ($classes as $cls) {
    for ($k = 0; $k < 8; $k++) {
        $female = (bool) mt_rand(0, 1);
        $fn = $female ? pick($first_f) : pick($first_m);
        $ln = pick($last);
        $father = pick($first_m) . ' ' . $ln;
        $mother = pick($first_f) . ' ' . $ln;
        $gPhone = phone();
        $stId = ins('students', [
            'parent_id' => 0, 'admission_no' => (string) $adm++, 'roll_no' => (string) ($k + 1), 'admission_date' => ($startYear) . '-01-' . sprintf('%02d', mt_rand(5, 20)),
            'firstname' => $fn, 'lastname' => $ln, 'mobileno' => '', 'email' => '', 'city' => pick($towns), 'state' => 'Kenya',
            'religion' => pick(['Christian', 'Muslim', 'Christian', 'Christian']), 'dob' => date('Y-m-d', strtotime('-' . (5 + $cls['grade']) . ' years -' . mt_rand(0, 360) . ' days')),
            'gender' => $female ? 'Female' : 'Male', 'current_address' => pick($towns), 'permanent_address' => pick($towns), 'blood_group' => pick(['A+', 'B+', 'O+', 'AB+', 'O-']),
            'guardian_is' => 'father', 'father_name' => $father, 'father_phone' => $gPhone, 'father_occupation' => pick(['Farmer', 'Teacher', 'Driver', 'Trader', 'Nurse', 'Engineer']),
            'mother_name' => $mother, 'mother_phone' => phone(), 'mother_occupation' => pick(['Trader', 'Nurse', 'Teacher', 'Tailor', 'Accountant']),
            'guardian_name' => $father, 'guardian_relation' => 'Father', 'guardian_phone' => $gPhone, 'guardian_address' => pick($towns),
            'guardian_email' => strtolower(str_replace(' ', '.', $father)) . mt_rand(100, 999) . '@test.shuletech.local',
            'is_active' => 'yes', 'note' => 'SEEDED TEST DATA', 'created_by' => $adminId,
        ]);
        $ssId = ins('student_session', ['session_id' => $sessionId, 'student_id' => $stId, 'class_id' => $cls['class_id'], 'section_id' => $cls['section_id'], 'is_active' => 'no', 'default_login' => 1]);
        ins('users', ['user_id' => $stId, 'username' => 'std' . $stId, 'password' => $testPassword, 'childs' => '', 'role' => 'student', 'lang_id' => $langId, 'is_active' => 'yes']);
        $pid = ins('users', ['user_id' => 0, 'username' => 'parent' . $stId, 'password' => $testPassword, 'childs' => (string) $stId, 'role' => 'parent', 'lang_id' => $langId, 'is_active' => 'yes']);
        $m->query("UPDATE students SET parent_id = $pid WHERE id = $stId");
        if ($firstStudentLogin === null) { $firstStudentLogin = ['std' . $stId, 'parent' . $stId]; }

        // fees: ~55% paid in full, ~25% partly, ~20% nothing yet
        $sfmId = ins('student_fees_master', ['student_session_id' => $ssId, 'fee_session_group_id' => $fsgId, 'amount' => 0, 'is_active' => 'no']);
        $roll = mt_rand(1, 100);
        foreach ($fgft as $idx => $line) {
            if ($roll > 80) { break; }
            if ($roll > 55 && $idx > 0) { break; }                     // partial: tuition only
            $amt = ($roll > 55) ? round($line['amount'] * mt_rand(40, 100) / 100) : $line['amount'];
            $day = date('Y-m-d', strtotime($monthStart . ' +' . mt_rand(0, max(0, (int) date('j') - 1)) . ' days'));
            ins('student_fees_deposite', ['student_fees_master_id' => $sfmId, 'fee_groups_feetype_id' => $line['id'], 'is_active' => 'no',
                'amount_detail' => json_encode(['1' => ['amount' => (string) $amt, 'amount_discount' => '0', 'amount_fine' => '0', 'date' => $day,
                    'description' => 'Seeded payment', 'collected_by' => 'Super Admin(9000)', 'payment_mode' => pick(['Cash', 'Cash', 'Bank Transfer', 'Cheque']),
                    'received_by' => (string) $adminId, 'inv_no' => 1]])]);
        }

        // attendance: mostly present
        foreach ($attDays as $day) {
            $t = mt_rand(1, 100);
            $type = $t <= 86 ? 1 : ($t <= 92 ? 3 : ($t <= 97 ? 4 : 6));    // present / late / absent / half day
            ins('student_attendences', ['student_session_id' => $ssId, 'date' => $day, 'attendence_type_id' => $type, 'remark' => '', 'is_active' => 'no']);
        }
        $studentCount++;
    }
}

/* staff attendance for the same days */
foreach ($attDays as $day) {
    foreach ($staffIds as $sid) {
        $t = mt_rand(1, 100);
        ins('staff_attendance', ['date' => $day, 'staff_id' => $sid, 'staff_attendance_type_id' => $t <= 88 ? 1 : ($t <= 95 ? 2 : 3), 'remark' => '', 'is_active' => 1, 'created_at' => $day . ' 08:00:00']);
    }
}

/* ---------------- income & expenses: every month of the session + this month ---------------- */
$incHeads = []; foreach (['Donations', 'Uniform Sales', 'Hall Hire', 'Canteen'] as $h) { $incHeads[] = ins('income_head', ['income_category' => $h, 'description' => 'SEEDED TEST DATA']); }
$expHeads = []; foreach (['Salaries', 'Utilities', 'Stationery', 'Maintenance', 'Transport Fuel'] as $h) { $expHeads[] = ins('expense_head', ['exp_category' => $h, 'description' => 'SEEDED TEST DATA']); }
$months = [];
for ($i = 0; $i < 12; $i++) { $ym = date('Y-m', strtotime(sprintf('%d-%02d-01', $startYear, $startMonth) . " +$i months")); if ($ym <= date('Y-m')) { $months[] = $ym; } }   // session months up to now
$months[] = date('Y-m');                                                                             // and the current month
$inv = 5000;
foreach (array_unique($months) as $ym) {
    $daysIn = (int) date('t', strtotime("$ym-01"));
    $lastDay = ($ym === date('Y-m')) ? (int) date('j') : $daysIn;
    for ($i = 0; $i < 6; $i++) {
        $d = "$ym-" . sprintf('%02d', mt_rand(1, $lastDay));
        ins('income', ['income_head_id' => pick($incHeads), 'name' => pick(['Parents association', 'Uniform shop', 'Church group', 'Canteen takings', 'Sports club']),
            'invoice_no' => 'INC' . $inv++, 'date' => $d, 'amount' => mt_rand(15, 120) * 100, 'note' => 'SEEDED TEST DATA', 'is_active' => 'yes', 'is_deleted' => 'no']);
    }
    for ($i = 0; $i < 7; $i++) {
        $d = "$ym-" . sprintf('%02d', mt_rand(1, $lastDay));
        $h = pick($expHeads);
        ins('expenses', ['exp_head_id' => $h, 'name' => pick(['KPLC', 'Nairobi Water', 'Text Book Centre', 'Hardware supplies', 'Fuel station', 'Payroll']),
            'invoice_no' => 'EXP' . $inv++, 'date' => $d, 'amount' => ($h === $expHeads[0] ? mt_rand(300, 600) : mt_rand(10, 90)) * 100, 'note' => 'SEEDED TEST DATA', 'is_active' => 'yes', 'is_deleted' => 'no']);
    }
}

/* ---------------- admission enquiries ---------------- */
$statuses = ['active', 'active', 'active', 'won', 'won', 'passive', 'lost', 'dead'];
for ($i = 0; $i < 14; $i++) {
    $nm = pick(array_merge($first_m, $first_f)) . ' ' . pick($last);
    $d = date('Y-m-d', strtotime('-' . mt_rand(0, 40) . ' days'));
    ins('enquiry', ['name' => $nm, 'contact' => phone(), 'address' => pick($towns), 'reference' => '', 'date' => $d,
        'description' => 'Asking about admission for ' . pick(['Grade 1', 'Grade 2', 'Grade 4', 'Grade 6']), 'follow_up_date' => date('Y-m-d', strtotime($d . ' +7 days')),
        'note' => 'SEEDED TEST DATA', 'source' => pick(['Phone', 'Walk-in', 'Website', 'Referral']), 'assigned' => $adminId, 'class_id' => $classes[mt_rand(0, count($classes) - 1)]['class_id'],
        'no_of_child' => (string) mt_rand(1, 2), 'status' => $statuses[$i % count($statuses)], 'created_by' => $adminId]);
}

/* ---------------- write manifest + accounts ---------------- */
file_put_contents($manifestFile, json_encode($man, JSON_PRETTY_PRINT));
$txt = "SHULETECH LOCAL TEST ACCOUNTS (seeded " . date('Y-m-d H:i') . ") - LOCAL TESTING ONLY\n"
     . "Password for every account below: $testPassword\n\n"
     . "Students: std<ID>    e.g. {$firstStudentLogin[0]}    -> http://localhost/shuletech/site/userlogin\n"
     . "Parents:  parent<ID> e.g. {$firstStudentLogin[1]} -> http://localhost/shuletech/site/userlogin\n\n"
     . implode("\n", $accounts) . "\n\n"
     . "Remove all seeded data: C:\\xampp\\php\\php.exe application\\third_party\\seed_test_data.php --remove\n";
file_put_contents($accountsFile, $txt);

echo "Seeded: " . count($staffIds) . " staff, " . count($classes) . " class-sections, $studentCount students (+ parent logins), "
   . count($man['ids']['student_fees_deposite'] ?? []) . " fee payments, " . count($man['ids']['student_attendences'] ?? []) . " student attendance rows, "
   . count($man['ids']['income'] ?? []) . " income, " . count($man['ids']['expenses'] ?? []) . " expenses, " . count($man['ids']['enquiry'] ?? []) . " enquiries.\n"
   . "Logins written to backup/test-accounts.txt\n";
