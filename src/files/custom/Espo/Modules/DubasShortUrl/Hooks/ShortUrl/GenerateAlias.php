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
use Espo\Core\Hook\Hook\BeforeSave;
use Espo\ORM\Repository\Option\SaveOptions;
use Espo\ORM\EntityManager;
use Espo\Core\Utils\Metadata;

/**
 * @implements BeforeSave<Entity>
 */
class GenerateAlias implements BeforeSave
{
    private $metadata;

    public function __construct(
        private readonly EntityManager $entityManager,
        Metadata $metadata
    ) {
        $this->metadata = $metadata;
    }

    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        $toProcess =
            !$entity->get('alias');

        if (!$toProcess) {
            return;
        }

        $length = $this->metadata->get(['app', 'dubasShortUrlOptions', 'length']);
        if (!$length) {
            $length = 5;
        }

        /** @var static $alias */
        $alias = $this->generateRandomString($length);

        /** @var Entity $record */
        $record = $this->entityManager->getRDBRepository('ShortUrl')
            ->where([
                'alias' => $alias,
            ])
            ->findOne();

        if ($record) {
            $alias = $this->generateRandomString($length);
        }

        $entity->set('alias', $alias);
    }

    protected function generateRandomString(int $length = 10): string
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
