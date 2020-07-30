<?php
/**
 * Copyright © OpenGento, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Opengento\CountryStorePhone\Test\Unit\Model\ResourceModel\Country\Relation;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Opengento\CountryStore\Api\Data\CountryExtensionInterface;
use Opengento\CountryStore\Api\Data\CountryInterface;
use Opengento\CountryStorePhone\Model\CountryPhoneNumberList;
use Opengento\CountryStorePhone\Model\ResourceModel\Country\Relation\ReadHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Opengento\CountryStorePhone\Model\ResourceModel\Country\Relation\ReadHandler
 */
class ReadHandlerTest extends TestCase
{
    /**
     * @var MockObject|ScopeConfigInterface
     */
    private $scopeConfig;

    private ReadHandler $readHandler;

    protected function setUp(): void
    {
        $this->scopeConfig = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $this->readHandler = new ReadHandler(
            $this->scopeConfig,
            new CountryPhoneNumberList($this->scopeConfig, new Json())
        );
    }

    /**
     * @dataProvider readHandlerData
     */
    public function testExecute(CountryInterface $country, string $phoneNumber, int $count, array $config): void
    {
        $this->scopeConfig->expects($this->exactly($count))->method('getValue')->willReturnMap($config);

        /** @var CountryInterface $extensionCountry */
        $extensionCountry = $this->readHandler->execute($country);

        $this->assertSame($country, $extensionCountry);
        $this->assertSame($phoneNumber, $extensionCountry->getExtensionAttributes()->getPhoneNumber());
    }

    public function readHandlerData(): array
    {
        return [
            [
                $this->createCountryMock('US', '+800'),
                '+800',
                1,
                [
                    [
                        'country/information/phone_number',
                        'default',
                        null,
                        '{"_0":{"phone_number":"+800","countries":["US"]}}'
                    ],
                ],
            ],
            [
                $this->createCountryMock('FR', '+33'),
                '+33',
                2,
                [
                    [
                        'country/information/phone_number',
                        'default',
                        null,
                        '{"_0":{"phone_number":"+800","countries":["US"]}}'
                    ],
                    [
                        'general/store_information/phone',
                        'store',
                        null,
                        '+33'
                    ],
                ],
            ],
        ];
    }

    private function createCountryMock(string $countryCode, string $phoneNumber): MockObject
    {
        $extensionAttributesMock = $this->getMockForAbstractClass(CountryExtensionInterface::class);
        $extensionAttributesMock->expects($this->once())->method('getPhoneNumber')->willReturn($phoneNumber);
        $extensionAttributesMock->expects($this->once())
            ->method('setPhoneNumber')
            ->with($phoneNumber)
            ->willReturn($extensionAttributesMock);

        $countryMock = $this->getMockForAbstractClass(CountryInterface::class);
        $countryMock->expects($this->once())->method('getCode')->willReturn($countryCode);
        $countryMock->expects($this->exactly(2))
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributesMock);

        return $countryMock;
    }
}
