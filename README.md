# Purchase Entry Module

A web application purchase items, viewing purchase history, and managing access with **Admin** and **User** roles.

---

## Setup guide

This section is for anyone who needs to **install and run** the app by following steps and copying commands. You do not need to understand the code.

### System Requirements

| Software | Purpose |
|----------|---------|
| **PHP** (8.3) | Runs the application backend |
| **Composer** | Installs PHP dependencies |
| **Node.js** and **npm** | Builds page styles and frontend assets |
| **MySQL** | Stores your data (database) |

### How to open the terminal

- **Windows:** Start menu → search for `cmd` or `PowerShell` → open it  
- **Mac:** Spotlight → search for `Terminal`  
- **Linux:** Open the Terminal app  


Your folder path may be different—ask your developer if unsure.

---

### Step 1 — Install dependencies (first time only)

```bash
git clone https://github.com/haris-mage4/purshase-entry-module.git
```

Go to the project folder (where these files live). Example:

```bash
cd /var/www/html/purshase-entry-module
```

```bash
composer install
```
```bash
npm install
```

**What this does:** Downloads everything the app needs. Requires internet. May take a few minutes.

---

### Step 2 — Create the settings file

```bash
cp .env.example .env
```

```bash
php artisan key:generate
```

**What this does:** Sets up the application’s configuration and security key.

