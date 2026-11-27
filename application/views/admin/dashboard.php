<?php $currency_symbol = $this->customlib->getSchoolCurrencyFormat();?>
<style type="text/css">
    /* .borderwhite{border-top-color: #fff !important;} */
    .box-header>.box-tools {display: none;}
    .sidebar-collapse #barChart{height: 100% !important;}
    .sidebar-collapse #lineChart{height: 100% !important;}
    .tooltip-inner {max-width: 135px;}
</style>
<div class="content-wrapper">
    <section class="content">
        <div class="st-welcome">
            <div>
                <h1>Welcome back, <?php echo html_escape(trim($this->customlib->getAdminSessionUserName())); ?></h1>
                <p>Here's what's happening at <?php echo html_escape($this->setting_model->getCurrentSchoolName()); ?> today.</p>
                <div class="st-welcome-meta">
                    <span><i class="icon-calendar"></i> <?php echo date('l, j F Y'); ?></span>
                    <span><i class="icon-graduation-cap"></i> Session <?php echo html_escape($this->setting_model->getCurrentSessionName()); ?></span>
                </div>
            </div>
            <div class="st-welcome-actions">
                <?php if ($this->rbac->hasPrivilege('student', 'can_add')) { ?>
                    <a href="<?php echo site_url('student/create'); ?>" class="btn btn-default"><i class="icon-user-plus"></i> Add student</a>
                <?php } ?>
                <?php if ($this->module_lib->hasActive('fees_collection') && $this->rbac->hasPrivilege('collect_fees', 'can_view')) { ?>
                    <a href="<?php echo site_url('studentfee'); ?>" class="btn btn-primary"><i class="icon-wallet"></i> Collect fees</a>
                <?php } ?>
            </div>
        </div>
        <div class="">
            <?php if (ENVIRONMENT != 'production') { ?>
                <div class="alert alert-danger">
                    Environment set to <?php echo ENVIRONMENT ;?>! <br>
                    Don't forget to set back to production in the main index.php file after finishing your tests or <?php echo ENVIRONMENT ;?>. <br>
                    Please be aware that in <?php echo ENVIRONMENT ;?> mode you may see some errors and deprecation warnings, for this reason, it's always recommended to set the environment to "production" if you are not actually developing some features/modules or trying to test some code.
                </div>
            <?php } ?>
                
            <?php if ($mysqlVersion && $sqlMode && strpos($sqlMode->mode, 'ONLY_FULL_GROUP_BY') !== false) {?>
                <div class="alert alert-danger">
                    Smart School may not work properly because ONLY_FULL_GROUP_BY is enabled, consult with your hosting provider to disable ONLY_FULL_GROUP_BY in sql_mode configuration.
                </div>
            <?php }?>

            <?php
$show    = false;
$role    = $this->customlib->getStaffRole();
$role_id = json_decode($role)->id;
foreach ($notifications as $notice_key => $notice_value) {

    if ($role_id == 7) {
        $show = true;
    } elseif (date($this->customlib->getSchoolDateFormat()) >= date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($notice_value->publish_date))) {
        $show = true;
    }
    if ($show) {
        ?>
                    <div class="dashalert alert alert-success alert-dismissible" role="alert">
                        <button type="button" class="alertclose close close_notice" data-dismiss="alert" aria-label="Close" data-noticeid="<?php echo $notice_value->id; ?>"><span aria-hidden="true">&times;</span></button>
                        <a href="<?php echo site_url('admin/notification') ?>"><?php echo $notice_value->title; ?></a>
                    </div>
                    <?php
}
}
?>
        </div>
		
        <div class="row">

            <?php
            $resources = [];
            foreach ($storageused as $res) {
                $resources[$res['resource_id']] = $res;
            }
            $resourceIds = [15, 16, 17];

            foreach ($resourceIds as $rid):
                if (!isset($resources[$rid])) continue;

                $res = $resources[$rid];
            ?>
                <div class="col-md-4">
                    <div class="dashalert alert" role="alert">
                        <strong><?= html_escape($res['resource_name']) ?></strong><br>

                        Resource Limit :
                        <?= html_escape($res['resource_limit']) ?>
                        <?= isset($res['resource_unit']) ? html_escape($res['resource_unit']) : '' ?>
                        <br>

                        Resource Usage :
                        <?= html_escape($res['resource_usage']) ?>
                        <?= isset($res['resource_unit']) ? html_escape($res['resource_unit']) : '' ?>
                    </div>
                </div>
            <?php endforeach; ?>

        </div>
		
		
        <div class="row">
            <?php
if ($this->module_lib->hasActive('fees_collection')) {
    if ($this->rbac->hasPrivilege('fees_awaiting_payment_widegts', 'can_view')) {
        ?>
                    <div class="<?php echo $std_graphclass; ?>">
                        <?php echo st_stat_card('wallet', 'blue', $this->lang->line('fees_awaiting_payment'), $total_paid, $total_fees); ?>
                    </div><!--./col-md-3-->
                    <?php
}
}
?>


<?php 
    if ($this->rbac->hasPrivilege('staff_approved_leave_widegts', 'can_view')) {
        ?>
                    <div class="<?php echo $std_graphclass; ?>">
                        <?php echo st_stat_card('calendar-x', 'rose', $this->lang->line('staff_approved_leave'), ($getStaffApproveMonthlyLeave) + 0, ($getStaffMonthlyLeave)); ?>
                    </div><!--./col-md-3-->
                    <?php
}
 ?>
 
