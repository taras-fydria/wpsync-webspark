<?php

namespace WpsyncWebspark\Inc;

class AdminPage extends Singleton
{
    const PAGE_TITLE = 'Wpsync Webspark';
    const PAGE_SLUG = 'wpsync-webspark';

    protected function __construct()
    {
        parent::__construct();
    }

    public static function register_page(): void
    {
        add_menu_page(self::PAGE_TITLE, self::PAGE_TITLE, 'manage_options', self::PAGE_SLUG, [__CLASS__, 'render']);
    }

    public static function render(): void
    {
        echo 123;
    }
}