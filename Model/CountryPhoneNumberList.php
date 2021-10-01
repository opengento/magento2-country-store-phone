<?php
/**
 * Copyright © OpenGento, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Opengento\CountryStorePhone\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Opengento\CountryStore\Api\Data\CountryInterface;
use function array_fill_keys;
use function array_merge;

final class CountryPhoneNumberList
{
    private const CONFIG_PATH_COUNTRY_PHONE_MAP = 'country/information/phone_number';

    private ScopeConfigInterface $scopeConfig;

    private SerializerInterface $serializer;

    /**
     * @var string[]
     */
    private array $phoneNumberMapping;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        SerializerInterface $serializer
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->serializer = $serializer;
    }

    public function getList(): array
    {
        return $this->phoneNumberMapping ??= $this->resolveCountryStorePhoneNumberMap();
    }

    public function get(CountryInterface $country): ?string
    {
        return $this->getList()[$country->getCode()] ?? null;
    }

    private function resolveCountryStorePhoneNumberMap(): array
    {
        $countryMapping = [];
        $countryStorePhoneMap = $this->serializer->unserialize(
            $this->scopeConfig->getValue(self::CONFIG_PATH_COUNTRY_PHONE_MAP) ?? '{}'
        );

        foreach ($countryStorePhoneMap as $phoneNumber) {
            if (isset($phoneNumber['phone_number'], $phoneNumber['countries'])) {
                $countryMapping[] = array_fill_keys((array) $phoneNumber['countries'], $phoneNumber['phone_number']);
            }
        }

        return array_merge([], ...$countryMapping);
    }
}
