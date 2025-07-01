<?php
use PHPUnit\Framework\TestCase;
use Controllers\ClientController;

class SecurityTest extends TestCase
{
    public function testSqlInjectionOnClientCreation(): void
    {
        $controller = new ClientController();

        $malicious = [
            'nom' => "'; DROP TABLE client; --",
            'prenom' => 'Injection',
            'email' => 'inject@test.com',
            'numero_telephone' => '0123456789',
            'mot_de_passe' => 'password123'
        ];

        ob_start();
        $controller->storeFromData($malicious);
        $output = ob_get_clean();

        $this->assertNotEmpty($output, "La réponse est vide.");
        $json = json_decode($output, true);


        $this->assertIsArray($json, "La réponse n'est pas un JSON valide.");
        $this->assertArrayHasKey('message', $json, "Le message de succès est absent.");
        $this->assertEquals("Client créé", $json['message']);
    }

public function testCascadeDeleteClientDeletesPanier()
{
    $controller = new \Controllers\ClientController();

    $data = [
        'nom' => 'CascadeTest',
        'prenom' => 'DeleteTest',
        'email' => 'cascade@test.com',
        'numero_telephone' => '0000000000',
        'mot_de_passe' => 'Secure123!'
    ];

    ob_start();
    $controller->storeFromData($data);
    $output = ob_get_clean();
    $response = json_decode($output, true);
    $idClient = $response['id_client'] ?? null;

    $this->assertNotNull($idClient);

    $pdo = new \Core\Database();
    $conn = $pdo->getConnection();

    $conn->prepare("INSERT INTO panier (id_client) VALUES (:id_client)")
         ->execute(['id_client' => $idClient]);

    $count = $conn->prepare("SELECT COUNT(*) FROM panier WHERE id_client = ?");
    $count->execute([$idClient]);
    $this->assertEquals(1, $count->fetchColumn());

    ob_start();
    $controller->destroy($idClient);
    ob_end_clean();

    $count->execute([$idClient]);
    $this->assertEquals(0, $count->fetchColumn());
}

}



