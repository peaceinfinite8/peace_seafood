# 🛠️ Scripts Directory

This directory contains utility scripts for Peace Seafood application.

---

## 📋 Available Scripts

### **Security**

#### `generate-jwt-secret.php`
Generate a cryptographically secure JWT secret for production use.

**Usage**:
```bash
php scripts/generate-jwt-secret.php
```

**Output**:
- 64-character random hex string
- Instructions for updating .env file
- Security warnings and best practices

**When to use**:
- Before deploying to production
- When rotating JWT secrets
- When setting up new environments

---

### **Path Fixing** (Legacy)

#### `fix-hardcoded-paths.ps1`
PowerShell script to fix hardcoded paths in view files.

**Status**: ✅ Already applied (historical reference)

---

#### `fix-mixed-quotes.ps1`
PowerShell script to fix mixed quotes in JavaScript.

**Status**: ✅ Already applied (historical reference)

---

#### `fix-template-literals.ps1`
PowerShell script to convert string concatenation to template literals.

**Status**: ✅ Already applied (historical reference)

---

#### `remove-duplicate-views.ps1`
PowerShell script to remove duplicate view files.

**Status**: ✅ Already applied (historical reference)

---

## 🔐 Security Best Practices

### **JWT Secret Generation**

Always generate a new JWT secret for each environment:

```bash
# Development
php scripts/generate-jwt-secret.php > dev-secret.txt

# Staging
php scripts/generate-jwt-secret.php > staging-secret.txt

# Production
php scripts/generate-jwt-secret.php > production-secret.txt
```

**Important**:
- Never reuse secrets across environments
- Store secrets securely (password manager, secrets vault)
- Never commit secrets to version control
- Rotate secrets periodically (every 90 days recommended)

---

## 📝 Adding New Scripts

When adding new scripts to this directory:

1. **Add shebang** for PHP scripts:
   ```php
   #!/usr/bin/env php
   <?php
   ```

2. **Make executable** (Linux/Mac):
   ```bash
   chmod +x scripts/your-script.php
   ```

3. **Add documentation** to this README

4. **Add usage examples**

5. **Include error handling**

---

## 🔗 Related Documentation

- `SECURITY_CHECKLIST.md` - Security verification guide
- `.env.production.example` - Production environment template
- `APP_PHP_ANALYSIS.md` - Configuration analysis

---

**Last Updated**: May 30, 2026
