<?php

namespace App\Tests\Helper\TestCase;

interface MinkTestCaseInterface
{
    //? Name "nginx" is an arbitrary value, it represents the name of the docker nginx container
    public const APP_URL                 = 'http://nginx';

    //? Same as above, chrome is the name of the docker chrome container
    public const CHROME_DEBUGGING_URL    = 'http://chrome';
    public const CHROME_DEBUGGING_PORT   = '9222';
    public const CHROME_URL              = self::CHROME_DEBUGGING_URL . ':' . self::CHROME_DEBUGGING_PORT;
    public const CHROME_DEFAULT_URL      = 'about:blank';

    public const DEV_ADMIN_USERNAME      = 'admin';
    public const DEV_ADMIN_PASSWORD      = 'password';

    public const DEV_USER_USERNAME       = 'user';
    public const DEV_USER_PASSWORD       = 'password';

    public const GET_DRIVERS_COOKIES     = 'getCookies';

    public const SCREENSHOTS_DIR         = './var/screenshots/';
}
