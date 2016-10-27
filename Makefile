all:
	tools/script

testenv:
	docker run --rm -p 5901:5901 -v $$(pwd):/wp_oauth_plugin -it wp_oauth_plugin_test /bin/bash

check:
	phpunit --stderr tests

