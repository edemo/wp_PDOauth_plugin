all:
	tools/script

testenv:
	docker run --rm -p 5901:5901 -v $$(pwd):/wp_oauth_plugin -it magwas/wp_oauth_plugin /bin/bash

check:
	phpunit --stderr tests

e2e:	recording
	PYTHONPAT=end2endtest python3 -m unittest discover -v -f -s end2endtest -p "*.py"

PDOauth:
	git clone https://github.com/edemo/PDOauth.git

runsso: PDOauth
	cd PDOauth; make runserver

cleanup: stoprecording
	rm -rf /tmp/wordpress/
	mv /tmp/wplog/* shippable
	rm -rf tmp/

recording:
	start-stop-daemon --start --background --oknodo --name flvrec --make-pidfile --pidfile /tmp/flvrec.pid --startas /usr/bin/python -- /usr/local/bin/flvrec.py -o /tmp/wplog/record.flv :1

stoprecording:
	-start-stop-daemon --stop --pidfile /tmp/flvrec.pid

