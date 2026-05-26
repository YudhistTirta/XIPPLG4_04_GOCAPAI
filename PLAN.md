# GoCapai - Planning Aplikasi Nabung dengan Goal

## 📋 Deskripsi Aplikasi
Aplikasi GoCapai adalah aplikasi mobile/web untuk membantu pengguna menabung dengan target tertentu. Pengguna dapat membuat goal menabung (misal: sepatu 2 juta, iPhone 16 10 juta, dll) dan melacak progress tabungan mereka dengan target mingguan/bulanan/harian.

**Contoh Use Case:**
- User membuat goal: "Beli iPhone 16" dengan target amount Rp 15.000.000
- User set target mingguan: Setor Rp 500.000 per minggu
- User dapat menambah/mengurangi uang dalam tabungan sesuai kemampuan
- Sistem menampilkan progress dan estimasi kapan goal tercapai

---

## 🗂️ Struktur Data & Relasi

### Diagram Relasi Data
```
User (1) ──────────── (M) SavingsGoal
  │
  └──────────── (M) SavingsTransaction

SavingsGoal (1) ──────────── (M) SavingsTransaction
SavingsGoal (1) ──────────── (1) Category
SavingsGoal (1) ──────────── (M) SavingsTarget
```

### Tabel yang Diperlukan

#### 1. **users** (existing, perlu ditambah fields)
- `id` - UUID Primary Key
- `name` - nama pengguna
- `email` - email unik
- `password` - password terenkripsi
- `phone` - nomor telepon (optional)
- `created_at`, `updated_at`

#### 2. **savings_goals** (NEW - untuk menyimpan goal nabung)
- `id` - UUID Primary Key
- `user_id` - FK ke users
- `category_id` - FK ke categories (opsional: makanan, electronic, dll)
- `name` - nama goal (e.g., "Beli iPhone 16")
- `description` - deskripsi detail
- `target_amount` - total uang yang ingin dikumpulkan (decimal)
- `current_amount` - uang yang sudah terkumpul (decimal)
- `status` - enum: active, paused, completed, failed
- `target_frequency` - enum: daily, weekly, monthly (frekuensi target)
- `target_amount_per_frequency` - berapa yang harus disetor per periode (decimal)
- `started_at` - tanggal mulai
- `target_date` - tanggal target selesai (optional)
- `created_at`, `updated_at`

#### 3. **savings_transactions** (NEW - untuk menyimpan setiap transaksi)
- `id` - UUID Primary Key
- `user_id` - FK ke users
- `savings_goal_id` - FK ke savings_goals
- `amount` - nominal uang (decimal, bisa positif/negatif)
- `type` - enum: deposit (menambah), withdrawal (mengurangi)
- `description` - keterangan transaksi (e.g., "Setor dari gajian", "Ambil untuk kebutuhan mendesak")
- `transaction_date` - tanggal transaksi
- `created_at`, `updated_at`

#### 4. **savings_targets** (NEW OPTIONAL - untuk tracking target harian/mingguan/bulanan)
- `id` - UUID Primary Key
- `savings_goal_id` - FK ke savings_goals
- `frequency_type` - enum: daily, weekly, monthly
- `target_amount` - target amount untuk periode ini
- `period_start_date` - tanggal mulai periode
- `period_end_date` - tanggal akhir periode
- `amount_collected` - jumlah yang sudah terkumpul di periode ini
- `status` - enum: pending, on_track, behind, completed
- `created_at`, `updated_at`

#### 5. **categories** (existing, untuk kategorisasi)
- `id` - UUID Primary Key
- `name` - nama kategori
- `description` - deskripsi
- `created_at`, `updated_at`

---

## 🔐 Authentication & Authorization
- **Library:** Laravel Sanctum (sudah ada di User model)
- **Flow:**
  1. User register → dapatkan token
  2. User login → dapatkan token
  3. Setiap request ke API protected route harus include token di header: `Authorization: Bearer {token}`
  4. Token tidak perlu refresh (simple mode), bisa di-logout dengan delete token

---

## 🛣️ API Routes

### Authentication Endpoints
```
POST   /api/auth/register          - Register pengguna baru
POST   /api/auth/login             - Login pengguna
POST   /api/auth/logout            - Logout (hapus token)
GET    /api/auth/me                - Get profil user yang sedang login
```

### SavingsGoal Endpoints (Protected)
```
GET    /api/savings-goals          - Get semua goal user
GET    /api/savings-goals/{id}     - Get detail goal
POST   /api/savings-goals          - Create goal baru
PUT    /api/savings-goals/{id}     - Update goal
DELETE /api/savings-goals/{id}     - Delete goal
PUT    /api/savings-goals/{id}/status - Update status goal (paused/active/completed)
GET    /api/savings-goals/{id}/progress - Get progress goal
```

### SavingsTransaction Endpoints (Protected)
```
GET    /api/savings-transactions                 - Get semua transaksi user
GET    /api/savings-goals/{goalId}/transactions  - Get transaksi untuk goal tertentu
POST   /api/savings-goals/{goalId}/transactions  - Add transaksi deposit/withdrawal
DELETE /api/savings-transactions/{id}            - Delete transaksi
```

### Category Endpoints
```
GET    /api/categories             - Get semua kategori
POST   /api/categories             - Create, Update, Delete kategori (admin only)
```

---

## 📊 Response Format

