<?php

namespace Tests\Unit;

use App\Rules\ValidateEgyptianOrUkPhone;
use PHPUnit\Framework\TestCase;

class ValidateEgyptianOrUkPhoneTest extends TestCase
{
    private ValidateEgyptianOrUkPhone $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new ValidateEgyptianOrUkPhone;
    }

    public function test_handles_empty_or_null_phone(): void
    {
        $this->assertTrue($this->validator->handle(null));
        $this->assertTrue($this->validator->handle(''));
    }

    public function test_validates_egyptian_mobile_numbers(): void
    {
        // With country code (+20)
        $this->assertTrue($this->validator->handle('+201012345678'));
        $this->assertTrue($this->validator->handle('+201112345678'));
        $this->assertTrue($this->validator->handle('+201212345678'));
        $this->assertTrue($this->validator->handle('+201512345678'));

        // With 0020
        $this->assertTrue($this->validator->handle('00201012345678'));

        // Without country code (010, 011, 012, 015)
        $this->assertTrue($this->validator->handle('01012345678'));
        $this->assertTrue($this->validator->handle('01112345678'));
        $this->assertTrue($this->validator->handle('01212345678'));
        $this->assertTrue($this->validator->handle('01512345678'));

        // With spaces and formatting
        $this->assertTrue($this->validator->handle('+20 10 1234 5678'));
        $this->assertTrue($this->validator->handle('010-1234-5678'));
        $this->assertTrue($this->validator->handle('(010) 1234 5678'));
    }

    public function test_validates_egyptian_landline_numbers(): void
    {
        // Cairo (+202)
        $this->assertTrue($this->validator->handle('+20212345678'));
        $this->assertTrue($this->validator->handle('0212345678'));
        // Alexandria (+203)
        $this->assertTrue($this->validator->handle('+2031234567'));
        $this->assertTrue($this->validator->handle('031234567'));
    }

    public function test_validates_uk_mobile_numbers(): void
    {
        // With +44
        $this->assertTrue($this->validator->handle('+447123456789'));
        // With 0044
        $this->assertTrue($this->validator->handle('00447123456789'));
        // Local 07
        $this->assertTrue($this->validator->handle('07123456789'));
        // With spaces and formatting
        $this->assertTrue($this->validator->handle('+44 7123 456 789'));
        $this->assertTrue($this->validator->handle('07123-456-789'));
    }

    public function test_validates_uk_landlines_and_special_numbers(): void
    {
        // London (+4420)
        $this->assertTrue($this->validator->handle('+442071234567'));
        $this->assertTrue($this->validator->handle('02071234567'));
        // Freephone 0800
        $this->assertTrue($this->validator->handle('0800123456'));
        $this->assertTrue($this->validator->handle('+44800123456'));
    }

    public function test_rejects_invalid_numbers(): void
    {
        // US phone number
        $this->assertFalse($this->validator->handle('+12025550123'));
        // Incomplete / random strings
        $this->assertFalse($this->validator->handle('12345'));
        $this->assertFalse($this->validator->handle('not-a-number'));
        $this->assertFalse($this->validator->handle('+201912345678')); // 19 is not a valid Egyptian mobile prefix
        $this->assertFalse($this->validator->handle('+446123456789')); // 6 is not a valid UK mobile prefix
    }
}
