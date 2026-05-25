# 🔴 ERROR HANDLING — Peace Seafood

---

## 🎯 Error Handling Strategy

Systematic error handling untuk reliability & user experience.

---

## 📋 ERROR TYPES & CODES

### **Validation Errors (400)**

```
Code: VALIDATION_ERROR
Message: "Validasi gagal"
Example:
{
  "success": false,
  "error_code": "VALIDATION_ERROR",
  "message": "Validasi gagal",
  "errors": {
    "email": "Email tidak valid",
    "qty": "Qty harus angka positif"
  }
}
```

### **Authentication Errors (401)**

```
Code: UNAUTHORIZED / TOKEN_EXPIRED / TOKEN_INVALID

UNAUTHORIZED:
{
  "error_code": "UNAUTHORIZED",
  "message": "Email atau password tidak sesuai"
}

TOKEN_EXPIRED:
{
  "error_code": "TOKEN_EXPIRED",
  "message": "Token sudah expired, silakan login ulang"
}

TOKEN_INVALID:
{
  "error_code": "TOKEN_INVALID",
  "message": "Token tidak valid"
}
```

### **Authorization Errors (403)**

```
Code: FORBIDDEN / INSUFFICIENT_PERMISSION

{
  "error_code": "FORBIDDEN",
  "message": "Anda tidak memiliki akses ke resource ini"
}

{
  "error_code": "INSUFFICIENT_PERMISSION",
  "message": "Role Anda tidak memiliki permission untuk action ini"
}
```

### **Not Found Errors (404)**

```
Code: NOT_FOUND

{
  "error_code": "NOT_FOUND",
  "message": "Data dengan ID XX tidak ditemukan"
}
```

### **Business Logic Errors (422)**

```
Code: BUSINESS_LOGIC_ERROR / INSUFFICIENT_STOCK / HUTANG_LIMIT_EXCEEDED

INSUFFICIENT_STOCK:
{
  "error_code": "INSUFFICIENT_STOCK",
  "message": "Stok tidak mencukupi. Tersedia: 50 kg, Diminta: 100 kg"
}

HUTANG_LIMIT_EXCEEDED:
{
  "error_code": "HUTANG_LIMIT_EXCEEDED",
  "message": "Hutang sudah mencapai kredit limit. Limit: 50M, Current: 45M, Request: 10M"
}
```

### **Server Errors (500)**

```
Code: INTERNAL_SERVER_ERROR / DATABASE_ERROR

{
  "error_code": "INTERNAL_SERVER_ERROR",
  "message": "Terjadi kesalahan server. Silakan hubungi support."
}

(Detailed error log di server, jangan show ke user)
```

---

## 🛡️ VALIDATION LAYER

### **Server-Side Validation (PHP)**

```php
<?php
class Validator {
    private $errors = [];

    public function validate($data, $rules) {
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;

            if ($rule['required'] && empty($value)) {
                $this->errors[$field] = "$field wajib diisi";
                continue;
            }

            if ($rule['type'] === 'numeric' && !is_numeric($value)) {
                $this->errors[$field] = "$field harus angka";
            }

            if ($rule['type'] === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $this->errors[$field] = "Format email tidak valid";
            }

            if ($rule['min'] && strlen($value) < $rule['min']) {
                $this->errors[$field] = "$field minimal {$rule['min']} karakter";
            }

            if ($rule['max'] && strlen($value) > $rule['max']) {
                $this->errors[$field] = "$field maksimal {$rule['max']} karakter";
            }
        }

        return empty($this->errors);
    }

    public function getErrors() {
        return $this->errors;
    }
}

// Usage
$validator = new Validator();
$rules = [
    'email' => ['required' => true, 'type' => 'email'],
    'qty' => ['required' => true, 'type' => 'numeric', 'min' => 1],
    'password' => ['required' => true, 'min' => 6]
];

if (!$validator->validate($_POST, $rules)) {
    return Response::error(400, 'VALIDATION_ERROR', $validator->getErrors());
}
?>
```

### **Client-Side Validation (JavaScript)**

```javascript
// Real-time validation
document.getElementById("email").addEventListener("blur", function () {
  const email = this.value;
  if (!email.includes("@")) {
    showError(this, "Format email tidak valid");
  } else {
    clearError(this);
  }
});

// Form submit validation
form.addEventListener("submit", async function (e) {
  e.preventDefault();

  const errors = validateForm(this);
  if (Object.keys(errors).length > 0) {
    displayErrors(errors);
    return;
  }

  // Proceed dengan submit
  submitForm();
});
```

---

## 📊 BUSINESS LOGIC VALIDATION

### **Stok Validation**

```php
// Check before posting nota
$stok_available = $this->db->query(
  "SELECT stok_qty FROM produk WHERE id = ? AND id_gudang = ?",
  [$id_produk, $id_gudang]
)->fetch()['stok_qty'];

if ($qty_requested > $stok_available) {
  throw new BusinessLogicException(
    'INSUFFICIENT_STOCK',
    "Stok tidak mencukupi. Tersedia: $stok_available kg, Diminta: $qty_requested kg"
  );
}
```

### **Hutang Validation**