<?php 
    if ($this->rbac->hasPrivilege('student_approved_leave_widegts', 'can_view')) {
        ?>
                    <div class="<?php echo $std_graphclass; ?>">
                        <?php echo st_stat_card('calendar-minus', 'amber', $this->lang->line('student_approved_leave'), ($getStudentApproveMonthlyLeave) + 0, ($getStudentMonthlyLeave)); ?>
                    </div><!--./col-md-3-->
                    <?php
}
  ?>


            <?php
if ($this->module_lib->hasActive('front_office')) {
    if ($this->rbac->hasPrivilege('conveted_leads_widegts', 'can_view')) {
        ?>
                    <div class="<?php echo $std_graphclass; ?>">
                        <?php echo st_stat_card('target', 'violet', $this->lang->line('converted_leads'), $total_complete + 0, $total_enquiry); ?>
                    </div><!--./col-md-3-->
                    <?php
}
} ?>
	
	<?php
if ($this->rbac->hasPrivilege('staff_present_today_widegts', 'can_view')) {
    ?>
                <div class="<?php echo $std_graphclass; ?>">
                    <?php echo st_stat_card('user-check', 'green', $this->lang->line('staff_present_today'), $Staffattendence_data + 0, $getTotalStaff_data); ?>
                </div><!--./col-md-3-->
                <?php
} ?>

<?php
if ($this->module_lib->hasActive('student_attendance') && $sch_setting->attendence_type == 0) {
    if ($this->rbac->hasPrivilege('student_present_today_widegts', 'can_view')) {
        ?>
                    <div class="<?php echo $std_graphclass; ?>">
                        <?php echo st_stat_card('users', 'blue', $this->lang->line('student_present_today'), 0 + $attendence_data['total_half_day'] + $attendence_data['total_late'] + $attendence_data['total_present'], $total_students); ?>
                    </div><!--./col-md-3-->
                <?php }
}
?>

 
  
<!-- added new features -->

        </div><!--./row-->
        <div class="row st-kpi-row">
                
<?php
if ($this->module_lib->hasActive('fees_collection')) {
    if ($this->rbac->hasPrivilege('Monthly fees_collection_widget', 'can_view')) {
        ?>
                                <div class="col-lg-3 col-md-6 col-sm-6">
                                    <a class="st-card st-kpi" href="<?php echo site_url('studentfee') ?>"><div class="st-kpi-top"><span class="st-chip st-tint-green"><i class="icon-banknote"></i></span><span class="st-meta">Fees · this month</span></div><div class="st-kpi-value"><?php echo $currency_symbol . amountFormat((float) $month_collection); ?></div><div class="st-kpi-label"><?php echo $this->lang->line('monthly_fees_collection'); ?></div></a>
                                </div>
    <?php }
}
?>
<?php
if ($this->module_lib->hasActive('expense')) {
    if ($this->rbac->hasPrivilege('monthly_expense_widget', 'can_view')) {
        ?>
                                <div class="col-lg-3 col-md-6 col-sm-6">
                                    <a class="st-card st-kpi" href="<?php echo site_url('admin/expense') ?>"><div class="st-kpi-top"><span class="st-chip st-tint-rose"><i class="icon-credit-card"></i></span><span class="st-meta">Expenses · this month</span></div><div class="st-kpi-value"><?php echo $currency_symbol . amountFormat((float) $month_expense); ?></div><div class="st-kpi-label"><?php echo $this->lang->line('monthly_expenses'); ?></div></a>
                                </div>
    <?php
}
}

if ($this->rbac->hasPrivilege('student_count_widget', 'can_view')) {
    ?>
                            <div class="col-lg-3 col-md-6 col-sm-6">
                                <a class="st-card st-kpi" href="<?php echo site_url('student/search') ?>"><div class="st-kpi-top"><span class="st-chip st-tint-blue"><i class="icon-graduation-cap"></i></span><span class="st-meta">Current session</span></div><div class="st-kpi-value"><?php echo $total_students; ?></div><div class="st-kpi-label"><?php echo $this->lang->line('student'); ?></div></a>
                            </div>
<?php } ?>

<!-- added new features -->
   <?php if ($this->rbac->hasPrivilege('student_head_count_widget', 'can_view')) {  ?>
                            <div class="col-lg-3 col-md-6 col-sm-6">
                                <a class="st-card st-kpi" href="<?php echo site_url('student/search') ?>"><div class="st-kpi-top"><span class="st-chip st-tint-amber"><i class="icon-users"></i></span><span class="st-meta">Current session</span></div><div class="st-kpi-value"><?php echo $total_students_heads; ?></div><div class="st-kpi-label"><?php echo $this->lang->line('student_head_count'); ?></div></a>
                            </div>
                        <?php }?>
       
<?php
if ($this->rbac->hasPrivilege('staff_role_count_widget', 'can_view')) {
    ?>
    <?php foreach ($roles as $key => $value) {
        ?>
                    <div class="col-lg-2 col-md-4 col-sm-6">
    
                            <a class="st-card st-kpi st-kpi-sm" href="<?php echo site_url('admin/staff'); ?>"><div class="st-kpi-top"><span class="st-chip st-tint-violet"><i class="icon-user-cog"></i></span></div><div class="st-kpi-value"><?php echo $value; ?></div><div class="st-kpi-label"><?php echo $key; ?></div></a>
   
                    </div><!--./col-lg-3-->
                     <?php }?>
<?php }?>
            </div><!--./row-->
        <div class="row">
            <?php
