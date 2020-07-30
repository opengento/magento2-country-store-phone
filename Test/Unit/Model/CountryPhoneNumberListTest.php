<?php
/**
 * Copyright © OpenGento, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Opengento\CountryStorePhone\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Opengento\CountryStore\Api\Data\CountryInterface;
use Opengento\CountryStorePhone\Model\CountryPhoneNumberList;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Opengento\CountryStorePhone\Model\CountryStorePhoneNumberList
 */
class CountryPhoneNumberListTest extends TestCase
{
    /**
     * @var MockObject|ScopeConfigInterface
     */
    private $scopeConfig;

    private CountryPhoneNumberList $countryPhoneNumberList;

    protected function setUp(): void
    {
        $this->scopeConfig = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $this->countryPhoneNumberList = new CountryPhoneNumberList($this->scopeConfig, new Json());
    }

    /**
     * @dataProvider singleCountryStoreMap
     */
    public function testGet(?string $config, ?string $phoneNumber, CountryInterface $country): void
    {
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with($this->equalTo('country/information/phone_number'))
            ->willReturn($config);

        $this->assertSame($phoneNumber, $this->countryPhoneNumberList->get($country));
    }

    /**
     * @dataProvider countryStoreMap
     */
    public function testGetList(?string $config, array $countryPhoneNumberList): void
    {
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with($this->equalTo('country/information/phone_number'))
            ->willReturn($config);

        $this->assertSame($countryPhoneNumberList, $this->countryPhoneNumberList->getList());
    }

    public function singleCountryStoreMap(): array
    {
        $countryFr = $this->getMockForAbstractClass(CountryInterface::class);
        $countryFr->method('getCode')->willReturn('FR');
        $countryUs = $this->getMockForAbstractClass(CountryInterface::class);
        $countryUs->method('getCode')->willReturn('US');

        return [
            [
                '{"_0":{"phone_number":"+800","countries":["US"]},"_1":{"phone_number":"+33","countries":["FR","DE"]}}',
                '+33',
                $countryFr
            ],
            [
                '{"_0":{"phone_number":"+800","countries":["US"]},"_1":{"phone_number":"+33","countries":[]}}',
                '+800',
                $countryUs
            ],
            [
                null,
                null,
                $countryFr
            ],
        ];
    }

    public function countryStoreMap(): array
    {
        return [
            [
                '{"_0":{"phone_number":"+800","countries":["US"]},"_1":{"phone_number":"+33","countries":["FR","DE"]}}',
                ['US' => '+800', 'FR' => '+33', 'DE' => '+33'],
            ],
            [
                '{"_0":{"phone_number":"+800","countries":["US"]},"_1":{"phone_number":"+33","countries":[]}}',
                ['US' => '+800'],
            ],
            [
                null,
                [],
            ],
        ];
    }
}
