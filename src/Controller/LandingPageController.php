<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Commande;
use App\Entity\Livraison;
use App\Entity\Produit;
use App\Form\CommandeType;
use App\Form\CommandeType2;
use App\Repository\CommandeRepository;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Validator\Constraints\Blank;
use Symfony\Component\Validator\Constraints\IsNull;
use Symfony\Config\FrameworkConfig;
use Symfony\Component\Validator\Constraints as Assert;

class LandingPageController extends AbstractController
{
   /**
     * @Route("/", name="landing_page")
     * @throws \Exception
     */
    public function index(Request $request, ProduitRepository $produitRepository)
    {

        $produits = $produitRepository->findAll();

        $commande = new Commande();
        $client = new Client();
        $livraison = new Livraison();

        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        $produitId = $request->get('produit');
        $produit = $produitRepository->findOneBy(['id' => $produitId]);
        $commande->setProduit($produit);
        
        
        if ($form->isSubmitted() && $form->isValid()) {

            if($commande->getLivraison()->getPrenom() == null && $commande->getLivraison()->getNom() == null && $commande->getLivraison()->getAdresseLivraison() == null){
                
                $prenom = $commande->getClient()->getPrenom();
                $commande->getLivraison()->setPrenom($prenom);

                $nom = $commande->getClient()->getNom();
                $commande->getLivraison()->setNom($nom);

                $adresse = $commande->getClient()->getAdresse();
                $commande->getLivraison()->setAdresseLivraison($adresse);

                if($commande->getClient()->getComplement() !== null){
                    
                    $complement = $commande->getClient()->getComplement();
                    $commande->getLivraison()->setComplementLivraison($complement);

                }

                $ville = $commande->getClient()->getVille();
                $commande->getLivraison()->setVille($ville);

                $codePostal = $commande->getClient()->getCodepostal();
                $commande->getLivraison()->setCodePostal($codePostal);

                $telephone = $commande->getClient()->getTelephone();
                $commande->getLivraison()->setTelephone($telephone);

            }
            
            $type = $request->get('type');
            $token = 'mJxTXVXMfRzLg6ZdhUhM4F6Eutcm1ZiPk4fNmvBMxyNR4ciRsc8v0hOmlzA0vTaX';
 
            $prixProduit = $produit->getPrixPromo();
            $commande->setMontant($prixProduit);

            $commande->setStatus('WAITING');
            $commande->setType($type);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($commande);
            $entityManager->flush();

            $clientInfo = $commande->getClient();
            $livraisonInfo = $commande->getLivraison();

            $datas = ['order' => [
                        'id' => strval($commande->getId()),
                        'product' => $produit->getNomProduit(),
                        'payment_method' => strtolower($commande->getType()),
                        'status' => $commande->getStatus(),
                        'client' => [
                            'firstname' => $clientInfo->getPrenom(),
                            'lastname' => $clientInfo->getNom(),
                            'email' => $clientInfo->getEmail(),
                        ],
                        'addresses' => [
                            'billing' => [
                                'address_line1' => $clientInfo->getAdresse(),
                                'address_line2' => strval($clientInfo->getComplement()),
                                'city' => $clientInfo->getVille(),
                                'zipcode' => strval($clientInfo->getCodepostal()),
                                'country' => $clientInfo->getPays(),
                                'phone' => strval($clientInfo->getTelephone()),
                            ],
                            'shipping' =>  [
                                'address_line1' => strval($livraisonInfo->getAdresseLivraison()),
                                'address_line2' => strval($livraisonInfo->getComplementLivraison()),
                                'city' => strval($livraisonInfo->getVille()),
                                'zipcode' => strval($livraisonInfo->getCodePostal()),
                                'country' => $livraisonInfo->getPays(),
                                'phone' => strval($livraisonInfo->getTelephone()),
                            ]
                        ],
                    ]
            ];

            $datasJson = json_encode($datas);

            // dd($datasJson);
           
            $httpClient = HttpClient::create([], 6, 50);
            $response = $httpClient->request('POST', 'https://api-commerce.simplon-roanne.com/order', 
                ['headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-type' => 'application/json'
                    ],
                'body' => $datasJson,
                ]);

            
                
            $statusCode = $response->getStatusCode();
            $contentType = $response->getHeaders()['content-type'][0];
            $content = $response->getContent();
            $content = $response->toArray();


            $orderId = $content['order_id'];
            $commande->setOrderId($orderId);
            
            
            $entityManager->persist($commande);
            $entityManager->flush();

            
            if($type === 'paypal'){

                return $this->redirectToRoute('paypal',[
                    'id' => $commande->getId(),
                    'commande' => $commande
                ]);

            }else{

                return $this->redirectToRoute('payment',[
                    'id' => $commande->getId(),
                ]);

            }

        }


