<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Vũ Xuân Bình <binh9aqktk@gmail.com>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\DEMO\AppInfo;

use OCP\AppFramework\App;

class Application extends App {
	public const APP_ID = 'demo';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}
}
