<?php

namespace App\Controller;

use App\Entity\Department;
use App\Form\DepartmentType;
use App\Repository\DepartmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/department')]
class DepartmentController extends AbstractController
{
    #[Route('/', name: 'app_department_manage', methods: ['GET', 'POST'])]
    public function manage(Request $request, DepartmentRepository $departmentRepository, EntityManagerInterface $entityManager): Response
    {
        // --- Form for creating a new Department ---
        $department = new Department();
        $form = $this->createForm(DepartmentType::class, $department);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Calculate total_equipe if necessary (assuming it's 0 by default or handled elsewhere)
            // $department->setTotalEquipe(0); // Example if needed

            $entityManager->persist($department);
            $entityManager->flush();

            $this->addFlash('success', 'Département ajouté avec succès.'); // Optional success message

            // Redirect back to the same page to show the updated list and clear the form
            return $this->redirectToRoute('app_department_manage', [], Response::HTTP_SEE_OTHER);
        }
        // --- End Form Handling ---

        // Fetch all departments to display in the list
        $departments = $departmentRepository->findAll(); // Or add ordering: findBy([], ['name' => 'ASC'])

        return $this->render('department/manage.html.twig', [
            'departments' => $departments,
            'creation_form' => $form->createView(), // Pass the form view
        ]);
    }

    #[Route('/{id}/edit', name: 'app_department_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Department $department, EntityManagerInterface $entityManager): Response
    {
        // Keep your existing edit logic, but redirect back to the manage page on success
        $form = $this->createForm(DepartmentType::class, $department);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Département mis à jour avec succès.'); // Optional success message
            return $this->redirectToRoute('app_department_manage', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('department/edit.html.twig', [ // Still need an edit template
            'department' => $department,
            'edit_form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_department_delete', methods: ['POST'])]
    public function delete(Request $request, Department $department, EntityManagerInterface $entityManager): Response
    {
        // Keep your existing delete logic, but redirect back to the manage page
        if ($this->isCsrfTokenValid('delete'.$department->getId(), $request->request->get('_token'))) {
            $entityManager->remove($department);
            $entityManager->flush();
            $this->addFlash('success', 'Département supprimé avec succès.'); // Optional success message
        } else {
            $this->addFlash('error', 'Jeton CSRF invalide.'); // Optional error message
        }


        return $this->redirectToRoute('app_department_manage', [], Response::HTTP_SEE_OTHER);
    }

    // You might not need the 'show' or 'new' actions anymore if 'manage' covers listing and creation.
    // Keep the 'edit' action as it likely requires its own page/modal.
    // Keep the 'delete' action as it handles the POST request for deletion.
}