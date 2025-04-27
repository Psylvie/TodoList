<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly EntityManagerInterface $em,
    ) {}

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/user', name: 'user_list', methods: ['GET'])]
    public function listUsers(): Response
    {
        $users = $this->userRepository->findAll();

        return $this->render('user/list.html.twig', [
            'users' => $users,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin/user/{id}/edit', name: 'user_edit', methods: ['GET', 'POST'])]
    public function edit(User $user, Request $request): Response
    {
        $currentUser = $this->getUser();
        $isAdmin = $currentUser && in_array('ROLE_ADMIN', $currentUser->getRoles(), true);
        $form = $this->createForm(UserType::class, $user, [
            'is_admin' => $isAdmin, ]);
        $form->handleRequest($request);
        $tasks = $user->getTasks();
        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            if (null !== $plainPassword && '' !== $plainPassword) {
                $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }
            $this->em->persist($user);
            $this->em->flush();
            $this->addFlash('success', "l'utilisateur ".$user->getUsername().' a bien été modifié!');

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'is_admin' => $isAdmin,
            'tasks' => $tasks,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/users/create', name: 'user_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $user = new User();

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->get('first')->getData();
            if (!empty($plainPassword)) {
                $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
                $this->em->persist($user);
                $this->em->flush();
                $this->addFlash('success', "L'utilisateur a bien été ajouté.");

                return $this->redirectToRoute('user_list');
            }
            $this->addFlash('error', 'Le mot de passe est requis.');
        }

        return $this->render('user/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}