### Success Response (200/201)
```json
{
  "success": true,
  "message": "string",
  "data": {
    "id": "uuid",
    "name": "string",
    ...
  }
}
```

### Error Response (400/401/403/404)
```json
{
  "success": false,
  "message": "string",
  "errors": {
    "field": ["error message"]
  }
}
```

### List Response
```json
{
  "success": true,
  "message": "string",
  "data": [
    {...},
    {...}
  ],
  "pagination": {
    "total": 10,
    "per_page": 15,
    "current_page": 1,
    "last_page": 1
  }
}
```

---

## 🔄 Business Logic

### 1. Create Savings Goal
- User input: name, target_amount, target_frequency, target_amount_per_frequency, category_id
- System: Set current_amount = 0, status = active, started_at = now()
- Return: goal details dengan initial empty transactions

### 2. Add Transaction (Deposit)
- User input: savings_goal_id, amount, description
- System:
  - Validasi: amount > 0
  - Update: savings_goal.current_amount += amount
  - Create: savings_transaction record
  - Check: Jika current_amount >= target_amount, set status = completed
  - Return: updated goal + transaction details

### 3. Add Transaction (Withdrawal)
- User input: savings_goal_id, amount, description  
- System:
  - Validasi: amount > 0 dan current_amount >= amount
  - Update: savings_goal.current_amount -= amount
  - Create: savings_transaction record (type = withdrawal)
  - Return: updated goal + transaction details

### 4. Get Progress
- Return data:
  - target_amount
  - current_amount
  - percentage_completed (current_amount / target_amount * 100)
  - amount_remaining
  - estimated_completion_date (berdasarkan rata-rata setor per periode)
  - target_frequency
  - target_amount_per_frequency
  - transactions (list 10 terakhir)

---

## 🛠️ Technology Stack
- **Backend:** Laravel 11
- **Database:** MySQL/PostgreSQL
- **Authentication:** Laravel Sanctum
- **API Format:** RESTful JSON

---

## 📝 Implementation Phases

### Phase 1: Database & Models ✅
- [x] Create migration: create_savings_goals_table
- [x] Create migration: create_savings_transactions_table
- [x] Create Model: SavingsGoal dengan relationships
- [x] Create Model: SavingsTransaction dengan relationships
- [x] Update User Model dengan relationships

### Phase 2: Authentication ✅
- [x] Create AuthController
  - [x] register()
  - [x] login()
  - [x] logout()
  - [x] me() - get profile
  - [x] logoutAll()
- [x] Register routes di routes/api.php

### Phase 3: SavingsGoal Controller ✅
- [x] Create SavingsGoalController
  - [x] index() - get all goals user
  - [x] show() - get goal detail
  - [x] store() - create goal
  - [x] update() - update goal
  - [x] destroy() - delete goal
  - [x] updateStatus() - change goal status
  - [x] getProgress() - get progress detail
- [x] Register routes
- [x] Add validation

### Phase 4: SavingsTransaction Controller ✅
- [x] Create SavingsTransactionController
  - [x] index() - get all transactions
  - [x] byGoal() - get transactions for specific goal
  - [x] store() - add deposit/withdrawal
  - [x] destroy() - delete transaction (soft delete)
- [x] Register routes
- [x] Add business logic

### Phase 5: Category Controller ✅
- [x] Create CategoryController
  - [x] index() - get all categories
  - [x] show() - get category
  - [x] store() - create category
  - [x] update() - update category
  - [x] destroy() - delete category
- [x] Register routes

### Phase 6: Documentation ✅
- [x] Update API_SPEC.md dengan semua endpoints
- [x] Update PLAN.md dengan progress

### Phase 7: Testing & Deployment (TODO)
- [ ] Test semua endpoints dengan Postman/Insomnia
- [ ] Test error cases
- [ ] Test authentication
- [ ] Run migrations: php artisan migrate
- [ ] Test di production environment

---

## 🚨 Important Notes
1. **Soft Deletes:** Gunakan soft delete untuk transactions (audit trail)
2. **User Authorization:** Pastikan user hanya bisa akses data miliknya sendiri
3. **Decimal Fields:** Gunakan decimal(15, 2) untuk amount (bisa up to 999 triliun)
4. **Transaction Atomicity:** Pastikan update amount dan create transaction itu atomic
5. **Status Validation:** Validasi status change (active → paused → active OK, tapi completed → active tidak boleh)
6. **Audit Log:** Track siapa yang membuat/update goal dan transaction

---

## 📌 File yang akan dibuat/dimodifikasi
```
app/Models/
├── User.php (update: add relationships)
├── SavingsGoal.php (NEW)
├── SavingsTransaction.php (NEW)
├── SavingsTarget.php (NEW - optional)
└── Category.php (existing atau create baru)

app/Http/Controllers/
├── AuthController.php (NEW)
├── SavingsGoalController.php (NEW)
├── SavingsTransactionController.php (NEW)
├── CategoryController.php (NEW)
└── Api/ (folder untuk API controllers)

database/migrations/
├── 2024_01_01_000004_create_savings_goals_table.php (NEW)
├── 2024_01_01_000005_create_savings_transactions_table.php (NEW)
├── 2024_01_01_000006_create_savings_targets_table.php (NEW)
└── update_users_table_add_phone.php (NEW - opsional)

routes/
└── api.php (update dengan auth & resource routes)

API_SPEC.md (update dengan semua endpoint baru)
```

