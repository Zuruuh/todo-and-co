<?php

namespace App\Tests\Service;

use LogicException,
    App\Entity\Task,
    App\Entity\User,
    App\Form\TaskType,
    App\Form\UserType,
    App\Service\UtilsService,
    Symfony\Component\Form\FormInterface,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Bundle\FrameworkBundle\Test\KernelTestCase,
    Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @group unit
 * @group service
 */
class UtilsServiceTest extends KernelTestCase
{
    private ?UtilsService $utilsService;

    public const HOME_ROUTE  = 'homepage';
    public const LOGIN_ROUTE = 'security_login';

    public const HOME_TEMPLATE = 'home/index.html.twig';

    public const FLASH_MESSAGE = 'New message';

    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $this->utilsService = $container->get(UtilsService::class);
        $this->router = $container->get(UrlGeneratorInterface::class);
    }

    public function testRedirection(): void
    {
        $redirectedToHomepage = $this->utilsService->redirect(self::HOME_ROUTE);
        $homeRoute = $this->router->generate(self::HOME_ROUTE);
        $this->assertSame($homeRoute, $redirectedToHomepage->getTargetUrl());
        $this->assertSame(302, $redirectedToHomepage->getStatusCode());


        $redirectedToLoginpage = $this->utilsService->redirect(self::LOGIN_ROUTE);
        $loginRoute = $this->router->generate(self::LOGIN_ROUTE);
        $this->assertSame($loginRoute, $redirectedToLoginpage->getTargetUrl());
        $this->assertSame(302, $redirectedToLoginpage->getStatusCode());
    }

    public function testRendering(): void
    {
        $response = $this->utilsService->render(self::HOME_TEMPLATE);
        $page = $response->getContent();

        $this->assertIsString($page);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('<html', $page);
    }

    public function testEntityClassDefinition(): void
    {
        try {
            $this->utilsService->isEntityClassDefined();
        } catch (LogicException $e) {
            $this->assertInstanceOf(LogicException::class, $e);
        }

        $userClass = $this->utilsService->isEntityClassDefined(User::class);
        $this->assertSame(User::class, $userClass);

        $this->utilsService->setupFormDefaults(entityClass: User::class);
        $userClass = $this->utilsService->isEntityClassDefined();
        $this->assertSame(User::class, $userClass);

        $taskClass = $this->utilsService->isEntityClassDefined(Task::class);
        $this->assertSame(Task::class, $taskClass);
    }

    public function testFormTypeClassDefinition(): void
    {
        try {
            $this->utilsService->isFormTypeClassDefined();
        } catch (LogicException $e) {
            $this->assertInstanceOf(LogicException::class, $e);
        }

        $userFormTypeClass = $this->utilsService->isFormTypeClassDefined(UserType::class);
        $this->assertSame(UserType::class, $userFormTypeClass);

        $this->utilsService->setupFormDefaults(formTypeClass: UserType::class);
        $userFormTypeClass = $this->utilsService->isFormTypeClassDefined();
        $this->assertSame(UserType::class, $userFormTypeClass);

        $taskFormTypeClass = $this->utilsService->isFormTypeClassDefined(TaskType::class);
        $this->assertSame(TaskType::class, $taskFormTypeClass);
    }

    public function testFormGeneration(): void
    {
        $this->utilsService->setupFormDefaults(UserType::class, User::class);

        $form = $this->utilsService->generateForm(new Request());
        $this->assertInstanceOf(FormInterface::class, $form->form);
        $this->assertInstanceOf(User::class, $form->entity);
    }

    protected function tearDown(): void
    {
        $this->utilsService = null;
    }
}
