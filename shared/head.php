<?php
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$projectDir = dirname(dirname($_SERVER['SCRIPT_NAME']));
$baseUrl = $protocol . $host . $projectDir . '/';
?>

<head>
    <meta charset="utf-8">
    <meta name="keywords" content="key words">
    <meta name="description" content="description of the page SEO">
    <meta name="format-detection" content="telephone=no">
    <title> STANDOX </title>

    <!-- NOTE: Card for Open Graph -->
    <meta property="og:title" content="<?= htmlspecialchars($ogTitle ?? 'СТО "STANDOX"') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($ogDescription ?? '672039 г. Чита, ул. Верхоленская 51, телефон: 8 914 472-10-10, 8 924 472-30-30, email: lider00@list.ru, web-site: www.standox.chita.ru') ?>">
    <meta property="og:image" content="https://www.standox.pro/files/Card_for_messengers.jpg?v=<?=time()?>"><meta property="og:url" content="<?= htmlspecialchars($ogUrl ?? 'https://www.standox.pro/') ?>">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="ru_RU">

    <!-- NOTE: Card for Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($ogTitle ?? 'СТО "STANDOX"') ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($ogDescription ?? '672039 г. Чита, ул. Верхоленская 51, телефон: 8 914 472-10-10, 8 924 472-30-30, email: lider00@list.ru, web-site: www.standox.chita.ru') ?>">
    <meta name="twitter:image" content="https://www.standox.pro/files/card_for_messengers.jpg">

    <!-- NOTE: Icon for iOS -->
    <link rel="apple-touch-icon" sizes="512x512" href="https://www.standox.pro/files/apple-touch-icon.png">

    <link rel="icon" href="<?= $baseUrl ?>../files/favicon.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.5/gsap.min.js"></script>

    <link rel="stylesheet" href="<?= $baseUrl ?>../public/style.css?v=<?= time() ?>">
    
    <?php if (isset($ebitingCss)): ?>
        <link rel="stylesheet" type="text/css" href="<?= htmlspecialchars_decode($ebitingCss) ?>">
    <?php endif; ?>

    <?php if (isset($loginCss)): ?>
        <link rel="stylesheet" type="text/css" href="<?= htmlspecialchars($loginCss) ?>">
    <?php endif; ?>

    <?php if (isset($adminOrderCss)): ?>
        <link rel="stylesheet" type="text/css" href="<?= htmlspecialchars($adminOrderCss) ?>">
    <?php endif; ?>

    <?php if (isset($printOrderCss)): ?>
        <link rel="stylesheet" type="text/css" href="<?= htmlspecialchars($printOrderCss) ?>">
    <?php endif; ?> 
</head>