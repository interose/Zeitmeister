<?php

namespace App\Lib;

use App\Entity\PublicHoliday;
use App\Repository\PublicHolidayRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PublicHolidayService
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly EntityManagerInterface $em,
        private readonly PublicHolidayRepository $repository,
    ) {
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     * @throws \Doctrine\DBAL\Exception
     */
    public function getPublicHolidays(int $year)
    {
        $publicHolidays = $this->repository->getByYear($year);

        if (count($publicHolidays) > 0) {
            return $publicHolidays;
        }

        return $this->doImport($year);
    }

    /**
     * @throws \Exception
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    private function doImport(int $year)
    {
        $response = $this->client->request(
            'GET',
            sprintf('https://get.api-feiertage.de/?years=%d&states=by', $year)
        );

        $statusCode = $response->getStatusCode();
        if (Response::HTTP_OK !== $statusCode) {
            throw new \Exception(sprintf('importPublicHolidays: got wrong status code from webservice! Expected 200 got %d', $statusCode));
        }

        $contentType = $response->getHeaders()['content-type'][0];
        if ('application/json' !== $contentType) {
            throw new \Exception(sprintf('importPublicHolidays: got wrong content type from webservice! Expected application/json got %s', $$contentType));
        }

        $result = [];

        $content = $response->toArray();
        foreach ($content['feiertage'] as $item) {
            $ph = new PublicHoliday();
            $ph->setHolidayDate(\DateTime::createFromFormat('Y-m-d', $item['date']));
            $ph->setName($item['fname']);

            $this->em->persist($ph);
            $this->em->flush();

            unset($ph);

            $result[$item['date']] = $item['fname'];
        }

        return $result;
    }
}
