# HomeArchive - Symfony Multi-Tenant Application

A secure multi-tenant subscription and password vault management system built with Symfony 7.

## Features

### 🔐 **Multi-Tenant Architecture**
- Complete data isolation per user (tenant_id based)
- Each user sees only their own data
- Secure row-level security with Doctrine

### 🔑 **Secure Password Vault**
- **AES-256-CBC encryption** for all passwords
- Secure password generator
- Password strength calculator
- Search and tag functionality
- Copy-to-clipboard support

### 📋 **Subscription Management**
- Track subscriptions and insurance policies
- Monthly/yearly cost tracking
- Expiration notifications
- Document attachments
- Category organization

### 👥 **Dual Authentication System**
- **User Login**: Regular users for their personal data
- **Admin Login**: Separate administrator panel
  - Create/manage users
  - Server statistics dashboard
  - Resource monitoring
  - Admin user management

### 📊 **Dashboard & Statistics**
- Real-time cost overview
- Expiring subscriptions alerts
- Monthly/yearly trends
- Category breakdown

## Installation

### Prerequisites
- PHP 8.1 or higher
- MySQL 8.0 or higher
- Composer
- Web server (Apache/Nginx)

### Step 1: Install Dependencies

The project is located in `j:/coding/homearchive2`.

```bash
cd j:/coding/homearchive2
composer install
```

### Step 2: Configure Database

The `.env` file is already configured for MySQL with root user and no password:

```env
DATABASE_URL="mysql://root:@127.0.0.1:3306/homearchive2?serverVersion=8.0.32&charset=utf8mb4"
ENCRYPTION_KEY=h0m34rch1v3_s3cur3_k3y_2026_v2_encrypt_p4ssw0rds
```

**IMPORTANT**: Make sure your PHP has the MySQL PDO extension enabled. Check `php.ini` and uncommwent:
```ini
extension=pdo_mysql
```

### Step 3: Create Database and Schema

```bash
# Create the database
php bin/console doctrine:database:create

# Generate and run migrations
php bin/console make:migration
php bin/console doctrine:migrations:migrate

# Load default admin user
php bin/console doctrine:fixtures:load
```

### Step 4: Start Development Server

```bash
# Start Symfony local server
php -S localhost:8000 -t public/

# OR use Symfony CLI
symfony server:start
```

## Default Credentials

### Administrator Login
- **URL**: `http://localhost:8000/admin/login`
- **Username**: `admin`
- **Password**: `admin123`
- **⚠️ CHANGE THIS IMMEDIATELY IN PRODUCTION!**

### Regular Users
Users must register through the registration page at `http://localhost:8000/register`

## Security Features

### 1. **Password Encryption**
All passwords (both in Password Vault and Subscriptions) are encrypted using AES-256-CBC with a unique encryption key stored in environment variables.

### 2. **Multi-Tenancy**
- Every entity is linked to a specific user via `user_id` foreign key
- Repositories filter all queries by the authenticated user
- No user can access another user's data

### 3. **Separate Admin System**
- Admins are stored in a separate `admins` table
- Separate authentication firewall
- Different role system (`ROLE_ADMIN` vs `ROLE_USER`)

### 4. **CSRF Protection**
- All forms include CSRF tokens
- Symfony security component handles validation

### 5. **Password Hashing**
- Bcrypt with cost factor 12
- Auto-rehashing on algorithm updates

## Project Structure

