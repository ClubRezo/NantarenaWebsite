<?php

namespace Nantarena\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Nantarena\UserBundle\Entity\Group;

class LoadGroupData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $adminsGroup = new Group('Administrateurs');
        $adminsGroup->addRole('ROLE_SUPER_ADMIN');

        $staffGroup = new Group('Staff');
        $staffGroup->addRole('ROLE_USER_ADMIN_USERS');

        $usersGroup = new Group('Membres');
        $usersGroup->addRole('ROLE_USER');
        $usersGroup->setDefault(true);

        $moderatorsGroup = new Group('Modérateurs');
        $moderatorsGroup->addRole('ROLE_FORUM_MODERATE');

        $this->addReference('group-admins', $adminsGroup);
        $this->addReference('group-staffs', $staffGroup);
        $this->addReference('group-users', $usersGroup);
        $this->addReference('group-moderators', $moderatorsGroup);

        $manager->persist($adminsGroup);
        $manager->persist($staffGroup);
        $manager->persist($usersGroup);
        $manager->persist($moderatorsGroup);

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 1;
    }
}
