<?php

namespace App\Controller\Api;

use App\Entity\Artist;
use OpenApi\Attributes as OA;
use App\Repository\ArtistRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[OA\Tag(name: "Artist")]
class ArtistAPIController extends AbstractController
{
    public function __construct(
        private ArtistRepository $artistRepository,
        private EntityManagerInterface $em,
        private SerializerInterface $serializer
    ) {
       
    }

    #[Route('/api/artist', name: 'app_api_artist', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Artist::class, groups: ['read']))
        )
    )]
    public function index(
        PaginatorInterface $paginator,
        Request $request
    ): JsonResponse {

        $artist = $this->artistRepository->getAllAbilitiesQuery();

        $data = $paginator->paginate(
            $artist,
            $request->query->get('page', 1),
            2
        );

        return $this->json([
            'data' => $data,
        ], 200, [], [
            'groups' => ['read']
        ]);
    }

    #[Route('/api/artist/{id}', name: 'app_api_artist_get',  methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new Model(type: Artist::class, groups: ['read'])
    )]
    public function get(?Artist $artist = null): JsonResponse
    {
        if (!$artist) {
            return $this->json([
                'error' => 'Ressource does not exist',
            ], 404);
        }

        return $this->json($artist, 200, [], [
            'groups' => ['read']
        ]);
    }

    #[Route('/api/artist', name: 'app_api_artist_add',  methods: ['POST'])]
    public function add(
        #[MapRequestPayload('json', ['groups' => ['create']])] Artist $artist
    ): JsonResponse {
        $this->em->persist($artist);
        $this->em->flush();

        return $this->json($artist, 200, [], [
            'groups' => ['read']
        ]);
    }


    #[Route('/api/artist/{id}', name: 'app_api_artist_update',  methods: ['PUT'])]
    #[OA\Put(
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                ref: new Model(
                    type: Artist::class,
                    groups: ['update']
                )
            )
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful response',
        content: new Model(type: Artist::class, groups: ['read'])
    )]
    public function update(Artist $artist, Request $request): JsonResponse
    {

        $data = $request->getContent();
        $this->serializer->deserialize($data, Artist::class, 'json', [
            AbstractNormalizer::OBJECT_TO_POPULATE => $artist,
            'groups' => ['update']
        ]);

        $this->em->flush();

        return $this->json($artist, 200, [], [
            'groups' => ['read'],
        ]);
    }

    #[Route('/api/artist/{id}', name: 'app_api_artist_delete',  methods: ['DELETE'])]
    public function delete(Artist $artist): JsonResponse
    {
        $this->em->remove($artist);
        $this->em->flush();

        return $this->json([
            'message' => 'artist deleted successfully'
        ], 200);
    }
}
