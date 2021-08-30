<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Request as UserRequest;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Inventory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use App\Entity\Category;
use App\Entity\Role;
use App\Entity\User;
use DateTime;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class MainController extends AbstractController
{
    /**
     * @Route("/api/inventories", name="get_inventories", methods={"GET"})
     */
    public function getInventories()
    {
        $inventories = $this->getDoctrine()
                    ->getRepository(Inventory::class)
                    ->findAll();
        if(empty($inventories)){
            $response = array(
                'code' => 404,
                'message' => 'No inventories',
                'errors' => null,
                'result' => null
            );
            return new JsonResponse($response, 404);
        }
        $encoders = [new JsonEncoder()];
        $defaultContext = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                return $object->getName();
            },
            // AbstractNormalizer::IGNORED_ATTRIBUTES => ['inventories']
        ];
        $normalizers = [new ObjectNormalizer(null, null, null, null, null, null, $defaultContext)];

        $serializer = new Serializer($normalizers, $encoders);
        $data = $serializer->serialize($inventories, 'json');
        $response = array(
            'code' => 200,
            'message' => 'Success',
            'errors' => null,
            'result' => json_decode($data)
        );
            return new JsonResponse($response, 200);
    }
    
    /**
    * @Route("/api/inventories/{id}", name="get_inventory", methods={"GET"}, requirements={"id"="\d+"})
    */
    public function getInventory($id, Request $request){
       if($request->isMethod("GET")){
           $inventory = $this->getDoctrine()
                            ->getRepository(Inventory::class)
                            ->find($id);
                            
            if(empty($inventory)){
                $response = array(
                    'code' => 404,
                    'message' => 'No inventory',
                    'errors' => null,
                    'result' => null
                );  
                return new JsonResponse($response, 404);           
            } 
            // $data = $serializer->serialize($inventory, 'json', [AbstractNormalizer::IGNORED_ATTRIBUTES => ['category']]); // Ignore attributes
            $encoders = [new JsonEncoder()];
            $defaultContext = [
                AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                    return $object->getName();
                },
                AbstractNormalizer::IGNORED_ATTRIBUTES => ['inventories']
            ];
            $normalizers = [new ObjectNormalizer(null, null, null, null, null, null, $defaultContext)];

            $serializer = new Serializer($normalizers, $encoders);
            $data = $serializer->serialize($inventory, 'json');


            $response = array(
                'code' => 200,
                'message' => 'Sucess',
                'errors' => null,
                'result' => json_decode($data)
            );
            return new JsonResponse($response, 200);
        }
    }

    /**
     * @Route("/api/inventories", name="new_inventory", methods={"POST"})
     */
    public function postNewInventories(Request $request, SerializerInterface $serializer)
    {
            $category = $this->getDoctrine()->getRepository(Category::class)->find(1);

            $body = $request -> getContent();            
            $inventory = $serializer->deserialize($body, Inventory::class, 'json');
            $inventory->setCreatedAt(new DateTime('NOW'));
            
            $inventory->setCategory($category); //

            $em = $this->getDoctrine()->getManager();
            $em -> persist($inventory);

            $em->persist($category);

            $em -> flush();

            $response=array(
                'code'=> 201,
                'message'=>'Inventory created!',
                'errors'=>null,
                'result'=>null
            );
            return new JsonResponse($response, 201);
    }

    /**
     * @Route("/api/inventories/edit/{id}", name="edit_inventory", methods={"PUT"})
     */
    public function editInventory($id, Request $request, SerializerInterface $serializer)
    {
            $entityManager = $this->getDoctrine()->getManager();
                        
            $inventory = $entityManager->getRepository(Inventory::class)
                        ->find($id);

            if(empty($inventory)){
                $response = array(
                    'code' => 404,
                    'message' => 'No Inventory',
                    'errors' => null,
                    'result' => null
                );  
                return new JsonResponse($response, 404);           
            }           
            
            $body = $request->getContent();
            $new_inventory = $serializer->deserialize($body, Inventory::class, 'json');

            $inventory->setName($new_inventory->getName());
            $inventory->setBrand($new_inventory->getBrand());
            $inventory->setCategory($new_inventory->getCategory());
            $inventory->setSubCategory($new_inventory->getSubCategory());
            $inventory->setModel($new_inventory->getModel());
            $inventory->setStatus($new_inventory->getStatus());
            $inventory->setDescription($new_inventory->getDescription());

            $entityManager->flush();

            $response = array(
                'code'=> 200,
                'message'=>'Inventory updated!',
                'errors'=>null,
                'result'=>null
            );
            return new JsonResponse($response, 200); 
    }

    /**
     * @Route("/api/inventories/dispose/{id}", name="dispose_inventory", methods={"PUT"})
     */
    public function deleteInventory($id, Request $request, SerializerInterface $serializer)
    {
            $inventory = $this->getDoctrine()
                        ->getRepository(Inventory::class)
                        ->findOneBy(['id'=> $id]);

            if(empty($inventory)){
                $response = array(
                    'code' => 404,
                    'message' => 'No posts',
                    'errors' => null,
                    'result' => null
                );  
                return new JsonResponse($response, 404);           
            }           
            
            $em = $this->getDoctrine()->getManager();
            $inventory->setStatus("disposed");
            $inventory->setDisposeAt(new \DateTime('NOW'));
            $em->persist($inventory);
            $em->flush();

            $response = array(
                'code'=> 0,
                'message'=>'Inventory disposed!',
                'errors'=>null,
                'result'=>null
            );
            return new JsonResponse($response, 200); 
    }

    /**
     * @Route("/api/inventories/revive/{id}", name="delete_inventory", methods={"PUT"})
     */
    public function reviveInventory($id, Request $request, SerializerInterface $serializer)
    {
            $inventory = $this->getDoctrine()
                        ->getRepository(Inventory::class)
                        ->findOneBy(['id'=> $id]);

            if(empty($inventory)){
                $response = array(
                    'code' => 404,
                    'message' => 'No posts',
                    'errors' => null,
                    'result' => null
                );  
                return new JsonResponse($response, 404);           
            }           
            
            $em = $this->getDoctrine()->getManager();
            $inventory->setStatus("new");
            $inventory->removeDisposeAt();
            $em->persist($inventory);
            $em->flush();

            $response = array(
                'code'=> 0,
                'message'=>'Inventory revived!',
                'errors'=>null,
                'result'=>null
            );
            return new JsonResponse($response, 200); 
    }

    // /**
    //  * @Route("/api/categories" methods={"GET"})
    //  */
    // public function getCategoryInventory($cat_name, Request $request, SerializerInterface $serializer)
    // {
    //         $category = $this->getDoctrine()
    //                     ->getRepository(Category::class)
    //                     ->find(1);

    //         dump($category);
    // }
    
    // User
     /**
     * @Route("/api/users", name="get_users", methods={"GET"})
     */
    public function getUsers()
    {
  
        $users = $this->getDoctrine()
                    ->getRepository(User::class)
                    ->findAll();
        if(empty($users)){
            $response = array(
                'code' => 404,
                'message' => 'No users',
                'errors' => null,
                'result' => null
            );
            return new JsonResponse($response, 404);
        }

        // $data = $serializer->serialize($inventory, 'json', [AbstractNormalizer::IGNORED_ATTRIBUTES => ['category']]); // Ignore attributes
        $encoders = [new JsonEncoder()];
        $defaultContext = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                return $object->getFullName();
            },
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['role']
        ];
        $normalizers = [new ObjectNormalizer(null, null, null, null, null, null, $defaultContext)];

        $serializer = new Serializer($normalizers, $encoders);

        $data = $serializer->serialize($users, 'json');
        $response = array(
            'code' => 200,
            'message' => 'Success',
            'errors' => null,
            'result' => json_decode($data)
        );
            return new JsonResponse($response, 200);
    }
    
    /**
    * @Route("/api/users/{id}", name="get_user", methods={"GET"}, requirements={"id"="\d+"})
    */
    public function showUser($id, Request $request){
            $user = $this->getDoctrine()
                             ->getRepository(User::class)
                             ->find($id);
                             
             if(empty($user)){
                 $response = array(
                     'code' => 404,
                     'message' => 'No posts',
                     'errors' => null,
                     'result' => null
                 );  
                 return new JsonResponse($response, 404);           
             } 
             // $data = $serializer->serialize($inventory, 'json', [AbstractNormalizer::IGNORED_ATTRIBUTES => ['category']]); // Ignore attributes
             $encoders = [new JsonEncoder()];
             $defaultContext = [
                 AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                     return $object->getFullName();
                 },
                 AbstractNormalizer::IGNORED_ATTRIBUTES => ['role']
             ];
             $normalizers = [new ObjectNormalizer(null, null, null, null, null, null, $defaultContext)];
 
             $serializer = new Serializer($normalizers, $encoders);
             $data = $serializer->serialize($user, 'json');
 
 
             $response = array(
                 'code' => 200,
                 'message' => 'Sucess',
                 'errors' => null,
                 'result' => json_decode($data)
             );
             return new JsonResponse($response, 200);
    }

    // Remove user
     /**
    * @Route("/api/users/remove/{id}", name="remove_user", methods={"PUT"}, requirements={"id"="\d+"})
    */
    public function removeUser($id, Request $request){
        $user = $this->getDoctrine()
                         ->getRepository(User::class)
                         ->find($id);
                         
         if(empty($user)){
             $response = array(
                 'code' => 404,
                 'message' => 'No posts',
                 'errors' => null,
                 'result' => null
             );  
             return new JsonResponse($response, 404);           
         } 
        
         $user->setStatus("left");
         $user->setLeftAt(new DateTime('NOW'));
         $em = $this->getDoctrine()->getManager();
         $em->persist($user);
         $em->flush();


         $response = array(
             'code' => 200,
             'message' => 'User removed',
             'errors' => null,
             'result' => null
         );
         return new JsonResponse($response, 200);

    
        }


    // Revive user
     /**
    * @Route("/api/users/revive/{id}", name="revive_user", methods={"PUT"}, requirements={"id"="\d+"})
    */
    public function reviveUser($id, Request $request){
        $user = $this->getDoctrine()
                         ->getRepository(User::class)
                         ->find($id);
                         
         if(empty($user)){
             $response = array(
                 'code' => 404,
                 'message' => 'No posts',
                 'errors' => null,
                 'result' => null
             );  
             return new JsonResponse($response, 404);           
         } 
        
         $user->setStatus("new");
         $user->removeLeftAt();
         $em = $this->getDoctrine()->getManager();
         $em->persist($user);
         $em->flush();


         $response = array(
             'code' => 200,
             'message' => 'User revived',
             'errors' => null,
             'result' => null
         );
         return new JsonResponse($response, 200); 
        }

    // Make request
     /**
    * @Route("/api/requests", name="make_request", methods={"POSt"})
    */
    public function makeRequest(Request $request, SerializerInterface $serializer){
        $em = $this->getDoctrine()
                         ->getManager();
         
        
        $body = $request->getContent();
         $request = $serializer->deserialize($body, UserRequest::class, 'json');
         $request->setCreatedAt(new \DateTime("NOW"));
         $request->setStatus('pending');
         
         $user = $this->getDoctrine()
                        ->getRepository(Request::class)
                        ->find(1);

         $request->setUser($user);

         $em->persist($request);
         $em->flush();


         $response = array(
             'code' => 200,
             'message' => 'Request created',
             'errors' => null,
             'result' => null
         );
         return new JsonResponse($response, 200); 
        }
}