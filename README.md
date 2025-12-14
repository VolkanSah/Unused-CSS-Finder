# CSS Cleaner Pro 
##### V.1.0.8

*"You killed Kenny! You bastards!"* — But your CSS doesn't have to die unused anymore.

A lightweight PHP tool that crawls your WordPress site (or any website), analyzes all internal pages and stylesheets, and generates a cleaned-up CSS file containing only the selectors you actually use. Perfect for optimizing load times, improving Core Web Vitals, and boosting SEO by cutting dead weight.

---

## Features

* **Smart Crawling** — Recursively scans internal pages up to your specified limit
* **CSS Extraction** — Pulls all linked stylesheets and inline styles
* **Intelligent Matching** — Detects which CSS selectors are actually used in your HTML
* **Pseudo-Class Handling** — Correctly handles `:hover`, `:focus`, `:before`, `:after`, etc.
* **Statistics Dashboard** — Shows original vs. cleaned selector count and percentage saved
* **Zero Dependencies** — Pure PHP, no composer packages or external libraries
* **Download Ready** — Generates timestamped CSS files for easy versioning

---

## Requirements

* PHP 7.4 or higher (8.x recommended)
* `allow_url_fopen` enabled in php.ini
* Basic web server (Apache, Nginx) or PHP built-in server
* Sufficient memory for large sites (adjust `memory_limit` if needed)

---

## Installation & Usage

### Quick Start

1. **Upload** the script to your web server or run locally:
   ```bash
   php -S localhost:8000
   ```

2. **Open** in your browser and enter:
   - Your website URL (e.g., `https://yoursite.com`)
   - Number of pages to scan (5–50 recommended)

3. **Hit Scan** — The tool will:
   - Crawl your site
   - Extract all CSS files
   - Analyze selector usage
   - Generate `cleaned_css_YYYY-MM-DD_HHMMSS.css`

4. **Download** the cleaned CSS and replace your bloated stylesheet

### Best Practices

* **Start Small** — Test with 5-10 pages first to verify results
* **Use Staging** — Run on a development/staging site before production
* **Verify Output** — Always test the cleaned CSS thoroughly before deploying
* **Backup First** — Keep your original CSS safe (obviously)
* **Re-run Periodically** — After major design changes or plugin updates

---

## How It Works

1. **Crawling Phase** — Starts at your URL, finds all internal links, follows them recursively
2. **CSS Collection** — Extracts `<link rel="stylesheet">` references from each page
3. **Selector Parsing** — Breaks down CSS into individual selectors and rules
4. **Usage Detection** — Checks if classes, IDs, and tags exist in the combined HTML
5. **Generation** — Outputs only the selectors that matched

### Matching Algorithm

The tool uses intelligent pattern matching:
- **Class selectors** (`.button`) → Checks for `class="...button..."`
- **ID selectors** (`#header`) → Checks for `id="header"`
- **Tag selectors** (`div`, `h1`) → Checks for `<div>`, `<h1>` tags
- **Pseudo-classes** (`:hover`, `:focus`) → Stripped during matching but kept in output
- **Universal selectors** (`*`) → Always kept
- **Media queries** (`@media`) → Always preserved

---

## Limitations & Known Issues

* **JavaScript-generated content** — Won't detect classes added via JS after page load
* **Dynamic CMS content** — May miss styles used on unpublished/draft pages
* **Complex selectors** — Advanced combinators like `.parent > .child:nth-of-type(2n+1)` are matched simplistically
* **Inline styles** — Not analyzed (only external/internal `<style>` tags in CSS files)
* **Page limit** — Crawling 100+ pages may timeout; adjust `set_time_limit()` or increase `max_pages` gradually

### Improving Accuracy

For best results:
- Scan multiple page types (homepage, blog posts, products, etc.)
- Include pages with different layouts/templates
- Test the output thoroughly before replacing production CSS

---

## Performance Tips

* **Memory Issues?** → Increase `memory_limit = 256M` in php.ini
* **Timeouts?** → Increase `max_execution_time = 300` or reduce page count
* **Slow Crawling?** → Check your server's outgoing connection speed
* **Huge CSS Files?** → Consider breaking them into smaller chunks first

---

## Why You Need This

**Unused CSS** is a silent killer:
- 🐌 Slows down page rendering (browser has to parse everything)
- 📉 Hurts Google PageSpeed Insights scores
- 💸 Wastes bandwidth (especially on mobile)
- 😤 Frustrates users with slow load times

**This tool helps you:**
- ⚡ Reduce CSS file size by 40-80% (typical results)
- 🚀 Improve First Contentful Paint (FCP) and Largest Contentful Paint (LCP)
- 📈 Boost SEO rankings through better Core Web Vitals
- 🧹 Clean up after years of plugin/theme bloat

---

## Example Output

```
Scan läuft...
Scanne: https://example.com
CSS gefunden: https://example.com/wp-content/themes/mytheme/style.css
Scanne: https://example.com/about
Scanne: https://example.com/contact

Bereinige CSS...

✅ Fertig!
Seiten gescannt: 10
Originale Selektoren: 2,847
Verwendete Selektoren: 623
Eingespart: 78.1%

📥 Download: cleaned_css_2024-12-14_153042.css
```

---

## Contributing

Found a bug? Want to add features? PRs welcome!

**Potential Improvements:**
- Add support for `<style>` inline blocks
- Integrate JavaScript selector detection
- Generate before/after comparison reports
- Add whitelist/blacklist for specific selectors
- Support for CSS-in-JS frameworks

---

## About the Author

Created by **Volkan Sah** ([GitHub](https://github.com/volkansah))  
A developer passionate about performance optimization and making the web faster for everyone.

**Other Projects:**
- [GitHub Profile](https://github.com/volkansah)
- [More Tools & Scripts](https://github.com/volkansah?tab=repositories)

---

## License

** GNU GENERAL PUBLIC LICENSE Version 3**


## Support

- 🐛 **Issues:** [GitHub Issues](https://github.com/volkansah/unused-css-finder/issues)
- 💬 **Discussions:** [GitHub Discussions](https://github.com/volkansah/unused-css-finder/discussions)
- ⭐ **Like it?** Star the repo!

---

**Remember:** Always test cleaned CSS before going live. This tool is smart, but you're smarter. 🧠

> Readme.md crafted with help of AI - PHP Code crafted by Volkan Sah
