<?php


/**
 * File Name: UserRank.php
 * Written By: Nikita Petko
 * Description: Rank of a user I suppose
 */

namespace Alphaland\Users {

    /**
     * Refers to the mock RoleSet of a user in the DataBase.
     * The abstract class here is apparently how you do Enums in PHP.
     */
    abstract class UserRank
    {
        /**
         * The user is a visitor to the site.
         */
        const Visitor = -1;

        /**
         * The user is a regular member that signed up.
         */
        const Member = 0;

        /**
         * The user has more privilages
         */
        const Moderator = 1;

        /**
         * Too lazy to doc
         */
        const Administrator = 2;

        /**
         * Too lazy to doc
         */
        const Owner = 3;

        public static function FromInt(int $id)
        {
            switch ($id) {
                case 0:
                    return UserRank::Member;
                case 1:
                    return UserRank::Moderator;
                case 2:
                    return UserRank::Administrator;
                case 3:
                    return UserRank::Owner;
                case -1:
                default:
                    return UserRank::Visitor;
            }
        }
    }
}
