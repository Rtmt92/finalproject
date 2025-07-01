<?php
use PHPUnit\Framework\TestCase;

class SecurityTest extends TestCase
{
    public function testAccessMeWithoutToken()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/api/me';
        unset($_SERVER['HTTP_AUTHORIZATION']);

        ob_start();
        include __DIR__ . '/../index.php';
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Token manquant', $response['error']);
    }

    public function testAccessPanierWithoutToken()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/panier';
        unset($_SERVER['HTTP_AUTHORIZATION']);

        ob_start();
        include __DIR__ . '/../index.php';
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Token manquant', $response['error']);
    }

    public function testAccessPanierWithInvalidToken()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/panier';
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer FAUX.TOKEN.JWT';

        ob_start();
        include __DIR__ . '/../index.php';
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('Token invalide', $response['error']);
    }
}
