<?php

namespace App\Tests\Controller;

use App\Entity\Task,
    App\Repository\TaskRepository,
    App\Tests\Helper\Attribute\ScreenshotFolder,
    App\Tests\Helper\TestCase\MinkTestCase,
    Doctrine\ORM\EntityManagerInterface,
    Behat\Mink\Element\NodeElement,
    App\Entity\User,
    App\Repository\UserRepository;

/**
 * @group e2e
 */
#[ScreenshotFolder('tasks')]
class TaskControllerTest extends MinkTestCase
{
    private ?EntityManagerInterface $em;
    private ?TaskRepository         $taskRepository;
    private ?UserRepository         $userRepository;

    private const FORM_TITLE_FIELD = '#task_title';
    private const FORM_CONTENT_FIELD = '#task_content';

    protected function setUp(): void
    {
        $this->setUpMink();

        $container = $this->getContainer();
        $this->em = $container->get(EntityManagerInterface::class);
        $this->taskRepository = $container->get(TaskRepository::class);
        $this->userRepository = $container->get(UserRepository::class);
    }

    #[ScreenshotFolder('list')]
    public function testTasksTodoListing(): void
    {
        $session = $this->visit('/tasks/list/todo', authenticated: true);
        $this->screenshotSession($session, 'list-todo');

        $page = $session->getPage();

        // Check that the container #tasks-list contains tasks
        $container = $page->find('css', '#tasks-list');
        $this->assertNotNull($container);

        $tasks = $container->findAll('css', 'div.task');

        $this->assertNotContains(null, $tasks);
        $this->assertContainsOnlyInstancesOf(NodeElement::class, $tasks);
        [$task] = $tasks;

        $this->assertTrue($task->hasClass('task'));
        $this->assertTrue($task->hasClass('task-todo'));
        $this->assertTrue($task->hasAttribute('data-task-id'));

        $taskId = $task->getAttribute('data-task-id');
        $taskInDatabase = $this->taskRepository->findOneBy(['id' => $taskId]);

        $this->assertInstanceOf(Task::class, $taskInDatabase);
        $this->assertFalse($taskInDatabase->getIsDone());

        [$toggleForm, $deleteForm] = $task->findAll('css', '.actions form');
        $this->assertTrue($toggleForm->hasAttribute('action'));
        $this->assertTrue($deleteForm->hasAttribute('action'));

        $this->assertSame(sprintf('/tasks/%s/toggle', $taskId), $toggleForm->getAttribute('action'));
        $this->assertSame(sprintf('/tasks/%s/delete', $taskId), $deleteForm->getAttribute('action'));

        $editLink = $task->find('css', 'a.edit-task');
        $this->assertTrue($editLink->hasAttribute('href'));

        $this->assertSame(sprintf('/tasks/%s/edit', $taskId), $editLink->getAttribute('href'));
    }


    #[ScreenshotFolder('list')]
    public function testTasksDoneListing(): void
    {
        $session = $this->visit('/tasks/list/done', authenticated: true);
        $this->screenshotSession($session, 'list-done');

        $page = $session->getPage();

        // Check that the container #tasks-list contains tasks
        $container = $page->find('css', '#tasks-list');
        $this->assertNotNull($container);

        $tasks = $container->findAll('css', 'div.task');

        $this->assertNotContains(null, $tasks);
        $this->assertContainsOnlyInstancesOf(NodeElement::class, $tasks);
        [$task] = $tasks;

        $this->assertTrue($task->hasClass('task'));
        $this->assertTrue($task->hasClass('task-done'));
        $this->assertTrue($task->hasAttribute('data-task-id'));

        $taskId = $task->getAttribute('data-task-id');
        $taskInDatabase = $this->taskRepository->findOneBy(['id' => $taskId]);

        $this->assertInstanceOf(Task::class, $taskInDatabase);
        $this->assertTrue($taskInDatabase->getIsDone());

        [$toggleForm, $deleteForm] = $task->findAll('css', '.actions form');
        $this->assertTrue($toggleForm->hasAttribute('action'));
        $this->assertTrue($deleteForm->hasAttribute('action'));

        $this->assertSame(sprintf('/tasks/%s/toggle', $taskId), $toggleForm->getAttribute('action'));
        $this->assertSame(sprintf('/tasks/%s/delete', $taskId), $deleteForm->getAttribute('action'));

        $editLink = $task->find('css', 'a.edit-task');
        $this->assertTrue($editLink->hasAttribute('href'));

        $this->assertSame(sprintf('/tasks/%s/edit', $taskId), $editLink->getAttribute('href'));
    }

