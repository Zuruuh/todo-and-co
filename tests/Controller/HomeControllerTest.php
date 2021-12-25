<?php

namespace App\Tests\Controller;

use App\Tests\Helper\TestCase\MinkTestCase,
    App\Tests\Helper\Attribute\ScreenshotFolder;

/**
 * @group e2e
 */
#[ScreenshotFolder('home')]
class HomeControllerTest extends MinkTestCase
{
    public function testHomepageRendering(): void
    {
        $authSess = $this->visit(authenticated: true);
        $anonSess = $this->visit();

        $this->screenshotSession($anonSess, 'homepage-anonymous');
        $this->screenshotSession($authSess, 'homepage-authenticated');

        $this->assertSame(200, $anonSess->getStatusCode());
        $this->assertSame(200, $authSess->getStatusCode());

        $anonPage = $anonSess->getPage();
        $authPage = $authSess->getPage();

        $this->assertTrue($authPage->has('css', '#auth-logout'));

        $this->assertTrue($anonPage->has('css', '#auth-login'));
        $this->assertTrue($anonPage->has('css', '#user-create'));
        $this->assertTrue($anonPage->has('css', '#tasks-create-button'));
        $this->assertTrue($anonPage->has('css', '#tasks-todo-button'));
        $this->assertTrue($anonPage->has('css', '#tasks-done-button'));
    }

    public function testHomepageLinks(): void
    {
        $authSess = $this->visit(authenticated: true);
        $anonSess = $this->visit();

        $this->assertSame(200, $authSess->getStatusCode());
        $this->assertSame(200, $anonSess->getStatusCode());

        $page = $anonSess->getPage();

        $links = [
            [
                'id' => 'navbar-brand',
                'link' => '/'
            ], [
                'id' => 'auth-login',
                'link' => '/login'
            ], [
                'id' => 'user-create',
                'link' => '/users/create'
            ], [
                'id' => 'tasks-create-button',
                'link' => '/tasks/create'
            ], [
                'id' => 'tasks-todo-button',
                'link' => '/tasks/list/todo'
            ], [
                'id' => 'tasks-done-button',
                'link' => '/tasks/list/done'
            ]
        ];

        foreach ($links as $link) {
            $pageLink = $page->find('css', '#' . $link['id']);

            $this->assertTrue($pageLink->hasAttribute('href'));
            $this->assertSame($link['link'], $pageLink->getAttribute('href'));
        }

        $logoutlink = $authSess->getPage()->find('css', '#auth-logout');

        $this->assertTrue($logoutlink->hasAttribute('href'));
        $this->assertSame('/logout', $logoutlink->getAttribute('href'));
    }

    public function testImageRendering(): void
    {
        $session = $this->visit('/');

        $script = <<<JS
        const testImageRendering = () => {

            const image = document.querySelector('#header-image');
            const { width, height, src, alt } = image;

            var http = new XMLHttpRequest();
            http.open('HEAD', src, false);
            http.send();

            return { width, height, status: http.status, alt };
        }
        
        testImageRendering();
        JS;

        $imageData = (object) $session->evaluateScript($script);

        $this->assertSame(200, $imageData->status);
        $this->assertNotNull($imageData->alt);

        $this->assertGreaterThanOrEqual(100, $imageData->width);
        $this->assertGreaterThanOrEqual(100, $imageData->height);
    }
}
