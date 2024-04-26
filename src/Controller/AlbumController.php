<?php

namespace App\Controller;

use App\Entity\Album;
use App\Form\AlbumType;
use App\Repository\AlbumRepository;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('admin/album')]
class AlbumController extends AbstractController
{
    #[Route('/', name: 'app_album_index', methods: ['GET'])]
    public function index(Request $request,EntityManagerInterface $entityManager, AlbumRepository $albumRepository): Response
    {

        $albums = $albumRepository->findAll();
        $totalAlbum = count($albums);

        
        
        return $this->render('album/index.html.twig', [
            'albums' => $albumRepository->findAll(),
            'totalAlbum' => $totalAlbum

        ]);
    }

    #[Route('/new', name: 'app_album_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $album = new Album();
        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($album);
            $entityManager->flush();

            return $this->redirectToRoute('app_album_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('album/new.html.twig', [
            'album' => $album,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_album_show', methods: ['GET'])]
    public function show(Album $album): Response
    {
        return $this->render('album/show.html.twig', [
            'album' => $album,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_album_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Album $album, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AlbumType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_album_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('album/edit.html.twig', [
            'album' => $album,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_album_delete', methods: ['POST'])]
    public function delete(Request $request, Album $album, EntityManagerInterface $entityManager): Response
    {
        // Check CSRF token validity
        if ($this->isCsrfTokenValid('delete'.$album->getId(), $request->request->get('_token'))) {
            
            // Check for associated tracks with the album
            if (!$album->getTracks()->isEmpty()) {
                $this->addFlash('error', 'Cannot delete album because there are tracks associated with it.');
                return $this->redirectToRoute('app_album_index');
            }
    
            // Proceed with deletion if no tracks are associated
            $entityManager->remove($album);
            $entityManager->flush();
            $this->addFlash('success', 'Album deleted successfully.');
        }
    
        return $this->redirectToRoute('app_album_index', [], Response::HTTP_SEE_OTHER);
    }
    



    
}
