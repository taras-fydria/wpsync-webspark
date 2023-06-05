<?php

namespace WpsyncWebspark\Inc;

class AdminPage extends Singleton {
	const PAGE_TITLE = 'Wpsync Webspark';
	const PAGE_SLUG = 'wpsync-webspark';

	protected function __construct() {
		parent::__construct();
	}

	public static function register_page(): void {
		add_menu_page( self::PAGE_TITLE, self::PAGE_TITLE, 'manage_options', self::PAGE_SLUG, [ __CLASS__, 'render' ] );
	}

	public static function render(): void {
		$schedule           = wp_get_schedule( TaskSchedule::ACTION_NAME );
		$next_run_timestamp = wp_next_scheduled( TaskSchedule::ACTION_NAME );
        $formatted_date = date('H:i:s d:M:Y', $next_run_timestamp)
		?>
        <div class="col-wrap">
            <div class="form-field">
                <h2><span>Name: </span> <?php echo TaskSchedule::ACTION_NAME ?></h2>
            </div>
            <div class="form-field">
                <p><b>Schedule </b> <?php echo $schedule ?> </p>
            </div>
            <div class="form-field">
                <p><b>Next Start </b> <?php echo $formatted_date ?> </p>
            </div>
        </div>
		<?php
	}
}