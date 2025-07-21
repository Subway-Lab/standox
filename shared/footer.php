<?php
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$projectDir = dirname(dirname($_SERVER['SCRIPT_NAME']));
$baseUrl = $protocol . $host . $projectDir . '/';
?>

<footer>
    <div class="all_footer_block">
        <div class="footer_column_1">
            <img class="bottom_logo" src="<?= $baseUrl ?>../files/black_logo.svg" alt="STANDOX logo">
            <div class="contacts">
                <p>
                    СТО "STANDOX" <br>
                    672039, г. Чита, ул. Верхоленская 51 <br>
                    телефон: 8 914 472-10-10, 8 924 472-30-30 <br>
                    email: lider00@list.ru <br>
                    web-site: www.standox.chita.ru
                </p>
            </div>
        </div>

        <div class="property">
            <p>
                Данное программное обеспечение является интеллектуальной собственностью <br>
                Индивидуального предпринимателя Фарафонова Владимира Владимировича <br>
                ОГРНИП: 306753636100113, ИНН: 753610458920 <br>
                Все права защищены
            </p>
        </div>

        <div class="sbwlab">
            <p>
                <a href="https://subway-lab.com" target="_blank" class="link_sbwlab"> © 2025 SUBWAY LAB COMPANY </a></br>
                <span class="other_font_size"> программа разработана в рамках проекта «STANDOX» </span>
            </p>
        </div>
    </div>
</footer>