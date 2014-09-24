<?php

namespace Nantarena\EventBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Nantarena\EventBundle\Entity\Event;
use Nantarena\EventBundle\Entity\Tournament;

class LoadEventData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $na142 = new Event();
        $na142->setName('Nantarena 14.2');
        $na142->setCapacity(250);
        $na142->setStartDate(new \DateTime('2014-11-15 10:00'));
        $na142->setEndDate(new \DateTime('2014-11-16 20:00'));
        $na142->setStartRegistrationDate(new \DateTime('2014-09-01 14:00'));
        $na142->setEndRegistrationDate(new \DateTime('2014-11-07 23:00'));

        $lol = new Tournament();
        $lol->setEvent($na142);
        $lol->setAdmin($this->getReference('user-1'));
        $lol->setGame($this->getReference('game-1'));
        $lol->setMaxTeams(24);
        $lol->setStartDate(new \DateTime('2014-11-15 14:00'));
        $lol->setProfessional(false);
        $lol->setPrice(10);

        $lolpro = new Tournament();
        $lolpro->setEvent($na142);
        $lolpro->setAdmin($this->getReference('user-1'));
        $lolpro->setGame($this->getReference('game-1'));
        $lolpro->setMaxTeams(16);
        $lolpro->setStartDate(new \DateTime('2014-11-15 14:00'));
        $lolpro->setProfessional(true);
        $lolpro->setPrice(30);

        $csgo = new Tournament();
        $csgo->setEvent($na142);
        $csgo->setAdmin($this->getReference('user-1'));
        $csgo->setGame($this->getReference('game-2'));
        $csgo->setMaxTeams(24);
        $csgo->setStartDate(new \DateTime('2014-11-15 14:00'));
        $csgo->setProfessional(false);
        $csgo->setPrice(10);

        $na142->addTournament($lol);
        $na142->addTournament($lolpro);
        $na142->addTournament($csgo);

        $this->addReference('event-142', $na142);

        $manager->persist($na142);

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 3;
    }
}
