<?php

namespace App\Tests\Controller;

use App\Tests\Helper\TestCase\MinkTestCase,
    App\Tests\Helper\Attribute\ScreenshotFolder;

/**
 * @group e2e
 */
#[ScreenshotFolder('security')]
class SecurityControllerTest extends MinkTestCase
{
    private const WRONG_PASSWORD = 'passwd';
    private const WRONG_USERNAME = 'admin_name';

    private const INVALID_CREDENTIALS = 'Invalid credentials.';

    #[ScreenshotFolder('login')]
    public function testValidLogin(): void
    {
        $session = $this->visit('/login');
        $page = $session->getPage();

        $usernameField = $page->findById('username');
        $passwordField = $page->findById('password');
        $submitButton = $page->findById('submit-login-form');
        $loginForm = $page->findById('login-form');

        $this->assertNotContains(null, [$usernameField, $passwordField, $submitButton, $loginForm]);

        $usernameField->setValue(self::DEV_ADMIN_USERNAME);
        $passwordField->setValue(self::DEV_ADMIN_PASSWORD);

        $this->screenshotSession($session, 'login-form-full');

        $submitButton->press();

        $this->visit('/', $session);
        $this->screenshotSession($session, 'should-be-logged-in');
    }

    #[ScreenshotFolder('invalid-login')]
    public function testInvalidCredentialsLogin(): void
    {
        $credentials = [
            [
                'username' => self::WRONG_USERNAME,
                'password' => self::WRONG_PASSWORD
            ],
            [
                'username' => self::WRONG_USERNAME,
                'password' => self::DEV_ADMIN_PASSWORD
            ],
            [
                'username' => self::DEV_ADMIN_USERNAME,
                'password' => self::WRONG_PASSWORD
            ]
        ];

        foreach ($credentials as $credential) {
            $session = $this->visit('/login');
            $page = $session->getPage();

            $username = $credential['username'];
            $password = $credential['password'];

            $usernameField = $page->findById('username');
            $passwordField = $page->findById('password');
            $loginForm = $page->findById('login-form');

            $usernameField->setValue($username);
            $passwordField->setValue($password);

            $loginForm->submit();
            $page = $session->getPage();

            $alertBox = $page->find('css', 'div.alert.alert-danger');
            $this->screenshotSession(
                $session,
                sprintf('%s:%s:%s', 'should-display-invalid-message', $username, $password)
            );

            $this->assertNotNull($alertBox);
            $this->assertSame(self::INVALID_CREDENTIALS, $alertBox->getText());
        }
    }

    #[ScreenshotFolder('logout')]
    public function testLogout(): void
    {
        $session = $this->visit(authenticated: true);
        $this->assertTrue($session->getPage()->has('css', '#auth-logout'));
        $this->screenshotSession($session, 'should-be-logged-in');

        $this->visit('/logout', $session);
        $this->assertSame(self::APP_URL . '/', $session->getCurrentUrl());
        $this->screenshotSession($session, 'should-not-be-logged-in');
    }

    #[ScreenshotFolder('redirection')]
    public function testAuthRedirection(): void
    {
        $session = $this->visit('/users/create');
        $this->assertSame($this->getUrlForRoute('/login'), $session->getCurrentUrl());
        $this->screenshotSession($session, 'redirected-to-login-page');

        $this->visit(session: $session);
        $session->reload();
        $this->screenshotSession($session, 'back-to-homepage');

        $this->loginSession($session, admin: false);
        $session->reload();
        $this->visit('/users/create', $session);

        $this->screenshotSession($session, 'not-allowed-403');
        $this->assertSame(403, $session->getStatusCode());

        $this->visit('/logout', $session);

        $this->loginSession($session, admin: true);
        $session->reload();
        $this->visit('/users/create', $session);

        $this->screenshotSession($session, 'should-access-page');
        $this->assertSame(200, $session->getStatusCode());
        $this->assertSame(self::APP_URL . '/users/create', $session->getCurrentUrl());
    }
}
