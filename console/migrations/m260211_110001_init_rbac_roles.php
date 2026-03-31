<?php

use yii\db\Migration;
use yii\rbac\DbManager;

/**
 * Migration to initialize RBAC roles and permissions based on the adapted role model.
 * Permissions:
 * - viewAnalytics: Access to view analytics, charts, and summary tables.
 * - editCompetitors: Access to the "Competitor Editor" section for data entry and modification.
 * - manageUsers: Full CRUD on users, including creating accounts and assigning roles.
 *
 * Roles:
 * - manager: Can only viewAnalytics.
 * - editor: Inherits manager + editCompetitors.
 * - admin: Inherits editor + manageUsers.
 */
class m260211_110001_init_rbac_roles extends Migration
{
    public function safeUp()
    {
        $auth = new DbManager();
        $auth->init();

        // Create permissions
        $viewAnalytics = $auth->createPermission('viewAnalytics');
        $viewAnalytics->description = 'View analytics, charts, and summary tables';
        $auth->add($viewAnalytics);

        $editCompetitors = $auth->createPermission('editCompetitors');
        $editCompetitors->description = 'Access to Competitor Editor for data entry';
        $auth->add($editCompetitors);

        $manageUsers = $auth->createPermission('manageUsers');
        $manageUsers->description = 'Manage users: CRUD, create accounts, assign roles';
        $auth->add($manageUsers);

        // Create roles
        $manager = $auth->createRole('manager');
        $manager->description = 'Manager: Only view analytics';
        $auth->add($manager);
        $auth->addChild($manager, $viewAnalytics);

        $editor = $auth->createRole('editor');
        $editor->description = 'Editor: Full view + edit competitors';
        $auth->add($editor);
        $auth->addChild($editor, $manager); // Inherits manager
        $auth->addChild($editor, $editCompetitors);

        $admin = $auth->createRole('admin');
        $admin->description = 'Administrator: Manage users + all editor access';
        $auth->add($admin);
        $auth->addChild($admin, $editor); // Inherits editor
        $auth->addChild($admin, $manageUsers);
    }

    public function safeDown()
    {
        $auth = new DbManager();
        $auth->init();

        // Remove roles
        $auth->remove($auth->getRole('admin'));
        $auth->remove($auth->getRole('editor'));
        $auth->remove($auth->getRole('manager'));

        // Remove permissions
        $auth->remove($auth->getPermission('manageUsers'));
        $auth->remove($auth->getPermission('editCompetitors'));
        $auth->remove($auth->getPermission('viewAnalytics'));
    }
}