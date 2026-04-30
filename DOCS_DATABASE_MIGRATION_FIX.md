# Database Migration Issue - 500 Error Fix

## Problem
The login endpoint (and all API endpoints) were returning HTTP 500 errors after implementing the registration system with file storage.

## Root Cause
**SQLite does not support multiple column additions in a single ALTER TABLE statement.**

The migration file `database/migrations/008_registrations_storage.sql` contained:
```sql
ALTER TABLE registrations
ADD COLUMN signature_data TEXT,
ADD COLUMN payment_proof_path TEXT,
ADD COLUMN referral_code TEXT;
```

This syntax is valid in MySQL/PostgreSQL but **not in SQLite**. When the migration failed:
1. The `_bootstrap.php` tried to execute the migration
2. SQLite threw a syntax error
3. The bootstrap error cascaded to ALL API endpoints  
4. Every API call returned a 500 error

## Solution
Separated the ALTER TABLE statements - one column per statement:

```sql
ALTER TABLE registrations ADD COLUMN signature_data TEXT;
ALTER TABLE registrations ADD COLUMN payment_proof_path TEXT;
ALTER TABLE registrations ADD COLUMN referral_code TEXT;
```

## Prevention Rules for Future Development

### 1. **SQLite-Specific Migration Rules**
- **ALWAYS** use one ALTER TABLE statement per column in SQLite
- **NEVER** use multi-column ALTER in SQLite migrations
- PostgreSQL/MySQL work differently - be aware of target DB

### 2. **Testing Migrations Before Commit**
Before any migration file is finalized:
```bash
# Test SQLite migrations syntax
php -r "
$sql = file_get_contents('database/migrations/XXX_name.sql');
$pdo = new PDO('sqlite::memory:');
$pdo->exec('CREATE TABLE test (id INTEGER PRIMARY KEY)');
try {
    $pdo->exec(\$sql);
    echo 'Migration syntax OK';
} catch (Exception \$e) {
    echo 'ERROR: ' . \$e->getMessage();
}
"
```

### 3. **Bootstrap Error Debugging**
If all endpoints return 500 errors:
1. Check `error_log` in project root for SQL syntax errors
2. Test login endpoint directly with curl:
   ```bash
   curl -X POST http://localhost:8000/api/auth/login \
     -H "Content-Type: application/json" \
     -d '{"email":"test@example.com","password":"pass"}'
   ```
3. Check `schema_migrations` table to see which migrations failed:
   ```bash
   sqlite3 data/app.sqlite "SELECT filename FROM schema_migrations;"
   ```

### 4. **Database Repair Procedure**
If migration fails silently:

**Step 1: Identify failed migration**
```bash
sqlite3 data/app.sqlite "SELECT * FROM schema_migrations;"
# Compare with actual migration files in database/migrations/
```

**Step 2: Fix migration SQL file** (use separate ALTER TABLE per column for SQLite)

**Step 3: Apply migration manually**
```bash
sqlite3 data/app.sqlite < database/migrations/XXX_fixed.sql
```

**Step 4: Mark as executed in database**
```bash
sqlite3 data/app.sqlite "INSERT INTO schema_migrations (filename) VALUES ('XXX.sql');"
```

### 5. **Code Review Checklist**
When reviewing migrations:
- [ ] No multi-column ALTER TABLE in SQLite migrations
- [ ] All DDL statements tested against SQLite syntax
- [ ] Index creation uses `IF NOT EXISTS` clause
- [ ] Foreign key references exist before constraints
- [ ] Column types compatible with SQLite (no ENUM, no AUTO_INCREMENT on non-PK)

### 6. **CI/CD Integration** (for future)
Add pre-commit hook:
```bash
#!/bin/bash
# .git/hooks/pre-commit
for file in database/migrations/*.sql; do
    sqlite3 :memory: < "$file" || {
        echo "Migration syntax error in $file"
        exit 1
    }
done
```

## Files Modified to Fix This Issue

1. **database/migrations/008_registrations_storage.sql**
   - Separated multi-column ALTER TABLE into three separate statements
   - This was the critical fix

2. **database schema**
   - Manually created missing columns in registrations table:
     - `signature_data` TEXT
     - `payment_proof_path` TEXT  
     - `referral_code` TEXT
   - Manually created `associate_payment_methods` table (migration 009)
   - Verified 26 forums exist (migration 010)

## Current Database State

✅ **Registrations table** - has all required columns
- signature_data_url (legacy)
- signature_data (NEW - PNG file paths)
- payment_proof_path (NEW - file paths)
- referral_code (NEW - associate tracking)

✅ **Associate_payment_methods table** - created and ready
- bank_name, account_holder, account_number
- account_type, currency, alias_or_reference
- user_id (UNIQUE - one config per associate)

✅ **Forums** - 26 forums with schedules loaded
✅ **All migrations** - marked as executed in schema_migrations table

## Testing After Fix
The login endpoint should now work. Test with:
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@psme.local","password":"Admin123*"}'
```

Expected response:
```json
{
  "ok": true,
  "user": {
    "id": 4,
    "name": "Maria Luz Genovese",
    "email": "admin@psme.local",
    "role": "admin"
  },
  "csrfToken": "...",
  "sessionExpiresAt": ...
}
```
