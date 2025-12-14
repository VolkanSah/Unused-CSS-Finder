<?php
// Unused CSS-Finder 2024 - Fixed Version
set_time_limit(300); // Gibt dir 5 Min für große Sites

function getHtmlContent($url) {
    $context = stream_context_create([
        "http" => [
            "header" => "User-Agent: Mozilla/5.0",
            "timeout" => 10
        ]
    ]);
    $content = @file_get_contents($url, false, $context);
    return $content !== false ? $content : '';
}

function getCssLinks($html, $baseUrl) {
    $cssLinks = [];
    preg_match_all('/<link[^>]+rel=["\']stylesheet["\'][^>]*href=["\']([^"\']+)["\'][^>]*>/i', $html, $matches);
    
    foreach ($matches[1] as $link) {
        if (!preg_match('/^https?:\/\//', $link)) {
            $parsedBase = parse_url($baseUrl);
            if (strpos($link, '/') === 0) {
                $link = $parsedBase['scheme'] . '://' . $parsedBase['host'] . $link;
            } else {
                $link = rtrim($baseUrl, '/') . '/' . ltrim($link, '/');
            }
        }
        $cssLinks[] = $link;
    }
    return array_unique($cssLinks);
}

function getInternalLinks($html, $baseUrl) {
    $internalLinks = [];
    preg_match_all('/<a[^>]+href=["\']([^"\'#]+)["\'][^>]*>/i', $html, $matches);
    
    $parsedUrl = parse_url($baseUrl);
    $baseHost = $parsedUrl['host'];
    
    foreach ($matches[1] as $link) {
        $fullLink = $link;
        
        if (!preg_match('/^https?:\/\//', $link)) {
            if (strpos($link, '/') === 0) {
                $fullLink = $parsedUrl['scheme'] . '://' . $baseHost . $link;
            } else {
                $fullLink = rtrim($baseUrl, '/') . '/' . ltrim($link, '/');
            }
        }
        
        $parsedLink = parse_url($fullLink);
        if (isset($parsedLink['host']) && $parsedLink['host'] === $baseHost) {
            $internalLinks[] = $fullLink;
        }
    }
    
    return array_unique($internalLinks);
}

function getCssContent($cssUrl) {
    return @file_get_contents($cssUrl) ?: '';
}

function extractSelectors($css) {
    // Entfernt Kommentare
    $css = preg_replace('/\/\*.*?\*\//s', '', $css);
    
    $selectors = [];
    preg_match_all('/([^{]+)\{([^}]+)\}/s', $css, $matches, PREG_SET_ORDER);
    
    foreach ($matches as $match) {
        $selectorGroup = trim($match[1]);
        $rules = trim($match[2]);
        
        // Split multiple selectors (comma-separated)
        $individualSelectors = array_map('trim', explode(',', $selectorGroup));
        
        foreach ($individualSelectors as $sel) {
            if (!empty($sel)) {
                $selectors[$sel] = $rules;
            }
        }
    }
    
    return $selectors;
}

function isUsedSelector($selector, $html) {
    // Pseudo-Klassen/Elemente für Matching entfernen
    $cleanSel = preg_replace('/:(hover|focus|active|visited|before|after|first-child|last-child|nth-child\([^)]+\))/', '', $selector);
    
    // @ rules (media queries etc) immer behalten
    if (strpos($selector, '@') === 0) {
        return true;
    }
    
    // Universal selector
    if (trim($cleanSel) === '*') {
        return true;
    }
    
    // Einfache Klassen/IDs/Tags extrahieren
    preg_match_all('/[.#]?[\w-]+/', $cleanSel, $matches);
    
    foreach ($matches[0] as $part) {
        $part = trim($part);
        if (empty($part)) continue;
        
        if ($part[0] === '.') {
            $class = substr($part, 1);
            if (preg_match('/class=["\'][^"\']*\b' . preg_quote($class, '/') . '\b[^"\']*["\']/', $html)) {
                return true;
            }
        } elseif ($part[0] === '#') {
            $id = substr($part, 1);
            if (preg_match('/id=["\']' . preg_quote($id, '/') . '["\']/', $html)) {
                return true;
            }
        } else {
            // Tag names
            if (preg_match('/<' . preg_quote($part, '/') . '[\s>\/]/i', $html)) {
                return true;
            }
        }
    }
    
    return false;
}

