<?php

// Извлечение всех URL из cz-products.xml и вывод как готовый PHP-массив

$xmlFile = __DIR__ . '/csv/ua-products.xml';
$urls = [];

if (file_exists($xmlFile)) {
    $xml = simplexml_load_file($xmlFile);
    
    if ($xml && isset($xml->url)) {
        foreach ($xml->url as $url) {
            if (isset($url->loc)) {
                $urls[] = (string)$url->loc;
            }
        }
    }
}

// Вывод как готовый PHP-массив для копирования
echo "<?php\n\n";
echo "\$productsUrls = " . var_export($urls, true) . ";\n\n";
echo "// Всего URL: " . count($urls) . "\n";
echo "?>";
