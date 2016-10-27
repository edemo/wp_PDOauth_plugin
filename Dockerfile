FROM ubuntu:xenial

RUN locale-gen en_US en_US.UTF-8 && \
    dpkg-reconfigure -f noninteractive locales

RUN apt-get update

RUN apt-get -uy upgrade

RUN apt-get -y install wordpress vim less xvfb firefox chromium-chromedriver wget git

RUN apt-get -y install make

RUN wget https://github.com/mozilla/geckodriver/releases/download/v0.11.1/geckodriver-v0.11.1-linux64.tar.gz -O /tmp/geckodriver.tar.gz;cd /usr/local/bin;tar xvzf /tmp/geckodriver.tar.gz

RUN wget https://phar.phpunit.de/phpunit.phar;chmod +x phpunit.phar;mv phpunit.phar /usr/local/bin/phpunit

CMD /bin/bash


