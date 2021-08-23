<?php

/*
 * This file is part of the Dubas Short Url - EspoCRM extension.
 *
 * DUBAS S.C. - contact@dubas.pro
 * Copyright (C) 2021 Arkadiy Asuratov, Emil Dubielecki
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Espo\Modules\DubasShortUrl\Controllers;

use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\NotFound;

class ShortUrl extends \Espo\Core\Templates\Controllers\Base
{
    /**
     *  GET api/v1/l/001
     */
    public function getActionRedirect($params, $data, $request, $response)
    {
        if (empty($params['link'])) {
            return 'Wrong link.';
            throw new BadRequest('Wrong link.');
        }

        $entityManager = $this->getEntityManager();

        $link = $params['link'];
        $record = $entityManager->getRepository('ShortUrl')->where([
            'alias' => $link,
        ])->findOne();

        if (!$record) {
            throw new NotFound("We couldn't find link.");
        }

        $expirationDate = $record->get('expirationDate');
        $today = date('Y-m-d');
        if (!empty($expirationDate) && $today >= $expirationDate) {
            return 'Link expired.';
        }

        $clicks = $record->get('clicks');
        $record->set('clicks', ++$clicks);
        $entityManager->saveEntity($record);

        $response->setHeader('Location', $record->get('name'));
        return true;
    }
}
