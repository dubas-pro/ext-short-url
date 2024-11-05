<?php

namespace Espo\Modules\DubasShortUrl\Tools\ShortUrl;

use Espo\Core\Api\Action;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Api\ResponseWrapper;
use Slim\Psr7\Factory\ResponseFactory;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Error;
use Espo\Core\Exceptions\NotFound;
use Espo\ORM\EntityManager;

class Service implements Action
{
    public function __construct(
        private readonly EntityManager $entityManager
    ) {
    }

    /**
     *  GET api/v1/l/001
     */
    public function process(Request $request): Response
    {
        if (empty($request->getRouteParam('link'))) {
            throw new BadRequest('Wrong link.');
        }

        $link = $request->getRouteParam('link');
        $record = $this->entityManager->getRDBRepository('ShortUrl')->where([
            'alias' => $link,
        ])->findOne();

        if (!$record) {
            throw new NotFound("We couldn't find link.");
        }

        $expirationDate = $record->get('expirationDate');
        $today = date('Y-m-d');
        if (!empty($expirationDate) && $today >= $expirationDate) {
            throw new Error('Link expired.');
        }

        $clicks = $record->get('clicks');
        $record->set('clicks', ++$clicks);
        $this->entityManager->saveEntity($record);

        header('Location: ' . $record->get('name'), true, 302); exit;
    }
}
