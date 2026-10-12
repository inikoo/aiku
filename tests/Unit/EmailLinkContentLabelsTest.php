<?php

use App\Actions\Comms\Traits\WithEmailLinkContentLabels;

function emailLinkContentLabels(array $anchors): array
{
    $labeller = new class () {
        use WithEmailLinkContentLabels;

        public function labels(array $anchors): array
        {
            $counters = [];

            return array_map(function (string $anchor) use (&$counters) {
                return $this->getContentLabel($anchor, $counters);
            }, $anchors);
        }
    };

    return $labeller->labels($anchors);
}

test('a button built in the email workshop is labelled as a button', function () {
    $labels = emailLinkContentLabels([
        '<a class="button" href="https://www.ancientwisdom.biz/" target="_blank" style="display:inline-block;text-decoration:none"><span style="word-break:break-word">Shop now</span></a>',
        '<a href="https://www.ancientwisdom.biz/new" target="_blank" class="cta button wide"><span>See more</span></a>',
    ]);

    expect($labels)->toBe(['hero_button', 'button_2']);
});

test('a button exported with outlook markup is still labelled as a button', function () {
    $labels = emailLinkContentLabels([
        '<a href="https://www.ancientwisdom.biz/" target="_blank"><!--[if mso]><v:roundrect href="https://www.ancientwisdom.biz/"><![endif]--><span>Shop now</span></a>',
    ]);

    expect($labels)->toBe(['hero_button']);
});

test('image and text links keep their own labels next to buttons', function () {
    $labels = emailLinkContentLabels([
        '<a href="https://www.ancientwisdom.biz/a" style="outline:none"><img src="https://cdn.example.com/a.png" alt=""></a>',
        '<a style="text-decoration:underline;color:#000" href="https://www.ancientwisdom.biz/b">Read about our button range</a>',
        '<a class="button-link" href="https://www.ancientwisdom.biz/c">Not a button</a>',
        '<a class="button" href="https://www.ancientwisdom.biz/d"><span>Buy</span></a>',
    ]);

    expect($labels)->toBe(['hero_image', 'text_1', 'text_2', 'hero_button']);
});
