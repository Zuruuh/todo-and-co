<?php

namespace App\Tests\Controller;

use App\Entity\User,
    App\Tests\Helper\TestCase\MinkTestCase,
    App\Tests\Helper\Attribute\ScreenshotFolder,
    Behat\Mink\Element\NodeElement,
    Doctrine\ORM\EntityManagerInterface,
    App\Repository\UserRepository;

/**
 * @group e2e
 */
#[ScreenshotFolder('users')]
class UserControllerTest extends MinkTestCase
{
    private ?EntityManagerInterface $em;
    private ?UserRepository $userRepository;

    private const INVALID_USERNAME    = 'admin'; // When fixtures are loaded, this username is already in use; (This is intended)
    private const INVALID_EMAIL       = 'mail';
    private const INVALID_ROLE        = 'ROLE_TESTER';

    private const VALID_USERNAME    = 'myNewUser';
    private const VALID_EMAIL       = 'my-email@gmail.com';

    private const FORM_USERNAME_FIELD = '#user_username';
    private const FORM_EMAIL_FIELD    = '#user_email';
    private const FORM_PASSWORD_FIELD = '#user_password';
    private const FORM_ROLES_FIELD    = '#user_roles';

    protected function setUp(): void
    {
        $this->setUpMink();

        $container = $this->getContainer();
        $this->em = $container->get(EntityManagerInterface::class);
        $this->userRepository = $container->get(UserRepository::class);
    }

    #[ScreenshotFolder('listing')]
    public function testUserListing(): void
    {
        $session = $this->visit('/users');

        $this->screenshotSession($session, 'not-authorized');
        $this->assertSame($this->getUrlForRoute('/login'), $session->getCurrentUrl());

        $this->loginSession($session, admin: true);
        $session->reload();

        $this->visit('/users', $session);
        $page = $session->getPage();
        $this->screenshotSession($session, 'list');

        $rows = $page->findAll('css', '#users-table > tbody > tr');
        $this->assertContainsOnlyInstancesOf(NodeElement::class, $rows);
        [$row] = $rows;

        [$idElement, $usernameElement, $emailElement, $editElement] = $row->findAll('css', 'th, td');

        $id = intval($idElement->getText());
        $this->assertIsNumeric($id);
        $this->assertGreaterThan(0, $id);

        /** @var User */
        $user = $this->em->getRepository(User::class)->findOneBy(['username' => $usernameElement->getText()]);

        $this->assertNotNull($user);
        $this->assertInstanceOf(User::class, $user);

        $this->assertSame($user->getUserIdentifier(), $usernameElement->getText());
        $this->assertSame($user->getEmail(), $emailElement->getText());

        $editLink = $editElement->find('css', 'a');

        $this->assertTrue($editLink->hasAttribute('href'));
        $this->assertSame(sprintf('/users/%d/edit', $user->getId()), $editLink->getAttribute('href'));
    }

    #[ScreenshotFolder('form')]
    public function testCreationFormGeneration(): void
    {
        $session = $this->visit('/users/create');
        $this->screenshotSession($session, 'redirected');
        $this->assertSame($this->getUrlForRoute('/login'), $session->getCurrentUrl());

        $this->loginSession($session, admin: true);
        $session->reload();

        $this->visit('/users/create', $session);
        $page = $session->getPage();
        $this->screenshotSession($session, 'empty-form');

        $usernameField = $page->find('css', self::FORM_USERNAME_FIELD);
        $emailField = $page->find('css', self::FORM_EMAIL_FIELD);
        $passwordField = $page->find('css', self::FORM_PASSWORD_FIELD);
        $rolesField = $page->find('css', self::FORM_ROLES_FIELD);

        $rolesOptions = $page->findAll('css', self::FORM_ROLES_FIELD . ' option');

        $this->assertNotContains(null, [$usernameField, $emailField, $passwordField, $rolesField]);
        $this->assertNotContains(null, $rolesOptions);

        $this->assertSame('input', $usernameField->getTagName());
        $this->assertSame('input', $emailField->getTagName());
        $this->assertSame('input', $passwordField->getTagName());
        $this->assertSame('select', $rolesField->getTagName());

        $this->assertNotContains(
            null,
            [
                $usernameField->getAttribute('type'),
                $emailField->getAttribute('type'),
                $passwordField->getAttribute('type'),
            ]
        );

        $this->assertSame('text', $usernameField->getAttribute('type'));
        $this->assertSame('email', $emailField->getAttribute('type'));
        $this->assertSame('password', $passwordField->getAttribute('type'));


        $roles = array_map(function (NodeElement $option) {
            $this->assertTrue($option->hasAttribute('value'));
            $this->assertNotSame('', $option->getText());

            return $option->getAttribute('value');
        }, $rolesOptions);

        $this->assertContains(User::USER_ROLE, $roles);
        $this->assertContains(User::ADMIN_ROLE, $roles);
    }

