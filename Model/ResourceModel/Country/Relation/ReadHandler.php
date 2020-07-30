<?php
/**
 * Copyright © OpenGento, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Opengento\CountryStorePhone\Model\ResourceModel\Country\Relation;

use InvalidArgumentException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Store\Model\Information;
use Magento\Store\Model\ScopeInterface;
use Opengento\CountryStore\Api\Data\CountryInterface;
use Opengento\CountryStorePhone\Model\CountryPhoneNumberList;

final class ReadHandler implements ExtensionInterface
{
    private ScopeConfigInterface $scopeConfig;

    private CountryPhoneNumberList $phoneNumberList;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CountryPhoneNumberList $phoneNumberList
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->phoneNumberList = $phoneNumberList;
    }

    public function execute($entity, $arguments = null): CountryInterface
    {
        if (!($entity instanceof CountryInterface)) {
            throw new InvalidArgumentException(
                'Argument name "entity" should be an instance of "' . CountryInterface::class . '".'
            );
        }

        $entity->setExtensionAttributes(
            $entity->getExtensionAttributes()->setPhoneNumber($this->resolvePhoneNumber($entity))
        );

        return $entity;
    }

    private function resolvePhoneNumber(CountryInterface $country): string
    {
        return $this->phoneNumberList->get($country) ?? (string) $this->scopeConfig->getValue(
            Information::XML_PATH_STORE_INFO_PHONE,
            ScopeInterface::SCOPE_STORE
        );
    }
}

