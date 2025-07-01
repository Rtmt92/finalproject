<?php
use PHPUnit\Framework\TestCase;
use Controllers\ProduitController;
use Src\Models\Produit;
use Src\Models\ProduitImage;
use Src\Models\Image;

class ProduitControllerTest extends TestCase
{
    public function testGetProduitByIdReturnsProduit(){
        $produitMock = $this->createMock(\Src\Models\Produit::class);
        $produitMock->method('getById')->willReturn([
            'id_produit'   => 1,
            'nom_produit'  => 'Produit Test',
            'description'  => 'Un produit',
            'prix'         => 9.99
        ]);

        $prodImageMock = $this->createMock(\Src\Models\ProduitImage::class);
        $prodImageMock->method('getImagesByProduitId')->willReturn([
            ['id_image' => 123]
        ]);

        $imageMock = $this->createMock(\Src\Models\Image::class);
        $imageMock->method('getById')->willReturn([
            'id_image' => 123,
            'lien'     => 'http://localhost/test.jpg'
        ]);

        $controller = new \Controllers\ProduitController();
        $this->setPrivateProperty($controller, 'produitModel', $produitMock);
        $this->setPrivateProperty($controller, 'prodImageModel', $prodImageMock);
        $this->setPrivateProperty($controller, 'imageModel', $imageMock);

        ob_start();
        $controller->show(1);
        $output = ob_get_clean();

        $data = json_decode($output, true);

        $this->assertIsArray($data);
        $this->assertEquals('Produit Test', $data['nom_produit']);
        $this->assertEquals('http://localhost/test.jpg', $data['images'][0]['lien']);
    }

    private function setPrivateProperty($object, string $propertyName, $value): void
    {
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }

}
