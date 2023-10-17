<?php

namespace App\Controller;

use App\Entity\Task;
use App\Repository\TaskRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TaskController extends AbstractController
{
//    #[Route('/task', name: 'create_task')]
//    public function index(EntityManagerInterface $entityManager): Response
//    {
//        $date = new DateTimeImmutable();
//        $task = new Task();
//        $task->setStatus(1);
//        $task->setPriority(1);
//        $task->setTitle('Learn Symfony');
//        $task->setDescription('Ergonomic and stylish!');
//        $task->setCreatedAt($date);
//        $task->setCompletedAt($date);
//
//        // tell Doctrine you want to (eventually) save the Product (no queries yet)
//        $entityManager->persist($task);
//
//        // actually executes the queries (i.e. the INSERT query)
//        $entityManager->flush();
//
//        return new Response('Saved new product with id '.$task->getId());
//    }

    #[Route('/tasks', name: 'task_create', methods:['post'] )]
    public function create(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        $entityManager = $doctrine->getManager();

        $task = new Task();
        $this->extracted($task, $request);

        $entityManager->persist($task);
        $entityManager->flush();

        $data =  [
            'id' => $task->getId(),
            'title' => $task->getTitle(),
            'description' => $task->getDescription(),
        ];

        return $this->json($data);
    }


    #[Route('/tasks/{id}', name: 'task_update', methods:['put', 'patch'] )]
    public function update(ManagerRegistry $doctrine, Request $request, int $id): JsonResponse
    {
        $entityManager = $doctrine->getManager();
        $task = $entityManager->getRepository(Task::class)->find($id);

        if (!$task) {
            return $this->json('No task found for id' . $id, 404);
        }

        $this->extracted($task, $request);
        $entityManager->flush();

        $data =  [
            'id' => $task->getId(),
            'title' => $task->getTitle(),
            'description' => $task->getDescription(),
        ];

        return $this->json($data);
    }

    #[Route('/tasks/{id}', name: 'task_delete', methods:['delete'] )]
    public function delete(ManagerRegistry $doctrine, int $id): JsonResponse
    {
        $entityManager = $doctrine->getManager();
        $task = $entityManager->getRepository(Task::class)->find($id);

        if (!$task) {
            return $this->json('No task found for id ' . $id, 404);
        }

        $entityManager->remove($task);
        $entityManager->flush();

        return $this->json('Deleted a task successfully with id ' . $id);
    }

    #[Route('/tasks', name: 'filter_by_status', methods: ['get'])]
    public function statusFilter(Request $request, TaskRepository $taskRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        if($request->query->get('filter_by_status')) {
            $filter = $entityManager->getFilters()->enable('status_filter');
            $filter->setParameter('status', '1');
        }

        $tasks = $entityManager->getRepository(Task::class)->findAll();

        $data = [];

        foreach ($tasks as $task) {
            $data[] = [
                'id' => $task->getId(),
                'title' => $task->getTitle(),
                'description' => $task->getDescription(),
                'status' => $task->getStatus(),
            ];
        }

        return $this->json($data);
    }

    /**
     * @param Task $task
     * @param Request $request
     * @return void
     */
    public function extracted(Task $task, Request $request): void
    {
        $task->setStatus($request->request->get('status'));
        $task->setPriority($request->request->get('priority'));
        $task->setTitle($request->request->get('title'));
        $task->setDescription($request->request->get('description'));
//        $task->setCreatedAt(\DateTimeImmutable::createFromFormat('Y-m-d', $request->request->get('createdAt')));
//        $task->setCompletedAt(\DateTimeImmutable::createFromFormat('Y-m-d', $request->request->get('completedAt')));
        $task->setCreatedAt(\DateTime::createFromFormat('Y-m-d', $request->request->get('createdAt')));
        $task->setCompletedAt(\DateTime::createFromFormat('Y-m-d', $request->request->get('completedAt')));
    }
}
