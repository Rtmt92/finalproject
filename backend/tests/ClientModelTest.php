<?php
use PHPUnit\Framework\TestCase;
use Src\Models\Client;

class ClientModelTest extends TestCase
{
    public function testPasswordHashingAndVerification(): void
    {
        $client = new Client();

        // Données de test
        $plainPassword = 'TestPassword123!';
        $data = [
            'nom' => 'Test',
            'prenom' => 'Hash',
            'email' => 'hash@test.com',
            'numero_telephone' => '0102030405',
            'mot_de_passe' => password_hash($plainPassword, PASSWORD_DEFAULT),
            'role' => 'client',
            'photo_profil' => null,
            'description' => null
        ];

        // Création du client
        $id = $client->create($data);
        $this->assertIsInt($id, "Échec de création du client");

        // Récupération du client pour vérifier le mot de passe
        $stored = $client->getById($id);
        $this->assertArrayHasKey('mot_de_passe', $stored, "Mot de passe manquant dans la base");
        $this->assertNotEmpty($stored['mot_de_passe'], "Le champ mot_de_passe est vide");

        // Vérification du hash avec password_verify
        $this->assertTrue(password_verify($plainPassword, $stored['mot_de_passe']), "Le mot de passe haché est invalide");

        // Nettoyage
        $client->delete($id);
    }
}
