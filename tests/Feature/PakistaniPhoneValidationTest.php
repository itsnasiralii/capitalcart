<?php

namespace Tests\Feature;

use App\Helpers\PhoneHelper;
use Tests\TestCase;

class PakistaniPhoneValidationTest extends TestCase
{
    public function test_validates_correct_pakistani_mobile_numbers(): void
    {
        $validNumbers = [
            '03001234567',
            '+923001234567',
            '923001234567',
            '03451234567',
            '03121234567',
            '03339876543',
            '0300 1234567',
            '+92-300-1234567',
        ];

        foreach ($validNumbers as $number) {
            $this->assertTrue(
                PhoneHelper::isValidPakistaniNumber($number),
                "Expected {$number} to be considered a valid Pakistani mobile number."
            );
        }
    }

    public function test_rejects_invalid_phone_numbers(): void
    {
        $invalidNumbers = [
            '12345',
            '02135812345',      // Karachi PTCL landline (starts with 021, not mobile 03)
            '0519201234',       // Islamabad PTCL landline
            '+14155552671',     // US phone
            'abcdefghijk',      // Alphabet letters
            '030012345',        // Too short (9 digits)
            '0300123456789',    // Too long (13 digits)
            '',
            null,
        ];

        foreach ($invalidNumbers as $number) {
            $this->assertFalse(
                PhoneHelper::isValidPakistaniNumber($number),
                "Expected '{$number}' to be rejected as an invalid Pakistani mobile number."
            );
        }
    }

    public function test_normalizes_to_local_03_format(): void
    {
        $this->assertEquals('03001234567', PhoneHelper::toLocal('+923001234567'));
        $this->assertEquals('03001234567', PhoneHelper::toLocal('923001234567'));
        $this->assertEquals('03001234567', PhoneHelper::toLocal('03001234567'));
        $this->assertEquals('03001234567', PhoneHelper::toLocal('0300-1234567'));
    }

    public function test_normalizes_to_international_92_format(): void
    {
        $this->assertEquals('923001234567', PhoneHelper::toInternational('03001234567'));
        $this->assertEquals('923001234567', PhoneHelper::toInternational('+923001234567'));
        $this->assertEquals('923001234567', PhoneHelper::toInternational('923001234567'));
    }

    public function test_privacy_masks_phone_number(): void
    {
        $masked = PhoneHelper::mask('03001234567');
        $this->assertEquals('030*******7', $masked);
        $this->assertStringNotContainsString('123456', $masked);
    }

    public function test_privacy_masks_customer_name(): void
    {
        $this->assertEquals('N***', PhoneHelper::maskName('Nasir Ali'));
        $this->assertEquals('H***', PhoneHelper::maskName('Hamza Khan'));
        $this->assertEquals('Z***', PhoneHelper::maskName('Zainab'));
    }
}
