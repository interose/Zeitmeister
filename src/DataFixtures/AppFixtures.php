<?php

namespace App\DataFixtures;

use App\Config\TimeLogEvent;
use App\Entity\TimeLog;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    protected $faker;

    public function __construct(private readonly UserPasswordHasherInterface $userPasswordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $this->faker = Factory::create();

        $user = new User();
        $user->setUsername('admin');
        $user->setRoles(['ROLE_USER', 'ROLE_API', 'ROLE_ADMIN']);
        $user->setToken(bin2hex(random_bytes(32)));
        $user->setPassword(
            $this->userPasswordHasher->hashPassword($user, 'test123')
        );
        $manager->persist($user);
        $manager->flush();


        $startDate = new \DateTime('2025-06-02');
        $endDate = new \DateTime('2025-06-07');

        $interval = \DateInterval::createFromDateString('1 day');
        $period = new \DatePeriod($startDate, $interval, $endDate);

        /** @var \DateTime $dt */
        foreach ($period as $dt) {

            $start1 = clone $dt;
            $start1->setTime(8, $this->faker->numberBetween(1, 59), 0);
            $obj = new TimeLog();
            $obj->setCreated($start1);
            $obj->setEvent(TimeLogEvent::CheckIn);
            $obj->setTracker($user);
            $manager->persist($obj);
            unset($start1);

            $start1 = clone $dt;
            $start1->setTime(11, $this->faker->numberBetween(1, 59), 0);
            $obj = new TimeLog();
            $obj->setCreated($start1);
            $obj->setEvent(TimeLogEvent::CheckOut);
            $obj->setTracker($user);
            $manager->persist($obj);
            unset($start1);

            $start1 = clone $dt;
            $start1->setTime(12, $this->faker->numberBetween(1, 59), 0);
            $obj = new TimeLog();
            $obj->setCreated($start1);
            $obj->setEvent(TimeLogEvent::CheckIn);
            $obj->setTracker($user);
            $manager->persist($obj);
            unset($start1);

            $start1 = clone $dt;
            $start1->setTime(16, $this->faker->numberBetween(1, 59), 0);
            $obj = new TimeLog();
            $obj->setCreated($start1);
            $obj->setEvent(TimeLogEvent::CheckOut);
            $obj->setTracker($user);
            $manager->persist($obj);
            unset($start1);

            if ($dt->format('Y-m-d') === '2025-07-03') {
                $start1 = clone $dt;
                $start1->setTime(17, $this->faker->numberBetween(1, 59), 0);
                $obj = new TimeLog();
                $obj->setCreated($start1);
                $obj->setEvent(TimeLogEvent::CheckIn);
                $obj->setTracker($user);
                $manager->persist($obj);
                unset($start1);

                $start1 = clone $dt;
                $start1->setTime(18, $this->faker->numberBetween(1, 59), 0);
                $obj = new TimeLog();
                $obj->setCreated($start1);
                $obj->setEvent(TimeLogEvent::CheckOut);
                $obj->setTracker($user);
                $manager->persist($obj);
                unset($start1);
            }

            $manager->flush();
        }


    }
}