$bar_chart = true;

if (($this->module_lib->hasActive('fees_collection')) || ($this->module_lib->hasActive('expense'))) {
    if ($this->rbac->hasPrivilege('fees_collection_and_expense_monthly_chart', 'can_view')) {

        $div_rol  = 3;
        $userdata = $this->customlib->getUserData();
        ?>
                    <div class="col-lg-7 col-md-7 col-sm-12 col60">
                        <div class="box box-primary borderwhite">
                            <div class="box-header with-border">
                                <h3 class="box-title"><?php echo $this->lang->line('fees_collection_expenses_for'); ?> <?php echo $this->lang->line(strtolower(date('F'))) . " " . date('Y');

        ?></h3>
                                
                            </div>
                            <div class="box-body">
                                <div class="chart">
                                  <div class="st-chart-wrap"><canvas id="barChart"></canvas></div>
                                </div>
                            </div>
                        </div>
                    </div><!--./col-lg-7-->
                <?php }
}
?>
            <?php
if ($this->module_lib->hasActive('income')) {
    if ($this->rbac->hasPrivilege('income_donut_graph', 'can_view')) {
        ?>
                    <div class="col-lg-5 col-md-5 col-sm-12 col40">
                        <div class="box box-primary borderwhite">
                            <div class="box-header with-border"><h3 class="box-title"><?php echo $this->lang->line('income') . " - " . $this->lang->line(strtolower(date('F'))) . " " . date('Y');  ?></h3></div>
                            <div class="box-body">
                                <div class="chart-responsive">
                                    <div class="st-chart-wrap"><canvas id="doughnut-chart"></canvas></div>
                                </div>
                            </div>
                        </div><!--./col-md-6-->
                    </div><!--./col-lg-5-->
    <?php
}
}
?>
        </div><!--./row-->
        <div class="row">
            <?php
$line_chart = true;
if (($this->module_lib->hasActive('fees_collection')) || ($this->module_lib->hasActive('expense'))) {
    if ($this->rbac->hasPrivilege('fees_collection_and_expense_yearly_chart', 'can_view')) {
        $div_rol = 3;
        ?>
                    <div class="col-lg-7 col-md-7 col-sm-12 col60">
                        <div class="box box-info borderwhite">
                            <div class="box-header with-border">
                                <h3 class="box-title"><?php echo $this->lang->line('fees_collection_expenses_for_session'); ?> <?php echo $this->setting_model->getCurrentSessionName(); ?></h3>
                                <div class="box-tools pull-right">
                                    <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                                    <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                                </div>
                            </div>
                            <div class="box-body">
                                <div class="chart">
                                      <div class="st-chart-wrap"><canvas id="lineChart"></canvas></div>
                                </div>
                            </div>
                        </div>
                    </div><!--./col-lg-7-->
                    <?php
}
}
if ($this->module_lib->hasActive('expense')) {
    ?>
    <?php if ($this->rbac->hasPrivilege('expense_donut_graph', 'can_view')) {
        ?>
                    <div class="col-lg-5 col-md-5 col-sm-12 col40">
                        <div class="box box-primary borderwhite">
                            <div class="box-header with-border"><h3 class="box-title"><?php echo $this->lang->line('expense') . " - " . $this->lang->line(strtolower(date('F'))) . " " . date('Y');  ?></h3>
                            </div><!--./info-box-->
                            <div class="box-body">
                                <div class="chart-responsive">
                                    <div class="st-chart-wrap"><canvas id="doughnut-chart1"></canvas></div>
                                </div>
                            </div>
                        </div>
                    </div><!--./col-lg-5-->
    <?php }
}
?>
        </div><!--./row-->
        <div class="row row-flex3">

