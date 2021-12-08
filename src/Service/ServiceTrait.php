<?php

namespace App\Service;

use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Security;

trait ServiceTrait
{
    private string $DEFAULT_FORM_TYPE_CLASS = "No form type class set";
    private string $DEFAULT_ENTITY_CLASS = "No entity class set";

    private FlashBag $flashes;

    public function __construct(
        private Environment $twig,
        private TaskRepository $taskRepo,
        private UserRepository $userRepo,
        private FormFactoryInterface $form,
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $hasher,
        private UrlGeneratorInterface $router,
        private Security $security,
        SessionInterface $session,

    ) {
        $this->flashes = $session->getBag('flashes');

        $this->FORM_TYPE_CLASS = $this->FORM_TYPE_CLASS ?? $this->DEFAULT_FORM_TYPE_CLASS;
        $this->ENTITY_CLASS = $this->ENTITY_CLASS ?? $this->DEFAULT_ENTITY_CLASS;
    }

    /**
     * Returns a redirect response.
     *
     * @param string $route The route to redirect to.
     *
     * @return RedirectResponse
     */
    public function redirect(string $route): RedirectResponse
    {
        $url = $this->router->generate($route);

        return new RedirectResponse($url);
    }

    /**
     * Returns an html response.
     *
     * @param string $template The twig template to render.
     * @param array  $context  The context to pass to the twig renderer.
     *
     * @return Response
     */
    public function render(string $template, array $context = []): Response
    {
        $page = $this->twig->render($template, $context);

        return new Response($page);
    }

    /**
     * Returns the entity class if defined or the entity if passed
     *
     * @param string $entityClass The passed in entity class.
     * @param mixed  $entity      The passed in entity.
     *
     * @throws LogicException If not entity nor entity classes are found.
     *
     * @return mixed
     */
    public function isEntityClassDefined(?string $entityClass): mixed
    {
        $entityClassNotSet = $this->ENTITY_CLASS === $this->DEFAULT_ENTITY_CLASS;

        if (!$entityClass) {
            if ($entityClassNotSet) {
                throw new LogicException('Tried using "generateForm" method whilst the service class did not specify it\'s Entity Class');
            }
        }

        return $entityClass ?? $this->ENTITY_CLASS;
    }

    /**
     * Returns the correct Form Class & throws an error if none is defined.
     *
     * @param string $formTypeClass the custom form type passed to the method.
     *
     * @throws LogicException If no classes were found.
     *
     * @return string
     */
    public function isFormTypeClassDefined(?string $formTypeClass): string
    {
        if (!$formTypeClass) {
            if ($this->FORM_TYPE_CLASS === $this->DEFAULT_FORM_TYPE_CLASS) {
                throw new LogicException('Tried using "generateForm" method whilst the service class did not specify it\'s Form Type Class');
            }
        }

        return $formTypeClass ?? $this->FORM_TYPE_CLASS;
    }


    /**
     * @return {form: FormInterface, entity: mixed}
     */
    public function generateForm(
        ?Request $request = null,
        mixed $entity = null,
        ?string $formTypeClass = null,
        ?string $entityClass = null
    ): \stdClass {
        $entityClass   = $this->isEntityClassDefined($entityClass);
        $formTypeClass = $this->isFormTypeClassDefined($formTypeClass);

        $entity = $entity ?? new $entityClass();
        $form = $this->form->create($formTypeClass, $entity);
        if ($request) {
            $form->handleRequest($request);
        }

        return (object) ['form' => $form, 'entity' => $entity];
    }
}
