# CRUD Module Akun - Dokumentasi

Module ini menyediakan sistem CRUD lengkap untuk mengelola akun keuangan menggunakan Filament dengan arsitektur yang terstruktur dan mudah di-maintenance.

> **⚠️ CATATAN PENTING**: Module ini saat ini tidak memiliki dependency pada model `Transaksi`. Relationship dan validasi terkait transaksi telah di-comment out sementara hingga model `Transaksi` dibuat.

## Struktur Folder

```
app/
├── Actions/                          # Action classes untuk operasi bisnis
│   ├── BulkDeleteAkunAction.php     # Bulk delete akun
│   └── ToggleAkunStatusAction.php   # Toggle status aktif/nonaktif
├── Filament/
│   ├── Resources/
│   │   ├── AkunResource.php         # Resource utama Filament
│   │   └── AkunResource/Pages/      # Pages untuk CRUD operations
│   │       ├── CreateAkun.php       # Halaman create akun
│   │       ├── EditAkun.php         # Halaman edit akun
│   │       ├── ListAkuns.php        # Halaman list akun dengan tabs
│   │       └── ViewAkun.php         # Halaman detail akun
│   ├── Tables/
│   │   └── AkunTable.php            # Custom table dengan filter advanced
│   └── Widgets/
│       └── AkunStatsWidget.php      # Widget statistik dashboard
├── Http/Requests/
│   ├── CreateAkunRequest.php        # Validasi untuk create akun
│   └── UpdateAkunRequest.php        # Validasi untuk update akun
├── Models/
│   └── Akun.php                     # Model Eloquent dengan relationships
├── Policies/
│   └── AkunPolicy.php               # Policy untuk otorisasi
├── Services/
│   └── AkunService.php              # Service untuk business logic
└── Traits/
    ├── BelongsToUser.php            # Trait untuk filter otomatis per user
    └── HasDefaultValues.php         # Trait untuk default values
```

## Fitur Utama

### 1. **Model & Database**
- Mendukung 5 tipe akun: Bank, Kas, E-Wallet, Investasi, Kredit
- Field dinamis berdasarkan tipe akun:
  - Bank/Kredit: Nama bank, nomor rekening
  - E-Wallet: Nama e-wallet, nomor HP
- Automatic user scope (user hanya bisa akses akun miliknya)
- Default values otomatis

### 2. **Validasi Robust**
- FormRequest terpisah untuk Create & Update
- Validasi conditional berdasarkan tipe akun
- Validasi format nomor HP untuk e-wallet
- Validasi hex color untuk warna akun
- Messages dalam Bahasa Indonesia

### 3. **Resource Filament**
- Form dinamis: field muncul/hilang berdasarkan tipe akun
- Table dengan filter advanced dan sorting
- Tab view berdasarkan tipe akun
- Bulk actions dengan validasi
- View page dengan infolist yang informatif

### 4. **Otorisasi**
- Policy-based authorization
- User hanya bisa CRUD akun miliknya sendiri
- Proteksi terhadap bulk delete akun dengan transaksi
- Global scope otomatis per user

### 5. **Business Logic Service**
- Centralized business logic di AkunService
- Statistics dan reporting
- Account operations (toggle status, etc.)
- Clean separation dari Filament Resource

### 6. **Widget Dashboard**
- Statistik real-time: total akun, saldo, breakdown per tipe
- Visual yang informatif dengan icons dan colors
- Responsive design

## Cara Penggunaan

### 1. **Setup Database**
Migration sudah tersedia:
```bash
php artisan migrate
```

### 2. **Register Resource di Panel**
Tambahkan di `AdminPanelProvider`:
```php
->resources([
    AkunResource::class,
])
->widgets([
    AkunStatsWidget::class,
])
```

### 3. **Penggunaan Service**
```php
// Get accounts untuk user
$service = app(AkunService::class);
$accounts = $service->getUserAccounts();

// Create account
$account = $service->createAccount($data);

// Get statistics
$stats = $service->getAccountStatistics();
```

