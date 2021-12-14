<?php

namespace App\Service;

use App\Entity\User;
use App\Form\UserType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Trait\ServiceTrait;

class UserService
{
    use ServiceTrait;

    public string $ENTITY_CLASS = User::class;
    public string $FORM_TYPE_CLASS = UserType::class;

    /*>>> Actions >>>*/

    /**
     * @codeCoverageIgnore
     * Returns a list of users.
     *
     * @return Response The html response.
     */
    public function listAction(): Response
    {
        return $this->render('user/list.html.twig', [
            'users' => $this->list(),
        ]);
    }

    /**
     * @codeCoverageIgnore
     * Creates a user.
     *
     * @param Request $request The incoming http request containing the form data.
     *
     * @return Response The html response.
     */
    public function createAction(Request $request): Response
    {
        $form = $this->generateForm($request);

        if ($form->form->isSubmitted() && $form->form->isValid()) {
            $this->save($form->entity);
            $message = 'Cet utilisateur a bien été crée.';
            $this->addFlash($message, 'success');

            return $this->redirect('user_list');
        }

        return $this->render('user/create.html.twig', [
            'form' => $form->form->createView()
        ]);
    }


    /**
     * @codeCoverageIgnore
     * Edits a users.
     *
     * @param User    $user    The user to modify
     * @param Request $request The incoming http request
     *
     * @return Response The html response.
     */
    public function editAction(User $user, Request $request): Response
    {
        $form = $this->generateForm($request, $user, formOptions: ['displayPasswordField' => false]);

        if ($form->form->isSubmitted() && $form->form->isValid()) {
            $this->update($user);
            $message = "L'utilisateur a bien été modifié";
            $this->addFlash($message, 'success');

            return $this->redirect('user_list');
        }

        return $this->render('user/edit.html.twig', ['form' => $form->form->createView(), 'user' => $user]);
    }

    /*<<< Actions <<<*/
    /*>>> Helpers >>>*/

    /**
     * Returns a list of users
     * 
     * @return User[]
     */
    public function list(): array
    {
        return $this->userRepo->findAll();
    }

    /**
     * @return string[]
     */
    public function save(User $user): void
    {
        $password = $this->hasher->hashPassword($user, $user->getPassword());
        $user->setPassword($password);

        $this->em->persist($user);
        $this->em->flush();
    }

    /**
     * @return string[]
     */
    public function update(User $user): void
    {
        if ($this->hasher->needsRehash($user)) {
            $password = $this->hasher->hashPassword($user, $user->getPassword());
            $user->setPassword($password);
        }

        $this->em->flush();
    }
}