    #[ScreenshotFolder('list')]
    public function testTasksListing(): void
    {
        $session = $this->visit('/tasks/list', authenticated: true);
        $this->screenshotSession($session, 'list-all');

        $page = $session->getPage();

        // Check that the container #tasks-list contains tasks
        $container = $page->find('css', '#tasks-list');
        $this->assertNotNull($container);

        $tasks = $container->findAll('css', 'div.task');

        $this->assertNotContains(null, $tasks);
        $this->assertContainsOnlyInstancesOf(NodeElement::class, $tasks);
        [$task] = $tasks;

        $this->assertTrue($task->hasClass('task'));
        $this->assertTrue($task->hasClass('task-done') || $task->hasClass('task-todo'));
        $this->assertTrue($task->hasAttribute('data-task-id'));

        $taskId = $task->getAttribute('data-task-id');
        $taskInDatabase = $this->taskRepository->findOneBy(['id' => $taskId]);

        $this->assertInstanceOf(Task::class, $taskInDatabase);

        [$toggleForm, $deleteForm] = $task->findAll('css', '.actions form');
        $this->assertTrue($toggleForm->hasAttribute('action'));
        $this->assertTrue($deleteForm->hasAttribute('action'));

        $this->assertSame(sprintf('/tasks/%s/toggle', $taskId), $toggleForm->getAttribute('action'));
        $this->assertSame(sprintf('/tasks/%s/delete', $taskId), $deleteForm->getAttribute('action'));

        $editLink = $task->find('css', 'a.edit-task');
        $this->assertTrue($editLink->hasAttribute('href'));

        $this->assertSame(sprintf('/tasks/%s/edit', $taskId), $editLink->getAttribute('href'));
    }

    #[ScreenshotFolder('form')]
    public function testFormValidation(): void
    {
        $session = $this->visit('/tasks/create', authenticated: true);
        $this->screenshotSession($session, 'list-all');

        $page = $session->getPage();
        // Get the form and submit it with invalid data
        $form = $page->find('css', 'form[name="task"]');
        $titleField = $page->find('css', self::FORM_TITLE_FIELD);
        $contentField = $page->find('css', self::FORM_CONTENT_FIELD);
        $submitButton = $page->find('css', 'form[name="task"] > button[type="submit"]');

        $contentMaxSize = 'a';
        for ($i = 0; $i < Task::CONTENT_MAX_LENGTH; ++$i) {
            $contentMaxSize .= 'a';
        }

        $titleMaxSize = 'a';
        for ($i = 0; $i < Task::TITLE_MAX_LENGTH; ++$i) {
            $titleMaxSize .= 'a';
        }
        $titleMinSize = 'aa';

        $this->assertNotContains(null, [
            $form,
            $titleField,
            $contentField,
            $submitButton,
        ]);

        $script = <<<JS
        const removePatterns = () => {
            const patterns = document.querySelectorAll('[pattern]');
            const lengthMaxed = document.querySelectorAll('[maxlength]');

            patterns.forEach((pattern) => {
                pattern.removeAttribute('pattern');
            });

            lengthMaxed.forEach((maxed) => {
                maxed.removeAttribute('maxlength');
            });

        }; removePatterns();
        JS;
        $session->executeScript($script);

        $titleField->setValue($titleMaxSize);
        $contentField->setValue($contentMaxSize);

        $this->screenshotSession($session, 'form-invalid');
        $submitButton->press();
        $this->screenshotSession($session, 'form-invalid-submitted');

        $titleError = $page->find('css', self::FORM_TITLE_FIELD . ' + .invalid-feedback');
        $contentError = $page->find('css', self::FORM_CONTENT_FIELD . ' + .invalid-feedback');

        $this->assertNotContains(null, [
            $titleError,
            $contentError,
        ]);

        $session->executeScript($script);

        $titleField->setValue($titleMinSize);
        $contentField->setValue('value');

        $submitButton->press();

        $titleError = $page->find('css', self::FORM_TITLE_FIELD . ' + .invalid-feedback');
        $this->assertNotNull($titleError);
    }

