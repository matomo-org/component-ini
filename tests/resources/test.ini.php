; <?php exit; ?> TEST
; For example if you want to override action_title_category_delimiter,
; edit config/config.ini.php and add the following:
; [General]
; action_title_category_delimiter = "-"

[database]
host =
username =
password =
; Phasellus tincidunt ultrices molestie.
; Nunc ultricies augue justo, a faucibus lectus sollicitudin quis.
; In nunc massa, congue faucibus tempor ac, vehicula eleifend urna.
charset = utf8

[tests]
; Lorem ipsum dolor sit amet, consectetur adipiscing elit.
; Vivamus vitae risus sit amet ante placerat vulputate sit amet vel purus.
; Vivamus ac cursus mauris. Phasellus congue tempor lacus.
test_key   = localhost
test_key2 = "127.0.0.1"
test_key3 =

; get ullamcorper nunc molestie. Null
testkey4 = ""
testkey5 = 5
; Vestibulum malesuada non nisl vitae maximus.
testkey6 = ""
; Proin convallis augue sed sapien bibendum, et maximus purus rutrum.
testkey7 = "path to foo bar"
test_key8[] = "default1"
test_key8[] = "default2"
test_key9 = "c3.large"

[log]
; Fusce maximus bibendum lectus, nec tristique enim malesuada hendrerit.
log_writers[] = screen
log_writers[] = file

; Quisque lorem justo, sollicitudin at pellentesque interdum, euismod quis nulla.
; Sed malesuada dolor in tempus ornare. Etiam lobortis commodo congue.
log_level = WARN

[Cache]
; available backends are 'file', 'array', 'null', 'redis', 'chained'
; 'array' will cache data only during one request
; 'null' will not cache anything at all
; 'file' will cache on the filesystem
; 'redis' will cache on a Redis server. Further configuration in [RedisCache] is needed
; 'chained' will chain multiple cache backends. Further configuration in [ChainedCache] is needed
backend = chained
