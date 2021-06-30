<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Commande;
use App\Entity\Livraison;
use App\Entity\Produit;
use App\Form\CommandeType;
use App\Repository\CommandeRepository;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Config\FrameworkConfig;
class LandingPageController extends AbstractController
{
    /**
     * @Route("/", name="landing_page")
     * @throws \Exception
     */
    public function index(Request $request, ProduitRepository $produitRepository)
    {
        try {
            $httpClient = HttpClient::create(['http_version'=> '2.0'], 6, 50);
            $response = $httpClient->request('POST', 'https://api-commerce.simplon-roanne.com', 
                ['headers' => 
                ['Authorization' => 
                'Bearer '. 'mJxTXVXMfRzLg6ZdhUhM4F6Eutcm1ZiPk4fNmvBMxyNR4ciRsc8v0hOmlzA0vTaX'],
                
                ]);

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
                     $prixProduit = $produit->getPrixPromo();
                    $commande->setMontant($prixProduit);
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($commande);
                    $entityManager->flush();
        
                    return $this->redirectToRoute('payment',[
                        'id'=> $commande->getId()
                    ]);
                }
                return $this->render('landing_page/index_new.html.twig', [
                    'commande' => $commande,
                    'client'=> $client,
                    'livraison'=> $livraison,
                    'produits' => $produits,
                    'form' => $form->createView(),

                ]);
        
            $statusCode = $response->getStatusCode();
            $contentType = $response->getHeaders()['content-type'][0];
            $content = $response->getContent();
            $content = $response->toArray();
        } catch (\Throwable $th) {
            throw $th;
        }

        return $this->render('landing_page/index_new.html.twig', [

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
     * @Route("/payment/{id}", name="payment",methods={"GET"})
     */
    public function payment(Commande $commande){
        return $this->render('landing_page/payment.html.twig',[
            'commande' => $commande,
        ]);
    }
     /**
     * @Route("/payment_process/{id}", name="payment_process",methods={"POST"})
     */
   

    public function payment_process(Request $request, Commande $commande){
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

            //redirection
            return $this->redirectToRoute('payment', ['id' => $commande->getId()]);
        
    }
}