## Validasi Rules

### Create Account
- `nama`: required, string, 2-255 karakter
- `tipe`: required, enum (bank, kas, e-wallet, investasi, kredit)
- `saldo_awal`: optional, numeric, >= 0
- `nomor_rekening`: required jika tipe bank/kredit
- `nama_bank`: required jika tipe bank/kredit  
- `nama_ewallet`: required jika tipe e-wallet
- `nomor_hp`: required jika tipe e-wallet, format Indonesian phone

### Update Account
- Same rules sebagai create
- Tambahan: authorization check ownership

## Tipe Akun & Field

| Tipe | Required Fields | Optional Fields |
|------|----------------|-----------------|
| Bank | nama_bank, nomor_rekening | - |
| Kredit | nama_bank, nomor_rekening | - |
| E-Wallet | nama_ewallet, nomor_hp | - |
| Kas | - | - |
| Investasi | - | - |

## Security Features

1. **User Isolation**: Automatic user scoping via Trait
2. **Policy Authorization**: Fine-grained permissions
3. **Input Sanitization**: FormRequest validation
4. **SQL Injection Protection**: Eloquent ORM
5. **CSRF Protection**: Laravel built-in

## Performance Optimizations

1. **Efficient Queries**: 
   - Scoped queries per user
   - Indexed columns (user_id, aktif)
   
2. **Lazy Loading**: 
   - Conditional form fields
   - Toggleable table columns
   
3. **Caching Ready**: 
   - Service layer siap untuk caching
   - Statistics dapat di-cache

## Extensibility

### Menambah Tipe Akun Baru
1. Update konstanta di `Akun` model
2. Update `getTipeOptions()` method
3. Update validasi rules
4. Update form conditional logic

### Custom Actions
Extend `AkunResource` dengan custom actions:
```php
->headerActions([
    Action::make('export')
        ->action(fn () => ExportService::exportAccounts())
])
```

### Custom Filters
Tambah filter di `AkunTable`:
```php
Filter::make('created_this_month')
    ->query(fn (Builder $query): Builder => 
        $query->whereMonth('created_at', now()->month)
    )
```

## Testing

Contoh test cases yang harus dicover:
- User hanya bisa akses akun miliknya
- Validasi field berdasarkan tipe akun
- Default values ter-set dengan benar
- Policy authorization working
- Service methods return correct data

## Todo: Integration dengan Transaksi Model

Ketika model `Transaksi` telah dibuat, lakukan hal berikut:

1. **Update Model Akun** (`app/Models/Akun.php`):
   ```php
   // Uncomment relationship
   public function transaksis(): HasMany
   {
       return $this->hasMany(Transaksi::class, 'akun_id');
   }
   ```

2. **Update AkunPolicy** (`app/Policies/AkunPolicy.php`):
   ```php
   // Uncomment validation
   private function canBeDeleted(Akun $akun): bool
   {
       return $akun->transaksis()->doesntExist();
   }
   ```

3. **Update AkunService** (`app/Services/AkunService.php`):
   ```php
   // Uncomment transaction checks di method deleteAccount() dan canBeDeleted()
   ```

## Troubleshooting

### Common Issues

1. **Field tidak muncul di form**
   - Check conditional logic di `AkunResource`
   - Pastikan `live()` aktif pada Select tipe

2. **User bisa akses akun orang lain**
   - Check Policy registration di `AuthServiceProvider`
   - Verify global scope di trait `BelongsToUser`

3. **Validasi error**
   - Check FormRequest rules
   - Pastikan `prepareForValidation()` berjalan

4. **Widget tidak muncul**
   - Register widget di panel provider
   - Check `canView()` method

## Best Practices

1. **Always use Service layer** untuk business logic
2. **FormRequest validation** untuk input validation
3. **Policy-based authorization** untuk security
4. **Trait usage** untuk reusable functionality
5. **Clean separation** antara Filament dan business logic
6. **Proper error handling** di semua layers
7. **Documentation** untuk maintainability
