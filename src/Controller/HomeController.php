<?php

namespace App\Controller;

use App\Repository\CampaignRepository;
use App\Repository\PaymentRepository;
use App\Repository\ParticipantRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(CampaignRepository $campaignRepository,PaymentRepository $paymentRepository, ParticipantRepository $participantRepository): Response
   {
    $campaigns = $campaignRepository->findAll();

    $participants = $participantRepository->FindBy(['campaign_id' => $campaigns]);

    $payments = $paymentRepository->findBy(['participant_id'=>$participants]);

    return $this->render('home/index.html.twig', [
        'controller_name' => 'HomeController',
        'campaigns' => $campaigns,
        'participants' => $participants,
        'payments' => $payments,
        ]);
    }

}