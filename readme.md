# Aurora বাংলা (PHP + JSON)

এই রিপোজিটরি এখন VuePress-নির্ভর নয়; PHP 8.1+ এবং JSON data storage ব্যবহার করে standalone Aurora-style ব্লগ অ্যাপ।

## ফিচার (বাংলা UI)
- হোমপেজ + পোস্ট তালিকা + পোস্ট ডিটেইল
- ক্যাটাগরি/ট্যাগ/টাইমলাইন
- “বলেছি” (moments)
- বন্ধু লিংক
- ফটো অ্যালবাম
- মিউজিক প্লেয়ার
- মন্তব্য ফর্ম + মডারেশন
- সার্চ/ফিল্টার + pagination

## রান করার নিয়ম (লোকাল)
```bash
php -S 127.0.0.1:8080 -t public
```
তারপর ব্রাউজারে `http://127.0.0.1:8080`

## প্রয়োজনীয়তা
- PHP 8.1+
- JSON read/write এর জন্য `storage/data` ডিরেক্টরিতে write permission

## নিরাপত্তা
- Context-aware escaping (`htmlspecialchars`)
- CSRF token validation (সব POST action)
- Comment input validation (length/email/url/honeypot/rate-limit)
- Atomic JSON write + file locking (`flock`)
- Secure headers: CSP, X-Content-Type-Options, X-Frame-Options, Referrer-Policy
- Session cookie: httponly + samesite + HTTPS হলে secure
- Data files web root-এর বাইরে (`storage/data`), `.htaccess` deny sample যুক্ত

## writable path
- `storage/data/comments.json`
- প্রয়োজনে import/update এর সময় `storage/data/posts.json` ইত্যাদি

## JSON ডাটা সম্পাদনা
সব data file `storage/data/*.json`-এ। ফাইল save করার সময় valid JSON বজায় রাখুন।

## মন্তব্য মডারেশন
- URL: `/admin/comments`
- প্রথমে পাসওয়ার্ড লগইন
- default password: `change-me-now` (চালুর আগে অবশ্যই বদলান)
- hash রাখা আছে `storage/config/app.php`-এ (`password_hash/password_verify` flow)

## Markdown → JSON মাইগ্রেশন
পুরোনো `docs/**/*.md` থেকে পোস্ট ইমপোর্ট:
```bash
php scripts/import-markdown.php
```
ম্যাপিং:
- `title`
- `date`
- `category/categories`
- `tag/tags`
- `cover`
- `content`

## টেস্ট/ভ্যালিডেশন
```bash
bash scripts/lint-php.sh
php tests/validate-json.php
bash tests/smoke.sh
```

## Shared Hosting Deploy
1. `public` কে document root করুন (বা `public/index.php` rewrite)
2. `storage` web root-এর বাইরে রাখুন
3. `storage/data`-তে write permission দিন (0640/0750 নীতি)
4. `storage/config/app.php`-এর admin hash পরিবর্তন করুন

## সীমাবদ্ধতা
- Markdown renderer lightweight; advanced VuePress markdown extensions নেই
- Comment moderation basic; full role-based auth যুক্ত নয়
