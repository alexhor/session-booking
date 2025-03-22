<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($configs['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="<?= site_url('style.css'); ?>" rel="stylesheet">
  </head>
<body>

<?php if ($_ENV['CI_ENVIRONMENT'] === 'development'): ?>
    <script type="module" src="<?= base_url('vite/%40vite/client'); ?>"></script>
    <script type="module" src="<?= base_url('vite/src/main.js'); ?>"></script>
<?php else: ?>
    <script type="module" src="<?= base_url('build/assets/index.js'); ?>"></script>
<?php endif; ?>

<div id="app">
    
</div>
</body>
</html>
