<?php

declare(strict_types=1);

/**
 * @param array{title?:string,target:string,message:string,linkLabel?:string} $config
 */
function render_redirect_layout(array $config): void
{
    $title = $config['title'] ?? 'Redireccionando…';
    $target = $config['target'];
    $message = $config['message'];
    $linkLabel = $config['linkLabel'] ?? 'Continuar';
    ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
  <script>
    location.replace(<?= json_encode($target, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>);
  </script>
</head>
<body style="font-family: system-ui, sans-serif; padding: 2rem;">
  <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?> <a href="<?= htmlspecialchars($target, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($linkLabel, ENT_QUOTES, 'UTF-8') ?></a></p>
</body>
</html>
<?php
}