<?php
if ($this->module_lib->hasActive('fees_collection')) {
    if ($this->rbac->hasPrivilege('fees_overview_widegts', 'can_view')) {
        ?>
                      <div class="col-lg-3 col-md-6 col-sm-6 mb10">
                        <div class="topprograssstart flex-card">
                            <h5 class="pro-border"><?php echo $this->lang->line('fees_overview'); ?></h5>
                            <p class="text-uppercase mt10 clearfix"><?php echo $fees_overview['total_unpaid']; ?> <?php echo $this->lang->line('unpaid'); ?><span class="pull-right"><?php echo round($fees_overview['unpaid_progress'], 2); ?>%</span>
                            </p>
                            <div class="progress-group">
                                <div class="progress progress-minibar">
                                    <div class="progress-bar" style="width: <?php echo $fees_overview['unpaid_progress']; ?>%"></div>
                                </div>
                            </div>
                            <p class="text-uppercase mt10 clearfix"><?php echo $fees_overview['total_partial']; ?> <?php echo $this->lang->line('partial'); ?><span class="pull-right"><?php echo round($fees_overview['partial_progress'], 2); ?>%</span>
                            </p>
                            <div class="progress-group">
                                <div class="progress progress-minibar">
                                    <div class="progress-bar progress-bar-aqua" style="width: <?php echo $fees_overview['partial_progress']; ?>%"></div>
                                </div>
                            </div>
                            <p class="text-uppercase mt10 clearfix"><?php echo $fees_overview['total_paid']; ?> <?php echo $this->lang->line('paid'); ?><span class="pull-right"><?php echo round($fees_overview['paid_progress'], 2); ?>%</span>
                            </p>
                            <div class="progress-group">
                                <div class="progress progress-minibar">
                                    <div class="progress-bar progress-bar-aqua" style="width: <?php echo $fees_overview['paid_progress']; ?>%"></div>
                                </div>
                            </div>
                        </div><!--./topprograssstart-->
                    </div><!--./col-md-3-->
        <?php
}
}
if ($this->module_lib->hasActive('front_office')) {
    if ($this->rbac->hasPrivilege('enquiry_overview_widegts', 'can_view')) {
        ?>
                     <div class="col-lg-3 col-md-6 col-sm-6 mb10">
                        <div class="topprograssstart flex-card">
                            <h5 class="pro-border"><?php echo $this->lang->line('enquiry_overview'); ?></h5>
                            <p class="text-uppercase mt10 clearfix"><?php echo $enquiry_overview['active']; ?> <?php echo $this->lang->line('active') ?><span class="pull-right"><?php echo round($enquiry_overview['active_progress'], 2); ?>%</span>
                            </p>
                            <div class="progress-group">
                                <div class="progress progress-minibar">
                                    <div class="progress-bar progress-bar-red" style="width: <?php echo $enquiry_overview['active_progress']; ?>%"></div>
                                </div>
                            </div>
                            <p class="text-uppercase mt10 clearfix"><?php echo $enquiry_overview['won']; ?> <?php echo $this->lang->line('won') ?><span class="pull-right"><?php echo round($enquiry_overview['won_progress'], 2); ?>%</span>
                            </p>
                            <div class="progress-group">
                                <div class="progress progress-minibar">
                                    <div class="progress-bar progress-bar-yellow" style="width: <?php echo $enquiry_overview['won_progress']; ?>%"></div>
                                </div>
                            </div>
                            <p class="text-uppercase mt10 clearfix"><?php echo $enquiry_overview['passive']; ?> <?php echo $this->lang->line('passive') ?><span class="pull-right"><?php echo round($enquiry_overview['passive_progress'], 2); ?>%</span>
                            </p>
                            <div class="progress-group">
                                <div class="progress progress-minibar">
                                    <div class="progress-bar progress-bar-yellow" style="width: <?php echo $enquiry_overview['passive_progress']; ?>%"></div>
                                </div>
                            </div>
                            <p class="text-uppercase mt10 clearfix"><?php echo $enquiry_overview['lost']; ?> <?php echo $this->lang->line('lost') ?><span class="pull-right"><?php echo round($enquiry_overview['lost_progress'], 2); ?>%</span>
                            </p>
                            <div class="progress-group">
                                <div class="progress progress-minibar">
                                    <div class="progress-bar progress-bar-yellow" style="width: <?php echo $enquiry_overview['lost_progress']; ?>%"></div>
                                </div>
                            </div>
                            <p class="text-uppercase mt10 clearfix"><?php echo $enquiry_overview['dead']; ?> <?php echo $this->lang->line('dead') ?><span class="pull-right"><?php echo round($enquiry_overview['dead_progress'], 2); ?>%</span>
                            </p>
                            <div class="progress-group">
                                <div class="progress progress-minibar">
                                    <div class="progress-bar progress-bar-yellow" style="width: <?php echo $enquiry_overview['dead_progress']; ?>%"></div>
                                </div>
                            </div>
                        </div><!--./topprograssstart-->
                    </div><!--./col-md-3-->
        <?php
}
}