    #[ScreenshotFolder('form')]
    public function testCreationFormValidation(): void
    {
        $passwordMaxSize = 'a';
        for ($i = 0; $i < 4097; ++$i) { // Max allowed password size is 4096 characters
            $passwordMaxSize .= 'a';
        }
        $passwordMinSize = 'aaaaa'; // User passwords are required to be at least 6 characters

        $session = $this->visit();
        $this->loginSession($session, admin: true);
        $this->visit('/users/create');

        $testRole = self::INVALID_ROLE;
        $script = <<<JS

            const emailField = document.querySelector('#user_email');
            const userRoleOption = document.querySelector('#user_roles option');

            const role = `$testRole`;

            emailField.type = 'text';
            userRoleOption.value = role;
        JS;

        $session->executeScript($script);
        $page = $session->getPage();

        $usernameField  = $page->find('css', self::FORM_USERNAME_FIELD);
        $emailField     = $page->find('css', self::FORM_EMAIL_FIELD);
        $passwordField  = $page->find('css', self::FORM_PASSWORD_FIELD);
        $rolesField     = $page->find('css', self::FORM_ROLES_FIELD);
        $userRoleOption = $page->find('css', self::FORM_ROLES_FIELD . ' option');

        $form = $page->find('css', 'form[name="user"]');
        $submitButton = $page->find('css', 'form[name="user"] > button[type="submit"]');

        $this->assertNotContains(null, [
            $usernameField,
            $emailField,
            $passwordField,
            $rolesField,
            $userRoleOption,
            $form,
            $submitButton
        ]);

        $usernameField->setValue(self::INVALID_USERNAME);
        $emailField->setValue(self::INVALID_EMAIL);
        $passwordField->setValue($passwordMaxSize);

        $this->assertSame(self::INVALID_USERNAME, $usernameField->getValue());
        $this->assertSame(self::INVALID_EMAIL, $emailField->getValue());
        $this->assertSame($passwordMaxSize, $passwordField->getValue());

        $session->getDriver()->selectOption($rolesField->getXpath(), $userRoleOption->getValue());
        $this->screenshotSession($session, 'form-full-invalid');

        $submitButton->press();
        $this->screenshotSession($session, 'form-invalid-submitted');

        $usernameError = $page->find('css', self::FORM_USERNAME_FIELD . ' + .invalid-feedback');
        $emailError    = $page->find('css', self::FORM_EMAIL_FIELD . ' + .invalid-feedback');
        $passwordError = $page->find('css', self::FORM_PASSWORD_FIELD . ' + .invalid-feedback');
        $rolesError    = $page->find('css', self::FORM_ROLES_FIELD . ' + .invalid-feedback');

        $this->assertNotContains(null, [
            $usernameError,
            $emailError,
            $passwordError,
            $rolesError
        ]);

        $passwordField->setValue($passwordMinSize);
        $session->executeScript($script);
        $submitButton->press();
        $this->screenshotSession($session, 'password-too-short');

        $passwordError = $page->find('css', self::FORM_PASSWORD_FIELD . ' + .invalid-feedback');
    }

    #[ScreenshotFolder('creation')]
    public function testCreation(): void
    {
        $session = $this->visit();
        $this->loginSession($session, admin: true);

        $this->visit('/users/create', $session);

        // Make sure the user does not exist yet
        $shouldNotExistYet = $this->userRepository->findOneBy(['username' => self::VALID_USERNAME]);
        $this->assertNull($shouldNotExistYet);

        // Fill in the form with valid data and submit it
        $page = $session->getPage();

        $usernameField = $page->find('css', self::FORM_USERNAME_FIELD);
        $emailField = $page->find('css', self::FORM_EMAIL_FIELD);
        $passwordField = $page->find('css', self::FORM_PASSWORD_FIELD);

        $form = $page->find('css', 'form[name="user"]');
        $submitButton = $page->find('css', 'form[name="user"] > button[type="submit"]');

        $this->assertNotContains(null, [
            $usernameField,
            $emailField,
            $passwordField,
            $form,
            $submitButton
        ]);

        $usernameField->setValue(self::VALID_USERNAME);
        $emailField->setValue(self::VALID_EMAIL);
        $passwordField->setValue(self::VALID_USERNAME . '123456');

        $submitButton->press();
        $this->screenshotSession($session, 'creation-submitted');

        // Make sure the user was created
        $shouldExistNow = $this->userRepository->findOneBy(['username' => self::VALID_USERNAME]);
        $this->assertNotNull($shouldExistNow);
        $this->assertInstanceOf(User::class, $shouldExistNow);
    }

    #[ScreenshotFolder('update')]
    public function testEdition(): void
    {
        $session = $this->visit();
        $this->loginSession($session, admin: true);

        $user = $this->userRepository->findOneBy(['username' => self::VALID_USERNAME]);
        $this->assertNotNull($user);
        $this->assertInstanceOf(User::class, $user);

        $this->visit(
            '/users/' . $user->getId() . '/edit',
            $session
        );

        // Fill in the form with valid data and submit it
        $session->getDriver()->wait(1000, 'false');
        $page = $session->getPage();
        $this->screenshotSession($session, 'edition-form');

        $usernameField = $page->find('css', self::FORM_USERNAME_FIELD);
        $emailField = $page->find('css', self::FORM_EMAIL_FIELD);
        $passwordField = $page->find('css', self::FORM_PASSWORD_FIELD);

        $form = $page->find('css', 'form[name="user"]');
        $submitButton = $page->find('css', 'form[name="user"] > button[type="submit"]');

        $this->assertNotContains(null, [
            $usernameField,
            $emailField,
            $form,
            $submitButton
        ]);

        $this->assertNull($passwordField);
        $this->assertSame(self::VALID_USERNAME, $usernameField->getValue());
        $this->assertSame(self::VALID_EMAIL, $emailField->getValue());

        $usernameField->setValue('updated-' . self::VALID_USERNAME);
        $emailField->setValue('updated-' . self::VALID_EMAIL);

        $this->screenshotSession($session, 'edition-filled');
        $submitButton->press();
        $this->screenshotSession($session, 'edition-submitted');

        // Make sure the user was updated
        $this->em->refresh($user);
        $this->assertNotNull($user);
        $this->assertInstanceOf(User::class, $user);

        $this->assertSame('updated-' . self::VALID_USERNAME, $user->getUserIdentifier());
        $this->assertSame('updated-' . self::VALID_EMAIL, $user->getEmail());
    }

    protected function tearDown(): void
    {
        $this->tearDownMink();
        $this->em->close();

        unset($this->userRepository);
        unset($this->em);
    }
}
