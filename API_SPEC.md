# API Specification

Berikut adalah spesifikasi API untuk aplikasi GoCapai. Semua request dan response menggunakan format JSON.

## 1. User Registration
**Endpoint:** `POST /api/user/register`  
**Deskripsi:** Mendaftarkan pengguna baru ke dalam sistem.

### Request
```json
{
  "name": "string",
  "email": "string",
  "password": "string"
}
```

### Response (Success - 201)
```json
{
  "message": "User registered successfully",
  "user": {
    "id": "integer",
    "name": "string",
    "email": "string",
    "created_at": "string"
  },
  "token": "string"
}
```

### Response (Error - 400)
```json
{
  "message": "Validation error",
  "errors": {
    "email": ["The email has already been taken."]
  }
}
```

## 2. User Login
**Endpoint:** `POST /api/user/login`  
**Deskripsi:** Masuk ke sistem dan mendapatkan token autentikasi.

### Request
```json
{
  "email": "string",
  "password": "string"
}
```

### Response (Success - 200)
```json
{
  "message": "Login successful",
  "token": "string",
  "user": {
    "id": "integer",
    "name": "string",
    "email": "string"
  }
}
```

### Response (Error - 401)
```json
{
  "message": "Invalid credentials"
}
```

## 3. Get Categories
**Endpoint:** `GET /api/category`  
**Deskripsi:** Mengambil daftar semua kategori.

### Request
Tidak ada body request.

### Response (Success - 200)
```json
{
  "categories": [
    {
      "id": "integer",
      "name": "string",
      "created_at": "string"
    }
  ]
}
```

## 4. Create Category
**Endpoint:** `POST /api/category`  
**Deskripsi:** Membuat kategori baru.

### Request
```json
{
  "name": "string"
}
```

### Response (Success - 201)
```json
{
  "message": "Category created successfully",
  "category": {
    "id": "integer",
    "name": "string",
    "created_at": "string"
  }
}
```

### Response (Error - 400)
```json
{
  "message": "Validation error",
  "errors": {
    "name": ["The name field is required."]
  }
}
```

## 5. Get Products
**Endpoint:** `GET /api/product`  
**Deskripsi:** Mengambil daftar semua produk.

### Request
Tidak ada body request.

### Response (Success - 200)
```json
{
  "products": [
    {
      "id": "string (uuid)",
      "name": "string",
      "description": "string",
      "price": "number",
      "category_id": "integer",
      "stock": "integer",
      "created_at": "string"
    }
  ]
}
```

## 6. Create Product
**Endpoint:** `POST /api/product`  
**Deskripsi:** Membuat produk baru.

### Request
```json
{
  "name": "string",
  "description": "string",
  "price": "number",
  "category_id": "integer",
  "stock": "integer"
}
```

### Response (Success - 201)
```json
{
  "message": "Product created successfully",
  "product": {
    "id": "string (uuid)",
    "name": "string",
    "description": "string",
    "price": "number",
    "category_id": "integer",
    "stock": "integer",
    "created_at": "string"
  }
}
```

### Response (Error - 400)
```json
{
  "message": "Validation error",
  "errors": {
    "name": ["The name field is required."],
    "price": ["The price must be a number."]
  }
}
```

## 7. Get Product by UUID
**Endpoint:** `GET /api/product/{uuid}`  
**Deskripsi:** Mengambil detail produk berdasarkan UUID.

### Request
Tidak ada body request. UUID di URL.

### Response (Success - 200)
```json
{
  "product": {
    "id": "string (uuid)",
    "name": "string",
    "description": "string",
    "price": "number",
    "category_id": "integer",
    "stock": "integer",
    "created_at": "string"
  }
}
```

### Response (Error - 404)
```json
{
  "message": "Product not found"
}
```

## 8. Update Product
**Endpoint:** `PUT /api/product/{uuid}`  
**Deskripsi:** Memperbarui produk berdasarkan UUID.

### Request
```json
{
  "name": "string",
  "description": "string",
  "price": "number",
  "category_id": "integer",
  "stock": "integer"
}
```

### Response (Success - 200)
```json
{
  "message": "Product updated successfully",
  "product": {
    "id": "string (uuid)",
    "name": "string",
    "description": "string",
    "price": "number",
    "category_id": "integer",
    "stock": "integer",
    "updated_at": "string"
  }
}
```

### Response (Error - 404)
```json
{
  "message": "Product not found"
}
```

## 9. Delete Product
**Endpoint:** `DELETE /api/product/{uuid}`  
**Deskripsi:** Menghapus produk berdasarkan UUID.

### Request
Tidak ada body request. UUID di URL.

### Response (Success - 200)
```json
{
  "message": "Product deleted successfully"
}
```

### Response (Error - 404)
```json
{
  "message": "Product not found"
}
```

## 10. Create Transaction
**Endpoint:** `POST /api/transaction`  
**Deskripsi:** Membuat transaksi baru (misalnya pembelian produk).

### Request
```json
{
  "product_id": "string (uuid)",
  "quantity": "integer",
  "user_id": "integer"
}
```

### Response (Success - 201)
```json
{
  "message": "Transaction created successfully",
  "transaction": {
    "id": "integer",
    "product_id": "string (uuid)",
    "quantity": "integer",
    "user_id": "integer",
    "total_price": "number",
    "created_at": "string"
  }
}
```

### Response (Error - 400)
```json
{
  "message": "Validation error",
  "errors": {
    "product_id": ["The product_id field is required."],
    "quantity": ["The quantity must be an integer."]
  }
}
```

## 11. Get Authenticated User
**Endpoint:** `GET /api/user`  
**Deskripsi:** Mengambil data pengguna yang sedang login (memerlukan autentikasi).

### Request
Tidak ada body request. Header: `Authorization: Bearer {token}`

### Response (Success - 200)
```json
{
  "id": "integer",
  "name": "string",
  "email": "string",
  "created_at": "string"
}
```

### Response (Error - 401)
```json
{
  "message": "Unauthenticated"
}
```