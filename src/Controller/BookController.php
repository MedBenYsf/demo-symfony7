<?php

namespace App\Controller;

use App\Entity\Book;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api')]
class BookController extends AbstractController
{
    #[Route('/books', name: 'app_getBooks', methods: ['GET'])]
    public function getBooks(BookRepository $bookRepository, SerializerInterface $serializerInterface): JsonResponse
    {
        $bookList = $bookRepository->findAll();
        $jsonBookList = $serializerInterface->serialize($bookList, 'json', ['groups' => 'getBooks']);
        
        return new  JsonResponse([
            $jsonBookList, Response::HTTP_OK, [], true
        ]);
    }

    #[Route('/books/{id}', name: 'app_getBook', methods: ['GET'])]
    public function getBook(Book $book, SerializerInterface $serializerInterface) {
        $bookJson = $serializerInterface->serialize($book, 'json', ['groups' => 'getBooks']);
        
        return new JsonResponse($bookJson, Response::HTTP_OK, [], true);
    }

    #[Route('/books/{id}', name: 'app_deleteBook', methods: ['DELETE'])]
    public function deleteBook(Book $book, EntityManagerInterface $em) {
        $em->remove($book);
        $em->flush();
        
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/books', name: 'app_createBook', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'vous n\'avez pas le role necéssaire')]
    public function createBook(Request $request, EntityManagerInterface $em, ValidatorInterface $validator, SerializerInterface $serializer) {
       $book = $serializer->deserialize($request->getContent(), Book::class, 'json');
       $errors = $validator->validate($book);

       if ($errors->count() > 0) {
        return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
       }

       $em->persist($book);
       $em->flush();
   
       $jsonBook = $serializer->serialize($book, 'json', ['groups' => 'getBooks']);

       return new JsonResponse($jsonBook, Response::HTTP_CREATED, [], true);
   }
    
}
