<?php

namespace App\Livewire\Components;

use Livewire\Component;

class PopularProduct extends Component
{
    public array $samples = [1, 2, 3, 4, 5, 6, 7];
    public int $currentIndex = 0;
    public int $showItems = 5;
    public int $translateX = 0;
    public bool $isNext = true;
    public bool $isPrev = false;

    public function hasNext(): bool
    {
        if ($this->showItems + $this->currentIndex == sizeof($this->samples)) {
            return false;
        }

        return true;
    }

    public function hasPrev(): bool
    {
        if ($this->showItems + $this->currentIndex == sizeof($this->samples)) {
           return true;
        }
        if (sizeof($this->samples) - $this->currentIndex == $this->showItems) {
            return false;
        }
        return true;
    }

    public function next(): void
    {
        $this->currentIndex += 1;
        $persentage = $this->currentIndex * -107;
        $this->translateX = $persentage;
        $this->isNext = $this->hasNext();
        $this->isPrev = $this->hasPrev();
    }

    public function prev(): void
    {
        $persentage = $this->currentIndex * 107;
        $this->currentIndex -= 1;
        $persentage -= 107;
        $this->translateX = $persentage;
        $this->isNext = $this->hasNext();
        $this->isPrev = $this->hasPrev();
    }

    public function render()
    {
        return view('livewire.components.popular-product');
    }
}