if ($this->module_lib->hasActive('library')) {
    if ($this->rbac->hasPrivilege('book_overview_widegts', 'can_view')) {
        ?>
                         <div class="col-lg-3 col-md-6 col-sm-6 mb10">
                        <div class="topprograssstart flex-card">
                            <h5 class="pro-border"><?php echo $this->lang->line('library_overview'); ?></h5>
                            <p class="text-uppercase mt10 clearfix"><?php echo $book_overview['dueforreturn']; ?> <?php echo $this->lang->line('due_for_return'); ?><span class="pull-right"></span>
                            </p>
                            <div class="progress-group">
                                <div class="progress progress-minibar">
                                    <div class="progress-bar progress-bar-green" style="width: <?php echo $book_overview['dueforreturn']; ?>%"></div>
                                </div>
                            </div>
                            <p class="text-uppercase mt10 clearfix"><?php echo $book_overview['forreturn']; ?> <?php echo $this->lang->line('returned') ?><span class="pull-right"></span>
                            </p>
                            <div class="progress-group">
                                <div class="progress progress-minibar">
                                    <div class="progress-bar progress-bar-green" style="width: <?php echo $book_overview['forreturn']; ?>%"></div>
                                </div>
                            </div>
                            <p class="text-uppercase mt10 clearfix"><?php echo $book_overview['total_issued']; ?> <?php echo $this->lang->line('issued_out_of'); ?> <?php echo $book_overview['total'] ?><span class="pull-right"><?php echo $book_overview['issued_progress']; ?>%</span>
                            </p>
                            <div class="progress-group">
                                <div class="progress progress-minibar">
                                    <div class="progress-bar progress-bar-green" style="width: <?php echo $book_overview['issued_progress']; ?>%"></div>
                                </div>
                            </div>
                            <p class="text-uppercase mt10 clearfix"><?php echo $book_overview['availble']; ?> <?php echo $this->lang->line('available_out_of') ?> <?php echo $book_overview['total']; ?><span class="pull-right"><?php echo $book_overview['availble_progress']; ?>%</span>
                            </p>
                            <div class="progress-group">
                                <div class="progress progress-minibar">
                                    <div class="progress-bar progress-bar-green" style="width: <?php echo $book_overview['availble_progress']; ?>%"></div>
                                </div>
                            </div>
                        </div><!--./topprograssstart-->
                    </div><!--./col-md-3-->
        <?php
}
}
if ($this->module_lib->hasActive('student_attendance')) {
    if ($this->rbac->hasPrivilege('today_attendance_widegts', 'can_view')) {
        ?>
                       <div class="col-lg-3 col-md-6 col-sm-6 mb10">
                        <div class="topprograssstart flex-card">
                            <h5 class="pro-border"> <?php echo $this->lang->line('student_today_attendance'); ?></h5>
                            <p class="text-uppercase mt10 clearfix"><?php echo $attendence_data['total_present']; ?> <?php echo $this->lang->line('present'); ?><span class="pull-right"><?php echo $attendence_data['present']; ?></span>
                            </p>
                            <div class="progress-group">
                                <div class="progress progress-minibar">
                                    <div class="progress-bar" style="width: <?php echo $attendence_data['present']; ?>"></div>
                                </div>
                            </div>
                            <p class="text-uppercase mt10 clearfix"><?php echo $attendence_data['total_late']; ?> <?php echo $this->lang->line('late') ?><span class="pull-right"><?php echo $attendence_data['late']; ?></span>
                            </p>
                            <div class="progress-group">
                                <div class="progress progress-minibar">
                                    <div class="progress-bar" style="width: <?php echo $attendence_data['late']; ?>"></div>
                                </div>
                            </div>
                            <p class="text-uppercase mt10 clearfix"><?php echo $attendence_data['total_absent']; ?> <?php echo $this->lang->line('absent'); ?><span class="pull-right"><?php echo $attendence_data['absent']; ?></span>
                            </p>
                            <div class="progress-group">
                                <div class="progress progress-minibar">
                                    <div class="progress-bar" style="width: <?php echo $attendence_data['absent']; ?>"></div>
                                </div>
                            </div>
                            <p class="text-uppercase mt10 clearfix"><?php echo $attendence_data['total_half_day']; ?> <?php echo $this->lang->line('half_day'); ?><span class="pull-right"><?php echo $attendence_data['half_day']; ?></span>
                            </p>
                            <div class="progress-group">
                                <div class="progress progress-minibar">
                                    <div class="progress-bar" style="width: <?php echo $attendence_data['half_day']; ?>"></div>
                                </div>
                            </div>
                        </div><!--./topprograssstart-->
                    </div><!--./col-md-3-->
                    <?php
}
}

$currency_symbol = $this->customlib->getSchoolCurrencyFormat();

$div_col    = 12;
$div_rol    = 12;
$bar_chart  = true;
$line_chart = true;
if ($this->rbac->hasPrivilege('staff_role_count_widget', 'can_view')) {
    $div_col = 9;
    $div_rol = 12;
}

$widget_col = array();
if ($this->rbac->hasPrivilege('Monthly fees_collection_widget', 'can_view')) {
    $widget_col[0] = 1;
    $div_rol       = 3;
}

if ($this->rbac->hasPrivilege('monthly_expense_widget', 'can_view')) {
    $widget_col[1] = 2;
    $div_rol       = 3;
}

if ($this->rbac->hasPrivilege('student_count_widget', 'can_view')) {
    $widget_col[2] = 3;
    $div_rol       = 3;
}
$div = sizeof($widget_col);
if (!empty($widget_col)) {
    $widget = 12 / $div;
} else {

    $widget = 12;
}
?>
</div>
        </div><!--./row-->
</div>
<div id="newEventModal" class="modal fade " role="dialog">
    <div class="modal-dialog modal-dialog2 modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php echo $this->lang->line("add_new_event"); ?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <form role="form" id="addevent_form" method="post" enctype="multipart/form-data" action="">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('event_title'); ?></label><small class="req"> *</small>
                                <input class="form-control" name="title" id="input-field">
                                <span class="text-danger"><?php echo form_error('title'); ?></span>
                            </div>    
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('description'); ?></label>
                                <textarea name="description" class="form-control" id="desc-field"></textarea>
                            </div>    
                        </div>
                    <div class="col-md-12 col-lg-12 col-sm-12">        
                         <div class="row">
                            <div class="col-md-6 col-lg-6 col-sm-6">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('event_from'); ?><small class="req"> *</small></label>
                                    <div class="input-group">
                                        <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                        <input type="text" autocomplete="off" name="event_from" class="form-control pull-right event_from">
                                    </div>
                                </div>    
                            </div>
                            <div class="col-md-6 col-lg-6 col-sm-6">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('event_to'); ?><small class="req"> *</small></label>
                                    <div class="input-group">
                                        <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                                        <input type="text" autocomplete="off" name="event_to" class="form-control pull-right event_to">
                                    </div>
                                </div>    
                            </div>
                        </div>
                    </div>    
                        <div class="col-md-12">
                            <div class="form-group">
                                <label><?php echo $this->lang->line('event_color'); ?></label>
                                <input type="hidden" name="eventcolor" autocomplete="off" id="eventcolor" class="form-control">
                            </div>    
                        </div>
                        <div class="col-md-12">
                           <div class="form-group"> 
                            <?php
