<?php 
    $version = "1.0.0";
?>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="keywords" content="key words">
    <meta name="description" content="description of the page SEO">
    <meta name="format-detection" content="telephone=no">
    <title> STANDOX </title>

    <!-- NOTE: Card for Open Graph -->
    <meta property="og:title" content="<?= htmlspecialchars($ogTitle ?? 'СТО "STANDOX"') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($ogDescription ?? '672039 г. Чита, ул. Верхоленская 51, телефон: 8 914 472-10-10, 8 924 472-30-30, email: lider00@list.ru, web-site: www.standox.chita.ru') ?>">
    <meta property="og:image" content="https://www.standox.pro/files/card_for_messengers.jpg">
    <meta property="og:url" content="<?= htmlspecialchars($ogUrl ?? 'https://www.standox.pro/') ?>">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="ru_RU">

    <!-- NOTE: Card for Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($ogTitle ?? 'СТО "STANDOX"') ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($ogDescription ?? '672039 г. Чита, ул. Верхоленская 51, телефон: 8 914 472-10-10, 8 924 472-30-30, email: lider00@list.ru, web-site: www.standox.chita.ru') ?>">
    <meta name="twitter:image" content="https://www.standox.pro/files/card_for_messengers.jpg">

    <!-- NOTE: Icon for iOS -->
    <link rel="apple-touch-icon" sizes="512x512" href="https://www.standox.pro/files/apple-touch-icon.png">

    <link rel="icon" href="https://www.standox.pro/files/favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">

    <?php if (!isset($noStyle) || !$noStyle): ?>
        <link rel="stylesheet" href="https://www.standox.pro/public/style.css?v=<?php echo $version; ?>">
    <?php endif; ?>

    <?php if (isset($printCss)): ?>
        <link rel="stylesheet" type="text/css" href="/features/print/print.css?v=<?= htmlspecialchars($printCss) ?>&version=<?php echo $version; ?>">
    <?php endif; ?>

    <?php if (isset($ebitingCss)): ?>
        <link rel="stylesheet" type="text/css" href="/features/editing/editing.css?v=<?= htmlspecialchars($ebitingCss) ?>&version=<?php echo $version; ?>">
    <?php endif; ?>

    <?php if (isset($loginCss)): ?>
        <link rel="stylesheet" type="text/css" href="/features/auth/login.css?v=<?= htmlspecialchars($loginCss) ?>&version=<?php echo $version; ?>">
    <?php endif; ?>

    <?php if (isset($databaseCss)): ?>
        <link rel="stylesheet" type="text/css" href="/features/database/database.css?v=<?= htmlspecialchars($databaseCss) ?>&version=<?php echo $version; ?>">
    <?php endif; ?>
</head>