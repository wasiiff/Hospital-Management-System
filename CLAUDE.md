# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

A Hospital Management System written in **procedural PHP 7 + MySQL (mysqli) + Bootstrap**. There is no framework, no router, no build step, and no automated tests. Each `.php` file at the repo root is simultaneously a page *and* its own request handler. It is designed to be served by Apache out of XAMPP's `htdocs`.

## Running it

There is no build/lint/test tooling (`composer.json` is empty; `vendor/` and `TCPDF/` are vendored libraries). To run locally:

1. Install XAMPP; start Apache + MySQL.
2. In phpMyAdmin create a database named **`myhmsdb`** and import `myhmsdb.sql` into it.
3. Copy the project into `htdocs/` and open `localhost/<foldername>` in a browser.
4. Default admin login: username `admin`, password `admin123` (see `admintb` in `myhmsdb.sql`).

There is no way to run a "single test" — verification is manual through the browser.

## Architecture

### Three roles, dispatched by submit-button name
The app has three user types — **Patient**, **Doctor**, **Admin** — each with its own login/registration flow. There is no central auth layer. Instead, every handler file branches on which named submit button is present in `$_POST` (e.g. `if(isset($_POST['adsub']))`, `if(isset($_POST['docsub1']))`). Forms `action=` directly to the handler file.

Entry points and routing:
- `index.php` — home page with three tabs; posts patient registration → `func2.php`, doctor login → `func1.php`, admin login → `func3.php`.
- `index1.php` — patient login page (→ `func.php`).
- `admin-panel.php` — patient dashboard (book appointment, view history). `admin-panel1.php` — admin dashboard (lists, add doctor, view feedback). The two are near-duplicates of each other.
- `doctor-panel.php` — doctor dashboard; `prescribe.php` — doctor writes a prescription.
- `func.php` / `func1.php` / `func2.php` / `func3.php` — form handlers + shared display helpers (`display_docs()`, `display_admin_panel()`). These four are largely copy-pasted variants of each other; the same helper is redefined in several of them.
- `*search.php` (`search.php`, `appsearch.php`, `doctorsearch.php`, `patientsearch.php`, `messearch.php`) — each renders a results table for one search box.
- `logout.php` / `logout1.php`, `error*.php` — session teardown and error pages.

State is carried in PHP sessions: every handler calls `session_start()` and reads/writes `$_SESSION` (`pid`, `dname`, `username`, etc.). There is no login-guard include actually wired into the panels.

### Database access
Almost every file opens its own connection inline at the top:
```php
$con = mysqli_connect("localhost","root","","myhmsdb");
```
The credentials and DB name are **hardcoded in each file**, not centralized. When changing DB connection details you must edit every file, not one config.

Note `include/config.php` defines a *different* database (`DB_NAME = 'hms'`) and is **not** the connection the running app uses — it appears to be leftover from the `master/` admin template and is effectively dead. The live schema is `myhmsdb.sql`.

### Data model (`myhmsdb.sql`)
- `patreg` — patients (PK `pid`, auto-increment). Stores `password` and `cpassword` in plaintext.
- `doctb` — doctors. **Schema drift warning:** the SQL defines `username, password, email, spec, docFees`, but `func.php` inserts `doctb(username,password,email,docFees)` and `func3.php` inserts `doctb(name)` — the insert column lists across handlers do not agree with each other or fully with the schema. Verify the actual columns before writing queries against `doctb`.
- `appointmenttb` — appointments (PK `ID`). `userStatus` / `doctorStatus` are int flags used to implement appointment cancellation (the "deleted by you" feature described in the README).
- `prestb` — prescriptions (doctor + patient + appointment snapshot + disease/allergy/prescription text).
- `admintb` — single admin row. `contact` — feedback/queries from the public contact form.

### PDF generation
`TCPDF/` is the bundled [TCPDF](https://tcpdf.org) library used to generate bill/prescription PDFs. Treat it as a third-party dependency — do not edit it.

## Conventions and cautions when editing

- **This code is intentionally insecure in its current form.** Queries are built by string-interpolating `$_POST`/`$_GET` directly (`"... where email='$email'"`) — SQL injection is pervasive. Passwords are stored and compared in plaintext. The README's "Need to work on" list confirms these are known gaps. When adding or modifying queries, prefer parameterized `mysqli` prepared statements rather than copying the existing interpolation pattern.
- **Heavy duplication:** the `func*.php` handlers and the two admin panels are copy-paste variants. A change in one often needs to be mirrored in its siblings; check for duplicates before assuming a fix is complete.
- **HTML is echoed from PHP** as large single-quoted strings inside functions like `display_admin_panel()`, mixed with inline `<?php ?>` in the page files. Match the surrounding style of the file you are editing.
- CSS/JS assets live in `css/`, `js/`, `assets/`, `plugins/`, `font-awesome/`, `fonts/`, `img/`, `images/`. Bootstrap is mostly pulled from CDNs in the page `<head>`. `master/` is an unused SASS admin-theme template.
