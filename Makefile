all:
	tools/script

testenv:
	docker run --rm -p 5901:5901 -v $$(pwd):/wp_oauth_plugin -it magwas/wp_oauth_plugin /bin/bash

check:
	phpunit --stderr tests

e2e:
	PYTHONPAT=end2endtest python3 -m unittest discover -v -f -s end2endtest -p "*.py"

PDOauth:
	git clone https://github.com/edemo/PDOauth.git

runsso: PDOauth
	cd PDOauth; make runserver
