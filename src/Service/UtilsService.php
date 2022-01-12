<?php

namespace App\Service;

use stdClass,
    LogicException,
    Twig\Environment,
    Symfony\Component\Form\FormFactoryInterface,
    Symfony\Component\HttpFoundation\RedirectResponse,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpFoundation\RequestStack,
    Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface,
    Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UtilsService
{
    const DEFAULT_FORM_TYPE_CLASS = "No form type class set";
    const DEFAULT_ENTITY_CLASS = "No entity class set";

    private string $formTypeClass = self::DEFAULT_FORM_TYPE_CLASS;
    private string $entityClass = self::DEFAULT_ENTITY_CLASS;

    public function __construct(
        private Environment $twig,
        private FormFactoryInterface $form,
        private UrlGeneratorInterface $router,
        private RequestStack $requestStack,
    ) {
    }

    /**
     * Sets the classes defaults for form generation.
     * 
     * @param ?string $formTypeClass   The form class to generate.
     * @param ?string $entityClass The entity class to generate.
     * 
     * @return void
     */
    public function setupFormDefaults(
        ?string $formTypeClass = null,
        ?string $entityClass = null
    ): void {
        if ($formTypeClass) $this->formTypeClass = $formTypeClass;
        if ($entityClass) $this->entityClass = $entityClass;
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
    public function isEntityClassDefined(?string $entityClass = null): mixed
    {
        $entityClassNotSet = $this->entityClass === self::DEFAULT_ENTITY_CLASS;

        if (!$entityClass) {
            if ($entityClassNotSet) {
                throw new LogicException('Tried using "generateForm" method whilst the service class did not specify it\'s Entity Class');
            }
        }

        return $entityClass ?? $this->entityClass;
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
    public function isFormTypeClassDefined(?string $formTypeClass = null): string
    {
        if (!$formTypeClass) {
            if ($this->formTypeClass === self::DEFAULT_FORM_TYPE_CLASS) {
                throw new LogicException('Tried using "generateForm" method whilst the service class did not specify it\'s Form Type Class');
            }
        }

        return $formTypeClass ?? $this->formTypeClass;
    }


    /**
     * Generates a form interface and an entity.
     *
     * @return {form: FormInterface, entity: mixed}
     */
    public function generateForm(
        ?Request $request = null,
        mixed $entity = null,
        ?string $formTypeClass = null,
        ?string $entityClass = null,
        array $formOptions = []
    ): stdClass {
        $entityClass   = $this->isEntityClassDefined($entityClass);
        $formTypeClass = $this->isFormTypeClassDefined($formTypeClass);

        $entity = $entity ?? new $entityClass();
        $form = $this->form->create($formTypeClass, $entity, $formOptions);
        if ($request) {
            $form->handleRequest($request);
        }

        return (object) ['form' => $form, 'entity' => $entity];
    }

    /**
     * @codeCoverageIgnore
     * Adds a flash to flashbag
     *
     * @param string $message The message to add.
     * @param string $type    The type of the message (e.g: 'succes', 'warning', etc...)
     *
     * @return FlashBagInterface
     */
    public function addFlash(string $message, string $type = 'success'): FlashBagInterface
    {
        /** @var FlashBagInterface */
        $flashbag = $this->requestStack->getSession()->getBag('flashes');
        $flashbag->add($type, $message);

        return $flashbag;
    }
}
