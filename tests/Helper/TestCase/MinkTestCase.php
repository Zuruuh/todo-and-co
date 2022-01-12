<?php

namespace App\Tests\Helper\TestCase;

use LogicException,
    App\Tests\Helper\ScreenshotFolder,
    Symfony\Bundle\FrameworkBundle\Test\KernelTestCase,
    Behat\Mink\Mink,
    Behat\Mink\Session,
    DMore\ChromeDriver\ChromeDriver,
    ReflectionClass;

abstract class MinkTestCase extends KernelTestCase implements MinkTestCaseInterface
{
    protected ?Mink    $mink;
    protected ?Session $authenticatedSession;
    protected ?Session $anonymousSession;


    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
    }

    /**
     * Default setUp method.
     * Override by creating a new one in child class.
     * Don't forget to call the setUpMink method in it.
     */
    protected function setUp(): void
    {
        $this->setUpMink();
    }

    /**
     * Creates a new instance of Mink with associated drivers & sessions.
     */
    protected function setUpMink(): void
    {
        self::bootKernel();
        $authenticatedSession = new Session(new ChromeDriver(self::CHROME_URL, base_url: ''));
        $anonymousSession = new Session(new ChromeDriver(self::CHROME_URL, base_url: ''));

        $mink = new Mink([
            'authenticatedSession' => $authenticatedSession,
            'anonymousSession' => $anonymousSession
        ]);
        $mink->setDefaultSessionName('authenticatedSession');
        $sessions = [$authenticatedSession, $anonymousSession];
        foreach ($sessions as $session) {
            if ($session->isStarted()) {
                /**
                 * Resetting session here as if a test crashes, the tearDown
                 * method will not be called and the session will stay 
                 * open until next test, which can cause multiple problems.
                 */
                $session->reset();
            }
        }

        $authenticatedSession->start();
        $anonymousSession->start();


        $this->authenticatedSession = $authenticatedSession;
        $this->anonymousSession = $anonymousSession;

        $authenticatedSession = $this->loginSession($authenticatedSession, admin: false);

        $this->mink = $mink;
    }

    /**
     * Make mink's default session visit a local route.
     *
     * @param string   $url           The local route to visit.
     * @param ?Session $session       The session to use. Provide none if you don't have one yet in your test method.
     * @param bool     $authenticated If no session is passed, return an authenticated one (Not Admin).
     *
     * @return Session
     */
    protected function visit(string $route = '/', ?Session $session = null, bool $authenticated = false): Session
    {
        $session = $session
            ?? ($authenticated
                ? $this->authenticatedSession
                : $this->anonymousSession
            );
        $session->getDriver()->visit($this->getUrlForRoute($route));
        $session->getDriver()->wait(2500, 'document.readyState === "complete"');

        return $session;
    }

    /**
     * Returns an url for given route.
     *
     * @param string $route The route to generate an url with.
     *
     * @return string
     */
    protected function getUrlForRoute(string $route = '/'): string
    {
        return self::APP_URL . $route;
    }

    /**
     * Creates a new screenshot of browser windows & saves it.
     *
     * @param Session $session         The browser session to save.
     * @param string  $fileName        The name of the saved image. Defaults to current timestamp.
     * @param bool    $useFunctionName If true, use the function name as the file name's prefix.
     * 
     * @return bool
     */
    protected function screenshotSession(Session $session, ?string $fileName = null, bool $useFunctionName = false): bool
    {
        $functionName = $this->getLastFunctionCall();
        $reflection = new ReflectionClass(get_class($this));

        $classAttributes = $reflection->getAttributes();
        $methodAttribute = $reflection->getMethod($functionName)->getAttributes();

        if (
            !array_key_exists(0, $classAttributes)
        ) {
            throw new LogicException(
                sprintf(
                    'Cannot use %s attribute on "%s" method when not used on class "%s"',
                    ScreenshotFolder::class,
                    $functionName,
                    get_class($this)
                )
            );
        }

        $folder  = trim($classAttributes[0]->newInstance()->path, "/\t\n\r\0\x0B");
        $subFolder = array_key_exists(0, $methodAttribute)
            ? trim($methodAttribute[0]->newInstance()->path, '/')
            : '.';
        $fileName = $fileName ?? time();
        $fileName = $useFunctionName ? sprintf('%s-%s', $functionName, $fileName) : $fileName;

        $path = sprintf('%s/%s/%s/', self::SCREENSHOTS_DIR, $folder, $subFolder);
        if (!file_exists($path)) {
            mkdir($path, recursive: true);
        }
        $path = realpath($path);

        try {
            $screenshot = $session->getScreenshot();
            $file = sprintf('%s/%s.png', $path, $fileName);
            if ($screenshot) {
                file_put_contents($file, $screenshot);
            } else {
                fwrite(STDERR, sprintf('Could not write file %s', $file));
            }

            return boolval($screenshot);
        } catch (\Exception $_) {
            // fwrite(STDERR, print_r($_, true));

            return false;
        }

        return true;
    }

    /**
     * Logins a session using default or custom credentials.
     *
     * @param Session $session  The session to login.
     * @param ?string $username The username to login with. 
     * @param ?string $password The password to login with.
     * @param bool    $admin    If no credentials are passed, use admin credentials.
     *
     * @return Session
     */
    protected function loginSession(
        Session $session,
        ?string $username = null,
        ?string $password = null,
        bool $admin = false,
    ): Session {
        $username = $username
            ?? ($admin
                ? self::DEV_ADMIN_USERNAME
                : self::DEV_USER_USERNAME);
        $password = $password
            ?? ($admin
                ? self::DEV_ADMIN_PASSWORD
                : self::DEV_USER_PASSWORD);

        $currentUrl = $session->getCurrentUrl();
        $currentUrl = $currentUrl === self::CHROME_DEFAULT_URL ? self::APP_URL : $currentUrl;

        $this->visit('/login', $session);
        $page = $session->getPage();

        $usernameField = $page->findById('username');
        $passwordField = $page->findById('password');
        $loginForm = $page->findById('login-form');

        $usernameField->setValue($username);
        $passwordField->setValue($password);

        $loginForm->submit();
        $this->getSessionCookies($session);
        $session->visit($currentUrl);

        return $session;
    }

    /**
     * Returns the last function caller
     *
     * @return string
     */
    private function getLastFunctionCall(): string
    {
        $e = new \Exception();
        $trace = $e->getTrace();
        $last_call = $trace[2]['function'];

        return $last_call;
    }

    /**
     * Gets all driver's cookies
     *
     * @return array
     */
    protected function getSessionCookies(Session $session): array
    {
        $getCookiesMethod = self::GET_DRIVERS_COOKIES;

        return $session->getDriver()->$getCookiesMethod();
    }

    /**
     * Default tearDown method.
     * Override by creating a new one in child class.
     * Don't forget to call the tearDownMink method in it.
     */
    protected function tearDown(): void
    {
        $this->tearDownMink();
    }

    /**
     * Closes mink's session.
     */
    protected function tearDownMink(): void
    {
        $this->mink->resetSessions();

        unset($this->authenticatedSession);
        unset($this->anonymousSession);
        unset($this->mink);
    }
}