Open the **`.env`** file in a text editor (Notepad, VS Code, etc.) and check the database section. For MySQL it should look like this:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=purchase_entry
DB_USERNAME=root
DB_PASSWORD=your_mysql_password
```

**Create the database:** In MySQL, create a database named `purchase_entry`.

---

### Step 3 — Create database tables (migration)

```bash
php artisan migrate
```

**What this does:** Creates all tables the app needs (items, brands, purchases, users, etc.).

If you see **“database does not exist”**, create the database first (Step 2).

---

### Step 4 — Add sample users and catalog data

```bash
php artisan db:seed
```

**What this does:** Adds login accounts and sample items/brands so you can test the app immediately.

---

### Step 5 — Build the website styles

```bash
npm run build
```

**What this does:** Prepares CSS and JavaScript so pages look correct. During development, a developer may use `npm run dev` instead.

---

### Step 6 — Start the application

```bash
php artisan serve
```

You should see:

```text
Server running on [http://127.0.0.1:8000]
```

Open your **browser** (Chrome, Firefox, Edge, etc.) and go to:

**http://127.0.0.1:8000**

To stop the app, press **Ctrl + C** in the terminal.

---

### How to log in

| Email | Password | Role | What you can do |
|-------|----------|------|-----------------|
| `admin@example.com` | `password` | Admin | Create, edit, and delete purchases; run migrations |
| `user@example.com` | `password` | User | View purchases only (read-only) |

### Using the app in the browser

1. On the login page, enter email and password → click **Sign in**  
2. **Purchases** — see the list of all purchases and line items  
3. If you are **Admin**, you can also:  
   - **New purchase** — open the purchase form  
   - **Edit** or **Delete** on an existing purchase  
   - **Run migrations** — update the database (use only when your developer approves)  

If a **User** tries to open an admin-only page (e.g. create purchase), they are sent back to the purchase list with the message: *You do not have permission to access this page.*

---

### Import old data (optional)

To import legacy purchase lines (item name, brand name, quantity, price) from a script:

```bash
php artisan purchases:import-legacy
```

- **First run:** data is imported  
- **Second run with the same data:** nothing is duplicated (safe to run again)  

Ask your developer how to change the data inside this command for your own records.

---

### Common problems

| Problem | What to try |
|---------|-------------|
| `command not found: php` | PHP is not installed—install PHP or ask your developer |
| `command not found: composer` | Install Composer |
| Database connection error | Fix database name, username, and password in `.env`; make sure MySQL is running |
| Page looks broken / no styling | Run `npm run build` again |
| Cannot log in | Run `php artisan db:seed` again; use the emails and passwords in the table above |
| Port already in use | Run `php artisan serve --port=8001` and open `http://127.0.0.1:8001` |

---

## Technical guide (developers)

### Requirements

- PHP 8.3+
- Composer
- Node.js 18+ and npm
- MySQL (recommended) or SQLite

### Quick setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
# Configure DB in .env
php artisan migrate
php artisan db:seed
npm run build
php artisan serve
```

### Database tables (after migrate)

| Table | Purpose |
|-------|---------|
| `items` | Product catalog (`id`, `name`, timestamps) |
| `brands` | Brand catalog (`id`, `name`, timestamps) |
| `purchases` | Purchase header (`id`, `total`, timestamps) |
| `purchase_items` | Line items (`purchase_id`, `item_id`, `brand_id`, `qty`, `price`, timestamps) |
| `users` | Authentication + `role` (`admin` / `user`) |
| `legacy_import_logs` | Idempotent legacy import tracking |

### Routes

| URL | Access | Description |
|-----|--------|-------------|
| `/login` | Guest | Sign in |
| `/purchases` | Admin, User | View all purchases and line items |
| `/purchases/create` | Admin | Create a new purchase |
| `/purchases/{id}/edit` | Admin | Edit or delete a purchase |
| `/admin/migrations` | Admin | Run `php artisan migrate --force` from the UI |

Unauthorized users who hit admin URLs are redirected to `/purchases` with a flash error message.

### Roles

| Role | Permissions |
|------|-------------|
| **Admin** | Create, edit, delete purchases; run migrations (CLI and UI) |
| **User** | View purchases only |

### Purchase form (Admin)

- Dynamic rows: item, brand, quantity, price  
- Alpine.js + Livewire `$wire.entangle()` for reactive rows and validation  
- Duplicate **item + brand** combinations on the same purchase are blocked  
- Instant client-side validation with server sync via Livewire  
- Live total and subtotals  
- All rows can be removed; empty state shows *“There are no purchase items.”*  

**Note:** Livewire bundles Alpine. Do not call `Alpine.start()` in `resources/js/app.js` (a second Alpine instance breaks `$wire`).

### Legacy data import

Legacy rows use **names** instead of foreign keys:

```php
$legacyPurchases = [
    [
        'item_name'  => 'Sugar',
        'brand_name' => 'ABC',
        'qty'        => 10,
        'price'      => 100,
    ],
];
```

`App\Services\LegacyPurchaseImporter`:

1. Maps `item_name` → `items` (creates if missing)  
2. Maps `brand_name` → `brands` (creates if missing)  
3. Creates one `purchase` and related `purchase_items` with proper IDs  
4. Avoids duplicate catalog names via `firstOrCreate`  
5. Stays **idempotent** via a SHA-256 hash in `legacy_import_logs`  

**Artisan command:**

```bash
php artisan purchases:import-legacy
```

**Seeder:**

```bash
php artisan db:seed --class=Database\\Seeders\\LegacyPurchaseSeeder
```

**Programmatic:**

```php
use App\Services\LegacyPurchaseImporter;

$result = app(LegacyPurchaseImporter::class)->import($legacyPurchases);
```

Edit the array in `ImportLegacyPurchasesCommand` or `LegacyPurchaseSeeder` to import different data.

### Run migrations

**CLI (recommended for production):**

```bash
php artisan migrate
```

**Admin UI:** sign in as admin → **Run migrations**.

### Tests

```bash
php artisan test
```

### Assumptions

1. Email/password session auth; no public registration UI (users are seeded or created manually).  
2. Roles are stored on `users.role` as `admin` or `user`.  
3. Catalog names use `firstOrCreate` on the exact trimmed name (case-sensitive).  
4. Admin save requires at least one line; duplicate item+brand on the same purchase is invalid.  
5. One legacy `import()` call creates **one** purchase containing **all** lines in the array.  
6. Re-importing the same normalized payload is skipped; any change produces a new hash and a new purchase.  
7. Legacy lines missing `item_name` or `brand_name` are ignored.  
8. Livewire 4 bundles Alpine; Vite builds CSS/JS only.  
9. MySQL is the primary target; SQLite works for local use via `.env`.  
10. Prefer CLI `php artisan migrate` in production; the admin UI runner is for controlled/dev use.

### Project structure

```text
app/
  Console/Commands/ImportLegacyPurchasesCommand.php
  Enums/Role.php
  Http/Middleware/EnsureUserHasRole.php
  Livewire/PurchaseForm.php, PurchaseList.php, RunMigrations.php
  Models/Item.php, Brand.php, Purchase.php, PurchaseItem.php, LegacyImportLog.php
  Policies/PurchasePolicy.php
  Services/LegacyPurchaseImporter.php
database/migrations/
database/seeders/
resources/views/livewire/
routes/web.php
```

## License

MIT (Laravel framework license applies to the underlying framework).
