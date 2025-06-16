<?php

namespace App\Controller;

use App\Repository\TimeLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class FrontendController extends AbstractController
{
    #[Route('/', name: 'app_frontend_index')]
    public function index(
        TimeLogRepository $repository,
        #[MapQueryParameter] ?int $month = null,
        #[MapQueryParameter] ?int $year = null,
    ): Response {
        if (null === $month) {
            $month = date('m');
        }

        if (null === $year) {
            $year = date('Y');
        }

        try {
            $timeLogs = [];
            $timeLogs = $repository->findBySearch($this->getUser(), $month, $year);
        } catch (\Exception $e) {

        }

        return $this->render('frontend/index.html.twig', [
            'month' => $month,
            'year' => $year,
            'timeLogs' => $timeLogs,
        ]);
    }
}
