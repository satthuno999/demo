<?php
namespace OCA\Demo\Tests\Unit\Controller;

require_once __DIR__ . '/NoteControllerTest.php';

class NoteApiControllerTest extends NoteControllerTest {

    public function setUp() {
        parent::setUp();
        $this->controller = new NoteApiController(
            'demo', $this->request, $this->service, $this->userId
        );
    }

}