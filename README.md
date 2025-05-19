# Unused CSS Finder 

*“You killed Kenny! You bastards!”* — But your CSS doesn’t have to die unused anymore.

A lightweight PHP tool to crawl your website, analyze all internal pages and stylesheets, and save a cleaned-up CSS file containing only the used selectors. Perfect for optimizing your site’s load times and improving SEO by cutting dead weight.

---

## Features

* Crawl all internal pages of a given URL
* Extract all linked CSS files
* Detect which CSS selectors are actually used in the HTML
* Save a cleaned CSS file with only used styles
* Simple, no dependencies beyond PHP and basic web access

---

## Requirements

* PHP 7.x or higher
* Allow outgoing HTTP requests
* Basic web server (e.g., Apache, Nginx) or PHP built-in server

---

## How to Use

1. Upload the script to your webserver or run locally with PHP built-in server:

   ```bash
   php -S localhost:8000
   ```
2. Open the page in your browser, enter your website URL, and hit **Scan starten**
3. The script crawls your site, analyzes CSS usage, and creates a `cleaned_css.css` file in the script folder

---

## Notes & Tips

* Best run on staging or local environment — crawling live sites might be slow or heavy
* Relative URLs in CSS and links are resolved automatically
* Some complex CSS selectors might not be detected perfectly (you can improve regex as needed)
* Feel free to tweak and extend for JS-injected styles or inline CSS

---

## Why bother?

Unused CSS bloats your site, slows down rendering, and hurts your SEO rankings. This tool helps you *find* and *trim* that dead code so your site loads faster and scores better with Google.

---

## About the Author

Created by **Volkan Sah** ([GitHub](https://github.com/volkansah)) — a developer who loves to clean up messy code and make life easier for others.

---

## License

MIT License — Use it, break it, fix it, but keep it cool.
