<?php

namespace App\Tests\Service;

use App\Service\HomeService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Response;

class HomeServiceTest extends KernelTestCase
{
    private ?HomeService $homeService;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->homeService = static::getContainer()->get(HomeService::class);
    }

    public function testHomePageAction(): void
    {
        $response = $this->homeService->homeAction();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->homeService = null;
    }
}
