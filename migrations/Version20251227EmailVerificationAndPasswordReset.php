<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251227EmailVerificationAndPasswordReset extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add email verification and password reset tokens to Client entity';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE client ADD is_email_verified TINYINT(1) NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE client ADD email_verification_token VARCHAR(255) NULL');
        $this->addSql('ALTER TABLE client ADD email_verified_at DATETIME NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE client ADD password_reset_token VARCHAR(255) NULL');
        $this->addSql('ALTER TABLE client ADD password_reset_requested_at DATETIME NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CLIENT_EMAIL_VERIFICATION_TOKEN ON client (email_verification_token)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CLIENT_PASSWORD_RESET_TOKEN ON client (password_reset_token)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_CLIENT_PASSWORD_RESET_TOKEN ON client');
        $this->addSql('DROP INDEX UNIQ_CLIENT_EMAIL_VERIFICATION_TOKEN ON client');
        $this->addSql('ALTER TABLE client DROP COLUMN is_email_verified');
        $this->addSql('ALTER TABLE client DROP COLUMN email_verification_token');
        $this->addSql('ALTER TABLE client DROP COLUMN email_verified_at');
        $this->addSql('ALTER TABLE client DROP COLUMN password_reset_token');
        $this->addSql('ALTER TABLE client DROP COLUMN password_reset_requested_at');
    }
}
