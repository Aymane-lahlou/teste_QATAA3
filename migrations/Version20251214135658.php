<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251214135658 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE client (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, prenom VARCHAR(100) NOT NULL, email VARCHAR(180) NOT NULL, mot_de_passe VARCHAR(255) NOT NULL, telephone VARCHAR(20) DEFAULT NULL, date_inscription DATETIME NOT NULL, statut_compte VARCHAR(20) NOT NULL, roles JSON NOT NULL, UNIQUE INDEX UNIQ_C7440455E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE commande (id INT AUTO_INCREMENT NOT NULL, date_commande DATETIME NOT NULL, montant_total NUMERIC(10, 2) NOT NULL, statut_commande VARCHAR(50) NOT NULL, mode_paiement VARCHAR(50) NOT NULL, email_acheteur VARCHAR(180) DEFAULT NULL, client_id INT NOT NULL, INDEX IDX_6EEAA67D19EB6921 (client_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE evenement (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, date_evenement DATETIME NOT NULL, lieu VARCHAR(255) NOT NULL, categorie VARCHAR(100) NOT NULL, image VARCHAR(255) DEFAULT NULL, statut VARCHAR(50) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE ligne_commande (id INT AUTO_INCREMENT NOT NULL, quantite INT NOT NULL, prix_unitaire NUMERIC(10, 2) NOT NULL, sous_total NUMERIC(10, 2) NOT NULL, commande_id INT NOT NULL, type_billet_id INT NOT NULL, INDEX IDX_3170B74B82EA2E54 (commande_id), INDEX IDX_3170B74BF0517D68 (type_billet_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE recu (id INT AUTO_INCREMENT NOT NULL, numero_recu VARCHAR(100) NOT NULL, date_recu DATETIME NOT NULL, montant_total NUMERIC(10, 2) NOT NULL, fichier_pdf VARCHAR(255) DEFAULT NULL, commande_id INT NOT NULL, UNIQUE INDEX UNIQ_C0D1031795E172BE (numero_recu), UNIQUE INDEX UNIQ_C0D1031782EA2E54 (commande_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE ticket (id INT AUTO_INCREMENT NOT NULL, code_ticket VARCHAR(100) NOT NULL, code_qr LONGTEXT NOT NULL, nom_titulaire VARCHAR(255) DEFAULT NULL, statut_ticket VARCHAR(20) NOT NULL, date_creation DATETIME NOT NULL, ligne_commande_id INT NOT NULL, UNIQUE INDEX UNIQ_97A0ADA3E826F501 (code_ticket), INDEX IDX_97A0ADA3E10FEE63 (ligne_commande_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE type_billet (id INT AUTO_INCREMENT NOT NULL, nom_type VARCHAR(50) NOT NULL, prix NUMERIC(10, 2) NOT NULL, quantite_totale INT NOT NULL, quantite_restante INT NOT NULL, date_validite DATETIME DEFAULT NULL, statut VARCHAR(20) NOT NULL, evenement_id INT NOT NULL, INDEX IDX_3CD421CEFD02F13 (evenement_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67D19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE ligne_commande ADD CONSTRAINT FK_3170B74B82EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id)');
        $this->addSql('ALTER TABLE ligne_commande ADD CONSTRAINT FK_3170B74BF0517D68 FOREIGN KEY (type_billet_id) REFERENCES type_billet (id)');
        $this->addSql('ALTER TABLE recu ADD CONSTRAINT FK_C0D1031782EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id)');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA3E10FEE63 FOREIGN KEY (ligne_commande_id) REFERENCES ligne_commande (id)');
        $this->addSql('ALTER TABLE type_billet ADD CONSTRAINT FK_3CD421CEFD02F13 FOREIGN KEY (evenement_id) REFERENCES evenement (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67D19EB6921');
        $this->addSql('ALTER TABLE ligne_commande DROP FOREIGN KEY FK_3170B74B82EA2E54');
        $this->addSql('ALTER TABLE ligne_commande DROP FOREIGN KEY FK_3170B74BF0517D68');
        $this->addSql('ALTER TABLE recu DROP FOREIGN KEY FK_C0D1031782EA2E54');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA3E10FEE63');
        $this->addSql('ALTER TABLE type_billet DROP FOREIGN KEY FK_3CD421CEFD02F13');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE commande');
        $this->addSql('DROP TABLE evenement');
        $this->addSql('DROP TABLE ligne_commande');
        $this->addSql('DROP TABLE recu');
        $this->addSql('DROP TABLE ticket');
        $this->addSql('DROP TABLE type_billet');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