$i      = 0;
$colors = '';
foreach ($event_colors as $color) {
    $color_selected_class = 'cpicker-small';
    if ($i == 0) {
        $color_selected_class = 'cpicker-big';
    }
    $colors .= "<div class='calendar-cpicker cpicker " . $color_selected_class . "' data-color='" . $color . "' style='background:" . $color . ";border:1px solid " . $color . "; border-radius:100px'></div>";
    $i++;
}
echo '<div class="cpicker-wrapper">';
echo $colors;
echo '</div>';
?>
                           </div> 
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="pt15 displayblock overflow-hidden w-100"><?php echo $this->lang->line('event_type'); ?></label>
                                <label class="radio-inline w-xs-45">
                                    <input type="radio" name="event_type" value="public" id="public"><?php echo $this->lang->line('public'); ?>
                                </label>
                                <label class="radio-inline w-xs-45">
                                    <input type="radio" name="event_type" value="private" checked id="private"><?php echo $this->lang->line('private'); ?>
                                </label>
                                <label class="radio-inline w-xs-45 ml-xs-0">
                                    <input type="radio" name="event_type" value="sameforall" id="public"><?php echo $this->lang->line('all'); ?> <?php echo json_decode($role)->name; ?>
                                </label>
                                <label class="radio-inline w-xs-45">
                                    <input type="radio" name="event_type" value="protected" id="public"><?php echo $this->lang->line('protected'); ?>
                                </label>
                            </div>    
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <input type="submit" class="btn btn-primary submit_addevent pull-right" value="<?php echo $this->lang->line('save'); ?>"></div> 
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="viewEventModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-dialog2 modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php echo $this->lang->line('edit_event'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <form role="form" method="post" id="updateevent_form" enctype="multipart/form-data" action="">
                        <div class="form-group col-md-12">
                            <label for="exampleInputEmail1"><?php echo $this->lang->line('event_title') ?></label>
                            <input class="form-control" name="title" placeholder="" id="event_title">
                        </div>
                        <div class="form-group col-md-12">
                            <label for="exampleInputEmail1"><?php echo $this->lang->line('description') ?></label>
                            <textarea name="description" class="form-control" placeholder="" id="event_desc"></textarea></div>
                      <div class="row">
                        <div class="form-group col-md-6">
                            <label for="exampleInputEmail1"><?php echo $this->lang->line('event_from'); ?></label>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" autocomplete="off" name="event_from" class="form-control pull-right event_from">
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="exampleInputEmail1"><?php echo $this->lang->line('event_to'); ?></label>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" autocomplete="off" name="event_to" class="form-control pull-right event_to">
                            </div>
                        </div>
                            </div>
                        <input type="hidden" name="eventid" id="eventid">
                        <div class="form-group col-md-12">
                            <label for="exampleInputEmail1"><?php echo $this->lang->line('event_color') ?></label>
                            <input type="hidden" name="eventcolor" autocomplete="off" placeholder="Event Color" id="event_color" class="form-control">
                        </div>
                        <div class="form-group col-md-12">
                            <?php
$i      = 0;
$colors = '';
foreach ($event_colors as $color) {
    $colorid              = trim($color, "#");
    $color_selected_class = 'cpicker-small';
    if ($i == 0) {
        $color_selected_class = 'cpicker-big';
    }
    $colors .= "<div id=" . $colorid . " class='calendar-cpicker cpicker " . $color_selected_class . "' data-color='" . $color . "' style='background:" . $color . ";border:1px solid " . $color . "; border-radius:100px'></div>";
    $i++;
}
echo '<div class="cpicker-wrapper selectevent">';
echo $colors;
echo '</div>';
?>
                        </div>
                        <div class="form-group col-md-12">
                            <label for="exampleInputEmail1"><?php echo $this->lang->line('event_type') ?></label>
                            <label class="radio-inline">
                                <input type="radio" name="eventtype" value="public" id="public"><?php echo $this->lang->line('public') ?>
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="eventtype" value="private" id="private"><?php echo $this->lang->line('private') ?>
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="eventtype" value="sameforall" id="public"><?php echo $this->lang->line('all') ?> <?php echo json_decode($role)->name; ?>
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="eventtype" value="protected" id="public"><?php echo $this->lang->line('protected') ?>
                            </label>
                        </div>
                        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-11">
                            <input type="submit" class="btn btn-primary submit_update pull-right" value="<?php echo $this->lang->line('save'); ?>">
                        </div>
                        <div class="col-xs-1 col-sm-1 col-md-1 col-lg-1">
<?php if ($this->rbac->hasPrivilege('calendar_to_do_list', 'can_delete')) {?>
                                <input type="button" id="delete_event" class="btn btn-primary submit_delete pull-right" value="<?php echo $this->lang->line('delete'); ?>">
<?php }?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () { 
    $('#viewEventModal,#newEventModal').modal({
        backdrop: 'static',
        keyboard: false,
        show: false
    });
});
</script> 

<style>
    canvas {
        -moz-user-select: none;
        -webkit-user-select: none;
        -ms-user-select: none;
    }
</style>
<!-- SHULETECH UI: dashboard charts (Chart.js 4, shadcn chart style). The footer still loads the old
     Chart.js 1 globally for other pages, so this build is kept under window.StChart. -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>window.StChart = window.Chart;</script>
