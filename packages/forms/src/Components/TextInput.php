<?php

namespace Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\TextInput\Mask;
use Illuminate\Contracts\Support\Arrayable;

class TextInput extends Field
{
    use Concerns\CanBeAutocapitalized;
    use Concerns\CanBeAutocompleted;
    use Concerns\CanBeLengthConstrained;
    use Concerns\HasExtraAlpineAttributes;
    use Concerns\HasPlaceholder;

    protected string $view = 'forms::components.text-input';

    protected ?Closure $configureMaskUsing = null;

    protected array | Arrayable | Closure | null $datalistOptions = null;

    protected string | Closure | null $inputMode = null;

    protected bool | Closure $isEmail = false;

    protected bool | Closure $isNumeric = false;

    protected bool | Closure $isPassword = false;

    protected bool | Closure $isTel = false;

    protected bool | Closure $isUrl = false;

    protected $maxValue = null;

    protected $minValue = null;

    protected int | float | string | Closure | null $step = null;

    protected string | Closure | null $postfixLabel = null;

    protected string | Closure | null $prefixLabel = null;

    protected string | Closure | null $type = null;

    public function currentPassword(bool | Closure $condition = true): static
    {
        $this->rule('current_password', $condition);

        return $this;
    }

    public function datalist(array | Arrayable | Closure | null $options): static
    {
        $this->datalistOptions = $options;

        return $this;
    }

    public function email(bool | Closure $condition = true): static
    {
        $this->isEmail = $condition;

        $this->rule('email', $condition);

        return $this;
    }

    public function inputMode(string | Closure | null $mode): static
    {
        $this->inputMode = $mode;

        return $this;
    }

    public function integer(bool | Closure $condition = true): static
    {
        $this->numeric($condition);
        $this->inputMode(fn (): ?string => $condition ? 'numeric' : null);
        $this->step(fn (): ?int => $condition ? 1 : null);

        return $this;
    }

    public function mask(?Closure $configuration): static
    {
        $this->configureMaskUsing = $configuration;

        return $this;
    }

    public function maxValue($value): static
    {
        $this->maxValue = $value;

        $this->rule(function (): string {
            $value = $this->getMaxValue();

            return "max:{$value}";
        });

        return $this;
    }

    public function minValue($value): static
    {
        $this->minValue = $value;

        $this->rule(function (): string {
            $value = $this->getMinValue();

            return "min:{$value}";
        });

        return $this;
    }

    public function numeric(bool | Closure $condition = true): static
    {
        $this->isNumeric = $condition;

        $this->inputMode(fn (): ?string => $condition ? 'decimal' : null);
        $this->rule('numeric', $condition);
        $this->step(fn (): ?string => $condition ? 'any' : null);

        return $this;
    }

    public function password(bool | Closure $condition = true): static
    {
        $this->isPassword = $condition;

        return $this;
    }

    public function prefix(string | Closure | null $label): static
    {
        $this->prefixLabel = $label;

        return $this;
    }

    public function postfix(string | Closure | null $label): static
    {
        $this->postfixLabel = $label;

        return $this;
    }

    public function step(int | float | string | Closure | null $interval): static
    {
        $this->step = $interval;

        return $this;
    }

    public function tel(bool | Closure $condition = true): static
    {
        $this->isTel = $condition;

        return $this;
    }

    public function type(string | Closure | null $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function url(bool | Closure $condition = true): static
    {
        $this->isUrl = $condition;

        $this->rule('url', $condition);

        return $this;
    }

    public function getDatalistOptions(): ?array
    {
        $options = $this->evaluate($this->datalistOptions);

        if ($options instanceof Arrayable) {
            $options = $options->toArray();
        }

        return $options;
    }

    public function getInputMode(): ?string
    {
        return $this->evaluate($this->inputMode);
    }

    public function getMask(): ?Mask
    {
        if (! $this->hasMask()) {
            return null;
        }

        return $this->evaluate($this->configureMaskUsing, [
            'mask' => new TextInput\Mask(),
        ]);
    }

    public function getJsonMaskConfiguration(): ?string
    {
        return $this->getMask()?->toJson();
    }

    public function getMaxValue()
    {
        return $this->evaluate($this->maxValue);
    }

    public function getMinValue()
    {
        return $this->evaluate($this->minValue);
    }

    public function getPrefixLabel(): ?string
    {
        return $this->evaluate($this->prefixLabel);
    }

    public function getPostfixLabel(): ?string
    {
        return $this->evaluate($this->postfixLabel);
    }

    public function getStep(): int | float | string | null
    {
        return $this->evaluate($this->step);
    }

    public function getType(): string
    {
        if ($type = $this->evaluate($this->type)) {
            return $type;
        } elseif ($this->isEmail()) {
            return 'email';
        } elseif ($this->isNumeric()) {
            return 'number';
        } elseif ($this->isPassword()) {
            return 'password';
        } elseif ($this->isTel()) {
            return 'tel';
        } elseif ($this->isUrl()) {
            return 'url';
        }

        return 'text';
    }

    public function hasMask(): bool
    {
        return $this->configureMaskUsing !== null;
    }

    public function isEmail(): bool
    {
        return (bool) $this->evaluate($this->isEmail);
    }

    public function isNumeric(): bool
    {
        return (bool) $this->evaluate($this->isNumeric);
    }

    public function isPassword(): bool
    {
        return (bool) $this->evaluate($this->isPassword);
    }

    public function isTel(): bool
    {
        return (bool) $this->evaluate($this->isTel);
    }

    public function isUrl(): bool
    {
        return (bool) $this->evaluate($this->isUrl);
    }
}
