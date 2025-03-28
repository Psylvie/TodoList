<?php

namespace App\Controller;


use App\Entity\Task;
use App\Entity\User;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TaskController extends AbstractController
{

    public function __construct(
        private readonly TaskRepository $taskRepository,
        private readonly EntityManagerInterface $em,
    )
    {
    }

    #[Route('/tasks', name: 'task_list')]
    public function list(): Response
    {
        $user = $this->getUser();
        $tasks = $this->taskRepository->findBy(['user' => $user]);
        return $this->render('task/list.html.twig', [
            'tasks' => $tasks
        ]);
    }

    #[Route('/tasks/create', name: 'task_create')]
    public function create(Request $request): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
        $user = $this->getUser();

        if (!$user || !$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $task->setUser($user);
        $task->setIsDone(false);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($task);
            $this->em->flush();

            $this->addFlash('success', 'La tâche a bien été ajoutée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/tasks/{id}/edit', name: 'task_edit')]
    public function edit(Task $task, Request $request): Response
    {
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('success', 'La tâche a bien été modifiée');
            return $this->redirectToRoute('task_list');
        }
        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task
        ]);
    }

    #[Route('/tasks/{id}/toggle', name: 'task_toggle')]
    public function toogleTask(Task $task): Response
    {
        $task->toggle(!$task->isDone());
        try {
            $this->em->flush();
            $this->addFlash('success', sprintf('La tâche "%s" a bien été marquée comme %s.', $task->getTitle(), $task->IsDone() ? 'terminée' : 'non terminée'));
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la mise à jour de la tâche.');
        }
        return $this->redirectToRoute('task_list');
    }

    #[Route('/tasks/{id}/delete', name: 'task_delete')]
    public function delete(Task $task): Response
    {
        $this->em->remove($task);
        $this->em->flush();

        $this->addFlash('success', 'La tache a bien été supprimé ');

        return $this->redirectToRoute('task_list');
    }
    }