    #[ScreenshotFolder('creation')]
    public function testTaskCreation(): void
    {
        $session = $this->visit('/tasks/create', authenticated: true);
        $page = $session->getPage();
        $this->screenshotSession($session, 'form-empty');

        $TITLE = uniqid('A new task title');
        $CONTENT = uniqid('A new task content');

        $shouldNotExistYet = $this->taskRepository->findOneBy(['title' => $TITLE]);
        $this->assertNull($shouldNotExistYet);

        $form = $page->find('css', 'form[name="task"]');
        $titleField = $page->find('css', self::FORM_TITLE_FIELD);
        $contentField = $page->find('css', self::FORM_CONTENT_FIELD);
        $submitButton = $page->find('css', 'form[name="task"] > button[type="submit"]');

        $this->assertNotContains(null, [
            $form,
            $titleField,
            $contentField,
            $submitButton,
        ]);

        $titleField->setValue($TITLE);
        $contentField->setValue($CONTENT);

        $this->screenshotSession($session, 'form-filled');
        $submitButton->press();
        $this->screenshotSession($session, 'form-submitted');

        $shouldExistNow = $this->taskRepository->findOneBy(['title' => $TITLE]);

        $this->assertInstanceOf(Task::class, $shouldExistNow);
        $this->assertSame($TITLE, $shouldExistNow->getTitle());
        $this->assertSame($CONTENT, $shouldExistNow->getContent());
        $this->assertFalse($shouldExistNow->getIsDone());
    }

    #[ScreenshotFolder('edition')]
    public function testTaskEdition(): void
    {
        $TITLE = 'A task title to edit';
        $CONTENT = 'A task content to edit';

        $task = (new Task())
            ->setTitle($TITLE)
            ->setContent($CONTENT)
            ->setIsDone(false);
        $this->em->persist($task);
        $this->em->flush();

        $session = $this->visit(sprintf('/tasks/%s/edit', $task->getId()), authenticated: true);
        $page = $session->getPage();
        $this->screenshotSession($session, 'form-default');

        $NEW_TITLE = uniqid('A new task title');
        $NEW_CONTENT = uniqid('A new task content');

        $form = $page->find('css', 'form[name="task"]');
        $titleField = $page->find('css', self::FORM_TITLE_FIELD);
        $contentField = $page->find('css', self::FORM_CONTENT_FIELD);
        $submitButton = $page->find('css', 'form[name="task"] > button[type="submit"]');

        $this->assertNotContains(null, [
            $form,
            $titleField,
            $contentField,
            $submitButton,
        ]);

        $this->assertSame($TITLE, $titleField->getValue());
        $this->assertSame($CONTENT, $contentField->getValue());

        $titleField->setValue($NEW_TITLE);
        $contentField->setValue($NEW_CONTENT);

        $this->screenshotSession($session, 'form-filled');
        $submitButton->press();
        $this->screenshotSession($session, 'form-submitted');

        $this->em->refresh($task);

        $this->assertInstanceOf(Task::class, $task);
        $this->assertSame($NEW_TITLE, $task->getTitle());
        $this->assertSame($NEW_CONTENT, $task->getContent());
        $this->assertFalse($task->getIsDone());
    }

    /**
     * * Delete anon task as admin            | True
     * * Delete anon task as non-admin        | False
     * * Delete user task as admin            | False
     * * Delete user task as non-admin        | False
     * * Delete user task as non-admin author | True
     * * Delete user task as admin author     | True
     */

    #[ScreenshotFolder('deletion')]
    public function testAnonTaskDeletionAsAdmin(): void
    {
        $session = $this->visit('');
        $this->loginSession($session, admin: true);

        $task = (new Task())
            ->setTitle('A task title to delete')
            ->setContent('A task content to delete')
            ->setIsDone(false);

        $this->em->persist($task);
        $this->em->flush();
        $this->visit(sprintf('/tasks/%s/delete', $task->getId()), $session);
        $this->screenshotSession($session, 'anon-task-deleted-as-admin');

        $current = $this->taskRepository->findOneBy(['id' => $task->getId()]);
        $this->assertNull($current);
    }

    #[ScreenshotFolder('deletion')]
    public function testAnonTaskDeletionAsNonAdmin(): void
    {
        $session = $this->visit('', authenticated: true);

        $task = (new Task())
            ->setTitle('A task title to delete')
            ->setContent('A task content to delete')
            ->setIsDone(false);

        $this->em->persist($task);
        $this->em->flush();
        $this->visit(sprintf('/tasks/%s/delete', $task->getId()), $session);
        $this->screenshotSession($session, 'anon-task-not-deleted-as-non-admin');

        $current = $this->taskRepository->findOneBy(['id' => $task->getId()]);
        $this->em->refresh($current);
        $this->assertNotNull($current);

        $this->assertTrue($session->getPage()->has('css', 'div.alert.alert-warning'));
    }

