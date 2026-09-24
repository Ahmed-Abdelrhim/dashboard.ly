<?php

namespace App\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;

class OtpInput extends Field
{
    protected string $view = 'filament.forms.components.otp-input';

    protected int|Closure $length = 6;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rule('numeric');
        $this->rule(static fn (OtpInput $component): string => "digits:{$component->getLength()}");
        $this->validationMessages([
            'required' => 'The authentication code is required.',
            'numeric' => 'The authentication code must contain only numbers.',
            'digits' => 'The authentication code must be exactly :digits digits.',
        ]);
    }

    public function length(int|Closure $length): static
    {
        $this->length = $length;

        return $this;
    }

    public function getLength(): int
    {
        return (int) $this->evaluate($this->length);
    }
}
