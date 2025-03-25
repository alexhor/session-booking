<?php
$additionalStyles= '';

$eventMarkingList = [
    [
        'startTimestamp' => strtotime("06.04.2025 18:0:0"),
        'endTimestamp' => strtotime("13.04.2025 19:0:0"),
        'color' => '#02cbb8',
        'title' => '24/7 Gebet',
        'description' => ""
    ]
];

foreach($eventMarkingList as $i => &$marking) {
    $marking['cssClasses'] = 'event-marking-' . $i;
    $additionalStyles .= "." . $marking['cssClasses'] . " {
        border-color: " . $marking['color'] . " !important;
    }";
}
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($configs['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  </head>
<body>

<div id="app"></div>

<script>

document.php = {};
document.php.eventMarkingList = <?= json_encode($eventMarkingList); ?>;
document.php.configs = <?= json_encode($configs); ?>;
document.php.pageLoadErrorMessageList = <?= json_encode($messages); ?>;
document.php.lang = {
    monthNames: [
        '<?= lang('Views.months.january'); ?>',
        '<?= lang('Views.months.february'); ?>',
        '<?= lang('Views.months.march'); ?>',
        '<?= lang('Views.months.april'); ?>',
        '<?= lang('Views.months.may'); ?>',
        '<?= lang('Views.months.june'); ?>',
        '<?= lang('Views.months.july'); ?>',
        '<?= lang('Views.months.august'); ?>',
        '<?= lang('Views.months.september'); ?>',
        '<?= lang('Views.months.october'); ?>',
        '<?= lang('Views.months.november'); ?>',
        '<?= lang('Views.months.december'); ?>'
    ],
    dayNames: [
        '<?= lang('Views.weekdays.long.monday'); ?>',
        '<?= lang('Views.weekdays.long.tuesday'); ?>',
        '<?= lang('Views.weekdays.long.wednesday'); ?>',
        '<?= lang('Views.weekdays.long.thursday'); ?>',
        '<?= lang('Views.weekdays.long.friday'); ?>',
        '<?= lang('Views.weekdays.long.saturday'); ?>',
        '<?= lang('Views.weekdays.long.sunday'); ?>',
    ],

    'Views.toggle_navigation': '<?= lang('Views.toggle_navigation'); ?>',
    'Views.logout': '<?= lang('Views.logout'); ?>',
    'Views.register': '<?= lang('Views.register'); ?>',
    'Views.login': '<?= lang('Views.login'); ?>',
    'Views.register_new_account': '<?= lang('Views.register_new_account'); ?>',
    'Views.close': '<?= lang('Views.close'); ?>',
    'Views.already_have_an_account_then_login': '<?= lang('Views.already_have_an_account_then_login'); ?>',
    'Views.register': '<?= lang('Views.register'); ?>',
    'Views.no_account_yet_then_register': '<?= lang('Views.no_account_yet_then_register'); ?>',
    'Views.request_login_link': '<?= lang('Views.request_login_link'); ?>',

    'Validation.user.id.label': '<?= lang('Validation.user.id.label'); ?>',
    'Validation.user.firstname.label': '<?= lang('Validation.user.firstname.label'); ?>',
    'Validation.user.lastname.label': '<?= lang('Validation.user.lastname.label'); ?>',
    'Validation.user.email.label': '<?= lang('Validation.user.email.label'); ?>',

    'Admin.admin': '<?= lang('Admin.admin'); ?>',
    'Admin.sessions': '<?= lang('Admin.sessions'); ?>',
    'Admin.settings': '<?= lang('Admin.settings'); ?>',
    'Admin.setting': '<?= lang('Admin.setting'); ?>',
    'Admin.value': '<?= lang('Admin.value'); ?>',
    'Admin.save': '<?= lang('Admin.save'); ?>',
    'Admin.discard': '<?= lang('Admin.discard'); ?>',
    'Admin.reset': '<?= lang('Admin.reset'); ?>',
    'Admin.users': '<?= lang('Admin.users'); ?>',
    'Admin.delete': '<?= lang('Admin.delete'); ?>',
    'Admin.really_clear_setting': '<?= lang('Admin.really_clear_setting'); ?>',
    'Admin.really_delete_user': '<?= lang('Admin.really_delete_user'); ?>',
    'Admin.really_remove_self_from_admin': '<?= lang('Admin.really_remove_self_from_admin'); ?>',

    'Admin.session_details.details': '<?= lang('Admin.session_details.details'); ?>',
    'Admin.session_details.user': '<?= lang('Admin.session_details.user'); ?>',
    'Admin.session_details.time': '<?= lang('Admin.session_details.time'); ?>',
    'Admin.session_details.title': '<?= lang('Admin.session_details.title'); ?>',
    'Admin.session_details.title_is_public': '<?= lang('Admin.session_details.title_is_public'); ?>',
    'Admin.session_details.description': '<?= lang('Admin.session_details.description'); ?>',
    'Admin.session_details.description_is_public': '<?= lang('Admin.session_details.description_is_public'); ?>',
    'Admin.session_details.delete': '<?= lang('Admin.session_details.delete'); ?>',
    'Admin.session_details.add': '<?= lang('Admin.session_details.add'); ?>',
};
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@3/dist/vue.global.js"></script>

<?php if ($_ENV['CI_ENVIRONMENT'] === 'development'): ?>
    <script type="module" src="<?= base_url('vite/@vite/client'); ?>"></script>
    <script type="module" src="<?= base_url('vite/src/main.js'); ?>"></script>
<?php else: ?>
    <script type="module" src="<?= base_url('build/assets/index.js'); ?>"></script>
<?php endif; ?>

</body>
</html>
