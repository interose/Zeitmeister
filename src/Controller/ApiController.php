<?php

namespace App\Controller;

use App\Entity\TimeLog;
use App\Lib\PayloadTimeTrackDto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
#[IsGranted('ROLE_API')]
final class ApiController extends AbstractController
{
    #[Route('/time/add', name: 'app_api_time_add', methods: ['POST'])]
    public function add(#[MapRequestPayload] PayloadTimeTrackDto $payloadTimeTrackDto, EntityManagerInterface $em): JsonResponse
    {
        $obj = new TimeLog();
        $obj->setTracker($this->getUser());
        $obj->setEvent($payloadTimeTrackDto->getEventTypeEnum());
        $obj->setCreated($payloadTimeTrackDto->getTime());

        $em->persist($obj);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_CREATED);
    }
}