        return $this->render('landing_page/index_new.html.twig', [

            'commande' => $commande,
            'form' => $form->createView(),
            'client' => $client,
            'livraison' => $livraison,
            'produits' => $produits,

        ]);
    }

    /**
     * @Route("/confirmation", name="confirmation")
     */
    public function confirmation()
    {
        return $this->render('landing_page/confirmation.html.twig', [

        ]);
    }

     /**
     * @Route("/payment/{id}", name="payment")
     */
    public function payment(Commande $commande,Request $request, MailerInterface $mailer){
        
        $token = 'mJxTXVXMfRzLg6ZdhUhM4F6Eutcm1ZiPk4fNmvBMxyNR4ciRsc8v0hOmlzA0vTaX';

        $clientMail = $commande->getClient()->getEmail();

        $form = $this->createForm(CommandeType2::class, $commande);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
               
        //    dd($commande->getMontant());
                // STRIPE
                \Stripe\Stripe::setApiKey('sk_test_51IuuelAX6HnD6DxpT9V57cSRES5zzoJiKXzrMKYvNWwji4xNC7mqZKZqdKRs9f2A1pDZTHkTOaCmx0Z0nitMeLrF00S73tVOD2');
                $paymentIntent = \Stripe\PaymentIntent::create([
                    'amount' => $commande->getMontant(),
                    'currency' => 'eur',
                ]);
            
                //envoi à la base de données

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($commande);
                $entityManager->flush();
                
                $idOrderApi = $commande->getOrderId();

                $data = [
                    "status" => "PAID"
                ];

                $data = json_encode($data);

                $httpClient = HttpClient::create([], 6, 50);
                $response = $httpClient->request('POST', 'https://api-commerce.simplon-roanne.com/order/'. $idOrderApi . '/status', 
                    ['headers' => [
                        'Authorization' => 'Bearer ' . $token,
                        'Content-type' => 'application/json'
                        ],
                    'body' => $data,
                    ]);
                    
                $statusCode = $response->getStatusCode();
                $contentType = $response->getHeaders()['content-type'][0];
                $content = $response->getContent();
                $content = $response->toArray();
                $commande->setStatus('PAID');
                $entityManager->persist($commande);
                $entityManager->flush();
                $this->sendMail($mailer, $clientMail);

                //redirection
                return $this->redirectToRoute('confirmation', [
                    'id' => $commande->getId(),
                
            ]);

        }
            return $this->render('landing_page/payment.html.twig',[
                'commande' => $commande,
                'form' => $form->createView(),

            ]);
  }
    
        /**
         * @Route("/paypal/{id}", name="paypal")
         */
   public function paypal(Commande $commande, Request $request, MailerInterface $mailer){

        $token = 'mJxTXVXMfRzLg6ZdhUhM4F6Eutcm1ZiPk4fNmvBMxyNR4ciRsc8v0hOmlzA0vTaX';

        $clientMail = $commande->getClient()->getEmail();
  
        if ($request->isMethod('post')) {
                //envoi à la base de données
                
                $idOrderApi = $commande->getOrderId();

                $data = [
                    "status" => "PAID"
                ];

                $data = json_encode($data);

                $httpClient = HttpClient::create([], 6, 50);
                $response = $httpClient->request('POST', 'https://api-commerce.simplon-roanne.com/order/'. $idOrderApi . '/status', 
                    ['headers' => [
                        'Authorization' => 'Bearer ' . $token,
                        'Content-type' => 'application/json'
                        ],
                    'body' => $data,
                    ]);
                    
                $statusCode = $response->getStatusCode();
                $contentType = $response->getHeaders()['content-type'][0];
                $content = $response->getContent();
                $content = $response->toArray();
                $commande->setStatus('PAID');

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($commande);
                $entityManager->flush();

                $this->sendMail($mailer, $clientMail);

                return $this->redirectToRoute('confirmation', [
                    'id' => $commande->getId(),
                ]);

            }

        return $this->render('landing_page/paypal.html.twig', [

            'commande' => $commande,

        ]);
    }

    public function sendMail(MailerInterface $mailer, $clientMail){

        $email = (new Email())
        ->from('battleoffice@contact.com')
        ->to($clientMail)
        //->cc('cc@example.com')
        //->bcc('bcc@example.com')
        //->replyTo('fabien@example.com')
        //->priority(Email::PRIORITY_HIGH)
        ->subject('Bonjour !')
        ->text('Votre commande est passée, bravo !');

        $mailer->send($email);


    }


}