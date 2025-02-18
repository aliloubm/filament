<?php

namespace Filament\Forms\Components;

use Closure;
use function Filament\Forms\array_move_after;
use function Filament\Forms\array_move_before;
use Filament\Forms\ComponentContainer;
use Illuminate\Support\Str;

class Repeater extends Field
{
    use Concerns\CanLimitItemsLength;

    protected string $view = 'forms::components.repeater';

    protected string | Closure | null $createItemButtonLabel = null;

    protected bool | Closure $isItemMovementDisabled = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registerListeners([
            'repeater::createItem' => [
                function (Repeater $component, string $statePath): void {
                    if ($component->isDisabled()) {
                        return;
                    }

                    if ($statePath !== $component->getStatePath()) {
                        return;
                    }

                    $newUuid = (string) Str::uuid();

                    $livewire = $component->getLivewire();
                    data_set($livewire, "{$statePath}.{$newUuid}", []);

                    $component->hydrateDefaultItemState($newUuid);
                },
            ],
            'repeater::deleteItem' => [
                function (Repeater $component, string $statePath, string $uuidToDelete): void {
                    if ($component->isDisabled()) {
                        return;
                    }

                    if ($statePath !== $component->getStatePath()) {
                        return;
                    }

                    $items = $component->getNormalisedState();

                    unset($items[$uuidToDelete]);

                    $livewire = $component->getLivewire();
                    data_set($livewire, $statePath, $items);
                },
            ],
            'repeater::moveItemDown' => [
                function (Repeater $component, string $statePath, string $uuidToMoveDown): void {
                    if ($component->isDisabled()) {
                        return;
                    }

                    if ($component->isItemMovementDisabled()) {
                        return;
                    }

                    if ($statePath !== $component->getStatePath()) {
                        return;
                    }

                    $items = array_move_after($component->getNormalisedState(), $uuidToMoveDown);

                    $livewire = $component->getLivewire();
                    data_set($livewire, $statePath, $items);
                },
            ],
            'repeater::moveItemUp' => [
                function (Repeater $component, string $statePath, string $uuidToMoveUp): void {
                    if ($component->isDisabled()) {
                        return;
                    }

                    if ($component->isItemMovementDisabled()) {
                        return;
                    }

                    if ($statePath !== $component->getStatePath()) {
                        return;
                    }

                    $items = array_move_before($component->getNormalisedState(), $uuidToMoveUp);

                    $livewire = $component->getLivewire();
                    data_set($livewire, $statePath, $items);
                },
            ],
        ]);

        $this->createItemButtonLabel(function (Repeater $component) {
            return __('forms::components.repeater.buttons.create_item.label', [
                'label' => lcfirst($component->getLabel()),
            ]);
        });
    }

    public function createItemButtonLabel(string | Closure | null $label): static
    {
        $this->createItemButtonLabel = $label;

        return $this;
    }

    public function defaultItems(int | Closure $count): static
    {
        $this->default(function (Repeater $component) use ($count): array {
            $items = [];

            foreach (range(1, $component->evaluate($count)) as $index) {
                $items[(string) Str::uuid()] = [];
            }

            return $items;
        });

        return $this;
    }

    public function disableItemMovement(bool | Closure $condition = true): static
    {
        $this->isItemMovementDisabled = $condition;

        return $this;
    }

    public function hydrateDefaultItemState(string $uuid): void
    {
        $this->getChildComponentContainers()[$uuid]->hydrateDefaultState();
    }

    public function getChildComponentContainers(): array
    {
        return collect($this->getNormalisedState())
            ->map(function ($itemData, $itemIndex): ComponentContainer {
                return $this
                    ->getChildComponentContainer()
                    ->getClone()
                    ->statePath($itemIndex);
            })->toArray();
    }

    public function getCreateItemButtonLabel(): string
    {
        return $this->evaluate($this->createItemButtonLabel);
    }

    public function getNormalisedState(): array
    {
        if (! is_array($state = $this->getState())) {
            return [];
        }

        return array_filter($state, fn ($item) => is_array($item));
    }

    public function isItemMovementDisabled(): bool
    {
        return $this->evaluate($this->isItemMovementDisabled);
    }
}
