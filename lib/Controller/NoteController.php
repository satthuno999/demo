<?php
 namespace OCA\Demo\Controller;

 use OCP\IRequest;
 use OCP\AppFramework\Http;
 use OCP\AppFramework\Http\DataResponse;
 use OCP\AppFramework\Controller;
 
 use OCA\Demo\Service\NoteService;

 class NoteController extends Controller {
    private $service;
    private $userId;

     public function __construct(string $AppName, IRequest $request,NoteService $service, $UserId){
         parent::__construct($AppName, $request);
         $this->service = $service;
         $this->userId = $UserId;
     }

     /**
      * @NoAdminRequired
      */
     public function index() {
        return new DataResponse($this->service->findAll($this->userId));
     }

     /**
      * @NoAdminRequired
      *
      * @param int $id
      */
     public function show(int $id) {
         return $this->handleNotFound(function () use ($id) {
            return $this->service->find($id, $this->userId);
        });
     }

     /**
      * @NoAdminRequired
      *
      * @param string $title
      * @param string $content
      */
     public function create(string $title, string $content) {
         return $this->service->create($title, $content, $this->userId);
     }

     /**
      * @NoAdminRequired
      *
      * @param int $id
      * @param string $title
      * @param string $content
      */
     public function update(int $id, string $title, string $content) {
         return $this->handleNotFound(function () use ($id, $title, $content) {
            return $this->service->update($id, $title, $content, $this->userId);
        });
     }

     /**
      * @NoAdminRequired
      *
      * @param int $id
      */
     public function destroy(int $id) {
         return $this->handleNotFound(function () use ($id) {
            return $this->service->delete($id, $this->userId);
        });
     }

 }