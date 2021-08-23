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

namespace Espo\Modules\DubasShortUrl\Hooks\ShortUrl;

use Espo\ORM\Entity;

class GenerateAlias extends \Espo\Core\Hooks\Base
{
    public function beforeSave(Entity $entity, array $options = [])
    {
        if (empty($entity->get('alias')) || $entity->isAttributeChanged('alias')) {
            $length = $this->getMetadata()->get(['app', 'dubasShortUrlOptions', 'length']);
            if (!$length) {
                $length = 5;
            }
            $alias = $this->generateRandomString($length);

            $record = $this->getEntityManager()->getRepository('ShortUrl')
                ->where([
                    'alias' => $alias,
                ])->findOne();
            if (!empty($record->id)) {
                $alias = $this->generateRandomString($length);
            }

            $entity->set('alias', $alias);
        }
    }

    protected function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-_';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
