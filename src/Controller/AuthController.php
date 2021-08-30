<?php

namespace App\Controller;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\SerializerInterface;

class AuthController extends AbstractController
{
    public function register(Request $request, UserPasswordHasherInterface $encoder)
    {
        $em = $this->getDoctrine()->getManager();
        $parameters = json_decode($request->getContent(), true);
        $email = $parameters['email'];
        $password = $parameters['password'];
        $designatin = $parameters['designation'];

        $user = new User($email);
        $user->setEmail($email);
        $user->setPassword($encoder->hashPassword($user, $password));
        $user->setDesignation($designatin);
        $em->persist($user);
        $em->flush();

        return new JsonResponse(['message'=> 'user created!']);
    }
    
    public function getTokenUser(UserInterface $user, JWTTokenManagerInterface $JWTManager)
    {
        return new JsonResponse(['token' => $JWTManager->create($user)]);
    }
    
    /**
     * @Route("/api/getuser", methods={"GET"})
     */
    public function showUser(SerializerInterface $serializer)
    {
        $user = $serializer->serialize($this->getUser(), 'json');
        return new JsonResponse(['user' => json_decode($user)]);
    }
}