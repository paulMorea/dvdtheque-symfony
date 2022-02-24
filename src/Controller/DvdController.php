<?php

namespace App\Controller;

use App\Entity\Dvd;
use App\Form\DvdFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

// #[Route('/admin', name: 'dvds_')]
class DvdController extends AbstractController
{
    #[Route('/', name: 'dvds_index')]
    public function index(ManagerRegistry $managerRegistry): Response
    {
        $entityManager = $managerRegistry->getManager();
        $dvds = $entityManager->getRepository(Dvd::class)->findAll();
        return $this->render(
            'dvd/index.html.twig',
            ['dvds' => $dvds]
        );
    }

    #[Route('/admin', name: 'dvds_admin')]
    public function admin(ManagerRegistry $managerRegistry): Response
    {
        $entityManager = $managerRegistry->getManager();
        $dvds = $entityManager->getRepository(Dvd::class)->findAll();
        return $this->render(
            'dvd/admin.html.twig',
            ['dvds' => $dvds]
        );
    }
    #[Route('/admin/add', name: 'dvds_add')]
    public function dvdAdd(EntityManagerInterface $entityManager, Request $request, SluggerInterface $slugger): Response
    {
        $dvd = new Dvd;
        $form = $this->createForm(DvdFormType::class, $dvd);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dvd = $form->getData();
            $cover = $form->get('cover')->getData();
            if ($cover) {
                $originalFilename = pathinfo($cover->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $cover->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $cover->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $dvd->setCover($newFilename);
            } else {
                $dvd->setCover('dvd.png');
            }

            $entityManager->persist($dvd);
            $entityManager->flush();
            return $this->redirectToRoute('dvds_admin');
        }
        return $this->render('dvd/addDvd.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/admin/edit/{id}', name: 'dvds_edit')]
    public function dvdEdit(EntityManagerInterface $entityManager, Request $request, SluggerInterface $slugger, int $id): Response
    {
        $dvd = $entityManager->getRepository(Dvd::class)->find($id);
        $form = $this->createForm(DvdFormType::class, $dvd);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $cover = $form->get('cover')->getData();
            if ($cover) {
                $originalFilename = pathinfo($cover->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $cover->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $cover->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                if($dvd->getCover()!='dvd.png'){
                    unlink('../public/images/' . $dvd->getCover());
                }
                $dvd->setCover($newFilename);
            }
            $entityManager->persist($data);
            $entityManager->flush();

            return $this->redirectToRoute('dvds_admin');
        }
        return $this->render('dvd/addDvd.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/admin/delete/{id}', name: 'dvds_delete')]
    public function dvdDelete(ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $dvd  = $entityManager->getRepository(Dvd::class)->find($id);
        if($dvd->getCover()!='dvd.png'){
            unlink('../public/images/' . $dvd->getCover());
        }
        $entityManager->remove($dvd);
        $entityManager->flush();
        return $this->redirectToRoute('dvds_admin');
    }
}
