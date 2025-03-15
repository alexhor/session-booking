<?= $this->extend(config('Auth')->views['layout']) ?>

<?= $this->section('title') ?><?= lang('Views.login_link_send_via_email') ?> <?= $this->endSection() ?>

<?= $this->section('main') ?>

<div class="container d-flex justify-content-center p-5">
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="card-title mb-5"><?= lang('Views.login_link_send_via_email') ?></h5>

            <p style="white-space: pre-wrap;"><?= lang('Views.email_send_only_shortly_valid', ['minutes' => setting('Auth.magicLinkLifetime') / 60]) ?></p>
            
            <p><b><?= lang('Views.check_you_spam_folder') ?></b></p>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
