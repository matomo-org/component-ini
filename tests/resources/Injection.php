<?php

return array(
    'ini' => array(
        'injection' => array(
            "] = 'xyz';\n FOO =" => 1,
            ' array(0,1,2,3)' => 'test',
            '[\'Test-Test-\']' => 4.8,
            '[\'Under_Score_\']' => 6.4,
            '<? []' => false
        ),
        "]\n[injected-with_] a=b\n" => array(
            'te"st' => "value\"\n[newsection]\nc=d\n",
        ),
    ),
    "]\n[a-\"poison_] a=b\n" => array(
        'test' => "va\"lue\n[newsection]\nc=d\n",
    ),
);
