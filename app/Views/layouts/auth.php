<?php

declare(strict_types=1);

/**
 * @param array{title?:string,bodyClass?:string,content:string,scripts?:list<string>} $config
 */
function render_auth_layout(array $config): void
{
    $title = $config['title'] ?? 'Acceso | Foros PSME';
    $bodyClass = $config['bodyClass'] ?? 'min-h-full bg-slate-50 text-slate-900';
    $content = $config['content'] ?? '';
    $scripts = $config['scripts'] ?? [];
    ?>
<!doctype html>
<html lang="es" class="h-full">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; }
  </style>
</head>
<body class="<?= htmlspecialchars($bodyClass, ENT_QUOTES, 'UTF-8') ?>">
<?= $content ?>
<?php foreach ($scripts as $script): ?>
  <script src="<?= htmlspecialchars($script, ENT_QUOTES, 'UTF-8') ?>"></script>
<?php endforeach; ?>
</body>
</html>
<?php
}
