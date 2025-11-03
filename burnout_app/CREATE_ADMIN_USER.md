# Creating Admin User

To create the admin user with the credentials:
- **Email**: admin@email.com
- **Password**: admin@password

Run the following command in your terminal:

```bash
php artisan db:seed --class=AdminUserSeeder
```

Or if you want to run all seeders:

```bash
php artisan db:seed
```

## Alternative: Using Tinker

You can also create the admin user directly using Laravel Tinker:

```bash
php artisan tinker
```

Then run:

```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

$admin = User::firstOrCreate(
    ['email' => 'admin@email.com'],
    [
        'name' => 'Admin',
        'password' => Hash::make('admin@password')
    ]
);

// If user already exists, update password
if ($admin->wasRecentlyCreated === false) {
    $admin->password = Hash::make('admin@password');
    $admin->save();
}
```

## Login

After creating the admin user, you can login at:
- URL: `/login`
- Email: `admin@email.com`
- Password: `admin@password`

