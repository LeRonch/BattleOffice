<?php

namespace App\Controller;

use App\Entity\Payment;
use App\Entity\Campaign;
use App\Form\PaymentType;
use DateTime;
use App\Repository\PaymentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/payment")
 */
class PaymentController extends AbstractController
{
    /**
     * @Route("/", name="payment_index", methods={"GET"})
     */
    public function index(PaymentRepository $paymentRepository): Response
    {
        return $this->render('payment/index.html.twig', [
            'payments' => $paymentRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new/{id}", name="payment_new", methods={"GET","POST"})
     */
    public function new(Campaign $campaign, Request $request, PaymentRepository $paymentRepository): Response
    {
        
        $payment = new Payment();
        $form = $this->createForm(PaymentType::class, $payment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // STRIPE
            \Stripe\Stripe::setApiKey('sk_test_51IuuelAX6HnD6DxpT9V57cSRES5zzoJiKXzrMKYvNWwji4xNC7mqZKZqdKRs9f2A1pDZTHkTOaCmx0Z0nitMeLrF00S73tVOD2');
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $payment->getAmount()*100,
                'currency' => 'eur',
            ]);
            $output = [
                'clientSecret' => $paymentIntent->client_secret,
            ];
            // // Recupération de la campagne grace a l'id en get
            // $idCampaign = explode('=', $request->getQueryString())[1];
            // $campaignrepository = $this->getDoctrine()->getRepository(Campaign::class);
            // $campaign =  $campaignrepository->find(intval($idCampaign));
            
            //set les parametres manquants
            $payment->setCreatedAt(new DateTime());
            $payment->setUpdatedAt(new DateTime());
            $payment->getParticipantId()->setCampaignId($campaign);

            //envoi à la base de données
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($payment);
            $entityManager->flush();

            //redirection
            return $this->redirectToRoute('campaign_show', ['id' => $campaign->getId()]);
        }

        return $this->render('payment/new.html.twig', [
            'payment' => $payment,
            'form' => $form->createView(),
            'campaign' => $campaign
        ]);
    }

    /**
     * @Route("/{id}", name="payment_show", methods={"GET"})
     */
    public function show(Payment $payment): Response
    {
        return $this->render('payment/new.html.twig', [
            'payment' => $payment,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="payment_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Payment $payment): Response
    {
        $form = $this->createForm(PaymentType::class, $payment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('payment_index');
        }

        return $this->render('payment/edit.html.twig', [
            'payment' => $payment,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="payment_delete", methods={"POST"})
     */
    public function delete(Request $request, Payment $payment): Response
    {
        if ($this->isCsrfTokenValid('delete'.$payment->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($payment);
            $entityManager->flush();
        }

        return $this->redirectToRoute('payment_index');
    }
}
