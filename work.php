<?php
// Unused CSS-Finder 2024
function getHtmlContent($url) {
    $context = stream_context_create([
        "http" => [
            "header" => "User-Agent: Mozilla/5.0"
        ]
    ]);
    return file_get_contents($url, false, $context);
}

function getCssLinks($html) {
    $cssLinks = [];
    preg_match_all('/<link[^>]+rel=["\']stylesheet["\'][^>]+href=["\']([^"\']+)["\']/i', $html, $matches);
    foreach ($matches[1] as $link) {
        $cssLinks[] = $link;
    }
    return $cssLinks;
}

function getInternalLinks($html, $baseUrl) {
    $internalLinks = [];
    preg_match_all('/<a[^>]+href=["\']([^"\']+)["\']/i', $html, $matches);
    $parsedUrl = parse_url($baseUrl);
    $baseHost = $parsedUrl['host'];

    foreach ($matches[1] as $link) {
        if (strpos($link, $baseHost) !== false || strpos($link, '/') === 0) {
            $internalLinks[] = filter_var($link, FILTER_VALIDATE_URL) ? $link : rtrim($baseUrl, '/') . '/' . ltrim($link, '/');
        }
    }

    return array_unique($internalLinks);
}

function getCssContent($url, $cssLink) {
    $cssUrl = $cssLink;
    if (!preg_match('/^https?:\/\//', $cssLink)) {
        $cssUrl = rtrim($url, '/') . '/' . ltrim($cssLink, '/');
    }
    return file_get_contents($cssUrl);
}

function extractUsedCss($html, $css) {
    $usedCss = '';
    preg_match_all('/([^{]+)\{[^}]+\}/', $css, $matches);
    foreach ($matches[1] as $selector) {
        if (preg_match('/' . preg_quote(trim($selector)) . '/i', $html)) {
            $usedCss .= $selector . "{" . $matches[2] . "}\n";
        }
    }
    return $usedCss;
}

function saveCssFile($filename, $content) {
    file_put_contents($filename, $content);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $url = $_POST['url'];
    $visitedLinks = [];
    $allCssLinks = [];
    $finalUsedCss = '';

    function analyzePage($url, &$visitedLinks, &$allCssLinks, &$finalUsedCss) {
        if (in_array($url, $visitedLinks)) {
            return;
        }

        $visitedLinks[] = $url;

        $html = getHtmlContent($url);
        $cssLinks = getCssLinks($html);
        $internalLinks = getInternalLinks($html, $url);

        foreach ($cssLinks as $cssLink) {
            if (!in_array($cssLink, $allCssLinks)) {
                $cssContent = getCssContent($url, $cssLink);
                $finalUsedCss .= extractUsedCss($html, $cssContent);
                $allCssLinks[] = $cssLink;
            }
        }

        foreach ($internalLinks as $internalLink) {
            analyzePage($internalLink, $visitedLinks, $allCssLinks, $finalUsedCss);
        }
    }

    analyzePage($url, $visitedLinks, $allCssLinks, $finalUsedCss);

    saveCssFile('cleaned_css.css', $finalUsedCss);
    echo "Bereinigtes CSS wurde gespeichert.";
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Unused CSS Finder</title>
</head>
<body>
    <h1>Finde ungenutztes CSS</h1>
    <form method="post">
        <label for="url">Webseiten-URL:</label>
        <input type="text" id="url" name="url" required>
        <button type="submit">Scan starten</button>
    </form>
</body>
</html>
