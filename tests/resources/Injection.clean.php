<?php

return array(
    'ini' => array(
        'injection' => array(
            'xyzFOO' => 1,
            'array0123' => 'test',
            'Test-Test-' => 4.8,
            'Under_Score_' => 6.4,
            0 => 0,
        ),
        'injected-with_ab' => [
            'test' => "value\n[newsection]\nc=d\n"
        ],
    ),
    'a-poison_ ab' => [
        'test' => "va\"lue\n[newsection]\nc=d\n"
    ],
);