```
homearchive2/
├── config/
│   ├── packages/
│   │   └── security.yaml          # Dual authentication setup
│   ├── routes/                     # Route configuration
│   └── services.yaml               # Service container
├── src/
│   ├── Controller/
│   │   ├── AdminController.php    # Admin panel & user management
│   │   ├── SecurityController.php # User login/register
│   │   ├── DashboardController.php
│   │   ├── PasswordVaultController.php
│   │   └── SubscriptionController.php
│   ├── Entity/
│   │   ├── Admin.php              # Administrator entity
│   │   ├── User.php               # Regular user entity
│   │   ├── Password.php           # Password vault entries
│   │   ├── Subscription.php       # Subscriptions/insurance
│   │   ├── Document.php
│   │   ├── Category.php
│   │   ├── Notification.php
│   │   ├── MonthlyCost.php
│   │   ├── Offer.php
│   │   └── SavingsRecommendation.php
│   ├── Repository/               # Data access layer
│   ├── Service/
│   │   └── EncryptionService.php # AES-256-CBC encryption
│   └── DataFixtures/
│       └── AppFixtures.php       # Default admin seeder
├── templates/
│   ├── base.html.twig            # User interface layout
│   ├── admin_base.html.twig      # Admin interface layout
│   ├── admin/                    # Admin templates
│   ├── security/                 # Login/register
│   ├── dashboard/
│   ├── password_vault/
│   └── subscription/
└── .env                          # Environment configuration
```

## API Endpoints

### User Routes
- `GET /` - Dashboard
- `GET /login` - User login
- `POST /login` - Authenticate user
- `GET /register` - Registration form
- `POST /register` - Create user account
- `GET /logout` - Logout

### Password Vault
- `GET /password-vault` - List all passwords
- `GET /password-vault/create` - Create new password
- `GET /password-vault/{id}` - View password (decrypted)
- `GET /password-vault/{id}/edit` - Edit password
- `POST /password-vault/{id}/delete` - Delete password
- `GET /password-vault/api/generate-password` - Generate secure password

### Subscriptions
- `GET /subscriptions` - List all subscriptions
- `GET /subscriptions/create` - Create subscription
- `GET /subscriptions/{id}` - View subscription
- `GET /subscriptions/{id}/edit` - Edit subscription
- `POST /subscriptions/{id}/delete` - Delete subscription

### Admin Routes
- `GET /admin/login` - Admin login
- `GET /admin` - Admin dashboard with server statistics
- `GET /admin/users` - Manage users
- `POST /admin/users/create` - Create new user
- `POST /admin/users/{id}/delete` - Delete user
- `GET /admin/administrators` - Manage administrators
- `POST /admin/administrators/create` - Create new admin
- `GET /admin/change-password` - Change admin password

## Database Schema

All tables include proper foreign key constraints and indexes for performance. Each user's data is completely isolated through foreign keys:

- **users** - Regular user accounts
- **admins** - Administrator accounts (separate table)
- **subscriptions** - User subscriptions (with `user_id`)
- **passwords** - Password vault entries (with `user_id`)
- **documents** - File attachments (with `user_id`)
- **categories** - User-specific categories (with `user_id`)
- **monthly_costs** - Cost tracking (with `user_id`)
- **notifications** - User notifications (with `user_id`)
- **offers** - Price comparison offers (shared)
- **savings_recommendations** - Savings suggestions

## Development Notes

### Multi-Tenancy Implementation
The multi-tenancy is implemented at the application layer:
1. All entities have a `user` foreign key relationship
2. Repositories filter by the authenticated user
3. Controllers use `$this->getUser()` to get current user
4. No user can query another user's data

### Adding Templates
You'll need to create additional Twig templates for:
- Dashboard display (`templates/dashboard/index.html.twig`)
- Password vault views (`templates/password_vault/*.html.twig`)
- Subscription views (`templates/subscription/*.html.twig`)
- Admin panel views (`templates/admin/*.html.twig`)

These can be modeled after the original homearchive application's layout.

## Production Deployment

1. **Change default admin password immediately**
2. **Use a strong encryption key** (generate with `php bin/console secret:generate`)
3. **Enable HTTPS** for all traffic
4. **Set `APP_ENV=prod`** in `.env`
5. **Clear cache**: `php bin/console cache:clear --env=prod`
6. **Set proper file permissions** on `var/` directory
7. **Configure web server** (Apache/Nginx) with proper document root pointing to `public/`

## Security Recommendations

- Store `.env` outside web root in production
- Use environment variables for sensitive data
- Enable database SSL connections
- Implement rate limiting on login endpoints
- Regular security audits
- Keep Symfony and dependencies updated
- Use PHP 8.2+ for latest security patches

## License

Proprietary - HomeArchive 2026

## Support

For issues or questions, contact the system administrator.
