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

class GenerateUrl extends \Espo\Core\Hooks\Base
{
    private $container;

    public function __construct(\Espo\Core\Container $container)
    {
        $this->container = $container;
    }

    public function beforeSave(Entity $entity, array $options = [])
    {
        if ($entity->isNew() || $entity->isAttributeChanged('alias')) {
            $record = $this->getEntityManager()->getRepository('ShortUrl')
                ->where([
                    'alias' => $entity->get('alias'),
                    'id!=' => $entity->get('id'),
                ])->findOne();
            if (!empty($record)) {
                throw new BadRequest('Short URL with this alias already exists.');
            }

            $alias = $entity->get('alias');
            $customDomain = $this->getMetadata()->get(['app', 'dubasShortUrlOptions', 'customDomain']);
            if ($customDomain === true) {
                $domain = $this->getMetadata()->get(['app', 'dubasShortUrlOptions', 'domain']);
                if (substr($domain, -1) !== '/') {
                    $domain = $domain . '/';
                }
            } else {
                $config = $this->getContainer()->get('config');
                $domain = $config->get('siteUrl');

                if (substr($domain, -1) !== '/') {
                    $domain = $domain . '/';
                }
                $domain = $domain . 'link/';
            }

            $entity->set('shortUrl', $domain . $entity->get('alias'));
        }
    }

    protected function getContainer()
    {
        return $this->container;
    }
}
