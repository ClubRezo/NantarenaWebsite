<?php

namespace Nantarena\EventBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Nantarena\EventBundle\Entity\Entry;
use Nantarena\EventBundle\Entity\Team;

class LoadTeamsData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $team1 = new Team();
        $team1->setEvent($this->getReference('event-3'));
        $team1->setCreator($this->getReference('user-1'));
        $team1->setName('Equipe test');
        $team1->setTag('test');
        $team1->addMember($this->getReference('user-1'));
        $team1->addTournament($this->getReference('event-3')->getTournaments()[0]);

        $this->addReference('team-1', $team1);
        $manager->persist($team1);

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 5;
    }
}
