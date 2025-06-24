<?php

namespace App\Controller;

use App\Lib\PublicHolidayService;
use App\Lib\SqlDataTransformer;
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
        SqlDataTransformer $transformer,
        PublicHolidayService $holidayService,
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
            $holidays = $holidayService->getPublicHolidays($year);
            $timeLogs = $repository->findBySearch($this->getUser(), $month, $year);
            if (0 == count($timeLogs)) {
                $timeLogs = $repository->getEmpty($month, $year);
            }
            $timeLogs = $transformer->setupData($timeLogs, $holidays);
        } catch (\Exception $e) {
            return $this->render('frontend/indexError.html.twig', [
                'message' => $e->getMessage(),
            ]);
        }

        return $this->render('frontend/index.html.twig', [
            'month' => $month,
            'year' => $year,
            'timeLogs' => $timeLogs,
        ]);
    }
}
