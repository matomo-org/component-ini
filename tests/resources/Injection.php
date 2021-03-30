<?php

return array(
    'ini' => array(
        'injection' => array(
            "] = 'xyz';\n FOO =" => 1,
            ' array(0,1,2,3)' => 'test',
            '[\'Test\']' => 4.8,
            '<? []' => false
        )
    )
);
