all:
	tools/script

testenv:
	docker run --rm -p 5901:5901 -v $$(pwd):/wp_oauth_plugin -it wp_oauth_plugin_test /bin/bash

check:
	phpunit --stderr tests

end2endtest:
	PYTHONPAT=end2endtest python3 -m unittest discover -v -f -s end2endtest -p "*.py"

