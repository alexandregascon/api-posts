<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\CategorieRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Attributes as OA;

#[Route('/api')]
class PostController extends AbstractController
{
    #[Route('/posts', name: 'api_post_index', methods: ["GET"])]
    #[OA\Tag(name: "Posts")]
    #[OA\Get(
        path: "/api/posts",
        description: "Permet de récupérer la liste des posts",
        summary: "Lister l'ensemble des posts",
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste des posts au format Json",
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        ref: new Model(type: Post::class, groups: ['list_posts'])
                    )
                )
            )
        ]
    )]
    public function index(PostRepository $postRepository, SerializerInterface $serializer): Response
    {
        // Rechercher tous les Posts dans la base de données
        $posts = $postRepository->findAll();

        // Serialiser le tableau de Posts en Json
        $postsJson = $serializer->serialize($posts,"json",['groups'=>'list_posts']);
        // Construire la réponse HTTP
//        $reponse = new Response();
//        $reponse->setStatusCode(Response::HTTP_OK);
//        $reponse->headers->set("content-type","application/json");
//        $reponse->setContent($postsJson);
//        return $reponse;
        // VERSION CONDENCE
        return new Response($postsJson,Response::HTTP_OK,["content-type"=>"application/json"]);
    }

    #[Route('/posts/{id}', name: 'api_post_show',requirements: ['id' => '\d+'],methods: ["GET"])]
    #[OA\Tag(name: "Posts")]
    #[OA\Get(

        path: "/api/posts/{id}",
        description: "Permet de récupérer un post par son id",
        summary: "Récupérer un post",
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Id du post à rechercher",
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    type: "integer"
                )
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Détail du post au format Json",
                content: new OA\JsonContent(
                    ref: new Model(type: Post::class, groups: ['show_post'])
                )
            )
        ]
    )]
    public function show(PostRepository $postRepository, SerializerInterface $serializer, int $id): Response
    {
        // Rechercher tous les Posts dans la base de données
        $post = $postRepository->find($id);

        // Serialiser le tableau de Posts en Json
        $postJson = $serializer->serialize($post,"json",['groups'=>'show_post']);
        // Construire la réponse HTTP
        return new Response($postJson,Response::HTTP_OK,["content-type"=>"application/json"]);
    }

    #[Route('/posts',"api_post_create",methods: ['POST'])]
    public function create(Request $requete, SerializerInterface $serializer, EntityManagerInterface $entityManager, CategorieRepository $categorieRepository) : Response {
        // Récupérer le body de la requête HTTP au format JSON
        $user = $this->getUser();
        $bodyRequete = $requete->getContent();
        // Désérialise le JSON en un objet de la classe Post
        $post = $serializer->deserialize($bodyRequete,Post::class,'json');
        // Insérer le nouveau post dans la BDD
        $post->setCreatedAt(new \DateTime());
        $post->setUser($user);

        $idCategorie = (json_decode($bodyRequete,true))["categorie"];
        $categorie = $categorieRepository->find($idCategorie);
        $post->setCategorie($categorie);

        $entityManager->persist($post);
        $entityManager->flush();
        // Générer la réponse
        $position = $serializer->serialize($post,'json',['groups'=>'list_posts']);
        return new Response($position,Response::HTTP_CREATED,["content-type" => "application/json"]);
    }

    #[Route('/posts/{id}', name: 'api_post_delete',requirements: ['id' => '\d+'],methods: ["DELETE"])]
    public function delete(EntityManagerInterface $entityManager,SerializerInterface $serializer, int $id): Response
    {
        $post = $entityManager->find(Post::class,33);
        $entityManager->remove($post);
        $entityManager->flush();

        // Construire la réponse HTTP
        return new Response(null,Response::HTTP_NO_CONTENT,["content-type"=>"application/json"]);
    }

    #[Route('/posts/{id}', name: 'api_post_update', requirements: ['id' => '\d+'],methods: ["PUT"])]
    public function update(EntityManagerInterface $entityManager, SerializerInterface $serializer,int $id, Request $request): Response
    {
        $bodyRequest = $request->getContent();
        $post = $entityManager->find(Post::class,$id);
        $serializer->deserialize($bodyRequest,Post::class,'json',['object_to_populate'=>$post]);

        // Construire la réponse HTTP
        $entityManager->flush();
        return new Response(null,Response::HTTP_NO_CONTENT);
    }

    #[Route('/posts/publies-apres', name: 'api_post_publies_apres',methods: ["GET"])]
    public function findPubliesApres(Request $requete,PostRepository $postRepository, SerializerInterface $serializer): Response
    {
        $date = $requete->query->get('date');
        // Rechercher tous les Posts dans la base de données
        $date = new \DateTime($date);
        $posts = $postRepository->findByDate($date);

        // Serialiser le tableau de Posts en Json
        $postsJson = $serializer->serialize($posts,"json",['groups'=>'list_posts']);
        // Construire la réponse HTTP
        return new Response($postsJson,Response::HTTP_OK,["content-type"=>"application/json"]);
    }
}
