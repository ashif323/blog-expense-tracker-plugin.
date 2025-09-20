<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists('BET_Capabilities') ) {

    class BET_Capabilities {
        const CAP = 'bet_manage_blog_expenses';

        public static function add_caps() {
            $roles = ['administrator'];
            foreach ($roles as $role_name) {
                $role = get_role($role_name);
                if ($role && ! $role->has_cap(self::CAP)) {
                    $role->add_cap(self::CAP);
                }
            }
        }

        public static function remove_caps() {
            $roles = ['administrator'];
            foreach ($roles as $role_name) {
                $role = get_role($role_name);
                if ($role && $role->has_cap(self::CAP)) {
                    $role->remove_cap(self::CAP);
                }
            }
        }
    }

}