```php
// Check kredit limit
$current_hutang = $this->db->query(
  "SELECT SUM(sisa_hutang) as total FROM hutang_piutang
   WHERE id_pembeli = ? AND jenis = 'piutang' AND status != 'lunas'"
)->fetch()['total'];

$kredit_limit = $pembeli['kredit_limit'];
$total_after_new_hutang = $current_hutang + $total_nota;

if ($total_after_new_hutang > $kredit_limit) {
  throw new BusinessLogicException(
    'HUTANG_LIMIT_EXCEEDED',
    "Hutang sudah mencapai limit. Limit: $kredit_limit, Current: $current_hutang, Request: $total_nota"
  );
}
```

---

## 🔒 TRY-CATCH IMPLEMENTATION

### **All Database Operations**

```php
<?php
try {
    $this->db->beginTransaction();

    // 1. Validate input
    if (!$this->validateInput($data)) {
        throw new ValidationException('Input tidak valid');
    }

    // 2. Check business logic
    if (!$this->checkStockAvailable($data)) {
        throw new BusinessLogicException('INSUFFICIENT_STOCK', '...');
    }

    // 3. Create nota
    $nota_id = $this->createNota($data);

    // 4. Update inventory
    $this->updateInventory($nota_id);

    // 5. Create hutang if needed
    if ($data['pembayaran'] === 'hutang') {
        $this->createHutang($nota_id, $data);
    }

    $this->db->commit();

    return [
        'success' => true,
        'data' => ['id' => $nota_id]
    ];

} catch (ValidationException $e) {
    $this->db->rollBack();
    Logger::error('Validation error: ' . $e->getMessage());
    return Response::error(400, 'VALIDATION_ERROR', $e->errors);

} catch (BusinessLogicException $e) {
    $this->db->rollBack();
    Logger::warning('Business logic error: ' . $e->getCode());
    return Response::error(422, $e->getCode(), $e->getMessage());

} catch (PDOException $e) {
    $this->db->rollBack();
    Logger::error('Database error: ' . $e->getMessage());
    return Response::error(500, 'DATABASE_ERROR', 'Terjadi kesalahan database');

} catch (Exception $e) {
    $this->db->rollBack();
    Logger::error('Unexpected error: ' . $e->getMessage());
    return Response::error(500, 'INTERNAL_SERVER_ERROR', 'Terjadi kesalahan server');
}
?>
```

---

## 📝 LOGGING & MONITORING

### **Logger Implementation**

```php
<?php
use Monolog\Logger;
use Monolog\Handlers\StreamHandler;

$logger = new Logger('peace_seafood');
$logger->pushHandler(new StreamHandler('storage/logs/app.log'));

// Different log levels
$logger->info('User login', ['user_id' => 1]);
$logger->warning('Stok minimum alert', ['produk_id' => 5]);
$logger->error('Database connection failed', ['error' => 'Connection refused']);
$logger->debug('Query executed', ['query' => 'SELECT * FROM nota']);
?>
```

### **Log Format**

```
[2025-05-17 10:30:15] app.INFO: User login {"user_id":1}
[2025-05-17 10:31:22] app.WARNING: Stok minimum alert {"produk_id":5,"stok":15}
[2025-05-17 10:32:45] app.ERROR: Validation failed {"field":"email","error":"Invalid format"}
```

---

## ❌ Error Message Standards

### **For User (Client)**

- Simple, non-technical
- Action-oriented: "Silakan isikan email"
- Clear: "Stok tidak mencukupi"

### **For Developer (Log)**

- Detailed, technical
- Stack trace included
- Query log included
- User/session context

```
[2025-05-17 10:30:15] app.ERROR:
{
  "message": "Database connection failed",
  "code": "SQLSTATE[HY000]",
  "file": "src/utils/Database.php:45",
  "stack_trace": "...",
  "context": {
    "user_id": 1,
    "action": "create_nota"
  }
}
```

---

## 🚨 COMMON ERROR SCENARIOS

| Scenario       | Error Code            | HTTP | User Message                          |
| -------------- | --------------------- | ---- | ------------------------------------- |
| Stok habis     | INSUFFICIENT_STOCK    | 422  | Stok tidak cukup, hanya X kg tersedia |
| Login gagal    | UNAUTHORIZED          | 401  | Email atau password tidak sesuai      |
| Hutang limit   | HUTANG_LIMIT_EXCEEDED | 422  | Hutang sudah mencapai limit kredit    |
| Data tidak ada | NOT_FOUND             | 404  | Data tidak ditemukan                  |
| Akses denied   | FORBIDDEN             | 403  | Anda tidak memiliki akses             |
| Database error | DATABASE_ERROR        | 500  | Terjadi kesalahan database            |
| Invalid email  | VALIDATION_ERROR      | 400  | Format email tidak valid              |

---

## ✅ Error Handling Checklist

- [ ] All user inputs validated server-side
- [ ] Business logic errors caught
- [ ] Database operations in try-catch
- [ ] Transaction rollback on error
- [ ] User-friendly error messages
- [ ] Detailed error logging
- [ ] No sensitive data in client errors
- [ ] Proper HTTP status codes
- [ ] Error response format consistent
- [ ] Rate limiting implemented

---

**Next**: Baca `15-security.md` untuk keamanan aplikasi →
