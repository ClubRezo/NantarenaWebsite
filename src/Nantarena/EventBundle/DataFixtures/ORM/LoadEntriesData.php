<?php

namespace Nantarena\EventBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Nantarena\EventBundle\Entity\Entry;

class LoadEntriesData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $adminEntry = new Entry();
        $adminEntry->setEntryType($this->getReference('event-3')->getEntryTypes()[0]);
        $adminEntry->setRegistrationDate(new \DateTime());
        $adminEntry->setUser($this->getReference('user-1'));


        $this->addReference('entry-1', $adminEntry);
        $manager->persist($adminEntry);

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 4;
    }
}
