<?php

namespace Nantarena\EventBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Nantarena\EventBundle\Entity\Event;
use Nantarena\EventBundle\Entity\EntryType;
use Nantarena\EventBundle\Entity\Tournament;

class LoadEventData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $na131 = new Event();
        $na131->setName('Nantarena 13.1');
        $na131->setCapacity(225);
        $na131->setStartDate(new \DateTime('2012-11-17 10:00'));
        $na131->setEndDate(new \DateTime('2012-11-18 17:00'));
        $na131->setStartRegistrationDate(new \DateTime('2012-10-23 14:00'));
        $na131->setEndRegistrationDate(new \DateTime('2012-11-16 23:00'));

        $types131 = new EntryType();
        $types131->setName("Joueur");
        $types131->setPrice(5);

        $na131->addEntryType($types131);

        $na132 = new Event();
        $na132->setName('Nantarena 13.2');
        $na132->setCapacity(250);
        $na132->setStartDate(new \DateTime('2013-03-23 10:00'));
        $na132->setEndDate(new \DateTime('2013-03-24 17:00'));
        $na132->setStartRegistrationDate(new \DateTime('2013-02-23 14:00'));
        $na132->setEndRegistrationDate(new \DateTime('2013-03-22 23:00'));

        $types132 = new EntryType();
        $types132->setName("Joueur");
        $types132->setPrice(5);

        $na132->addEntryType($types132);

        $na133 = new Event();
        $na133->setName('Nantarena 13.3');
        $na133->setCapacity(250);
        $na133->setStartDate(new \DateTime('2013-11-16 10:00'));
        $na133->setEndDate(new \DateTime('2013-11-17 17:00'));
        $na133->setStartRegistrationDate(new \DateTime('2013-07-16 14:00'));
        $na133->setEndRegistrationDate(new \DateTime('2013-11-15 23:00'));

        $types133 = new EntryType();
        $types133->setName("Joueur");
        $types133->setPrice(5);

        $types134 = new EntryType();
        $types134->setName("Manager");
        $types134->setPrice(3);

        $na133->addEntryType($types133);
        $na133->addEntryType($types134);

        $lol = new Tournament();
        $lol->setEvent($na133);
        $lol->setAdmin($this->getReference('user-1'));
        $lol->setExclusive(true);
        $lol->setGame($this->getReference('game-1'));
        $lol->setMaxTeams(16);
        $lol->setStartDate(new \DateTime('2013-11-16 14:00'));

        $na133->addTournament($lol);

        $this->addReference('event-1', $na131);
        $this->addReference('event-2', $na132);
        $this->addReference('event-3', $na133);

        $manager->persist($na131);
        $manager->persist($na132);
        $manager->persist($na133);

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
