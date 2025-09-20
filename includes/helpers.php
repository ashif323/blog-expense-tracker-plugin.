<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class BET_Utils {
    public static function sanitize_text( $value, $max = 255 ) {
        $value = sanitize_text_field( (string) $value );
        return mb_substr( $value, 0, $max );
    }

    public static function sanitize_money( $value ) {
        $v = preg_replace( '/[^0-9.\-]/', '', (string) $value );
        return (float) $v;
    }

    public static function sanitize_date( $value ) {
        $d = date_create( $value );
        return $d ? $d->format( 'Y-m-d' ) : gmdate( 'Y-m-d' );
    }

    public static function current_user_can_manage() {
        return current_user_can( 'manage_blog_expenses' );
    }

    public static function require_manage_cap() {
        if ( ! self::current_user_can_manage() ) {
            wp_die( __( 'You do not have permission to manage expenses.', 'blog-expense-tracker' ), 403 );
        }
    }
}