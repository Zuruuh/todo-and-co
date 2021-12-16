<?php

namespace App\Tests\Service;

use App\Service\SecurityService,
    Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @group unit
 * @group service
 */
class SecurityServiceTest extends KernelTestCase
{
    private ?SecurityService $securityService;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->securityService = self::getContainer()->get(SecurityService::class);
    }

    public function testContructor(): void
    {
        // Only testing constructor, dummy assertion here for filling
        $this->assertNotNull($this->securityService);
    }

    protected function tearDown(): void
    {
        $this->securityService = null;
    }
}
