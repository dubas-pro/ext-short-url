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

use Espo\Core\Exceptions\BadRequest;
use Espo\ORM\Entity;
use Espo\Core\Hook\Hook\BeforeSave;
use Espo\ORM\Repository\Option\SaveOptions;
use Espo\ORM\EntityManager;
use Espo\Core\Utils\Metadata;

use Espo\Core\Utils\Config;
/**
 * @implements BeforeSave<Entity>
 */
class GenerateUrl implements BeforeSave
{
    private Config $config;
    private $metadata;

    public function __construct(
        Config $config,
        private readonly EntityManager $entityManager,
        Metadata $metadata
    ) {
        $this->config = $config;
        $this->metadata = $metadata;
    }

    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
        $toProcess =
            $entity->isNew() ||
            $entity->isAttributeChanged('alias');

        if (!$toProcess) {
            return;
        }

        $record = $this->entityManager->getRDBRepository('ShortUrl')
            ->where([
                'alias' => $entity->get('alias'),
                'id!=' => $entity->get('id'),
            ])->findOne();

        if ($record) {
            throw new BadRequest('Short URL with this alias already exists.');
        }

        $customDomain = $this->metadata->get(['app', 'dubasShortUrlOptions', 'customDomain']);

        if ($customDomain === true) {
            $domain = $this->metadata->get(['app', 'dubasShortUrlOptions', 'domain']);
            if (substr($domain, -1) !== '/') {
                $domain = $domain . '/';
            }
        } else {
            $domain = $this->config->get('siteUrl');

            if (substr($domain, -1) !== '/') {
                $domain = $domain . '/';
            }
            $domain = $domain . 'link/';
        }

        $entity->set('shortUrl', $domain . $entity->get('alias'));
    }
}
