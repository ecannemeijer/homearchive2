<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260208151124 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE admins (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, name VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_ADMIN_USERNAME (username), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE categories (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, color VARCHAR(7) DEFAULT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_3AF34668A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE documents (id INT AUTO_INCREMENT NOT NULL, filename VARCHAR(255) NOT NULL, original_filename VARCHAR(255) NOT NULL, file_type VARCHAR(50) DEFAULT NULL, file_size INT DEFAULT NULL, file_path VARCHAR(500) NOT NULL, uploaded_at DATETIME NOT NULL, subscription_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_A2B072889A1887DC (subscription_id), INDEX IDX_A2B07288A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE monthly_costs (id INT AUTO_INCREMENT NOT NULL, year INT NOT NULL, month INT NOT NULL, total_cost NUMERIC(10, 2) NOT NULL, recorded_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_6C858E20A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE notifications (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(50) DEFAULT NULL, title VARCHAR(255) NOT NULL, message LONGTEXT DEFAULT NULL, is_read TINYINT NOT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, subscription_id INT DEFAULT NULL, INDEX IDX_6000B0D3A76ED395 (user_id), INDEX IDX_6000B0D39A1887DC (subscription_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE offers (id INT AUTO_INCREMENT NOT NULL, provider VARCHAR(191) NOT NULL, plan_name VARCHAR(255) NOT NULL, price NUMERIC(10, 2) NOT NULL, frequency VARCHAR(50) NOT NULL, category VARCHAR(100) DEFAULT NULL, description LONGTEXT DEFAULT NULL, url VARCHAR(1024) DEFAULT NULL, features JSON DEFAULT NULL, conditions LONGTEXT DEFAULT NULL, is_active TINYINT NOT NULL, last_checked DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE passwords (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, username VARCHAR(255) DEFAULT NULL, password_encrypted VARCHAR(1000) NOT NULL, website_url VARCHAR(500) DEFAULT NULL, notes LONGTEXT DEFAULT NULL, tags VARCHAR(500) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_ED822B16A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE savings_recommendations (id INT AUTO_INCREMENT NOT NULL, monthly_savings NUMERIC(10, 2) NOT NULL, yearly_savings NUMERIC(10, 2) NOT NULL, status VARCHAR(50) NOT NULL, notes LONGTEXT DEFAULT NULL, recommended_at DATETIME NOT NULL, responded_at DATETIME DEFAULT NULL, subscription_id INT NOT NULL, offer_id INT NOT NULL, INDEX IDX_726300B49A1887DC (subscription_id), INDEX IDX_726300B453C674EE (offer_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE subscriptions (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(50) NOT NULL, category VARCHAR(100) DEFAULT NULL, cost NUMERIC(10, 2) NOT NULL, frequency VARCHAR(50) NOT NULL, billing_date INT DEFAULT NULL, start_date DATE DEFAULT NULL, end_date DATE DEFAULT NULL, is_monthly_cancelable TINYINT NOT NULL, username VARCHAR(255) DEFAULT NULL, password_encrypted VARCHAR(500) DEFAULT NULL, website_url VARCHAR(500) DEFAULT NULL, notes LONGTEXT DEFAULT NULL, is_active TINYINT NOT NULL, renewal_reminder INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_4778A01A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE categories ADD CONSTRAINT FK_3AF34668A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE documents ADD CONSTRAINT FK_A2B072889A1887DC FOREIGN KEY (subscription_id) REFERENCES subscriptions (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE documents ADD CONSTRAINT FK_A2B07288A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE monthly_costs ADD CONSTRAINT FK_6C858E20A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notifications ADD CONSTRAINT FK_6000B0D3A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notifications ADD CONSTRAINT FK_6000B0D39A1887DC FOREIGN KEY (subscription_id) REFERENCES subscriptions (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE passwords ADD CONSTRAINT FK_ED822B16A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE savings_recommendations ADD CONSTRAINT FK_726300B49A1887DC FOREIGN KEY (subscription_id) REFERENCES subscriptions (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE savings_recommendations ADD CONSTRAINT FK_726300B453C674EE FOREIGN KEY (offer_id) REFERENCES offers (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE subscriptions ADD CONSTRAINT FK_4778A01A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE categories DROP FOREIGN KEY FK_3AF34668A76ED395');
        $this->addSql('ALTER TABLE documents DROP FOREIGN KEY FK_A2B072889A1887DC');
        $this->addSql('ALTER TABLE documents DROP FOREIGN KEY FK_A2B07288A76ED395');
        $this->addSql('ALTER TABLE monthly_costs DROP FOREIGN KEY FK_6C858E20A76ED395');
        $this->addSql('ALTER TABLE notifications DROP FOREIGN KEY FK_6000B0D3A76ED395');
        $this->addSql('ALTER TABLE notifications DROP FOREIGN KEY FK_6000B0D39A1887DC');
        $this->addSql('ALTER TABLE passwords DROP FOREIGN KEY FK_ED822B16A76ED395');
        $this->addSql('ALTER TABLE savings_recommendations DROP FOREIGN KEY FK_726300B49A1887DC');
        $this->addSql('ALTER TABLE savings_recommendations DROP FOREIGN KEY FK_726300B453C674EE');
        $this->addSql('ALTER TABLE subscriptions DROP FOREIGN KEY FK_4778A01A76ED395');
        $this->addSql('DROP TABLE admins');
        $this->addSql('DROP TABLE categories');
        $this->addSql('DROP TABLE documents');
        $this->addSql('DROP TABLE monthly_costs');
        $this->addSql('DROP TABLE notifications');
        $this->addSql('DROP TABLE offers');
        $this->addSql('DROP TABLE passwords');
        $this->addSql('DROP TABLE savings_recommendations');
        $this->addSql('DROP TABLE subscriptions');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
