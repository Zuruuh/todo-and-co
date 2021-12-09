<?php

namespace App\Tests\Trait;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use App\Service\TraitService;
use LogicException;
use App\Entity\Task;
use App\Entity\User;
use App\Form\TaskType;
use App\Form\UserType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ServiceTraitTest extends KernelTestCase
{
    private ?TraitService $serviceTrait;

    public const HOME_ROUTE  = 'homepage';
    public const LOGIN_ROUTE = 'security_login';

    public const HOME_TEMPLATE = 'home/index.html.twig';

    public const FLASH_MESSAGE = 'New message';

    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $this->serviceTrait = $container->get(TraitService::class);
        $this->router = $container->get(UrlGeneratorInterface::class);
    }

    public function testRedirection(): void
    {
        $redirectedToHomepage = $this->serviceTrait->redirect(self::HOME_ROUTE);
        $homeRoute = $this->router->generate(self::HOME_ROUTE);
        $this->assertSame($homeRoute, $redirectedToHomepage->getTargetUrl());
        $this->assertSame(302, $redirectedToHomepage->getStatusCode());


        $redirectedToLoginpage = $this->serviceTrait->redirect(self::LOGIN_ROUTE);
        $loginRoute = $this->router->generate(self::LOGIN_ROUTE);
        $this->assertSame($loginRoute, $redirectedToLoginpage->getTargetUrl());
        $this->assertSame(302, $redirectedToLoginpage->getStatusCode());
    }

    public function testRendering(): void
    {
        $response = $this->serviceTrait->render(self::HOME_TEMPLATE);
        $page = $response->getContent();

        $this->assertIsString($page);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('<html', $page);
    }

    public function testEntityClassDefinition(): void
    {
        try {
            $this->serviceTrait->isEntityClassDefined();
        } catch (LogicException $e) {
            $this->assertInstanceOf(LogicException::class, $e);
        }

        $userClass = $this->serviceTrait->isEntityClassDefined(User::class);
        $this->assertSame(User::class, $userClass);

        $this->serviceTrait->ENTITY_CLASS = User::class;
        $userClass = $this->serviceTrait->isEntityClassDefined();
        $this->assertSame(User::class, $userClass);

        $taskClass = $this->serviceTrait->isEntityClassDefined(Task::class);
        $this->assertSame(Task::class, $taskClass);
    }

    public function testFormTypeClassDefinition(): void
    {
        try {
            $this->serviceTrait->isFormTypeClassDefined();
        } catch (LogicException $e) {
            $this->assertInstanceOf(LogicException::class, $e);
        }

        $userFormTypeClass = $this->serviceTrait->isFormTypeClassDefined(UserType::class);
        $this->assertSame(UserType::class, $userFormTypeClass);

        $this->serviceTrait->FORM_TYPE_CLASS = UserType::class;
        $userFormTypeClass = $this->serviceTrait->isFormTypeClassDefined();
        $this->assertSame(UserType::class, $userFormTypeClass);

        $taskFormTypeClass = $this->serviceTrait->isFormTypeClassDefined(TaskType::class);
        $this->assertSame(TaskType::class, $taskFormTypeClass);
    }

    public function testFormGeneration(): void
    {
        $this->serviceTrait->ENTITY_CLASS = User::class;
        $this->serviceTrait->FORM_TYPE_CLASS = UserType::class;

        $form = $this->serviceTrait->generateForm(new Request());
        $this->assertInstanceOf(FormInterface::class, $form->form);
        $this->assertInstanceOf(User::class, $form->entity);
    }

    protected function tearDown(): void
    {
        $this->serviceTrait = null;
    }
}
