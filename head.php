<head>
    <meta charset="utf-8">
    <meta name="keywords" content="key words">
    <meta name="description" content="description of the page SEO">
    <meta name="format-detection" content="telephone=no">
    <title> STANDOX </title>

    <!-- NOTE: Карточка для месенджеров -->
    <meta property="og:title" content="<?= htmlspecialchars($ogTitle ?? 'STANDOX') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($ogDescription ?? 'СТО "STANDOX" 672039 г. Чита, ул. Верхоленская 51, телефон: 8 914 472-10-10, 8 924 472-30-30, email: lider00@list.ru, web-site: www.standox.chita.ru') ?>">
    <meta property="og:image" content="<?= htmlspecialchars($ogImage ?? 'files/Card_for_messenger.jpg') ?>">
    <meta property="og:url" content="<?= htmlspecialchars($ogUrl ?? 'https://www.standox.pro/') ?>">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="ru_RU">

    <link rel="apple-touch-icon" href="files/apple-touch-icon.png">

    <link rel="icon" href="files/favicon.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.5/gsap.min.js"></script>

    <?php if (!isset($noStyle) || $noStyle !== true): ?> 
        <link rel="stylesheet" type="text/css" href="style.css">
    <?php endif; ?>

    <?php if (isset($ebitOrderCss)): ?>
        <link rel="stylesheet" type="text/css" href="<?= htmlspecialchars($ebitOrderCss) ?>">
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