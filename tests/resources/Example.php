<?php

return array(
    'PostgreSQL' => array(
        'developer' => 'Michael Stonebraker',
        'database' => array(
            'system' => 'postgres',
            'embedded' => 0,
            'license' => 'PostgreSQL License',
        ),
        'website' => array(
            'address' => 'postgresql.org',
        ),
        'numbers' => array(
            'float' => 0.8,
        ),
        'tools' => array('psql', 'pg_dump')
    ),
    'MariaDB' => array(
        'developer' => 'Michael Widenius',
        'database' => array(
            'system' => 'mariadb',
            'embedded' => 0,
            'license' => 'GPLv2',
        ),
        'website' => array(
            'address' => 'mariadb.org',
        ),
        'numbers' => array(
            'float' => 1.2,
        ),
        'tools' => array(
            0 => 'mysql',
            1 => 'mysqldump'
        )
    ),
    'SQLite' => array(
        'developer' => 'Dwayne Richard Hipp',
        'database' => array(
            'system' => 'sqlite',
            'embedded' => 1,
            'license' => 'Public domain',
        ),
        'website' => array(
            'address' => 'sqlite.org',
        ),
        'numbers' => array(
            'float' => 2.4,
        )
    ),
);
