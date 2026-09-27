<?php

namespace App\Livewire;

use App\Models\HeroSlide;
use App\Models\Setting;
use Livewire\Component;

class HeroSlider extends Component
{
    public function render()
    {
        $slides = HeroSlide::active()->get();
        $autoplayDuration = max(4, min(6, (int) Setting::get('slider_autoplay_duration', 5)));

        return view('livewire.hero-slider', [
            'slides'           => $slides,
            'autoplayDuration' => $autoplayDuration,
        ]);
    }
}
