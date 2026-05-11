# API Specification - GoCapai Savings App

Dokumentasi lengkap API untuk aplikasi GoCapai. Semua request dan response menggunakan format JSON.

---

## 📋 Base URL
```
http://localhost:8000/api
```

---

## 🔐 Authentication
Semua endpoint yang dilindungi (Protected) memerlukan token di header:
```
Authorization: Bearer {token}
```

Token didapatkan dari endpoint login/register.

---

## ✅ Response Format

### Success Response
```json
{
  "success": true,
  "message": "string",
  "data": {}
}
```

### Error Response
```json
{
  "success": false,
  "message": "string",
  "errors": {
    "field": ["error message"]
  }
}
```

---

# 🔑 Authentication Endpoints

## 1. Register User
**Endpoint:** `POST /auth/register`  
**Protected:** ❌ No  
**Deskripsi:** Mendaftarkan pengguna baru ke dalam sistem.

### Request
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "08123456789"
}
```

### Response (Success - 201)
```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "user": {
      "id": "550e8400-e29b-41d4-a716-446655440000",
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "08123456789",
      "created_at": "2024-05-11T10:00:00Z"
    },
    "token": "1|abcdefghijklmnopqrstuvwxyz",
    "token_type": "Bearer"
  }
}
```

### Validation Rules
- `name`: required, string, max 255 characters
- `email`: required, string, email format, unique, max 255 characters
- `password`: required, string, min 8 characters, must be confirmed
- `phone`: optional, string, max 20 characters

---

## 2. Login User
**Endpoint:** `POST /auth/login`  
**Protected:** ❌ No  
**Deskripsi:** Login dan mendapatkan token autentikasi.

### Request
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

### Response (Success - 200)
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": "550e8400-e29b-41d4-a716-446655440000",
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "08123456789"
    },
    "token": "2|abcdefghijklmnopqrstuvwxyz",
    "token_type": "Bearer"
  }
}
```

### Error Response (401)
```json
{
  "success": false,
  "message": "Invalid credentials",
  "errors": {
    "email": ["The provided credentials are invalid."]
  }
}
```

---

## 3. Get Current User Profile
**Endpoint:** `GET /auth/me`  
**Protected:** ✅ Yes  
**Deskripsi:** Mendapatkan profil user yang sedang login.

### Response (Success - 200)
```json
{
  "success": true,
  "message": "User profile retrieved",
  "data": {
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "08123456789",
    "created_at": "2024-05-11T10:00:00Z",
    "updated_at": "2024-05-11T10:00:00Z"
  }
}
```

---

## 4. Logout User
**Endpoint:** `POST /auth/logout`  
**Protected:** ✅ Yes  
**Deskripsi:** Logout dan revoke current token.

### Response (Success - 200)
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

---

## 5. Logout All Sessions
**Endpoint:** `POST /auth/logout-all`  
**Protected:** ✅ Yes  
**Deskripsi:** Logout dari semua device (revoke semua token).

### Response (Success - 200)
```json
{
  "success": true,
  "message": "Logged out from all devices successfully"
}
```

---

# 📝 Catatan
Dokumentasi ini mencakup semua endpoint API yang saat ini diimplementasikan. Endpoint untuk Savings Goals, Transactions, dan Categories masih dalam tahap pengembangan.