function buildCleanedCss($allSelectors, $combinedHtml) {
    $cleaned = '';
    
    foreach ($allSelectors as $selector => $rules) {
        if (isUsedSelector($selector, $combinedHtml)) {
            $cleaned .= $selector . " {\n  " . str_replace(';', ";\n  ", trim($rules)) . "\n}\n\n";
        }
    }
    
    return $cleaned;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $startUrl = trim($_POST['url']);
    $maxPages = (int)($_POST['max_pages'] ?? 10);
    
    $visitedLinks = [];
    $allCssUrls = [];
    $allSelectors = [];
    $combinedHtml = '';
    
    function analyzePage($url, &$visitedLinks, &$allCssUrls, &$allSelectors, &$combinedHtml, $maxPages) {
        if (count($visitedLinks) >= $maxPages || in_array($url, $visitedLinks)) {
            return;
        }
        
        echo "Scanne: $url<br>";
        flush();
        
        $visitedLinks[] = $url;
        $html = getHtmlContent($url);
        
        if (empty($html)) {
            echo " Konnte $url nicht laden<br>";
            return;
        }
        
        $combinedHtml .= $html . "\n";
        
        $cssLinks = getCssLinks($html, $url);
        foreach ($cssLinks as $cssUrl) {
            if (!in_array($cssUrl, $allCssUrls)) {
                echo "CSS gefunden: $cssUrl<br>";
                $cssContent = getCssContent($cssUrl);
                if (!empty($cssContent)) {
                    $selectors = extractSelectors($cssContent);
                    $allSelectors = array_merge($allSelectors, $selectors);
                    $allCssUrls[] = $cssUrl;
                }
            }
        }
        
        $internalLinks = getInternalLinks($html, $url);
        foreach (array_slice($internalLinks, 0, 5) as $link) {
            analyzePage($link, $visitedLinks, $allCssUrls, $allSelectors, $combinedHtml, $maxPages);
        }
    }
    
    echo "<h2>Scan läuft...</h2>";
    analyzePage($startUrl, $visitedLinks, $allCssUrls, $allSelectors, $combinedHtml, $maxPages);
    
    echo "<h2>Bereinige CSS...</h2>";
    $cleanedCss = buildCleanedCss($allSelectors, $combinedHtml);
    
    $filename = 'cleaned_css_' . date('Y-m-d_His') . '.css';
    file_put_contents($filename, $cleanedCss);
    
    $originalCount = count($allSelectors);
    preg_match_all('/[^{]+\{/', $cleanedCss, $usedMatches);
    $usedCount = count($usedMatches[0]);
    $savedPercent = $originalCount > 0 ? round((1 - $usedCount/$originalCount) * 100, 1) : 0;
    
    echo "<h2> Fertig!</h2>";
    echo "<p><strong>Seiten gescannt:</strong> " . count($visitedLinks) . "</p>";
    echo "<p><strong>Originale Selektoren:</strong> $originalCount</p>";
    echo "<p><strong>Verwendete Selektoren:</strong> $usedCount</p>";
    echo "<p><strong>Eingespart:</strong> $savedPercent%</p>";
    echo "<p><a href='$filename' download>📥 Download: $filename</a></p>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unused CSS Finder Pro</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 500px;
            width: 100%;
        }
        h1 { 
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 600;
        }
        input, select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            margin-bottom: 20px;
            transition: border 0.3s;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #667eea;
        }
        button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        button:hover {
            transform: translateY(-2px);
        }
        .info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 13px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 CSS Cleaner Pro</h1>
        <p class="subtitle">Entfernt ungenutztes CSS von deiner WordPress-Site</p>
        
        <form method="post">
            <label for="url">Website URL:</label>
            <input 
                type="url" 
                id="url" 
                name="url" 
                placeholder="https://deine-website.de"
                required
            >
            
            <label for="max_pages">Max. Seiten scannen:</label>
            <select id="max_pages" name="max_pages">
                <option value="5">5 Seiten (schnell)</option>
                <option value="10" selected>10 Seiten (empfohlen)</option>
                <option value="20">20 Seiten (gründlich)</option>
                <option value="50">50 Seiten (sehr gründlich)</option>
            </select>
            
            <button type="submit">🚀 Scan starten</button>
        </form>
        
        <div class="info">
            <strong> Tipp:</strong> Je mehr Seiten gescannt werden, desto genauer das Ergebnis. Aber: dauert länger!
        </div>
    </div>
</body>
</html>
