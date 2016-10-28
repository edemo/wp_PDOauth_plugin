FROM ubuntu:xenial

RUN locale-gen en_US en_US.UTF-8 && \
    dpkg-reconfigure -f noninteractive locales

RUN apt-get update

RUN apt-get -uy upgrade

RUN echo 'mysql-server mysql-server/root_password password password' | debconf-set-selections
RUN echo 'mysql-server mysql-server/root_password_again password password' | debconf-set-selections

RUN apt-get -y install wordpress vim less xvfb firefox chromium-chromedriver \
    wget git mysql-server iputils-ping vnc4server net-tools strace fvwm python3-pip make

ADD requirements.txt /tmp/requirements.txt

RUN pip3 install -r /tmp/requirements.txt

RUN wget https://github.com/mozilla/geckodriver/releases/download/v0.11.1/geckodriver-v0.11.1-linux64.tar.gz -O /tmp/geckodriver.tar.gz;cd /usr/local/bin;tar xvzf /tmp/geckodriver.tar.gz

RUN wget https://phar.phpunit.de/phpunit.phar;chmod +x phpunit.phar;mv phpunit.phar /usr/local/bin/phpunit

CMD /bin/bash