<script type="text/javascript">
(function () {
    var StChart = window.StChart;
    if (!StChart) { return; }
    var currency = <?php echo json_encode(html_entity_decode((string) $currency_symbol, ENT_QUOTES, 'UTF-8')); ?>;
    var L = {
        collection: <?php echo json_encode($this->lang->line('fees_collection')); ?>,
        expense: <?php echo json_encode($this->lang->line('expenses')); ?>
    };
    var data = {
        incomeLabels: <?php echo json_encode(array_column((array) ($incomegraph ?? []), 'income_category')); ?>,
        incomeTotals: <?php echo json_encode(array_map('floatval', array_column((array) ($incomegraph ?? []), 'total'))); ?>,
        expenseLabels: <?php echo json_encode(array_column((array) ($expensegraph ?? []), 'exp_category')); ?>,
        expenseTotals: <?php echo json_encode(array_map('floatval', array_column((array) ($expensegraph ?? []), 'total'))); ?>,
        months: <?php echo json_encode(array_values((array) ($total_month ?? []))); ?>,
        yearCollection: <?php echo json_encode(array_values((array) ($yearly_collection ?? []))); ?>,
        yearExpense: <?php echo json_encode(array_values((array) ($yearly_expense ?? []))); ?>,
        days: <?php echo json_encode(array_values((array) ($current_month_days ?? []))); ?>,
        dayCollection: <?php echo json_encode(array_values((array) ($days_collection ?? []))); ?>,
        dayExpense: <?php echo json_encode(array_values((array) ($days_expense ?? []))); ?>
    };
    var show = {
        income: <?php echo json_encode((bool) ($this->module_lib->hasActive('income'))); ?>,
        expense: <?php echo json_encode((bool) ($this->module_lib->hasActive('expense'))); ?>,
        incomeDonut: <?php echo json_encode((bool) ($this->rbac->hasPrivilege('income_donut_graph', 'can_view') && $this->module_lib->hasActive('income'))); ?>,
        expenseDonut: <?php echo json_encode((bool) ($this->rbac->hasPrivilege('expense_donut_graph', 'can_view') && $this->module_lib->hasActive('expense'))); ?>,
        yearly: <?php echo json_encode((bool) $this->rbac->hasPrivilege('fees_collection_and_expense_yearly_chart', 'can_view')); ?>,
        monthly: <?php echo json_encode((bool) $this->rbac->hasPrivilege('fees_collection_and_expense_monthly_chart', 'can_view')); ?>
    };

    function cssVar(name) { return getComputedStyle(document.documentElement).getPropertyValue(name).trim(); }
    function isDark() { return document.documentElement.classList.contains('st-dark'); }
    function palette() {
        return isDark()
            ? { collection: '#34d399', expense: '#fb7185', series: ['#60a5fa', '#34d399', '#fbbf24', '#a78bfa', '#fb7185', '#22d3ee', '#f472b6'] }
            : { collection: '#059669', expense: '#e11d48', series: ['#2563eb', '#059669', '#d97706', '#7c3aed', '#e11d48', '#0891b2', '#db2777'] };
    }
    function alpha(hex, a) {
        var n = parseInt(hex.slice(1), 16);
        return 'rgba(' + (n >> 16 & 255) + ',' + (n >> 8 & 255) + ',' + (n & 255) + ',' + a + ')';
    }
    function money(v) {
        var n = Number(v) || 0;
        return currency + n.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }
    function num(arr) { return (arr || []).map(function (v) { return Number(v) || 0; }); }

    function applyDefaults() {
        var d = StChart.defaults;
        d.font.family = "'Bricolage Grotesque', 'Segoe UI', system-ui, sans-serif";
        d.font.size = 12;
        d.color = cssVar('--muted-foreground') || '#737373';
        d.borderColor = cssVar('--border') || '#e5e5e5';
        d.plugins.legend.labels.usePointStyle = true;
        d.plugins.legend.labels.pointStyle = 'circle';
        d.plugins.legend.labels.boxWidth = 8;
        d.plugins.legend.labels.boxHeight = 8;
        d.plugins.legend.labels.padding = 16;
        var t = d.plugins.tooltip;
        t.backgroundColor = cssVar('--popover') || '#fff';
        t.titleColor = cssVar('--foreground') || '#0a0a0a';
        t.bodyColor = cssVar('--muted-foreground') || '#737373';
        t.borderColor = cssVar('--border') || '#e5e5e5';
        t.borderWidth = 1;
        t.cornerRadius = 8;
        t.padding = 10;
        t.boxPadding = 4;
        t.usePointStyle = true;
        t.titleFont = { weight: '600' };
        t.callbacks = t.callbacks || {};
    }

    function axes(stacked) {
        return {
            x: { stacked: !!stacked, grid: { display: false }, border: { display: false }, ticks: { maxRotation: 0, autoSkipPadding: 12 } },
            y: { beginAtZero: true, suggestedMax: 100, border: { display: false, dash: [4, 4] },
                 grid: { color: cssVar('--border'), drawTicks: false },
                 ticks: { padding: 8, maxTicksLimit: 5, callback: function (v) { return money(v); } } }
        };
    }
    var moneyTooltip = { callbacks: { label: function (c) { return ' ' + c.dataset.label + ': ' + money(c.parsed.y !== undefined ? c.parsed.y : c.parsed); } } };

    // Doughnut centre total
    var centerText = {
        id: 'stCenterText',
        afterDraw: function (chart, args, opts) {
            if (chart.config.type !== 'doughnut') { return; }
            var meta = chart.getDatasetMeta(0);
            if (!meta || !meta.data.length) { return; }
            var arc = meta.data[0], ctx = chart.ctx;
            var total = (opts && opts.empty) ? 0 : chart.data.datasets[0].data.reduce(function (a, b) { return a + (Number(b) || 0); }, 0);
            ctx.save();
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillStyle = cssVar('--foreground');
            ctx.font = "700 22px 'Bricolage Grotesque', system-ui, sans-serif";
            ctx.fillText(money(total), arc.x, arc.y - 8);
            ctx.fillStyle = cssVar('--muted-foreground');
            ctx.font = "500 12px 'Bricolage Grotesque', system-ui, sans-serif";
            ctx.fillText((opts && opts.empty) ? 'No data yet' : (opts && opts.label ? opts.label : 'Total'), arc.x, arc.y + 14);
            ctx.restore();
        }
    };
    StChart.register(centerText);

    var charts = [];
    function build() {
        charts.forEach(function (c) { c.destroy(); });
        charts = [];
        applyDefaults();
        var p = palette();

        // Monthly (days of this month): grouped rounded bars
        var bar = document.getElementById('barChart');
        if (bar && show.monthly) {
            var ds = [];
            if (show.income) { ds.push({ label: L.collection, data: num(data.dayCollection), backgroundColor: p.collection, hoverBackgroundColor: p.collection }); }
            if (show.expense) { ds.push({ label: L.expense, data: num(data.dayExpense), backgroundColor: p.expense, hoverBackgroundColor: p.expense }); }
            ds.forEach(function (d) { d.borderRadius = 4; d.borderSkipped = false; d.maxBarThickness = 14; d.categoryPercentage = .7; d.barPercentage = .85; });
            charts.push(new StChart(bar, {
                type: 'bar', data: { labels: data.days, datasets: ds },
                options: { responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false },
                    scales: axes(false),
                    plugins: { legend: { position: 'top', align: 'end' }, tooltip: moneyTooltip } }
            }));
        }

        // Session (by month): smooth area lines with gradient fill
        var line = document.getElementById('lineChart');
        if (line && show.yearly) {
            var ctx = line.getContext('2d');
            var grad = function (hex) {
                return function (context) {
                    var area = context.chart.chartArea;
                    if (!area) { return alpha(hex, .15); }
                    var g = ctx.createLinearGradient(0, area.top, 0, area.bottom);
                    g.addColorStop(0, alpha(hex, .28)); g.addColorStop(1, alpha(hex, 0));
                    return g;
                };
            };
            var lds = [];
            if (show.income) { lds.push({ label: L.collection, data: num(data.yearCollection), borderColor: p.collection, backgroundColor: grad(p.collection) }); }
            if (show.expense) { lds.push({ label: L.expense, data: num(data.yearExpense), borderColor: p.expense, backgroundColor: grad(p.expense) }); }
            lds.forEach(function (d) { d.fill = true; d.tension = .4; d.borderWidth = 2; d.pointRadius = 0; d.pointHoverRadius = 4; d.pointHoverBorderWidth = 2; d.pointHoverBackgroundColor = cssVar('--card'); d.pointHoverBorderColor = d.borderColor; });
            charts.push(new StChart(line, {
                type: 'line', data: { labels: data.months, datasets: lds },
                options: { responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false },
                    scales: axes(false),
                    plugins: { legend: { position: 'top', align: 'end' }, tooltip: moneyTooltip } }
            }));
        }

        // Income / expense by category: doughnuts with the total in the middle
        function donut(id, labels, values, label) {
            var el = document.getElementById(id);
            if (!el) { return; }
            var empty = !values.length || values.every(function (v) { return !v; });
            charts.push(new StChart(el, {
                type: 'doughnut',
                data: { labels: empty ? ['—'] : labels,
                        datasets: [{ data: empty ? [1] : values,
                                     backgroundColor: empty ? [cssVar('--muted')] : labels.map(function (_, i) { return p.series[i % p.series.length]; }),
                                     borderColor: cssVar('--card'), borderWidth: 3, hoverOffset: 4, borderRadius: 4 }] },
                options: { responsive: true, maintainAspectRatio: false, cutout: '72%',
                    plugins: { legend: { display: !empty, position: 'bottom' },
                               tooltip: { enabled: !empty, callbacks: { label: function (c) { return ' ' + c.label + ': ' + money(c.parsed); } } },
                               stCenterText: { label: label, empty: empty } } }
            }));
        }
        if (show.incomeDonut) { donut('doughnut-chart', data.incomeLabels, num(data.incomeTotals), <?php echo json_encode($this->lang->line('income')); ?>); }
        if (show.expenseDonut) { donut('doughnut-chart1', data.expenseLabels, num(data.expenseTotals), <?php echo json_encode($this->lang->line('expense')); ?>); }
    }

    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', build); } else { build(); }
    document.addEventListener('st-themechange', function () { setTimeout(build, 30); });
})();
</script>
<script type="text/javascript">
    $(document).ready(function () {
        $(document).on('click', '.close_notice', function () {
        var data = $(this).data();
        $.ajax({
        type: "POST",
                url: base_url + "admin/notification/read",
                data: {'notice': data.noticeid},
                dataType: "json",
                success: function (data) {
                if (data.status == "fail") {

                errorMsg(data.msg);
                } else {
                successMsg(data.msg);
                }

                }
        });
        });
    });
</script>