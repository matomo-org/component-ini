<?php

return array(
    'ini' => array(
        'injection' => array(
            "] = 'xyz';\n FOO =" => 1,
            ' array(0,1,2,3)' => 'test',
            '[\'Test-Test-\']' => 4.8,
            '[\'Under_Score_\']' => 6.4,
            '<? []' => false
        )
    )
);
