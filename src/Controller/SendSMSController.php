<?php

namespace App\Controller;

use Goxens\Goxens;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SendSMSController extends AbstractController
{
    /**
     * @Route("/send/sms", name="app_send_ssms")
     */
    public function index(): Response
    {
        $apiKey = 'ROD-T20902RQ7YXFRJLE5K3O28GDU7KKEWWAX72';
        $userUid = 'RPQWD4';

        $goxens =  new Goxens($apiKey, $userUid);

        $sender = 'Cheikh DIENG'; // Valid sender name
        $number = '+221773043248';
        $message = 'Bienvenue sur Goxens';

        $send = $goxens->sendSms($apiKey, $userUid, $number, $sender, $message);

        dd($send);
        // $solde = $goxens->verifySolde($apiKey);

        // dd($solde);

        return $this->render('send_sms/index.html.twig', [
            'controller_name' => 'SendSMSController',
            'send' => $send
        ]);
    }
}