    #[ScreenshotFolder('deletion')]
    public function testUserTaskDeletionAsAdmin(): void
    {
        $session = $this->visit('');
        $this->loginSession($session, admin: true);

        $task = (new Task())
            ->setTitle('A task title to delete')
            ->setContent('A task content to delete')
            ->setIsDone(false)
            ->setAuthor($this->userRepository->findOneBy(['username' => 'user']));

        $this->em->persist($task);
        $this->em->flush();
        $this->visit(sprintf('/tasks/%s/delete', $task->getId()), $session);
        $this->screenshotSession($session, 'task-not-deleted-as-admin-non-author');

        $current = $this->taskRepository->findOneBy(['id' => $task->getId()]);
        $this->em->refresh($current);
        $this->assertNotNull($current);

        $this->assertTrue($session->getPage()->has('css', 'div.alert.alert-warning'));
    }

    #[ScreenshotFolder('deletion')]
    public function testUserTaskDeletionAsUser(): void
    {
        $session = $this->visit('', authenticated: true);

        $author = (new User())
            ->setUsername(uniqid() . 'user')
            ->setPassword(uniqid() . 'user')
            ->setEmail(uniqid() . 'test@mail.com');
        $task = (new Task())
            ->setTitle('A task title to delete')
            ->setContent('A task content to delete')
            ->setIsDone(false)
            ->setAuthor($author);

        $this->em->persist($author);
        $this->em->persist($task);
        $this->em->flush();

        $this->visit(sprintf('/tasks/%s/delete', $task->getId()), $session);
        $this->screenshotSession($session, 'task-not-deleted-as-user');

        $current = $this->taskRepository->findOneBy(['id' => $task->getId()]);
        $this->em->refresh($current);
        $this->assertNotNull($current);

        $this->assertTrue($session->getPage()->has('css', 'div.alert.alert-warning'));
    }

    #[ScreenshotFolder('deletion')]
    public function testUserTaskDeletionAsNonAdminAuthor(): void
    {
        $session = $this->visit('/');
        $this->loginSession($session, self::DEV_USER_USERNAME, self::DEV_USER_PASSWORD, false);

        $task = (new Task())
            ->setTitle('A task title to delete')
            ->setContent('A task content to delete')
            ->setIsDone(false)
            ->setAuthor($this->userRepository->findOneBy(['username' => self::DEV_USER_USERNAME]));

        $this->em->persist($task);
        $this->em->flush();
        $this->visit(sprintf('/tasks/%s/delete', $task->getId()), $session);
        $this->screenshotSession($session, 'task-deleted-as-non-admin-author');

        $current = $this->taskRepository->findOneBy(['id' => $task->getId()]);
        if ($current !== null) {
            $this->em->refresh($current);
        }

        $this->assertNull($current);
    }

    #[ScreenshotFolder('deletion')]
    public function testAdminTaskDeletionAsAdminAuthor(): void
    {
        $session = $this->visit('');
        $this->loginSession($session, admin: true);

        $task = (new Task())
            ->setTitle('A task title to delete')
            ->setContent('A task content to delete')
            ->setIsDone(false)
            ->setAuthor($this->userRepository->findOneBy(['username' => 'admin']));

        $this->em->persist($task);
        $this->em->flush();
        $this->visit(sprintf('/tasks/%s/delete', $task->getId()), $session);
        $this->screenshotSession($session, 'task-deleted-as-admin-author');

        $current = $this->taskRepository->findOneBy(['id' => $task->getId()]);
        $this->assertNull($current);
    }

    #[ScreenshotFolder('toggling')]
    public function testTaskToggling(): void
    {
        $session = $this->visit('');
        $this->loginSession($session, admin: true);

        $task = (new Task())
            ->setTitle('A task to toggle\'s title')
            ->setContent('A task to toggle\'s content')
            ->setIsDone(false);

        $this->em->persist($task);
        $this->em->flush();
        $this->visit(sprintf('/tasks/%s/toggle', $task->getId()), $session);
        $this->screenshotSession($session, 'task-toggled');

        $this->em->refresh($task);
        $this->assertTrue($task->getIsDone());

        $this->visit(sprintf('/tasks/%s/toggle', $task->getId()), $session);
        $this->screenshotSession($session, 'task-toggled-again');

        $this->em->refresh($task);
        $this->assertFalse($task->getIsDone());
    }

    protected function tearDown(): void
    {
        $this->tearDownMink();

        $this->em->close();
        unset($this->em);
        unset($this->taskRepository);
        unset($this->userRepository);
    }
}
