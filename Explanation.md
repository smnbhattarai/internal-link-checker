## Explanation

### Problem
- Crawl the homepage to find internal links to other pages. In the meantime figure out pages that links to homepage throughout the site.
Store the result in database and display in the admin page created by the plugin.

### How plugin works
- Plugin creates a page inside `Settings` called `IL Checker`.
- Users will need to trigger the crawl process manually from this page. After that a cron is scheduled every hour.
- This cron basically crawls the site to save necessary links and also creates `index.html` and `sitemap.html` file inside `uploads` folder.
- Crawl result is saved in transient and is deleted when crawl is initiated manually or transient expires.

### Technical decisions
- Transient is used to store records and was chosen because the data change frequently and needs to be rebuild.
- `index.html` and `sitemap.html` are saved inside `uploads/il-checker` folder.
- `sitemap.html` contains simple list of all crawled links while building the crawl result.
- `index.html` is build using `wp_remote_get` function so that all links are captured throughout the page.

### Caveats
- To check link to homepage from internal pages, all posts are fetched. In larger site, this might create performance problem. Next improvement should be to do this in batches.
- Older links that mismatches current blog url are ignored. Example if there was `www` before but now removed. Future improvement should account for this.
